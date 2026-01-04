/**
 * Admin Inventory Store
 *
 * Manages admin inventory management state and actions.
 *
 * @requirement INV-001 Create inventory dashboard
 * @requirement INV-002 Display stock levels table
 * @requirement INV-003 Implement stock filtering
 * @requirement INV-004 Create stock receive form
 * @requirement INV-005 Create stock adjustment form
 * @requirement INV-008 Set minimum stock thresholds
 * @requirement INV-009 Create low stock alerts
 * @requirement INV-010 Create out of stock alerts
 * @requirement INV-011 Display inventory history
 * @requirement INV-012 View product inventory detail
 * @requirement INV-013 Create waste management section
 * @requirement INV-014 Review and approve waste logs
 * @requirement INV-015 Generate inventory report
 * @requirement INV-016 Export inventory data
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { adminDashboard } from "@/services/dashboard";

export const useAdminInventoryStore = defineStore("adminInventory", () => {
  // ==================== STATE ====================

  // Dashboard data
  const dashboard = ref({
    total_products: 0,
    low_stock_count: 0,
    out_of_stock_count: 0,
    waste_this_month: { quantity: 0, value: 0 },
    recent_movements: [],
  });

  // Inventory list
  const inventory = ref([]);
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 20,
    total: 0,
  });

  // Filters
  const filters = ref({
    category_id: null,
    status: null,
    search: "",
  });

  // Current product detail
  const currentProduct = ref(null);

  // Inventory history
  const history = ref([]);
  const historyPagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 20,
    total: 0,
  });
  const historyFilters = ref({
    product_id: null,
    type: null,
    start_date: null,
    end_date: null,
  });

  // Alerts
  const alerts = ref({
    low_stock: [],
    out_of_stock: [],
  });
  const alertsSummary = ref({
    low_stock_count: 0,
    out_of_stock_count: 0,
    total_alerts: 0,
  });

  // Waste management
  const wasteEntries = ref([]);
  const wastePagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 20,
    total: 0,
  });
  const wasteFilters = ref({
    reason: null,
    product_id: null,
    approved: null,
    start_date: null,
    end_date: null,
  });
  const wasteSummary = ref({
    total_quantity: 0,
    total_value: 0,
    total_entries: 0,
  });

  // Report data
  const report = ref(null);

  // Loading states
  const isLoading = ref(false);
  const isDashboardLoading = ref(false);
  const isHistoryLoading = ref(false);
  const isWasteLoading = ref(false);
  const isReportLoading = ref(false);

  // Error state
  const error = ref(null);

  // ==================== GETTERS ====================

  const hasInventory = computed(() => inventory.value.length > 0);

  const hasAlerts = computed(() => alertsSummary.value.total_alerts > 0);

  const lowStockItems = computed(() =>
    inventory.value.filter(
      (item) => item.stock > 0 && item.stock <= (item.meta?.min_stock || 10)
    )
  );

  const outOfStockItems = computed(() =>
    inventory.value.filter((item) => item.stock <= 0)
  );

  const pendingWasteCount = computed(
    () => wasteEntries.value.filter((w) => !w.approved).length
  );

  // ==================== ACTIONS ====================

  /**
   * Fetch inventory dashboard
   * @requirement INV-001 Create inventory dashboard
   */
  async function fetchDashboard() {
    isDashboardLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getInventoryDashboard();

      if (response.success) {
        dashboard.value = response.data;
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch inventory dashboard:", err);
      error.value = err.message || "Failed to load inventory dashboard";
      throw err;
    } finally {
      isDashboardLoading.value = false;
    }
  }

  /**
   * Fetch inventory list
   * @requirement INV-002 Display stock levels table
   * @requirement INV-003 Implement stock filtering
   */
  async function fetchInventory(page = 1) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
        ...filters.value,
      };

      // Clean up null/empty params
      Object.keys(params).forEach((key) => {
        if (params[key] === null || params[key] === "") {
          delete params[key];
        }
      });

      const response = await adminDashboard.getInventory(params);

      if (response.success) {
        inventory.value = response.data || [];
        pagination.value = {
          currentPage: response.meta?.current_page || 1,
          lastPage: response.meta?.last_page || 1,
          perPage: response.meta?.per_page || 20,
          total: response.meta?.total || 0,
        };
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch inventory:", err);
      error.value = err.message || "Failed to load inventory";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch single product inventory detail
   * @requirement INV-012 View product inventory detail
   */
  async function fetchProductInventory(productId) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getProductInventory(productId);

      if (response.success) {
        currentProduct.value = response.data;
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch product inventory:", err);
      error.value = err.message || "Failed to load product inventory";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Receive stock
   * @requirement INV-004 Create stock receive form
   */
  async function receiveStock(data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.receiveStock(data);

      if (response.success) {
        // Update local inventory if the product exists
        const index = inventory.value.findIndex(
          (p) => p.id === data.product_id
        );
        if (index !== -1) {
          inventory.value[index].stock = response.data.stock_after;
        }

        // Refresh dashboard to update counts
        await fetchDashboard();
      }

      return response;
    } catch (err) {
      console.error("Failed to receive stock:", err);
      error.value = err.message || "Failed to receive stock";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Adjust stock
   * @requirement INV-005 Create stock adjustment form
   */
  async function adjustStock(productId, newQuantity, reason, notes = null) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.adjustStock(
        productId,
        newQuantity,
        reason,
        notes
      );

      if (response.success) {
        // Update local inventory
        const index = inventory.value.findIndex((p) => p.id === productId);
        if (index !== -1) {
          inventory.value[index].stock = response.data.stock_after;
        }

        // Refresh dashboard
        await fetchDashboard();
      }

      return response;
    } catch (err) {
      console.error("Failed to adjust stock:", err);
      error.value = err.message || "Failed to adjust stock";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Update minimum stock threshold
   * @requirement INV-008 Set minimum stock thresholds
   */
  async function updateMinStock(productId, minStock) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updateMinStock(productId, minStock);

      if (response.success) {
        // Update local inventory
        const index = inventory.value.findIndex((p) => p.id === productId);
        if (index !== -1) {
          if (!inventory.value[index].meta) {
            inventory.value[index].meta = {};
          }
          inventory.value[index].meta.min_stock = minStock;
        }

        // Refresh alerts
        await fetchAlerts();
      }

      return response;
    } catch (err) {
      console.error("Failed to update min stock:", err);
      error.value = err.message || "Failed to update minimum stock";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch inventory history
   * @requirement INV-011 Display inventory history
   */
  async function fetchHistory(page = 1) {
    isHistoryLoading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: historyPagination.value.perPage,
        ...historyFilters.value,
      };

      // Clean up null/empty params
      Object.keys(params).forEach((key) => {
        if (params[key] === null || params[key] === "") {
          delete params[key];
        }
      });

      const response = await adminDashboard.getInventoryLogs(params);

      if (response.success) {
        history.value = response.data || [];
        historyPagination.value = {
          currentPage: response.meta?.current_page || 1,
          lastPage: response.meta?.last_page || 1,
          perPage: response.meta?.per_page || 20,
          total: response.meta?.total || 0,
        };
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch inventory history:", err);
      error.value = err.message || "Failed to load inventory history";
      throw err;
    } finally {
      isHistoryLoading.value = false;
    }
  }

  /**
   * Fetch inventory alerts
   * @requirement INV-009, INV-010 Low stock and out of stock alerts
   */
  async function fetchAlerts() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getInventoryAlerts();

      if (response.success) {
        alerts.value = response.data;
        alertsSummary.value = response.summary;
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch inventory alerts:", err);
      error.value = err.message || "Failed to load inventory alerts";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch waste entries
   * @requirement INV-013 Create waste management section
   */
  async function fetchWaste(page = 1) {
    isWasteLoading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: wastePagination.value.perPage,
        ...wasteFilters.value,
      };

      // Clean up null/empty params
      Object.keys(params).forEach((key) => {
        if (params[key] === null || params[key] === "") {
          delete params[key];
        }
      });

      const response = await adminDashboard.getWasteEntries(params);

      if (response.success) {
        wasteEntries.value = response.data || [];
        wastePagination.value = {
          currentPage: response.meta?.current_page || 1,
          lastPage: response.meta?.last_page || 1,
          perPage: response.meta?.per_page || 20,
          total: response.meta?.total || 0,
        };
        wasteSummary.value = response.summary || {
          total_quantity: 0,
          total_value: 0,
          total_entries: 0,
        };
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch waste entries:", err);
      error.value = err.message || "Failed to load waste entries";
      throw err;
    } finally {
      isWasteLoading.value = false;
    }
  }

  /**
   * Approve or reject waste entry
   * @requirement INV-014 Review and approve waste logs
   */
  async function approveWaste(wasteId, approved, notes = null) {
    isWasteLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.approveWaste(
        wasteId,
        approved,
        notes
      );

      if (response.success) {
        // Update local waste entry
        const index = wasteEntries.value.findIndex((w) => w.id === wasteId);
        if (index !== -1) {
          wasteEntries.value[index].approved = approved;
          wasteEntries.value[index].approved_at = new Date().toISOString();
        }

        // Refresh dashboard
        await fetchDashboard();
      }

      return response;
    } catch (err) {
      console.error("Failed to approve/reject waste:", err);
      error.value = err.message || "Failed to process waste approval";
      throw err;
    } finally {
      isWasteLoading.value = false;
    }
  }

  /**
   * Fetch inventory report
   * @requirement INV-015 Generate inventory report
   */
  async function fetchReport(params = {}) {
    isReportLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getInventoryReport(params);

      if (response.success) {
        report.value = response.data;
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch inventory report:", err);
      error.value = err.message || "Failed to load inventory report";
      throw err;
    } finally {
      isReportLoading.value = false;
    }
  }

  /**
   * Export inventory
   * @requirement INV-016 Export inventory data
   */
  async function exportInventory(params = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.exportInventory(params);
      return response;
    } catch (err) {
      console.error("Failed to export inventory:", err);
      error.value = err.message || "Failed to export inventory";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  // ==================== FILTER ACTIONS ====================

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
  }

  function resetFilters() {
    filters.value = {
      category_id: null,
      status: null,
      search: "",
    };
  }

  function setHistoryFilters(newFilters) {
    historyFilters.value = { ...historyFilters.value, ...newFilters };
  }

  function resetHistoryFilters() {
    historyFilters.value = {
      product_id: null,
      type: null,
      start_date: null,
      end_date: null,
    };
  }

  function setWasteFilters(newFilters) {
    wasteFilters.value = { ...wasteFilters.value, ...newFilters };
  }

  function resetWasteFilters() {
    wasteFilters.value = {
      reason: null,
      product_id: null,
      approved: null,
      start_date: null,
      end_date: null,
    };
  }

  // ==================== UTILITY ====================

  function clearError() {
    error.value = null;
  }

  function $reset() {
    dashboard.value = {
      total_products: 0,
      low_stock_count: 0,
      out_of_stock_count: 0,
      waste_this_month: { quantity: 0, value: 0 },
      recent_movements: [],
    };
    inventory.value = [];
    pagination.value = {
      currentPage: 1,
      lastPage: 1,
      perPage: 20,
      total: 0,
    };
    currentProduct.value = null;
    history.value = [];
    historyPagination.value = {
      currentPage: 1,
      lastPage: 1,
      perPage: 20,
      total: 0,
    };
    alerts.value = { low_stock: [], out_of_stock: [] };
    alertsSummary.value = {
      low_stock_count: 0,
      out_of_stock_count: 0,
      total_alerts: 0,
    };
    wasteEntries.value = [];
    wastePagination.value = {
      currentPage: 1,
      lastPage: 1,
      perPage: 20,
      total: 0,
    };
    wasteSummary.value = {
      total_quantity: 0,
      total_value: 0,
      total_entries: 0,
    };
    report.value = null;
    isLoading.value = false;
    isDashboardLoading.value = false;
    isHistoryLoading.value = false;
    isWasteLoading.value = false;
    isReportLoading.value = false;
    error.value = null;
    resetFilters();
    resetHistoryFilters();
    resetWasteFilters();
  }

  return {
    // State
    dashboard,
    inventory,
    pagination,
    filters,
    currentProduct,
    history,
    historyPagination,
    historyFilters,
    alerts,
    alertsSummary,
    wasteEntries,
    wastePagination,
    wasteFilters,
    wasteSummary,
    report,
    isLoading,
    isDashboardLoading,
    isHistoryLoading,
    isWasteLoading,
    isReportLoading,
    error,

    // Getters
    hasInventory,
    hasAlerts,
    lowStockItems,
    outOfStockItems,
    pendingWasteCount,

    // Actions
    fetchDashboard,
    fetchInventory,
    fetchProductInventory,
    receiveStock,
    adjustStock,
    updateMinStock,
    fetchHistory,
    fetchAlerts,
    fetchWaste,
    approveWaste,
    fetchReport,
    exportInventory,

    // Filter actions
    setFilters,
    resetFilters,
    setHistoryFilters,
    resetHistoryFilters,
    setWasteFilters,
    resetWasteFilters,

    // Utility
    clearError,
    $reset,
  };
});
