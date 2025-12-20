/**
 * Admin Activity Logs Store
 *
 * Manages admin activity logs state and actions.
 *
 * @requirement ADMIN-023 Create audit/activity logs page
 * @requirement ADMIN-024 Implement bulk delete for activity logs
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { adminDashboard } from "@/services/dashboard";

export const useAdminLogsStore = defineStore("adminLogs", () => {
  // State
  const logs = ref([]);
  const isLoading = ref(false);
  const error = ref(null);
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 50,
    total: 0,
  });
  const filters = ref({
    userId: null,
    action: null,
    dateFrom: null,
    dateTo: null,
  });
  const selectedLogIds = ref([]);

  // Action types for filtering
  const actionTypes = [
    { value: "product_created", label: "Product Created" },
    { value: "product_updated", label: "Product Updated" },
    { value: "product_deleted", label: "Product Deleted" },
    { value: "order_updated", label: "Order Updated" },
    { value: "order_status_changed", label: "Order Status Changed" },
    { value: "order_assigned", label: "Order Assigned" },
    { value: "order_refunded", label: "Order Refunded" },
    { value: "category_created", label: "Category Created" },
    { value: "category_updated", label: "Category Updated" },
    { value: "category_deleted", label: "Category Deleted" },
    { value: "customer_updated", label: "Customer Updated" },
    { value: "staff_created", label: "Staff Created" },
    { value: "staff_updated", label: "Staff Updated" },
    { value: "staff_deleted", label: "Staff Deleted" },
    { value: "promotion_created", label: "Promotion Created" },
    { value: "promotion_updated", label: "Promotion Updated" },
    { value: "promotion_deleted", label: "Promotion Deleted" },
    { value: "stock_adjusted", label: "Stock Adjusted" },
  ];

  // Getters
  const hasLogs = computed(() => logs.value.length > 0);

  const hasSelectedLogs = computed(() => selectedLogIds.value.length > 0);

  const selectedLogsCount = computed(() => selectedLogIds.value.length);

  const isAllSelected = computed(
    () =>
      logs.value.length > 0 && selectedLogIds.value.length === logs.value.length
  );

  // Actions
  async function fetchLogs(page = 1) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
      };

      if (filters.value.userId) {
        params.user_id = filters.value.userId;
      }
      if (filters.value.action) {
        params.action = filters.value.action;
      }
      if (filters.value.dateFrom) {
        params.date_from = filters.value.dateFrom;
      }
      if (filters.value.dateTo) {
        params.date_to = filters.value.dateTo;
      }

      const response = await adminDashboard.getActivityLogs(params);

      if (response.success) {
        logs.value = response.logs || [];
        pagination.value = {
          currentPage: response.pagination?.current_page || 1,
          lastPage: response.pagination?.last_page || 1,
          perPage: response.pagination?.per_page || 50,
          total: response.pagination?.total || 0,
        };
        // Clear selection when page changes
        selectedLogIds.value = [];
      }
    } catch (err) {
      console.error("Failed to fetch logs:", err);
      error.value = err.message || "Failed to load activity logs";
    } finally {
      isLoading.value = false;
    }
  }

  async function bulkDeleteLogs(ids = null, beforeDate = null) {
    isLoading.value = true;
    error.value = null;

    try {
      const data = {};
      if (ids && ids.length > 0) {
        data.ids = ids;
      } else if (beforeDate) {
        data.before_date = beforeDate;
      } else {
        throw new Error("Please provide log IDs or a date range");
      }

      const response = await adminDashboard.bulkDeleteActivityLogs(data);

      if (response.success) {
        // Remove deleted logs from the list
        if (ids && ids.length > 0) {
          logs.value = logs.value.filter((log) => !ids.includes(log.id));
          pagination.value.total -= response.deleted_count || ids.length;
        } else {
          // Refetch logs when deleting by date
          await fetchLogs(pagination.value.currentPage);
        }
        // Clear selection
        selectedLogIds.value = [];
      }

      return response;
    } catch (err) {
      console.error("Failed to delete logs:", err);
      error.value = err.message || "Failed to delete activity logs";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function deleteSelectedLogs() {
    if (selectedLogIds.value.length === 0) {
      return { success: false, message: "No logs selected" };
    }
    return bulkDeleteLogs(selectedLogIds.value);
  }

  function toggleLogSelection(logId) {
    const index = selectedLogIds.value.indexOf(logId);
    if (index === -1) {
      selectedLogIds.value.push(logId);
    } else {
      selectedLogIds.value.splice(index, 1);
    }
  }

  function toggleAllSelection() {
    if (isAllSelected.value) {
      selectedLogIds.value = [];
    } else {
      selectedLogIds.value = logs.value.map((log) => log.id);
    }
  }

  function clearSelection() {
    selectedLogIds.value = [];
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
  }

  function resetFilters() {
    filters.value = {
      userId: null,
      action: null,
      dateFrom: null,
      dateTo: null,
    };
  }

  function clearError() {
    error.value = null;
  }

  function getActionLabel(action) {
    const actionType = actionTypes.find((a) => a.value === action);
    return (
      actionType?.label ||
      action.replace(/_/g, " ").replace(/\b\w/g, (c) => c.toUpperCase())
    );
  }

  function $reset() {
    logs.value = [];
    isLoading.value = false;
    error.value = null;
    pagination.value = {
      currentPage: 1,
      lastPage: 1,
      perPage: 50,
      total: 0,
    };
    selectedLogIds.value = [];
    resetFilters();
  }

  return {
    // State
    logs,
    isLoading,
    error,
    pagination,
    filters,
    selectedLogIds,
    actionTypes,

    // Getters
    hasLogs,
    hasSelectedLogs,
    selectedLogsCount,
    isAllSelected,

    // Actions
    fetchLogs,
    bulkDeleteLogs,
    deleteSelectedLogs,
    toggleLogSelection,
    toggleAllSelection,
    clearSelection,
    setFilters,
    resetFilters,
    getActionLabel,
    clearError,
    $reset,
  };
});
