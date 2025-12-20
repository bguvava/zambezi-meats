<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Payment Resource
 *
 * @requirement CHK-009 to CHK-012 Payment processing
 */
class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'gateway' => $this->gateway,
            'gateway_label' => $this->getGatewayLabel(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'amount' => (float) $this->amount,
            'amount_formatted' => '$' . number_format((float) $this->amount, 2),
            'currency' => $this->currency,
            'transaction_id' => $this->transaction_id,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    /**
     * Get human-readable gateway label.
     */
    private function getGatewayLabel(): string
    {
        return match ($this->gateway) {
            'stripe' => 'Credit/Debit Card',
            'paypal' => 'PayPal',
            'afterpay' => 'Afterpay',
            'cod' => 'Cash on Delivery',
            default => ucfirst($this->gateway),
        };
    }

    /**
     * Get human-readable status label.
     */
    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            default => ucfirst($this->status),
        };
    }
}
