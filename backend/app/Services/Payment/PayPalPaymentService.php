<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayPal Payment Service
 *
 * Handles PayPal payment processing using PayPal REST API.
 *
 * @requirement CHK-010 Integrate PayPal payment
 * @requirement SET-028 Use settings from database
 */
class PayPalPaymentService implements PaymentServiceInterface
{
    private string $baseUrl;
    private ?string $accessToken = null;
    private SettingsService $settings;
    private InvoiceService $invoiceService;

    public function __construct(?SettingsService $settings = null, ?InvoiceService $invoiceService = null)
    {
        $this->settings = $settings ?? app(SettingsService::class);
        $this->invoiceService = $invoiceService ?? app(InvoiceService::class);

        $isSandbox = $this->settings->getPayPalMode() === 'sandbox';
        $this->baseUrl = $isSandbox
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    /**
     * Get the payment gateway name.
     */
    public function getGatewayName(): string
    {
        return Payment::GATEWAY_PAYPAL;
    }

    /**
     * Check if the gateway is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->settings->isPayPalEnabled() && !empty($this->settings->getPayPalClientId());
    }

    /**
     * Initiate a PayPal payment (create Order).
     */
    public function initiatePayment(Order $order, array $paymentData = []): array
    {
        try {
            if (!$this->isEnabled()) {
                return $this->mockPayPalOrder($order);
            }

            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return $this->mockPayPalOrder($order);
            }

            $returnUrl = $paymentData['return_url'] ?? config('app.frontend_url') . '/checkout/confirm';
            $cancelUrl = $paymentData['cancel_url'] ?? config('app.frontend_url') . '/checkout/payment';

            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'reference_id' => $order->order_number,
                            'amount' => [
                                'currency_code' => $order->currency,
                                'value' => number_format((float) $order->total, 2, '.', ''),
                            ],
                            'description' => "Zambezi Meats Order {$order->order_number}",
                        ],
                    ],
                    'application_context' => [
                        'brand_name' => 'Zambezi Meats',
                        'shipping_preference' => 'NO_SHIPPING',
                        'user_action' => 'PAY_NOW',
                        'return_url' => $returnUrl,
                        'cancel_url' => $cancelUrl,
                    ],
                ]);

            if (!$response->successful()) {
                throw new \Exception('PayPal order creation failed: ' . $response->body());
            }

            $paypalOrder = $response->json();
            $approveUrl = collect($paypalOrder['links'])->firstWhere('rel', 'approve')['href'] ?? null;

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'gateway' => $this->getGatewayName(),
                'transaction_id' => $paypalOrder['id'],
                'status' => Payment::STATUS_PENDING,
                'amount' => $order->total,
                'currency' => $order->currency,
                'gateway_response' => [
                    'paypal_order_id' => $paypalOrder['id'],
                    'status' => $paypalOrder['status'],
                ],
            ]);

            // Generate invoice
            $invoice = $this->invoiceService->generateFromOrder($order);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'paypal_order_id' => $paypalOrder['id'],
                'approve_url' => $approveUrl,
            ];
        } catch (\Exception $e) {
            Log::error('PayPal payment initiation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to initiate PayPal payment. Please try again.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Confirm/capture a PayPal payment.
     */
    public function confirmPayment(Payment $payment, array $data = []): array
    {
        try {
            if (!$this->isEnabled()) {
                return $this->mockConfirmPayment($payment);
            }

            $accessToken = $this->getAccessToken();
            $paypalOrderId = $data['paypal_order_id'] ?? $payment->transaction_id;

            // Capture the order
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture");

            if (!$response->successful()) {
                throw new \Exception('PayPal capture failed: ' . $response->body());
            }

            $captureData = $response->json();

            if ($captureData['status'] === 'COMPLETED') {
                $captureId = $captureData['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

                $payment->update([
                    'status' => Payment::STATUS_COMPLETED,
                    'gateway_response' => array_merge(
                        $payment->gateway_response ?? [],
                        [
                            'capture_id' => $captureId,
                            'confirmed_at' => now()->toIso8601String(),
                        ]
                    ),
                ]);

                $payment->order->update(['status' => Order::STATUS_CONFIRMED]);

                // Mark invoice as paid
                if ($payment->order->invoice) {
                    $this->invoiceService->markAsPaid($payment->order->invoice);
                }

                return [
                    'success' => true,
                    'message' => 'Payment confirmed successfully.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Payment capture not completed.',
                'status' => $captureData['status'],
            ];
        } catch (\Exception $e) {
            Log::error('PayPal payment confirmation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to confirm PayPal payment.',
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

            $accessToken = $this->getAccessToken();
            $captureId = $payment->gateway_response['capture_id'] ?? null;

            if (!$captureId) {
                return [
                    'success' => false,
                    'message' => 'No capture ID found for refund.',
                ];
            }

            $refundData = [];
            if ($amount !== null) {
                $refundData['amount'] = [
                    'currency_code' => $payment->currency,
                    'value' => number_format((float) $amount, 2, '.', ''),
                ];
            }

            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/v2/payments/captures/{$captureId}/refund", $refundData);

            if (!$response->successful()) {
                throw new \Exception('PayPal refund failed: ' . $response->body());
            }

            $refundResult = $response->json();

            $payment->update([
                'status' => Payment::STATUS_REFUNDED,
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    [
                        'refund_id' => $refundResult['id'],
                        'refunded_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

            return [
                'success' => true,
                'refund_id' => $refundResult['id'],
                'message' => 'Refund processed successfully.',
            ];
        } catch (\Exception $e) {
            Log::error('PayPal refund failed', [
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
     * Handle PayPal webhook.
     */
    public function handleWebhook(array $payload, string $signature): array
    {
        try {
            $eventType = $payload['event_type'] ?? '';
            $resource = $payload['resource'] ?? [];

            switch ($eventType) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    return $this->handleCaptureCompleted($resource);

                case 'PAYMENT.CAPTURE.DENIED':
                case 'PAYMENT.CAPTURE.REFUNDED':
                    return $this->handleCaptureStatusChange($resource, $eventType);

                default:
                    return [
                        'success' => true,
                        'message' => "Unhandled event type: {$eventType}",
                    ];
            }
        } catch (\Exception $e) {
            Log::error('PayPal webhook handling failed', [
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
     * Get PayPal access token.
     */
    private function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        try {
            $clientId = $this->settings->getPayPalClientId();
            $clientSecret = $this->settings->getPayPalSecret();

            $response = Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->successful()) {
                $this->accessToken = $response->json()['access_token'];
                return $this->accessToken;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get PayPal access token', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Handle capture completed webhook.
     */
    private function handleCaptureCompleted(array $resource): array
    {
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        if (!$orderId) {
            return ['success' => false, 'message' => 'Missing order ID'];
        }

        $payment = Payment::where('transaction_id', $orderId)->first();

        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }

        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
            'gateway_response' => array_merge(
                $payment->gateway_response ?? [],
                [
                    'capture_id' => $resource['id'],
                    'confirmed_at' => now()->toIso8601String(),
                ]
            ),
        ]);

        $payment->order->update(['status' => Order::STATUS_CONFIRMED]);

        return [
            'success' => true,
            'event_type' => 'payment_success',
            'order_id' => $payment->order_id,
        ];
    }

    /**
     * Handle capture status change webhook.
     */
    private function handleCaptureStatusChange(array $resource, string $eventType): array
    {
        $captureId = $resource['id'] ?? null;
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        // First try to find by capture_id in gateway_response
        $payment = Payment::whereJsonContains('gateway_response->capture_id', $captureId)->first();

        // Fallback: try to find by transaction_id (PayPal order ID)
        if (!$payment && $orderId) {
            $payment = Payment::where('transaction_id', $orderId)->first();
        }

        if (!$payment) {
            return ['success' => false, 'message' => 'Payment not found'];
        }

        $newStatus = match ($eventType) {
            'PAYMENT.CAPTURE.DENIED' => Payment::STATUS_FAILED,
            'PAYMENT.CAPTURE.REFUNDED' => Payment::STATUS_REFUNDED,
            default => $payment->status,
        };

        $payment->update(['status' => $newStatus]);

        return [
            'success' => true,
            'event_type' => $eventType,
            'order_id' => $payment->order_id,
        ];
    }

    /**
     * Mock PayPal order for development.
     */
    private function mockPayPalOrder(Order $order): array
    {
        $mockOrderId = 'PP_MOCK_' . strtoupper(uniqid());

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => $this->getGatewayName(),
            'transaction_id' => $mockOrderId,
            'status' => Payment::STATUS_PENDING,
            'amount' => $order->total,
            'currency' => $order->currency,
            'gateway_response' => [
                'paypal_order_id' => $mockOrderId,
                'status' => 'CREATED',
                'mock' => true,
            ],
        ]);

        return [
            'success' => true,
            'payment_id' => $payment->id,
            'paypal_order_id' => $mockOrderId,
            'approval_url' => config('app.frontend_url') . '/checkout/confirm?mock_paypal=1&order=' . $order->order_number,
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
                    'capture_id' => 'CAP_MOCK_' . uniqid(),
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
                    'refund_id' => 'REF_MOCK_' . uniqid(),
                    'refunded_at' => now()->toIso8601String(),
                    'mock' => true,
                ]
            ),
        ]);

        return [
            'success' => true,
            'refund_id' => 'REF_MOCK_' . uniqid(),
            'message' => 'Refund processed successfully.',
            'mock' => true,
        ];
    }
}
