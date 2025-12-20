/**
 * Admin Customers Store Tests
 *
 * Comprehensive tests for the adminCustomers Pinia store.
 *
 * @requirement ADMIN-016 Create customers management page
 * @requirement ADMIN-017 View customer details
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAdminCustomersStore } from "@/stores/adminCustomers";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  adminDashboard: {
    getCustomers: vi.fn(),
    getCustomer: vi.fn(),
    updateCustomer: vi.fn(),
  },
}));

import { adminDashboard } from "@/services/dashboard";

describe("Admin Customers Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useAdminCustomersStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty customers array", () => {
      expect(store.customers).toEqual([]);
    });

    it("starts with null currentCustomer", () => {
      expect(store.currentCustomer).toBeNull();
    });

    it("starts with empty customerOrders", () => {
      expect(store.customerOrders).toEqual([]);
    });

    it("starts with empty customerAddresses", () => {
      expect(store.customerAddresses).toEqual([]);
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
    it("hasCustomers returns false when empty", () => {
      expect(store.hasCustomers).toBe(false);
    });

    it("hasCustomers returns true when customers exist", () => {
      store.customers = [{ id: 1 }];
      expect(store.hasCustomers).toBe(true);
    });

    it("totalCustomers returns pagination total", () => {
      store.pagination.total = 50;
      expect(store.totalCustomers).toBe(50);
    });

    it("activeCustomersCount counts active customers", () => {
      store.customers = [
        { id: 1, is_active: true },
        { id: 2, is_active: false },
        { id: 3 }, // No is_active property means active
      ];
      expect(store.activeCustomersCount).toBe(2);
    });
  });

  describe("fetchCustomers", () => {
    it("sets isLoading during fetch", async () => {
      adminDashboard.getCustomers.mockResolvedValue({
        success: true,
        customers: [],
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      });

      const promise = store.fetchCustomers();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores customers from response", async () => {
      const mockCustomers = [
        { id: 1, name: "John Doe" },
        { id: 2, name: "Jane Doe" },
      ];
      adminDashboard.getCustomers.mockResolvedValue({
        success: true,
        customers: mockCustomers,
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
      });

      await store.fetchCustomers();
      expect(store.customers).toEqual(mockCustomers);
    });

    it("applies search filter", async () => {
      adminDashboard.getCustomers.mockResolvedValue({
        success: true,
        customers: [],
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      });

      store.setFilters({ search: "john" });
      await store.fetchCustomers();

      expect(adminDashboard.getCustomers).toHaveBeenCalledWith(
        expect.objectContaining({ search: "john" })
      );
    });

    it("handles API errors", async () => {
      adminDashboard.getCustomers.mockRejectedValue(new Error("Network error"));

      await store.fetchCustomers();
      expect(store.error).toBe("Network error");
    });
  });

  describe("fetchCustomer", () => {
    it("fetches customer details with orders and addresses", async () => {
      const mockCustomer = { id: 1, name: "John Doe" };
      const mockOrders = [{ id: 1, order_number: "ORD-001" }];
      const mockAddresses = [{ id: 1, street: "123 Main St" }];

      adminDashboard.getCustomer.mockResolvedValue({
        success: true,
        customer: mockCustomer,
        recent_orders: mockOrders,
        addresses: mockAddresses,
      });

      await store.fetchCustomer(1);

      expect(store.currentCustomer).toEqual(mockCustomer);
      expect(store.customerOrders).toEqual(mockOrders);
      expect(store.customerAddresses).toEqual(mockAddresses);
    });

    it("handles fetch error", async () => {
      adminDashboard.getCustomer.mockRejectedValue(new Error("Not found"));

      await expect(store.fetchCustomer(999)).rejects.toThrow("Not found");
      expect(store.error).toBe("Not found");
    });
  });

  describe("updateCustomer", () => {
    it("updates customer and refreshes list", async () => {
      store.customers = [{ id: 1, phone: "old" }];
      const updatedCustomer = { id: 1, phone: "new" };

      adminDashboard.updateCustomer.mockResolvedValue({
        success: true,
        customer: updatedCustomer,
      });

      await store.updateCustomer(1, { phone: "new" });
      expect(store.customers[0].phone).toBe("new");
      expect(store.currentCustomer.phone).toBe("new");
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
      store.customers = [{ id: 1 }];
      store.currentCustomer = { id: 1 };
      store.customerOrders = [{ id: 1 }];
      store.error = "Error";

      store.$reset();

      expect(store.customers).toEqual([]);
      expect(store.currentCustomer).toBeNull();
      expect(store.customerOrders).toEqual([]);
      expect(store.error).toBeNull();
    });
  });
});
