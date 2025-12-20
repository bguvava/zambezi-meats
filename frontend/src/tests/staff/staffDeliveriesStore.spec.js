/**
 * Staff Deliveries Store Tests
 *
 * Comprehensive tests for the staffDeliveries Pinia store.
 *
 * @requirement STAFF-007 Deliveries management
 * @requirement STAFF-008 Proof of delivery capture
 * @requirement STAFF-009 Pickup management
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useStaffDeliveriesStore } from "@/stores/staffDeliveries";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  staffDashboard: {
    getTodaysDeliveries: vi.fn(),
    getTodaysPickups: vi.fn(),
    markOutForDelivery: vi.fn(),
    uploadProofOfDelivery: vi.fn(),
    getProofOfDelivery: vi.fn(),
    markAsPickedUp: vi.fn(),
  },
}));

import { staffDashboard } from "@/services/dashboard";

describe("Staff Deliveries Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useStaffDeliveriesStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty deliveries array", () => {
      expect(store.deliveries).toEqual([]);
    });

    it("starts with empty pickups array", () => {
      expect(store.pickups).toEqual([]);
    });

    it("starts with null selectedDelivery", () => {
      expect(store.selectedDelivery).toBeNull();
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with isUploadingPOD false", () => {
      expect(store.isUploadingPOD).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });
  });

  describe("Computed Properties", () => {
    it("hasDeliveries returns false when empty", () => {
      expect(store.hasDeliveries).toBe(false);
    });

    it("hasDeliveries returns true when deliveries exist", () => {
      store.deliveries = [{ id: 1 }];
      expect(store.hasDeliveries).toBe(true);
    });

    it("hasPickups returns false when empty", () => {
      expect(store.hasPickups).toBe(false);
    });

    it("hasPickups returns true when pickups exist", () => {
      store.pickups = [{ id: 1 }];
      expect(store.hasPickups).toBe(true);
    });

    it("pendingDeliveries filters correctly", () => {
      store.deliveries = [
        { id: 1, status: "ready_for_delivery" },
        { id: 2, status: "out_for_delivery" },
        { id: 3, status: "delivered" },
      ];
      expect(store.scheduledDeliveries).toHaveLength(1);
    });

    it("inProgressDeliveries filters correctly", () => {
      store.deliveries = [
        { id: 1, status: "ready_for_delivery" },
        { id: 2, status: "out_for_delivery" },
      ];
      expect(store.outForDeliveryItems).toHaveLength(1);
    });

    it("completedDeliveries filters correctly", () => {
      store.deliveries = [
        { id: 1, status: "delivered" },
        { id: 2, status: "out_for_delivery" },
      ];
      expect(store.completedDeliveries).toHaveLength(1);
    });

    it("pendingPickups filters correctly", () => {
      store.pickups = [
        { id: 1, status: "ready_for_pickup" },
        { id: 2, status: "picked_up" },
      ];
      expect(store.pendingPickups).toHaveLength(1);
    });

    it("completedPickups filters correctly", () => {
      store.pickups = [
        { id: 1, status: "ready_for_pickup" },
        { id: 2, status: "picked_up" },
      ];
      expect(store.completedPickups).toHaveLength(1);
    });

    it("deliveryStats calculates all counts", () => {
      store.deliveries = [
        { id: 1, status: "ready_for_delivery" },
        { id: 2, status: "out_for_delivery" },
        { id: 3, status: "delivered" },
      ];
      const stats = store.deliveryCounts;
      expect(stats.total).toBe(3);
      expect(stats.scheduled).toBe(1);
      expect(stats.outForDelivery).toBe(1);
      expect(stats.completed).toBe(1);
    });

    it("deliveriesByTimeSlot groups deliveries correctly", () => {
      store.deliveries = [
        { id: 1, time_slot: "08:00-10:00" },
        { id: 2, time_slot: "08:00-10:00" },
        { id: 3, time_slot: "14:00-16:00" },
      ];
      const grouped = store.deliveriesByTimeSlot;
      expect(grouped["08:00-10:00"]).toHaveLength(2);
      expect(grouped["14:00-16:00"]).toHaveLength(1);
    });
  });

  describe("fetchTodaysDeliveries", () => {
    it("sets isLoading during fetch", async () => {
      staffDashboard.getTodaysDeliveries.mockResolvedValue({
        success: true,
        data: [],
      });

      const promise = store.fetchTodaysDeliveries();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores deliveries from response data", async () => {
      const mockDeliveries = [
        { id: 1, order_number: "ORD-001" },
        { id: 2, order_number: "ORD-002" },
      ];
      staffDashboard.getTodaysDeliveries.mockResolvedValue({
        success: true,
        data: mockDeliveries,
      });

      await store.fetchTodaysDeliveries();
      expect(store.deliveries).toEqual(mockDeliveries);
    });

    it("stores deliveries from response.deliveries", async () => {
      const mockDeliveries = [{ id: 1, order_number: "ORD-001" }];
      staffDashboard.getTodaysDeliveries.mockResolvedValue({
        success: true,
        deliveries: mockDeliveries,
      });

      await store.fetchTodaysDeliveries();
      expect(store.deliveries).toEqual(mockDeliveries);
    });

    it("sets error on failure", async () => {
      staffDashboard.getTodaysDeliveries.mockRejectedValue(
        new Error("Network error")
      );

      await expect(store.fetchTodaysDeliveries()).rejects.toThrow();
      expect(store.error).toBe("Network error");
    });
  });

  describe("fetchTodaysPickups", () => {
    it("stores pickups from response data", async () => {
      const mockPickups = [
        { id: 1, order_number: "ORD-001" },
        { id: 2, order_number: "ORD-002" },
      ];
      staffDashboard.getTodaysPickups.mockResolvedValue({
        success: true,
        data: mockPickups,
      });

      await store.fetchTodaysPickups();
      expect(store.pickups).toEqual(mockPickups);
    });

    it("stores pickups from response.pickups", async () => {
      const mockPickups = [{ id: 1, order_number: "ORD-001" }];
      staffDashboard.getTodaysPickups.mockResolvedValue({
        success: true,
        pickups: mockPickups,
      });

      await store.fetchTodaysPickups();
      expect(store.pickups).toEqual(mockPickups);
    });

    it("sets error on failure", async () => {
      staffDashboard.getTodaysPickups.mockRejectedValue(
        new Error("Network error")
      );

      await expect(store.fetchTodaysPickups()).rejects.toThrow();
      expect(store.error).toBe("Network error");
    });
  });

  describe("markOutForDelivery", () => {
    beforeEach(() => {
      store.deliveries = [
        { id: 1, status: "ready_for_delivery" },
        { id: 2, status: "ready_for_delivery" },
      ];
    });

    it("marks delivery as out for delivery", async () => {
      staffDashboard.markOutForDelivery.mockResolvedValue({ success: true });

      await store.markOutForDelivery(1);
      expect(staffDashboard.markOutForDelivery).toHaveBeenCalledWith(1);
    });

    it("updates local delivery status on success", async () => {
      staffDashboard.markOutForDelivery.mockResolvedValue({ success: true });

      await store.markOutForDelivery(1);
      expect(store.deliveries[0].status).toBe("out_for_delivery");
    });

    it("sets error on failure", async () => {
      staffDashboard.markOutForDelivery.mockRejectedValue(
        new Error("Update failed")
      );

      await expect(store.markOutForDelivery(1)).rejects.toThrow();
      expect(store.error).toBe("Update failed");
    });
  });

  describe("uploadProofOfDelivery", () => {
    beforeEach(() => {
      store.deliveries = [{ id: 1, status: "out_for_delivery" }];
    });

    it("sets isUploadingPOD during upload", async () => {
      staffDashboard.uploadProofOfDelivery.mockResolvedValue({ success: true });

      const promise = store.uploadProofOfDelivery(1, {});
      expect(store.isUploadingPOD).toBe(true);
      await promise;
      expect(store.isUploadingPOD).toBe(false);
    });

    it("uploads POD data via API", async () => {
      const podData = {
        signature: "data:image/png;base64,xyz",
        photo: null,
        notes: "Left at door",
      };
      staffDashboard.uploadProofOfDelivery.mockResolvedValue({ success: true });

      await store.uploadProofOfDelivery(1, podData);
      expect(staffDashboard.uploadProofOfDelivery).toHaveBeenCalledWith(
        1,
        podData
      );
    });

    it("updates local delivery status on success", async () => {
      staffDashboard.uploadProofOfDelivery.mockResolvedValue({ success: true });

      await store.uploadProofOfDelivery(1, {});
      expect(store.deliveries[0].status).toBe("delivered");
    });

    it("sets error on failure", async () => {
      staffDashboard.uploadProofOfDelivery.mockRejectedValue(
        new Error("Upload failed")
      );

      await expect(store.uploadProofOfDelivery(1, {})).rejects.toThrow();
      expect(store.error).toBe("Upload failed");
    });
  });

  describe("markAsPickedUp", () => {
    beforeEach(() => {
      store.pickups = [
        { id: 1, status: "ready_for_pickup" },
        { id: 2, status: "ready_for_pickup" },
      ];
    });

    it("marks order as picked up", async () => {
      staffDashboard.markAsPickedUp.mockResolvedValue({ success: true });

      await store.markAsPickedUp(1);
      expect(staffDashboard.markAsPickedUp).toHaveBeenCalledWith(1, undefined);
    });

    it("passes notes when provided", async () => {
      staffDashboard.markAsPickedUp.mockResolvedValue({ success: true });

      await store.markAsPickedUp(1, "Customer ID verified");
      expect(staffDashboard.markAsPickedUp).toHaveBeenCalledWith(
        1,
        "Customer ID verified"
      );
    });

    it("updates local pickup status on success", async () => {
      staffDashboard.markAsPickedUp.mockResolvedValue({ success: true });

      await store.markAsPickedUp(1);
      expect(store.pickups[0].status).toBe("picked_up");
    });

    it("sets error on failure", async () => {
      staffDashboard.markAsPickedUp.mockRejectedValue(
        new Error("Update failed")
      );

      await expect(store.markAsPickedUp(1)).rejects.toThrow();
      expect(store.error).toBe("Update failed");
    });
  });

  describe("Helper Actions", () => {
    it("selectDelivery sets selectedDelivery", () => {
      const delivery = { id: 1, order_number: "ORD-001" };
      store.selectDelivery(delivery);
      expect(store.selectedDelivery).toEqual(delivery);
    });

    it("clearSelectedDelivery clears selectedDelivery", () => {
      store.selectedDelivery = { id: 1 };
      store.clearSelectedDelivery();
      expect(store.selectedDelivery).toBeNull();
    });

    it("clearError clears error", () => {
      store.error = "Some error";
      store.clearError();
      expect(store.error).toBeNull();
    });
  });

  describe("$reset", () => {
    it("resets all state to initial values", () => {
      store.deliveries = [{ id: 1 }];
      store.pickups = [{ id: 2 }];
      store.selectedDelivery = { id: 1 };
      store.isLoading = true;
      store.isUploading = true;
      store.error = "Error";

      store.$reset();

      expect(store.deliveries).toEqual([]);
      expect(store.pickups).toEqual([]);
      expect(store.selectedDelivery).toBeNull();
      expect(store.isLoading).toBe(false);
      expect(store.isUploadingPOD).toBe(false);
      expect(store.error).toBeNull();
    });
  });
});
