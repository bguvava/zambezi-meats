<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\WasteLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * InventoryController handles inventory management operations.
 *
 * @requirement INV-001 Create inventory dashboard
 * @requirement INV-002 Display stock levels table
 * @requirement INV-003 Implement stock filtering
 * @requirement INV-004 Create stock receive form
 * @requirement INV-005 Create stock adjustment form
 * @requirement INV-006 Auto-deduct stock on order
 * @requirement INV-007 Restore stock on order cancel
 * @requirement INV-008 Set minimum stock thresholds
 * @requirement INV-009 Create low stock alerts
 * @requirement INV-010 Create out of stock alerts
 * @requirement INV-011 Display inventory history
 * @requirement INV-012 View product inventory detail
 * @requirement INV-013 Create waste management section
 * @requirement INV-014 Review and approve waste logs
 * @requirement INV-015 Generate inventory report
 * @requirement INV-016 Export inventory data
 * @requirement INV-017 Create inventory API endpoints
 */
class InventoryController extends Controller
{
    /**
     * Default minimum stock threshold.
     */
    private const DEFAULT_MIN_STOCK = 10;

    /**
     * Get inventory dashboard overview.
     *
     * @requirement INV-001 Create inventory dashboard
     */
    public function getDashboard(): JsonResponse
    {
        $totalProducts = Product::count();
        $lowStockCount = Product::where('stock', '>', 0)
            ->where('stock', '<=', DB::raw('COALESCE(JSON_EXTRACT(meta, "$.min_stock"), ' . self::DEFAULT_MIN_STOCK . ')'))
            ->count();
        $outOfStockCount = Product::where('stock', '<=', 0)->count();

        // Waste this month
        $wasteThisMonth = WasteLog::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('quantity');

        $wasteValueThisMonth = WasteLog::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_cost');

        // Recent movements (last 10)
        $recentMovements = InventoryLog::with(['product:id,name', 'user:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'date' => $log->created_at->toIso8601String(),
                'product' => $log->product?->name ?? 'Unknown',
                'type' => $log->type,
                'quantity' => $log->quantity,
                'stock_before' => $log->stock_before,
                'stock_after' => $log->stock_after,
                'reason' => $log->reason,
                'user' => $log->user?->name ?? 'System',
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'waste_this_month' => [
                    'quantity' => (float) $wasteThisMonth,
                    'value' => (float) $wasteValueThisMonth,
                ],
                'recent_movements' => $recentMovements,
            ],
        ]);
    }

    /**
     * Get all inventory items with filtering.
     *
     * @requirement INV-002 Display stock levels table
     * @requirement INV-003 Implement stock filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category:id,name')
            ->select([
                'id',
                'name',
                'sku',
                'category_id',
                'stock',
                'unit',
                'price_aud',
                'is_active',
                'meta',
                'updated_at',
            ]);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by stock status
        if ($request->filled('status')) {
            $minStock = self::DEFAULT_MIN_STOCK;

            switch ($request->status) {
                case 'low':
                    $query->where('stock', '>', 0)
                        ->where('stock', '<=', DB::raw("COALESCE(JSON_EXTRACT(meta, '$.min_stock'), {$minStock})"));
                    break;
                case 'out':
                    $query->where('stock', '<=', 0);
                    break;
                case 'normal':
                    $query->where('stock', '>', DB::raw("COALESCE(JSON_EXTRACT(meta, '$.min_stock'), {$minStock})"));
                    break;
            }
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = min((int) $request->input('per_page', 20), 100);
        $inventory = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $inventory->items(),
            'meta' => [
                'current_page' => $inventory->currentPage(),
                'last_page' => $inventory->lastPage(),
                'per_page' => $inventory->perPage(),
                'total' => $inventory->total(),
            ],
        ]);
    }

    /**
     * Get single product inventory detail.
     *
     * @requirement INV-012 View product inventory detail
     */
    public function show(int $productId): JsonResponse
    {
        $product = Product::with('category:id,name')
            ->find($productId);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        // Get inventory history for this product
        $history = InventoryLog::with('user:id,name')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'date' => $log->created_at->toIso8601String(),
                'type' => $log->type,
                'quantity' => $log->quantity,
                'stock_before' => $log->stock_before,
                'stock_after' => $log->stock_after,
                'reason' => $log->reason,
                'user' => $log->user?->name ?? 'System',
            ]);

        // Get waste logs for this product
        $wasteLogs = WasteLog::with('loggedByUser:id,name')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'date' => $log->created_at->toIso8601String(),
                'quantity' => (float) $log->quantity,
                'reason' => $log->reason,
                'notes' => $log->notes,
                'logged_by' => $log->loggedByUser?->name ?? 'Unknown',
            ]);

        $minStock = $product->meta['min_stock'] ?? self::DEFAULT_MIN_STOCK;

        return response()->json([
            'success' => true,
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category?->name,
                    'stock' => $product->stock,
                    'min_stock' => $minStock,
                    'unit' => $product->unit,
                    'price_aud' => (float) $product->price_aud,
                    'is_active' => $product->is_active,
                    'status' => $this->getStockStatus($product->stock, $minStock),
                ],
                'history' => $history,
                'waste_logs' => $wasteLogs,
            ],
        ]);
    }

    /**
     * Receive stock (add incoming stock).
     *
     * @requirement INV-004 Create stock receive form
     */
    public function receiveStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'date' => ['nullable', 'date'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $stockBefore = $product->stock;
        $stockAfter = $stockBefore + $validated['quantity'];

        DB::transaction(function () use ($product, $validated, $stockBefore, $stockAfter, $request) {
            // Update product stock
            $product->update(['stock' => $stockAfter]);

            // Create inventory log
            InventoryLog::create([
                'product_id' => $product->id,
                'type' => 'addition',
                'quantity' => $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => 'Stock received' . ($validated['supplier'] ? " from {$validated['supplier']}" : ''),
                'user_id' => $request->user()->id,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Stock received successfully',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity_added' => $validated['quantity'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ],
        ]);
    }

    /**
     * Adjust stock (correct discrepancies).
     *
     * @requirement INV-005 Create stock adjustment form
     */
    public function adjustStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'new_quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $stockBefore = $product->stock;
        $stockAfter = $validated['new_quantity'];
        $difference = $stockAfter - $stockBefore;

        DB::transaction(function () use ($product, $validated, $stockBefore, $stockAfter, $difference, $request) {
            // Update product stock
            $product->update(['stock' => $stockAfter]);

            // Create inventory log
            InventoryLog::create([
                'product_id' => $product->id,
                'type' => 'adjustment',
                'quantity' => abs($difference),
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $validated['reason'] . ($validated['notes'] ? ": {$validated['notes']}" : ''),
                'user_id' => $request->user()->id,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'adjustment' => $difference,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ],
        ]);
    }

    /**
     * Update minimum stock threshold.
     *
     * @requirement INV-008 Set minimum stock thresholds
     */
    public function updateMinStock(Request $request, int $productId): JsonResponse
    {
        $validated = $request->validate([
            'min_stock' => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::findOrFail($productId);
        $meta = $product->meta ?? [];
        $meta['min_stock'] = $validated['min_stock'];
        $product->update(['meta' => $meta]);

        return response()->json([
            'success' => true,
            'message' => 'Minimum stock threshold updated',
            'data' => [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'min_stock' => $validated['min_stock'],
            ],
        ]);
    }

    /**
     * Get inventory history with filtering.
     *
     * @requirement INV-011 Display inventory history
     */
    public function getHistory(Request $request): JsonResponse
    {
        $query = InventoryLog::with(['product:id,name', 'user:id,name'])
            ->orderBy('created_at', 'desc');

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $logs = $query->paginate($perPage);

        $data = collect($logs->items())->map(fn($log) => [
            'id' => $log->id,
            'date' => $log->created_at->toIso8601String(),
            'product' => [
                'id' => $log->product?->id,
                'name' => $log->product?->name ?? 'Deleted Product',
            ],
            'type' => $log->type,
            'quantity' => $log->quantity,
            'stock_before' => $log->stock_before,
            'stock_after' => $log->stock_after,
            'reason' => $log->reason,
            'user' => $log->user?->name ?? 'System',
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Get low stock items.
     *
     * @requirement INV-009 Create low stock alerts
     */
    public function getLowStock(): JsonResponse
    {
        $products = Product::with('category:id,name')
            ->where('stock', '>', 0)
            ->where('stock', '<=', DB::raw('COALESCE(JSON_EXTRACT(meta, "$.min_stock"), ' . self::DEFAULT_MIN_STOCK . ')'))
            ->orderBy('stock', 'asc')
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category?->name,
                'stock' => $product->stock,
                'min_stock' => $product->meta['min_stock'] ?? self::DEFAULT_MIN_STOCK,
                'unit' => $product->unit,
                'status' => 'low',
            ]);

        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count(),
        ]);
    }

    /**
     * Get inventory alerts (low stock + out of stock).
     *
     * @requirement INV-009 Create low stock alerts
     * @requirement INV-010 Create out of stock alerts
     */
    public function getAlerts(): JsonResponse
    {
        $lowStock = Product::with('category:id,name')
            ->where('stock', '>', 0)
            ->where('stock', '<=', DB::raw('COALESCE(JSON_EXTRACT(meta, "$.min_stock"), ' . self::DEFAULT_MIN_STOCK . ')'))
            ->orderBy('stock', 'asc')
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category?->name,
                'stock' => $product->stock,
                'min_stock' => $product->meta['min_stock'] ?? self::DEFAULT_MIN_STOCK,
                'unit' => $product->unit,
                'alert_type' => 'low_stock',
                'message' => "Stock below minimum threshold ({$product->stock} remaining)",
            ]);

        $outOfStock = Product::with('category:id,name')
            ->where('stock', '<=', 0)
            ->orderBy('name')
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'category' => $product->category?->name,
                'stock' => 0,
                'min_stock' => $product->meta['min_stock'] ?? self::DEFAULT_MIN_STOCK,
                'unit' => $product->unit,
                'alert_type' => 'out_of_stock',
                'message' => 'Product is out of stock',
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
            ],
            'summary' => [
                'low_stock_count' => $lowStock->count(),
                'out_of_stock_count' => $outOfStock->count(),
                'total_alerts' => $lowStock->count() + $outOfStock->count(),
            ],
        ]);
    }

    /**
     * Get waste entries.
     *
     * @requirement INV-013 Create waste management section
     */
    public function getWaste(Request $request): JsonResponse
    {
        $query = WasteLog::with(['product:id,name', 'loggedByUser:id,name'])
            ->orderBy('created_at', 'desc');

        // Filter by reason
        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by approval status
        if ($request->filled('approved')) {
            $approved = filter_var($request->approved, FILTER_VALIDATE_BOOLEAN);
            if ($approved) {
                $query->whereNotNull('approved_at');
            } else {
                $query->whereNull('approved_at');
            }
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $logs = $query->paginate($perPage);

        $data = collect($logs->items())->map(fn($log) => [
            'id' => $log->id,
            'date' => $log->created_at->toIso8601String(),
            'product' => [
                'id' => $log->product?->id,
                'name' => $log->product?->name ?? 'Deleted Product',
            ],
            'quantity' => (float) $log->quantity,
            'unit_cost' => (float) ($log->unit_cost ?? 0),
            'total_cost' => (float) ($log->total_cost ?? 0),
            'reason' => $log->reason,
            'notes' => $log->notes,
            'logged_by' => $log->loggedByUser?->name ?? 'Unknown',
            'approved' => $log->approved_at !== null,
            'approved_at' => $log->approved_at,
        ]);

        // Calculate summary
        $summary = WasteLog::query()
            ->when($request->filled('start_date'), fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->filled('end_date'), fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->selectRaw('SUM(quantity) as total_quantity, SUM(total_cost) as total_value, COUNT(*) as total_entries')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
            'summary' => [
                'total_quantity' => (float) ($summary->total_quantity ?? 0),
                'total_value' => (float) ($summary->total_value ?? 0),
                'total_entries' => (int) ($summary->total_entries ?? 0),
            ],
        ]);
    }

    /**
     * Approve or reject a waste log entry.
     *
     * @requirement INV-014 Review and approve waste logs
     */
    public function approveWaste(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'approved' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $wasteLog = WasteLog::findOrFail($id);

        if ($wasteLog->approved_at !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Waste log has already been reviewed',
            ], 400);
        }

        if ($validated['approved']) {
            $wasteLog->update([
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
            ]);

            $message = 'Waste log approved';
        } else {
            $wasteLog->update([
                'rejected_at' => now(),
                'rejected_by' => $request->user()->id,
                'rejection_notes' => $validated['notes'] ?? null,
            ]);

            $message = 'Waste log rejected';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'id' => $wasteLog->id,
                'approved' => $validated['approved'],
            ],
        ]);
    }

    /**
     * Generate inventory report.
     *
     * @requirement INV-015 Generate inventory report
     */
    public function getReport(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // Current stock levels by category
        $stockByCategory = Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as category', DB::raw('SUM(products.stock) as total_stock'), DB::raw('COUNT(products.id) as product_count'))
            ->groupBy('categories.id', 'categories.name')
            ->get();

        // Stock movements in period
        $movements = InventoryLog::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("type, SUM(quantity) as total_quantity, COUNT(*) as count")
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        // Waste summary
        $wasteSummary = WasteLog::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("reason, SUM(quantity) as total_quantity, SUM(total_cost) as total_value, COUNT(*) as count")
            ->groupBy('reason')
            ->get();

        // Low stock and out of stock counts
        $lowStockCount = Product::where('stock', '>', 0)
            ->where('stock', '<=', DB::raw('COALESCE(JSON_EXTRACT(meta, "$.min_stock"), ' . self::DEFAULT_MIN_STOCK . ')'))
            ->count();

        $outOfStockCount = Product::where('stock', '<=', 0)->count();

        // Top products by movement
        $topMovements = InventoryLog::whereBetween('inventory_logs.created_at', [$startDate, $endDate])
            ->join('products', 'inventory_logs.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', DB::raw('SUM(ABS(inventory_logs.quantity)) as total_movement'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_movement', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'current_stock' => [
                    'by_category' => $stockByCategory,
                    'low_stock_count' => $lowStockCount,
                    'out_of_stock_count' => $outOfStockCount,
                ],
                'movements' => [
                    'additions' => $movements->get('addition', (object) ['total_quantity' => 0, 'count' => 0]),
                    'deductions' => $movements->get('deduction', (object) ['total_quantity' => 0, 'count' => 0]),
                    'adjustments' => $movements->get('adjustment', (object) ['total_quantity' => 0, 'count' => 0]),
                ],
                'waste' => [
                    'by_reason' => $wasteSummary,
                    'total_quantity' => $wasteSummary->sum('total_quantity'),
                    'total_value' => $wasteSummary->sum('total_value'),
                ],
                'top_movements' => $topMovements,
            ],
        ]);
    }

    /**
     * Export inventory data as PDF.
     *
     * @requirement INV-016 Export inventory data
     */
    public function export(Request $request): JsonResponse
    {
        // Get all inventory data
        $products = Product::with('category:id,name')
            ->orderBy('category_id')
            ->orderBy('name')
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'category' => $product->category?->name ?? 'Uncategorized',
                'stock' => $product->stock,
                'min_stock' => $product->meta['min_stock'] ?? self::DEFAULT_MIN_STOCK,
                'unit' => $product->unit,
                'price_aud' => (float) $product->price_aud,
                'stock_value' => (float) $product->price_aud * $product->stock,
                'status' => $this->getStockStatus($product->stock, $product->meta['min_stock'] ?? self::DEFAULT_MIN_STOCK),
            ]);

        // Summary
        $summary = [
            'total_products' => $products->count(),
            'total_stock_value' => $products->sum('stock_value'),
            'low_stock_count' => $products->where('status', 'low')->count(),
            'out_of_stock_count' => $products->where('status', 'out')->count(),
            'generated_at' => now()->toIso8601String(),
        ];

        // In a real implementation, this would generate a PDF
        // For now, return the data that would be in the PDF
        return response()->json([
            'success' => true,
            'message' => 'Export data generated',
            'data' => [
                'summary' => $summary,
                'products' => $products,
            ],
        ]);
    }

    /**
     * Get stock status label.
     */
    private function getStockStatus(int $stock, int $minStock): string
    {
        if ($stock <= 0) {
            return 'out';
        }

        if ($stock <= $minStock) {
            return 'low';
        }

        return 'normal';
    }
}
