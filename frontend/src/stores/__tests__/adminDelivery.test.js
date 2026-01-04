/**
 * Admin Delivery Store Tests
 *
 * Tests for the delivery management Pinia store.
 * @requirements DEL-001 to DEL-019
 */
import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAdminDeliveryStore } from "../adminDelivery";

// Mock the dashboard service
vi.mock("@/services/dashboard", () => ({
  adminDashboard: {
    getDeliveryDashboard: vi.fn(),
    getDeliveries: vi.fn(),
    getDelivery: vi.fn(),
    assignDelivery: vi.fn(),
    getDeliveryPod: vi.fn(),
    downloadDeliveryPod: vi.fn(),
    resolveDeliveryIssue: vi.fn(),
    getDeliveryZones: vi.fn(),
    createDeliveryZone: vi.fn(),
    updateDeliveryZone: vi.fn(),
    deleteDeliveryZone: vi.fn(),
    getDeliverySettings: vi.fn(),
    updateDeliverySettings: vi.fn(),
    getDeliveryMapData: vi.fn(),
    getDeliveryReport: vi.fn(),
    exportDeliveries: vi.fn(),
    getStaff: vi.fn(),
  },
}));

import { adminDashboard } from "@/services/dashboard";

describe("AdminDeliveryStore", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    store = useAdminDeliveryStore();
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  // ===========================================================================
  // STATE TESTS
  // ===========================================================================
  describe("initial state", () => {
    it("should have correct initial dashboard state", () => {
      expect(store.dashboard).toEqual({
        todaysDeliveries: 0,
        pending: 0,
        inProgress: 0,
        completed: 0,
        issues: 0,
        recentDeliveries: [],
      });
    });

    it("should have empty deliveries array", () => {
      expect(store.deliveries).toEqual([]);
    });

    it("should have default pagination", () => {
      expect(store.pagination).toEqual({
        currentPage: 1,
        lastPage: 1,
        perPage: 15,
        total: 0,
      });
    });

    it("should have default filters", () => {
      expect(store.filters).toEqual({
        status: "",
        staff_id: "",
        date_from: "",
        date_to: "",
        search: "",
      });
    });

    it("should have empty zones array", () => {
      expect(store.zones).toEqual([]);
    });

    it("should have default settings", () => {
      expect(store.settings).toEqual({
        free_delivery_threshold: 100,
        per_km_rate: 0.15,
        base_delivery_fee: 9.95,
        max_delivery_distance: 50,
      });
    });

    it("should have all loading states as false", () => {
      expect(store.loading.dashboard).toBe(false);
      expect(store.loading.deliveries).toBe(false);
      expect(store.loading.zones).toBe(false);
      expect(store.loading.settings).toBe(false);
      expect(store.loading.action).toBe(false);
    });

    it("should have null error", () => {
      expect(store.error).toBeNull();
    });
  });

  // ===========================================================================
  // GETTER TESTS
  // ===========================================================================
  describe("getters", () => {
    it("hasDeliveries returns true when deliveries exist", () => {
      store.deliveries = [{ id: 1 }];
      expect(store.hasDeliveries).toBe(true);
    });

    it("hasDeliveries returns false when no deliveries", () => {
      store.deliveries = [];
      expect(store.hasDeliveries).toBe(false);
    });

    it("hasZones returns true when zones exist", () => {
      store.zones = [{ id: 1, name: "Local" }];
      expect(store.hasZones).toBe(true);
    });

    it("activeZones filters only active zones", () => {
      store.zones = [
        { id: 1, name: "Active Zone", is_active: true },
        { id: 2, name: "Inactive Zone", is_active: false },
      ];
      expect(store.activeZones).toHaveLength(1);
      expect(store.activeZones[0].name).toBe("Active Zone");
    });

    it("pendingDeliveries filters by pending status", () => {
      store.deliveries = [
        { id: 1, status: "pending" },
        { id: 2, status: "delivered" },
        { id: 3, status: "pending" },
      ];
      expect(store.pendingDeliveries).toHaveLength(2);
    });

    it("inProgressDeliveries filters out_for_delivery status", () => {
      store.deliveries = [
        { id: 1, status: "assigned" },
        { id: 2, status: "out_for_delivery" },
        { id: 3, status: "delivered" },
      ];
      expect(store.inProgressDeliveries).toHaveLength(1);
      expect(store.inProgressDeliveries[0].id).toBe(2);
    });

    it("deliveriesWithIssues filters by has_issue", () => {
      store.deliveries = [
        { id: 1, has_issue: true },
        { id: 2, has_issue: false },
      ];
      expect(store.deliveriesWithIssues).toHaveLength(1);
    });

    it("hasActiveFilters detects active filters", () => {
      expect(store.hasActiveFilters).toBe(false);
      store.filters.status = "pending";
      expect(store.hasActiveFilters).toBe(true);
    });
  });

  // ===========================================================================
  // ACTION TESTS - DASHBOARD
  // ===========================================================================
  describe("fetchDashboard", () => {
    it("should fetch dashboard data successfully", async () => {
      const mockData = {
        todays_deliveries: 5,
        pending: 2,
        in_progress: 1,
        completed: 2,
        issues: 0,
        recent_deliveries: [{ id: 1, order_number: "ORD-001" }],
      };

      adminDashboard.getDeliveryDashboard.mockResolvedValue({
        data: mockData,
      });

      await store.fetchDashboard();

      expect(adminDashboard.getDeliveryDashboard).toHaveBeenCalled();
      expect(store.dashboard.todaysDeliveries).toBe(5);
      expect(store.dashboard.pending).toBe(2);
      expect(store.dashboard.recentDeliveries).toHaveLength(1);
      expect(store.loading.dashboard).toBe(false);
    });

    it("should handle dashboard fetch error", async () => {
      adminDashboard.getDeliveryDashboard.mockRejectedValue(
        new Error("Network error")
      );

      await store.fetchDashboard();

      expect(store.error).toBe("Network error");
      expect(store.loading.dashboard).toBe(false);
    });
  });

  // ===========================================================================
  // ACTION TESTS - DELIVERIES
  // ===========================================================================
  describe("fetchDeliveries", () => {
    it("should fetch deliveries with pagination", async () => {
      const mockResponse = {
        data: {
          data: [
            { id: 1, order_number: "ORD-001" },
            { id: 2, order_number: "ORD-002" },
          ],
          meta: {
            current_page: 1,
            last_page: 3,
            per_page: 15,
            total: 45,
          },
        },
      };

      adminDashboard.getDeliveries.mockResolvedValue(mockResponse);

      await store.fetchDeliveries(1);

      expect(adminDashboard.getDeliveries).toHaveBeenCalledWith(
        expect.objectContaining({ page: 1 })
      );
      expect(store.deliveries).toHaveLength(2);
      expect(store.pagination.total).toBe(45);
      expect(store.pagination.lastPage).toBe(3);
    });

    it("should apply filters when fetching", async () => {
      store.filters.status = "pending";
      store.filters.search = "test";

      adminDashboard.getDeliveries.mockResolvedValue({
        data: {
          data: [],
          meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
        },
      });

      await store.fetchDeliveries(1);

      expect(adminDashboard.getDeliveries).toHaveBeenCalledWith(
        expect.objectContaining({
          status: "pending",
          search: "test",
        })
      );
    });

    it("should handle fetch error", async () => {
      adminDashboard.getDeliveries.mockRejectedValue(new Error("Error"));

      await store.fetchDeliveries(1);

      expect(store.error).toBe("Error");
      expect(store.loading.deliveries).toBe(false);
    });
  });

  describe("fetchDelivery", () => {
    it("should fetch single delivery details", async () => {
      const mockDelivery = {
        id: 1,
        order_number: "ORD-001",
        customer_name: "John Doe",
        address: "123 Main St",
      };

      adminDashboard.getDelivery.mockResolvedValue({
        data: mockDelivery,
      });

      await store.fetchDelivery(1);

      expect(adminDashboard.getDelivery).toHaveBeenCalledWith(1);
      expect(store.currentDelivery).toEqual(mockDelivery);
    });
  });

  // ===========================================================================
  // ACTION TESTS - ASSIGNMENT
  // ===========================================================================
  describe("assignDelivery", () => {
    it("should assign delivery to staff", async () => {
      adminDashboard.assignDelivery.mockResolvedValue({
        data: { id: 1, assigned_staff_id: 5 },
      });

      const result = await store.assignDelivery(1, 5);

      expect(adminDashboard.assignDelivery).toHaveBeenCalledWith(1, {
        staff_id: 5,
      });
      expect(result).toBeDefined();
    });

    it("should handle assignment error", async () => {
      adminDashboard.assignDelivery.mockRejectedValue(
        new Error("Staff unavailable")
      );

      try {
        await store.assignDelivery(1, 5);
      } catch (error) {
        expect(store.error).toBe("Staff unavailable");
      }
    });
  });

  describe("reassignDelivery", () => {
    it("should reassign with reason", async () => {
      adminDashboard.assignDelivery.mockResolvedValue({
        data: { id: 1, assigned_staff_id: 6 },
      });

      const result = await store.reassignDelivery(1, 6, "Staff sick");

      expect(adminDashboard.assignDelivery).toHaveBeenCalledWith(1, {
        staff_id: 6,
        reason: "Staff sick",
      });
      expect(result).toBeDefined();
    });
  });

  // ===========================================================================
  // ACTION TESTS - PROOF OF DELIVERY
  // ===========================================================================
  describe("fetchProofOfDelivery", () => {
    it("should fetch POD data", async () => {
      const mockPod = {
        signature_url: "https://example.com/sig.png",
        photo_url: "https://example.com/photo.jpg",
        receiver_name: "John Smith",
        delivered_at: "2024-01-15T10:30:00Z",
      };

      adminDashboard.getDeliveryPod.mockResolvedValue({
        data: mockPod,
      });

      await store.fetchProofOfDelivery(1);

      expect(adminDashboard.getDeliveryPod).toHaveBeenCalledWith(1);
      expect(store.proofOfDelivery).toEqual(mockPod);
    });
  });

  describe("resolveIssue", () => {
    it("should resolve delivery issue", async () => {
      adminDashboard.resolveDeliveryIssue.mockResolvedValue({
        data: { id: 1, has_issue: false },
      });

      const result = await store.resolveIssue(
        1,
        "Customer rescheduled",
        "resolved"
      );

      expect(adminDashboard.resolveDeliveryIssue).toHaveBeenCalledWith(1, {
        resolution: "Customer rescheduled",
        status: "resolved",
      });
      expect(result).toBeDefined();
    });
  });

  // ===========================================================================
  // ACTION TESTS - ZONES
  // ===========================================================================
  describe("fetchZones", () => {
    it("should fetch all zones", async () => {
      const mockZones = [
        {
          id: 1,
          name: "Engadine Local",
          postcodes: ["2233", "2234"],
          is_active: true,
        },
        {
          id: 2,
          name: "Extended Area",
          postcodes: ["2230", "2231"],
          is_active: true,
        },
      ];

      adminDashboard.getDeliveryZones.mockResolvedValue({
        data: mockZones,
      });

      await store.fetchZones();

      expect(adminDashboard.getDeliveryZones).toHaveBeenCalled();
      expect(store.zones).toHaveLength(2);
      expect(store.zones[0].name).toBe("Engadine Local");
    });
  });

  describe("createZone", () => {
    it("should create a new zone", async () => {
      const newZone = {
        name: "New Zone",
        postcodes: ["2235", "2236"],
        min_order: 100,
        delivery_fee: 0,
        is_active: true,
      };

      adminDashboard.createDeliveryZone.mockResolvedValue({
        data: { id: 3, ...newZone },
      });

      const result = await store.createZone(newZone);

      expect(adminDashboard.createDeliveryZone).toHaveBeenCalledWith(newZone);
      expect(result).toBeDefined();
      expect(store.zones).toContainEqual(expect.objectContaining({ id: 3 }));
    });

    it("should handle creation error", async () => {
      adminDashboard.createDeliveryZone.mockRejectedValue(
        new Error("Validation error")
      );

      try {
        await store.createZone({ name: "" });
      } catch (error) {
        expect(store.error).toBe("Validation error");
      }
    });
  });

  describe("updateZone", () => {
    it("should update existing zone", async () => {
      store.zones = [
        { id: 1, name: "Old Name", postcodes: ["2233"], is_active: true },
      ];

      const updatedZone = {
        id: 1,
        name: "New Name",
        postcodes: ["2233", "2234"],
        is_active: true,
      };

      adminDashboard.updateDeliveryZone.mockResolvedValue({
        data: updatedZone,
      });

      const result = await store.updateZone(1, updatedZone);

      expect(adminDashboard.updateDeliveryZone).toHaveBeenCalledWith(
        1,
        updatedZone
      );
      expect(result).toBeDefined();
      expect(store.zones[0].name).toBe("New Name");
    });
  });

  describe("deleteZone", () => {
    it("should delete zone", async () => {
      store.zones = [
        { id: 1, name: "Zone 1" },
        { id: 2, name: "Zone 2" },
      ];

      adminDashboard.deleteDeliveryZone.mockResolvedValue({
        data: { success: true },
      });

      await store.deleteZone(1);

      expect(adminDashboard.deleteDeliveryZone).toHaveBeenCalledWith(1);
      expect(store.zones).toHaveLength(1);
      expect(store.zones[0].id).toBe(2);
    });
  });

  // ===========================================================================
  // ACTION TESTS - SETTINGS
  // ===========================================================================
  describe("fetchSettings", () => {
    it("should fetch delivery settings", async () => {
      const mockSettings = {
        free_delivery_threshold: 150,
        per_km_rate: 0.2,
        base_delivery_fee: 12.95,
        max_delivery_distance: 75,
      };

      adminDashboard.getDeliverySettings.mockResolvedValue({
        data: mockSettings,
      });

      await store.fetchSettings();

      expect(adminDashboard.getDeliverySettings).toHaveBeenCalled();
      expect(store.settings.free_delivery_threshold).toBe(150);
      expect(store.settings.per_km_rate).toBe(0.2);
    });
  });

  describe("updateSettings", () => {
    it("should update delivery settings", async () => {
      const newSettings = {
        free_delivery_threshold: 200,
        per_km_rate: 0.25,
        base_delivery_fee: 14.95,
        max_delivery_distance: 100,
      };

      adminDashboard.updateDeliverySettings.mockResolvedValue({
        data: newSettings,
      });

      const result = await store.updateSettings(newSettings);

      expect(adminDashboard.updateDeliverySettings).toHaveBeenCalledWith(
        newSettings
      );
      expect(result).toBeDefined();
      expect(store.settings).toEqual(newSettings);
    });
  });

  // ===========================================================================
  // ACTION TESTS - MAP & REPORTS
  // ===========================================================================
  describe("fetchMapData", () => {
    it("should fetch map data with filters", async () => {
      const mockMapData = {
        deliveries: [
          { id: 1, lat: -34.0, lng: 151.0, status: "out_for_delivery" },
        ],
        zones: [{ id: 1, name: "Local", boundary: [] }],
      };

      adminDashboard.getDeliveryMapData.mockResolvedValue({
        data: mockMapData,
      });

      await store.fetchMapData({ status: "out_for_delivery" });

      expect(adminDashboard.getDeliveryMapData).toHaveBeenCalled();
      expect(store.mapData.deliveries).toBeDefined();
    });
  });

  describe("fetchReport", () => {
    it("should fetch delivery report", async () => {
      const mockReport = {
        total_deliveries: 150,
        on_time_rate: 95.5,
        average_delivery_time: 45,
        by_zone: [],
        by_staff: [],
      };

      adminDashboard.getDeliveryReport.mockResolvedValue({
        data: mockReport,
      });

      await store.fetchReport({
        date_from: "2024-01-01",
        date_to: "2024-01-31",
      });

      expect(adminDashboard.getDeliveryReport).toHaveBeenCalled();
      expect(store.report).toEqual(mockReport);
    });
  });

  describe("exportDeliveries", () => {
    it("should export deliveries data", async () => {
      const mockData = { deliveries: [] };

      adminDashboard.exportDeliveries.mockResolvedValue({
        data: mockData,
      });

      await store.exportDeliveries({ format: "json" });

      expect(adminDashboard.exportDeliveries).toHaveBeenCalledWith(
        expect.objectContaining({ format: "json" })
      );
    });
  });

  // ===========================================================================
  // ACTION TESTS - STAFF
  // ===========================================================================
  describe("fetchStaffList", () => {
    it("should fetch delivery staff list", async () => {
      const mockStaff = [
        { id: 1, name: "John Driver", role: "staff" },
        { id: 2, name: "Jane Driver", role: "staff" },
      ];

      adminDashboard.getStaff.mockResolvedValue({
        data: mockStaff,
      });

      await store.fetchStaffList();

      expect(adminDashboard.getStaff).toHaveBeenCalled();
      expect(store.staffList).toHaveLength(2);
    });
  });

  // ===========================================================================
  // ACTION TESTS - UTILITY
  // ===========================================================================
  describe("setFilters", () => {
    it("should merge new filters with existing", () => {
      store.filters.status = "pending";
      store.setFilters({ search: "test", date_from: "2024-01-01" });

      expect(store.filters.status).toBe("pending");
      expect(store.filters.search).toBe("test");
      expect(store.filters.date_from).toBe("2024-01-01");
    });
  });

  describe("resetFilters", () => {
    it("should reset all filters to defaults", () => {
      store.filters = {
        search: "test",
        status: "pending",
        staff_id: "5",
        date_from: "2024-01-01",
        date_to: "2024-01-31",
      };

      store.resetFilters();

      expect(store.filters.search).toBe("");
      expect(store.filters.status).toBe("");
      expect(store.filters.staff_id).toBe("");
      expect(store.filters.date_from).toBe("");
      expect(store.filters.date_to).toBe("");
    });
  });

  describe("clearCurrentDelivery", () => {
    it("should clear current delivery and POD", () => {
      store.currentDelivery = { id: 1, order_number: "ORD-001" };
      store.proofOfDelivery = { signature_url: "test" };

      store.clearCurrentDelivery();

      expect(store.currentDelivery).toBeNull();
      expect(store.proofOfDelivery).toBeNull();
    });
  });

  describe("clearError", () => {
    it("should clear error state", () => {
      store.error = "Some error message";

      store.clearError();

      expect(store.error).toBeNull();
    });
  });
});
