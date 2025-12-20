<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User resource for API responses.
 *
 * @requirement USER-014 Create users API endpoints
 * @requirement USER-012 Create user profile component
 *
 * @property \App\Models\User $resource
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
            'avatar' => $this->resource->avatar,
            'role' => $this->resource->role,
            'status' => $this->resource->status,
            'is_active' => $this->resource->is_active,
            'currency_preference' => $this->resource->currency_preference,
            'email_verified_at' => $this->resource->email_verified_at?->toISOString(),
            'last_login_at' => $this->resource->last_login_at?->toISOString(),
            'created_at' => $this->resource->created_at->toISOString(),
            'updated_at' => $this->resource->updated_at->toISOString(),

            // Computed properties for frontend display
            'role_display' => ucfirst($this->resource->role),
            'status_display' => ucfirst($this->resource->status),
            'is_admin' => $this->resource->isAdmin(),
            'is_staff' => $this->resource->isStaff(),
            'is_customer' => $this->resource->isCustomer(),

            // Avatar URL or fallback
            'avatar_url' => $this->resource->avatar
                ? asset('storage/' . $this->resource->avatar)
                : null,
        ];
    }
}
