/**
 * Staff Stock Store
 *
 * Manages stock check state for staff dashboard including
 * inventory viewing, stock count updates, and low stock alerts.
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { staffDashboard } from "@/services/dashboard";

export const useStaffStockStore = defineStore("staffStock", () => {
  // State
  const products = ref([]);
  const selectedProduct = ref(null);
  const isLoading = ref(false);
  const isUpdating = ref(false);
  const error = ref(null);
  const pagination = ref({
    currentPage: 1,
    totalPages: 1,
    total: 0,
    perPage: 20,
  });
  const filters = ref({
    search: "",
    category: "",
    stockStatus: "", // 'low', 'out', 'normal'
    sortBy: "name",
    sortOrder: "asc",
  });

  // Computed
  const hasProducts = computed(() => products.value.length > 0);

  const lowStockProducts = computed(() =>
    products.value.filter(
      (p) =>
        p.stock_quantity > 0 &&
        p.stock_quantity <= (p.low_stock_threshold || 10)
    )
  );

  const outOfStockProducts = computed(() =>
    products.value.filter((p) => p.stock_quantity <= 0)
  );

  const normalStockProducts = computed(() =>
    products.value.filter(
      (p) => p.stock_quantity > (p.low_stock_threshold || 10)
    )
  );

  const stockStats = computed(() => ({
    total: products.value.length,
    lowStock: lowStockProducts.value.length,
    outOfStock: outOfStockProducts.value.length,
    normal: normalStockProducts.value.length,
  }));

  const filteredProducts = computed(() => {
    let result = [...products.value];

    // Filter by search
    if (filters.value.search) {
      const search = filters.value.search.toLowerCase();
      result = result.filter(
        (p) =>
          p.name?.toLowerCase().includes(search) ||
          p.sku?.toLowerCase().includes(search)
      );
    }

    // Filter by category
    if (filters.value.category) {
      result = result.filter((p) => p.category_id === filters.value.category);
    }

    // Filter by stock status
    if (filters.value.stockStatus === "low") {
      result = result.filter(
        (p) =>
          p.stock_quantity > 0 &&
          p.stock_quantity <= (p.low_stock_threshold || 10)
      );
    } else if (filters.value.stockStatus === "out") {
      result = result.filter((p) => p.stock_quantity <= 0);
    } else if (filters.value.stockStatus === "normal") {
      result = result.filter(
        (p) => p.stock_quantity > (p.low_stock_threshold || 10)
      );
    }

    return result;
  });

  // Actions
  async function fetchStockCheck(params = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const queryParams = {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
        ...params,
      };

      const response = await staffDashboard.getStockCheck(queryParams);

      if (response.success) {
        products.value = response.data || response.products || [];
        if (response.meta) {
          pagination.value = {
            currentPage: response.meta.current_page,
            totalPages: response.meta.last_page,
            total: response.meta.total,
            perPage: response.meta.per_page,
          };
        }
      } else {
        throw new Error(response.message || "Failed to fetch stock");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to load stock check";
      console.error("Error fetching stock:", err);
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateStock(productId, quantity, notes = null) {
    isUpdating.value = true;
    error.value = null;

    try {
      const response = await staffDashboard.updateStock(
        productId,
        quantity,
        notes
      );

      if (response.success) {
        // Update local product stock
        const index = products.value.findIndex((p) => p.id === productId);
        if (index !== -1) {
          products.value[index] = {
            ...products.value[index],
            stock_quantity: quantity,
            updated_at: new Date().toISOString(),
          };
        }
      } else {
        throw new Error(response.message || "Failed to update stock");
      }

      return response;
    } catch (err) {
      error.value = err.message || "Failed to update stock";
      console.error("Error updating stock:", err);
      throw err;
    } finally {
      isUpdating.value = false;
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
  }

  function setPage(page) {
    pagination.value.currentPage = page;
  }

  function selectProduct(product) {
    selectedProduct.value = product;
  }

  function clearSelectedProduct() {
    selectedProduct.value = null;
  }

  function clearError() {
    error.value = null;
  }

  function $reset() {
    products.value = [];
    selectedProduct.value = null;
    isLoading.value = false;
    isUpdating.value = false;
    error.value = null;
    pagination.value = {
      currentPage: 1,
      totalPages: 1,
      total: 0,
      perPage: 20,
    };
    filters.value = {
      search: "",
      category: "",
      stockStatus: "",
      sortBy: "name",
      sortOrder: "asc",
    };
  }

  return {
    // State
    products,
    selectedProduct,
    isLoading,
    isUpdating,
    error,
    pagination,
    filters,

    // Computed
    hasProducts,
    lowStockProducts,
    outOfStockProducts,
    normalStockProducts,
    stockStats,
    filteredProducts,

    // Actions
    fetchStockCheck,
    updateStock,
    setFilters,
    setPage,
    selectProduct,
    clearSelectedProduct,
    clearError,
    $reset,
  };
});
