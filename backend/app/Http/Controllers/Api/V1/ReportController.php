<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\DeliveryZone;
use App\Models\InventoryLog;
use App\Models\WasteLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ReportController handles all reports and analytics endpoints.
 *
 * @requirement RPT-001 to RPT-022 Reports & Analytics
 */
class ReportController extends Controller
{
    /**
     * Get reports dashboard with quick stats.
     *
     * @requirement RPT-001 Create reports dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Calculate previous period for comparison
        $periodDays = $startDate->diffInDays($endDate) + 1;
        $prevStart = (clone $startDate)->subDays($periodDays);
        $prevEnd = (clone $endDate)->subDays($periodDays);

        // Current period stats
        $currentRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('total');

        $currentOrders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->count();

        $currentCustomers = User::where('role', 'customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $currentAvgOrder = $currentOrders > 0 ? $currentRevenue / $currentOrders : 0;

        // Previous period stats for comparison
        $prevRevenue = Order::whereBetween('created_at', [$prevStart, $prevEnd])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('total');

        $prevOrders = Order::whereBetween('created_at', [$prevStart, $prevEnd])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->count();

        $prevCustomers = User::where('role', 'customer')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $prevAvgOrder = $prevOrders > 0 ? $prevRevenue / $prevOrders : 0;

        // Calculate percentage changes
        $revenueChange = $prevRevenue > 0 ? (($currentRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
        $ordersChange = $prevOrders > 0 ? (($currentOrders - $prevOrders) / $prevOrders) * 100 : 0;
        $customersChange = $prevCustomers > 0 ? (($currentCustomers - $prevCustomers) / $prevCustomers) * 100 : 0;
        $avgOrderChange = $prevAvgOrder > 0 ? (($currentAvgOrder - $prevAvgOrder) / $prevAvgOrder) * 100 : 0;

        // Top products
        $topProducts = OrderItem::select(
            'products.id',
            'products.name',
            DB::raw('SUM(order_items.quantity) as total_quantity'),
            DB::raw('SUM(order_items.total_price) as total_revenue')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        // Top customers
        $topCustomers = Order::select(
            'users.id',
            'users.name',
            DB::raw('SUM(orders.total) as total_spent'),
            DB::raw('COUNT(orders.id) as order_count')
        )
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                    'period_days' => $periodDays,
                ],
                'quick_stats' => [
                    'revenue' => [
                        'value' => round((float) $currentRevenue, 2),
                        'change' => round($revenueChange, 1),
                    ],
                    'orders' => [
                        'value' => $currentOrders,
                        'change' => round($ordersChange, 1),
                    ],
                    'customers' => [
                        'value' => $currentCustomers,
                        'change' => round($customersChange, 1),
                    ],
                    'avg_order' => [
                        'value' => round($currentAvgOrder, 2),
                        'change' => round($avgOrderChange, 1),
                    ],
                ],
                'top_products' => $topProducts,
                'top_customers' => $topCustomers,
            ],
        ]);
    }

    /**
     * Get sales summary report.
     *
     * @requirement RPT-002 Create sales summary report
     */
    public function salesSummary(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->get();

        $totalRevenue = $orders->sum('total');
        $totalOrders = $orders->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $totalItems = OrderItem::whereIn('order_id', $orders->pluck('id'))->sum('quantity');

        // Revenue by status
        $revenueByStatus = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // Daily revenue trend (SQLite compatible)
        $dailyRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->select(
                DB::raw("date(created_at) as date"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy(DB::raw('date(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'summary' => [
                    'total_revenue' => round((float) $totalRevenue, 2),
                    'total_orders' => $totalOrders,
                    'avg_order_value' => round($avgOrderValue, 2),
                    'total_items_sold' => (int) $totalItems,
                ],
                'revenue_by_status' => $revenueByStatus,
                'daily_revenue' => $dailyRevenue,
            ],
        ]);
    }

