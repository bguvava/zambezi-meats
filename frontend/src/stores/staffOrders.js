/**
 * Staff Orders Queue Store
 *
 * Manages order queue state for staff dashboard operations including
 * order processing, status updates, packing checklists, and notes.
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { staffDashboard } from "@/services/dashboard";

export const useStaffOrdersStore = defineStore("staffOrders", () => {
  // State
  const orders = ref([]);
  const selectedOrder = ref(null);
  const isLoading = ref(false);
  const isUpdating = ref(false);
  const error = ref(null);
  const pagination = ref({
    currentPage: 1,
    totalPages: 1,
    total: 0,
    perPage: 15,
  });
  const filters = ref({
    status: "",
    search: "",
    date: "today",
    deliveryType: "",
  });

  // Computed
  const hasOrders = computed(() => orders.value.length > 0);

  const pendingOrders = computed(() =>
    orders.value.filter((order) => order.status === "pending")
  );

  const processingOrders = computed(() =>
    orders.value.filter((order) => order.status === "processing")
  );

  const readyOrders = computed(() =>
    orders.value.filter(
      (order) =>
        order.status === "ready_for_pickup" ||
        order.status === "ready_for_delivery"
    )
  );

  const outForDeliveryOrders = computed(() =>
    orders.value.filter((order) => order.status === "out_for_delivery")
  );

  const orderCounts = computed(() => ({
    all: orders.value.length,
    pending: pendingOrders.value.length,
    processing: processingOrders.value.length,
    ready: readyOrders.value.length,
    outForDelivery: outForDeliveryOrders.value.length,
  }));

  // Actions
  async function fetchOrders(params = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const queryParams = {
        ...filters.value,
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
        ...params,
      };

      const response = await staffDashboard.getOrderQueue(queryParams);

      if (response.success) {
        orders.value = response.data || response.orders || [];
        if (response.meta) {
          pagination.value = {
            currentPage: response.meta.current_page,
            totalPages: response.meta.last_page,
            total: response.meta.total,
            perPage: response.meta.per_page,
          };
        }
      } else {
        throw new Error(response.message || "Failed to fetch orders");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to load order queue";
      console.error("Error fetching order queue:", err);
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchOrder(orderId) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await staffDashboard.getOrder(orderId);

      if (response.success) {
        selectedOrder.value = response.data || response.order;
      } else {
        throw new Error(response.message || "Failed to fetch order");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to load order details";
      console.error("Error fetching order:", err);
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateOrderStatus(orderId, status, notes = null) {
    isUpdating.value = true;
    error.value = null;

    try {
      const response = await staffDashboard.updateOrderStatus(
        orderId,
        status,
        notes
      );

      if (response.success) {
        // Update local order
        const index = orders.value.findIndex((o) => o.id === orderId);
        if (index !== -1) {
          orders.value[index] = {
            ...orders.value[index],
            status,
            updated_at: new Date().toISOString(),
          };
        }

        // Update selected order if it's the same
        if (selectedOrder.value?.id === orderId) {
          selectedOrder.value = {
            ...selectedOrder.value,
            status,
            updated_at: new Date().toISOString(),
          };
        }
      } else {
        throw new Error(response.message || "Failed to update order status");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to update order status";
      console.error("Error updating order status:", err);
      throw err;
    } finally {
      isUpdating.value = false;
    }
  }

  async function addNote(orderId, note) {
    isUpdating.value = true;
    error.value = null;

    try {
      const response = await staffDashboard.addOrderNote(orderId, note);

      if (response.success && selectedOrder.value?.id === orderId) {
        // Refresh order to get updated notes
        await fetchOrder(orderId);
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to add note";
      console.error("Error adding order note:", err);
      throw err;
    } finally {
      isUpdating.value = false;
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
    pagination.value.currentPage = 1;
  }

  function setPage(page) {
    pagination.value.currentPage = page;
  }

  function selectOrder(order) {
    selectedOrder.value = order;
  }

  function clearSelectedOrder() {
    selectedOrder.value = null;
  }

  function clearError() {
    error.value = null;
  }

  function $reset() {
    orders.value = [];
    selectedOrder.value = null;
    isLoading.value = false;
    isUpdating.value = false;
    error.value = null;
    pagination.value = {
      currentPage: 1,
      totalPages: 1,
      total: 0,
      perPage: 15,
    };
    filters.value = {
      status: "",
      search: "",
      date: "today",
      deliveryType: "",
    };
  }

  return {
    // State
    orders,
    selectedOrder,
    isLoading,
    isUpdating,
    error,
    pagination,
    filters,

    // Computed
    hasOrders,
    pendingOrders,
    processingOrders,
    readyOrders,
    outForDeliveryOrders,
    orderCounts,

    // Actions
    fetchOrders,
    fetchOrder,
    updateOrderStatus,
    addNote,
    setFilters,
    setPage,
    selectOrder,
    clearSelectedOrder,
    clearError,
    $reset,
  };
});
