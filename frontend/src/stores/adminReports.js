/**
 * Admin Reports Store
 *
 * Pinia store for managing admin reports and analytics state
 * @module stores/adminReports
 * @requirements RPT-001 to RPT-022
 */
import { defineStore } from "pinia";
import { adminDashboard } from "@/services/dashboard";

export const useAdminReportsStore = defineStore("adminReports", {
  state: () => ({
    // Dashboard overview
    dashboard: {
      dateRange: {
        start: "",
        end: "",
        periodDays: 30,
      },
      quickStats: {
        revenue: { value: 0, change: 0 },
        orders: { value: 0, change: 0 },
        customers: { value: 0, change: 0 },
        avgOrder: { value: 0, change: 0 },
      },
      topProducts: [],
      topCustomers: [],
    },

    // Date range filters
    dateFilters: {
      preset: "month",
      startDate: "",
      endDate: "",
      groupBy: "day",
    },

    // Sales summary report data
    salesSummary: {
      summary: {
        totalRevenue: 0,
        totalOrders: 0,
        avgOrderValue: 0,
        totalItemsSold: 0,
      },
      revenueByStatus: {},
      dailyRevenue: [],
    },

    // Revenue report data
    revenue: {
      periods: [],
      totals: {
        revenue: 0,
        subtotal: 0,
        deliveryFees: 0,
        discounts: 0,
        orderCount: 0,
      },
    },

    // Orders report data
    orders: {
      totalOrders: 0,
      byStatus: [],
      recentOrders: [],
    },

    // Products report data
    products: {
      totalRevenue: 0,
      products: [],
      pagination: {
        currentPage: 1,
        lastPage: 1,
        perPage: 15,
        total: 0,
      },
    },

    // Categories report data
    categories: {
      totalRevenue: 0,
      categories: [],
    },

    // Top products report data
    topProducts: {
      byRevenue: [],
      byQuantity: [],
    },

    // Low performing products data
    lowPerformingProducts: {
      lowSelling: [],
      noSales: [],
    },

    // Customer report data
    customers: {
      summary: {
        newCustomers: 0,
        activeCustomers: 0,
        returningCustomers: 0,
        newCustomerRate: 0,
        avgSpend: 0,
      },
      topCustomers: [],
    },

    // Customer acquisition report data
    customerAcquisition: {
      totalNewCustomers: 0,
      conversionRate: 0,
      customersWithOrders: 0,
      byPeriod: [],
    },

    // Staff performance report data
    staffPerformance: {
      staff: [],
    },

    // Delivery report data
    deliveryPerformance: {
      summary: {
        totalDeliveries: 0,
        completedDeliveries: 0,
        onTimeDeliveries: 0,
        onTimeRate: 0,
        avgDeliveryFee: 0,
      },
      byZone: [],
    },

    // Inventory report data
    inventory: {
      stockLevels: {
        totalProducts: 0,
        activeProducts: 0,
        lowStock: 0,
        outOfStock: 0,
      },
      movements: {
        stockReceived: 0,
        stockSold: 0,
        stockAdjusted: 0,
      },
      waste: {
        count: 0,
        quantity: 0,
        estimatedValue: 0,
      },
      turnoverRate: 0,
    },

    // Financial report data
    financial: {
      summary: {
        grossRevenue: 0,
        productRevenue: 0,
        deliveryFees: 0,
        discounts: 0,
        refunds: 0,
        netRevenue: 0,
      },
      dailyBreakdown: [],
    },

    // Payment methods report data
    paymentMethods: {
      totalOrders: 0,
      ordersByStatus: [],
    },

    // Export state
    exportData: null,

    // Loading states
    loading: {
      dashboard: false,
      salesSummary: false,
      revenue: false,
      orders: false,
      products: false,
      categories: false,
      topProducts: false,
      lowPerforming: false,
      customers: false,
      acquisition: false,
      staff: false,
      deliveries: false,
      inventory: false,
      financial: false,
      payments: false,
      export: false,
    },

    // Error state
    error: null,
  }),

  getters: {
    /**
     * Check if dashboard has data
     */
    hasDashboardData: (state) =>
      state.dashboard.quickStats.revenue.value > 0 ||
      state.dashboard.quickStats.orders.value > 0,

    /**
     * Get formatted date range
     */
    formattedDateRange: (state) => {
      if (state.dashboard.dateRange.start && state.dashboard.dateRange.end) {
        return `${state.dashboard.dateRange.start} - ${state.dashboard.dateRange.end}`;
      }
      return "Last 30 days";
    },

    /**
     * Check if loading any report
     */
    isLoading: (state) => Object.values(state.loading).some((v) => v),

    /**
     * Get revenue trend data for charts
     */
    revenueTrendData: (state) => {
      return state.salesSummary.dailyRevenue.map((item) => ({
        date: item.date,
        revenue: parseFloat(item.revenue) || 0,
        orders: parseInt(item.orders) || 0,
      }));
    },

    /**
     * Get order status breakdown for pie chart
     */
    orderStatusBreakdown: (state) => {
      return state.orders.byStatus.map((item) => ({
        status: item.status,
        count: item.count,
        percentage: item.percentage,
        revenue: parseFloat(item.revenue) || 0,
      }));
    },

    /**
     * Get category breakdown for charts
     */
    categoryBreakdown: (state) => {
      return state.categories.categories.map((cat) => ({
        name: cat.name,
        revenue: parseFloat(cat.revenue) || 0,
        percentage: cat.percentage,
        quantitySold: parseInt(cat.quantity_sold) || 0,
      }));
    },

    /**
     * Get staff performance summary
     */
    staffPerformanceSummary: (state) => {
      const staff = state.staffPerformance.staff;
      return {
        totalOrdersProcessed: staff.reduce(
          (sum, s) => sum + s.orders_processed,
          0
        ),
        totalDeliveriesCompleted: staff.reduce(
          (sum, s) => sum + s.deliveries_completed,
          0
        ),
        totalOrderValue: staff.reduce(
          (sum, s) => sum + s.order_value_processed,
          0
        ),
      };
    },

    /**
     * Check if financial data has revenue
     */
    hasFinancialData: (state) => state.financial.summary.grossRevenue > 0,
  },

  actions: {
    /**
     * Fetch reports dashboard
     * @requirement RPT-001 Create reports dashboard
     */
    async fetchDashboard(params = {}) {
      this.loading.dashboard = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          ...params,
        };

        const response = await adminDashboard.getReportsDashboard(mergedParams);

        if (response.success && response.data) {
          const data = response.data;

          this.dashboard = {
            dateRange: {
              start: data.date_range?.start || "",
              end: data.date_range?.end || "",
              periodDays: data.date_range?.period_days || 30,
            },
            quickStats: {
              revenue: data.quick_stats?.revenue || { value: 0, change: 0 },
              orders: data.quick_stats?.orders || { value: 0, change: 0 },
              customers: data.quick_stats?.customers || { value: 0, change: 0 },
              avgOrder: data.quick_stats?.avg_order || { value: 0, change: 0 },
            },
            topProducts: data.top_products || [],
            topCustomers: data.top_customers || [],
          };
        }

        return response;
      } catch (err) {
        this.error = err.response?.data?.message || "Failed to fetch dashboard";
        console.error("Failed to fetch reports dashboard:", err);
        throw err;
      } finally {
        this.loading.dashboard = false;
      }
    },

    /**
     * Fetch sales summary report
     * @requirement RPT-002 Create sales summary report
     */
    async fetchSalesSummary(params = {}) {
      this.loading.salesSummary = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          ...params,
        };

        const response = await adminDashboard.getSalesSummaryReport(
          mergedParams
        );

        if (response.success && response.data) {
          const data = response.data;

          this.salesSummary = {
            summary: {
              totalRevenue: data.summary?.total_revenue || 0,
              totalOrders: data.summary?.total_orders || 0,
              avgOrderValue: data.summary?.avg_order_value || 0,
              totalItemsSold: data.summary?.total_items_sold || 0,
            },
            revenueByStatus: data.revenue_by_status || {},
            dailyRevenue: data.daily_revenue || [],
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message || "Failed to fetch sales summary";
        console.error("Failed to fetch sales summary:", err);
        throw err;
      } finally {
        this.loading.salesSummary = false;
      }
    },

    /**
     * Fetch revenue by period report
     * @requirement RPT-003 Create revenue by period report
     */
    async fetchRevenue(params = {}) {
      this.loading.revenue = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          group_by: this.dateFilters.groupBy,
          ...params,
        };

        const response = await adminDashboard.getRevenueByPeriodReport(
          mergedParams
        );

        if (response.success && response.data) {
          const data = response.data;

          this.revenue = {
            periods: data.periods || [],
            totals: {
              revenue: data.totals?.revenue || 0,
              subtotal: data.totals?.subtotal || 0,
              deliveryFees: data.totals?.delivery_fees || 0,
              discounts: data.totals?.discounts || 0,
              orderCount: data.totals?.order_count || 0,
            },
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message || "Failed to fetch revenue report";
        console.error("Failed to fetch revenue report:", err);
        throw err;
      } finally {
        this.loading.revenue = false;
      }
    },

    /**
     * Fetch orders report
     * @requirement RPT-004 Create orders by status report
     */
    async fetchOrdersReport(params = {}) {
      this.loading.orders = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          ...params,
        };

        const response = await adminDashboard.getOrdersReport(mergedParams);

        if (response.success && response.data) {
          const data = response.data;

          this.orders = {
            totalOrders: data.total_orders || 0,
            byStatus: data.by_status || [],
            recentOrders: data.recent_orders || [],
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message || "Failed to fetch orders report";
        console.error("Failed to fetch orders report:", err);
        throw err;
      } finally {
        this.loading.orders = false;
      }
    },

    /**
     * Fetch products report
     * @requirement RPT-005 Create product sales report
     */
    async fetchProductsReport(params = {}) {
      this.loading.products = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          ...params,
        };

        const response = await adminDashboard.getProductSalesReport(
          mergedParams
        );

        if (response.success && response.data) {
          const data = response.data;

          this.products = {
            totalRevenue: data.total_revenue || 0,
            products: data.products?.data || [],
            pagination: {
              currentPage: data.products?.current_page || 1,
              lastPage: data.products?.last_page || 1,
              perPage: data.products?.per_page || 15,
              total: data.products?.total || 0,
            },
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message || "Failed to fetch products report";
        console.error("Failed to fetch products report:", err);
        throw err;
      } finally {
        this.loading.products = false;
      }
    },

    /**
     * Fetch categories report
     * @requirement RPT-006 Create category sales report
     */
    async fetchCategoriesReport(params = {}) {
      this.loading.categories = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          ...params,
        };

        const response = await adminDashboard.getCategorySalesReport(
          mergedParams
        );

        if (response.success && response.data) {
          const data = response.data;

          this.categories = {
            totalRevenue: data.total_revenue || 0,
            categories: data.categories || [],
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message || "Failed to fetch categories report";
        console.error("Failed to fetch categories report:", err);
        throw err;
      } finally {
        this.loading.categories = false;
      }
    },

    /**
     * Fetch top products report
     * @requirement RPT-007 Create top products report
     */
    async fetchTopProducts(params = {}) {
      this.loading.topProducts = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          limit: 10,
          ...params,
        };

        const response = await adminDashboard.getTopProductsReport(
          mergedParams
        );

        if (response.success && response.data) {
          const data = response.data;

          this.topProducts = {
            byRevenue: data.top_by_revenue || [],
            byQuantity: data.top_by_quantity || [],
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message || "Failed to fetch top products";
        console.error("Failed to fetch top products:", err);
        throw err;
      } finally {
        this.loading.topProducts = false;
      }
    },

    /**
     * Fetch low performing products
     * @requirement RPT-008 Create low performing products
     */
    async fetchLowPerformingProducts(params = {}) {
      this.loading.lowPerforming = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          limit: 10,
          ...params,
        };

        const response = await adminDashboard.getLowPerformingProductsReport(
          mergedParams
        );

        if (response.success && response.data) {
          const data = response.data;

          this.lowPerformingProducts = {
            lowSelling: data.low_selling || [],
            noSales: data.no_sales || [],
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message ||
          "Failed to fetch low performing products";
        console.error("Failed to fetch low performing products:", err);
        throw err;
      } finally {
        this.loading.lowPerforming = false;
      }
    },

    /**
     * Fetch customer analytics report
     * @requirement RPT-009 Create customer report
     */
    async fetchCustomersReport(params = {}) {
      this.loading.customers = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          ...params,
        };

        const response = await adminDashboard.getCustomerAnalyticsReport(
          mergedParams
        );

        if (response.success && response.data) {
          const data = response.data;

          this.customers = {
            summary: {
              newCustomers: data.summary?.new_customers || 0,
              activeCustomers: data.summary?.active_customers || 0,
              returningCustomers: data.summary?.returning_customers || 0,
              newCustomerRate: data.summary?.new_customer_rate || 0,
              avgSpend: data.summary?.avg_spend || 0,
            },
            topCustomers: data.top_customers || [],
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message || "Failed to fetch customers report";
        console.error("Failed to fetch customers report:", err);
        throw err;
      } finally {
        this.loading.customers = false;
      }
    },

    /**
     * Fetch customer acquisition report
     * @requirement RPT-010 Create customer acquisition report
     */
    async fetchCustomerAcquisition(params = {}) {
      this.loading.acquisition = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          group_by: this.dateFilters.groupBy,
          ...params,
        };

        const response = await adminDashboard.getCustomerAcquisitionReport(
          mergedParams
        );

        if (response.success && response.data) {
          const data = response.data;

          this.customerAcquisition = {
            totalNewCustomers: data.total_new_customers || 0,
            conversionRate: data.conversion_rate || 0,
            customersWithOrders: data.customers_with_orders || 0,
            byPeriod: data.by_period || [],
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message ||
          "Failed to fetch customer acquisition report";
        console.error("Failed to fetch customer acquisition report:", err);
        throw err;
      } finally {
        this.loading.acquisition = false;
      }
    },

    /**
     * Fetch staff performance report
     * @requirement RPT-011 Create staff performance report
     */
    async fetchStaffPerformance(params = {}) {
      this.loading.staff = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          ...params,
        };

        const response = await adminDashboard.getStaffPerformanceReport(
          mergedParams
        );

        if (response.success && response.data) {
          this.staffPerformance = {
            staff: response.data.staff || [],
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message ||
          "Failed to fetch staff performance report";
        console.error("Failed to fetch staff performance report:", err);
        throw err;
      } finally {
        this.loading.staff = false;
      }
    },

    /**
     * Fetch delivery performance report
     * @requirement RPT-012 Create delivery performance report
     */
    async fetchDeliveryPerformance(params = {}) {
      this.loading.deliveries = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          ...params,
        };

        const response = await adminDashboard.getDeliveryPerformanceReport(
          mergedParams
        );

        if (response.success && response.data) {
          const data = response.data;

          this.deliveryPerformance = {
            summary: {
              totalDeliveries: data.summary?.total_deliveries || 0,
              completedDeliveries: data.summary?.completed_deliveries || 0,
              onTimeDeliveries: data.summary?.on_time_deliveries || 0,
              onTimeRate: data.summary?.on_time_rate || 0,
              avgDeliveryFee: data.summary?.avg_delivery_fee || 0,
            },
            byZone: data.by_zone || [],
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message ||
          "Failed to fetch delivery performance report";
        console.error("Failed to fetch delivery performance report:", err);
        throw err;
      } finally {
        this.loading.deliveries = false;
      }
    },

    /**
     * Fetch inventory report
     * @requirement RPT-013 Create inventory report
     */
    async fetchInventoryReport(params = {}) {
      this.loading.inventory = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          ...params,
        };

        const response = await adminDashboard.getInventoryReport(mergedParams);

        if (response.success && response.data) {
          const data = response.data;

          this.inventory = {
            stockLevels: {
              totalProducts: data.stock_levels?.total_products || 0,
              activeProducts: data.stock_levels?.active_products || 0,
              lowStock: data.stock_levels?.low_stock || 0,
              outOfStock: data.stock_levels?.out_of_stock || 0,
            },
            movements: {
              stockReceived: data.movements?.stock_received || 0,
              stockSold: data.movements?.stock_sold || 0,
              stockAdjusted: data.movements?.stock_adjusted || 0,
            },
            waste: {
              count: data.waste?.count || 0,
              quantity: data.waste?.quantity || 0,
              estimatedValue: data.waste?.estimated_value || 0,
            },
            turnoverRate: data.turnover_rate || 0,
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message || "Failed to fetch inventory report";
        console.error("Failed to fetch inventory report:", err);
        throw err;
      } finally {
        this.loading.inventory = false;
      }
    },

    /**
     * Fetch financial summary report
     * @requirement RPT-014 Create financial summary report
     */
    async fetchFinancialSummary(params = {}) {
      this.loading.financial = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          ...params,
        };

        const response = await adminDashboard.getFinancialSummaryReport(
          mergedParams
        );

        if (response.success && response.data) {
          const data = response.data;

          this.financial = {
            summary: {
              grossRevenue: data.summary?.gross_revenue || 0,
              productRevenue: data.summary?.product_revenue || 0,
              deliveryFees: data.summary?.delivery_fees || 0,
              discounts: data.summary?.discounts || 0,
              refunds: data.summary?.refunds || 0,
              netRevenue: data.summary?.net_revenue || 0,
            },
            dailyBreakdown: data.daily_breakdown || [],
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message || "Failed to fetch financial summary";
        console.error("Failed to fetch financial summary:", err);
        throw err;
      } finally {
        this.loading.financial = false;
      }
    },

    /**
     * Fetch payment methods report
     * @requirement RPT-015 Create payment methods report
     */
    async fetchPaymentMethods(params = {}) {
      this.loading.payments = true;
      this.error = null;

      try {
        const mergedParams = {
          ...this.getDateParams(),
          ...params,
        };

        const response = await adminDashboard.getPaymentMethodsReport(
          mergedParams
        );

        if (response.success && response.data) {
          const data = response.data;

          this.paymentMethods = {
            totalOrders: data.total_orders || 0,
            ordersByStatus: data.orders_by_status || [],
          };
        }

        return response;
      } catch (err) {
        this.error =
          err.response?.data?.message ||
          "Failed to fetch payment methods report";
        console.error("Failed to fetch payment methods report:", err);
        throw err;
      } finally {
        this.loading.payments = false;
      }
    },

    /**
     * Export report as PDF
     * @requirement RPT-018 Export report to PDF (View)
     * @requirement RPT-019 Export report to PDF (Download)
     */
    async exportReport(type, action = "view") {
      this.loading.export = true;
      this.error = null;

      try {
        const params = {
          ...this.getDateParams(),
          action,
        };

        const response = await adminDashboard.exportReport(type, params);

        // Create blob URL from PDF data
        const blob = new Blob([response.data], { type: "application/pdf" });
        const url = window.URL.createObjectURL(blob);

        // Create filename
        const filename = `${type.replace(/_/g, "-")}-report-${
          new Date().toISOString().split("T")[0]
        }.pdf`;

        if (action === "view") {
          // Open PDF in new tab
          window.open(url, "_blank");
          // Clean up the URL after a short delay
          setTimeout(() => window.URL.revokeObjectURL(url), 100);
        } else {
          // Download the PDF
          const link = document.createElement("a");
          link.href = url;
          link.download = filename;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          // Clean up the URL
          window.URL.revokeObjectURL(url);
        }

        return { success: true };
      } catch (err) {
        this.error = err.response?.data?.message || "Failed to export report";
        console.error("Failed to export report:", err);
        throw err;
      } finally {
        this.loading.export = false;
      }
    },

    /**
     * Set date filter preset
     * @requirement RPT-016 Implement date range selector
     */
    setDatePreset(preset) {
      this.dateFilters.preset = preset;

      // Clear custom dates when using preset
      if (preset !== "custom") {
        this.dateFilters.startDate = "";
        this.dateFilters.endDate = "";
      }
    },

    /**
     * Set custom date range
     * @requirement RPT-016 Implement date range selector
     */
    setCustomDateRange(startDate, endDate) {
      this.dateFilters.preset = "custom";
      this.dateFilters.startDate = startDate;
      this.dateFilters.endDate = endDate;
    },

    /**
     * Set group by option
     */
    setGroupBy(groupBy) {
      this.dateFilters.groupBy = groupBy;
    },

    /**
     * Get date params for API calls
     */
    getDateParams() {
      const params = {};

      if (this.dateFilters.preset && this.dateFilters.preset !== "custom") {
        params.preset = this.dateFilters.preset;
      } else if (this.dateFilters.startDate && this.dateFilters.endDate) {
        params.start_date = this.dateFilters.startDate;
        params.end_date = this.dateFilters.endDate;
      }

      return params;
    },

    /**
     * Reset all filters
     */
    resetFilters() {
      this.dateFilters = {
        preset: "month",
        startDate: "",
        endDate: "",
        groupBy: "day",
      };
    },

    /**
     * Clear error state
     */
    clearError() {
      this.error = null;
    },

    /**
     * Reset store to initial state
     */
    $reset() {
      this.$state = this.$options.state();
    },
  },
});
