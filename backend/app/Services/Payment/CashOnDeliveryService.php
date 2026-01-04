<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Log;

/**
 * Cash on Delivery Payment Service
 *
 * Handles Cash on Delivery (COD) payment processing.
 *
 * @requirement CHK-012 Implement Cash on Delivery
 * @requirement SET-028 Use settings from database
 */
class CashOnDeliveryService implements PaymentServiceInterface
{
    private const GATEWAY_COD = 'cod';
    private SettingsService $settings;
    private InvoiceService $invoiceService;

    public function __construct(?SettingsService $settings = null, ?InvoiceService $invoiceService = null)
    {
        $this->settings = $settings ?? app(SettingsService::class);
        $this->invoiceService = $invoiceService ?? app(InvoiceService::class);
    }

    /**
     * Get the payment gateway name.
     */
    public function getGatewayName(): string
    {
        return self::GATEWAY_COD;
    }

    /**
     * Check if the gateway is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->settings->isCodEnabled();
    }

    /**
     * Check if COD is available for an order.
     *
     * @param Order $order
     * @return array
     */
    public function isAvailable(Order $order): array
    {
        // COD only available for AUD orders
        if ($order->currency !== 'AUD') {
            return [
                'available' => false,
                'message' => 'Cash on Delivery is only available for Australian orders.',
            ];
        }

        // COD might have a maximum order limit
        $maxAmount = config('services.cod.max_amount', 500.00);
        if ($order->total > $maxAmount) {
            return [
                'available' => false,
                'message' => sprintf(
                    'Cash on Delivery is available for orders up to $%.2f.',
                    $maxAmount
                ),
            ];
        }

        // Check if delivery zone supports COD
        if ($order->deliveryZone && !$this->zoneSupportsCoD($order->deliveryZone)) {
            return [
                'available' => false,
                'message' => 'Cash on Delivery is not available in your delivery area.',
            ];
        }

        return [
            'available' => true,
            'message' => 'Pay with cash when your order arrives.',
        ];
    }

    /**
     * Initiate a COD payment (just creates the payment record).
     */
    public function initiatePayment(Order $order, array $paymentData = []): array
    {
        try {
            $availability = $this->isAvailable($order);
            if (!$availability['available']) {
                return [
                    'success' => false,
                    'message' => $availability['message'],
                ];
            }

            // Create payment record (pending until delivery)
            $payment = Payment::create([
                'order_id' => $order->id,
                'gateway' => $this->getGatewayName(),
                'transaction_id' => 'COD_' . $order->order_number,
                'status' => Payment::STATUS_PENDING,
                'amount' => $order->total,
                'currency' => $order->currency,
                'gateway_response' => [
                    'type' => 'cash_on_delivery',
                    'collect_on_delivery' => true,
                ],
            ]);

            // Update order status to confirmed (payment pending until delivery)
            $order->update(['status' => Order::STATUS_CONFIRMED]);

            // Generate invoice
            $invoice = $this->invoiceService->generateFromOrder($order);

            Log::info('COD payment initiated', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'message' => 'Your order has been placed. Please have $' . number_format((float) $order->total, 2) . ' ready upon delivery.',
                'collect_amount' => (float) $order->total,
                'collect_amount_formatted' => '$' . number_format((float) $order->total, 2),
            ];
        } catch (\Exception $e) {
            Log::error('COD payment initiation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process your order. Please try again.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Confirm COD payment (called when delivery person collects cash).
     */
    public function confirmPayment(Payment $payment, array $data = []): array
    {
        try {
            $collectedAmount = $data['collected_amount'] ?? $payment->amount;
            $collectorName = $data['collector_name'] ?? 'Delivery Driver';

            // Verify amount collected matches order total
            if ($collectedAmount < $payment->amount) {
                return [
                    'success' => false,
                    'message' => sprintf(
                        'Collected amount ($%.2f) is less than the order total ($%.2f).',
                        $collectedAmount,
                        $payment->amount
                    ),
                ];
            }

            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    [
                        'collected_amount' => $collectedAmount,
                        'collector_name' => $collectorName,
                        'collected_at' => now()->toIso8601String(),
                    ]
                ),
            ]);

            $payment->order->update(['status' => Order::STATUS_DELIVERED]);

            // Mark invoice as paid
            if ($payment->order->invoice) {
                $this->invoiceService->markAsPaid($payment->order->invoice);
            }

            Log::info('COD payment confirmed', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'collected_amount' => $collectedAmount,
            ]);

            return [
                'success' => true,
                'message' => 'Cash payment collected successfully.',
            ];
        } catch (\Exception $e) {
            Log::error('COD payment confirmation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to confirm cash collection.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process a refund for COD.
     */
    public function refund(Payment $payment, ?float $amount = null): array
    {
        // COD refunds are handled manually
        $refundAmount = $amount ?? $payment->amount;

        $payment->update([
            'status' => Payment::STATUS_REFUNDED,
            'gateway_response' => array_merge(
                $payment->gateway_response ?? [],
                [
                    'refund_amount' => $refundAmount,
                    'refund_note' => 'Manual cash refund required',
                    'refunded_at' => now()->toIso8601String(),
                ]
            ),
        ]);

        Log::info('COD refund recorded', [
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'refund_amount' => $refundAmount,
        ]);

        return [
            'success' => true,
            'message' => 'Refund recorded. Please issue a manual cash refund of $' . number_format((float) $refundAmount, 2),
            'refund_amount' => $refundAmount,
        ];
    }

    /**
     * Handle webhook (not applicable for COD).
     */
    public function handleWebhook(array $payload, string $signature): array
    {
        return [
            'success' => true,
            'message' => 'COD does not use webhooks.',
        ];
    }

    /**
     * Check if a delivery zone supports COD.
     */
    private function zoneSupportsCoD($zone): bool
    {
        // For now, all zones support COD
        // This could be extended to check zone settings
        return true;
    }
}
