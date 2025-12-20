<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

/**
 * Stripe Payment Service
 *
 * Handles Stripe payment processing using Stripe PHP SDK.
 *
 * @requirement CHK-009 Integrate Stripe payment
 */
class StripePaymentService implements PaymentServiceInterface
{
    private ?object $stripe = null;

    public function __construct()
    {
        if (class_exists(\Stripe\Stripe::class)) {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $this->stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        }
    }

    /**
     * Get the payment gateway name.
     */
    public function getGatewayName(): string
    {
        return Payment::GATEWAY_STRIPE;
    }

    /**
     * Check if the gateway is enabled.
     */
    public function isEnabled(): bool
    {
        return !empty(config('services.stripe.secret'));
    }

    /**
     * Initiate a Stripe payment (create PaymentIntent).
     */
    public function initiatePayment(Order $order, array $paymentData = []): array
    {
        try {
            if (!$this->stripe) {
                // Mock response for development
                return $this->mockPaymentIntent($order);
            }

            // Create PaymentIntent
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => (int) ($order->total * 100), // Convert to cents
                'currency' => strtolower($order->currency),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'description' => "Order {$order->order_number}",
            ]);

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'gateway' => $this->getGatewayName(),
                'transaction_id' => $paymentIntent->id,
                'status' => Payment::STATUS_PENDING,
                'amount' => $order->total,
                'currency' => $order->currency,
                'gateway_response' => [
                    'payment_intent_id' => $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                ],
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'publishable_key' => config('services.stripe.key'),
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment initiation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to initiate payment. Please try again.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Confirm a Stripe payment.
     */
    public function confirmPayment(Payment $payment, array $data = []): array
    {
        try {
            if (!$this->stripe) {
                // Mock confirmation for development
                return $this->mockConfirmPayment($payment);
            }

            $paymentIntentId = $data['payment_intent_id'] ?? $payment->transaction_id;

            // Retrieve the PaymentIntent
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            if ($paymentIntent->status === 'succeeded') {
                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'gateway_response' => array_merge(
                        $payment->gateway_response ?? [],
                        ['confirmed_at' => now()->toIso8601String()]
                    ),
                ]);

                // Update order status
                $payment->order->update(['status' => Order::STATUS_CONFIRMED]);

                return [
                    'success' => true,
                    'message' => 'Payment confirmed successfully.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Payment not yet completed.',
                'status' => $paymentIntent->status,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment confirmation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to confirm payment.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process a refund.
     */
    public function refund(Payment $payment, ?float $amount = null): array
    {
        try {
            if (!$this->stripe) {
                return $this->mockRefund($payment, $amount);
            }

            $refundData = [
                'payment_intent' => $payment->transaction_id,
            ];

            if ($amount !== null) {
                $refundData['amount'] = (int) ($amount * 100);
            }

            $refund = $this->stripe->refunds->create($refundData);

            $payment->update([
                'status' => Payment::STATUS_REFUNDED,
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    [
                        'refund_id' => $refund->id,
                        'refunded_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'message' => 'Refund processed successfully.',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process refund.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle Stripe webhook.
     */
    public function handleWebhook(array $payload, string $signature): array
    {
        try {
            $webhookSecret = config('services.stripe.webhook_secret');

            if ($webhookSecret && class_exists(\Stripe\Webhook::class)) {
                $event = \Stripe\Webhook::constructEvent(
                    json_encode($payload),
                    $signature,
                    $webhookSecret
                );
            } else {
                // Development mode - trust the payload
                $event = (object) $payload;
            }

            $eventType = $event->type ?? $payload['type'] ?? '';
            $data = $event->data->object ?? $payload['data']['object'] ?? [];

            switch ($eventType) {
                case 'payment_intent.succeeded':
                    return $this->handlePaymentSuccess($data);

                case 'payment_intent.payment_failed':
                    return $this->handlePaymentFailed($data);

                case 'charge.refunded':
                    return $this->handleRefund($data);

                default:
                    return [
                        'success' => true,
                        'message' => "Unhandled event type: {$eventType}",
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Stripe webhook handling failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Webhook handling failed.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle successful payment webhook.
     */
    private function handlePaymentSuccess(array $data): array
    {
        $paymentIntentId = $data['id'] ?? null;

        if (!$paymentIntentId) {
            return ['success' => false, 'message' => 'Missing payment intent ID'];
        }

        $payment = Payment::where('transaction_id', $paymentIntentId)->first();

        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }

        $payment->update(['status' => Payment::STATUS_COMPLETED]);
        $payment->order->update(['status' => Order::STATUS_CONFIRMED]);

        Log::info('Stripe payment succeeded via webhook', [
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
        ]);

        return [
            'success' => true,
            'event_type' => 'payment_success',
            'order_id' => $payment->order_id,
        ];
    }

    /**
     * Handle failed payment webhook.
     */
    private function handlePaymentFailed(array $data): array
    {
        $paymentIntentId = $data['id'] ?? null;

        if (!$paymentIntentId) {
            return ['success' => false, 'message' => 'Missing payment intent ID'];
        }

        $payment = Payment::where('transaction_id', $paymentIntentId)->first();

        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }

        $payment->update([
            'status' => Payment::STATUS_FAILED,
            'gateway_response' => array_merge(
                $payment->gateway_response ?? [],
                [
                    'failure_message' => $data['last_payment_error']['message'] ?? 'Payment failed',
                    'failed_at' => now()->toIso8601String(),
                ]
            ),
        ]);

        Log::warning('Stripe payment failed via webhook', [
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
        ]);

        return [
            'success' => true,
            'event_type' => 'payment_failed',
            'order_id' => $payment->order_id,
        ];
    }

    /**
     * Handle refund webhook.
     */
    private function handleRefund(array $data): array
    {
        // For charge.refunded, the payment_intent is in the data
        $paymentIntentId = $data['payment_intent'] ?? null;

        if (!$paymentIntentId) {
            return ['success' => false, 'message' => 'Missing payment intent ID'];
        }

        $payment = Payment::where('transaction_id', $paymentIntentId)->first();

        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }

        $payment->update([
            'status' => Payment::STATUS_REFUNDED,
            'gateway_response' => array_merge(
                $payment->gateway_response ?? [],
                [
                    'refunded_at' => now()->toIso8601String(),
                    'refund_amount' => ($data['amount_refunded'] ?? 0) / 100,
                ]
            ),
        ]);

        Log::info('Stripe payment refunded via webhook', [
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
        ]);

        return [
            'success' => true,
            'event_type' => 'refund',
            'order_id' => $payment->order_id,
        ];
    }

    /**
     * Mock payment intent for development.
     */
    private function mockPaymentIntent(Order $order): array
    {
        $mockPaymentIntentId = 'pi_mock_' . uniqid();
        $mockClientSecret = $mockPaymentIntentId . '_secret_' . uniqid();

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => $this->getGatewayName(),
            'transaction_id' => $mockPaymentIntentId,
            'status' => Payment::STATUS_PENDING,
            'amount' => $order->total,
            'currency' => $order->currency,
            'gateway_response' => [
                'payment_intent_id' => $mockPaymentIntentId,
                'client_secret' => $mockClientSecret,
                'mock' => true,
            ],
        ]);

        return [
            'success' => true,
            'payment_id' => $payment->id,
            'client_secret' => $mockClientSecret,
            'payment_intent_id' => $mockPaymentIntentId,
            'publishable_key' => config('services.stripe.key', 'pk_test_mock'),
            'mock' => true,
        ];
    }

    /**
     * Mock payment confirmation for development.
     */
    private function mockConfirmPayment(Payment $payment): array
    {
        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
            'gateway_response' => array_merge(
                $payment->gateway_response ?? [],
                [
                    'confirmed_at' => now()->toIso8601String(),
                    'mock' => true,
                ]
            ),
        ]);

        $payment->order->update(['status' => Order::STATUS_CONFIRMED]);

        return [
            'success' => true,
            'message' => 'Payment confirmed successfully.',
            'mock' => true,
        ];
    }

    /**
     * Mock refund for development.
     */
    private function mockRefund(Payment $payment, ?float $amount): array
    {
        $payment->update([
            'status' => Payment::STATUS_REFUNDED,
            'gateway_response' => array_merge(
                $payment->gateway_response ?? [],
                [
                    'refund_id' => 're_mock_' . uniqid(),
                    'refunded_at' => now()->toIso8601String(),
                    'mock' => true,
                ]
            ),
        ]);

        return [
            'success' => true,
            'refund_id' => 're_mock_' . uniqid(),
            'message' => 'Refund processed successfully.',
            'mock' => true,
        ];
    }
}
