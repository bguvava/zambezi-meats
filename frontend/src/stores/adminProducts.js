/**
 * Admin Products Store
 *
 * Manages admin product management state and actions.
 *
 * @requirement ADMIN-011 Create products management page
 * @requirement ADMIN-012 Create add/edit product form
 * @requirement ADMIN-013 Implement product image upload
 * @requirement ADMIN-015 Delete products (single)
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { adminDashboard } from "@/services/dashboard";

export const useAdminProductsStore = defineStore("adminProducts", () => {
  // State
  const products = ref([]);
  const currentProduct = ref(null);
  const lowStockProducts = ref([]);
  const isLoading = ref(false);
  const error = ref(null);
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });
  const filters = ref({
    categoryId: null,
    status: "all",
    stockStatus: null,
    search: "",
  });

  // Getters
  const hasProducts = computed(() => products.value.length > 0);

  const activeProductsCount = computed(
    () => products.value.filter((p) => p.is_active).length
  );

  const outOfStockCount = computed(
    () => products.value.filter((p) => p.stock <= 0).length
  );

  const lowStockCount = computed(
    () => products.value.filter((p) => p.stock > 0 && p.stock <= 10).length
  );

  // Actions
  async function fetchProducts(page = 1) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
      };

      if (filters.value.categoryId) {
        params.category_id = filters.value.categoryId;
      }
      if (filters.value.status !== "all") {
        params.status = filters.value.status;
      }
      if (filters.value.stockStatus) {
        params.stock_status = filters.value.stockStatus;
      }
      if (filters.value.search) {
        params.search = filters.value.search;
      }

      const response = await adminDashboard.getProducts(params);

      if (response.success) {
        products.value = response.products || [];
        pagination.value = {
          currentPage: response.pagination?.current_page || 1,
          lastPage: response.pagination?.last_page || 1,
          perPage: response.pagination?.per_page || 15,
          total: response.pagination?.total || 0,
        };
      }
    } catch (err) {
      console.error("Failed to fetch products:", err);
      error.value = err.message || "Failed to load products";
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchProduct(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getProduct(id);

      if (response.success) {
        currentProduct.value = response.product;
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch product:", err);
      error.value = err.message || "Failed to load product";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function createProduct(formData) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.createProduct(formData);

      if (response.success && response.product) {
        products.value.unshift(response.product);
        pagination.value.total += 1;
      }

      return response;
    } catch (err) {
      console.error("Failed to create product:", err);
      error.value = err.message || "Failed to create product";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateProduct(id, formData) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updateProduct(id, formData);

      if (response.success && response.product) {
        const index = products.value.findIndex((p) => p.id === id);
        if (index !== -1) {
          products.value[index] = response.product;
        }
        currentProduct.value = response.product;
      }

      return response;
    } catch (err) {
      console.error("Failed to update product:", err);
      error.value = err.message || "Failed to update product";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function deleteProduct(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.deleteProduct(id);

      if (response.success) {
        const index = products.value.findIndex((p) => p.id === id);
        if (index !== -1) {
          // Soft delete - mark as inactive
          products.value[index].is_active = false;
        }
        pagination.value.total -= 1;
      }

      return response;
    } catch (err) {
      console.error("Failed to delete product:", err);
      error.value = err.message || "Failed to delete product";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchLowStockProducts(threshold = 10) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getLowStockProducts(threshold);

      if (response.success) {
        lowStockProducts.value = response.products || [];
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch low stock products:", err);
      error.value = err.message || "Failed to load low stock products";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function adjustStock(productId, quantity, type, reason) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.adjustStock(
        productId,
        quantity,
        type,
        reason
      );

      if (response.success && response.product) {
        const index = products.value.findIndex((p) => p.id === productId);
        if (index !== -1) {
          products.value[index] = response.product;
        }
        if (currentProduct.value?.id === productId) {
          currentProduct.value = response.product;
        }
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

  async function exportProducts(params = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const blob = await adminDashboard.exportProducts(params);
      return blob;
    } catch (err) {
      console.error("Failed to export products:", err);
      error.value = err.message || "Failed to export products";
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
      categoryId: null,
      status: "all",
      stockStatus: null,
      search: "",
    };
  }

  function clearError() {
    error.value = null;
  }

  function $reset() {
    products.value = [];
    currentProduct.value = null;
    lowStockProducts.value = [];
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
    products,
    currentProduct,
    lowStockProducts,
    isLoading,
    error,
    pagination,
    filters,

    // Getters
    hasProducts,
    activeProductsCount,
    outOfStockCount,
    lowStockCount,

    // Actions
    fetchProducts,
    fetchProduct,
    createProduct,
    updateProduct,
    deleteProduct,
    fetchLowStockProducts,
    adjustStock,
    exportProducts,
    setFilters,
    resetFilters,
    clearError,
    $reset,
  };
});
