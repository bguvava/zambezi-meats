<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Product Image Resource
 *
 * Transforms product image data for API responses.
 *
 * @requirement SHOP-022 Product image serialization
 */
class ProductImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url ?? $this->url,
            'alt_text' => $this->alt_text ?? '',
            'is_primary' => (bool) $this->is_primary,
            'sort_order' => $this->sort_order ?? 0,
        ];
    }
}
