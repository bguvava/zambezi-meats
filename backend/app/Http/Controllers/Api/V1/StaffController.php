<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\DeliveryProof;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WasteLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Staff Dashboard Controller.
 *
 * Handles all staff dashboard functionality including order processing,
 * delivery management, proof of delivery, stock checks, and waste logging.
 *
 * @requirement STAFF-001 to STAFF-024 Staff Dashboard Features
 */
class StaffController extends Controller
{
    /**
     * The allowed staff roles.
     *
     * @var array<string>
     */
    private const STAFF_ROLES = [User::ROLE_STAFF, User::ROLE_ADMIN];

    /*
    |--------------------------------------------------------------------------
    | Dashboard Overview
    |--------------------------------------------------------------------------
    */

    /**
     * Get staff dashboard overview.
     *
     * @requirement STAFF-001 Create staff dashboard homepage
     * @requirement STAFF-002 Show quick stats for staff
     */
    public function getDashboard(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();

        // Today's stats
        $todayStats = [
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'deliveries_today' => Order::where('status', Order::STATUS_OUT_FOR_DELIVERY)
                ->whereDate('updated_at', $today)
                ->count(),
            'completed_today' => Order::where('status', Order::STATUS_DELIVERED)
                ->whereDate('updated_at', $today)
                ->count(),
        ];

        // Order queue stats
        $queueStats = [
            'pending' => Order::where('status', Order::STATUS_PENDING)->count(),
            'processing' => Order::where('status', Order::STATUS_PROCESSING)->count(),
            'ready_for_pickup' => Order::where('status', Order::STATUS_READY)->count(),
            'out_for_delivery' => Order::where('status', Order::STATUS_OUT_FOR_DELIVERY)->count(),
        ];

        // Weekly summary
        $weeklySummary = [
            'orders_this_week' => Order::where('created_at', '>=', $startOfWeek)->count(),
            'deliveries_this_week' => Order::where('status', Order::STATUS_DELIVERED)
                ->where('updated_at', '>=', $startOfWeek)
                ->count(),
            'waste_logs_this_week' => WasteLog::where('created_at', '>=', $startOfWeek)->count(),
        ];

        // Low stock alerts
        $lowStockProducts = Product::where('stock', '<=', 10)
            ->where('is_active', true)
            ->select('id', 'name', 'stock', 'sku')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'dashboard' => [
                'today' => $todayStats,
                'queue' => $queueStats,
                'weekly_summary' => $weeklySummary,
                'low_stock_alerts' => $lowStockProducts,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Order Queue Management
    |--------------------------------------------------------------------------
    */

    /**
     * Get order queue for staff.
     *
     * @requirement STAFF-003 Create order queue view
     * @requirement STAFF-004 Add sorting/filtering for queue
     * @requirement STAFF-005 Show order priority
     */
    public function getOrderQueue(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $query = Order::with(['user', 'address', 'items.product']);

        // Filter by status
        if ($request->has('status')) {
            $statuses = is_array($request->status) ? $request->status : [$request->status];
            $query->whereIn('status', $statuses);
        } else {
            // Default: show actionable orders
            $query->whereIn('status', [
                Order::STATUS_PENDING,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY,
            ]);
        }

        // Filter by delivery method
        if ($request->has('delivery_method')) {
            $query->where('delivery_method', $request->delivery_method);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by order number or customer name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting - priority orders first, then by created_at
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'asc');

        $query->orderByRaw("CASE
            WHEN status = 'pending' THEN 1
            WHEN status = 'processing' THEN 2
            WHEN status = 'ready' THEN 3
            ELSE 4 END")
            ->orderBy($sortBy, $sortOrder);

        $orders = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'orders' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get single order details for staff.
     *
     * @requirement STAFF-006 Create order detail view for staff
     */
    public function getOrder(Request $request, int $id): JsonResponse
    {
        $this->authorizeStaff($request);

        $order = Order::with([
            'user',
            'address',
            'items.product',
            'payment',
            'statusHistory',
            'deliveryProof',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Update order status.
     *
     * @requirement STAFF-007 Implement status update buttons
     * @requirement STAFF-023 Log all status changes
     */
    public function updateOrderStatus(Request $request, int $id): JsonResponse
    {
        $this->authorizeStaff($request);

        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in([
                Order::STATUS_PENDING,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY,
                Order::STATUS_OUT_FOR_DELIVERY,
                Order::STATUS_DELIVERED,
                Order::STATUS_CANCELLED,
            ])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        // Validate status transitions
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            return response()->json([
                'success' => false,
                'message' => "Cannot transition from {$oldStatus} to {$newStatus}.",
            ], 422);
        }

        DB::transaction(function () use ($order, $newStatus, $validated, $request) {
            $order->update(['status' => $newStatus]);

            // Log status change
            $order->statusHistory()->create([
                'status' => $newStatus,
                'changed_by' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'order' => new OrderResource($order->fresh(['statusHistory'])),
        ]);
    }

    /**
     * Validate status transitions.
     */
    private function isValidStatusTransition(string $from, string $to): bool
    {
        $validTransitions = [
            Order::STATUS_PENDING => [Order::STATUS_PROCESSING, Order::STATUS_CANCELLED],
            Order::STATUS_PROCESSING => [Order::STATUS_READY, Order::STATUS_CANCELLED],
            Order::STATUS_READY => [Order::STATUS_OUT_FOR_DELIVERY, Order::STATUS_DELIVERED, Order::STATUS_CANCELLED],
            Order::STATUS_OUT_FOR_DELIVERY => [Order::STATUS_DELIVERED, Order::STATUS_CANCELLED],
            Order::STATUS_DELIVERED => [], // Cannot change from delivered
            Order::STATUS_CANCELLED => [], // Cannot change from cancelled
        ];

        return in_array($to, $validTransitions[$from] ?? []);
    }

    /**
     * Add note to order.
     *
     * @requirement STAFF-008 Add internal notes to orders
     */
    public function addOrderNote(Request $request, int $id): JsonResponse
    {
        $this->authorizeStaff($request);

        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        // Store note in order's staff_notes field (separate from customer notes)
        $notes = $order->staff_notes ?? [];
        $notes[] = [
            'content' => $validated['note'],
            'added_by' => $request->user()->id,
            'added_by_name' => $request->user()->name,
            'added_at' => now()->toIso8601String(),
        ];

        $order->update(['staff_notes' => $notes]);

        return response()->json([
            'success' => true,
            'message' => 'Note added successfully.',
            'notes' => $notes,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Delivery Management
    |--------------------------------------------------------------------------
    */

    /**
     * Get today's deliveries.
     *
     * @requirement STAFF-009 Create "Today's Deliveries" list
     * @requirement STAFF-010 Show delivery details (address, time slot)
     */
    public function getTodaysDeliveries(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $today = Carbon::today();

        $deliveries = Order::with(['user', 'address', 'items.product', 'deliveryProof'])
            ->where('delivery_method', 'delivery')
            ->whereIn('status', [
                Order::STATUS_READY,
                Order::STATUS_OUT_FOR_DELIVERY,
                Order::STATUS_DELIVERED,
            ])
            ->where(function ($query) use ($today) {
                $query->whereDate('delivery_date', $today)
                    ->orWhere(function ($q) use ($today) {
                        $q->whereIn('status', [Order::STATUS_READY, Order::STATUS_OUT_FOR_DELIVERY])
                            ->whereDate('delivery_date', '<=', $today);
                    });
            })
            ->orderByRaw("CASE
                WHEN status = 'out_for_delivery' THEN 1
                WHEN status = 'ready' THEN 2
                ELSE 3 END")
            ->orderBy('delivery_time_slot')
            ->get();

        // Group by status
        $grouped = [
            'pending_dispatch' => $deliveries->where('status', Order::STATUS_READY)->values(),
            'in_transit' => $deliveries->where('status', Order::STATUS_OUT_FOR_DELIVERY)->values(),
            'completed' => $deliveries->where('status', Order::STATUS_DELIVERED)->values(),
        ];

        return response()->json([
            'success' => true,
            'date' => $today->toDateString(),
            'deliveries' => [
                'pending_dispatch' => OrderResource::collection($grouped['pending_dispatch']),
                'in_transit' => OrderResource::collection($grouped['in_transit']),
                'completed' => OrderResource::collection($grouped['completed']),
            ],
            'summary' => [
                'total' => $deliveries->count(),
                'pending' => $grouped['pending_dispatch']->count(),
                'in_transit' => $grouped['in_transit']->count(),
                'completed' => $grouped['completed']->count(),
            ],
        ]);
    }

    /**
     * Mark order as out for delivery.
     *
     * @requirement STAFF-011 Mark orders "Out for Delivery"
     */
    public function markOutForDelivery(Request $request, int $id): JsonResponse
    {
        $this->authorizeStaff($request);

        $order = Order::findOrFail($id);

        if ($order->status !== Order::STATUS_READY) {
            return response()->json([
                'success' => false,
                'message' => 'Order must be in "ready" status to mark as out for delivery.',
            ], 422);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update(['status' => Order::STATUS_OUT_FOR_DELIVERY]);

            $order->statusHistory()->create([
                'status' => Order::STATUS_OUT_FOR_DELIVERY,
                'changed_by' => $request->user()->id,
                'notes' => 'Marked out for delivery',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order marked as out for delivery.',
            'order' => new OrderResource($order->fresh()),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Proof of Delivery
    |--------------------------------------------------------------------------
    */

    /**
     * Upload proof of delivery.
     *
     * @requirement STAFF-012 Create POD capture interface
     * @requirement STAFF-013 Store POD with order
     */
    public function uploadProofOfDelivery(Request $request, int $id): JsonResponse
    {
        $this->authorizeStaff($request);

        $order = Order::findOrFail($id);

        if ($order->status !== Order::STATUS_OUT_FOR_DELIVERY) {
            return response()->json([
                'success' => false,
                'message' => 'Order must be out for delivery to upload proof.',
            ], 422);
        }

        $validated = $request->validate([
            'signature' => ['nullable', 'string'], // Base64 encoded signature
            'photo' => ['nullable', 'image', 'max:5120'], // Max 5MB
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'left_at_door' => ['nullable', 'boolean'],
        ]);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('delivery-proofs', 'public');
        }

        DB::transaction(function () use ($order, $validated, $photoPath, $request) {
            // Create or update delivery proof
            DeliveryProof::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'signature_data' => $validated['signature'] ?? null,
                    'photo_path' => $photoPath,
                    'recipient_name' => $validated['recipient_name'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'left_at_door' => $validated['left_at_door'] ?? false,
                    'captured_by' => $request->user()->id,
                    'captured_at' => now(),
                ]
            );

            // Mark order as delivered
            $order->update(['status' => Order::STATUS_DELIVERED]);

            $order->statusHistory()->create([
                'status' => Order::STATUS_DELIVERED,
                'changed_by' => $request->user()->id,
                'notes' => 'Delivery completed with proof',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Proof of delivery uploaded and order marked as delivered.',
            'order' => new OrderResource($order->fresh(['deliveryProof'])),
        ], 201);
    }

    /**
     * Get proof of delivery for an order.
     *
     * @requirement STAFF-014 View POD history
     */
    public function getProofOfDelivery(Request $request, int $id): JsonResponse
    {
        $this->authorizeStaff($request);

        $order = Order::with('deliveryProof.capturedBy')->findOrFail($id);

        if (!$order->deliveryProof) {
            return response()->json([
                'success' => false,
                'message' => 'No proof of delivery found for this order.',
            ], 404);
        }

        $proof = $order->deliveryProof;

        return response()->json([
            'success' => true,
            'proof_of_delivery' => [
                'id' => $proof->id,
                'order_id' => $proof->order_id,
                'signature_data' => $proof->signature_data,
                'photo_url' => $proof->photo_path ? asset('storage/' . $proof->photo_path) : null,
                'recipient_name' => $proof->recipient_name,
                'notes' => $proof->notes,
                'left_at_door' => $proof->left_at_door,
                'captured_by' => $proof->capturedBy ? [
                    'id' => $proof->capturedBy->id,
                    'name' => $proof->capturedBy->name,
                ] : null,
                'captured_at' => $proof->captured_at?->toIso8601String(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Waste Logging
    |--------------------------------------------------------------------------
    */

    /**
     * Log waste.
     *
     * @requirement STAFF-015 Create waste logging interface
     * @requirement STAFF-016 Record reason for waste
     */
    public function logWaste(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', Rule::in([
                WasteLog::REASON_DAMAGED,
                WasteLog::REASON_EXPIRED,
                WasteLog::REASON_QUALITY,
                WasteLog::REASON_OTHER,
            ])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $wasteLog = DB::transaction(function () use ($validated, $product, $request) {
            // Create waste log
            $wasteLog = WasteLog::create([
                'product_id' => $product->id,
                'logged_by' => $request->user()->id,
                'quantity' => $validated['quantity'],
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'unit_cost' => $product->price_aud,
                'total_cost' => $product->price_aud * $validated['quantity'],
            ]);

            // Reduce stock
            $product->decrement('stock', (int) $validated['quantity']);

            return $wasteLog;
        });

        return response()->json([
            'success' => true,
            'message' => 'Waste logged successfully.',
            'waste_log' => [
                'id' => $wasteLog->id,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                ],
                'quantity' => $wasteLog->quantity,
                'reason' => $wasteLog->reason,
                'notes' => $wasteLog->notes,
                'total_cost' => $wasteLog->total_cost,
                'logged_at' => $wasteLog->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Get waste logs.
     *
     * @requirement STAFF-017 View waste log history
     */
    public function getWasteLogs(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $query = WasteLog::with(['product', 'loggedBy']);

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by reason
        if ($request->has('reason')) {
            $query->where('reason', $request->reason);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $wasteLogs = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        // Calculate totals
        $totals = WasteLog::query()
            ->when($request->has('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->has('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->selectRaw('SUM(quantity) as total_quantity, SUM(total_cost) as total_cost')
            ->first();

        return response()->json([
            'success' => true,
            'waste_logs' => $wasteLogs->map(fn($log) => [
                'id' => $log->id,
                'product' => [
                    'id' => $log->product->id,
                    'name' => $log->product->name,
                    'sku' => $log->product->sku,
                ],
                'quantity' => $log->quantity,
                'reason' => $log->reason,
                'notes' => $log->notes,
                'unit_cost' => $log->unit_cost,
                'total_cost' => $log->total_cost,
                'logged_by' => $log->loggedBy ? [
                    'id' => $log->loggedBy->id,
                    'name' => $log->loggedBy->name,
                ] : null,
                'logged_at' => $log->created_at->toIso8601String(),
            ]),
            'totals' => [
                'total_quantity' => (float) ($totals->total_quantity ?? 0),
                'total_cost' => (float) ($totals->total_cost ?? 0),
            ],
            'meta' => [
                'current_page' => $wasteLogs->currentPage(),
                'last_page' => $wasteLogs->lastPage(),
                'per_page' => $wasteLogs->perPage(),
                'total' => $wasteLogs->total(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Stock Management
    |--------------------------------------------------------------------------
    */

    /**
     * Quick stock check.
     *
     * @requirement STAFF-018 Create quick stock check
     */
    public function getStockCheck(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $query = Product::query()->where('is_active', true);

        // Search by name or SKU
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by stock level
        if ($request->get('low_stock_only')) {
            $query->where('stock', '<=', 10);
        }

        if ($request->get('out_of_stock_only')) {
            $query->where('stock', '<=', 0);
        }

        $products = $query->orderBy('name')
            ->select('id', 'category_id', 'name', 'sku', 'stock', 'unit', 'price_aud')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'products' => $products->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock' => $product->stock,
                'unit' => $product->unit,
                'price' => $product->price_aud,
                'stock_status' => $this->getStockStatus($product->stock),
            ]),
            'summary' => [
                'total_products' => Product::where('is_active', true)->count(),
                'low_stock' => Product::where('is_active', true)->where('stock', '<=', 10)->where('stock', '>', 0)->count(),
                'out_of_stock' => Product::where('is_active', true)->where('stock', '<=', 0)->count(),
            ],
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Get stock status label.
     */
    private function getStockStatus(int $stock): string
    {
        if ($stock <= 0) {
            return 'out_of_stock';
        }
        if ($stock <= 10) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    /**
     * Update product stock.
     *
     * @requirement STAFF-019 Quick stock adjustment
     */
    public function updateStock(Request $request, int $productId): JsonResponse
    {
        $this->authorizeStaff($request);

        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'adjustment' => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $oldStock = $product->stock;
        $newStock = max(0, $product->stock + $validated['adjustment']);

        $product->update(['stock' => $newStock]);

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully.',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'adjustment' => $validated['adjustment'],
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Activity Tracking
    |--------------------------------------------------------------------------
    */

    /**
     * Get staff activity log.
     *
     * @requirement STAFF-020 Create activity log for staff actions
     * @requirement STAFF-021 Track order status changes
     */
    public function getActivityLog(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $staffId = $request->user()->id;
        $today = Carbon::today();

        // Get today's order status changes
        $statusChanges = DB::table('order_status_history')
            ->join('orders', 'order_status_history.order_id', '=', 'orders.id')
            ->where('order_status_history.changed_by', $staffId)
            ->whereDate('order_status_history.created_at', $today)
            ->select(
                'order_status_history.id',
                'order_status_history.order_id',
                'orders.order_number',
                'order_status_history.status',
                'order_status_history.notes',
                'order_status_history.created_at'
            )
            ->orderByDesc('order_status_history.created_at')
            ->limit(50)
            ->get();

        // Get today's waste logs
        $wasteLogs = WasteLog::with('product')
            ->where('logged_by', $staffId)
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Get today's deliveries completed
        $deliveriesCompleted = DeliveryProof::with('order')
            ->where('captured_by', $staffId)
            ->whereDate('captured_at', $today)
            ->orderByDesc('captured_at')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'date' => $today->toDateString(),
            'activity' => [
                'status_changes' => $statusChanges->map(fn($change) => [
                    'id' => $change->id,
                    'type' => 'status_change',
                    'order_id' => $change->order_id,
                    'order_number' => $change->order_number,
                    'status' => $change->status,
                    'notes' => $change->notes,
                    'timestamp' => $change->created_at,
                ]),
                'waste_logs' => $wasteLogs->map(fn($log) => [
                    'id' => $log->id,
                    'type' => 'waste_log',
                    'product_name' => $log->product->name,
                    'quantity' => $log->quantity,
                    'reason' => $log->reason,
                    'timestamp' => $log->created_at->toIso8601String(),
                ]),
                'deliveries' => $deliveriesCompleted->map(fn($proof) => [
                    'id' => $proof->id,
                    'type' => 'delivery_completed',
                    'order_id' => $proof->order_id,
                    'order_number' => $proof->order->order_number ?? null,
                    'recipient_name' => $proof->recipient_name,
                    'timestamp' => $proof->captured_at?->toIso8601String(),
                ]),
            ],
            'summary' => [
                'status_changes_count' => $statusChanges->count(),
                'waste_logs_count' => $wasteLogs->count(),
                'deliveries_count' => $deliveriesCompleted->count(),
            ],
        ]);
    }

    /**
     * Get staff performance stats.
     *
     * @requirement STAFF-022 Show staff performance metrics
     */
    public function getPerformanceStats(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $staffId = $request->user()->id;
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Weekly stats
        $weeklyStats = [
            'orders_processed' => DB::table('order_status_history')
                ->where('changed_by', $staffId)
                ->where('status', Order::STATUS_PROCESSING)
                ->where('created_at', '>=', $startOfWeek)
                ->count(),
            'deliveries_completed' => DeliveryProof::where('captured_by', $staffId)
                ->where('captured_at', '>=', $startOfWeek)
                ->count(),
            'waste_logs' => WasteLog::where('logged_by', $staffId)
                ->where('created_at', '>=', $startOfWeek)
                ->count(),
        ];

        // Monthly stats
        $monthlyStats = [
            'orders_processed' => DB::table('order_status_history')
                ->where('changed_by', $staffId)
                ->where('status', Order::STATUS_PROCESSING)
                ->where('created_at', '>=', $startOfMonth)
                ->count(),
            'deliveries_completed' => DeliveryProof::where('captured_by', $staffId)
                ->where('captured_at', '>=', $startOfMonth)
                ->count(),
            'waste_logs' => WasteLog::where('logged_by', $staffId)
                ->where('created_at', '>=', $startOfMonth)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'staff' => [
                'id' => $staffId,
                'name' => $request->user()->name,
            ],
            'this_week' => $weeklyStats,
            'this_month' => $monthlyStats,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Pickup Management
    |--------------------------------------------------------------------------
    */

    /**
     * Get today's pickups.
     *
     * @requirement STAFF-024 Create pickup management view
     */
    public function getTodaysPickups(Request $request): JsonResponse
    {
        $this->authorizeStaff($request);

        $today = Carbon::today();

        $pickups = Order::with(['user', 'items.product'])
            ->where('delivery_method', 'pickup')
            ->whereIn('status', [
                Order::STATUS_READY,
                Order::STATUS_DELIVERED, // Picked up
            ])
            ->where(function ($query) use ($today) {
                $query->whereDate('pickup_date', $today)
                    ->orWhere(function ($q) use ($today) {
                        $q->where('status', Order::STATUS_READY)
                            ->whereDate('pickup_date', '<=', $today);
                    });
            })
            ->orderByRaw("CASE WHEN status = 'ready' THEN 1 ELSE 2 END")
            ->orderBy('pickup_time_slot')
            ->get();

        // Group by status
        $grouped = [
            'awaiting_pickup' => $pickups->where('status', Order::STATUS_READY)->values(),
            'picked_up' => $pickups->where('status', Order::STATUS_DELIVERED)->values(),
        ];

        return response()->json([
            'success' => true,
            'date' => $today->toDateString(),
            'pickups' => [
                'awaiting' => OrderResource::collection($grouped['awaiting_pickup']),
                'picked_up' => OrderResource::collection($grouped['picked_up']),
            ],
            'summary' => [
                'total' => $pickups->count(),
                'awaiting' => $grouped['awaiting_pickup']->count(),
                'picked_up' => $grouped['picked_up']->count(),
            ],
        ]);
    }

    /**
     * Mark order as picked up.
     */
    public function markAsPickedUp(Request $request, int $id): JsonResponse
    {
        $this->authorizeStaff($request);

        $order = Order::findOrFail($id);

        if ($order->status !== Order::STATUS_READY) {
            return response()->json([
                'success' => false,
                'message' => 'Order must be in "ready" status to mark as picked up.',
            ], 422);
        }

        if ($order->delivery_method !== 'pickup') {
            return response()->json([
                'success' => false,
                'message' => 'This order is not a pickup order.',
            ], 422);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update(['status' => Order::STATUS_DELIVERED]);

            $order->statusHistory()->create([
                'status' => Order::STATUS_DELIVERED,
                'changed_by' => $request->user()->id,
                'notes' => 'Order picked up by customer',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order marked as picked up.',
            'order' => new OrderResource($order->fresh()),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    /**
     * Authorize that the user is staff.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function authorizeStaff(Request $request): void
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, self::STAFF_ROLES)) {
            abort(403, 'Unauthorized. Staff access required.');
        }
    }
}
