<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Cart Resource
 *
 * Transforms cart data for API responses.
 *
 * @requirement CART-019 Cart data serialization
 */
class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items', function () {
            return $this->items;
        }, collect());

        $subtotal = $items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $itemCount = $items->count();
        $totalQuantity = $items->sum('quantity');

        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($items),
            'item_count' => $itemCount,
            'total_quantity' => (float) $totalQuantity,
            'subtotal' => (float) $subtotal,
            'subtotal_formatted' => '$' . number_format($subtotal, 2),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
