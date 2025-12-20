/**
 * Staff Stock Store Tests
 *
 * Comprehensive tests for the staffStock Pinia store.
 *
 * @requirement STAFF-010 Stock check functionality
 * @requirement STAFF-011 Stock updates
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useStaffStockStore } from "@/stores/staffStock";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  staffDashboard: {
    getStockCheck: vi.fn(),
    updateStock: vi.fn(),
  },
}));

import { staffDashboard } from "@/services/dashboard";

describe("Staff Stock Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useStaffStockStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty products array", () => {
      expect(store.products).toEqual([]);
    });

    it("starts with null selectedProduct", () => {
      expect(store.selectedProduct).toBeNull();
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with isUpdating false", () => {
      expect(store.isUpdating).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });

    it("starts with default filters", () => {
      expect(store.filters.search).toBe("");
      expect(store.filters.stockStatus).toBe("");
      expect(store.filters.category).toBe("");
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

    it("lowStockProducts filters correctly", () => {
      store.products = [
        { id: 1, stock_quantity: 5 },
        { id: 2, stock_quantity: 50 },
        { id: 3, stock_quantity: 3 },
      ];
      expect(store.lowStockProducts).toHaveLength(2);
    });

    it("outOfStockProducts filters correctly", () => {
      store.products = [
        { id: 1, stock_quantity: 0 },
        { id: 2, stock_quantity: 50 },
      ];
      expect(store.outOfStockProducts).toHaveLength(1);
    });

    it("normalStockProducts filters correctly", () => {
      store.products = [
        { id: 1, stock_quantity: 50 },
        { id: 2, stock_quantity: 5 },
        { id: 3, stock_quantity: 15 },
      ];
      expect(store.normalStockProducts).toHaveLength(2);
    });

    it("stockStats calculates all counts", () => {
      store.products = [
        { id: 1, stock_quantity: 50 },
        { id: 2, stock_quantity: 5 },
        { id: 3, stock_quantity: 0 },
        { id: 4, stock_quantity: 20 },
      ];
      const stats = store.stockStats;
      expect(stats.total).toBe(4);
      expect(stats.normal).toBe(2);
      expect(stats.lowStock).toBe(1);
      expect(stats.outOfStock).toBe(1);
    });

    it("filteredProducts filters by search", () => {
      store.products = [
        { id: 1, name: "Beef Steak", sku: "BEF-001" },
        { id: 2, name: "Pork Chops", sku: "POR-001" },
        { id: 3, name: "Beef Mince", sku: "BEF-002" },
      ];
      store.filters.search = "beef";
      expect(store.filteredProducts).toHaveLength(2);
    });

    it("filteredProducts filters by status", () => {
      store.products = [
        { id: 1, name: "Product A", stock_quantity: 5 },
        { id: 2, name: "Product B", stock_quantity: 50 },
      ];
      store.filters.stockStatus = "low";
      expect(store.filteredProducts).toHaveLength(1);
    });

    it("filteredProducts filters by category", () => {
      store.products = [
        { id: 1, name: "Product A", category_id: 1 },
        { id: 2, name: "Product B", category_id: 2 },
      ];
      store.filters.category = 1;
      expect(store.filteredProducts).toHaveLength(1);
    });

    it("filteredProducts applies multiple filters", () => {
      store.products = [
        { id: 1, name: "Beef Steak", category_id: 1, stock_quantity: 50 },
        { id: 2, name: "Pork Chops", category_id: 2, stock_quantity: 50 },
        { id: 3, name: "Beef Mince", category_id: 1, stock_quantity: 5 },
      ];
      store.filters.search = "beef";
      expect(store.filteredProducts).toHaveLength(2);
    });
  });

  describe("fetchStockCheck", () => {
    it("sets isLoading during fetch", async () => {
      staffDashboard.getStockCheck.mockResolvedValue({
        success: true,
        data: [],
      });

      const promise = store.fetchStockCheck();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores products from response data", async () => {
      const mockProducts = [
        { id: 1, name: "Beef Steak", stock_quantity: 50 },
        { id: 2, name: "Pork Chops", stock_quantity: 20 },
      ];
      staffDashboard.getStockCheck.mockResolvedValue({
        success: true,
        data: mockProducts,
      });

      await store.fetchStockCheck();
      expect(store.products).toEqual(mockProducts);
    });

    it("stores products from response.products", async () => {
      const mockProducts = [{ id: 1, name: "Beef Steak" }];
      staffDashboard.getStockCheck.mockResolvedValue({
        success: true,
        products: mockProducts,
      });

      await store.fetchStockCheck();
      expect(store.products).toEqual(mockProducts);
    });

    it("sets error on failure", async () => {
      staffDashboard.getStockCheck.mockRejectedValue(
        new Error("Network error")
      );

      await expect(store.fetchStockCheck()).rejects.toThrow();
      expect(store.error).toBe("Network error");
    });
  });

  describe("updateStock", () => {
    beforeEach(() => {
      store.products = [
        {
          id: 1,
          name: "Beef Steak",
          stock_quantity: 50,
          stock_status: "in_stock",
        },
        {
          id: 2,
          name: "Pork Chops",
          stock_quantity: 20,
          stock_status: "low_stock",
        },
      ];
    });

    it("sets isUpdating during update", async () => {
      staffDashboard.updateStock.mockResolvedValue({ success: true });

      const promise = store.updateStock(1, 75);
      expect(store.isUpdating).toBe(true);
      await promise;
      expect(store.isUpdating).toBe(false);
    });

    it("updates stock via API", async () => {
      staffDashboard.updateStock.mockResolvedValue({ success: true });

      await store.updateStock(1, 75);
      expect(staffDashboard.updateStock).toHaveBeenCalledWith(1, 75, null);
    });

    it("passes notes when provided", async () => {
      staffDashboard.updateStock.mockResolvedValue({ success: true });

      await store.updateStock(1, 75, "Stock count after delivery");
      expect(staffDashboard.updateStock).toHaveBeenCalledWith(
        1,
        75,
        "Stock count after delivery"
      );
    });

    it("updates local product on success", async () => {
      staffDashboard.updateStock.mockResolvedValue({ success: true });

      await store.updateStock(1, 75);
      expect(store.products[0].stock_quantity).toBe(75);
    });

    it("sets error on failure", async () => {
      staffDashboard.updateStock.mockRejectedValue(new Error("Update failed"));

      await expect(store.updateStock(1, 75)).rejects.toThrow();
      expect(store.error).toBe("Update failed");
    });
  });

  describe("Filter Actions", () => {
    it("setFilters updates filters", () => {
      store.setFilters({ search: "beef", stockStatus: "low" });

      expect(store.filters.search).toBe("beef");
      expect(store.filters.stockStatus).toBe("low");
    });

    it("setFilters preserves existing filters", () => {
      store.filters.search = "pork";
      store.setFilters({ stockStatus: "normal" });

      expect(store.filters.search).toBe("pork");
      expect(store.filters.stockStatus).toBe("normal");
    });

    it("setPage updates currentPage", () => {
      store.setPage(3);
      expect(store.pagination.currentPage).toBe(3);
    });
  });

  describe("Helper Actions", () => {
    it("selectProduct sets selectedProduct", () => {
      const product = { id: 1, name: "Beef Steak" };
      store.selectProduct(product);
      expect(store.selectedProduct).toEqual(product);
    });

    it("clearSelectedProduct clears selectedProduct", () => {
      store.selectedProduct = { id: 1 };
      store.clearSelectedProduct();
      expect(store.selectedProduct).toBeNull();
    });

    it("clearError clears error", () => {
      store.error = "Some error";
      store.clearError();
      expect(store.error).toBeNull();
    });
  });

  describe("$reset", () => {
    it("resets all state to initial values", () => {
      store.products = [{ id: 1 }];
      store.selectedProduct = { id: 1 };
      store.isLoading = true;
      store.isUpdating = true;
      store.error = "Error";
      store.filters = { search: "test", stockStatus: "low", category: 1 };

      store.$reset();

      expect(store.products).toEqual([]);
      expect(store.selectedProduct).toBeNull();
      expect(store.isLoading).toBe(false);
      expect(store.isUpdating).toBe(false);
      expect(store.error).toBeNull();
      expect(store.filters.search).toBe("");
      expect(store.filters.stockStatus).toBe("");
      expect(store.filters.category).toBe("");
    });
  });
});
