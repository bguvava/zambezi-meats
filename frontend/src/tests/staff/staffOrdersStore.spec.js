/**
 * Staff Orders Store Tests
 *
 * Comprehensive tests for the staffOrders Pinia store.
 *
 * @requirement STAFF-003 Order queue management
 * @requirement STAFF-004 Order status updates
 * @requirement STAFF-005 Order notes
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useStaffOrdersStore } from "@/stores/staffOrders";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  staffDashboard: {
    getOrderQueue: vi.fn(),
    getOrder: vi.fn(),
    updateOrderStatus: vi.fn(),
    addOrderNote: vi.fn(),
  },
}));

import { staffDashboard } from "@/services/dashboard";

describe("Staff Orders Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useStaffOrdersStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty orders array", () => {
      expect(store.orders).toEqual([]);
    });

    it("starts with null selectedOrder", () => {
      expect(store.selectedOrder).toBeNull();
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

    it("starts with default pagination", () => {
      expect(store.pagination.currentPage).toBe(1);
      expect(store.pagination.perPage).toBe(15);
    });

    it("starts with default filters", () => {
      expect(store.filters.status).toBe("");
      expect(store.filters.search).toBe("");
      expect(store.filters.date).toBe("today");
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

    it("pendingOrders filters correctly", () => {
      store.orders = [
        { id: 1, status: "pending" },
        { id: 2, status: "processing" },
        { id: 3, status: "pending" },
      ];
      expect(store.pendingOrders).toHaveLength(2);
    });

    it("processingOrders filters correctly", () => {
      store.orders = [
        { id: 1, status: "pending" },
        { id: 2, status: "processing" },
      ];
      expect(store.processingOrders).toHaveLength(1);
    });

    it("readyOrders includes both pickup and delivery", () => {
      store.orders = [
        { id: 1, status: "ready_for_pickup" },
        { id: 2, status: "ready_for_delivery" },
        { id: 3, status: "pending" },
      ];
      expect(store.readyOrders).toHaveLength(2);
    });

    it("outForDeliveryOrders filters correctly", () => {
      store.orders = [
        { id: 1, status: "out_for_delivery" },
        { id: 2, status: "delivered" },
      ];
      expect(store.outForDeliveryOrders).toHaveLength(1);
    });

    it("orderCounts calculates all counts", () => {
      store.orders = [
        { id: 1, status: "pending" },
        { id: 2, status: "processing" },
        { id: 3, status: "ready_for_pickup" },
        { id: 4, status: "out_for_delivery" },
      ];
      const counts = store.orderCounts;
      expect(counts.all).toBe(4);
      expect(counts.pending).toBe(1);
      expect(counts.processing).toBe(1);
      expect(counts.ready).toBe(1);
      expect(counts.outForDelivery).toBe(1);
    });
  });

  describe("fetchOrders", () => {
    it("sets isLoading during fetch", async () => {
      staffDashboard.getOrderQueue.mockResolvedValue({
        success: true,
        data: [],
      });

      const promise = store.fetchOrders();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores orders from response data", async () => {
      const mockOrders = [
        { id: 1, order_number: "ORD-001" },
        { id: 2, order_number: "ORD-002" },
      ];
      staffDashboard.getOrderQueue.mockResolvedValue({
        success: true,
        data: mockOrders,
      });

      await store.fetchOrders();
      expect(store.orders).toEqual(mockOrders);
    });

    it("stores orders from response.orders", async () => {
      const mockOrders = [{ id: 1, order_number: "ORD-001" }];
      staffDashboard.getOrderQueue.mockResolvedValue({
        success: true,
        orders: mockOrders,
      });

      await store.fetchOrders();
      expect(store.orders).toEqual(mockOrders);
    });

    it("updates pagination from meta", async () => {
      staffDashboard.getOrderQueue.mockResolvedValue({
        success: true,
        data: [],
        meta: {
          current_page: 2,
          last_page: 5,
          total: 100,
          per_page: 15,
        },
      });

      await store.fetchOrders();
      expect(store.pagination.currentPage).toBe(2);
      expect(store.pagination.totalPages).toBe(5);
      expect(store.pagination.total).toBe(100);
    });

    it("sets error on failure", async () => {
      staffDashboard.getOrderQueue.mockRejectedValue(
        new Error("Network error")
      );

      await expect(store.fetchOrders()).rejects.toThrow();
      expect(store.error).toBe("Network error");
    });

    it("includes filters in request", async () => {
      staffDashboard.getOrderQueue.mockResolvedValue({
        success: true,
        data: [],
      });

      store.filters.status = "pending";
      store.filters.search = "test";
      await store.fetchOrders();

      expect(staffDashboard.getOrderQueue).toHaveBeenCalledWith(
        expect.objectContaining({
          status: "pending",
          search: "test",
        })
      );
    });
  });

  describe("fetchOrder", () => {
    it("fetches single order details", async () => {
      const mockOrder = { id: 1, order_number: "ORD-001", items: [] };
      staffDashboard.getOrder.mockResolvedValue({
        success: true,
        data: mockOrder,
      });

      await store.fetchOrder(1);
      expect(store.selectedOrder).toEqual(mockOrder);
    });

    it("sets error on failure", async () => {
      staffDashboard.getOrder.mockRejectedValue(new Error("Not found"));

      await expect(store.fetchOrder(999)).rejects.toThrow();
      expect(store.error).toBe("Not found");
    });
  });

  describe("updateOrderStatus", () => {
    beforeEach(() => {
      store.orders = [
        { id: 1, status: "pending", updated_at: "2024-01-01" },
        { id: 2, status: "processing", updated_at: "2024-01-01" },
      ];
    });

    it("updates order status via API", async () => {
      staffDashboard.updateOrderStatus.mockResolvedValue({ success: true });

      await store.updateOrderStatus(1, "processing");
      expect(staffDashboard.updateOrderStatus).toHaveBeenCalledWith(
        1,
        "processing",
        null
      );
    });

    it("updates local order on success", async () => {
      staffDashboard.updateOrderStatus.mockResolvedValue({ success: true });

      await store.updateOrderStatus(1, "processing");
      expect(store.orders[0].status).toBe("processing");
    });

    it("updates selectedOrder if matching", async () => {
      store.selectedOrder = { id: 1, status: "pending" };
      staffDashboard.updateOrderStatus.mockResolvedValue({ success: true });

      await store.updateOrderStatus(1, "confirmed");
      expect(store.selectedOrder.status).toBe("confirmed");
    });

    it("passes notes when provided", async () => {
      staffDashboard.updateOrderStatus.mockResolvedValue({ success: true });

      await store.updateOrderStatus(1, "processing", "Ready to pack");
      expect(staffDashboard.updateOrderStatus).toHaveBeenCalledWith(
        1,
        "processing",
        "Ready to pack"
      );
    });

    it("sets error on failure", async () => {
      staffDashboard.updateOrderStatus.mockRejectedValue(
        new Error("Update failed")
      );

      await expect(store.updateOrderStatus(1, "processing")).rejects.toThrow();
      expect(store.error).toBe("Update failed");
    });
  });

  describe("addNote", () => {
    it("adds note via API", async () => {
      store.selectedOrder = { id: 1 };
      staffDashboard.addOrderNote.mockResolvedValue({ success: true });
      staffDashboard.getOrder.mockResolvedValue({
        success: true,
        data: { id: 1, notes: [{ id: 1, note: "Test note" }] },
      });

      await store.addNote(1, "Test note");
      expect(staffDashboard.addOrderNote).toHaveBeenCalledWith(1, "Test note");
    });

    it("refreshes order after adding note", async () => {
      store.selectedOrder = { id: 1 };
      staffDashboard.addOrderNote.mockResolvedValue({ success: true });
      staffDashboard.getOrder.mockResolvedValue({
        success: true,
        data: { id: 1, notes: [{ id: 1, note: "Test note" }] },
      });

      await store.addNote(1, "Test note");
      expect(staffDashboard.getOrder).toHaveBeenCalledWith(1);
    });
  });

  describe("Filter and Pagination Actions", () => {
    it("setFilters updates filters and resets page", () => {
      store.pagination.currentPage = 5;
      store.setFilters({ status: "pending", search: "test" });

      expect(store.filters.status).toBe("pending");
      expect(store.filters.search).toBe("test");
      expect(store.pagination.currentPage).toBe(1);
    });

    it("setPage updates currentPage", () => {
      store.setPage(3);
      expect(store.pagination.currentPage).toBe(3);
    });

    it("selectOrder sets selectedOrder", () => {
      const order = { id: 1, order_number: "ORD-001" };
      store.selectOrder(order);
      expect(store.selectedOrder).toEqual(order);
    });

    it("clearSelectedOrder clears selectedOrder", () => {
      store.selectedOrder = { id: 1 };
      store.clearSelectedOrder();
      expect(store.selectedOrder).toBeNull();
    });

    it("clearError clears error", () => {
      store.error = "Some error";
      store.clearError();
      expect(store.error).toBeNull();
    });
  });

  describe("$reset", () => {
    it("resets all state to initial values", () => {
      store.orders = [{ id: 1 }];
      store.selectedOrder = { id: 1 };
      store.isLoading = true;
      store.error = "Error";
      store.pagination.currentPage = 5;
      store.filters.status = "pending";

      store.$reset();

      expect(store.orders).toEqual([]);
      expect(store.selectedOrder).toBeNull();
      expect(store.isLoading).toBe(false);
      expect(store.error).toBeNull();
      expect(store.pagination.currentPage).toBe(1);
      expect(store.filters.status).toBe("");
    });
  });
});
