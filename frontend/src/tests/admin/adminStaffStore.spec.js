/**
 * Admin Staff Store Tests
 *
 * Comprehensive tests for the adminStaff Pinia store.
 *
 * @requirement ADMIN-018 Create staff management page
 * @requirement ADMIN-019 Create/edit staff accounts
 * @requirement ADMIN-020 Activate/deactivate staff
 * @requirement ADMIN-021 View staff activity
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAdminStaffStore } from "@/stores/adminStaff";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  adminDashboard: {
    getStaff: vi.fn(),
    createStaff: vi.fn(),
    updateStaff: vi.fn(),
    deleteStaff: vi.fn(),
    getStaffActivity: vi.fn(),
  },
}));

import { adminDashboard } from "@/services/dashboard";

describe("Admin Staff Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useAdminStaffStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty staff array", () => {
      expect(store.staff).toEqual([]);
    });

    it("starts with null currentStaff", () => {
      expect(store.currentStaff).toBeNull();
    });

    it("starts with empty staffActivity", () => {
      expect(store.staffActivity).toEqual([]);
    });

    it("starts with null staffStats", () => {
      expect(store.staffStats).toBeNull();
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
      expect(store.filters.search).toBe("");
    });
  });

  describe("Computed Properties", () => {
    it("hasStaff returns false when empty", () => {
      expect(store.hasStaff).toBe(false);
    });

    it("hasStaff returns true when staff exist", () => {
      store.staff = [{ id: 1 }];
      expect(store.hasStaff).toBe(true);
    });

    it("activeStaffCount counts active staff", () => {
      store.staff = [
        { id: 1, is_active: true },
        { id: 2, is_active: false },
        { id: 3 }, // No is_active means active
      ];
      expect(store.activeStaffCount).toBe(2);
    });

    it("adminCount counts admins", () => {
      store.staff = [
        { id: 1, role: "admin" },
        { id: 2, role: "staff" },
        { id: 3, role: "admin" },
      ];
      expect(store.adminCount).toBe(2);
    });

    it("staffCount counts staff role", () => {
      store.staff = [
        { id: 1, role: "admin" },
        { id: 2, role: "staff" },
        { id: 3, role: "staff" },
      ];
      expect(store.staffCount).toBe(2);
    });
  });

  describe("fetchStaff", () => {
    it("sets isLoading during fetch", async () => {
      adminDashboard.getStaff.mockResolvedValue({
        success: true,
        staff: [],
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      });

      const promise = store.fetchStaff();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores staff from response", async () => {
      const mockStaff = [
        { id: 1, name: "Admin User", role: "admin" },
        { id: 2, name: "Staff User", role: "staff" },
      ];
      adminDashboard.getStaff.mockResolvedValue({
        success: true,
        staff: mockStaff,
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
      });

      await store.fetchStaff();
      expect(store.staff).toEqual(mockStaff);
    });

    it("applies search filter", async () => {
      adminDashboard.getStaff.mockResolvedValue({
        success: true,
        staff: [],
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      });

      store.setFilters({ search: "john" });
      await store.fetchStaff();

      expect(adminDashboard.getStaff).toHaveBeenCalledWith(
        expect.objectContaining({ search: "john" })
      );
    });

    it("handles API errors", async () => {
      adminDashboard.getStaff.mockRejectedValue(new Error("Network error"));

      await store.fetchStaff();
      expect(store.error).toBe("Network error");
    });
  });

  describe("createStaff", () => {
    it("creates staff and adds to list", async () => {
      const newStaff = { id: 1, name: "New Staff", role: "staff" };
      adminDashboard.createStaff.mockResolvedValue({
        success: true,
        staff: newStaff,
      });

      await store.createStaff({
        name: "New Staff",
        email: "staff@example.com",
        password: "password123",
        role: "staff",
      });

      expect(store.staff[0]).toEqual(newStaff);
      expect(store.pagination.total).toBe(1);
    });
  });

  describe("updateStaff", () => {
    it("updates staff in list", async () => {
      store.staff = [{ id: 1, name: "Old Name" }];
      const updatedStaff = { id: 1, name: "New Name" };

      adminDashboard.updateStaff.mockResolvedValue({
        success: true,
        staff: updatedStaff,
      });

      await store.updateStaff(1, { name: "New Name" });
      expect(store.staff[0].name).toBe("New Name");
      expect(store.currentStaff.name).toBe("New Name");
    });
  });

  describe("deleteStaff", () => {
    it("removes staff from list", async () => {
      store.staff = [
        { id: 1, name: "Staff A" },
        { id: 2, name: "Staff B" },
      ];
      store.pagination.total = 2;

      adminDashboard.deleteStaff.mockResolvedValue({
        success: true,
      });

      await store.deleteStaff(1);
      expect(store.staff).toHaveLength(1);
      expect(store.staff[0].id).toBe(2);
      expect(store.pagination.total).toBe(1);
    });
  });

  describe("toggleStaffStatus", () => {
    it("toggles staff active status", async () => {
      store.staff = [{ id: 1, is_active: true }];
      const updatedStaff = { id: 1, is_active: false };

      adminDashboard.updateStaff.mockResolvedValue({
        success: true,
        staff: updatedStaff,
      });

      await store.toggleStaffStatus(1, false);
      expect(store.staff[0].is_active).toBe(false);
    });
  });

  describe("fetchStaffActivity", () => {
    it("fetches staff activity and stats", async () => {
      const mockStaff = { id: 1, name: "Staff Member" };
      const mockActivity = [{ id: 1, action: "order_updated" }];
      const mockStats = { orders_processed: 50, deliveries_completed: 30 };

      adminDashboard.getStaffActivity.mockResolvedValue({
        success: true,
        staff: mockStaff,
        activity: mockActivity,
        stats: mockStats,
      });

      await store.fetchStaffActivity(1);

      expect(store.currentStaff).toEqual(mockStaff);
      expect(store.staffActivity).toEqual(mockActivity);
      expect(store.staffStats).toEqual(mockStats);
    });
  });

  describe("setCurrentStaff", () => {
    it("sets current staff member", () => {
      const staffMember = { id: 1, name: "Test" };
      store.setCurrentStaff(staffMember);
      expect(store.currentStaff).toEqual(staffMember);
    });
  });

  describe("Filter Management", () => {
    it("setFilters updates filter values", () => {
      store.setFilters({ search: "test" });
      expect(store.filters.search).toBe("test");
    });

    it("resetFilters restores defaults", () => {
      store.setFilters({ search: "test" });
      store.resetFilters();
      expect(store.filters.search).toBe("");
    });
  });

  describe("$reset", () => {
    it("resets all state to initial values", () => {
      store.staff = [{ id: 1 }];
      store.currentStaff = { id: 1 };
      store.staffActivity = [{ id: 1 }];
      store.staffStats = { orders_processed: 10 };
      store.error = "Error";

      store.$reset();

      expect(store.staff).toEqual([]);
      expect(store.currentStaff).toBeNull();
      expect(store.staffActivity).toEqual([]);
      expect(store.staffStats).toBeNull();
      expect(store.error).toBeNull();
    });
  });
});
