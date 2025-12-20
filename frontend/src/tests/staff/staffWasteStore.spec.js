/**
 * Staff Waste Store Tests
 *
 * Comprehensive tests for the staffWaste Pinia store.
 *
 * @requirement STAFF-012 Waste logging functionality
 * @requirement STAFF-013 Waste tracking
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useStaffWasteStore } from "@/stores/staffWaste";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  staffDashboard: {
    getWasteLogs: vi.fn(),
    logWaste: vi.fn(),
  },
}));

import { staffDashboard } from "@/services/dashboard";

describe("Staff Waste Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useStaffWasteStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty wasteLogs array", () => {
      expect(store.wasteLogs).toEqual([]);
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with isSubmitting false", () => {
      expect(store.isSubmitting).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });

    it("starts with default filters", () => {
      expect(store.filters.search).toBe("");
      expect(store.filters.reason).toBe("");
      expect(store.filters.dateFrom).toBe("");
    });

    it("starts with default pagination", () => {
      expect(store.pagination.currentPage).toBe(1);
      expect(store.pagination.perPage).toBe(15);
    });

    it("has wasteReasons defined", () => {
      expect(store.wasteReasons).toBeDefined();
      expect(Array.isArray(store.wasteReasons)).toBe(true);
      expect(store.wasteReasons.length).toBeGreaterThan(0);
    });
  });

  describe("Waste Reasons", () => {
    it("includes common waste reasons", () => {
      const reasonValues = store.wasteReasons.map((r) => r.value);
      expect(reasonValues).toContain("expired");
      expect(reasonValues).toContain("damaged");
      expect(reasonValues).toContain("quality");
    });

    it("each reason has value and label", () => {
      store.wasteReasons.forEach((reason) => {
        expect(reason.value).toBeDefined();
        expect(reason.label).toBeDefined();
      });
    });
  });

  describe("Computed Properties", () => {
    it("hasWasteLogs returns false when empty", () => {
      expect(store.hasWasteLogs).toBe(false);
    });

    it("hasWasteLogs returns true when logs exist", () => {
      store.wasteLogs = [{ id: 1 }];
      expect(store.hasWasteLogs).toBe(true);
    });

    it("wasteStats calculates total entries", () => {
      store.wasteLogs = [
        { id: 1, quantity: 5 },
        { id: 2, quantity: 10 },
      ];
      expect(store.wasteStats.total).toBe(2);
    });

    it("wasteStats calculates today count", () => {
      const today = new Date().toISOString().split("T")[0];
      store.wasteLogs = [
        { id: 1, quantity: 5, created_at: `${today}T10:00:00Z` },
        { id: 2, quantity: 10, created_at: "2024-01-01T10:00:00Z" },
      ];
      expect(store.wasteStats.todayCount).toBe(1);
    });

    it("wasteStats calculates total value", () => {
      store.wasteLogs = [
        { id: 1, value: 50.0 },
        { id: 2, value: 25.5 },
      ];
      expect(store.wasteStats.totalValue).toBe(75.5);
    });

    it("wasteByReason groups correctly", () => {
      store.wasteLogs = [
        { id: 1, reason: "expired", quantity: 5 },
        { id: 2, reason: "expired", quantity: 3 },
        { id: 3, reason: "damaged", quantity: 2 },
      ];
      const byReason = store.wasteByReason;
      expect(byReason.expired.quantity).toBe(8);
      expect(byReason.damaged.quantity).toBe(2);
    });

    it("todaysWaste filters correctly", () => {
      const today = new Date().toISOString().split("T")[0];
      const yesterday = new Date(Date.now() - 86400000)
        .toISOString()
        .split("T")[0];

      store.wasteLogs = [
        { id: 1, created_at: `${today}T10:00:00Z` },
        { id: 2, created_at: `${yesterday}T10:00:00Z` },
        { id: 3, created_at: `${today}T14:00:00Z` },
      ];
      expect(store.todaysWaste).toHaveLength(2);
    });
  });

  describe("fetchWasteLogs", () => {
    it("sets isLoading during fetch", async () => {
      staffDashboard.getWasteLogs.mockResolvedValue({
        success: true,
        data: [],
      });

      const promise = store.fetchWasteLogs();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores wasteLogs from response data", async () => {
      const mockLogs = [
        { id: 1, product_name: "Beef Steak", quantity: 5 },
        { id: 2, product_name: "Pork Chops", quantity: 3 },
      ];
      staffDashboard.getWasteLogs.mockResolvedValue({
        success: true,
        data: mockLogs,
      });

      await store.fetchWasteLogs();
      expect(store.wasteLogs).toEqual(mockLogs);
    });

    it("stores wasteLogs from response.waste_logs", async () => {
      const mockLogs = [{ id: 1, product_name: "Beef Steak" }];
      staffDashboard.getWasteLogs.mockResolvedValue({
        success: true,
        waste_logs: mockLogs,
      });

      await store.fetchWasteLogs();
      expect(store.wasteLogs).toEqual(mockLogs);
    });

    it("updates pagination from meta", async () => {
      staffDashboard.getWasteLogs.mockResolvedValue({
        success: true,
        data: [],
        meta: {
          current_page: 2,
          last_page: 5,
          total: 100,
        },
      });

      await store.fetchWasteLogs();
      expect(store.pagination.currentPage).toBe(2);
      expect(store.pagination.totalPages).toBe(5);
      expect(store.pagination.total).toBe(100);
    });

    it("includes filters in request", async () => {
      staffDashboard.getWasteLogs.mockResolvedValue({
        success: true,
        data: [],
      });

      store.filters.reason = "expired";
      store.filters.search = "beef";
      await store.fetchWasteLogs();

      expect(staffDashboard.getWasteLogs).toHaveBeenCalledWith(
        expect.objectContaining({
          reason: "expired",
          search: "beef",
        })
      );
    });

    it("sets error on failure", async () => {
      staffDashboard.getWasteLogs.mockRejectedValue(new Error("Network error"));

      await expect(store.fetchWasteLogs()).rejects.toThrow();
      expect(store.error).toBe("Network error");
    });
  });

  describe("logWaste", () => {
    it("sets isSubmitting during submission", async () => {
      staffDashboard.logWaste.mockResolvedValue({ success: true });
      staffDashboard.getWasteLogs.mockResolvedValue({
        success: true,
        data: [],
      });

      const wasteData = {
        product_id: 1,
        quantity: 5,
        reason: "expired",
      };

      const promise = store.logWaste(wasteData);
      expect(store.isSubmitting).toBe(true);
      await promise;
      expect(store.isSubmitting).toBe(false);
    });

    it("submits waste data via API", async () => {
      staffDashboard.logWaste.mockResolvedValue({ success: true });
      staffDashboard.getWasteLogs.mockResolvedValue({
        success: true,
        data: [],
      });

      const wasteData = {
        product_id: 1,
        quantity: 5,
        reason: "expired",
        notes: "Found expired in storage",
      };

      await store.logWaste(wasteData);
      expect(staffDashboard.logWaste).toHaveBeenCalledWith(wasteData);
    });

    it("refreshes waste logs after submission", async () => {
      staffDashboard.logWaste.mockResolvedValue({
        success: true,
        data: { id: 1, product_name: "Test" },
      });

      await store.logWaste({ product_id: 1, quantity: 5, reason: "expired" });
      // Store adds log to array directly rather than refetching
      expect(store.wasteLogs).toHaveLength(1);
    });

    it("sets error on failure", async () => {
      staffDashboard.logWaste.mockRejectedValue(new Error("Submission failed"));

      await expect(
        store.logWaste({ product_id: 1, quantity: 5, reason: "expired" })
      ).rejects.toThrow();
      expect(store.error).toBe("Submission failed");
    });
  });

  describe("Filter and Pagination Actions", () => {
    it("setFilters updates filters and resets page", () => {
      store.pagination.currentPage = 3;
      store.setFilters({ reason: "expired", search: "beef" });

      expect(store.filters.reason).toBe("expired");
      expect(store.filters.search).toBe("beef");
      expect(store.pagination.currentPage).toBe(1);
    });

    it("setPage updates currentPage", () => {
      store.setPage(5);
      expect(store.pagination.currentPage).toBe(5);
    });
  });

  describe("Helper Actions", () => {
    it("clearError clears error", () => {
      store.error = "Some error";
      store.clearError();
      expect(store.error).toBeNull();
    });
  });

  describe("$reset", () => {
    it("resets all state to initial values", () => {
      store.wasteLogs = [{ id: 1 }];
      store.isLoading = true;
      store.isSubmitting = true;
      store.error = "Error";
      store.filters = { search: "test", reason: "expired", date: "2024-01-15" };
      store.pagination.currentPage = 5;

      store.$reset();

      expect(store.wasteLogs).toEqual([]);
      expect(store.isLoading).toBe(false);
      expect(store.isSubmitting).toBe(false);
      expect(store.error).toBeNull();
      expect(store.filters.search).toBe("");
      expect(store.filters.reason).toBe("");
      expect(store.pagination.currentPage).toBe(1);
    });
  });
});
