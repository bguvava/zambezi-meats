<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Cart Item Resource
 *
 * Transforms cart item data for API responses.
 *
 * @requirement CART-019 Cart item serialization
 */
class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lineTotal = $this->quantity * $this->unit_price;
        $product = $this->whenLoaded('product');

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($product),
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'unit_price_formatted' => '$' . number_format((float) $this->unit_price, 2),
            'line_total' => (float) $lineTotal,
            'line_total_formatted' => '$' . number_format($lineTotal, 2),
            'price_changed' => $product && $product->price != $this->unit_price,
            'in_stock' => $product && $product->stock_quantity >= $this->quantity,
            'available_stock' => $product ? (float) $product->stock_quantity : 0,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
