/**
 * Admin Reports Store Tests
 *
 * Comprehensive tests for the admin reports and analytics Pinia store
 * @requirements RPT-001 to RPT-022
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAdminReportsStore } from "../adminReports";

// Mock the dashboard service - MUST use named export pattern
vi.mock("@/services/dashboard", () => ({
  adminDashboard: {
    getReportsDashboard: vi.fn(),
    getSalesSummaryReport: vi.fn(),
    getRevenueByPeriodReport: vi.fn(),
    getOrdersReport: vi.fn(),
    getProductSalesReport: vi.fn(),
    getCategorySalesReport: vi.fn(),
    getTopProductsReport: vi.fn(),
    getLowPerformingProductsReport: vi.fn(),
    getCustomerAnalyticsReport: vi.fn(),
    getCustomerAcquisitionReport: vi.fn(),
    getStaffPerformanceReport: vi.fn(),
    getDeliveryPerformanceReport: vi.fn(),
    getInventoryReport: vi.fn(),
    getFinancialSummaryReport: vi.fn(),
    getPaymentMethodsReport: vi.fn(),
    exportReport: vi.fn(),
  },
}));

import { adminDashboard } from "@/services/dashboard";

describe("useAdminReportsStore", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    store = useAdminReportsStore();
    vi.clearAllMocks();
  });

  // ========================================
  // Initial State Tests (8 tests)
  // ========================================
  describe("Initial State", () => {
    it("has correct initial dashboard state", () => {
      expect(store.dashboard.dateRange.start).toBe("");
      expect(store.dashboard.dateRange.end).toBe("");
      expect(store.dashboard.dateRange.periodDays).toBe(30);
      expect(store.dashboard.quickStats.revenue).toEqual({
        value: 0,
        change: 0,
      });
      expect(store.dashboard.quickStats.orders).toEqual({
        value: 0,
        change: 0,
      });
      expect(store.dashboard.quickStats.customers).toEqual({
        value: 0,
        change: 0,
      });
      expect(store.dashboard.quickStats.avgOrder).toEqual({
        value: 0,
        change: 0,
      });
      expect(store.dashboard.topProducts).toEqual([]);
      expect(store.dashboard.topCustomers).toEqual([]);
    });

    it("has correct initial dateFilters state", () => {
      expect(store.dateFilters.preset).toBe("month");
      expect(store.dateFilters.startDate).toBe("");
      expect(store.dateFilters.endDate).toBe("");
      expect(store.dateFilters.groupBy).toBe("day");
    });

    it("has correct initial salesSummary state", () => {
      expect(store.salesSummary.summary.totalRevenue).toBe(0);
      expect(store.salesSummary.summary.totalOrders).toBe(0);
      expect(store.salesSummary.summary.avgOrderValue).toBe(0);
      expect(store.salesSummary.summary.totalItemsSold).toBe(0);
      expect(store.salesSummary.revenueByStatus).toEqual({});
      expect(store.salesSummary.dailyRevenue).toEqual([]);
    });

    it("has correct initial loading states", () => {
      expect(store.loading.dashboard).toBe(false);
      expect(store.loading.salesSummary).toBe(false);
      expect(store.loading.revenue).toBe(false);
      expect(store.loading.orders).toBe(false);
      expect(store.loading.products).toBe(false);
      expect(store.loading.categories).toBe(false);
      expect(store.loading.topProducts).toBe(false);
      expect(store.loading.lowPerforming).toBe(false);
      expect(store.loading.customers).toBe(false);
      expect(store.loading.acquisition).toBe(false);
      expect(store.loading.staff).toBe(false);
      expect(store.loading.deliveries).toBe(false);
      expect(store.loading.inventory).toBe(false);
      expect(store.loading.financial).toBe(false);
      expect(store.loading.payments).toBe(false);
      expect(store.loading.export).toBe(false);
    });

    it("has null error initially", () => {
      expect(store.error).toBeNull();
    });

    it("has empty products state", () => {
      expect(store.products.totalRevenue).toBe(0);
      expect(store.products.products).toEqual([]);
      expect(store.products.pagination.currentPage).toBe(1);
      expect(store.products.pagination.lastPage).toBe(1);
      expect(store.products.pagination.perPage).toBe(15);
      expect(store.products.pagination.total).toBe(0);
    });

    it("has empty customers state", () => {
      expect(store.customers.summary.newCustomers).toBe(0);
      expect(store.customers.summary.activeCustomers).toBe(0);
      expect(store.customers.summary.returningCustomers).toBe(0);
      expect(store.customers.summary.newCustomerRate).toBe(0);
      expect(store.customers.summary.avgSpend).toBe(0);
      expect(store.customers.topCustomers).toEqual([]);
    });

    it("has empty financial state", () => {
      expect(store.financial.summary.grossRevenue).toBe(0);
      expect(store.financial.summary.productRevenue).toBe(0);
      expect(store.financial.summary.deliveryFees).toBe(0);
      expect(store.financial.summary.discounts).toBe(0);
      expect(store.financial.summary.refunds).toBe(0);
      expect(store.financial.summary.netRevenue).toBe(0);
      expect(store.financial.dailyBreakdown).toEqual([]);
    });
  });

  // ========================================
  // Getter Tests (6 tests)
  // ========================================
  describe("Getters", () => {
    it("hasDashboardData returns false when no data", () => {
      expect(store.hasDashboardData).toBe(false);
    });

    it("hasDashboardData returns true when revenue > 0", () => {
      store.dashboard.quickStats.revenue.value = 1000;
      expect(store.hasDashboardData).toBe(true);
    });

    it("hasDashboardData returns true when orders > 0", () => {
      store.dashboard.quickStats.orders.value = 5;
      expect(store.hasDashboardData).toBe(true);
    });

    it("formattedDateRange returns default text when no dates", () => {
      expect(store.formattedDateRange).toBe("Last 30 days");
    });

    it("formattedDateRange returns formatted range when dates exist", () => {
      store.dashboard.dateRange.start = "2024-01-01";
      store.dashboard.dateRange.end = "2024-01-31";
      expect(store.formattedDateRange).toBe("2024-01-01 - 2024-01-31");
    });

    it("isLoading returns true when any loading is true", () => {
      expect(store.isLoading).toBe(false);
      store.loading.dashboard = true;
      expect(store.isLoading).toBe(true);
    });

    it("revenueTrendData transforms data correctly", () => {
      store.salesSummary.dailyRevenue = [
        { date: "2024-01-01", revenue: "100.50", orders: "5" },
        { date: "2024-01-02", revenue: "200.75", orders: "10" },
      ];
      const result = store.revenueTrendData;
      expect(result).toHaveLength(2);
      expect(result[0]).toEqual({
        date: "2024-01-01",
        revenue: 100.5,
        orders: 5,
      });
      expect(result[1]).toEqual({
        date: "2024-01-02",
        revenue: 200.75,
        orders: 10,
      });
    });

    it("orderStatusBreakdown maps data correctly", () => {
      store.orders.byStatus = [
        { status: "pending", count: 10, percentage: 25, revenue: "500.00" },
        { status: "completed", count: 30, percentage: 75, revenue: "1500.00" },
      ];
      const result = store.orderStatusBreakdown;
      expect(result).toHaveLength(2);
      expect(result[0]).toEqual({
        status: "pending",
        count: 10,
        percentage: 25,
        revenue: 500,
      });
      expect(result[1]).toEqual({
        status: "completed",
        count: 30,
        percentage: 75,
        revenue: 1500,
      });
    });

    it("categoryBreakdown transforms data correctly", () => {
      store.categories.categories = [
        {
          name: "Beef",
          revenue: "1000.00",
          percentage: 50,
          quantity_sold: "100",
        },
        {
          name: "Pork",
          revenue: "500.00",
          percentage: 25,
          quantity_sold: "50",
        },
      ];
      const result = store.categoryBreakdown;
      expect(result).toHaveLength(2);
      expect(result[0]).toEqual({
        name: "Beef",
        revenue: 1000,
        percentage: 50,
        quantitySold: 100,
      });
    });

    it("staffPerformanceSummary calculates totals correctly", () => {
      store.staffPerformance.staff = [
        {
          orders_processed: 10,
          deliveries_completed: 5,
          order_value_processed: 500,
        },
        {
          orders_processed: 15,
          deliveries_completed: 8,
          order_value_processed: 750,
        },
      ];
      const result = store.staffPerformanceSummary;
      expect(result.totalOrdersProcessed).toBe(25);
      expect(result.totalDeliveriesCompleted).toBe(13);
      expect(result.totalOrderValue).toBe(1250);
    });

    it("hasFinancialData returns false when no revenue", () => {
      expect(store.hasFinancialData).toBe(false);
    });

    it("hasFinancialData returns true when revenue exists", () => {
      store.financial.summary.grossRevenue = 5000;
      expect(store.hasFinancialData).toBe(true);
    });
  });

  // ========================================
  // Action Tests - Dashboard (3 tests)
  // ========================================
  describe("fetchDashboard", () => {
    it("fetchDashboard sets dashboard data on success", async () => {
      const mockData = {
        success: true,
        data: {
          date_range: {
            start: "2024-01-01",
            end: "2024-01-31",
            period_days: 30,
          },
          quick_stats: {
            revenue: { value: 12450, change: 15 },
            orders: { value: 156, change: 12 },
            customers: { value: 89, change: 8 },
            avg_order: { value: 79.81, change: 3 },
          },
          top_products: [{ id: 1, name: "Ribeye", total_revenue: 1500 }],
          top_customers: [{ id: 1, name: "John Doe", total_spent: 500 }],
        },
      };
      adminDashboard.getReportsDashboard.mockResolvedValue(mockData);

      await store.fetchDashboard();

      expect(store.dashboard.dateRange.start).toBe("2024-01-01");
      expect(store.dashboard.dateRange.end).toBe("2024-01-31");
      expect(store.dashboard.quickStats.revenue.value).toBe(12450);
      expect(store.dashboard.quickStats.orders.value).toBe(156);
      expect(store.dashboard.topProducts).toHaveLength(1);
      expect(store.dashboard.topCustomers).toHaveLength(1);
      expect(store.loading.dashboard).toBe(false);
    });

    it("fetchDashboard sets loading state", async () => {
      let loadingDuringCall = false;
      adminDashboard.getReportsDashboard.mockImplementation(async () => {
        loadingDuringCall = store.loading.dashboard;
        return { success: true, data: {} };
      });

      await store.fetchDashboard();

      expect(loadingDuringCall).toBe(true);
      expect(store.loading.dashboard).toBe(false);
    });

    it("fetchDashboard handles error", async () => {
      adminDashboard.getReportsDashboard.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchDashboard()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch dashboard");
      expect(store.loading.dashboard).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Sales Summary (2 tests)
  // ========================================
  describe("fetchSalesSummary", () => {
    it("fetchSalesSummary sets salesSummary data", async () => {
      const mockData = {
        success: true,
        data: {
          summary: {
            total_revenue: 25000,
            total_orders: 200,
            avg_order_value: 125,
            total_items_sold: 500,
          },
          revenue_by_status: { completed: 20000, pending: 5000 },
          daily_revenue: [{ date: "2024-01-01", revenue: 1000, orders: 10 }],
        },
      };
      adminDashboard.getSalesSummaryReport.mockResolvedValue(mockData);

      await store.fetchSalesSummary();

      expect(store.salesSummary.summary.totalRevenue).toBe(25000);
      expect(store.salesSummary.summary.totalOrders).toBe(200);
      expect(store.salesSummary.summary.avgOrderValue).toBe(125);
      expect(store.salesSummary.summary.totalItemsSold).toBe(500);
      expect(store.salesSummary.revenueByStatus.completed).toBe(20000);
      expect(store.salesSummary.dailyRevenue).toHaveLength(1);
    });

    it("fetchSalesSummary handles error", async () => {
      adminDashboard.getSalesSummaryReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchSalesSummary()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch sales summary");
      expect(store.loading.salesSummary).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Revenue (2 tests)
  // ========================================
  describe("fetchRevenue", () => {
    it("fetchRevenue sets revenue data", async () => {
      const mockData = {
        success: true,
        data: {
          periods: [
            { period: "2024-01-01", revenue: 1000, order_count: 10 },
            { period: "2024-01-02", revenue: 1500, order_count: 15 },
          ],
          totals: {
            revenue: 2500,
            subtotal: 2300,
            delivery_fees: 200,
            discounts: 50,
            order_count: 25,
          },
        },
      };
      adminDashboard.getRevenueByPeriodReport.mockResolvedValue(mockData);

      await store.fetchRevenue();

      expect(store.revenue.periods).toHaveLength(2);
      expect(store.revenue.totals.revenue).toBe(2500);
      expect(store.revenue.totals.subtotal).toBe(2300);
      expect(store.revenue.totals.deliveryFees).toBe(200);
      expect(store.revenue.totals.discounts).toBe(50);
      expect(store.revenue.totals.orderCount).toBe(25);
    });

    it("fetchRevenue handles error", async () => {
      adminDashboard.getRevenueByPeriodReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchRevenue()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch revenue report");
      expect(store.loading.revenue).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Orders (2 tests)
  // ========================================
  describe("fetchOrdersReport", () => {
    it("fetchOrdersReport sets orders data", async () => {
      const mockData = {
        success: true,
        data: {
          total_orders: 150,
          by_status: [
            {
              status: "completed",
              count: 100,
              percentage: 66.67,
              revenue: 5000,
            },
            { status: "pending", count: 50, percentage: 33.33, revenue: 2500 },
          ],
          recent_orders: [{ id: 1, total: 100, status: "completed" }],
        },
      };
      adminDashboard.getOrdersReport.mockResolvedValue(mockData);

      await store.fetchOrdersReport();

      expect(store.orders.totalOrders).toBe(150);
      expect(store.orders.byStatus).toHaveLength(2);
      expect(store.orders.recentOrders).toHaveLength(1);
    });

    it("fetchOrdersReport handles error", async () => {
      adminDashboard.getOrdersReport.mockRejectedValue(new Error("API Error"));

      await expect(store.fetchOrdersReport()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch orders report");
      expect(store.loading.orders).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Products (2 tests)
  // ========================================
  describe("fetchProductsReport", () => {
    it("fetchProductsReport sets products data with pagination", async () => {
      const mockData = {
        success: true,
        data: {
          total_revenue: 50000,
          products: {
            data: [
              { id: 1, name: "Ribeye Steak", revenue: 5000, quantity: 50 },
              { id: 2, name: "Pork Chops", revenue: 3000, quantity: 40 },
            ],
            current_page: 1,
            last_page: 3,
            per_page: 15,
            total: 45,
          },
        },
      };
      adminDashboard.getProductSalesReport.mockResolvedValue(mockData);

      await store.fetchProductsReport();

      expect(store.products.totalRevenue).toBe(50000);
      expect(store.products.products).toHaveLength(2);
      expect(store.products.pagination.currentPage).toBe(1);
      expect(store.products.pagination.lastPage).toBe(3);
      expect(store.products.pagination.perPage).toBe(15);
      expect(store.products.pagination.total).toBe(45);
    });

    it("fetchProductsReport handles error", async () => {
      adminDashboard.getProductSalesReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchProductsReport()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch products report");
      expect(store.loading.products).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Categories (2 tests)
  // ========================================
  describe("fetchCategoriesReport", () => {
    it("fetchCategoriesReport sets categories data", async () => {
      const mockData = {
        success: true,
        data: {
          total_revenue: 30000,
          categories: [
            {
              id: 1,
              name: "Beef",
              revenue: 15000,
              percentage: 50,
              quantity_sold: 150,
            },
            {
              id: 2,
              name: "Pork",
              revenue: 10000,
              percentage: 33.33,
              quantity_sold: 100,
            },
          ],
        },
      };
      adminDashboard.getCategorySalesReport.mockResolvedValue(mockData);

      await store.fetchCategoriesReport();

      expect(store.categories.totalRevenue).toBe(30000);
      expect(store.categories.categories).toHaveLength(2);
      expect(store.categories.categories[0].name).toBe("Beef");
    });

    it("fetchCategoriesReport handles error", async () => {
      adminDashboard.getCategorySalesReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchCategoriesReport()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch categories report");
      expect(store.loading.categories).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Top Products (2 tests)
  // ========================================
  describe("fetchTopProducts", () => {
    it("fetchTopProducts sets topProducts data", async () => {
      const mockData = {
        success: true,
        data: {
          top_by_revenue: [
            { id: 1, name: "Ribeye", total_revenue: 5000 },
            { id: 2, name: "Filet Mignon", total_revenue: 4500 },
          ],
          top_by_quantity: [
            { id: 3, name: "Ground Beef", total_quantity: 200 },
            { id: 1, name: "Ribeye", total_quantity: 150 },
          ],
        },
      };
      adminDashboard.getTopProductsReport.mockResolvedValue(mockData);

      await store.fetchTopProducts();

      expect(store.topProducts.byRevenue).toHaveLength(2);
      expect(store.topProducts.byQuantity).toHaveLength(2);
      expect(store.topProducts.byRevenue[0].name).toBe("Ribeye");
    });

    it("fetchTopProducts handles error", async () => {
      adminDashboard.getTopProductsReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchTopProducts()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch top products");
      expect(store.loading.topProducts).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Low Performing (2 tests)
  // ========================================
  describe("fetchLowPerformingProducts", () => {
    it("fetchLowPerformingProducts sets data", async () => {
      const mockData = {
        success: true,
        data: {
          low_selling: [
            { id: 1, name: "Lamb Shoulder", revenue: 50, quantity: 2 },
          ],
          no_sales: [
            { id: 2, name: "Exotic Sausage", last_sale: "2023-06-01" },
          ],
        },
      };
      adminDashboard.getLowPerformingProductsReport.mockResolvedValue(mockData);

      await store.fetchLowPerformingProducts();

      expect(store.lowPerformingProducts.lowSelling).toHaveLength(1);
      expect(store.lowPerformingProducts.noSales).toHaveLength(1);
      expect(store.lowPerformingProducts.lowSelling[0].name).toBe(
        "Lamb Shoulder"
      );
    });

    it("fetchLowPerformingProducts handles error", async () => {
      adminDashboard.getLowPerformingProductsReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchLowPerformingProducts()).rejects.toThrow(
        "API Error"
      );
      expect(store.error).toBe("Failed to fetch low performing products");
      expect(store.loading.lowPerforming).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Customers (2 tests)
  // ========================================
  describe("fetchCustomersReport", () => {
    it("fetchCustomersReport sets customers data", async () => {
      const mockData = {
        success: true,
        data: {
          summary: {
            new_customers: 25,
            active_customers: 150,
            returning_customers: 80,
            new_customer_rate: 16.67,
            avg_spend: 125.5,
          },
          top_customers: [
            { id: 1, name: "John Doe", total_spent: 1500, order_count: 12 },
          ],
        },
      };
      adminDashboard.getCustomerAnalyticsReport.mockResolvedValue(mockData);

      await store.fetchCustomersReport();

      expect(store.customers.summary.newCustomers).toBe(25);
      expect(store.customers.summary.activeCustomers).toBe(150);
      expect(store.customers.summary.returningCustomers).toBe(80);
      expect(store.customers.summary.newCustomerRate).toBe(16.67);
      expect(store.customers.summary.avgSpend).toBe(125.5);
      expect(store.customers.topCustomers).toHaveLength(1);
    });

    it("fetchCustomersReport handles error", async () => {
      adminDashboard.getCustomerAnalyticsReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchCustomersReport()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch customers report");
      expect(store.loading.customers).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Acquisition (2 tests)
  // ========================================
  describe("fetchCustomerAcquisition", () => {
    it("fetchCustomerAcquisition sets data", async () => {
      const mockData = {
        success: true,
        data: {
          total_new_customers: 50,
          conversion_rate: 35.5,
          customers_with_orders: 45,
          by_period: [
            { period: "2024-01-01", new_customers: 10, orders: 8 },
            { period: "2024-01-02", new_customers: 15, orders: 12 },
          ],
        },
      };
      adminDashboard.getCustomerAcquisitionReport.mockResolvedValue(mockData);

      await store.fetchCustomerAcquisition();

      expect(store.customerAcquisition.totalNewCustomers).toBe(50);
      expect(store.customerAcquisition.conversionRate).toBe(35.5);
      expect(store.customerAcquisition.customersWithOrders).toBe(45);
      expect(store.customerAcquisition.byPeriod).toHaveLength(2);
    });

    it("fetchCustomerAcquisition handles error", async () => {
      adminDashboard.getCustomerAcquisitionReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchCustomerAcquisition()).rejects.toThrow(
        "API Error"
      );
      expect(store.error).toBe("Failed to fetch customer acquisition report");
      expect(store.loading.acquisition).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Staff (2 tests)
  // ========================================
  describe("fetchStaffPerformance", () => {
    it("fetchStaffPerformance sets staff data", async () => {
      const mockData = {
        success: true,
        data: {
          staff: [
            {
              id: 1,
              name: "Alice",
              orders_processed: 50,
              deliveries_completed: 30,
              order_value_processed: 2500,
            },
            {
              id: 2,
              name: "Bob",
              orders_processed: 45,
              deliveries_completed: 25,
              order_value_processed: 2250,
            },
          ],
        },
      };
      adminDashboard.getStaffPerformanceReport.mockResolvedValue(mockData);

      await store.fetchStaffPerformance();

      expect(store.staffPerformance.staff).toHaveLength(2);
      expect(store.staffPerformance.staff[0].name).toBe("Alice");
      expect(store.staffPerformance.staff[0].orders_processed).toBe(50);
    });

    it("fetchStaffPerformance handles error", async () => {
      adminDashboard.getStaffPerformanceReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchStaffPerformance()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch staff performance report");
      expect(store.loading.staff).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Deliveries (2 tests)
  // ========================================
  describe("fetchDeliveryPerformance", () => {
    it("fetchDeliveryPerformance sets data", async () => {
      const mockData = {
        success: true,
        data: {
          summary: {
            total_deliveries: 200,
            completed_deliveries: 180,
            on_time_deliveries: 170,
            on_time_rate: 94.44,
            avg_delivery_fee: 5.5,
          },
          by_zone: [
            { zone: "Zone A", deliveries: 80, on_time_rate: 95 },
            { zone: "Zone B", deliveries: 100, on_time_rate: 92 },
          ],
        },
      };
      adminDashboard.getDeliveryPerformanceReport.mockResolvedValue(mockData);

      await store.fetchDeliveryPerformance();

      expect(store.deliveryPerformance.summary.totalDeliveries).toBe(200);
      expect(store.deliveryPerformance.summary.completedDeliveries).toBe(180);
      expect(store.deliveryPerformance.summary.onTimeDeliveries).toBe(170);
      expect(store.deliveryPerformance.summary.onTimeRate).toBe(94.44);
      expect(store.deliveryPerformance.summary.avgDeliveryFee).toBe(5.5);
      expect(store.deliveryPerformance.byZone).toHaveLength(2);
    });

    it("fetchDeliveryPerformance handles error", async () => {
      adminDashboard.getDeliveryPerformanceReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchDeliveryPerformance()).rejects.toThrow(
        "API Error"
      );
      expect(store.error).toBe("Failed to fetch delivery performance report");
      expect(store.loading.deliveries).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Inventory (2 tests)
  // ========================================
  describe("fetchInventoryReport", () => {
    it("fetchInventoryReport sets inventory data", async () => {
      const mockData = {
        success: true,
        data: {
          stock_levels: {
            total_products: 100,
            active_products: 85,
            low_stock: 10,
            out_of_stock: 5,
          },
          movements: {
            stock_received: 500,
            stock_sold: 400,
            stock_adjusted: 20,
          },
          waste: {
            count: 15,
            quantity: 50,
            estimated_value: 250,
          },
          turnover_rate: 4.5,
        },
      };
      adminDashboard.getInventoryReport.mockResolvedValue(mockData);

      await store.fetchInventoryReport();

      expect(store.inventory.stockLevels.totalProducts).toBe(100);
      expect(store.inventory.stockLevels.activeProducts).toBe(85);
      expect(store.inventory.stockLevels.lowStock).toBe(10);
      expect(store.inventory.stockLevels.outOfStock).toBe(5);
      expect(store.inventory.movements.stockReceived).toBe(500);
      expect(store.inventory.movements.stockSold).toBe(400);
      expect(store.inventory.movements.stockAdjusted).toBe(20);
      expect(store.inventory.waste.count).toBe(15);
      expect(store.inventory.waste.quantity).toBe(50);
      expect(store.inventory.waste.estimatedValue).toBe(250);
      expect(store.inventory.turnoverRate).toBe(4.5);
    });

    it("fetchInventoryReport handles error", async () => {
      adminDashboard.getInventoryReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchInventoryReport()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch inventory report");
      expect(store.loading.inventory).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Financial (2 tests)
  // ========================================
  describe("fetchFinancialSummary", () => {
    it("fetchFinancialSummary sets financial data", async () => {
      const mockData = {
        success: true,
        data: {
          summary: {
            gross_revenue: 50000,
            product_revenue: 45000,
            delivery_fees: 5000,
            discounts: 2000,
            refunds: 500,
            net_revenue: 47500,
          },
          daily_breakdown: [
            { date: "2024-01-01", revenue: 1500, orders: 15 },
            { date: "2024-01-02", revenue: 1800, orders: 18 },
          ],
        },
      };
      adminDashboard.getFinancialSummaryReport.mockResolvedValue(mockData);

      await store.fetchFinancialSummary();

      expect(store.financial.summary.grossRevenue).toBe(50000);
      expect(store.financial.summary.productRevenue).toBe(45000);
      expect(store.financial.summary.deliveryFees).toBe(5000);
      expect(store.financial.summary.discounts).toBe(2000);
      expect(store.financial.summary.refunds).toBe(500);
      expect(store.financial.summary.netRevenue).toBe(47500);
      expect(store.financial.dailyBreakdown).toHaveLength(2);
    });

    it("fetchFinancialSummary handles error", async () => {
      adminDashboard.getFinancialSummaryReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchFinancialSummary()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch financial summary");
      expect(store.loading.financial).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Payments (2 tests)
  // ========================================
  describe("fetchPaymentMethods", () => {
    it("fetchPaymentMethods sets data", async () => {
      const mockData = {
        success: true,
        data: {
          total_orders: 500,
          orders_by_status: [
            { status: "paid", count: 450, percentage: 90 },
            { status: "pending", count: 50, percentage: 10 },
          ],
        },
      };
      adminDashboard.getPaymentMethodsReport.mockResolvedValue(mockData);

      await store.fetchPaymentMethods();

      expect(store.paymentMethods.totalOrders).toBe(500);
      expect(store.paymentMethods.ordersByStatus).toHaveLength(2);
      expect(store.paymentMethods.ordersByStatus[0].status).toBe("paid");
    });

    it("fetchPaymentMethods handles error", async () => {
      adminDashboard.getPaymentMethodsReport.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchPaymentMethods()).rejects.toThrow("API Error");
      expect(store.error).toBe("Failed to fetch payment methods report");
      expect(store.loading.payments).toBe(false);
    });
  });

  // ========================================
  // Action Tests - Export (2 tests)
  // ========================================
  describe("exportReport", () => {
    it("exportReport calls service with correct params", async () => {
      const mockData = {
        success: true,
        data: {
          pdf_url: "https://example.com/report.pdf",
        },
      };
      adminDashboard.exportReport.mockResolvedValue(mockData);

      // Mock window.open
      const windowOpenSpy = vi
        .spyOn(window, "open")
        .mockImplementation(() => {});

      store.dateFilters.preset = "week";
      await store.exportReport("sales", "view");

      expect(adminDashboard.exportReport).toHaveBeenCalledWith(
        "sales",
        expect.objectContaining({
          preset: "week",
          action: "view",
        })
      );
      expect(store.exportData).toEqual(mockData.data);
      expect(windowOpenSpy).toHaveBeenCalledWith(
        "https://example.com/report.pdf",
        "_blank"
      );

      windowOpenSpy.mockRestore();
    });

    it("exportReport handles error", async () => {
      adminDashboard.exportReport.mockRejectedValue(new Error("Export failed"));

      await expect(store.exportReport("sales", "download")).rejects.toThrow(
        "Export failed"
      );
      expect(store.error).toBe("Failed to export report");
      expect(store.loading.export).toBe(false);
    });

    it("exportReport downloads PDF when action is download", async () => {
      const mockData = {
        success: true,
        data: {
          pdf_url: "https://example.com/report.pdf",
        },
      };
      adminDashboard.exportReport.mockResolvedValue(mockData);

      // Mock document.createElement and link.click
      const clickMock = vi.fn();
      const createElementSpy = vi
        .spyOn(document, "createElement")
        .mockReturnValue({
          href: "",
          download: "",
          click: clickMock,
        });

      await store.exportReport("financial", "download");

      expect(createElementSpy).toHaveBeenCalledWith("a");
      expect(clickMock).toHaveBeenCalled();

      createElementSpy.mockRestore();
    });
  });

  // ========================================
  // Filter Actions (4 tests)
  // ========================================
  describe("Filter Actions", () => {
    it("setDatePreset updates preset", () => {
      store.setDatePreset("week");
      expect(store.dateFilters.preset).toBe("week");
    });

    it("setDatePreset clears custom dates when not custom", () => {
      store.dateFilters.startDate = "2024-01-01";
      store.dateFilters.endDate = "2024-01-31";
      store.setDatePreset("month");

      expect(store.dateFilters.preset).toBe("month");
      expect(store.dateFilters.startDate).toBe("");
      expect(store.dateFilters.endDate).toBe("");
    });

    it("setCustomDateRange sets custom dates", () => {
      store.setCustomDateRange("2024-01-01", "2024-01-31");

      expect(store.dateFilters.preset).toBe("custom");
      expect(store.dateFilters.startDate).toBe("2024-01-01");
      expect(store.dateFilters.endDate).toBe("2024-01-31");
    });

    it("setGroupBy updates groupBy", () => {
      store.setGroupBy("week");
      expect(store.dateFilters.groupBy).toBe("week");
    });

    it("resetFilters resets to defaults", () => {
      store.setDatePreset("year");
      store.setGroupBy("month");
      store.setCustomDateRange("2024-01-01", "2024-12-31");

      store.resetFilters();

      expect(store.dateFilters.preset).toBe("month");
      expect(store.dateFilters.startDate).toBe("");
      expect(store.dateFilters.endDate).toBe("");
      expect(store.dateFilters.groupBy).toBe("day");
    });
  });

  // ========================================
  // Utility Actions (2 tests)
  // ========================================
  describe("Utility Actions", () => {
    it("clearError clears error", () => {
      store.error = "Some error occurred";
      store.clearError();
      expect(store.error).toBeNull();
    });

    it("getDateParams returns correct params for preset", () => {
      store.dateFilters.preset = "week";
      const params = store.getDateParams();

      expect(params).toEqual({ preset: "week" });
    });

    it("getDateParams returns custom dates when preset is custom", () => {
      store.dateFilters.preset = "custom";
      store.dateFilters.startDate = "2024-01-01";
      store.dateFilters.endDate = "2024-01-31";

      const params = store.getDateParams();

      expect(params).toEqual({
        start_date: "2024-01-01",
        end_date: "2024-01-31",
      });
    });

    it("getDateParams returns empty object when custom without dates", () => {
      store.dateFilters.preset = "custom";
      store.dateFilters.startDate = "";
      store.dateFilters.endDate = "";

      const params = store.getDateParams();

      expect(params).toEqual({});
    });

    it("resetFilters resets filter state correctly", () => {
      // Modify filter properties
      store.dateFilters.preset = "year";
      store.dateFilters.groupBy = "month";
      store.dateFilters.startDate = "2024-01-01";
      store.dateFilters.endDate = "2024-12-31";

      store.resetFilters();

      expect(store.dateFilters.preset).toBe("month");
      expect(store.dateFilters.groupBy).toBe("day");
      expect(store.dateFilters.startDate).toBe("");
      expect(store.dateFilters.endDate).toBe("");
    });
  });

  // ========================================
  // Additional Edge Cases
  // ========================================
  describe("Edge Cases", () => {
    it("handles null data in API response", async () => {
      const mockData = {
        success: true,
        data: {
          date_range: null,
          quick_stats: null,
          top_products: null,
          top_customers: null,
        },
      };
      adminDashboard.getReportsDashboard.mockResolvedValue(mockData);

      await store.fetchDashboard();

      expect(store.dashboard.dateRange.start).toBe("");
      expect(store.dashboard.quickStats.revenue).toEqual({
        value: 0,
        change: 0,
      });
      expect(store.dashboard.topProducts).toEqual([]);
    });

    it("handles partial data in sales summary response", async () => {
      const mockData = {
        success: true,
        data: {
          summary: {
            total_revenue: 5000,
            // Missing other fields
          },
          // Missing revenue_by_status and daily_revenue
        },
      };
      adminDashboard.getSalesSummaryReport.mockResolvedValue(mockData);

      await store.fetchSalesSummary();

      expect(store.salesSummary.summary.totalRevenue).toBe(5000);
      expect(store.salesSummary.summary.totalOrders).toBe(0);
      expect(store.salesSummary.revenueByStatus).toEqual({});
      expect(store.salesSummary.dailyRevenue).toEqual([]);
    });

    it("handles API error with response data message", async () => {
      const error = new Error("Network error");
      error.response = { data: { message: "Custom error message" } };
      adminDashboard.getReportsDashboard.mockRejectedValue(error);

      await expect(store.fetchDashboard()).rejects.toThrow();
      expect(store.error).toBe("Custom error message");
    });

    it("passes merged params to fetchRevenue including groupBy", async () => {
      adminDashboard.getRevenueByPeriodReport.mockResolvedValue({
        success: true,
        data: { periods: [], totals: {} },
      });

      store.dateFilters.preset = "month";
      store.dateFilters.groupBy = "week";

      await store.fetchRevenue({ custom_param: "value" });

      expect(adminDashboard.getRevenueByPeriodReport).toHaveBeenCalledWith(
        expect.objectContaining({
          preset: "month",
          group_by: "week",
          custom_param: "value",
        })
      );
    });

    it("passes limit parameter to fetchTopProducts", async () => {
      adminDashboard.getTopProductsReport.mockResolvedValue({
        success: true,
        data: { top_by_revenue: [], top_by_quantity: [] },
      });

      await store.fetchTopProducts({ limit: 5 });

      expect(adminDashboard.getTopProductsReport).toHaveBeenCalledWith(
        expect.objectContaining({
          limit: 5,
        })
      );
    });
  });
});
