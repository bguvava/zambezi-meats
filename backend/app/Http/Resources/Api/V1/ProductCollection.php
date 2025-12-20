<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Product Collection
 *
 * Wraps a collection of products with pagination metadata.
 *
 * @requirement SHOP-022 Product collection serialization
 */
class ProductCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ProductResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'filters' => [
                    'category' => $request->query('category'),
                    'min_price' => $request->query('min_price'),
                    'max_price' => $request->query('max_price'),
                    'in_stock' => $request->query('in_stock'),
                    'search' => $request->query('search'),
                ],
                'sort' => [
                    'field' => $request->query('sort', 'created_at'),
                    'direction' => $request->query('direction', 'desc'),
                ],
            ],
        ];
    }
}
