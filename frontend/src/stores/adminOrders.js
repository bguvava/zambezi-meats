/**
 * Admin Orders Store
 *
 * Manages admin order management state and actions.
 *
 * @requirement ADMIN-005 Create orders management page
 * @requirement ADMIN-006 Implement order filtering
 * @requirement ADMIN-007 Create order detail view
 * @requirement ADMIN-008 Implement order actions
 * @requirement ADMIN-009 Assign orders to staff
 * @requirement ADMIN-010 Process refunds
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { adminDashboard } from "@/services/dashboard";

export const useAdminOrdersStore = defineStore("adminOrders", () => {
  // State
  const orders = ref([]);
  const currentOrder = ref(null);
  const isLoading = ref(false);
  const error = ref(null);
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });
  const filters = ref({
    status: "all",
    search: "",
    dateFrom: null,
    dateTo: null,
  });

  // Order statuses
  const orderStatuses = [
    { value: "all", label: "All Orders", color: "gray" },
    { value: "pending", label: "Pending", color: "yellow" },
    { value: "confirmed", label: "Confirmed", color: "blue" },
    { value: "processing", label: "Processing", color: "indigo" },
    { value: "ready", label: "Ready", color: "purple" },
    { value: "out_for_delivery", label: "Out for Delivery", color: "orange" },
    { value: "delivered", label: "Delivered", color: "green" },
    { value: "cancelled", label: "Cancelled", color: "red" },
  ];

  // Getters
  const hasOrders = computed(() => orders.value.length > 0);

  const pendingOrdersCount = computed(
    () => orders.value.filter((o) => o.status === "pending").length
  );

  const getStatusColor = computed(() => (status) => {
    const statusObj = orderStatuses.find((s) => s.value === status);
    return statusObj?.color || "gray";
  });

  const getStatusLabel = computed(() => (status) => {
    const statusObj = orderStatuses.find((s) => s.value === status);
    return statusObj?.label || status;
  });

  // Actions
  async function fetchOrders(page = 1) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
      };

      if (filters.value.status !== "all") {
        params.status = filters.value.status;
      }
      if (filters.value.search) {
        params.search = filters.value.search;
      }
      if (filters.value.dateFrom) {
        params.date_from = filters.value.dateFrom;
      }
      if (filters.value.dateTo) {
        params.date_to = filters.value.dateTo;
      }

      const response = await adminDashboard.getOrders(params);

      if (response.success) {
        orders.value = response.orders || [];
        pagination.value = {
          currentPage: response.pagination?.current_page || 1,
          lastPage: response.pagination?.last_page || 1,
          perPage: response.pagination?.per_page || 15,
          total: response.pagination?.total || 0,
        };
      }
    } catch (err) {
      console.error("Failed to fetch orders:", err);
      error.value = err.message || "Failed to load orders";
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchOrder(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getOrder(id);

      if (response.success) {
        currentOrder.value = response.order;
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch order:", err);
      error.value = err.message || "Failed to load order";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateOrder(id, data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updateOrder(id, data);

      if (response.success) {
        currentOrder.value = response.order;
        // Update order in list
        const index = orders.value.findIndex((o) => o.id === id);
        if (index !== -1) {
          orders.value[index] = response.order;
        }
      }

      return response;
    } catch (err) {
      console.error("Failed to update order:", err);
      error.value = err.message || "Failed to update order";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateOrderStatus(id, status, reason = null) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updateOrderStatus(
        id,
        status,
        reason
      );

      if (response.success) {
        // Update order in list
        const index = orders.value.findIndex((o) => o.id === id);
        if (index !== -1) {
          orders.value[index] = { ...orders.value[index], status };
        }
        if (currentOrder.value?.id === id) {
          currentOrder.value = { ...currentOrder.value, status };
        }
      }

      return response;
    } catch (err) {
      console.error("Failed to update order status:", err);
      error.value = err.message || "Failed to update order status";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function assignOrder(orderId, staffId) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.assignOrder(orderId, staffId);

      if (response.success && response.order) {
        // Update order in list
        const index = orders.value.findIndex((o) => o.id === orderId);
        if (index !== -1) {
          orders.value[index] = response.order;
        }
        if (currentOrder.value?.id === orderId) {
          currentOrder.value = response.order;
        }
      }

      return response;
    } catch (err) {
      console.error("Failed to assign order:", err);
      error.value = err.message || "Failed to assign order";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function refundOrder(orderId, amount, reason) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.refundOrder(
        orderId,
        amount,
        reason
      );

      if (response.success && response.order) {
        // Update order in list
        const index = orders.value.findIndex((o) => o.id === orderId);
        if (index !== -1) {
          orders.value[index] = response.order;
        }
        if (currentOrder.value?.id === orderId) {
          currentOrder.value = response.order;
        }
      }

      return response;
    } catch (err) {
      console.error("Failed to process refund:", err);
      error.value = err.message || "Failed to process refund";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
  }

  function resetFilters() {
    filters.value = {
      status: "all",
      search: "",
      dateFrom: null,
      dateTo: null,
    };
  }

  function clearError() {
    error.value = null;
  }

  function $reset() {
    orders.value = [];
    currentOrder.value = null;
    isLoading.value = false;
    error.value = null;
    pagination.value = {
      currentPage: 1,
      lastPage: 1,
      perPage: 15,
      total: 0,
    };
    resetFilters();
  }

  return {
    // State
    orders,
    currentOrder,
    isLoading,
    error,
    pagination,
    filters,
    orderStatuses,

    // Getters
    hasOrders,
    pendingOrdersCount,
    getStatusColor,
    getStatusLabel,

    // Actions
    fetchOrders,
    fetchOrder,
    updateOrder,
    updateOrderStatus,
    assignOrder,
    refundOrder,
    setFilters,
    resetFilters,
    clearError,
    $reset,
  };
});
