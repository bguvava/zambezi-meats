/**
 * Orders Store
 *
 * Manages customer orders state and operations.
 *
 * @requirement PROJ-INIT-010 Configure Pinia store structure
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/services/api";

export const useOrdersStore = defineStore("orders", () => {
  // State
  const orders = ref([]);
  const currentOrder = ref(null);
  const isLoading = ref(false);
  const error = ref(null);

  // Pagination
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0,
  });

  // Order statuses
  const ORDER_STATUSES = {
    pending: { label: "Pending", color: "yellow" },
    confirmed: { label: "Confirmed", color: "blue" },
    processing: { label: "Processing", color: "indigo" },
    ready: { label: "Ready for Delivery", color: "purple" },
    out_for_delivery: { label: "Out for Delivery", color: "orange" },
    delivered: { label: "Delivered", color: "green" },
    cancelled: { label: "Cancelled", color: "red" },
  };

  // Getters
  const pendingOrders = computed(() =>
    orders.value.filter((order) => order.status === "pending")
  );

  const activeOrders = computed(() =>
    orders.value.filter((order) =>
      [
        "pending",
        "confirmed",
        "processing",
        "ready",
        "out_for_delivery",
      ].includes(order.status)
    )
  );

  const completedOrders = computed(() =>
    orders.value.filter((order) => order.status === "delivered")
  );

  // Actions

  /**
   * Fetch customer orders
   * @param {Object} options - Query options
   */
  async function fetchOrders(options = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {
        page: options.page || pagination.value.currentPage,
        per_page: options.perPage || pagination.value.perPage,
        ...options,
      };

      const response = await api.get("/customer/orders", { params });

      orders.value = response.data.data || response.data;
      if (response.data.meta) {
        pagination.value = {
          currentPage: response.data.meta.current_page,
          lastPage: response.data.meta.last_page,
          perPage: response.data.meta.per_page,
          total: response.data.meta.total,
        };
      }

      return orders.value;
    } catch (err) {
      error.value = err.message || "Failed to fetch orders";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch single order by order number
   * @param {string} orderNumber - Order number
   */
  async function fetchOrder(orderNumber) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/customer/orders/${orderNumber}`);
      currentOrder.value = response.data.data || response.data;
      return currentOrder.value;
    } catch (err) {
      error.value = err.message || "Failed to fetch order";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Create new order
   * @param {Object} orderData - Order data
   */
  async function createOrder(orderData) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.post("/customer/orders", orderData);
      currentOrder.value = response.data.data || response.data;
      return { success: true, order: currentOrder.value };
    } catch (err) {
      error.value = err.message || "Failed to create order";
      return {
        success: false,
        message: err.response?.data?.message || "Failed to create order",
        errors: err.response?.data?.errors || {},
      };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Cancel order
   * @param {string} orderNumber - Order number
   */
  async function cancelOrder(orderNumber) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.post(`/customer/orders/${orderNumber}/cancel`);
      return { success: true, message: "Order cancelled successfully" };
    } catch (err) {
      error.value = err.message || "Failed to cancel order";
      return {
        success: false,
        message: err.response?.data?.message || "Failed to cancel order",
      };
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Get status config
   * @param {string} status - Status key
   */
  function getStatusConfig(status) {
    return ORDER_STATUSES[status] || { label: status, color: "gray" };
  }

  /**
   * Clear current order
   */
  function clearCurrentOrder() {
    currentOrder.value = null;
  }

  return {
    // State
    orders,
    currentOrder,
    isLoading,
    error,
    pagination,
    ORDER_STATUSES,

    // Getters
    pendingOrders,
    activeOrders,
    completedOrders,

    // Actions
    fetchOrders,
    fetchOrder,
    createOrder,
    cancelOrder,
    getStatusConfig,
    clearCurrentOrder,
  };
});
