/**
 * Admin Inventory Store Tests
 *
 * @requirement INV-018 Backend and frontend tests pass
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAdminInventoryStore } from "../adminInventory";

// Mock the dashboard service
vi.mock("@/services/dashboard", () => ({
  adminDashboard: {
    getInventoryDashboard: vi.fn(),
    getInventory: vi.fn(),
    getProductInventory: vi.fn(),
    receiveStock: vi.fn(),
    adjustStock: vi.fn(),
    updateMinStock: vi.fn(),
    getInventoryLogs: vi.fn(),
    getInventoryAlerts: vi.fn(),
    getWasteEntries: vi.fn(),
    approveWaste: vi.fn(),
    getInventoryReport: vi.fn(),
    exportInventory: vi.fn(),
  },
}));

import { adminDashboard } from "@/services/dashboard";

describe("useAdminInventoryStore", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    store = useAdminInventoryStore();
    vi.clearAllMocks();
  });

  afterEach(() => {
    store.$reset();
  });

  describe("Initial State", () => {
    it("has correct initial state", () => {
      expect(store.dashboard.total_products).toBe(0);
      expect(store.dashboard.low_stock_count).toBe(0);
      expect(store.dashboard.out_of_stock_count).toBe(0);
      expect(store.inventory).toEqual([]);
      expect(store.isLoading).toBe(false);
      expect(store.error).toBeNull();
    });

    it("has correct pagination defaults", () => {
      expect(store.pagination.currentPage).toBe(1);
      expect(store.pagination.perPage).toBe(20);
      expect(store.pagination.total).toBe(0);
    });

    it("has correct filter defaults", () => {
      expect(store.filters.category_id).toBeNull();
      expect(store.filters.status).toBeNull();
      expect(store.filters.search).toBe("");
    });
  });

  describe("Getters", () => {
    it("hasInventory returns false when empty", () => {
      expect(store.hasInventory).toBe(false);
    });

    it("hasInventory returns true when inventory exists", () => {
      store.inventory = [{ id: 1, name: "Product 1" }];
      expect(store.hasInventory).toBe(true);
    });

    it("hasAlerts returns true when alerts exist", () => {
      store.alertsSummary.total_alerts = 5;
      expect(store.hasAlerts).toBe(true);
    });

    it("lowStockItems filters correctly", () => {
      store.inventory = [
        { id: 1, stock: 5, meta: { min_stock: 10 } },
        { id: 2, stock: 15, meta: { min_stock: 10 } },
        { id: 3, stock: 0, meta: { min_stock: 10 } },
      ];
      expect(store.lowStockItems).toHaveLength(1);
      expect(store.lowStockItems[0].id).toBe(1);
    });

    it("outOfStockItems filters correctly", () => {
      store.inventory = [
        { id: 1, stock: 5 },
        { id: 2, stock: 0 },
        { id: 3, stock: -1 },
      ];
      expect(store.outOfStockItems).toHaveLength(2);
    });

    it("pendingWasteCount calculates correctly", () => {
      store.wasteEntries = [
        { id: 1, approved: true },
        { id: 2, approved: false },
        { id: 3, approved: false },
      ];
      expect(store.pendingWasteCount).toBe(2);
    });
  });

  describe("fetchDashboard", () => {
    it("fetches dashboard data successfully", async () => {
      const mockData = {
        success: true,
        data: {
          total_products: 50,
          low_stock_count: 5,
          out_of_stock_count: 2,
          waste_this_month: { quantity: 10, value: 150 },
          recent_movements: [],
        },
      };
      adminDashboard.getInventoryDashboard.mockResolvedValue(mockData);

      await store.fetchDashboard();

      expect(store.dashboard.total_products).toBe(50);
      expect(store.dashboard.low_stock_count).toBe(5);
      expect(store.dashboard.out_of_stock_count).toBe(2);
      expect(store.isDashboardLoading).toBe(false);
    });

    it("handles dashboard fetch error", async () => {
      adminDashboard.getInventoryDashboard.mockRejectedValue(
        new Error("API Error")
      );

      await expect(store.fetchDashboard()).rejects.toThrow("API Error");
      expect(store.error).toBe("API Error");
    });
  });

  describe("fetchInventory", () => {
    it("fetches inventory list successfully", async () => {
      const mockData = {
        success: true,
        data: [
          { id: 1, name: "Beef Steak", stock: 100 },
          { id: 2, name: "Pork Chops", stock: 50 },
        ],
        meta: {
          current_page: 1,
          last_page: 1,
          per_page: 20,
          total: 2,
        },
      };
      adminDashboard.getInventory.mockResolvedValue(mockData);

      await store.fetchInventory();

      expect(store.inventory).toHaveLength(2);
      expect(store.pagination.total).toBe(2);
      expect(store.isLoading).toBe(false);
    });

    it("applies filters when fetching", async () => {
      adminDashboard.getInventory.mockResolvedValue({
        success: true,
        data: [],
        meta: {},
      });

      store.setFilters({ category_id: 1, status: "low", search: "beef" });
      await store.fetchInventory();

      expect(adminDashboard.getInventory).toHaveBeenCalledWith(
        expect.objectContaining({
          category_id: 1,
          status: "low",
          search: "beef",
        })
      );
    });

    it("handles pagination", async () => {
      adminDashboard.getInventory.mockResolvedValue({
        success: true,
        data: [],
        meta: { current_page: 2 },
      });

      await store.fetchInventory(2);

      expect(adminDashboard.getInventory).toHaveBeenCalledWith(
        expect.objectContaining({ page: 2 })
      );
    });
  });

  describe("fetchProductInventory", () => {
    it("fetches product detail successfully", async () => {
      const mockData = {
        success: true,
        data: {
          product: { id: 1, name: "Beef Steak", stock: 100 },
          history: [],
          waste_logs: [],
        },
      };
      adminDashboard.getProductInventory.mockResolvedValue(mockData);

      await store.fetchProductInventory(1);

      expect(store.currentProduct).toEqual(mockData.data);
    });
  });

  describe("receiveStock", () => {
    it("receives stock successfully", async () => {
      const mockData = {
        success: true,
        data: { product_id: 1, stock_after: 110 },
      };
      adminDashboard.receiveStock.mockResolvedValue(mockData);
      adminDashboard.getInventoryDashboard.mockResolvedValue({
        success: true,
        data: {},
      });

      store.inventory = [{ id: 1, stock: 100 }];

      await store.receiveStock({ product_id: 1, quantity: 10 });

      expect(store.inventory[0].stock).toBe(110);
    });

    it("handles receive stock error", async () => {
      adminDashboard.receiveStock.mockRejectedValue(
        new Error("Receive failed")
      );

      await expect(
        store.receiveStock({ product_id: 1, quantity: 10 })
      ).rejects.toThrow();
      expect(store.error).toBe("Receive failed");
    });
  });

  describe("adjustStock", () => {
    it("adjusts stock successfully", async () => {
      const mockData = {
        success: true,
        data: { product_id: 1, stock_after: 75 },
      };
      adminDashboard.adjustStock.mockResolvedValue(mockData);
      adminDashboard.getInventoryDashboard.mockResolvedValue({
        success: true,
        data: {},
      });

      store.inventory = [{ id: 1, stock: 100 }];

      await store.adjustStock(1, 75, "Physical count correction");

      expect(store.inventory[0].stock).toBe(75);
    });
  });

  describe("updateMinStock", () => {
    it("updates min stock threshold successfully", async () => {
      const mockData = {
        success: true,
        data: { product_id: 1, min_stock: 20 },
      };
      adminDashboard.updateMinStock.mockResolvedValue(mockData);
      adminDashboard.getInventoryAlerts.mockResolvedValue({
        success: true,
        data: {},
        summary: {},
      });

      store.inventory = [{ id: 1, meta: { min_stock: 10 } }];

      await store.updateMinStock(1, 20);

      expect(store.inventory[0].meta.min_stock).toBe(20);
    });
  });

  describe("fetchHistory", () => {
    it("fetches inventory history successfully", async () => {
      const mockData = {
        success: true,
        data: [
          { id: 1, type: "addition", quantity: 50 },
          { id: 2, type: "deduction", quantity: 10 },
        ],
        meta: { current_page: 1, last_page: 1, per_page: 20, total: 2 },
      };
      adminDashboard.getInventoryLogs.mockResolvedValue(mockData);

      await store.fetchHistory();

      expect(store.history).toHaveLength(2);
      expect(store.historyPagination.total).toBe(2);
    });

    it("applies history filters", async () => {
      adminDashboard.getInventoryLogs.mockResolvedValue({
        success: true,
        data: [],
        meta: {},
      });

      store.setHistoryFilters({ type: "addition", product_id: 1 });
      await store.fetchHistory();

      expect(adminDashboard.getInventoryLogs).toHaveBeenCalledWith(
        expect.objectContaining({
          type: "addition",
          product_id: 1,
        })
      );
    });
  });

  describe("fetchAlerts", () => {
    it("fetches alerts successfully", async () => {
      const mockData = {
        success: true,
        data: {
          low_stock: [{ id: 1, name: "Product 1" }],
          out_of_stock: [{ id: 2, name: "Product 2" }],
        },
        summary: {
          low_stock_count: 1,
          out_of_stock_count: 1,
          total_alerts: 2,
        },
      };
      adminDashboard.getInventoryAlerts.mockResolvedValue(mockData);

      await store.fetchAlerts();

      expect(store.alerts.low_stock).toHaveLength(1);
      expect(store.alerts.out_of_stock).toHaveLength(1);
      expect(store.alertsSummary.total_alerts).toBe(2);
    });
  });

  describe("fetchWaste", () => {
    it("fetches waste entries successfully", async () => {
      const mockData = {
        success: true,
        data: [
          { id: 1, reason: "expired", quantity: 5 },
          { id: 2, reason: "damaged", quantity: 3 },
        ],
        meta: { current_page: 1, last_page: 1, per_page: 20, total: 2 },
        summary: { total_quantity: 8, total_value: 120, total_entries: 2 },
      };
      adminDashboard.getWasteEntries.mockResolvedValue(mockData);

      await store.fetchWaste();

      expect(store.wasteEntries).toHaveLength(2);
      expect(store.wasteSummary.total_quantity).toBe(8);
    });

    it("applies waste filters", async () => {
      adminDashboard.getWasteEntries.mockResolvedValue({
        success: true,
        data: [],
        meta: {},
        summary: {},
      });

      store.setWasteFilters({ reason: "expired", approved: false });
      await store.fetchWaste();

      expect(adminDashboard.getWasteEntries).toHaveBeenCalledWith(
        expect.objectContaining({
          reason: "expired",
          approved: false,
        })
      );
    });
  });

  describe("approveWaste", () => {
    it("approves waste entry successfully", async () => {
      const mockData = { success: true, data: { id: 1, approved: true } };
      adminDashboard.approveWaste.mockResolvedValue(mockData);
      adminDashboard.getInventoryDashboard.mockResolvedValue({
        success: true,
        data: {},
      });

      store.wasteEntries = [{ id: 1, approved: false }];

      await store.approveWaste(1, true);

      expect(store.wasteEntries[0].approved).toBe(true);
    });

    it("rejects waste entry with notes", async () => {
      const mockData = { success: true, data: { id: 1, approved: false } };
      adminDashboard.approveWaste.mockResolvedValue(mockData);
      adminDashboard.getInventoryDashboard.mockResolvedValue({
        success: true,
        data: {},
      });

      await store.approveWaste(1, false, "Invalid entry");

      expect(adminDashboard.approveWaste).toHaveBeenCalledWith(
        1,
        false,
        "Invalid entry"
      );
    });
  });

  describe("fetchReport", () => {
    it("fetches inventory report successfully", async () => {
      const mockData = {
        success: true,
        data: {
          period: { start_date: "2025-12-01", end_date: "2025-12-31" },
          current_stock: {},
          movements: {},
          waste: {},
        },
      };
      adminDashboard.getInventoryReport.mockResolvedValue(mockData);

      await store.fetchReport({
        start_date: "2025-12-01",
        end_date: "2025-12-31",
      });

      expect(store.report).toEqual(mockData.data);
    });
  });

  describe("exportInventory", () => {
    it("exports inventory data successfully", async () => {
      const mockData = {
        success: true,
        data: { summary: {}, products: [] },
      };
      adminDashboard.exportInventory.mockResolvedValue(mockData);

      const result = await store.exportInventory();

      expect(result).toEqual(mockData);
    });
  });

  describe("Filter Actions", () => {
    it("setFilters updates filters correctly", () => {
      store.setFilters({ category_id: 5, status: "low" });

      expect(store.filters.category_id).toBe(5);
      expect(store.filters.status).toBe("low");
    });

    it("resetFilters clears all filters", () => {
      store.setFilters({ category_id: 5, status: "low", search: "test" });
      store.resetFilters();

      expect(store.filters.category_id).toBeNull();
      expect(store.filters.status).toBeNull();
      expect(store.filters.search).toBe("");
    });

    it("setHistoryFilters updates history filters", () => {
      store.setHistoryFilters({ type: "addition", product_id: 1 });

      expect(store.historyFilters.type).toBe("addition");
      expect(store.historyFilters.product_id).toBe(1);
    });

    it("resetHistoryFilters clears history filters", () => {
      store.setHistoryFilters({ type: "addition" });
      store.resetHistoryFilters();

      expect(store.historyFilters.type).toBeNull();
    });

    it("setWasteFilters updates waste filters", () => {
      store.setWasteFilters({ reason: "expired" });

      expect(store.wasteFilters.reason).toBe("expired");
    });

    it("resetWasteFilters clears waste filters", () => {
      store.setWasteFilters({ reason: "expired" });
      store.resetWasteFilters();

      expect(store.wasteFilters.reason).toBeNull();
    });
  });

  describe("Utility Actions", () => {
    it("clearError clears error state", () => {
      store.error = "Some error";
      store.clearError();

      expect(store.error).toBeNull();
    });

    it("$reset resets entire store state", () => {
      store.inventory = [{ id: 1 }];
      store.dashboard.total_products = 50;
      store.error = "Some error";
      store.setFilters({ category_id: 5 });

      store.$reset();

      expect(store.inventory).toEqual([]);
      expect(store.dashboard.total_products).toBe(0);
      expect(store.error).toBeNull();
      expect(store.filters.category_id).toBeNull();
    });
  });
});
