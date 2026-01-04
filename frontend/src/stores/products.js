/**
 * Products Store
 *
 * Manages products and categories state with API integration.
 *
 * @requirement PROJ-INIT-010 Configure Pinia store structure
 * @requirement SHOP-025 Products Pinia store with filtering, pagination, search
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/services/api";

export const useProductsStore = defineStore("products", () => {
  // State
  const products = ref([]);
  const categories = ref([]);
  const currentProduct = ref(null);
  const currentCategory = ref(null);
  const relatedProducts = ref([]);
  const searchResults = ref([]);
  const isLoading = ref(false);
  const isSearching = ref(false);
  const error = ref(null);

  // Pagination
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 12,
    total: 0,
  });

  // Filters
  const filters = ref({
    category: null,
    search: "",
    minPrice: null,
    maxPrice: null,
    inStock: null,
    sort: "created_at",
    direction: "desc",
  });

  // Getters
  const featuredProducts = computed(() =>
    products.value.filter((product) => product.is_featured)
  );

  const activeCategories = computed(() =>
    categories.value.filter((category) => category.is_active)
  );

  const hasProducts = computed(() => products.value.length > 0);

  const hasMore = computed(
    () => pagination.value.currentPage < pagination.value.lastPage
  );

  // Actions

  /**
   * Fetch products with filters and pagination
   * @requirement SHOP-021 Products API with filtering/sorting/pagination
   * @param {Object} options - Query options
   */
  async function fetchProducts(options = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {
        page: options.page || pagination.value.currentPage,
        per_page: options.perPage || pagination.value.perPage,
      };

      // Apply filters
      if (filters.value.category) params.category = filters.value.category;
      if (filters.value.search) params.search = filters.value.search;
      if (filters.value.minPrice) params.min_price = filters.value.minPrice;
      if (filters.value.maxPrice) params.max_price = filters.value.maxPrice;
      if (filters.value.inStock !== null)
        params.in_stock = filters.value.inStock;
      if (filters.value.sort) params.sort = filters.value.sort;
      if (filters.value.direction) params.direction = filters.value.direction;

      // Override with passed options
      Object.assign(params, options);

      const response = await api.get("/products", { params });

      products.value = response.data.data || response.data;
      if (response.data.meta) {
        pagination.value = {
          currentPage: response.data.meta.current_page,
          lastPage: response.data.meta.last_page,
          perPage: response.data.meta.per_page,
          total: response.data.meta.total,
        };
      }

      return products.value;
    } catch (err) {
      error.value = err.message || "Failed to fetch products";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch featured products
   * @requirement SHOP-006 Display featured products
   * @param {number} limit - Number of products to fetch
   */
  async function fetchFeaturedProducts(limit = 8) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get("/products/featured", {
        params: { limit },
      });

      // Process and normalize the data
      const featuredData = response.data.data || response.data;

      // If it's an array, normalize each product
      if (Array.isArray(featuredData)) {
        const normalizedProducts = featuredData.map((product) => ({
          ...product,
          // Ensure category_name is extracted from category object
          category_name: product.category?.name || product.category_name || "",
          // Ensure main_image is properly set
          main_image:
            product.primary_image?.url ||
            product.main_image ||
            product.images?.[0]?.url ||
            null,
        }));

        // Update products array with featured products
        products.value = normalizedProducts;
        return normalizedProducts;
      }

      return [];
    } catch (err) {
      error.value = err.message || "Failed to fetch featured products";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch single product by slug
   * @requirement SHOP-022 Product detail API
   * @param {string} slug - Product slug
   */
  async function fetchProduct(slug) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/products/${slug}`);
      currentProduct.value = response.data.data || response.data;
      return currentProduct.value;
    } catch (err) {
      error.value = err.message || "Failed to fetch product";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch related products for a product
   * @requirement SHOP-009 Show related products
   * @param {string} slug - Product slug
   * @param {number} limit - Number of products
   */
  async function fetchRelatedProducts(slug, limit = 4) {
    try {
      const response = await api.get(`/products/${slug}/related`, {
        params: { limit },
      });
      relatedProducts.value = response.data.data || response.data;
      return relatedProducts.value;
    } catch (err) {
      console.error("Failed to fetch related products:", err);
      relatedProducts.value = [];
      return [];
    }
  }

  /**
   * Quick search for autocomplete
   * @requirement SHOP-024 Product search API
   * @param {string} query - Search query
   */
  async function quickSearch(query) {
    if (!query || query.length < 2) {
      searchResults.value = [];
      return [];
    }

    isSearching.value = true;

    try {
      const response = await api.get("/products/search", {
        params: { q: query },
      });
      searchResults.value = response.data.data || response.data;
      return searchResults.value;
    } catch (err) {
      console.error("Search failed:", err);
      searchResults.value = [];
      return [];
    } finally {
      isSearching.value = false;
    }
  }

  /**
   * Fetch all categories
   * @requirement SHOP-023 Categories API
   * @requirement ISSUE-008 Filter to show only main categories
   * @param {Object} options - Fetch options
   */
  async function fetchCategories(options = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {};

      // Add main_only parameter if specified
      if (options.mainOnly) {
        params.main_only = true;
      }

      const response = await api.get("/categories", { params });
      categories.value = response.data.data || response.data;
      return categories.value;
    } catch (err) {
      error.value = err.message || "Failed to fetch categories";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch single category by slug
   * @param {string} slug - Category slug
   */
  async function fetchCategory(slug) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/categories/${slug}`);
      currentCategory.value = response.data.data || response.data;
      return currentCategory.value;
    } catch (err) {
      error.value = err.message || "Failed to fetch category";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Fetch products by category
   * @param {string} categorySlug - Category slug
   * @param {Object} options - Additional options
   */
  async function fetchCategoryProducts(categorySlug, options = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {
        page: options.page || 1,
        per_page: options.perPage || 12,
      };

      if (options.sort) params.sort = options.sort;
      if (options.direction) params.direction = options.direction;
      if (options.minPrice) params.min_price = options.minPrice;
      if (options.maxPrice) params.max_price = options.maxPrice;
      if (options.inStock !== undefined) params.in_stock = options.inStock;

      const response = await api.get(`/categories/${categorySlug}/products`, {
        params,
      });

      products.value = response.data.data || response.data;
      if (response.data.meta) {
        pagination.value = {
          currentPage: response.data.meta.current_page,
          lastPage: response.data.meta.last_page,
          perPage: response.data.meta.per_page,
          total: response.data.meta.total,
        };
      }

      return products.value;
    } catch (err) {
      error.value = err.message || "Failed to fetch category products";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Update filters
   * @param {Object} newFilters - New filter values
   */
  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
    pagination.value.currentPage = 1; // Reset to first page on filter change
  }

  /**
   * Reset filters to defaults
   */
  function resetFilters() {
    filters.value = {
      category: null,
      search: "",
      minPrice: null,
      maxPrice: null,
      inStock: null,
      sort: "created_at",
      direction: "desc",
    };
    pagination.value.currentPage = 1;
  }

  /**
   * Clear current product
   */
  function clearCurrentProduct() {
    currentProduct.value = null;
    relatedProducts.value = [];
  }

  /**
   * Clear search results
   */
  function clearSearch() {
    searchResults.value = [];
    filters.value.search = "";
  }

  return {
    // State
    products,
    categories,
    currentProduct,
    currentCategory,
    relatedProducts,
    searchResults,
    isLoading,
    isSearching,
    error,
    pagination,
    filters,

    // Getters
    featuredProducts,
    activeCategories,
    hasProducts,
    hasMore,

    // Actions
    fetchProducts,
    fetchFeaturedProducts,
    fetchProduct,
    fetchRelatedProducts,
    quickSearch,
    fetchCategories,
    fetchCategory,
    fetchCategoryProducts,
    setFilters,
    resetFilters,
    clearCurrentProduct,
    clearSearch,
  };
});
