<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Delivery Zone Resource
 *
 * @requirement CHK-006 to CHK-007 Delivery validation and fee calculation
 */
class DeliveryZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $deliveryFee = (float) $this->delivery_fee;
        $freeThreshold = $this->free_delivery_threshold !== null
            ? (float) $this->free_delivery_threshold
            : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'delivery_fee' => $deliveryFee,
            'delivery_fee_formatted' => '$' . number_format($deliveryFee, 2),
            'free_delivery_threshold' => $freeThreshold,
            'free_delivery_threshold_formatted' => $freeThreshold !== null
                ? '$' . number_format($freeThreshold, 2)
                : null,
            'estimated_days' => $this->estimated_days,
            'estimated_delivery' => $this->getEstimatedDeliveryText(),
        ];
    }

    /**
     * Get estimated delivery text.
     */
    private function getEstimatedDeliveryText(): string
    {
        $days = $this->estimated_days;

        if ($days === 0) {
            return 'Same day delivery';
        } elseif ($days === 1) {
            return 'Next day delivery';
        } else {
            return "{$days} business days";
        }
    }
}
