<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Afterpay Payment Service
 *
 * Handles Afterpay payment processing.
 *
 * @requirement CHK-011 Integrate Afterpay payment
 */
class AfterpayPaymentService implements PaymentServiceInterface
{
    private const GATEWAY_AFTERPAY = 'afterpay';

    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.afterpay.sandbox')
            ? 'https://global-api-sandbox.afterpay.com'
            : 'https://global-api.afterpay.com';
    }

    /**
     * Get the payment gateway name.
     */
    public function getGatewayName(): string
    {
        return self::GATEWAY_AFTERPAY;
    }

    /**
     * Check if the gateway is enabled.
     */
    public function isEnabled(): bool
    {
        return !empty(config('services.afterpay.merchant_id'));
    }

    /**
     * Calculate installment amounts for display.
     *
     * @param float $total
     * @return array
     */
    public static function calculateInstallments(float $total): array
    {
        $installment = round($total / 4, 2);

        return [
            'total' => $total,
            'installment_count' => 4,
            'installment_amount' => $installment,
            'installment_formatted' => '$' . number_format((float) $installment, 2),
            'frequency' => 'fortnightly',
        ];
    }

    /**
     * Initiate an Afterpay checkout.
     */
    public function initiatePayment(Order $order, array $paymentData = []): array
    {
        try {
            // Afterpay only supports AUD
            if ($order->currency !== 'AUD') {
                return [
                    'success' => false,
                    'message' => 'Afterpay is only available for AUD payments.',
                ];
            }

            // Afterpay has min/max limits
            $minAmount = 35.00;
            $maxAmount = 2000.00;

            if ($order->total < $minAmount || $order->total > $maxAmount) {
                return [
                    'success' => false,
                    'message' => sprintf(
                        'Afterpay is available for orders between $%.2f and $%.2f.',
                        $minAmount,
                        $maxAmount
                    ),
                ];
            }

            if (!$this->isEnabled()) {
                return $this->mockAfterpayCheckout($order);
            }

            $returnUrl = $paymentData['return_url'] ?? config('app.frontend_url') . '/checkout/confirm';
            $cancelUrl = $paymentData['cancel_url'] ?? config('app.frontend_url') . '/checkout/payment';

            // Build order items
            $items = [];
            foreach ($order->items as $item) {
                $items[] = [
                    'name' => $item->product_name,
                    'sku' => $item->product_sku ?? 'N/A',
                    'quantity' => $item->quantity,
                    'price' => [
                        'amount' => number_format((float) $item->unit_price, 2, '.', ''),
                        'currency' => 'AUD',
                    ],
                ];
            }

            $response = Http::withBasicAuth(
                config('services.afterpay.merchant_id'),
                config('services.afterpay.secret_key')
            )->post("{$this->baseUrl}/v2/checkouts", [
                'amount' => [
                    'amount' => number_format((float) $order->total, 2, '.', ''),
                    'currency' => 'AUD',
                ],
                'consumer' => [
                    'email' => $order->user->email,
                    'givenNames' => explode(' ', $order->user->name)[0] ?? 'Customer',
                    'surname' => explode(' ', $order->user->name)[1] ?? 'Customer',
                ],
                'merchant' => [
                    'redirectConfirmUrl' => $returnUrl . '?gateway=afterpay',
                    'redirectCancelUrl' => $cancelUrl,
                ],
                'merchantReference' => $order->order_number,
                'items' => $items,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Afterpay checkout creation failed: ' . $response->body());
            }

            $checkoutData = $response->json();

            $payment = Payment::create([
                'order_id' => $order->id,
                'gateway' => $this->getGatewayName(),
                'transaction_id' => $checkoutData['token'],
                'status' => Payment::STATUS_PENDING,
                'amount' => $order->total,
                'currency' => 'AUD',
                'gateway_response' => [
                    'checkout_token' => $checkoutData['token'],
                    'expires' => $checkoutData['expires'] ?? null,
                ],
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'checkout_token' => $checkoutData['token'],
                'redirect_url' => $checkoutData['redirectCheckoutUrl'],
                'installments' => self::calculateInstallments((float) $order->total),
            ];
        } catch (\Exception $e) {
            Log::error('Afterpay payment initiation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to initiate Afterpay payment. Please try again.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Confirm/capture an Afterpay payment.
     */
    public function confirmPayment(Payment $payment, array $data = []): array
    {
        try {
            if (!$this->isEnabled()) {
                return $this->mockConfirmPayment($payment);
            }

            $token = $data['token'] ?? $payment->transaction_id;

            $response = Http::withBasicAuth(
                config('services.afterpay.merchant_id'),
                config('services.afterpay.secret_key')
            )->post("{$this->baseUrl}/v2/payments/capture", [
                'token' => $token,
                'merchantReference' => $payment->order->order_number,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Afterpay capture failed: ' . $response->body());
            }

            $captureData = $response->json();

            if ($captureData['status'] === 'APPROVED') {
                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'transaction_id' => $captureData['id'],
                    'gateway_response' => array_merge(
                        $payment->gateway_response ?? [],
                        [
                            'afterpay_order_id' => $captureData['id'],
                            'confirmed_at' => now()->toIso8601String(),
                        ]
                    ),
                ]);

                $payment->order->update(['status' => Order::STATUS_CONFIRMED]);

                return [
                    'success' => true,
                    'message' => 'Afterpay payment confirmed successfully.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Afterpay payment was not approved.',
                'status' => $captureData['status'],
            ];
        } catch (\Exception $e) {
            Log::error('Afterpay payment confirmation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to confirm Afterpay payment.',
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
            if (!$this->isEnabled()) {
                return $this->mockRefund($payment, $amount);
            }

            $afterpayOrderId = $payment->gateway_response['afterpay_order_id'] ?? $payment->transaction_id;
            $refundAmount = $amount ?? $payment->amount;

            $response = Http::withBasicAuth(
                config('services.afterpay.merchant_id'),
                config('services.afterpay.secret_key')
            )->post("{$this->baseUrl}/v2/payments/{$afterpayOrderId}/refund", [
                'amount' => [
                    'amount' => number_format((float) $refundAmount, 2, '.', ''),
                    'currency' => 'AUD',
                ],
                'merchantReference' => $payment->order->order_number . '-REFUND',
            ]);

            if (!$response->successful()) {
                throw new \Exception('Afterpay refund failed: ' . $response->body());
            }

            $refundData = $response->json();

            $payment->update([
                'status' => Payment::STATUS_REFUNDED,
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    [
                        'refund_id' => $refundData['refundId'] ?? null,
                        'refunded_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

            return [
                'success' => true,
                'refund_id' => $refundData['refundId'] ?? null,
                'message' => 'Afterpay refund processed successfully.',
            ];
        } catch (\Exception $e) {
            Log::error('Afterpay refund failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process Afterpay refund.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle Afterpay webhook (not commonly used).
     */
    public function handleWebhook(array $payload, string $signature): array
    {
        // Afterpay typically uses redirect-based confirmation
        // rather than webhooks, but we support it for future use
        return [
            'success' => true,
            'message' => 'Webhook received.',
        ];
    }

    /**
     * Mock Afterpay checkout for development.
     */
    private function mockAfterpayCheckout(Order $order): array
    {
        $mockToken = 'AP_MOCK_' . strtoupper(uniqid());

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => $this->getGatewayName(),
            'transaction_id' => $mockToken,
            'status' => Payment::STATUS_PENDING,
            'amount' => $order->total,
            'currency' => 'AUD',
            'gateway_response' => [
                'checkout_token' => $mockToken,
                'mock' => true,
            ],
        ]);

        return [
            'success' => true,
            'payment_id' => $payment->id,
            'checkout_token' => $mockToken,
            'redirect_url' => config('app.frontend_url') . '/checkout/confirm?mock_afterpay=1&order=' . $order->order_number,
            'installments' => self::calculateInstallments((float) $order->total),
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
                    'afterpay_order_id' => 'AP_ORD_MOCK_' . uniqid(),
                    'confirmed_at' => now()->toIso8601String(),
                    'mock' => true,
                ]
            ),
        ]);

        $payment->order->update(['status' => Order::STATUS_CONFIRMED]);

        return [
            'success' => true,
            'message' => 'Afterpay payment confirmed successfully.',
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
                    'refund_id' => 'AP_REF_MOCK_' . uniqid(),
                    'refunded_at' => now()->toIso8601String(),
                    'mock' => true,
                ]
            ),
        ]);

        return [
            'success' => true,
            'refund_id' => 'AP_REF_MOCK_' . uniqid(),
            'message' => 'Afterpay refund processed successfully.',
            'mock' => true,
        ];
    }
}
