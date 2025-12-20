/**
 * Staff Dashboard Service Tests
 *
 * Comprehensive tests for the staffDashboard service methods.
 *
 * @requirement STAFF-016 Staff API integration
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";

// Mock the api module
vi.mock("@/services/api", () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}));

import api from "@/services/api";
import { staffDashboard } from "@/services/dashboard";

describe("Staff Dashboard Service", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("getOverview", () => {
    it("fetches dashboard overview", async () => {
      const mockResponse = {
        data: {
          success: true,
          data: {
            todays_orders: 15,
            pending_orders: 5,
            my_deliveries: 3,
          },
        },
      };
      api.get.mockResolvedValue(mockResponse);

      const result = await staffDashboard.getOverview();

      expect(api.get).toHaveBeenCalledWith("/staff/dashboard");
      expect(result).toEqual(mockResponse.data);
    });

    it("handles error", async () => {
      api.get.mockRejectedValue(new Error("Network error"));

      await expect(staffDashboard.getOverview()).rejects.toThrow(
        "Network error"
      );
    });
  });

  describe("getOrderQueue", () => {
    it("fetches order queue without params", async () => {
      const mockResponse = {
        data: {
          success: true,
          data: [{ id: 1, order_number: "ORD-001" }],
        },
      };
      api.get.mockResolvedValue(mockResponse);

      const result = await staffDashboard.getOrderQueue();

      expect(api.get).toHaveBeenCalledWith("/staff/orders/queue", {
        params: {},
      });
      expect(result).toEqual(mockResponse.data);
    });

    it("fetches order queue with params", async () => {
      const mockResponse = { data: { success: true, data: [] } };
      api.get.mockResolvedValue(mockResponse);

      const params = { status: "pending", page: 2 };
      await staffDashboard.getOrderQueue(params);

      expect(api.get).toHaveBeenCalledWith("/staff/orders/queue", { params });
    });
  });

  describe("getOrder", () => {
    it("fetches single order details", async () => {
      const mockResponse = {
        data: {
          success: true,
          data: { id: 1, order_number: "ORD-001", items: [] },
        },
      };
      api.get.mockResolvedValue(mockResponse);

      const result = await staffDashboard.getOrder(1);

      expect(api.get).toHaveBeenCalledWith("/staff/orders/1");
      expect(result).toEqual(mockResponse.data);
    });
  });

  describe("updateOrderStatus", () => {
    it("updates order status", async () => {
      const mockResponse = { data: { success: true } };
      api.put.mockResolvedValue(mockResponse);

      const result = await staffDashboard.updateOrderStatus(1, "processing");

      expect(api.put).toHaveBeenCalledWith("/staff/orders/1/status", {
        status: "processing",
      });
      expect(result).toEqual(mockResponse.data);
    });

    it("updates order status with notes", async () => {
      const mockResponse = { data: { success: true } };
      api.put.mockResolvedValue(mockResponse);

      await staffDashboard.updateOrderStatus(
        1,
        "processing",
        "Started packing"
      );

      expect(api.put).toHaveBeenCalledWith("/staff/orders/1/status", {
        status: "processing",
        notes: "Started packing",
      });
    });
  });

  describe("addOrderNote", () => {
    it("adds note to order", async () => {
      const mockResponse = { data: { success: true } };
      api.post.mockResolvedValue(mockResponse);

      const result = await staffDashboard.addOrderNote(1, "Customer called");

      expect(api.post).toHaveBeenCalledWith("/staff/orders/1/notes", {
        note: "Customer called",
      });
      expect(result).toEqual(mockResponse.data);
    });
  });

  describe("getTodaysDeliveries", () => {
    it("fetches todays deliveries", async () => {
      const mockResponse = {
        data: {
          success: true,
          data: [{ id: 1, status: "ready_for_delivery" }],
        },
      };
      api.get.mockResolvedValue(mockResponse);

      const result = await staffDashboard.getTodaysDeliveries();

      expect(api.get).toHaveBeenCalledWith("/staff/deliveries/today");
      expect(result).toEqual(mockResponse.data);
    });
  });

  describe("markOutForDelivery", () => {
    it("marks order as out for delivery", async () => {
      const mockResponse = { data: { success: true } };
      api.post.mockResolvedValue(mockResponse);

      const result = await staffDashboard.markOutForDelivery(1);

      expect(api.post).toHaveBeenCalledWith(
        "/staff/deliveries/1/out-for-delivery"
      );
      expect(result).toEqual(mockResponse.data);
    });
  });

  describe("uploadProofOfDelivery", () => {
    it("uploads POD data", async () => {
      const mockResponse = { data: { success: true } };
      api.post.mockResolvedValue(mockResponse);

      const podData = {
        signature: "data:image/png;base64,xyz",
        photo: null,
        notes: "Left at door",
      };
      const result = await staffDashboard.uploadProofOfDelivery(1, podData);

      expect(api.post).toHaveBeenCalledWith(
        "/staff/deliveries/1/pod",
        podData,
        {
          headers: { "Content-Type": "multipart/form-data" },
        }
      );
      expect(result).toEqual(mockResponse.data);
    });
  });

  describe("getProofOfDelivery", () => {
    it("fetches POD for order", async () => {
      const mockResponse = {
        data: {
          success: true,
          data: { signature: "data:image/png;base64,xyz" },
        },
      };
      api.get.mockResolvedValue(mockResponse);

      const result = await staffDashboard.getProofOfDelivery(1);

      expect(api.get).toHaveBeenCalledWith("/staff/deliveries/1/pod");
      expect(result).toEqual(mockResponse.data);
    });
  });

  describe("getTodaysPickups", () => {
    it("fetches todays pickups", async () => {
      const mockResponse = {
        data: {
          success: true,
          data: [{ id: 1, status: "ready_for_pickup" }],
        },
      };
      api.get.mockResolvedValue(mockResponse);

      const result = await staffDashboard.getTodaysPickups();

      expect(api.get).toHaveBeenCalledWith("/staff/pickups/today");
      expect(result).toEqual(mockResponse.data);
    });
  });

  describe("markAsPickedUp", () => {
    it("marks order as picked up", async () => {
      const mockResponse = { data: { success: true } };
      api.post.mockResolvedValue(mockResponse);

      const result = await staffDashboard.markAsPickedUp(1);

      expect(api.post).toHaveBeenCalledWith("/staff/pickups/1/picked-up", {
        receiver_name: undefined,
      });
      expect(result).toEqual(mockResponse.data);
    });

    it("marks as picked up with receiver name", async () => {
      const mockResponse = { data: { success: true } };
      api.post.mockResolvedValue(mockResponse);

      await staffDashboard.markAsPickedUp(1, "John Doe");

      expect(api.post).toHaveBeenCalledWith("/staff/pickups/1/picked-up", {
        receiver_name: "John Doe",
      });
    });
  });

  describe("getStockCheck", () => {
    it("fetches stock check data", async () => {
      const mockResponse = {
        data: {
          success: true,
          data: [{ id: 1, name: "Beef Steak", stock_quantity: 50 }],
        },
      };
      api.get.mockResolvedValue(mockResponse);

      const result = await staffDashboard.getStockCheck();

      expect(api.get).toHaveBeenCalledWith("/staff/stock", { params: {} });
      expect(result).toEqual(mockResponse.data);
    });
  });

  describe("updateStock", () => {
    it("updates stock for product", async () => {
      const mockResponse = { data: { success: true } };
      api.put.mockResolvedValue(mockResponse);

      const result = await staffDashboard.updateStock(1, 75, "After delivery");

      expect(api.put).toHaveBeenCalledWith("/staff/stock/1", {
        quantity: 75,
        notes: "After delivery",
      });
      expect(result).toEqual(mockResponse.data);
    });

    it("updates stock without notes", async () => {
      const mockResponse = { data: { success: true } };
      api.put.mockResolvedValue(mockResponse);

      await staffDashboard.updateStock(1, 75);

      expect(api.put).toHaveBeenCalledWith("/staff/stock/1", {
        quantity: 75,
      });
    });
  });

  describe("getWasteLogs", () => {
    it("fetches waste logs without params", async () => {
      const mockResponse = {
        data: {
          success: true,
          data: [{ id: 1, product_name: "Beef Steak", quantity: 5 }],
        },
      };
      api.get.mockResolvedValue(mockResponse);

      const result = await staffDashboard.getWasteLogs();

      expect(api.get).toHaveBeenCalledWith("/staff/waste", { params: {} });
      expect(result).toEqual(mockResponse.data);
    });

    it("fetches waste logs with params", async () => {
      const mockResponse = { data: { success: true, data: [] } };
      api.get.mockResolvedValue(mockResponse);

      const params = { reason: "expired", page: 2 };
      await staffDashboard.getWasteLogs(params);

      expect(api.get).toHaveBeenCalledWith("/staff/waste", { params });
    });
  });

  describe("logWaste", () => {
    it("logs waste entry", async () => {
      const mockResponse = { data: { success: true } };
      api.post.mockResolvedValue(mockResponse);

      const wasteData = {
        product_id: 1,
        quantity: 5,
        reason: "expired",
        notes: "Found in storage",
      };
      const result = await staffDashboard.logWaste(wasteData);

      expect(api.post).toHaveBeenCalledWith("/staff/waste", wasteData);
      expect(result).toEqual(mockResponse.data);
    });
  });

  describe("getActivityLog", () => {
    it("fetches activity log without params", async () => {
      const mockResponse = {
        data: {
          success: true,
          data: [{ id: 1, type: "order_processed" }],
        },
      };
      api.get.mockResolvedValue(mockResponse);

      const result = await staffDashboard.getActivityLog();

      expect(api.get).toHaveBeenCalledWith("/staff/activity", { params: {} });
      expect(result).toEqual(mockResponse.data);
    });

    it("fetches activity log with params", async () => {
      const mockResponse = { data: { success: true, data: [] } };
      api.get.mockResolvedValue(mockResponse);

      const params = { type: "order_processed", date_from: "2024-01-01" };
      await staffDashboard.getActivityLog(params);

      expect(api.get).toHaveBeenCalledWith("/staff/activity", { params });
    });
  });

  describe("getPerformanceStats", () => {
    it("fetches performance stats", async () => {
      const mockResponse = {
        data: {
          success: true,
          data: { orders_processed: 50, deliveries_completed: 30 },
        },
      };
      api.get.mockResolvedValue(mockResponse);

      const result = await staffDashboard.getPerformanceStats({
        period: "week",
      });

      expect(api.get).toHaveBeenCalledWith("/staff/performance", {
        params: { period: "week" },
      });
      expect(result).toEqual(mockResponse.data);
    });

    it("fetches with empty params", async () => {
      const mockResponse = { data: { success: true, data: {} } };
      api.get.mockResolvedValue(mockResponse);

      await staffDashboard.getPerformanceStats();

      expect(api.get).toHaveBeenCalledWith("/staff/performance", {
        params: {},
      });
    });

    it("accepts different periods", async () => {
      const mockResponse = { data: { success: true, data: {} } };
      api.get.mockResolvedValue(mockResponse);

      await staffDashboard.getPerformanceStats({ period: "month" });

      expect(api.get).toHaveBeenCalledWith("/staff/performance", {
        params: { period: "month" },
      });
    });
  });

  describe("Error Handling", () => {
    it("propagates network errors", async () => {
      api.get.mockRejectedValue(new Error("Network error"));

      await expect(staffDashboard.getOverview()).rejects.toThrow(
        "Network error"
      );
    });

    it("propagates API errors", async () => {
      const apiError = {
        response: {
          status: 401,
          data: { message: "Unauthorized" },
        },
      };
      api.get.mockRejectedValue(apiError);

      await expect(staffDashboard.getOrderQueue()).rejects.toEqual(apiError);
    });

    it("handles 404 errors", async () => {
      const notFoundError = {
        response: {
          status: 404,
          data: { message: "Order not found" },
        },
      };
      api.get.mockRejectedValue(notFoundError);

      await expect(staffDashboard.getOrder(999)).rejects.toEqual(notFoundError);
    });

    it("handles validation errors", async () => {
      const validationError = {
        response: {
          status: 422,
          data: {
            message: "The given data was invalid.",
            errors: { status: ["Invalid status"] },
          },
        },
      };
      api.put.mockRejectedValue(validationError);

      await expect(
        staffDashboard.updateOrderStatus(1, "invalid")
      ).rejects.toEqual(validationError);
    });
  });
});
