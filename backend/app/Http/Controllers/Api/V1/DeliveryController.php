<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeliveryProof;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * DeliveryController handles delivery management operations.
 *
 * @requirement DEL-001 Create delivery dashboard
 * @requirement DEL-002 Display all deliveries list
 * @requirement DEL-003 Filter deliveries
 * @requirement DEL-004 View delivery detail
 * @requirement DEL-005 Assign delivery to staff
 * @requirement DEL-006 Reassign delivery
 * @requirement DEL-007 View proof of delivery
 * @requirement DEL-008 Download POD document
 * @requirement DEL-009 Handle delivery issues
 * @requirement DEL-010 Create delivery zones management
 * @requirement DEL-011 Add delivery zone
 * @requirement DEL-012 Edit delivery zone
 * @requirement DEL-013 Delete delivery zone
 * @requirement DEL-014 Configure delivery fees
 * @requirement DEL-015 View delivery map
 * @requirement DEL-016 Generate delivery report
 * @requirement DEL-017 Export delivery data
 * @requirement DEL-018 Create delivery API endpoints
 */
class DeliveryController extends Controller
{
    /**
     * Get delivery dashboard overview.
     *
     * @requirement DEL-001 Create delivery dashboard
     */
    public function getDashboard(): JsonResponse
    {
        $today = now()->toDateString();

        // Today's delivery stats
        $todaysDeliveries = Order::where('delivery_method', 'delivery')
            ->whereDate('scheduled_date', $today)
            ->count();

        $pending = Order::where('delivery_method', 'delivery')
            ->whereDate('scheduled_date', $today)
            ->whereIn('status', ['pending', 'confirmed', 'preparing'])
            ->count();

        $inProgress = Order::where('delivery_method', 'delivery')
            ->whereDate('scheduled_date', $today)
            ->where('status', 'out_for_delivery')
            ->count();

        $completed = Order::where('delivery_method', 'delivery')
            ->whereDate('scheduled_date', $today)
            ->where('status', 'delivered')
            ->count();

        // Orders with issues
        $issues = Order::where('delivery_method', 'delivery')
            ->whereNotNull('delivery_issue')
            ->whereNull('delivery_issue_resolved_at')
            ->count();

        // Weekly stats
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $weeklyDeliveries = Order::where('delivery_method', 'delivery')
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$weekStart, $weekEnd])
            ->count();

        // Staff performance (top 5)
        $staffPerformance = Order::where('delivery_method', 'delivery')
            ->where('status', 'delivered')
            ->whereNotNull('assigned_to')
            ->whereBetween('delivered_at', [$weekStart, $weekEnd])
            ->select('assigned_to', DB::raw('COUNT(*) as deliveries_count'))
            ->groupBy('assigned_to')
            ->orderBy('deliveries_count', 'desc')
            ->limit(5)
            ->with('assignedTo:id,name')
            ->get()
            ->map(fn($item) => [
                'staff_id' => $item->assigned_to,
                'staff_name' => $item->assignedTo?->name ?? 'Unknown',
                'deliveries_count' => $item->deliveries_count,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'today' => [
                    'total' => $todaysDeliveries,
                    'pending' => $pending,
                    'in_progress' => $inProgress,
                    'completed' => $completed,
                    'issues' => $issues,
                ],
                'weekly' => [
                    'total_delivered' => $weeklyDeliveries,
                    'start_date' => $weekStart->toDateString(),
                    'end_date' => $weekEnd->toDateString(),
                ],
                'staff_performance' => $staffPerformance,
            ],
        ]);
    }

    /**
     * Get all deliveries with filtering.
     *
     * @requirement DEL-002 Display all deliveries list
     * @requirement DEL-003 Filter deliveries
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::where('delivery_method', 'delivery')
            ->with(['user:id,name,email', 'assignedTo:id,name', 'address']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('scheduled_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('scheduled_date', '<=', $request->end_date);
        }

        // Filter by assigned staff
        if ($request->filled('staff_id')) {
            $query->where('assigned_to', $request->staff_id);
        }

        // Filter by issues
        if ($request->filled('has_issue')) {
            $hasIssue = filter_var($request->has_issue, FILTER_VALIDATE_BOOLEAN);
            if ($hasIssue) {
                $query->whereNotNull('delivery_issue');
            } else {
                $query->whereNull('delivery_issue');
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'scheduled_date');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = min((int) $request->input('per_page', 20), 100);
        $deliveries = $query->paginate($perPage);

        $data = collect($deliveries->items())->map(fn($order) => [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer' => [
                'id' => $order->user?->id,
                'name' => $order->user?->name,
                'email' => $order->user?->email,
            ],
            'address' => $order->address ? [
                'street' => $order->address->street_address,
                'suburb' => $order->address->suburb,
                'state' => $order->address->state,
                'postcode' => $order->address->postcode,
            ] : null,
            'status' => $order->status,
            'scheduled_date' => $order->scheduled_date,
            'time_slot' => $order->time_slot,
            'assigned_to' => $order->assignedTo ? [
                'id' => $order->assignedTo->id,
                'name' => $order->assignedTo->name,
            ] : null,
            'has_pod' => $order->deliveryProof()->exists(),
            'has_issue' => $order->delivery_issue !== null,
            'total' => (float) $order->total,
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $deliveries->currentPage(),
                'last_page' => $deliveries->lastPage(),
                'per_page' => $deliveries->perPage(),
                'total' => $deliveries->total(),
            ],
        ]);
    }

    /**
     * Get single delivery detail.
     *
     * @requirement DEL-004 View delivery detail
     */
    public function show(int $id): JsonResponse
    {
        $order = Order::where('delivery_method', 'delivery')
            ->with([
                'user:id,name,email,phone',
                'assignedTo:id,name,email,phone',
                'address',
                'items.product:id,name,sku',
                'deliveryProof.capturedBy:id,name',
                'statusHistory',
            ])
            ->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer' => [
                    'id' => $order->user?->id,
                    'name' => $order->user?->name,
                    'email' => $order->user?->email,
                    'phone' => $order->user?->phone,
                ],
                'address' => $order->address ? [
                    'street' => $order->address->street_address,
                    'suburb' => $order->address->suburb,
                    'state' => $order->address->state,
                    'postcode' => $order->address->postcode,
                    'delivery_instructions' => $order->delivery_instructions,
                ] : null,
                'status' => $order->status,
                'scheduled_date' => $order->scheduled_date,
                'time_slot' => $order->time_slot,
                'assigned_to' => $order->assignedTo ? [
                    'id' => $order->assignedTo->id,
                    'name' => $order->assignedTo->name,
                    'phone' => $order->assignedTo->phone,
                ] : null,
                'items' => $order->items->map(fn($item) => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                ]),
                'subtotal' => (float) $order->subtotal,
                'delivery_fee' => (float) $order->delivery_fee,
                'discount' => (float) $order->discount,
                'total' => (float) $order->total,
                'delivery_proof' => $order->deliveryProof ? [
                    'captured_at' => $order->deliveryProof->captured_at,
                    'recipient_name' => $order->deliveryProof->recipient_name,
                    'left_at_door' => $order->deliveryProof->left_at_door,
                    'notes' => $order->deliveryProof->notes,
                    'has_photo' => $order->deliveryProof->photo_path !== null,
                    'has_signature' => $order->deliveryProof->signature_path !== null,
                    'captured_by' => $order->deliveryProof->capturedBy?->name,
                ] : null,
                'issue' => $order->delivery_issue ? [
                    'description' => $order->delivery_issue,
                    'reported_at' => $order->delivery_issue_reported_at,
                    'resolved' => $order->delivery_issue_resolved_at !== null,
                    'resolved_at' => $order->delivery_issue_resolved_at,
                    'resolution' => $order->delivery_issue_resolution,
                ] : null,
                'timeline' => $order->statusHistory?->map(fn($h) => [
                    'status' => $h->status,
                    'changed_at' => $h->created_at,
                    'notes' => $h->notes,
                ]) ?? [],
                'created_at' => $order->created_at,
            ],
        ]);
    }

    /**
     * Assign delivery to staff.
     *
     * @requirement DEL-005 Assign delivery to staff
     * @requirement DEL-006 Reassign delivery
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:users,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $order = Order::where('delivery_method', 'delivery')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery not found',
            ], 404);
        }

        // Verify staff member has staff role
        $staff = User::where('id', $validated['staff_id'])
            ->where('role', 'staff')
            ->first();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Selected user is not a staff member',
            ], 400);
        }

        $previousAssignee = $order->assignedTo?->name;
        $order->update(['assigned_to' => $validated['staff_id']]);

        $message = $previousAssignee
            ? "Delivery reassigned from {$previousAssignee} to {$staff->name}"
            : "Delivery assigned to {$staff->name}";

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'assigned_to' => [
                    'id' => $staff->id,
                    'name' => $staff->name,
                ],
            ],
        ]);
    }

    /**
     * Get proof of delivery.
     *
     * @requirement DEL-007 View proof of delivery
     */
    public function getProofOfDelivery(int $id): JsonResponse
    {
        $order = Order::where('delivery_method', 'delivery')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery not found',
            ], 404);
        }

        $pod = $order->deliveryProof()->with('capturedBy:id,name')->first();

        if (!$pod) {
            return response()->json([
                'success' => false,
                'message' => 'Proof of delivery not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pod->id,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'captured_at' => $pod->captured_at,
                'recipient_name' => $pod->recipient_name,
                'left_at_door' => $pod->left_at_door,
                'notes' => $pod->notes,
                'photo_url' => $pod->photo_path ? url("storage/{$pod->photo_path}") : null,
                'signature_url' => $pod->signature_path ? url("storage/{$pod->signature_path}") : null,
                'captured_by' => $pod->capturedBy?->name,
            ],
        ]);
    }

    /**
     * Handle delivery issue.
     *
     * @requirement DEL-009 Handle delivery issues
     */
    public function updateIssue(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'resolved' => ['required', 'boolean'],
            'resolution' => ['required_if:resolved,true', 'nullable', 'string', 'max:1000'],
        ]);

        $order = Order::where('delivery_method', 'delivery')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery not found',
            ], 404);
        }

        if (!$order->delivery_issue) {
            return response()->json([
                'success' => false,
                'message' => 'No issue reported for this delivery',
            ], 400);
        }

        if ($validated['resolved']) {
            $order->update([
                'delivery_issue_resolved_at' => now(),
                'delivery_issue_resolution' => $validated['resolution'],
            ]);

            $message = 'Delivery issue marked as resolved';
        } else {
            $order->update([
                'delivery_issue_resolved_at' => null,
                'delivery_issue_resolution' => null,
            ]);

            $message = 'Delivery issue marked as unresolved';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'order_id' => $order->id,
                'issue' => $order->delivery_issue,
                'resolved' => $validated['resolved'],
                'resolution' => $validated['resolution'] ?? null,
            ],
        ]);
    }

    /**
     * Get all delivery zones.
     *
     * @requirement DEL-010 Create delivery zones management
     */
    public function getZones(Request $request): JsonResponse
    {
        $query = DeliveryZone::query();

        // Filter by active status
        if ($request->filled('active')) {
            $active = filter_var($request->active, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $active);
        }

        $zones = $query->orderBy('name')->get()->map(fn($zone) => [
            'id' => $zone->id,
            'name' => $zone->name,
            'suburbs' => $zone->suburbs,
            'delivery_fee' => (float) $zone->delivery_fee,
            'free_delivery_threshold' => $zone->free_delivery_threshold ? (float) $zone->free_delivery_threshold : null,
            'estimated_days' => $zone->estimated_days,
            'is_active' => $zone->is_active,
        ]);

        return response()->json([
            'success' => true,
            'data' => $zones,
        ]);
    }

    /**
     * Create a new delivery zone.
     *
     * @requirement DEL-011 Add delivery zone
     */
    public function createZone(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:delivery_zones,name'],
            'suburbs' => ['required', 'array', 'min:1'],
            'suburbs.*' => ['string', 'max:100'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'free_delivery_threshold' => ['nullable', 'numeric', 'min:0'],
            'estimated_days' => ['required', 'integer', 'min:1', 'max:14'],
            'is_active' => ['boolean'],
        ]);

        $zone = DeliveryZone::create([
            'name' => $validated['name'],
            'suburbs' => $validated['suburbs'],
            'delivery_fee' => $validated['delivery_fee'],
            'free_delivery_threshold' => $validated['free_delivery_threshold'] ?? null,
            'estimated_days' => $validated['estimated_days'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery zone created successfully',
            'data' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'suburbs' => $zone->suburbs,
                'delivery_fee' => (float) $zone->delivery_fee,
                'free_delivery_threshold' => $zone->free_delivery_threshold ? (float) $zone->free_delivery_threshold : null,
                'estimated_days' => $zone->estimated_days,
                'is_active' => $zone->is_active,
            ],
        ], 201);
    }

    /**
     * Update a delivery zone.
     *
     * @requirement DEL-012 Edit delivery zone
     */
    public function updateZone(Request $request, int $id): JsonResponse
    {
        $zone = DeliveryZone::find($id);

        if (!$zone) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery zone not found',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('delivery_zones', 'name')->ignore($id)],
            'suburbs' => ['sometimes', 'array', 'min:1'],
            'suburbs.*' => ['string', 'max:100'],
            'delivery_fee' => ['sometimes', 'numeric', 'min:0'],
            'free_delivery_threshold' => ['nullable', 'numeric', 'min:0'],
            'estimated_days' => ['sometimes', 'integer', 'min:1', 'max:14'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $zone->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Delivery zone updated successfully',
            'data' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'suburbs' => $zone->suburbs,
                'delivery_fee' => (float) $zone->delivery_fee,
                'free_delivery_threshold' => $zone->free_delivery_threshold ? (float) $zone->free_delivery_threshold : null,
                'estimated_days' => $zone->estimated_days,
                'is_active' => $zone->is_active,
            ],
        ]);
    }

    /**
     * Delete a delivery zone.
     *
     * @requirement DEL-013 Delete delivery zone
     */
    public function deleteZone(int $id): JsonResponse
    {
        $zone = DeliveryZone::find($id);

        if (!$zone) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery zone not found',
            ], 404);
        }

        // Check for active orders in this zone
        $activeOrders = Order::where('delivery_zone_id', $id)
            ->whereNotIn('status', ['delivered', 'cancelled', 'refunded'])
            ->count();

        if ($activeOrders > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete zone with {$activeOrders} active orders",
            ], 400);
        }

        $zone->delete();

        return response()->json([
            'success' => true,
            'message' => 'Delivery zone deleted successfully',
        ]);
    }

    /**
     * Get delivery settings.
     *
     * @requirement DEL-014 Configure delivery fees
     */
    public function getSettings(): JsonResponse
    {
        // Get settings from database or use defaults
        $settings = [
            'free_delivery_threshold' => (float) (Setting::getValue('delivery.free_threshold', 100.00)),
            'per_km_rate' => (float) (Setting::getValue('delivery.per_km_rate', 0.15)),
            'base_fee' => (float) (Setting::getValue('delivery.base_fee', 5.00)),
            'max_delivery_distance_km' => (int) (Setting::getValue('delivery.max_distance', 50)),
            'store_address' => Setting::getValue('store.address', '6/1053 Old Princes Highway, Engadine, NSW 2233'),
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update delivery settings.
     *
     * @requirement DEL-014 Configure delivery fees
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'free_delivery_threshold' => ['sometimes', 'numeric', 'min:0'],
            'per_km_rate' => ['sometimes', 'numeric', 'min:0'],
            'base_fee' => ['sometimes', 'numeric', 'min:0'],
            'max_delivery_distance_km' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ]);

        // Save settings
        if (isset($validated['free_delivery_threshold'])) {
            Setting::setValue('delivery.free_threshold', $validated['free_delivery_threshold'], 'float', 'delivery');
        }
        if (isset($validated['per_km_rate'])) {
            Setting::setValue('delivery.per_km_rate', $validated['per_km_rate'], 'float', 'delivery');
        }
        if (isset($validated['base_fee'])) {
            Setting::setValue('delivery.base_fee', $validated['base_fee'], 'float', 'delivery');
        }
        if (isset($validated['max_delivery_distance_km'])) {
            Setting::setValue('delivery.max_distance', $validated['max_delivery_distance_km'], 'integer', 'delivery');
        }

        return response()->json([
            'success' => true,
            'message' => 'Delivery settings updated successfully',
            'data' => [
                'free_delivery_threshold' => (float) Setting::getValue('delivery.free_threshold', 100.00),
                'per_km_rate' => (float) Setting::getValue('delivery.per_km_rate', 0.15),
                'base_fee' => (float) Setting::getValue('delivery.base_fee', 5.00),
                'max_delivery_distance_km' => (int) Setting::getValue('delivery.max_distance', 50),
            ],
        ]);
    }

    /**
     * Get delivery map data.
     *
     * @requirement DEL-015 View delivery map
     */
    public function getMapData(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->toDateString());

        $deliveries = Order::where('delivery_method', 'delivery')
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['confirmed', 'preparing', 'ready', 'out_for_delivery'])
            ->with(['user:id,name', 'address', 'assignedTo:id,name'])
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user?->name,
                'address' => $order->address ? [
                    'full' => "{$order->address->street_address}, {$order->address->suburb}, {$order->address->state} {$order->address->postcode}",
                    'latitude' => $order->address->latitude,
                    'longitude' => $order->address->longitude,
                ] : null,
                'status' => $order->status,
                'time_slot' => $order->time_slot,
                'assigned_to' => $order->assignedTo?->name,
            ]);

        // Store location
        $storeLocation = [
            'address' => '6/1053 Old Princes Highway, Engadine, NSW 2233, Australia',
            'latitude' => -34.0636,
            'longitude' => 151.0127,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'store' => $storeLocation,
                'deliveries' => $deliveries,
                'count' => $deliveries->count(),
            ],
        ]);
    }

    /**
     * Generate delivery report.
     *
     * @requirement DEL-016 Generate delivery report
     */
    public function getReport(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        // Deliveries by status
        $byStatus = Order::where('delivery_method', 'delivery')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Deliveries by zone
        $byZone = Order::where('delivery_method', 'delivery')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->join('delivery_zones', 'orders.delivery_zone_id', '=', 'delivery_zones.id')
            ->select('delivery_zones.name as zone', DB::raw('COUNT(*) as count'), DB::raw('SUM(orders.delivery_fee) as total_fees'))
            ->groupBy('delivery_zones.id', 'delivery_zones.name')
            ->get();

        // Staff performance
        $staffPerformance = Order::where('delivery_method', 'delivery')
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->whereNotNull('assigned_to')
            ->select('assigned_to', DB::raw('COUNT(*) as deliveries_count'))
            ->groupBy('assigned_to')
            ->with('assignedTo:id,name')
            ->get()
            ->map(fn($item) => [
                'staff_name' => $item->assignedTo?->name ?? 'Unknown',
                'deliveries_count' => $item->deliveries_count,
            ]);

        // Issues summary
        $issuesSummary = [
            'total_reported' => Order::where('delivery_method', 'delivery')
                ->whereNotNull('delivery_issue')
                ->whereBetween('delivery_issue_reported_at', [$startDate, $endDate])
                ->count(),
            'resolved' => Order::where('delivery_method', 'delivery')
                ->whereNotNull('delivery_issue')
                ->whereNotNull('delivery_issue_resolved_at')
                ->whereBetween('delivery_issue_reported_at', [$startDate, $endDate])
                ->count(),
            'pending' => Order::where('delivery_method', 'delivery')
                ->whereNotNull('delivery_issue')
                ->whereNull('delivery_issue_resolved_at')
                ->whereBetween('delivery_issue_reported_at', [$startDate, $endDate])
                ->count(),
        ];

        // Revenue from delivery fees
        $deliveryRevenue = Order::where('delivery_method', 'delivery')
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->sum('delivery_fee');

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'summary' => [
                    'total_deliveries' => array_sum($byStatus->toArray()),
                    'completed' => $byStatus->get('delivered', 0),
                    'delivery_revenue' => (float) $deliveryRevenue,
                ],
                'by_status' => $byStatus,
                'by_zone' => $byZone,
                'staff_performance' => $staffPerformance,
                'issues' => $issuesSummary,
            ],
        ]);
    }

    /**
     * Export delivery data as PDF.
     *
     * @requirement DEL-017 Export delivery data
     */
    public function export(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $deliveries = Order::where('delivery_method', 'delivery')
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->with(['user:id,name', 'address', 'assignedTo:id,name'])
            ->orderBy('scheduled_date')
            ->get()
            ->map(fn($order) => [
                'order_number' => $order->order_number,
                'customer' => $order->user?->name,
                'address' => $order->address
                    ? "{$order->address->street_address}, {$order->address->suburb} {$order->address->postcode}"
                    : 'N/A',
                'scheduled_date' => $order->scheduled_date,
                'time_slot' => $order->time_slot,
                'status' => $order->status,
                'assigned_to' => $order->assignedTo?->name ?? 'Unassigned',
                'delivery_fee' => (float) $order->delivery_fee,
                'total' => (float) $order->total,
            ]);

        // Summary
        $summary = [
            'period' => "{$startDate} to {$endDate}",
            'total_deliveries' => $deliveries->count(),
            'total_delivery_fees' => $deliveries->sum('delivery_fee'),
            'total_order_value' => $deliveries->sum('total'),
            'generated_at' => now()->toIso8601String(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Export data generated',
            'data' => [
                'summary' => $summary,
                'deliveries' => $deliveries,
            ],
        ]);
    }
}
