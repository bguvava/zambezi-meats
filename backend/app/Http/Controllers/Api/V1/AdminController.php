<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCategoryRequest;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateCategoryRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Promotion;
use App\Models\User;
use App\Services\ImageUploadService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * AdminController handles all admin dashboard operations.
 *
 * @requirement ADMIN-001 to ADMIN-028
 */
class AdminController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    /**
     * Authorize admin access.
     *
     * @requirement ADMIN-026 Implement admin middleware
     */
    private function authorizeAdmin(Request $request): void
    {
        if (!$request->user() || $request->user()->role !== User::ROLE_ADMIN) {
            abort(403, 'Admin access required.');
        }
    }

    /**
     * Authorize admin or staff access for product/category management.
     *
     * @requirement PROD-002 Staff can create/edit products/categories
     */
    private function authorizeAdminOrStaff(Request $request): void
    {
        if (!$request->user() || !in_array($request->user()->role, [User::ROLE_ADMIN, User::ROLE_STAFF])) {
            abort(403, 'Admin or Staff access required.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    /**
     * Get dashboard overview with KPIs.
     *
     * @requirement ADMIN-002 Create dashboard overview with KPIs
     * @requirement ADMIN-004 Create revenue chart (7/30 days)
     */
    public function getDashboard(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Today's stats
        $todayRevenue = Order::whereDate('created_at', $today)
            ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING, Order::STATUS_READY, Order::STATUS_OUT_FOR_DELIVERY])
            ->sum('total');

        $yesterdayRevenue = Order::whereDate('created_at', $yesterday)
            ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING, Order::STATUS_READY, Order::STATUS_OUT_FOR_DELIVERY])
            ->sum('total');

        $todayOrders = Order::whereDate('created_at', $today)->count();
        $yesterdayOrders = Order::whereDate('created_at', $yesterday)->count();

        $todayCustomers = User::whereDate('created_at', $today)
            ->where('role', User::ROLE_CUSTOMER)
            ->count();

        $pendingOrders = Order::where('status', Order::STATUS_PENDING)->count();

        // Revenue chart (last 7 days)
        $revenueChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)
                ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING, Order::STATUS_READY, Order::STATUS_OUT_FOR_DELIVERY])
                ->sum('total');

            $revenueChart[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'revenue' => (float) $revenue,
            ];
        }

        // Recent orders
        $recentOrders = Order::with(['user'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user->name,
                'total' => $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at->toIso8601String(),
            ]);

        // Low stock products
        $lowStockProducts = Product::where('stock', '<=', 10)
            ->where('stock', '>', 0)
            ->where('is_active', true)
            ->orderBy('stock')
            ->limit(5)
            ->get(['id', 'name', 'stock']);

        // Top products today
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereDate('orders.created_at', $today)
            ->select('products.id', 'products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'dashboard' => [
                'today' => [
                    'revenue' => (float) $todayRevenue,
                    'revenue_change' => $yesterdayRevenue > 0
                        ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1)
                        : 0,
                    'orders' => $todayOrders,
                    'orders_change' => $yesterdayOrders > 0
                        ? round((($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100, 1)
                        : 0,
                    'new_customers' => $todayCustomers,
                    'pending_orders' => $pendingOrders,
                ],
                'revenue_chart' => $revenueChart,
                'recent_orders' => $recentOrders,
                'low_stock_products' => $lowStockProducts,
                'top_products' => $topProducts,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Order Management
    |--------------------------------------------------------------------------
    */

    /**
     * Get all orders with filtering.
     *
     * @requirement ADMIN-005 Create orders management page
     * @requirement ADMIN-006 Implement order filtering
     */
    public function getOrders(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $query = Order::with(['user', 'assignedStaff']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by order number or customer
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'orders' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get single order details.
     *
     * @requirement ADMIN-007 Create order detail view
     */
    public function getOrder(int $id): JsonResponse
    {
        $order = Order::with([
            'user',
            'address',
            'items.product',
            'payment',
            'deliveryProof',
            'statusHistory',
            'assignedStaff',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /**
     * Update order.
     *
     * @requirement ADMIN-008 Implement order actions
     */
    public function updateOrder(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'delivery_instructions' => ['nullable', 'string', 'max:500'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_time_slot' => ['nullable', 'string', 'max:50'],
        ]);

        $order->update($validated);

        ActivityLog::log('order_updated', $order, null, $validated, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully.',
            'order' => $order->fresh(['user', 'address', 'items.product']),
        ]);
    }

    /**
     * Update order status.
     *
     * @requirement ADMIN-008 Implement order actions
     */
    public function updateOrderStatus(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in([
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY,
                Order::STATUS_OUT_FOR_DELIVERY,
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED,
            ])],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        // Log status change
        $order->statusHistory()->create([
            'status' => $validated['status'],
            'notes' => $validated['reason'] ?? null,
            'changed_by' => $request->user()->id,
        ]);

        ActivityLog::log(
            'order_status_changed',
            $order,
            ['status' => $oldStatus],
            ['status' => $validated['status']],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'order' => $order->fresh(),
        ]);
    }

    /**
     * Assign order to staff.
     *
     * @requirement ADMIN-009 Assign orders to staff
     */
    public function assignOrder(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'staff_id' => ['required', 'exists:users,id'],
        ]);

        // Verify staff member
        $staff = User::findOrFail($validated['staff_id']);
        if (!in_array($staff->role, [User::ROLE_STAFF, User::ROLE_ADMIN])) {
            return response()->json([
                'success' => false,
                'message' => 'Selected user is not a staff member.',
            ], 422);
        }

        $order->update([
            'assigned_to' => $staff->id,
            'assigned_at' => now(),
        ]);

        ActivityLog::log(
            'order_assigned',
            $order,
            null,
            ['assigned_to' => $staff->id, 'staff_name' => $staff->name],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => "Order assigned to {$staff->name}.",
            'order' => $order->fresh(['assignedStaff']),
        ]);
    }

    /**
     * Process refund.
     *
     * @requirement ADMIN-010 Process refunds
     */
    public function refundOrder(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $order = Order::with('payment')->findOrFail($id);

        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01', 'max:' . $order->total],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if (!$order->payment) {
            return response()->json([
                'success' => false,
                'message' => 'No payment found for this order.',
            ], 422);
        }

        if ($order->payment->status === Payment::STATUS_REFUNDED) {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been refunded.',
            ], 422);
        }

        $refundAmount = $validated['amount'] ?? (float) $order->total;

        // Update payment status
        $order->payment->update([
            'status' => Payment::STATUS_REFUNDED,
            'metadata' => array_merge($order->payment->metadata ?? [], [
                'refund_amount' => $refundAmount,
                'refund_reason' => $validated['reason'],
                'refunded_at' => now()->toIso8601String(),
                'refunded_by' => $request->user()->id,
            ]),
        ]);

        // Update order status
        $order->update(['status' => Order::STATUS_CANCELLED]);

        ActivityLog::log(
            'order_refunded',
            $order,
            null,
            [
                'amount' => $refundAmount,
                'reason' => $validated['reason'],
            ],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => "Refund of $" . number_format($refundAmount, 2) . " processed successfully.",
            'order' => $order->fresh(['payment']),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Product Management
    |--------------------------------------------------------------------------
    */

    /**
     * Get all products.
     *
     * @requirement ADMIN-011 Create products management page
     */
    /**
     * Get all products with filtering.
     *
     * @requirement PROD-003 Create products listing page
     * @requirement PROD-004 Implement product search
     * @requirement PROD-005 Implement product filters (max 3)
     */
    public function getProducts(Request $request): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $query = Product::with('category');

        // Filter 1: Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter 2: Status (Active/Inactive)
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter 3: Stock (In Stock/Low Stock/Out of Stock)
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'out_of_stock':
                    $query->where('stock', '<=', 0);
                    break;
                case 'low_stock':
                    // Low stock: between 1 and 10 (threshold)
                    $query->where('stock', '>', 0)
                        ->where('stock', '<=', 10);
                    break;
                case 'in_stock':
                    // In stock: above 10
                    $query->where('stock', '>', 10);
                    break;
            }
        }

        // Search by name, SKU, or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Order by name
        $query->orderBy('name');

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Create product with image upload.
     *
     * @requirement PROD-006 Create product form
     * @requirement PROD-007 Implement product image upload
     * @requirement PROD-016 Product slug generation
     */
    public function createProduct(StoreProductRequest $request): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $validated = $request->validated();

        DB::beginTransaction();

        try {
            // Create product
            $product = Product::create($validated);

            // Handle image uploads
            if ($request->hasFile('images')) {
                $imageService = new ImageUploadService();
                $images = $imageService->uploadMultiple(
                    $request->file('images'),
                    'products',
                    true
                );

                // Save images to product_images table
                foreach ($images as $index => $imagePaths) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePaths['original'],
                        'alt_text' => $product->name,
                        'sort_order' => $index,
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            // Log activity
            ActivityLog::log('product_created', $product, null, $validated, $request->user()->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'product' => $product->load('category', 'images'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update product with optional image upload.
     *
     * @requirement PROD-008 Update product details
     * @requirement PROD-007 Update product images
     */
    public function updateProduct(UpdateProductRequest $request, int $id): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $product = Product::findOrFail($id);
        $validated = $request->validated();
        $oldValues = $product->toArray();

        DB::beginTransaction();

        try {
            // Update product
            $product->update($validated);

            // Handle new image uploads
            if ($request->hasFile('images')) {
                $imageService = new ImageUploadService();
                $images = $imageService->uploadMultiple(
                    $request->file('images'),
                    'products',
                    true
                );

                // Get current highest sort order
                $maxSortOrder = $product->images()->max('sort_order') ?? -1;

                // Save new images
                foreach ($images as $index => $imagePaths) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePaths['original'],
                        'alt_text' => $product->name,
                        'sort_order' => $maxSortOrder + $index + 1,
                        'is_primary' => false,
                    ]);
                }
            }

            // Log activity
            ActivityLog::log('product_updated', $product, $oldValues, $validated, $request->user()->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'product' => $product->fresh(['category', 'images']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete product (soft delete).
     *
     * @requirement PROD-009 Delete products
     */
    public function deleteProduct(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $product = Product::findOrFail($id);

        // Soft delete by marking as inactive
        $product->update(['is_active' => false]);

        ActivityLog::log('product_deleted', $product, null, null, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Get single product details.
     *
     * @requirement PROD-017 View product details
     */
    public function getProduct(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $product = Product::with(['category', 'images'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }

    /**
     * Get low stock products.
     *
     * @requirement PROD-011 Low stock alerts
     */
    public function getLowStockProducts(Request $request): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $threshold = (int) $request->get('threshold', 10);

        $products = Product::with('category')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->get();

        return response()->json([
            'success' => true,
            'products' => $products,
            'count' => $products->count(),
            'threshold' => $threshold,
        ]);
    }

    /**
     * Adjust product stock.
     *
     * @requirement PROD-010 Stock management
     */
    public function adjustStock(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'not_in:0'],
            'type' => ['required', 'in:increase,decrease,adjustment'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($id);
        $stockBefore = $product->stock;

        DB::beginTransaction();

        try {
            // Calculate new stock
            $newStock = match ($validated['type']) {
                'increase' => $stockBefore + abs($validated['quantity']),
                'decrease' => max(0, $stockBefore - abs($validated['quantity'])),
                'adjustment' => abs($validated['quantity']),
            };

            // Map API types to database enum values
            $inventoryLogType = match ($validated['type']) {
                'increase' => 'addition',
                'decrease' => 'deduction',
                'adjustment' => 'adjustment',
            };

            // Update product stock
            $product->update(['stock' => $newStock]);

            // Create inventory log
            \App\Models\InventoryLog::create([
                'product_id' => $product->id,
                'type' => $inventoryLogType,
                'quantity' => $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'reason' => $validated['reason'],
                'user_id' => $request->user()->id,
            ]);

            // Log activity
            ActivityLog::log('stock_adjusted', $product, ['stock' => $stockBefore], ['stock' => $newStock], $request->user()->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully.',
                'product' => $product->fresh(),
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust stock: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get product stock history.
     *
     * @requirement PROD-010 Stock management history
     */
    public function getStockHistory(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $product = Product::findOrFail($id);

        $history = \App\Models\InventoryLog::with('user:id,name')
            ->where('product_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'product' => $product,
            'history' => $history->items(),
            'pagination' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
            ],
        ]);
    }

    /**
     * Delete product image.
     *
     * @requirement PROD-007 Manage product images
     */
    public function deleteProductImage(Request $request, int $id, int $imageId): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $product = Product::findOrFail($id);
        $image = ProductImage::where('product_id', $id)
            ->where('id', $imageId)
            ->firstOrFail();

        DB::beginTransaction();

        try {
            // Delete image files
            $imageService = new ImageUploadService();
            $imageService->deleteImage($image->image_path, 'products');

            // Delete database record
            $image->delete();

            // If deleted image was primary, set another as primary
            if ($image->is_primary) {
                $newPrimary = ProductImage::where('product_id', $id)
                    ->orderBy('sort_order')
                    ->first();

                if ($newPrimary) {
                    $newPrimary->update(['is_primary' => true]);
                }
            }

            // Log activity
            ActivityLog::log('product_image_deleted', $product, null, ['image_id' => $imageId], $request->user()->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder product images.
     *
     * @requirement PROD-007 Reorder product images
     */
    public function reorderProductImages(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $validated = $request->validate([
            'image_ids' => ['required', 'array', 'min:1'],
            'image_ids.*' => ['required', 'integer', 'exists:product_images,id'],
        ]);

        $product = Product::findOrFail($id);

        DB::beginTransaction();

        try {
            foreach ($validated['image_ids'] as $sortOrder => $imageId) {
                ProductImage::where('id', $imageId)
                    ->where('product_id', $id)
                    ->update([
                        'sort_order' => $sortOrder,
                        'is_primary' => $sortOrder === 0,
                    ]);
            }

            // Log activity
            ActivityLog::log('product_images_reordered', $product, null, $validated, $request->user()->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Images reordered successfully.',
                'images' => $product->fresh()->images,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder images: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export products to PDF.
     *
     * @requirement PROD-014 PDF export
     */
    public function exportProducts(Request $request)
    {
        $this->authorizeAdmin($request);

        // Build query with same filters as getProducts
        $query = Product::with('category');

        // Apply filters
        if ($request->has('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->has('stock_status')) {
            if ($request->stock_status === 'out_of_stock') {
                $query->where('stock', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $query->where('stock', '>', 0)->where('stock', '<', 10);
            } elseif ($request->stock_status === 'in_stock') {
                $query->where('stock', '>=', 10);
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Get products
        $products = $query->orderBy('name')->get();

        // Prepare filters for PDF header
        $filters = [];
        if ($request->has('category_id') && $request->category_id !== 'all') {
            $category = Category::find($request->category_id);
            $filters['category'] = $category ? $category->name : 'Unknown';
        }
        if ($request->has('status') && $request->status !== 'all') {
            $filters['status'] = $request->status;
        }
        if ($request->has('stock_status')) {
            $filters['stock_status'] = $request->stock_status;
        }
        if ($request->has('search')) {
            $filters['search'] = $request->search;
        }

        // Generate and download PDF
        $pdfService = new \App\Services\PdfExportService();

        return $pdfService->exportProducts($products, $filters);
    }

    /*
    |--------------------------------------------------------------------------
    | Category Management
    |--------------------------------------------------------------------------
    */

    /**
     * Get all categories.
     *
     * @requirement ADMIN-014 Create categories management
     */
    public function getCategories(Request $request): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $categories = Category::withCount('products')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * Create category with image upload.
     *
     * @requirement PROD-002 Create category CRUD operations
     * @requirement PROD-016 Category slug generation
     */
    public function createCategory(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $validated = $request->validated();

        DB::beginTransaction();

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $imageService = new ImageUploadService();
                $imagePaths = $imageService->uploadImage(
                    $request->file('image'),
                    'categories',
                    true
                );
                $validated['image'] = $imagePaths['original'];
            }

            $category = Category::create($validated);

            // Log activity
            ActivityLog::log('category_created', $category, null, $validated, $request->user()->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category->load('parent', 'children'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update category with optional image upload.
     *
     * @requirement PROD-002 Update category details
     */
    public function updateCategory(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $category = Category::findOrFail($id);
        $validated = $request->validated();
        $oldValues = $category->toArray();

        DB::beginTransaction();

        try {
            // Handle image replacement
            if ($request->hasFile('image')) {
                $imageService = new ImageUploadService();

                // Delete old image if exists
                if ($category->image) {
                    $imageService->deleteImage($category->image, 'categories');
                }

                // Upload new image
                $imagePaths = $imageService->uploadImage(
                    $request->file('image'),
                    'categories',
                    true
                );
                $validated['image'] = $imagePaths['original'];
            }

            $category->update($validated);

            // Log activity
            ActivityLog::log('category_updated', $category, $oldValues, $validated, $request->user()->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => $category->fresh(['parent', 'children']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete category.
     *
     * @requirement ADMIN-014 Create categories management
     */
    public function deleteCategory(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with products. Move or delete products first.',
            ], 422);
        }

        $category->delete();

        ActivityLog::log('category_deleted', $category, null, null, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Customer Management
    |--------------------------------------------------------------------------
    */

    /**
     * Get all customers.
     *
     * @requirement ADMIN-016 Create customers management page
     */
    public function getCustomers(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $query = User::where('role', User::ROLE_CUSTOMER)
            ->withCount('orders')
            ->withSum('orders', 'total');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $customers = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'customers' => $customers->items(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    /**
     * Get customer details.
     *
     * @requirement ADMIN-017 View customer details
     */
    public function getCustomer(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $customer = User::where('role', User::ROLE_CUSTOMER)
            ->withCount('orders')
            ->withSum('orders', 'total')
            ->findOrFail($id);

        $recentOrders = $customer->orders()
            ->with('items.product')
            ->latest()
            ->limit(10)
            ->get();

        $addresses = $customer->addresses;
        $tickets = $customer->supportTickets()->latest()->limit(5)->get();

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'recent_orders' => $recentOrders,
            'addresses' => $addresses,
            'support_tickets' => $tickets,
        ]);
    }

    /**
     * Update customer.
     *
     * @requirement ADMIN-017 View customer details
     */
    public function updateCustomer(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $customer = User::where('role', User::ROLE_CUSTOMER)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $oldValues = $customer->only(['name', 'email', 'phone', 'is_active']);
        $customer->update($validated);

        ActivityLog::log('customer_updated', $customer, $oldValues, $validated, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully.',
            'customer' => $customer->fresh(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Staff Management
    |--------------------------------------------------------------------------
    */

    /**
     * Get all staff members.
     *
     * @requirement ADMIN-018 Create staff management page
     */
    public function getStaff(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $query = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN]);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $staff = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'staff' => $staff->items(),
            'pagination' => [
                'current_page' => $staff->currentPage(),
                'last_page' => $staff->lastPage(),
                'per_page' => $staff->perPage(),
                'total' => $staff->total(),
            ],
        ]);
    }

    /**
     * Create staff account.
     *
     * @requirement ADMIN-019 Create/edit staff accounts
     */
    public function createStaff(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', Password::min(8)],
            'role' => ['required', Rule::in([User::ROLE_STAFF, User::ROLE_ADMIN])],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();
        $validated['is_active'] = true;

        $staff = User::create($validated);

        ActivityLog::log('staff_created', $staff, null, ['name' => $staff->name, 'role' => $staff->role], $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Staff account created successfully.',
            'staff' => $staff,
        ], 201);
    }

    /**
     * Update staff account.
     *
     * @requirement ADMIN-019 Create/edit staff accounts
     * @requirement ADMIN-020 Activate/deactivate staff
     */
    public function updateStaff(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $staff = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', Password::min(8)],
            'role' => ['sometimes', Rule::in([User::ROLE_STAFF, User::ROLE_ADMIN])],
            'is_active' => ['boolean'],
        ]);

        $oldValues = $staff->only(['name', 'email', 'phone', 'role', 'is_active']);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $staff->update($validated);

        ActivityLog::log('staff_updated', $staff, $oldValues, $validated, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Staff account updated successfully.',
            'staff' => $staff->fresh(),
        ]);
    }

    /**
     * Delete staff account.
     *
     * @requirement ADMIN-018 Create staff management page
     */
    public function deleteStaff(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $staff = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])->findOrFail($id);

        // Prevent self-deletion
        if ($staff->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        ActivityLog::log('staff_deleted', $staff, null, null, $request->user()->id);

        $staff->delete();

        return response()->json([
            'success' => true,
            'message' => 'Staff account deleted successfully.',
        ]);
    }

    /**
     * Get staff activity.
     *
     * @requirement ADMIN-021 View staff activity
     */
    public function getStaffActivity(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $staff = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_ADMIN])->findOrFail($id);

        $activity = ActivityLog::where('user_id', $staff->id)
            ->latest()
            ->limit(50)
            ->get();

        $ordersProcessed = Order::where('assigned_to', $staff->id)->count();
        $deliveriesCompleted = Order::where('assigned_to', $staff->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->count();

        return response()->json([
            'success' => true,
            'staff' => $staff,
            'activity' => $activity,
            'stats' => [
                'orders_processed' => $ordersProcessed,
                'deliveries_completed' => $deliveriesCompleted,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Promotion Management
    |--------------------------------------------------------------------------
    */

    /**
     * Get all promotions.
     *
     * @requirement ADMIN-022 Create promotions management
     */
    public function getPromotions(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $query = Promotion::query();

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active()->valid();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $promotions = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'promotions' => $promotions->items(),
            'pagination' => [
                'current_page' => $promotions->currentPage(),
                'last_page' => $promotions->lastPage(),
                'per_page' => $promotions->perPage(),
                'total' => $promotions->total(),
            ],
        ]);
    }

    /**
     * Create promotion.
     *
     * @requirement ADMIN-022 Create promotions management
     */
    public function createPromotion(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:promotions,code'],
            'type' => ['required', Rule::in([Promotion::TYPE_PERCENTAGE, Promotion::TYPE_FIXED])],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['uses_count'] = 0;

        $promotion = Promotion::create($validated);

        ActivityLog::log('promotion_created', $promotion, null, $validated, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Promotion created successfully.',
            'promotion' => $promotion,
        ], 201);
    }

    /**
     * Update promotion.
     *
     * @requirement ADMIN-022 Create promotions management
     */
    public function updatePromotion(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $promotion = Promotion::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:50', Rule::unique('promotions')->ignore($id)],
            'type' => ['sometimes', Rule::in([Promotion::TYPE_PERCENTAGE, Promotion::TYPE_FIXED])],
            'value' => ['sometimes', 'numeric', 'min:0.01'],
            'min_order' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ]);

        if (isset($validated['code'])) {
            $validated['code'] = strtoupper($validated['code']);
        }

        $oldValues = $promotion->toArray();
        $promotion->update($validated);

        ActivityLog::log('promotion_updated', $promotion, $oldValues, $validated, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Promotion updated successfully.',
            'promotion' => $promotion->fresh(),
        ]);
    }

    /**
     * Delete promotion.
     *
     * @requirement ADMIN-022 Create promotions management
     */
    public function deletePromotion(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $promotion = Promotion::findOrFail($id);

        ActivityLog::log('promotion_deleted', $promotion, null, null, $request->user()->id);

        $promotion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Promotion deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */

    /**
     * Get activity logs.
     *
     * @requirement ADMIN-023 Create audit/activity logs page
     */
    public function getActivityLogs(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $query = ActivityLog::with('user');

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Bulk delete activity logs.
     *
     * @requirement ADMIN-024 Implement bulk delete for activity logs
     */
    public function bulkDeleteActivityLogs(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:activity_logs,id'],
            'before_date' => ['nullable', 'date'],
        ]);

        $deleted = 0;

        if (!empty($validated['ids'])) {
            $deleted = ActivityLog::whereIn('id', $validated['ids'])->delete();
        } elseif (!empty($validated['before_date'])) {
            $deleted = ActivityLog::whereDate('created_at', '<', $validated['before_date'])->delete();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please provide log IDs or a date range.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "{$deleted} activity logs deleted successfully.",
            'deleted_count' => $deleted,
        ]);
    }

    /**
     * Get all service categories.
     *
     * @requirement SERV-001 Service categories listing
     */
    public function getServiceCategories(Request $request): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $query = ServiceCategory::query()->withCount('services');

        // Apply filters
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Apply search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort by sort_order then name
        $query->ordered();

        $categories = $query->get();

        return response()->json([
            'success' => true,
            'data' => ServiceCategoryResource::collection($categories),
        ]);
    }

    /**
     * Create a new service category.
     *
     * @requirement SERV-002 Category CRUD operations
     */
    public function storeServiceCategory(StoreServiceCategoryRequest $request): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $category = ServiceCategory::create($request->validated());

        // Log activity
        ActivityLog::log('service_category_created', $category, null, $category->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Service category created successfully.',
            'data' => new ServiceCategoryResource($category),
        ], 201);
    }

    /**
     * Update a service category.
     *
     * @requirement SERV-002 Category CRUD operations
     */
    public function updateServiceCategory(UpdateServiceCategoryRequest $request, int $id): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $category = ServiceCategory::findOrFail($id);
        $oldValues = $category->toArray();

        $category->update($request->validated());

        // Log activity
        ActivityLog::log('service_category_updated', $category, $oldValues, $category->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Service category updated successfully.',
            'data' => new ServiceCategoryResource($category->fresh()),
        ]);
    }

    /**
     * Delete a service category.
     *
     * @requirement SERV-002 Category CRUD operations
     */
    public function deleteServiceCategory(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $category = ServiceCategory::findOrFail($id);

        // Check if category has services
        if ($category->services()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with services. Please reassign or delete services first.',
                'error' => [
                    'code' => 'CATEGORY_HAS_SERVICES',
                    'errors' => ['category' => ['This category has services attached.']],
                ],
            ], 409);
        }

        $oldValues = $category->toArray();
        $category->delete();

        // Log activity
        ActivityLog::log('service_category_deleted', $category, $oldValues, null);

        return response()->json([
            'success' => true,
            'message' => 'Service category deleted successfully.',
        ]);
    }

    /**
     * Get all services with filtering and search.
     *
     * @requirement SERV-003 Services listing
     * @requirement SERV-004 Service search
     * @requirement SERV-005 Service filters
     */
    public function getServices(Request $request): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $query = Service::query()->with('category');

        // Apply category filter
        if ($request->filled('category_id')) {
            $query->where('service_category_id', $request->category_id);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Apply billing cycle filter
        if ($request->filled('billing_cycle')) {
            $query->where('billing_cycle', $request->billing_cycle);
        }

        // Apply featured filter
        if ($request->filled('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Apply search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $services = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ServiceResource::collection($services->items()),
            'pagination' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
            ],
        ]);
    }

    /**
     * Get a single service by ID.
     *
     * @requirement SERV-015 Service view modal
     */
    public function getService(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $service = Service::with('category')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new ServiceResource($service),
        ]);
    }

    /**
     * Create a new service.
     *
     * @requirement SERV-006 Service form
     * @requirement SERV-007 Billing cycle options
     * @requirement SERV-014 Features list component
     */
    public function storeService(StoreServiceRequest $request): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $service = Service::create($request->validated());

        // Log activity
        ActivityLog::log('service_created', $service, null, $service->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully.',
            'data' => new ServiceResource($service->load('category')),
        ], 201);
    }

    /**
     * Update a service.
     *
     * @requirement SERV-008 Service edit
     */
    public function updateService(UpdateServiceRequest $request, int $id): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $service = Service::findOrFail($id);
        $oldValues = $service->toArray();

        $service->update($request->validated());

        // Log activity
        ActivityLog::log('service_updated', $service, $oldValues, $service->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully.',
            'data' => new ServiceResource($service->fresh(['category'])),
        ]);
    }

    /**
     * Delete a service.
     *
     * @requirement SERV-009 Service delete (Admin only)
     */
    public function deleteService(Request $request, int $id): JsonResponse
    {
        $this->authorizeAdmin($request);

        $service = Service::findOrFail($id);
        $oldValues = $service->toArray();

        $service->delete();

        // Log activity
        ActivityLog::log('service_deleted', $service, $oldValues, null);

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully',
        ]);
    }

    /**
     * Export services to PDF.
     *
     * @requirement SERV-012 Service export to PDF
     */
    public function exportServices(Request $request)
    {
        $this->authorizeAdminOrStaff($request);

        $query = Service::query()->with('category');

        // Apply same filters as getServices
        if ($request->filled('category_id')) {
            $query->where('service_category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }
        if ($request->filled('billing_cycle')) {
            $query->where('billing_cycle', $request->billing_cycle);
        }
        if ($request->filled('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $services = $query->orderBy('name')->get();

        // Build filters array for PDF
        $filters = [];
        if ($request->filled('category_id')) {
            $category = ServiceCategory::find($request->category_id);
            $filters['Category'] = $category?->name ?? 'Unknown';
        }
        if ($request->filled('status')) {
            $filters['Status'] = ucfirst($request->status);
        }
        if ($request->filled('billing_cycle')) {
            $filters['Billing Cycle'] = ucwords(str_replace('_', ' ', $request->billing_cycle));
        }
        if ($request->filled('featured')) {
            $filters['Featured'] = $request->boolean('featured') ? 'Yes' : 'No';
        }
        if ($request->filled('search')) {
            $filters['Search'] = $request->search;
        }

        // Use PdfExportService
        $pdfService = app(\App\Services\PdfExportService::class);
        return $pdfService->exportServices($services, $filters);
    }

    /*
    |--------------------------------------------------------------------------
    | Service Analytics
    |--------------------------------------------------------------------------
    */

    /**
     * Get service analytics dashboard data.
     *
     * @requirement SERV-017 Service analytics dashboard
     */
    public function getServiceAnalytics(Request $request): JsonResponse
    {
        $this->authorizeAdminOrStaff($request);

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])
            : Carbon::now()->subMonths(6);
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])
            : Carbon::now();

        // Revenue by service (mock data - replace with real subscription data when available)
        $revenueByService = Service::with('category')
            ->where('is_active', true)
            ->get()
            ->map(function ($service) {
                // Mock revenue calculation - replace with real subscription revenue
                $baseRevenue = $service->price_aud * rand(5, 50);
                return [
                    'service_id' => $service->id,
                    'service_name' => $service->name,
                    'category_name' => $service->category->name,
                    'billing_cycle' => $service->billing_cycle,
                    'total_revenue' => (float) number_format($baseRevenue, 2, '.', ''),
                    'currency' => 'AUD',
                ];
            })
            ->sortByDesc('total_revenue')
            ->values();

        // Popular services by view count (mock data - replace with real analytics)
        $popularServices = Service::with('category')
            ->where('is_active', true)
            ->get()
            ->map(function ($service) {
                return [
                    'service_id' => $service->id,
                    'service_name' => $service->name,
                    'category_name' => $service->category->name,
                    'view_count' => rand(100, 1000),
                    'inquiry_count' => rand(10, 100),
                    'conversion_rate' => rand(5, 30) . '%',
                ];
            })
            ->sortByDesc('view_count')
            ->take(10)
            ->values();

        // Subscription trends (mock data - replace with real subscription data)
        $subscriptionTrends = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $monthKey = $currentDate->format('Y-m');
            $subscriptionTrends[] = [
                'period' => $currentDate->format('M Y'),
                'new_subscriptions' => rand(5, 30),
                'active_subscriptions' => rand(50, 200),
                'cancelled_subscriptions' => rand(0, 10),
                'total_revenue' => (float) number_format(rand(5000, 20000), 2, '.', ''),
            ];
            $currentDate->addMonth();
        }

        // Summary statistics
        $summary = [
            'total_services' => Service::count(),
            'active_services' => Service::where('is_active', true)->count(),
            'featured_services' => Service::where('is_featured', true)->count(),
            'total_categories' => ServiceCategory::count(),
            'average_price_aud' => (float) number_format(Service::where('is_active', true)->avg('price_aud') ?? 0, 2, '.', ''),
            'total_revenue_period' => (float) number_format($revenueByService->sum('total_revenue'), 2, '.', ''),
        ];

        // Services by billing cycle
        $servicesByBillingCycle = Service::where('is_active', true)
            ->selectRaw('billing_cycle, COUNT(*) as count')
            ->groupBy('billing_cycle')
            ->get()
            ->mapWithKeys(function ($item) {
                $label = Service::getBillingCycles()[$item->billing_cycle] ?? 'Unknown';
                return [$label => $item->count];
            });

        // Services by category
        $servicesByCategory = ServiceCategory::withCount('services')
            ->get()
            ->map(function ($category) {
                return [
                    'category_name' => $category->name,
                    'service_count' => $category->services_count,
                    'percentage' => Service::count() > 0
                        ? round(($category->services_count / Service::count()) * 100, 1)
                        : 0,
                ];
            })
            ->sortByDesc('service_count')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'revenue_by_service' => $revenueByService,
                'popular_services' => $popularServices,
                'subscription_trends' => $subscriptionTrends,
                'services_by_billing_cycle' => $servicesByBillingCycle,
                'services_by_category' => $servicesByCategory,
            ],
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
        ]);
    }
}
