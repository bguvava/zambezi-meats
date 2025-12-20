/**
 * Customer Dashboard Components Tests
 *
 * Basic smoke tests for customer dashboard Vue components.
 * Store functionality is thoroughly tested in dedicated store spec files.
 *
 * @requirement CUST-001 to CUST-023 Customer dashboard features
 */
import { describe, it, expect, vi } from "vitest";

// Test that components can be imported without error
describe("Customer Components Import Tests", () => {
  describe("NotificationsPage", () => {
    it("can be imported", async () => {
      const module = await import("@/pages/customer/NotificationsPage.vue");
      expect(module.default).toBeDefined();
      expect(module.default.__name || module.default.name).toBe(
        "NotificationsPage"
      );
    });
  });

  describe("SupportTicketsPage", () => {
    it("can be imported", async () => {
      const module = await import("@/pages/customer/SupportTicketsPage.vue");
      expect(module.default).toBeDefined();
      expect(module.default.__name || module.default.name).toBe(
        "SupportTicketsPage"
      );
    });
  });

  describe("DashboardPage", () => {
    it("can be imported", async () => {
      const module = await import("@/pages/customer/DashboardPage.vue");
      expect(module.default).toBeDefined();
    });
  });

  describe("OrdersPage", () => {
    it("can be imported", async () => {
      const module = await import("@/pages/customer/OrdersPage.vue");
      expect(module.default).toBeDefined();
    });
  });

  describe("ProfilePage", () => {
    it("can be imported", async () => {
      const module = await import("@/pages/customer/ProfilePage.vue");
      expect(module.default).toBeDefined();
    });
  });

  describe("AddressesPage", () => {
    it("can be imported", async () => {
      const module = await import("@/pages/customer/AddressesPage.vue");
      expect(module.default).toBeDefined();
    });
  });

  describe("WishlistPage", () => {
    it("can be imported", async () => {
      const module = await import("@/pages/customer/WishlistPage.vue");
      expect(module.default).toBeDefined();
    });
  });

  describe("CustomerLayout", () => {
    it("can be imported", async () => {
      const module = await import("@/layouts/CustomerLayout.vue");
      expect(module.default).toBeDefined();
    });
  });
});

describe("Customer Stores Import Tests", () => {
  describe("Profile Store", () => {
    it("can be imported and exports useProfileStore", async () => {
      const module = await import("@/stores/profile");
      expect(module.useProfileStore).toBeDefined();
      expect(typeof module.useProfileStore).toBe("function");
    });
  });

  describe("Address Store", () => {
    it("can be imported and exports useAddressStore", async () => {
      const module = await import("@/stores/address");
      expect(module.useAddressStore).toBeDefined();
      expect(typeof module.useAddressStore).toBe("function");
    });
  });

  describe("Wishlist Store", () => {
    it("can be imported and exports useWishlistStore", async () => {
      const module = await import("@/stores/wishlist");
      expect(module.useWishlistStore).toBeDefined();
      expect(typeof module.useWishlistStore).toBe("function");
    });
  });

  describe("Tickets Store", () => {
    it("can be imported and exports useTicketsStore", async () => {
      const module = await import("@/stores/tickets");
      expect(module.useTicketsStore).toBeDefined();
      expect(typeof module.useTicketsStore).toBe("function");
    });
  });

  describe("Customer Notifications Store", () => {
    it("can be imported and exports useCustomerNotificationsStore", async () => {
      const module = await import("@/stores/customerNotifications");
      expect(module.useCustomerNotificationsStore).toBeDefined();
      expect(typeof module.useCustomerNotificationsStore).toBe("function");
    });
  });
});
