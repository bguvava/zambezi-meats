<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\ProductCollection;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Product Controller
 *
 * Handles product listing, search, filtering, and detail endpoints.
 *
 * @requirement SHOP-021 Create products API endpoint
 * @requirement SHOP-022 Create single product API endpoint
 * @requirement SHOP-024 Create featured products API endpoint
 */
class ProductController extends Controller
{
    /**
     * Display a paginated list of products with filtering and sorting.
     *
     * @requirement SHOP-021 GET /api/v1/products with filtering, sorting, pagination
     *
     * @param Request $request
     * @return ProductCollection
     */
    public function index(Request $request): ProductCollection
    {
        $query = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true);

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

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

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['price_aud', 'name', 'created_at', 'stock'];
        // Map API sort fields to database columns
        $sortMap = ['price' => 'price_aud', 'stock_quantity' => 'stock'];
        $sortField = $sortMap[$sortField] ?? $sortField;

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = min((int) $request->get('per_page', 12), 48);

        return new ProductCollection($query->paginate($perPage));
    }

    /**
     * Display featured products.
     *
     * @requirement SHOP-024 GET /api/v1/products/featured returns featured items
     *
     * @param Request $request
     * @return ProductCollection
     */
    public function featured(Request $request): ProductCollection
    {
        $limit = min((int) $request->get('limit', 8), 24);

        $products = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->where('stock', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return new ProductCollection($products);
    }

    /**
     * Display a single product by slug.
     *
     * @requirement SHOP-022 GET /api/v1/products/{slug} returns full product data
     *
     * @param string $slug
     * @return ProductResource|JsonResponse
     */
    public function show(string $slug): ProductResource|JsonResponse
    {
        $product = Product::query()
            ->with(['category', 'images'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        return new ProductResource($product);
    }

    /**
     * Get related products based on category.
     *
     * @param string $slug
     * @param Request $request
     * @return ProductCollection
     */
    public function related(string $slug, Request $request): ProductCollection
    {
        $product = Product::where('slug', $slug)->first();

        if (!$product) {
            return new ProductCollection(collect());
        }

        $limit = min((int) $request->get('limit', 4), 12);

        $related = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return new ProductCollection($related);
    }

    /**
     * Quick search for products (autocomplete).
     *
     * @requirement SHOP-004 Implement product search
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'data' => [],
            ]);
        }

        $products = Product::query()
            ->select(['id', 'name', 'slug', 'price_aud', 'stock'])
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->price_aud,
                    'formatted_price' => '$' . number_format((float) $product->price_aud, 2),
                    'in_stock' => $product->stock > 0,
                ];
            });

        return response()->json([
            'data' => $products,
        ]);
    }
}
