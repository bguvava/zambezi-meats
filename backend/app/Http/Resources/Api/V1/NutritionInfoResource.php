<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Nutrition Info Resource
 *
 * Transforms nutrition information for API responses.
 *
 * @requirement SHOP-008 Display nutrition info
 */
class NutritionInfoResource extends JsonResource
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
            'serving_size' => $this->serving_size,
            'calories' => $this->calories,
            'protein' => $this->protein,
            'fat' => $this->fat,
            'saturated_fat' => $this->saturated_fat,
            'carbohydrates' => $this->carbohydrates,
            'fiber' => $this->fiber,
            'sodium' => $this->sodium,
            'cholesterol' => $this->cholesterol,
        ];
    }
}
