<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Resources\Api\V1\CategoryCollection;
use App\Http\Resources\Api\V1\ProductCollection;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Category Controller
 *
 * Handles category listing and category-based product filtering.
 *
 * @requirement SHOP-023 Create categories API endpoint
 */
class CategoryController extends Controller
{
    /**
     * Display a list of all active categories with product counts.
     *
     * @requirement SHOP-023 GET /api/v1/categories returns all active categories
     * @requirement ISSUE-008 Filter to show only main categories
     *
     * @param Request $request
     * @return CategoryCollection
     */
    public function index(Request $request): CategoryCollection
    {
        $query = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }]);

        // Filter to main categories only (no parent)
        if ($request->boolean('main_only', false)) {
            $query->whereNull('parent_id');
        }

        $categories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return new CategoryCollection($categories);
    }

    /**
     * Display a single category with its products.
     *
     * @param string $slug
     * @param Request $request
     * @return CategoryResource|JsonResponse
     */
    public function show(string $slug, Request $request): CategoryResource|JsonResponse
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->withCount(['products' => function ($query) {
                $query->where('is_active', true);
            }])
            ->first();

        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        return new CategoryResource($category);
    }

    /**
     * Get products for a specific category.
     *
     * @param string $slug
     * @param Request $request
     * @return ProductCollection|JsonResponse
     */
    public function products(string $slug, Request $request): ProductCollection|JsonResponse
    {
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        $query = $category->products()
            ->with(['images'])
            ->where('is_active', true);

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('price_aud', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_aud', '<=', (float) $request->max_price);
        }

        // In stock filter
        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        // Map API sort fields to database columns
        $sortMap = ['price' => 'price_aud', 'stock_quantity' => 'stock'];
        $sortField = $sortMap[$sortField] ?? $sortField;

        $allowedSorts = ['price_aud', 'name', 'created_at', 'stock'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = min((int) $request->get('per_page', 12), 48);

        return new ProductCollection($query->paginate($perPage));
    }
}
