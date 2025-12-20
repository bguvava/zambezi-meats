<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wishlist Resource
 *
 * Transforms Wishlist model for API responses.
 *
 * @property-read \App\Models\Wishlist $resource
 * @requirement CUST-012 Create wishlist page
 */
class WishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'product_id' => $this->resource->product_id,
            'product' => $this->when(
                $this->resource->relationLoaded('product') && $this->resource->product,
                fn() => new ProductResource($this->resource->product)
            ),
            'added_at' => $this->resource->created_at->toIso8601String(),
        ];
    }
}
