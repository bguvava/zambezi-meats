<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Product Resource
 *
 * Transforms product model data for API responses.
 *
 * @requirement SHOP-022 Product data serialization
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $price = (float) $this->price_aud;
        $salePrice = $this->sale_price_aud ? (float) $this->sale_price_aud : null;
        $currentPrice = $salePrice ?? $price;
        $stock = (int) ($this->stock ?? 0);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $currentPrice,
            'price_formatted' => '$' . number_format($currentPrice, 2),
            'original_price' => $salePrice ? $price : null,
            'original_price_formatted' => $salePrice ? '$' . number_format($price, 2) : null,
            'discount_percentage' => $salePrice
                ? round((1 - $salePrice / $price) * 100)
                : null,
            'is_on_sale' => $salePrice !== null && $salePrice < $price,
            'stock' => $stock,
            'in_stock' => $stock > 0,
            'unit' => $this->unit ?? 'kg',
            'min_order_quantity' => 0.5,
            'max_order_quantity' => 50.0,
            'is_featured' => (bool) $this->is_featured,
            'is_active' => (bool) $this->is_active,
            'sku' => $this->sku,
            'weight_kg' => $this->weight_kg ? (float) $this->weight_kg : null,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'primary_image' => $this->whenLoaded('images', function () {
                $primary = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
                return $primary ? new ProductImageResource($primary) : null;
            }),
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
