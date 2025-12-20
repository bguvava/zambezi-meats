<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;

/**
 * Payment Service Interface
 *
 * Defines the contract for all payment gateway implementations.
 */
interface PaymentServiceInterface
{
    /**
     * Get the payment gateway name.
     */
    public function getGatewayName(): string;

    /**
     * Check if the gateway is enabled.
     */
    public function isEnabled(): bool;

    /**
     * Initiate a payment for an order.
     *
     * @param Order $order
     * @param array $paymentData Additional payment data from the request
     * @return array Payment result with 'success', 'payment_id', 'client_secret' etc.
     */
    public function initiatePayment(Order $order, array $paymentData = []): array;

    /**
     * Confirm/complete a payment.
     *
     * @param Payment $payment
     * @param array $data Confirmation data from gateway
     * @return array Result with 'success' and 'message'
     */
    public function confirmPayment(Payment $payment, array $data = []): array;

    /**
     * Process a refund.
     *
     * @param Payment $payment
     * @param float|null $amount Amount to refund (null for full refund)
     * @return array Result with 'success' and 'refund_id'
     */
    public function refund(Payment $payment, ?float $amount = null): array;

    /**
     * Handle a webhook from the payment gateway.
     *
     * @param array $payload Webhook payload
     * @param string $signature Webhook signature for verification
     * @return array Result with 'success', 'event_type', 'order_id' etc.
     */
    public function handleWebhook(array $payload, string $signature): array;
}