    /**
     * Get revenue by period report.
     *
     * @requirement RPT-003 Create revenue by period report
     */
    public function revenue(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $groupBy = $request->input('group_by', 'day'); // day, week, month

        // SQLite compatible date formatting
        $periodSelect = match ($groupBy) {
            'week' => "strftime('%Y-%W', created_at)",
            'month' => "strftime('%Y-%m', created_at)",
            default => "date(created_at)",
        };

        $revenueData = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->select(
                DB::raw("{$periodSelect} as period"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('SUM(subtotal) as subtotal'),
                DB::raw('SUM(delivery_fee) as delivery_fees'),
                DB::raw('SUM(discount) as discounts'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $totals = [
            'revenue' => (float) $revenueData->sum('revenue'),
            'subtotal' => (float) $revenueData->sum('subtotal'),
            'delivery_fees' => (float) $revenueData->sum('delivery_fees'),
            'discounts' => (float) $revenueData->sum('discounts'),
            'order_count' => (int) $revenueData->sum('order_count'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'group_by' => $groupBy,
                'periods' => $revenueData,
                'totals' => $totals,
            ],
        ]);
    }

    /**
     * Get orders by status report.
     *
     * @requirement RPT-004 Create orders by status report
     */
    public function orders(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        $ordersByStatus = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                'status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('status')
            ->get();

        $totalOrders = $ordersByStatus->sum('count');
        $ordersByStatus = $ordersByStatus->map(function ($item) use ($totalOrders) {
            $item->percentage = $totalOrders > 0 ? round(($item->count / $totalOrders) * 100, 1) : 0;
            return $item;
        });

        // Recent orders
        $recentOrders = Order::with(['user:id,name,email'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'order_number', 'user_id', 'total', 'status', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'total_orders' => $totalOrders,
                'by_status' => $ordersByStatus,
                'recent_orders' => $recentOrders,
            ],
        ]);
    }

    /**
     * Get product sales report.
     *
     * @requirement RPT-005 Create product sales report
     */
    public function products(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        $productSales = OrderItem::select(
            'products.id',
            'products.name',
            'products.slug',
            'products.sku',
            'categories.name as category_name',
            DB::raw('SUM(order_items.quantity) as quantity_sold'),
            DB::raw('SUM(order_items.total_price) as revenue'),
            DB::raw('COUNT(DISTINCT order_items.order_id) as order_count')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->groupBy('products.id', 'products.name', 'products.slug', 'products.sku', 'categories.name')
            ->orderByDesc('revenue')
            ->paginate($request->input('per_page', 15));

        $totalRevenue = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->sum('order_items.total_price');

        // Add percentage of total to each product
        $productSales->getCollection()->transform(function ($item) use ($totalRevenue) {
            $item->percentage = $totalRevenue > 0 ? round(($item->revenue / $totalRevenue) * 100, 1) : 0;
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'total_revenue' => round((float) $totalRevenue, 2),
                'products' => $productSales,
            ],
        ]);
    }

    /**
     * Get category sales report.
     *
     * @requirement RPT-006 Create category sales report
     */
    public function categories(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        $categorySales = OrderItem::select(
            'categories.id',
            'categories.name',
            'categories.slug',
            DB::raw('SUM(order_items.quantity) as quantity_sold'),
            DB::raw('SUM(order_items.total_price) as revenue'),
            DB::raw('COUNT(DISTINCT order_items.order_id) as order_count'),
            DB::raw('COUNT(DISTINCT products.id) as products_sold')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->groupBy('categories.id', 'categories.name', 'categories.slug')
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue = $categorySales->sum('revenue');
        $categorySales->transform(function ($item) use ($totalRevenue) {
            $item->percentage = $totalRevenue > 0 ? round(($item->revenue / $totalRevenue) * 100, 1) : 0;
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'total_revenue' => round((float) $totalRevenue, 2),
                'categories' => $categorySales,
            ],
        ]);
    }

    /**
     * Get top products report.
     *
     * @requirement RPT-007 Create top products report
     */
    public function topProducts(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $limit = $request->input('limit', 10);

        // Top by revenue
        $topByRevenue = OrderItem::select(
            'products.id',
            'products.name',
            'products.sku',
            DB::raw('SUM(order_items.quantity) as quantity_sold'),
            DB::raw('SUM(order_items.total_price) as revenue')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        // Top by quantity
        $topByQuantity = OrderItem::select(
            'products.id',
            'products.name',
            'products.sku',
            DB::raw('SUM(order_items.quantity) as quantity_sold'),
            DB::raw('SUM(order_items.total_price) as revenue')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('quantity_sold')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'top_by_revenue' => $topByRevenue,
                'top_by_quantity' => $topByQuantity,
            ],
        ]);
    }

