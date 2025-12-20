/**
 * Admin Customers Store
 *
 * Manages admin customer management state and actions.
 *
 * @requirement ADMIN-016 Create customers management page
 * @requirement ADMIN-017 View customer details
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { adminDashboard } from "@/services/dashboard";

export const useAdminCustomersStore = defineStore("adminCustomers", () => {
  // State
  const customers = ref([]);
  const currentCustomer = ref(null);
  const customerOrders = ref([]);
  const customerAddresses = ref([]);
  const isLoading = ref(false);
  const error = ref(null);
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });
  const filters = ref({
    search: "",
  });

  // Getters
  const hasCustomers = computed(() => customers.value.length > 0);

  const totalCustomers = computed(() => pagination.value.total);

  const activeCustomersCount = computed(
    () => customers.value.filter((c) => c.is_active !== false).length
  );

  // Actions
  async function fetchCustomers(page = 1) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
      };

      if (filters.value.search) {
        params.search = filters.value.search;
      }

      const response = await adminDashboard.getCustomers(params);

      if (response.success) {
        customers.value = response.customers || [];
        pagination.value = {
          currentPage: response.pagination?.current_page || 1,
          lastPage: response.pagination?.last_page || 1,
          perPage: response.pagination?.per_page || 15,
          total: response.pagination?.total || 0,
        };
      }
    } catch (err) {
      console.error("Failed to fetch customers:", err);
      error.value = err.message || "Failed to load customers";
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchCustomer(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getCustomer(id);

      if (response.success) {
        currentCustomer.value = response.customer;
        customerOrders.value = response.recent_orders || [];
        customerAddresses.value = response.addresses || [];
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch customer:", err);
      error.value = err.message || "Failed to load customer";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateCustomer(id, data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updateCustomer(id, data);

      if (response.success && response.customer) {
        const index = customers.value.findIndex((c) => c.id === id);
        if (index !== -1) {
          customers.value[index] = response.customer;
        }
        currentCustomer.value = response.customer;
      }

      return response;
    } catch (err) {
      console.error("Failed to update customer:", err);
      error.value = err.message || "Failed to update customer";
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
      search: "",
    };
  }

  function clearError() {
    error.value = null;
  }

  function $reset() {
    customers.value = [];
    currentCustomer.value = null;
    customerOrders.value = [];
    customerAddresses.value = [];
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
    customers,
    currentCustomer,
    customerOrders,
    customerAddresses,
    isLoading,
    error,
    pagination,
    filters,

    // Getters
    hasCustomers,
    totalCustomers,
    activeCustomersCount,

    // Actions
    fetchCustomers,
    fetchCustomer,
    updateCustomer,
    setFilters,
    resetFilters,
    clearError,
    $reset,
  };
});
