<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Support Ticket Resource
 *
 * Transforms SupportTicket model for API responses.
 *
 * @property-read \App\Models\SupportTicket $resource
 * @requirement CUST-017 Create support ticket submission
 * @requirement CUST-018 View support ticket history
 */
class SupportTicketResource extends JsonResource
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
            'subject' => $this->resource->subject,
            'message' => $this->resource->message,
            'status' => $this->resource->status,
            'priority' => $this->resource->priority,
            'order_id' => $this->resource->order_id,
            'order' => $this->when(
                $this->resource->relationLoaded('order') && $this->resource->order,
                fn() => [
                    'id' => $this->resource->order->id,
                    'order_number' => $this->resource->order->order_number,
                ]
            ),
            'replies' => $this->when(
                $this->resource->relationLoaded('replies'),
                fn() => $this->resource->replies->map(fn($reply) => [
                    'id' => $reply->id,
                    'message' => $reply->message,
                    'is_staff_reply' => $reply->isFromStaff(),
                    'user' => [
                        'id' => $reply->user->id,
                        'name' => $reply->user->name,
                    ],
                    'created_at' => $reply->created_at->toIso8601String(),
                ])
            ),
            'reply_count' => $this->when(
                $this->resource->relationLoaded('replies'),
                fn() => $this->resource->replies->count()
            ),
            'created_at' => $this->resource->created_at->toIso8601String(),
            'updated_at' => $this->resource->updated_at->toIso8601String(),
        ];
    }
}
