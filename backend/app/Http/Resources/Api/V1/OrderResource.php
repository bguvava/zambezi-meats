<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Order Resource
 *
 * @requirement CHK-025 Order creation
 */
class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),

            // Amounts
            'subtotal' => (float) $this->subtotal,
            'subtotal_formatted' => '$' . number_format((float) $this->subtotal, 2),
            'delivery_fee' => (float) $this->delivery_fee,
            'delivery_fee_formatted' => (float) $this->delivery_fee > 0
                ? '$' . number_format((float) $this->delivery_fee, 2)
                : 'FREE',
            'discount' => (float) $this->discount,
            'discount_formatted' => (float) $this->discount > 0
                ? '-$' . number_format((float) $this->discount, 2)
                : null,
            'total' => (float) $this->total,
            'total_formatted' => '$' . number_format((float) $this->total, 2),
            'currency' => $this->currency,

            // Promo
            'promotion_code' => $this->promotion_code,

            // Notes
            'notes' => $this->notes,
            'delivery_instructions' => $this->delivery_instructions,

            // Scheduling
            'scheduled_date' => $this->scheduled_date?->format('Y-m-d'),
            'scheduled_time_slot' => $this->scheduled_time_slot,

            // Relationships
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'delivery_zone' => new DeliveryZoneResource($this->whenLoaded('deliveryZone')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }

    /**
     * Get human-readable status label.
     */
    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'ready' => 'Ready for Delivery',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
