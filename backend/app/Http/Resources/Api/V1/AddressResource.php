<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Address Resource
 *
 * Transforms Address model for API responses.
 *
 * @property-read \App\Models\Address $resource
 */
class AddressResource extends JsonResource
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
            'label' => $this->resource->label,
            'street' => $this->resource->street,
            'suburb' => $this->resource->suburb,
            'city' => $this->resource->city,
            'state' => $this->resource->state,
            'postcode' => $this->resource->postcode,
            'country' => $this->resource->country,
            'is_default' => $this->resource->is_default,
            'formatted' => $this->formatAddress(),
        ];
    }

    /**
     * Format the address as a single string.
     */
    private function formatAddress(): string
    {
        $parts = [
            $this->resource->street,
            $this->resource->suburb,
            $this->resource->city,
            $this->resource->state,
            $this->resource->postcode,
        ];

        return implode(', ', array_filter($parts));
    }
}