    /**
     * Get low performing products report.
     *
     * @requirement RPT-008 Create low performing products
     */
    public function lowPerformingProducts(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $limit = $request->input('limit', 10);

        // Products with sales in period
        $productsWithSales = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->pluck('order_items.product_id')
            ->unique();

        // Low selling products (products with sales but low quantity)
        $lowSelling = OrderItem::select(
            'products.id',
            'products.name',
            'products.sku',
            'products.stock',
            DB::raw('SUM(order_items.quantity) as quantity_sold'),
            DB::raw('SUM(order_items.total_price) as revenue')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->where('products.is_active', true)
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.stock')
            ->orderBy('revenue')
            ->limit($limit)
            ->get();

        // Products with no sales in period
        $noSales = Product::where('is_active', true)
            ->whereNotIn('id', $productsWithSales)
            ->select('id', 'name', 'sku', 'stock', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                $product->quantity_sold = 0;
                $product->revenue = 0;
                return $product;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'low_selling' => $lowSelling,
                'no_sales' => $noSales,
            ],
        ]);
    }

    /**
     * Get customer report.
     *
     * @requirement RPT-009 Create customer report
     */
    public function customers(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // New customers in period
        $newCustomers = User::where('role', 'customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Active customers (made an order in period)
        $activeCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->distinct('user_id')
            ->count('user_id');

        // Returning customers (ordered before the period and in the period)
        $returningCustomerIds = Order::where('created_at', '<', $startDate)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->pluck('user_id')
            ->unique();

        $returningCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->whereIn('user_id', $returningCustomerIds)
            ->distinct('user_id')
            ->count('user_id');

        // Average customer spend
        $avgSpend = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->avg('total') ?? 0;

        // Top customers
        $topCustomers = Order::select(
            'users.id',
            'users.name',
            'users.email',
            DB::raw('SUM(orders.total) as total_spent'),
            DB::raw('COUNT(orders.id) as order_count'),
            DB::raw('AVG(orders.total) as avg_order')
        )
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'summary' => [
                    'new_customers' => $newCustomers,
                    'active_customers' => $activeCustomers,
                    'returning_customers' => $returningCustomers,
                    'new_customer_rate' => $activeCustomers > 0 ? round((($activeCustomers - $returningCustomers) / $activeCustomers) * 100, 1) : 0,
                    'avg_spend' => round((float) $avgSpend, 2),
                ],
                'top_customers' => $topCustomers,
            ],
        ]);
    }

    /**
     * Get customer acquisition report.
     *
     * @requirement RPT-010 Create customer acquisition report
     */
    public function customerAcquisition(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $groupBy = $request->input('group_by', 'day');

        // SQLite compatible date formatting
        $periodSelect = match ($groupBy) {
            'week' => "strftime('%Y-%W', created_at)",
            'month' => "strftime('%Y-%m', created_at)",
            default => "date(created_at)",
        };

        $newCustomersByPeriod = User::where('role', 'customer')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("{$periodSelect} as period"),
                DB::raw('COUNT(*) as new_customers')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Total new customers
        $totalNew = $newCustomersByPeriod->sum('new_customers');

        // First order conversion rate
        $customersWithOrders = User::where('role', 'customer')
            ->whereBetween('users.created_at', [$startDate, $endDate])
            ->whereHas('orders', function ($query) {
                $query->whereNotIn('status', ['cancelled', 'refunded']);
            })
            ->count();

        $conversionRate = $totalNew > 0 ? round(($customersWithOrders / $totalNew) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'group_by' => $groupBy,
                'total_new_customers' => $totalNew,
                'conversion_rate' => $conversionRate,
                'customers_with_orders' => $customersWithOrders,
                'by_period' => $newCustomersByPeriod,
            ],
        ]);
    }

    /**
     * Get staff performance report.
     *
     * @requirement RPT-011 Create staff performance report
     */
    public function staff(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        $staffPerformance = User::whereIn('role', ['staff', 'admin'])
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.role'
            )
            ->get()
            ->map(function ($staff) use ($startDate, $endDate) {
                // Orders processed (assigned to this staff)
                $ordersProcessed = Order::where('assigned_staff_id', $staff->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                // Deliveries completed
                $deliveriesCompleted = Order::where('assigned_staff_id', $staff->id)
                    ->where('status', 'delivered')
                    ->whereBetween('delivered_at', [$startDate, $endDate])
                    ->count();

                // Waste logged
                $wasteLogged = WasteLog::where('logged_by', $staff->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                // Total order value processed
                $orderValueProcessed = Order::where('assigned_staff_id', $staff->id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereNotIn('status', ['cancelled', 'refunded'])
                    ->sum('total');

                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'email' => $staff->email,
                    'role' => $staff->role,
                    'orders_processed' => $ordersProcessed,
                    'deliveries_completed' => $deliveriesCompleted,
                    'waste_logged' => $wasteLogged,
                    'order_value_processed' => round((float) $orderValueProcessed, 2),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'staff' => $staffPerformance,
            ],
        ]);
    }

    /**
     * Get delivery performance report.
     *
     * @requirement RPT-012 Create delivery performance report
     */
    public function deliveries(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Total deliveries (orders with a delivery zone assigned)
        $totalDeliveries = Order::whereNotNull('delivery_zone_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->count();

        // Completed deliveries
        $completedDeliveries = Order::whereNotNull('delivery_zone_id')
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->count();

        // On-time deliveries (delivered on or before scheduled date) - SQLite compatible
        $onTimeDeliveries = Order::whereNotNull('delivery_zone_id')
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$startDate, $endDate])
            ->whereNotNull('scheduled_date')
            ->whereRaw("date(delivered_at) <= scheduled_date")
            ->count();

        $onTimeRate = $completedDeliveries > 0 ? round(($onTimeDeliveries / $completedDeliveries) * 100, 1) : 0;

        // By zone
        $byZone = Order::select(
            'delivery_zones.name as zone_name',
            DB::raw('COUNT(orders.id) as deliveries'),
            DB::raw('SUM(orders.delivery_fee) as delivery_revenue')
        )
            ->join('delivery_zones', 'orders.delivery_zone_id', '=', 'delivery_zones.id')
            ->whereNotNull('orders.delivery_zone_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotIn('orders.status', ['cancelled', 'refunded'])
            ->groupBy('delivery_zones.name')
            ->orderByDesc('deliveries')
            ->get();

        // Average delivery fee
        $avgDeliveryFee = Order::whereNotNull('delivery_zone_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->avg('delivery_fee') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'summary' => [
                    'total_deliveries' => $totalDeliveries,
                    'completed_deliveries' => $completedDeliveries,
                    'on_time_deliveries' => $onTimeDeliveries,
                    'on_time_rate' => $onTimeRate,
                    'avg_delivery_fee' => round((float) $avgDeliveryFee, 2),
                ],
                'by_zone' => $byZone,
            ],
        ]);
    }

    /**
     * Get inventory report.
     *
     * @requirement RPT-013 Create inventory report
     */
    public function inventory(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Current stock levels
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();

        // Low stock check - simplified for compatibility
        $lowStockProducts = Product::where('is_active', true)
            ->where('stock', '<=', 10)
            ->count();

        $outOfStockProducts = Product::where('is_active', true)
            ->where('stock', '<=', 0)
            ->count();

        // Stock movements (using correct column names from schema)
        $stockReceived = InventoryLog::where('type', 'addition')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('quantity');

        $stockSold = InventoryLog::where('type', 'deduction')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('quantity');

        $stockAdjusted = InventoryLog::where('type', 'adjustment')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('quantity');

        // Waste summary
        $wasteCount = WasteLog::whereBetween('created_at', [$startDate, $endDate])->count();
        $wasteQuantity = WasteLog::whereBetween('created_at', [$startDate, $endDate])->sum('quantity');

        // Calculate waste value using price_aud column
        $wasteValue = WasteLog::join('products', 'waste_logs.product_id', '=', 'products.id')
            ->whereBetween('waste_logs.created_at', [$startDate, $endDate])
            ->selectRaw('SUM(waste_logs.quantity * products.price_aud) as value')
            ->value('value') ?? 0;

        // Stock turnover (simplified)
        $avgStock = Product::avg('stock') ?? 0;
        $turnover = $avgStock > 0 ? abs((float) $stockSold) / $avgStock : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'stock_levels' => [
                    'total_products' => $totalProducts,
                    'active_products' => $activeProducts,
                    'low_stock' => $lowStockProducts,
                    'out_of_stock' => $outOfStockProducts,
                ],
                'movements' => [
                    'stock_received' => abs((int) $stockReceived),
                    'stock_sold' => abs((int) $stockSold),
                    'stock_adjusted' => (int) $stockAdjusted,
                ],
                'waste' => [
                    'count' => $wasteCount,
                    'quantity' => (int) $wasteQuantity,
                    'estimated_value' => round((float) $wasteValue, 2),
                ],
                'turnover_rate' => round($turnover, 2),
            ],
        ]);
    }

    /**
     * Get financial summary report.
     *
     * @requirement RPT-014 Create financial summary report
     */
    public function financial(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Gross revenue
        $grossRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('total');

        // Subtotal (product revenue)
        $subtotal = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('subtotal');

        // Delivery fees collected
        $deliveryFees = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('delivery_fee');

        // Discounts given
        $discounts = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('discount');

        // Refunds
        $refunds = Order::where('status', 'refunded')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        // Net revenue
        $netRevenue = $grossRevenue - $refunds;

        // Daily breakdown (SQLite compatible)
        $dailyRevenue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->select(
                DB::raw('date(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('SUM(subtotal) as product_revenue'),
                DB::raw('SUM(delivery_fee) as delivery_revenue'),
                DB::raw('SUM(discount) as discounts')
            )
            ->groupBy(DB::raw('date(created_at)'))
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'summary' => [
                    'gross_revenue' => round((float) $grossRevenue, 2),
                    'product_revenue' => round((float) $subtotal, 2),
                    'delivery_fees' => round((float) $deliveryFees, 2),
                    'discounts' => round((float) $discounts, 2),
                    'refunds' => round((float) $refunds, 2),
                    'net_revenue' => round((float) $netRevenue, 2),
                ],
                'daily_breakdown' => $dailyRevenue,
            ],
        ]);
    }

    /**
     * Get payment methods report.
     *
     * Note: Since payment_method column doesn't exist on orders table,
     * this report is simplified to show order totals by status.
     *
     * @requirement RPT-015 Create payment methods report
     */
    public function paymentMethods(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Since payment_method column doesn't exist, provide order stats by status
        $orderStats = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                'status',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'total_orders' => $item->total_orders,
                    'revenue' => round((float) $item->revenue, 2),
                ];
            });

        $totalOrders = $orderStats->sum('total_orders');

        return response()->json([
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'total_orders' => $totalOrders,
                'orders_by_status' => $orderStats,
            ],
        ]);
    }

    /**
     * Export report as PDF.
     *
     * @requirement RPT-018 Export report to PDF (View)
     * @requirement RPT-019 Export report to PDF (Download)
     */
    public function export(Request $request, string $type): JsonResponse
    {
        $validTypes = [
            'sales-summary',
            'revenue',
            'orders',
            'products',
            'categories',
            'customers',
            'staff',
            'deliveries',
            'inventory',
            'financial',
            'payment-methods',
        ];

        if (!in_array($type, $validTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid report type',
            ], 400);
        }

        $dateRange = $this->getDateRange($request);
        $action = $request->input('action', 'view'); // view or download

        // For MVP, we return the data that would be used to generate the PDF
        // In production, this would use a PDF library like DomPDF or Snappy
        $reportData = match ($type) {
            'sales-summary' => $this->getSalesSummaryData($dateRange),
            'revenue' => $this->getRevenueData($dateRange),
            'orders' => $this->getOrdersData($dateRange),
            'products' => $this->getProductsData($dateRange),
            'categories' => $this->getCategoriesData($dateRange),
            'customers' => $this->getCustomersData($dateRange),
            'staff' => $this->getStaffData($dateRange),
            'deliveries' => $this->getDeliveriesData($dateRange),
            'inventory' => $this->getInventoryData($dateRange),
            'financial' => $this->getFinancialData($dateRange),
            'payment-methods' => $this->getPaymentMethodsData($dateRange),
        };

        return response()->json([
            'success' => true,
            'data' => [
                'report_type' => $type,
                'action' => $action,
                'date_range' => [
                    'start' => $dateRange['start']->toDateString(),
                    'end' => $dateRange['end']->toDateString(),
                ],
                'generated_at' => now()->toIso8601String(),
                'report_data' => $reportData,
                // In production, this would be a URL to the generated PDF
                'pdf_url' => "/api/v1/admin/reports/{$type}/pdf?" . http_build_query([
                    'start_date' => $dateRange['start']->toDateString(),
                    'end_date' => $dateRange['end']->toDateString(),
                    'action' => $action,
                ]),
            ],
        ]);
    }

    /**
     * Parse date range from request.
     */
    private function getDateRange(Request $request): array
    {
        $preset = $request->input('preset');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($preset) {
            return match ($preset) {
                'today' => [
                    'start' => Carbon::today()->startOfDay(),
                    'end' => Carbon::today()->endOfDay(),
                ],
                'yesterday' => [
                    'start' => Carbon::yesterday()->startOfDay(),
                    'end' => Carbon::yesterday()->endOfDay(),
                ],
                'week' => [
                    'start' => Carbon::now()->startOfWeek()->startOfDay(),
                    'end' => Carbon::now()->endOfWeek()->endOfDay(),
                ],
                'last_week' => [
                    'start' => Carbon::now()->subWeek()->startOfWeek()->startOfDay(),
                    'end' => Carbon::now()->subWeek()->endOfWeek()->endOfDay(),
                ],
                'month' => [
                    'start' => Carbon::now()->startOfMonth()->startOfDay(),
                    'end' => Carbon::now()->endOfMonth()->endOfDay(),
                ],
                'last_month' => [
                    'start' => Carbon::now()->subMonth()->startOfMonth()->startOfDay(),
                    'end' => Carbon::now()->subMonth()->endOfMonth()->endOfDay(),
                ],
                'year' => [
                    'start' => Carbon::now()->startOfYear()->startOfDay(),
                    'end' => Carbon::now()->endOfYear()->endOfDay(),
                ],
                'last_year' => [
                    'start' => Carbon::now()->subYear()->startOfYear()->startOfDay(),
                    'end' => Carbon::now()->subYear()->endOfYear()->endOfDay(),
                ],
                default => [
                    'start' => Carbon::now()->subDays(30)->startOfDay(),
                    'end' => Carbon::now()->endOfDay(),
                ],
            };
        }

        return [
            'start' => $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay(),
            'end' => $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay(),
        ];
    }

    /**
     * Get sales summary data for PDF export.
     */
    private function getSalesSummaryData(array $dateRange): array
    {
        $orders = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->get();

        return [
            'total_revenue' => round((float) $orders->sum('total'), 2),
            'total_orders' => $orders->count(),
            'avg_order_value' => $orders->count() > 0 ? round((float) $orders->sum('total') / $orders->count(), 2) : 0,
        ];
    }

    /**
     * Get revenue data for PDF export.
     */
    private function getRevenueData(array $dateRange): array
    {
        return [
            'total' => round(
                (float) Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->whereNotIn('status', ['cancelled', 'refunded'])
                    ->sum('total'),
                2
            ),
        ];
    }

    /**
     * Get orders data for PDF export.
     */
    private function getOrdersData(array $dateRange): array
    {
        return [
            'total' => Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
        ];
    }

    /**
     * Get products data for PDF export.
     */
    private function getProductsData(array $dateRange): array
    {
        return [
            'active_products' => Product::where('is_active', true)->count(),
        ];
    }

    /**
     * Get categories data for PDF export.
     */
    private function getCategoriesData(array $dateRange): array
    {
        return [
            'total_categories' => Category::count(),
        ];
    }

    /**
     * Get customers data for PDF export.
     */
    private function getCustomersData(array $dateRange): array
    {
        return [
            'total' => User::where('role', 'customer')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count(),
        ];
    }

    /**
     * Get staff data for PDF export.
     */
    private function getStaffData(array $dateRange): array
    {
        return [
            'total' => User::whereIn('role', ['staff', 'admin'])->count(),
        ];
    }

    /**
     * Get deliveries data for PDF export.
     */
    private function getDeliveriesData(array $dateRange): array
    {
        return [
            'total' => Order::whereNotNull('delivery_zone_id')
                ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->count(),
        ];
    }

    /**
     * Get inventory data for PDF export.
     */
    private function getInventoryData(array $dateRange): array
    {
        return [
            'total_products' => Product::count(),
            'low_stock' => Product::where('is_active', true)
                ->where('stock', '<=', 10)
                ->count(),
        ];
    }

    /**
     * Get financial data for PDF export.
     */
    private function getFinancialData(array $dateRange): array
    {
        return [
            'net_revenue' => round(
                (float) Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->whereNotIn('status', ['cancelled', 'refunded'])
                    ->sum('total'),
                2
            ),
        ];
    }

    /**
     * Get payment methods data for PDF export.
     */
    private function getPaymentMethodsData(array $dateRange): array
    {
        return Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->toArray();
    }
}
