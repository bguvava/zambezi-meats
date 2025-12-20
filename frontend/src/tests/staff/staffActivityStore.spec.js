/**
 * Staff Activity Store Tests
 *
 * Comprehensive tests for the staffActivity Pinia store.
 *
 * @requirement STAFF-014 Activity tracking
 * @requirement STAFF-015 Performance stats
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useStaffActivityStore } from "@/stores/staffActivity";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  staffDashboard: {
    getActivityLog: vi.fn(),
    getPerformanceStats: vi.fn(),
  },
}));

import { staffDashboard } from "@/services/dashboard";

describe("Staff Activity Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useStaffActivityStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty activities array", () => {
      expect(store.activities).toEqual([]);
    });

    it("starts with null performanceStats", () => {
      expect(store.performanceStats).toBeNull();
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with isLoadingStats false", () => {
      expect(store.isLoadingStats).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });

    it("starts with default filters", () => {
      expect(store.filters.type).toBe("");
      expect(store.filters.dateFrom).toBe("");
      expect(store.filters.dateTo).toBe("");
    });

    it("starts with default pagination", () => {
      expect(store.pagination.currentPage).toBe(1);
      expect(store.pagination.perPage).toBe(20);
    });

    it("has activityTypes defined", () => {
      expect(store.activityTypes).toBeDefined();
      expect(Array.isArray(store.activityTypes)).toBe(true);
      expect(store.activityTypes.length).toBeGreaterThan(0);
    });
  });

  describe("Activity Types", () => {
    it("includes common activity types", () => {
      const typeValues = store.activityTypes.map((t) => t.value);
      expect(typeValues).toContain("order_status_updated");
      expect(typeValues).toContain("delivery_completed");
      expect(typeValues).toContain("stock_updated");
    });

    it("each type has value, label, and icon", () => {
      store.activityTypes.forEach((type) => {
        expect(type.value).toBeDefined();
        expect(type.label).toBeDefined();
        expect(type.icon).toBeDefined();
      });
    });
  });

  describe("Computed Properties", () => {
    it("hasActivities returns false when empty", () => {
      expect(store.hasActivities).toBe(false);
    });

    it("hasActivities returns true when activities exist", () => {
      store.activities = [{ id: 1 }];
      expect(store.hasActivities).toBe(true);
    });

    it("todaysActivities filters correctly", () => {
      const today = new Date().toISOString().split("T")[0];
      const yesterday = new Date(Date.now() - 86400000)
        .toISOString()
        .split("T")[0];

      store.activities = [
        { id: 1, created_at: `${today}T10:00:00Z` },
        { id: 2, created_at: `${yesterday}T10:00:00Z` },
        { id: 3, created_at: `${today}T14:00:00Z` },
      ];
      expect(store.todaysActivities).toHaveLength(2);
    });

    it("activitiesByType groups activities correctly", () => {
      store.activities = [
        { id: 1, type: "order_status_updated" },
        { id: 2, type: "order_status_updated" },
        { id: 3, type: "delivery_completed" },
      ];
      const grouped = store.activitiesByType;
      expect(grouped.order_status_updated).toHaveLength(2);
      expect(grouped.delivery_completed).toHaveLength(1);
    });

    it("recentActivities returns first 10", () => {
      store.activities = Array.from({ length: 15 }, (_, i) => ({ id: i + 1 }));
      expect(store.recentActivities).toHaveLength(10);
    });

    it("activityStats calculates totals by type", () => {
      store.activities = [
        { id: 1, type: "order_status_updated" },
        { id: 2, type: "order_status_updated" },
        { id: 3, type: "delivery_completed" },
        { id: 4, type: "stock_updated" },
      ];
      const stats = store.activityStats;
      expect(stats.total).toBe(4);
      expect(stats.byType.order_status_updated).toBe(2);
      expect(stats.byType.delivery_completed).toBe(1);
      expect(stats.byType.stock_updated).toBe(1);
    });
  });

  describe("fetchActivityLog", () => {
    it("sets isLoading during fetch", async () => {
      staffDashboard.getActivityLog.mockResolvedValue({
        success: true,
        data: [],
      });

      const promise = store.fetchActivityLog();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores activities from response data", async () => {
      const mockActivities = [
        { id: 1, type: "order_processed", description: "Processed order #123" },
        {
          id: 2,
          type: "delivery_completed",
          description: "Delivered order #124",
        },
      ];
      staffDashboard.getActivityLog.mockResolvedValue({
        success: true,
        data: mockActivities,
      });

      await store.fetchActivityLog();
      expect(store.activities).toEqual(mockActivities);
    });

    it("stores activities from response.activities", async () => {
      const mockActivities = [{ id: 1, type: "order_processed" }];
      staffDashboard.getActivityLog.mockResolvedValue({
        success: true,
        activities: mockActivities,
      });

      await store.fetchActivityLog();
      expect(store.activities).toEqual(mockActivities);
    });

    it("updates pagination from meta", async () => {
      staffDashboard.getActivityLog.mockResolvedValue({
        success: true,
        data: [],
        meta: {
          current_page: 2,
          last_page: 10,
          total: 250,
        },
      });

      await store.fetchActivityLog();
      expect(store.pagination.currentPage).toBe(2);
      expect(store.pagination.totalPages).toBe(10);
      expect(store.pagination.total).toBe(250);
    });

    it("includes filters in request", async () => {
      staffDashboard.getActivityLog.mockResolvedValue({
        success: true,
        data: [],
      });

      store.filters.type = "order_status_updated";
      store.filters.dateFrom = "2024-01-01";
      store.filters.dateTo = "2024-01-31";
      await store.fetchActivityLog();

      expect(staffDashboard.getActivityLog).toHaveBeenCalledWith(
        expect.objectContaining({
          type: "order_status_updated",
          dateFrom: "2024-01-01",
          dateTo: "2024-01-31",
        })
      );
    });

    it("sets error on failure", async () => {
      staffDashboard.getActivityLog.mockRejectedValue(
        new Error("Network error")
      );

      await expect(store.fetchActivityLog()).rejects.toThrow();
      expect(store.error).toBe("Network error");
    });
  });

  describe("fetchPerformanceStats", () => {
    it("sets isLoadingStats during fetch", async () => {
      staffDashboard.getPerformanceStats.mockResolvedValue({
        success: true,
        data: {},
      });

      const promise = store.fetchPerformanceStats();
      expect(store.isLoadingStats).toBe(true);
      await promise;
      expect(store.isLoadingStats).toBe(false);
    });

    it("stores performanceStats from response data", async () => {
      const mockStats = {
        orders_processed: 50,
        deliveries_completed: 30,
        average_order_time: 15,
        on_time_percentage: 95.5,
      };
      staffDashboard.getPerformanceStats.mockResolvedValue({
        success: true,
        data: mockStats,
      });

      await store.fetchPerformanceStats();
      expect(store.performanceStats).toEqual(mockStats);
    });

    it("stores from response.stats", async () => {
      const mockStats = { orders_processed: 50 };
      staffDashboard.getPerformanceStats.mockResolvedValue({
        success: true,
        stats: mockStats,
      });

      await store.fetchPerformanceStats();
      expect(store.performanceStats).toEqual(mockStats);
    });

    it("passes params parameter", async () => {
      staffDashboard.getPerformanceStats.mockResolvedValue({
        success: true,
        data: {},
      });

      await store.fetchPerformanceStats({ period: "month" });
      expect(staffDashboard.getPerformanceStats).toHaveBeenCalledWith({
        period: "month",
      });
    });

    it("defaults to empty params", async () => {
      staffDashboard.getPerformanceStats.mockResolvedValue({
        success: true,
        data: {},
      });

      await store.fetchPerformanceStats();
      expect(staffDashboard.getPerformanceStats).toHaveBeenCalledWith({});
    });

    it("sets error on failure", async () => {
      staffDashboard.getPerformanceStats.mockRejectedValue(
        new Error("Stats failed")
      );

      await expect(store.fetchPerformanceStats()).rejects.toThrow();
      expect(store.error).toBe("Stats failed");
    });
  });

  describe("Filter and Pagination Actions", () => {
    it("setFilters updates filters and resets page", () => {
      store.pagination.currentPage = 3;
      store.setFilters({
        type: "order_status_updated",
        dateFrom: "2024-01-01",
      });

      expect(store.filters.type).toBe("order_status_updated");
      expect(store.filters.dateFrom).toBe("2024-01-01");
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
      store.activities = [{ id: 1 }];
      store.performanceStats = { orders_processed: 50 };
      store.isLoading = true;
      store.isLoadingStats = true;
      store.error = "Error";
      store.filters = {
        type: "order_processed",
        dateFrom: "2024-01-01",
        dateTo: "2024-01-31",
      };
      store.pagination.currentPage = 5;

      store.$reset();

      expect(store.activities).toEqual([]);
      expect(store.performanceStats).toBeNull();
      expect(store.isLoading).toBe(false);
      expect(store.isLoadingStats).toBe(false);
      expect(store.error).toBeNull();
      expect(store.filters.type).toBe("");
      expect(store.filters.dateFrom).toBe("");
      expect(store.pagination.currentPage).toBe(1);
    });
  });
});
