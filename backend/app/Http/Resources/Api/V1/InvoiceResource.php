<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Invoice Resource.
 *
 * @mixin \App\Models\Invoice
 */
class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'order_id' => $this->order_id,
            'order_number' => $this->whenLoaded('order', fn() => $this->order->order_number),
            'subtotal' => (float) $this->subtotal,
            'delivery_fee' => (float) $this->delivery_fee,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'currency' => $this->currency,
            'status' => $this->status,
            'issue_date' => $this->issue_date->format('Y-m-d'),
            'due_date' => $this->due_date->format('Y-m-d'),
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'is_paid' => $this->isPaid(),
            'is_overdue' => $this->isOverdue(),
            'notes' => $this->notes,
            'customer' => $this->whenLoaded('order', function () {
                return $this->whenLoaded('user', function () {
                    return [
                        'id' => $this->order->user->id,
                        'name' => $this->order->user->name,
                        'email' => $this->order->user->email,
                    ];
                }, fn() => null);
            }),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
