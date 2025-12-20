<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User Resource
 *
 * Transforms User model for API responses.
 *
 * @property-read \App\Models\User $resource
 */
class UserResource extends JsonResource
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
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'role' => $this->resource->role,
            'status' => $this->resource->status ?? 'active',
            'is_active' => $this->resource->is_active ?? true,
            'currency_preference' => $this->resource->currency_preference ?? 'AUD',
            'is_admin' => $this->resource->isAdmin(),
            'is_staff' => $this->resource->isStaff(),
            'is_customer' => $this->resource->isCustomer(),
            'email_verified_at' => $this->resource->email_verified_at?->toIso8601String(),
            'last_login_at' => $this->resource->last_login_at?->toIso8601String(),
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'default_address' => $this->whenLoaded('addresses', function () {
                $defaultAddress = $this->resource->addresses->where('is_default', true)->first();
                return $defaultAddress ? new AddressResource($defaultAddress) : null;
            }),
        ];
    }
}
