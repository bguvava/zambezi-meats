/**
 * Admin Products Store Tests
 *
 * Comprehensive tests for the adminProducts Pinia store.
 *
 * @requirement ADMIN-011 Create products management page
 * @requirement ADMIN-012 Create add/edit product form
 * @requirement ADMIN-013 Implement product image upload
 * @requirement ADMIN-015 Delete products (single)
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAdminProductsStore } from "@/stores/adminProducts";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  adminDashboard: {
    getProducts: vi.fn(),
    getProduct: vi.fn(),
    createProduct: vi.fn(),
    updateProduct: vi.fn(),
    deleteProduct: vi.fn(),
    getLowStockProducts: vi.fn(),
    adjustStock: vi.fn(),
    exportProducts: vi.fn(),
  },
}));

import { adminDashboard } from "@/services/dashboard";

describe("Admin Products Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useAdminProductsStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty products array", () => {
      expect(store.products).toEqual([]);
    });

    it("starts with null currentProduct", () => {
      expect(store.currentProduct).toBeNull();
    });

    it("starts with empty lowStockProducts", () => {
      expect(store.lowStockProducts).toEqual([]);
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });

    it("starts with default pagination", () => {
      expect(store.pagination.currentPage).toBe(1);
      expect(store.pagination.perPage).toBe(15);
    });

    it("starts with default filters", () => {
      expect(store.filters.categoryId).toBeNull();
      expect(store.filters.status).toBe("all");
      expect(store.filters.stockStatus).toBeNull();
      expect(store.filters.search).toBe("");
    });
  });

  describe("Computed Properties", () => {
    it("hasProducts returns false when empty", () => {
      expect(store.hasProducts).toBe(false);
    });

    it("hasProducts returns true when products exist", () => {
      store.products = [{ id: 1 }];
      expect(store.hasProducts).toBe(true);
    });

    it("activeProductsCount counts active products", () => {
      store.products = [
        { id: 1, is_active: true },
        { id: 2, is_active: false },
        { id: 3, is_active: true },
      ];
      expect(store.activeProductsCount).toBe(2);
    });

    it("outOfStockCount counts products with no stock", () => {
      store.products = [
        { id: 1, stock: 0 },
        { id: 2, stock: 10 },
        { id: 3, stock: -1 },
      ];
      expect(store.outOfStockCount).toBe(2);
    });

    it("lowStockCount counts products between 1-10 stock", () => {
      store.products = [
        { id: 1, stock: 5 },
        { id: 2, stock: 15 },
        { id: 3, stock: 10 },
        { id: 4, stock: 0 },
      ];
      expect(store.lowStockCount).toBe(2);
    });
  });

  describe("fetchProducts", () => {
    it("sets isLoading during fetch", async () => {
      adminDashboard.getProducts.mockResolvedValue({
        success: true,
        products: [],
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      });

      const promise = store.fetchProducts();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores products from response", async () => {
      const mockProducts = [
        { id: 1, name: "Product A" },
        { id: 2, name: "Product B" },
      ];
      adminDashboard.getProducts.mockResolvedValue({
        success: true,
        products: mockProducts,
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
      });

      await store.fetchProducts();
      expect(store.products).toEqual(mockProducts);
    });

    it("applies category filter", async () => {
      adminDashboard.getProducts.mockResolvedValue({
        success: true,
        products: [],
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      });

      store.setFilters({ categoryId: 5 });
      await store.fetchProducts();

      expect(adminDashboard.getProducts).toHaveBeenCalledWith(
        expect.objectContaining({ category_id: 5 })
      );
    });

    it("handles API errors", async () => {
      adminDashboard.getProducts.mockRejectedValue(new Error("Network error"));

      await store.fetchProducts();
      expect(store.error).toBe("Network error");
    });
  });

  describe("fetchProduct", () => {
    it("fetches single product details", async () => {
      const mockProduct = { id: 1, name: "Test Product", images: [] };
      adminDashboard.getProduct.mockResolvedValue({
        success: true,
        product: mockProduct,
      });

      await store.fetchProduct(1);
      expect(store.currentProduct).toEqual(mockProduct);
    });
  });

  describe("createProduct", () => {
    it("creates product and adds to list", async () => {
      const newProduct = { id: 1, name: "New Product" };
      adminDashboard.createProduct.mockResolvedValue({
        success: true,
        product: newProduct,
      });

      const formData = new FormData();
      formData.append("name", "New Product");

      await store.createProduct(formData);
      expect(store.products[0]).toEqual(newProduct);
      expect(store.pagination.total).toBe(1);
    });
  });

  describe("updateProduct", () => {
    it("updates product in list", async () => {
      store.products = [{ id: 1, name: "Old Name" }];
      const updatedProduct = { id: 1, name: "New Name" };

      adminDashboard.updateProduct.mockResolvedValue({
        success: true,
        product: updatedProduct,
      });

      const formData = new FormData();
      await store.updateProduct(1, formData);

      expect(store.products[0].name).toBe("New Name");
      expect(store.currentProduct.name).toBe("New Name");
    });
  });

  describe("deleteProduct", () => {
    it("soft deletes product by marking inactive", async () => {
      store.products = [{ id: 1, is_active: true }];

      adminDashboard.deleteProduct.mockResolvedValue({
        success: true,
      });

      await store.deleteProduct(1);
      expect(store.products[0].is_active).toBe(false);
    });
  });

  describe("fetchLowStockProducts", () => {
    it("fetches low stock products", async () => {
      const mockProducts = [{ id: 1, name: "Low Stock", stock: 3 }];
      adminDashboard.getLowStockProducts.mockResolvedValue({
        success: true,
        products: mockProducts,
      });

      await store.fetchLowStockProducts(10);
      expect(store.lowStockProducts).toEqual(mockProducts);
    });
  });

  describe("adjustStock", () => {
    it("adjusts product stock", async () => {
      store.products = [{ id: 1, stock: 10 }];
      const updatedProduct = { id: 1, stock: 20 };

      adminDashboard.adjustStock.mockResolvedValue({
        success: true,
        product: updatedProduct,
      });

      await store.adjustStock(1, 10, "increase", "Restocking");
      expect(store.products[0].stock).toBe(20);
    });
  });

  describe("exportProducts", () => {
    it("exports products to PDF", async () => {
      const mockBlob = new Blob(["PDF content"]);
      adminDashboard.exportProducts.mockResolvedValue(mockBlob);

      const result = await store.exportProducts({ category_id: 1 });
      expect(result).toEqual(mockBlob);
    });
  });

  describe("Filter Management", () => {
    it("setFilters updates filter values", () => {
      store.setFilters({ categoryId: 5, status: "active" });
      expect(store.filters.categoryId).toBe(5);
      expect(store.filters.status).toBe("active");
    });

    it("resetFilters restores defaults", () => {
      store.setFilters({ categoryId: 5 });
      store.resetFilters();
      expect(store.filters.categoryId).toBeNull();
    });
  });

  describe("$reset", () => {
    it("resets all state to initial values", () => {
      store.products = [{ id: 1 }];
      store.currentProduct = { id: 1 };
      store.error = "Error";

      store.$reset();

      expect(store.products).toEqual([]);
      expect(store.currentProduct).toBeNull();
      expect(store.error).toBeNull();
    });
  });
});
