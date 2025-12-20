/**
 * Admin Orders Store Tests
 *
 * Comprehensive tests for the adminOrders Pinia store.
 *
 * @requirement ADMIN-005 Create orders management page
 * @requirement ADMIN-006 Implement order filtering
 * @requirement ADMIN-007 Create order detail view
 * @requirement ADMIN-008 Implement order actions
 * @requirement ADMIN-009 Assign orders to staff
 * @requirement ADMIN-010 Process refunds
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAdminOrdersStore } from "@/stores/adminOrders";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  adminDashboard: {
    getOrders: vi.fn(),
    getOrder: vi.fn(),
    updateOrder: vi.fn(),
    updateOrderStatus: vi.fn(),
    assignOrder: vi.fn(),
    refundOrder: vi.fn(),
  },
}));

import { adminDashboard } from "@/services/dashboard";

describe("Admin Orders Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useAdminOrdersStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty orders array", () => {
      expect(store.orders).toEqual([]);
    });

    it("starts with null currentOrder", () => {
      expect(store.currentOrder).toBeNull();
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
      expect(store.pagination.total).toBe(0);
    });

    it("starts with default filters", () => {
      expect(store.filters.status).toBe("all");
      expect(store.filters.search).toBe("");
      expect(store.filters.dateFrom).toBeNull();
      expect(store.filters.dateTo).toBeNull();
    });

    it("has orderStatuses defined", () => {
      expect(store.orderStatuses).toHaveLength(8);
      expect(store.orderStatuses[0].value).toBe("all");
    });
  });

  describe("Computed Properties", () => {
    it("hasOrders returns false when orders is empty", () => {
      expect(store.hasOrders).toBe(false);
    });

    it("hasOrders returns true when orders exist", () => {
      store.orders = [{ id: 1 }];
      expect(store.hasOrders).toBe(true);
    });

    it("pendingOrdersCount counts pending orders", () => {
      store.orders = [
        { id: 1, status: "pending" },
        { id: 2, status: "processing" },
        { id: 3, status: "pending" },
      ];
      expect(store.pendingOrdersCount).toBe(2);
    });

    it("getStatusColor returns correct color", () => {
      expect(store.getStatusColor("pending")).toBe("yellow");
      expect(store.getStatusColor("delivered")).toBe("green");
      expect(store.getStatusColor("cancelled")).toBe("red");
    });

    it("getStatusLabel returns correct label", () => {
      expect(store.getStatusLabel("pending")).toBe("Pending");
      expect(store.getStatusLabel("out_for_delivery")).toBe("Out for Delivery");
    });
  });

  describe("fetchOrders", () => {
    it("sets isLoading during fetch", async () => {
      adminDashboard.getOrders.mockResolvedValue({
        success: true,
        orders: [],
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      });

      const promise = store.fetchOrders();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores orders from response", async () => {
      const mockOrders = [
        { id: 1, order_number: "ORD-001" },
        { id: 2, order_number: "ORD-002" },
      ];
      adminDashboard.getOrders.mockResolvedValue({
        success: true,
        orders: mockOrders,
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
      });

      await store.fetchOrders();
      expect(store.orders).toEqual(mockOrders);
      expect(store.pagination.total).toBe(2);
    });

    it("applies filters to API call", async () => {
      adminDashboard.getOrders.mockResolvedValue({
        success: true,
        orders: [],
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      });

      store.setFilters({ status: "pending", search: "test" });
      await store.fetchOrders();

      expect(adminDashboard.getOrders).toHaveBeenCalledWith({
        page: 1,
        per_page: 15,
        status: "pending",
        search: "test",
      });
    });

    it("handles API errors", async () => {
      adminDashboard.getOrders.mockRejectedValue(new Error("Network error"));

      await store.fetchOrders();
      expect(store.error).toBe("Network error");
    });
  });

  describe("fetchOrder", () => {
    it("fetches single order details", async () => {
      const mockOrder = { id: 1, order_number: "ORD-001", items: [] };
      adminDashboard.getOrder.mockResolvedValue({
        success: true,
        order: mockOrder,
      });

      await store.fetchOrder(1);
      expect(store.currentOrder).toEqual(mockOrder);
    });

    it("handles fetch error", async () => {
      adminDashboard.getOrder.mockRejectedValue(new Error("Not found"));

      await expect(store.fetchOrder(999)).rejects.toThrow("Not found");
      expect(store.error).toBe("Not found");
    });
  });

  describe("updateOrder", () => {
    it("updates order and refreshes list", async () => {
      store.orders = [{ id: 1, notes: "old" }];
      const updatedOrder = { id: 1, notes: "new" };

      adminDashboard.updateOrder.mockResolvedValue({
        success: true,
        order: updatedOrder,
      });

      await store.updateOrder(1, { notes: "new" });
      expect(store.orders[0].notes).toBe("new");
      expect(store.currentOrder.notes).toBe("new");
    });
  });

  describe("updateOrderStatus", () => {
    it("updates order status", async () => {
      store.orders = [{ id: 1, status: "pending" }];

      adminDashboard.updateOrderStatus.mockResolvedValue({
        success: true,
      });

      await store.updateOrderStatus(1, "confirmed");
      expect(store.orders[0].status).toBe("confirmed");
    });

    it("updates currentOrder if matching", async () => {
      store.currentOrder = { id: 1, status: "pending" };
      store.orders = [{ id: 1, status: "pending" }];

      adminDashboard.updateOrderStatus.mockResolvedValue({
        success: true,
      });

      await store.updateOrderStatus(1, "processing");
      expect(store.currentOrder.status).toBe("processing");
    });
  });

  describe("assignOrder", () => {
    it("assigns order to staff member", async () => {
      store.orders = [{ id: 1, assigned_to: null }];
      const assignedOrder = {
        id: 1,
        assigned_to: 5,
        assignedStaff: { name: "John" },
      };

      adminDashboard.assignOrder.mockResolvedValue({
        success: true,
        order: assignedOrder,
      });

      await store.assignOrder(1, 5);
      expect(store.orders[0].assigned_to).toBe(5);
    });
  });

  describe("refundOrder", () => {
    it("processes refund for order", async () => {
      store.orders = [{ id: 1, status: "delivered" }];
      const refundedOrder = { id: 1, status: "cancelled" };

      adminDashboard.refundOrder.mockResolvedValue({
        success: true,
        order: refundedOrder,
      });

      await store.refundOrder(1, 50.0, "Customer request");
      expect(adminDashboard.refundOrder).toHaveBeenCalledWith(
        1,
        50.0,
        "Customer request"
      );
    });
  });

  describe("Filter Management", () => {
    it("setFilters updates filter values", () => {
      store.setFilters({ status: "pending", search: "test" });
      expect(store.filters.status).toBe("pending");
      expect(store.filters.search).toBe("test");
    });

    it("resetFilters restores defaults", () => {
      store.setFilters({ status: "pending", search: "test" });
      store.resetFilters();
      expect(store.filters.status).toBe("all");
      expect(store.filters.search).toBe("");
    });
  });

  describe("$reset", () => {
    it("resets all state to initial values", () => {
      store.orders = [{ id: 1 }];
      store.currentOrder = { id: 1 };
      store.error = "Some error";
      store.setFilters({ status: "pending" });

      store.$reset();

      expect(store.orders).toEqual([]);
      expect(store.currentOrder).toBeNull();
      expect(store.error).toBeNull();
      expect(store.filters.status).toBe("all");
    });
  });
});
