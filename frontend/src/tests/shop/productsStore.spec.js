/**
 * Products Store Tests
 *
 * Tests for the products Pinia store.
 *
 * @requirement SHOP-025 Products Pinia store with filtering, pagination, search
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useProductsStore } from "../../stores/products";
import api from "../../services/api";

// Mock the API module
vi.mock("../../services/api", () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  },
}));

describe("Products Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    store = useProductsStore();
    vi.clearAllMocks();
  });

  describe("Initial State", () => {
    it("has empty products array", () => {
      expect(store.products).toEqual([]);
    });

    it("has empty categories array", () => {
      expect(store.categories).toEqual([]);
    });

    it("has null currentProduct", () => {
      expect(store.currentProduct).toBeNull();
    });

    it("has empty relatedProducts array", () => {
      expect(store.relatedProducts).toEqual([]);
    });

    it("has empty searchResults array", () => {
      expect(store.searchResults).toEqual([]);
    });

    it("has isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("has isSearching false", () => {
      expect(store.isSearching).toBe(false);
    });

    it("has null error", () => {
      expect(store.error).toBeNull();
    });

    it("has default pagination", () => {
      expect(store.pagination).toEqual({
        currentPage: 1,
        lastPage: 1,
        perPage: 12,
        total: 0,
      });
    });

    it("has default filters", () => {
      expect(store.filters).toEqual({
        category: null,
        search: "",
        minPrice: null,
        maxPrice: null,
        inStock: null,
        sort: "created_at",
        direction: "desc",
      });
    });
  });

  describe("Getters", () => {
    it("featuredProducts returns only featured products", () => {
      store.products = [
        { id: 1, is_featured: true },
        { id: 2, is_featured: false },
        { id: 3, is_featured: true },
      ];
      expect(store.featuredProducts).toHaveLength(2);
      expect(store.featuredProducts.every((p) => p.is_featured)).toBe(true);
    });

    it("activeCategories returns only active categories", () => {
      store.categories = [
        { id: 1, is_active: true },
        { id: 2, is_active: false },
        { id: 3, is_active: true },
      ];
      expect(store.activeCategories).toHaveLength(2);
      expect(store.activeCategories.every((c) => c.is_active)).toBe(true);
    });

    it("hasProducts returns true when products exist", () => {
      store.products = [{ id: 1 }];
      expect(store.hasProducts).toBe(true);
    });

    it("hasProducts returns false when no products", () => {
      store.products = [];
      expect(store.hasProducts).toBe(false);
    });

    it("hasMore returns true when more pages exist", () => {
      store.pagination.currentPage = 1;
      store.pagination.lastPage = 3;
      expect(store.hasMore).toBe(true);
    });

    it("hasMore returns false on last page", () => {
      store.pagination.currentPage = 3;
      store.pagination.lastPage = 3;
      expect(store.hasMore).toBe(false);
    });
  });

  describe("fetchProducts", () => {
    it("fetches products successfully", async () => {
      const mockProducts = [
        { id: 1, name: "Product 1" },
        { id: 2, name: "Product 2" },
      ];
      const mockResponse = {
        data: {
          data: mockProducts,
          meta: {
            current_page: 1,
            last_page: 1,
            per_page: 12,
            total: 2,
          },
        },
      };

      api.get.mockResolvedValueOnce(mockResponse);

      const result = await store.fetchProducts();

      expect(api.get).toHaveBeenCalledWith("/products", {
        params: expect.any(Object),
      });
      expect(store.products).toEqual(mockProducts);
      expect(store.isLoading).toBe(false);
    });

    it("sets loading state during fetch", async () => {
      api.get.mockImplementation(() => new Promise(() => {})); // Never resolves

      store.fetchProducts();
      expect(store.isLoading).toBe(true);
    });

    it("applies filters to API call", async () => {
      api.get.mockResolvedValueOnce({ data: { data: [] } });

      store.setFilters({
        category: "beef",
        minPrice: 10,
        maxPrice: 50,
        inStock: true,
        sort: "price",
        direction: "asc",
      });

      await store.fetchProducts();

      expect(api.get).toHaveBeenCalledWith("/products", {
        params: expect.objectContaining({
          category: "beef",
          min_price: 10,
          max_price: 50,
          in_stock: true,
          sort: "price",
          direction: "asc",
        }),
      });
    });

    it("updates pagination from response", async () => {
      const mockResponse = {
        data: {
          data: [],
          meta: {
            current_page: 2,
            last_page: 5,
            per_page: 12,
            total: 60,
          },
        },
      };

      api.get.mockResolvedValueOnce(mockResponse);

      await store.fetchProducts();

      expect(store.pagination).toEqual({
        currentPage: 2,
        lastPage: 5,
        perPage: 12,
        total: 60,
      });
    });

    it("handles fetch error", async () => {
      api.get.mockRejectedValueOnce(new Error("Network error"));

      await expect(store.fetchProducts()).rejects.toThrow("Network error");
      expect(store.error).toBe("Network error");
      expect(store.isLoading).toBe(false);
    });
  });

  describe("fetchFeaturedProducts", () => {
    it("fetches featured products with default limit", async () => {
      const mockProducts = [{ id: 1, is_featured: true }];
      api.get.mockResolvedValueOnce({ data: { data: mockProducts } });

      const result = await store.fetchFeaturedProducts();

      expect(api.get).toHaveBeenCalledWith("/products/featured", {
        params: { limit: 8 },
      });
      expect(result).toEqual(mockProducts);
    });

    it("respects custom limit parameter", async () => {
      api.get.mockResolvedValueOnce({ data: { data: [] } });

      await store.fetchFeaturedProducts(4);

      expect(api.get).toHaveBeenCalledWith("/products/featured", {
        params: { limit: 4 },
      });
    });
  });

  describe("fetchProduct", () => {
    it("fetches single product by slug", async () => {
      const mockProduct = { id: 1, name: "Test Product", slug: "test-product" };
      api.get.mockResolvedValueOnce({ data: { data: mockProduct } });

      const result = await store.fetchProduct("test-product");

      expect(api.get).toHaveBeenCalledWith("/products/test-product");
      expect(store.currentProduct).toEqual(mockProduct);
      expect(result).toEqual(mockProduct);
    });

    it("handles product not found", async () => {
      api.get.mockRejectedValueOnce(new Error("Product not found"));

      await expect(store.fetchProduct("non-existent")).rejects.toThrow();
      expect(store.error).toBeDefined();
    });
  });

  describe("fetchRelatedProducts", () => {
    it("fetches related products for a product", async () => {
      const mockRelated = [
        { id: 2, name: "Related 1" },
        { id: 3, name: "Related 2" },
      ];
      api.get.mockResolvedValueOnce({ data: { data: mockRelated } });

      const result = await store.fetchRelatedProducts("test-product", 4);

      expect(api.get).toHaveBeenCalledWith("/products/test-product/related", {
        params: { limit: 4 },
      });
      expect(store.relatedProducts).toEqual(mockRelated);
    });

    it("returns empty array on error", async () => {
      api.get.mockRejectedValueOnce(new Error("Error"));

      const result = await store.fetchRelatedProducts("test-product");

      expect(result).toEqual([]);
      expect(store.relatedProducts).toEqual([]);
    });
  });

  describe("quickSearch", () => {
    it("performs search for valid query", async () => {
      const mockResults = [{ id: 1, name: "Beef Steak" }];
      api.get.mockResolvedValueOnce({ data: { data: mockResults } });

      const result = await store.quickSearch("beef");

      expect(api.get).toHaveBeenCalledWith("/products/search", {
        params: { q: "beef" },
      });
      expect(store.searchResults).toEqual(mockResults);
    });

    it("returns empty for short query", async () => {
      const result = await store.quickSearch("a");

      expect(api.get).not.toHaveBeenCalled();
      expect(result).toEqual([]);
    });

    it("returns empty for empty query", async () => {
      const result = await store.quickSearch("");

      expect(api.get).not.toHaveBeenCalled();
      expect(result).toEqual([]);
    });

    it("sets isSearching during search", async () => {
      api.get.mockImplementation(() => new Promise(() => {}));

      store.quickSearch("beef");
      expect(store.isSearching).toBe(true);
    });
  });

  describe("fetchCategories", () => {
    it("fetches categories successfully", async () => {
      const mockCategories = [
        { id: 1, name: "Beef" },
        { id: 2, name: "Lamb" },
      ];
      api.get.mockResolvedValueOnce({ data: { data: mockCategories } });

      const result = await store.fetchCategories();

      expect(api.get).toHaveBeenCalledWith("/categories");
      expect(store.categories).toEqual(mockCategories);
    });
  });

  describe("fetchCategory", () => {
    it("fetches single category by slug", async () => {
      const mockCategory = { id: 1, name: "Beef", slug: "beef" };
      api.get.mockResolvedValueOnce({ data: { data: mockCategory } });

      const result = await store.fetchCategory("beef");

      expect(api.get).toHaveBeenCalledWith("/categories/beef");
      expect(store.currentCategory).toEqual(mockCategory);
    });
  });

  describe("fetchCategoryProducts", () => {
    it("fetches products for a category", async () => {
      const mockProducts = [{ id: 1, name: "Beef Steak" }];
      const mockResponse = {
        data: {
          data: mockProducts,
          meta: {
            current_page: 1,
            last_page: 1,
            per_page: 12,
            total: 1,
          },
        },
      };
      api.get.mockResolvedValueOnce(mockResponse);

      await store.fetchCategoryProducts("beef");

      expect(api.get).toHaveBeenCalledWith("/categories/beef/products", {
        params: expect.objectContaining({ page: 1, per_page: 12 }),
      });
      expect(store.products).toEqual(mockProducts);
    });

    it("applies options to category products fetch", async () => {
      api.get.mockResolvedValueOnce({ data: { data: [] } });

      await store.fetchCategoryProducts("beef", {
        page: 2,
        sort: "price",
        minPrice: 20,
      });

      expect(api.get).toHaveBeenCalledWith("/categories/beef/products", {
        params: expect.objectContaining({
          page: 2,
          sort: "price",
          min_price: 20,
        }),
      });
    });
  });

  describe("setFilters", () => {
    it("updates filters partially", () => {
      store.setFilters({ category: "beef" });

      expect(store.filters.category).toBe("beef");
      expect(store.filters.sort).toBe("created_at"); // Default preserved
    });

    it("resets pagination to page 1", () => {
      store.pagination.currentPage = 5;
      store.setFilters({ category: "lamb" });

      expect(store.pagination.currentPage).toBe(1);
    });
  });

  describe("resetFilters", () => {
    it("resets all filters to defaults", () => {
      store.setFilters({
        category: "beef",
        minPrice: 10,
        maxPrice: 100,
        inStock: true,
        sort: "price",
        direction: "asc",
      });

      store.resetFilters();

      expect(store.filters).toEqual({
        category: null,
        search: "",
        minPrice: null,
        maxPrice: null,
        inStock: null,
        sort: "created_at",
        direction: "desc",
      });
    });

    it("resets pagination to page 1", () => {
      store.pagination.currentPage = 5;
      store.resetFilters();

      expect(store.pagination.currentPage).toBe(1);
    });
  });

  describe("clearCurrentProduct", () => {
    it("clears current product and related products", () => {
      store.currentProduct = { id: 1 };
      store.relatedProducts = [{ id: 2 }];

      store.clearCurrentProduct();

      expect(store.currentProduct).toBeNull();
      expect(store.relatedProducts).toEqual([]);
    });
  });

  describe("clearSearch", () => {
    it("clears search results and search filter", () => {
      store.searchResults = [{ id: 1 }];
      store.filters.search = "beef";

      store.clearSearch();

      expect(store.searchResults).toEqual([]);
      expect(store.filters.search).toBe("");
    });
  });
});
