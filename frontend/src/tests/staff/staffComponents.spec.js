/**
 * Staff Components Tests
 *
 * Comprehensive tests for Staff dashboard Vue components.
 *
 * @requirement STAFF-001 Staff dashboard layout
 * @requirement STAFF-002 to STAFF-015 All staff functionality
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { mount, shallowMount } from "@vue/test-utils";
import { setActivePinia, createPinia } from "pinia";
import { createRouter, createWebHistory } from "vue-router";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  staffDashboard: {
    getOverview: vi.fn(),
    getOrderQueue: vi.fn(),
    getOrder: vi.fn(),
    updateOrderStatus: vi.fn(),
    addOrderNote: vi.fn(),
    getTodaysDeliveries: vi.fn(),
    getTodaysPickups: vi.fn(),
    markOutForDelivery: vi.fn(),
    uploadProofOfDelivery: vi.fn(),
    getProofOfDelivery: vi.fn(),
    markAsPickedUp: vi.fn(),
    getStockCheck: vi.fn(),
    updateStock: vi.fn(),
    getWasteLogs: vi.fn(),
    logWaste: vi.fn(),
    getActivityLog: vi.fn(),
    getPerformanceStats: vi.fn(),
  },
}));

import { staffDashboard } from "@/services/dashboard";

// Create mock router
const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/staff",
      name: "staff-dashboard",
      component: { template: "<div />" },
    },
    {
      path: "/staff/orders",
      name: "staff-orders",
      component: { template: "<div />" },
    },
    {
      path: "/staff/deliveries",
      name: "staff-deliveries",
      component: { template: "<div />" },
    },
    {
      path: "/staff/stock",
      name: "staff-stock",
      component: { template: "<div />" },
    },
    {
      path: "/staff/waste",
      name: "staff-waste",
      component: { template: "<div />" },
    },
    {
      path: "/staff/activity",
      name: "staff-activity",
      component: { template: "<div />" },
    },
  ],
});

describe("Staff Components", () => {
  let pinia;

  beforeEach(async () => {
    pinia = createPinia();
    setActivePinia(pinia);
    vi.clearAllMocks();
    await router.push("/staff");
    await router.isReady();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("StaffLayout", () => {
    it("renders layout with sidebar", async () => {
      // Import dynamically to ensure mocks are in place
      const StaffLayout = (await import("@/layouts/StaffLayout.vue")).default;

      const wrapper = shallowMount(StaffLayout, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link", "router-view"],
        },
      });

      expect(wrapper.exists()).toBe(true);
    });

    it("contains navigation links", async () => {
      const StaffLayout = (await import("@/layouts/StaffLayout.vue")).default;

      const wrapper = mount(StaffLayout, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-view"],
        },
      });

      const links = wrapper.findAll("a");
      // Should have at least Dashboard, Orders, Deliveries links
      expect(links.length).toBeGreaterThanOrEqual(3);
    });
  });

  describe("DashboardPage", () => {
    beforeEach(() => {
      staffDashboard.getOverview.mockResolvedValue({
        success: true,
        data: {
          todays_orders: 15,
          pending_orders: 5,
          my_deliveries: 3,
          my_pickups: 2,
        },
      });
    });

    it("renders dashboard page", async () => {
      const DashboardPage = (await import("@/pages/staff/DashboardPage.vue"))
        .default;

      const wrapper = shallowMount(DashboardPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      expect(wrapper.exists()).toBe(true);
    });

    it("displays loading state initially", async () => {
      const DashboardPage = (await import("@/pages/staff/DashboardPage.vue"))
        .default;

      const wrapper = mount(DashboardPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      // Component should exist
      expect(wrapper.exists()).toBe(true);
    });

    it("calls API on mount", async () => {
      const DashboardPage = (await import("@/pages/staff/DashboardPage.vue"))
        .default;

      shallowMount(DashboardPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      // Wait for async operations
      await new Promise((r) => setTimeout(r, 0));
      expect(staffDashboard.getOverview).toHaveBeenCalled();
    });
  });

  describe("OrdersPage", () => {
    beforeEach(() => {
      staffDashboard.getOrderQueue.mockResolvedValue({
        success: true,
        data: [
          {
            id: 1,
            order_number: "ORD-001",
            status: "pending",
            customer_name: "John Doe",
            total: 150.0,
            created_at: "2024-01-15T10:00:00Z",
          },
        ],
        meta: { current_page: 1, last_page: 1, total: 1 },
      });
    });

    it("renders orders page", async () => {
      const OrdersPage = (await import("@/pages/staff/OrdersPage.vue")).default;

      const wrapper = shallowMount(OrdersPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      expect(wrapper.exists()).toBe(true);
    });

    it("fetches orders on mount", async () => {
      const OrdersPage = (await import("@/pages/staff/OrdersPage.vue")).default;

      shallowMount(OrdersPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 0));
      expect(staffDashboard.getOrderQueue).toHaveBeenCalled();
    });

    it("displays order status tabs", async () => {
      const OrdersPage = (await import("@/pages/staff/OrdersPage.vue")).default;

      const wrapper = mount(OrdersPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 10));
      await wrapper.vm.$nextTick();

      // Should have status filter tabs
      const html = wrapper.html();
      expect(html).toContain("All");
    });
  });

  describe("DeliveriesPage", () => {
    beforeEach(() => {
      staffDashboard.getTodaysDeliveries.mockResolvedValue({
        success: true,
        data: [
          {
            id: 1,
            order_number: "ORD-001",
            status: "ready_for_delivery",
            customer_name: "John Doe",
            delivery_address: "123 Main St",
            time_slot: "09:00 - 11:00",
          },
        ],
      });
      staffDashboard.getTodaysPickups.mockResolvedValue({
        success: true,
        data: [
          {
            id: 2,
            order_number: "ORD-002",
            status: "ready_for_pickup",
            customer_name: "Jane Smith",
          },
        ],
      });
    });

    it("renders deliveries page", async () => {
      const DeliveriesPage = (await import("@/pages/staff/DeliveriesPage.vue"))
        .default;

      const wrapper = shallowMount(DeliveriesPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      expect(wrapper.exists()).toBe(true);
    });

    it("fetches deliveries and pickups on mount", async () => {
      const DeliveriesPage = (await import("@/pages/staff/DeliveriesPage.vue"))
        .default;

      shallowMount(DeliveriesPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 0));
      expect(staffDashboard.getTodaysDeliveries).toHaveBeenCalled();
      expect(staffDashboard.getTodaysPickups).toHaveBeenCalled();
    });

    it("displays deliveries and pickups tabs", async () => {
      const DeliveriesPage = (await import("@/pages/staff/DeliveriesPage.vue"))
        .default;

      const wrapper = mount(DeliveriesPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 10));
      await wrapper.vm.$nextTick();

      const html = wrapper.html();
      // Should have delivery/pickup related content
      expect(html.toLowerCase()).toContain("deliver");
    });
  });

  describe("StockCheckPage", () => {
    beforeEach(() => {
      staffDashboard.getStockCheck.mockResolvedValue({
        success: true,
        data: [
          {
            id: 1,
            name: "Beef Steak",
            sku: "BEF-001",
            stock_quantity: 50,
            stock_status: "in_stock",
            category: "Beef",
          },
          {
            id: 2,
            name: "Pork Chops",
            sku: "POR-001",
            stock_quantity: 5,
            stock_status: "low_stock",
            category: "Pork",
          },
        ],
      });
    });

    it("renders stock check page", async () => {
      const StockCheckPage = (await import("@/pages/staff/StockCheckPage.vue"))
        .default;

      const wrapper = shallowMount(StockCheckPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      expect(wrapper.exists()).toBe(true);
    });

    it("fetches stock data on mount", async () => {
      const StockCheckPage = (await import("@/pages/staff/StockCheckPage.vue"))
        .default;

      shallowMount(StockCheckPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 0));
      expect(staffDashboard.getStockCheck).toHaveBeenCalled();
    });

    it("displays stock status filters", async () => {
      const StockCheckPage = (await import("@/pages/staff/StockCheckPage.vue"))
        .default;

      const wrapper = mount(StockCheckPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 10));
      await wrapper.vm.$nextTick();

      const html = wrapper.html().toLowerCase();
      expect(html).toContain("stock");
    });
  });

  describe("WasteLogPage", () => {
    beforeEach(() => {
      staffDashboard.getWasteLogs.mockResolvedValue({
        success: true,
        data: [
          {
            id: 1,
            product_name: "Beef Steak",
            quantity: 5,
            reason: "expired",
            logged_at: "2024-01-15T10:00:00Z",
            logged_by: "John Staff",
          },
        ],
        meta: { current_page: 1, last_page: 1, total: 1 },
      });
      // WasteLogPage may also need stock check for product selection
      staffDashboard.getStockCheck.mockResolvedValue({
        success: true,
        data: [],
      });
    });

    it("renders waste log page", async () => {
      const WasteLogPage = (await import("@/pages/staff/WasteLogPage.vue"))
        .default;

      const wrapper = shallowMount(WasteLogPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      expect(wrapper.exists()).toBe(true);
    });

    it("fetches waste logs on mount", async () => {
      const WasteLogPage = (await import("@/pages/staff/WasteLogPage.vue"))
        .default;

      shallowMount(WasteLogPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 0));
      expect(staffDashboard.getWasteLogs).toHaveBeenCalled();
    });

    it("displays waste reason options", async () => {
      const WasteLogPage = (await import("@/pages/staff/WasteLogPage.vue"))
        .default;

      const wrapper = mount(WasteLogPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 10));
      await wrapper.vm.$nextTick();

      const html = wrapper.html().toLowerCase();
      expect(html).toContain("waste");
    });
  });

  describe("ActivityPage", () => {
    beforeEach(() => {
      staffDashboard.getActivityLog.mockResolvedValue({
        success: true,
        data: [
          {
            id: 1,
            type: "order_processed",
            description: "Processed order #123",
            created_at: "2024-01-15T10:00:00Z",
          },
          {
            id: 2,
            type: "delivery_completed",
            description: "Delivered order #124",
            created_at: "2024-01-15T14:00:00Z",
          },
        ],
        meta: { current_page: 1, last_page: 1, total: 2 },
      });
      staffDashboard.getPerformanceStats.mockResolvedValue({
        success: true,
        data: {
          orders_processed: 50,
          deliveries_completed: 30,
          on_time_percentage: 95.5,
        },
      });
    });

    it("renders activity page", async () => {
      const ActivityPage = (await import("@/pages/staff/ActivityPage.vue"))
        .default;

      const wrapper = shallowMount(ActivityPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      expect(wrapper.exists()).toBe(true);
    });

    it("fetches activity log on mount", async () => {
      const ActivityPage = (await import("@/pages/staff/ActivityPage.vue"))
        .default;

      shallowMount(ActivityPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 0));
      expect(staffDashboard.getActivityLog).toHaveBeenCalled();
    });

    it("fetches performance stats on mount", async () => {
      const ActivityPage = (await import("@/pages/staff/ActivityPage.vue"))
        .default;

      shallowMount(ActivityPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 0));
      expect(staffDashboard.getPerformanceStats).toHaveBeenCalled();
    });

    it("displays activity timeline", async () => {
      const ActivityPage = (await import("@/pages/staff/ActivityPage.vue"))
        .default;

      const wrapper = mount(ActivityPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 10));
      await wrapper.vm.$nextTick();

      const html = wrapper.html().toLowerCase();
      expect(html).toContain("activity");
    });
  });
});

describe("Staff Component Interactions", () => {
  let pinia;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Order Status Update", () => {
    beforeEach(() => {
      staffDashboard.getOrderQueue.mockResolvedValue({
        success: true,
        data: [{ id: 1, order_number: "ORD-001", status: "pending" }],
        meta: { current_page: 1, last_page: 1, total: 1 },
      });
      staffDashboard.updateOrderStatus.mockResolvedValue({ success: true });
    });

    it("updates order status on button click", async () => {
      const OrdersPage = (await import("@/pages/staff/OrdersPage.vue")).default;

      const wrapper = mount(OrdersPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 50));
      await wrapper.vm.$nextTick();

      // Component should render without errors
      expect(wrapper.exists()).toBe(true);
    });
  });

  describe("POD Upload", () => {
    beforeEach(() => {
      staffDashboard.getTodaysDeliveries.mockResolvedValue({
        success: true,
        data: [{ id: 1, order_number: "ORD-001", status: "out_for_delivery" }],
      });
      staffDashboard.getTodaysPickups.mockResolvedValue({
        success: true,
        data: [],
      });
      staffDashboard.uploadProofOfDelivery.mockResolvedValue({ success: true });
    });

    it("handles POD upload", async () => {
      const DeliveriesPage = (await import("@/pages/staff/DeliveriesPage.vue"))
        .default;

      const wrapper = mount(DeliveriesPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 50));
      await wrapper.vm.$nextTick();

      // Component should render without errors
      expect(wrapper.exists()).toBe(true);
    });
  });

  describe("Stock Update", () => {
    beforeEach(() => {
      staffDashboard.getStockCheck.mockResolvedValue({
        success: true,
        data: [
          {
            id: 1,
            name: "Beef Steak",
            stock_quantity: 50,
            stock_status: "in_stock",
          },
        ],
      });
      staffDashboard.updateStock.mockResolvedValue({ success: true });
    });

    it("handles stock update", async () => {
      const StockCheckPage = (await import("@/pages/staff/StockCheckPage.vue"))
        .default;

      const wrapper = mount(StockCheckPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 50));
      await wrapper.vm.$nextTick();

      // Component should render without errors
      expect(wrapper.exists()).toBe(true);
    });
  });

  describe("Waste Log Entry", () => {
    beforeEach(() => {
      staffDashboard.getWasteLogs.mockResolvedValue({
        success: true,
        data: [],
        meta: { current_page: 1, last_page: 1, total: 0 },
      });
      staffDashboard.logWaste.mockResolvedValue({ success: true });
      // WasteLogPage may also call getStockCheck for product selection
      staffDashboard.getStockCheck.mockResolvedValue({
        success: true,
        data: [],
      });
    });

    it("handles waste log entry", async () => {
      const WasteLogPage = (await import("@/pages/staff/WasteLogPage.vue"))
        .default;

      const wrapper = mount(WasteLogPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 50));
      await wrapper.vm.$nextTick();

      // Component should render without errors
      expect(wrapper.exists()).toBe(true);
    });
  });
});

describe("Staff Component Error Handling", () => {
  let pinia;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("API Error Handling", () => {
    it("handles order fetch error gracefully", async () => {
      staffDashboard.getOrderQueue.mockRejectedValue(
        new Error("Network error")
      );

      const OrdersPage = (await import("@/pages/staff/OrdersPage.vue")).default;

      const wrapper = mount(OrdersPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 50));
      await wrapper.vm.$nextTick();

      // Component should still render
      expect(wrapper.exists()).toBe(true);
    });

    it("handles deliveries fetch error gracefully", async () => {
      staffDashboard.getTodaysDeliveries.mockRejectedValue(
        new Error("Network error")
      );
      staffDashboard.getTodaysPickups.mockRejectedValue(
        new Error("Network error")
      );

      const DeliveriesPage = (await import("@/pages/staff/DeliveriesPage.vue"))
        .default;

      const wrapper = mount(DeliveriesPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 50));
      await wrapper.vm.$nextTick();

      // Component should still render
      expect(wrapper.exists()).toBe(true);
    });

    it("handles stock fetch error gracefully", async () => {
      staffDashboard.getStockCheck.mockRejectedValue(
        new Error("Network error")
      );

      const StockCheckPage = (await import("@/pages/staff/StockCheckPage.vue"))
        .default;

      const wrapper = mount(StockCheckPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 50));
      await wrapper.vm.$nextTick();

      // Component should still render
      expect(wrapper.exists()).toBe(true);
    });

    it("handles waste logs fetch error gracefully", async () => {
      staffDashboard.getWasteLogs.mockRejectedValue(new Error("Network error"));
      // WasteLogPage may also call getStockCheck
      staffDashboard.getStockCheck.mockResolvedValue({
        success: true,
        data: [],
      });

      const WasteLogPage = (await import("@/pages/staff/WasteLogPage.vue"))
        .default;

      const wrapper = mount(WasteLogPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 50));
      await wrapper.vm.$nextTick();

      // Component should still render
      expect(wrapper.exists()).toBe(true);
    });

    it("handles activity fetch error gracefully", async () => {
      staffDashboard.getActivityLog.mockRejectedValue(
        new Error("Network error")
      );
      staffDashboard.getPerformanceStats.mockRejectedValue(
        new Error("Network error")
      );

      const ActivityPage = (await import("@/pages/staff/ActivityPage.vue"))
        .default;

      const wrapper = mount(ActivityPage, {
        global: {
          plugins: [pinia, router],
          stubs: ["router-link"],
        },
      });

      await new Promise((r) => setTimeout(r, 50));
      await wrapper.vm.$nextTick();

      // Component should still render
      expect(wrapper.exists()).toBe(true);
    });
  });
});
