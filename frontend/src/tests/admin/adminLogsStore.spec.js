/**
 * Admin Logs Store Tests
 *
 * Comprehensive tests for the adminLogs Pinia store.
 * Includes bulk delete functionality per ADMIN-024.
 *
 * @requirement ADMIN-024 Activity logs with bulk delete
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAdminLogsStore } from "@/stores/adminLogs";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  adminDashboard: {
    getActivityLogs: vi.fn(),
    bulkDeleteActivityLogs: vi.fn(),
  },
}));

import { adminDashboard } from "@/services/dashboard";

describe("Admin Logs Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useAdminLogsStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty logs array", () => {
      expect(store.logs).toEqual([]);
    });

    it("starts with empty selectedLogIds", () => {
      expect(store.selectedLogIds).toEqual([]);
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });

    it("starts with default pagination", () => {
      expect(store.pagination.currentPage).toBe(1);
      expect(store.pagination.perPage).toBe(50);
    });

    it("starts with default filters", () => {
      expect(store.filters.userId).toBe(null);
      expect(store.filters.action).toBe(null);
      expect(store.filters.dateFrom).toBe(null);
      expect(store.filters.dateTo).toBe(null);
    });

    it("has actionTypes defined", () => {
      expect(store.actionTypes.length).toBeGreaterThan(0);
      expect(store.actionTypes).toContainEqual({
        value: "product_created",
        label: "Product Created",
      });
      expect(store.actionTypes).toContainEqual({
        value: "order_updated",
        label: "Order Updated",
      });
    });
  });

  describe("Computed Properties", () => {
    it("hasLogs returns false when empty", () => {
      expect(store.hasLogs).toBe(false);
    });

    it("hasLogs returns true when logs exist", () => {
      store.logs = [{ id: 1 }];
      expect(store.hasLogs).toBe(true);
    });

    it("hasSelectedLogs returns false when empty", () => {
      expect(store.hasSelectedLogs).toBe(false);
    });

    it("hasSelectedLogs returns true when selections exist", () => {
      store.selectedLogIds = [1, 2];
      expect(store.hasSelectedLogs).toBe(true);
    });

    it("selectedLogsCount returns correct count", () => {
      store.selectedLogIds = [1, 2, 3];
      expect(store.selectedLogsCount).toBe(3);
    });

    it("isAllSelected returns true when all logs selected", () => {
      store.logs = [{ id: 1 }, { id: 2 }];
      store.selectedLogIds = [1, 2];
      expect(store.isAllSelected).toBe(true);
    });

    it("isAllSelected returns false when partial selection", () => {
      store.logs = [{ id: 1 }, { id: 2 }];
      store.selectedLogIds = [1];
      expect(store.isAllSelected).toBe(false);
    });

    it("isAllSelected returns false when logs empty", () => {
      store.logs = [];
      store.selectedLogIds = [];
      expect(store.isAllSelected).toBe(false);
    });
  });

  describe("fetchLogs", () => {
    it("sets isLoading during fetch", async () => {
      adminDashboard.getActivityLogs.mockResolvedValue({
        success: true,
        logs: [],
        pagination: { current_page: 1, last_page: 1, per_page: 50, total: 0 },
      });

      const promise = store.fetchLogs();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores logs from response", async () => {
      const mockLogs = [
        { id: 1, action: "product_created", user_id: 1 },
        { id: 2, action: "order_updated", user_id: 1 },
      ];
      adminDashboard.getActivityLogs.mockResolvedValue({
        success: true,
        logs: mockLogs,
        pagination: { current_page: 1, last_page: 1, per_page: 50, total: 2 },
      });

      await store.fetchLogs();
      expect(store.logs).toEqual(mockLogs);
    });

    it("clears selected logs on fetch", async () => {
      store.selectedLogIds = [1, 2];
      adminDashboard.getActivityLogs.mockResolvedValue({
        success: true,
        logs: [],
        pagination: { current_page: 1, last_page: 1, per_page: 50, total: 0 },
      });

      await store.fetchLogs();
      expect(store.selectedLogIds).toEqual([]);
    });

    it("applies filters to API call", async () => {
      adminDashboard.getActivityLogs.mockResolvedValue({
        success: true,
        logs: [],
        pagination: { current_page: 1, last_page: 1, per_page: 50, total: 0 },
      });

      store.setFilters({ action: "product_created", userId: 5 });
      await store.fetchLogs();

      expect(adminDashboard.getActivityLogs).toHaveBeenCalledWith(
        expect.objectContaining({ action: "product_created", user_id: 5 })
      );
    });

    it("handles API errors", async () => {
      adminDashboard.getActivityLogs.mockRejectedValue(
        new Error("Network error")
      );

      await store.fetchLogs();
      expect(store.error).toBe("Network error");
    });
  });

  describe("Log Selection", () => {
    beforeEach(() => {
      store.logs = [{ id: 1 }, { id: 2 }, { id: 3 }];
    });

    it("toggleLogSelection adds log to selection", () => {
      store.toggleLogSelection(1);
      expect(store.selectedLogIds).toContain(1);
    });

    it("toggleLogSelection removes log from selection", () => {
      store.selectedLogIds = [1, 2];
      store.toggleLogSelection(1);
      expect(store.selectedLogIds).not.toContain(1);
      expect(store.selectedLogIds).toContain(2);
    });

    it("toggleAllSelection selects all when none selected", () => {
      store.toggleAllSelection();
      expect(store.selectedLogIds).toEqual([1, 2, 3]);
    });

    it("toggleAllSelection deselects all when all selected", () => {
      store.selectedLogIds = [1, 2, 3];
      store.toggleAllSelection();
      expect(store.selectedLogIds).toEqual([]);
    });

    it("clearSelection empties selectedLogIds", () => {
      store.selectedLogIds = [1, 2, 3];
      store.clearSelection();
      expect(store.selectedLogIds).toEqual([]);
    });
  });

  describe("bulkDeleteLogs", () => {
    it("deletes specified logs", async () => {
      store.logs = [{ id: 1 }, { id: 2 }, { id: 3 }];
      store.pagination.total = 3;

      adminDashboard.bulkDeleteActivityLogs.mockResolvedValue({
        success: true,
        deleted_count: 2,
      });

      await store.bulkDeleteLogs([1, 2]);

      expect(adminDashboard.bulkDeleteActivityLogs).toHaveBeenCalledWith({
        ids: [1, 2],
      });
      expect(store.logs).toHaveLength(1);
      expect(store.logs[0].id).toBe(3);
    });

    it("clears selection after delete", async () => {
      store.logs = [{ id: 1 }, { id: 2 }];
      store.selectedLogIds = [1, 2];

      adminDashboard.bulkDeleteActivityLogs.mockResolvedValue({
        success: true,
        deleted_count: 2,
      });

      await store.bulkDeleteLogs([1, 2]);
      expect(store.selectedLogIds).toEqual([]);
    });

    it("updates pagination total", async () => {
      store.logs = [{ id: 1 }, { id: 2 }, { id: 3 }];
      store.pagination.total = 3;

      adminDashboard.bulkDeleteActivityLogs.mockResolvedValue({
        success: true,
        deleted_count: 2,
      });

      await store.bulkDeleteLogs([1, 2]);
      expect(store.pagination.total).toBe(1);
    });

    it("handles API errors", async () => {
      adminDashboard.bulkDeleteActivityLogs.mockRejectedValue(
        new Error("Delete failed")
      );

      await expect(store.bulkDeleteLogs([1])).rejects.toThrow("Delete failed");
      expect(store.error).toBe("Delete failed");
    });
  });

  describe("deleteSelectedLogs", () => {
    it("deletes currently selected logs", async () => {
      store.logs = [{ id: 1 }, { id: 2 }, { id: 3 }];
      store.selectedLogIds = [1, 3];
      store.pagination.total = 3;

      adminDashboard.bulkDeleteActivityLogs.mockResolvedValue({
        success: true,
        deleted_count: 2,
      });

      await store.deleteSelectedLogs();

      expect(adminDashboard.bulkDeleteActivityLogs).toHaveBeenCalledWith({
        ids: [1, 3],
      });
      expect(store.logs).toHaveLength(1);
      expect(store.logs[0].id).toBe(2);
    });

    it("returns error when no logs selected", async () => {
      store.selectedLogIds = [];
      const result = await store.deleteSelectedLogs();
      expect(result.success).toBe(false);
    });
  });

  describe("Filter Management", () => {
    it("setFilters updates filter values", () => {
      store.setFilters({ action: "product_updated", userId: 10 });
      expect(store.filters.action).toBe("product_updated");
      expect(store.filters.userId).toBe(10);
    });

    it("resetFilters restores defaults", () => {
      store.setFilters({ action: "product_created", userId: 5 });
      store.resetFilters();
      expect(store.filters.action).toBe(null);
      expect(store.filters.userId).toBe(null);
    });
  });

  describe("getActionLabel", () => {
    it("returns the label for a known action type", () => {
      expect(store.getActionLabel("product_created")).toBe("Product Created");
    });

    it("formats unknown action types", () => {
      expect(store.getActionLabel("custom_action")).toBe("Custom Action");
    });
  });

  describe("$reset", () => {
    it("resets all state to initial values", () => {
      store.logs = [{ id: 1 }];
      store.selectedLogIds = [1];
      store.error = "Error";
      store.setFilters({ action: "product_created" });

      store.$reset();

      expect(store.logs).toEqual([]);
      expect(store.selectedLogIds).toEqual([]);
      expect(store.error).toBeNull();
      expect(store.filters.action).toBe(null);
    });
  });
});
