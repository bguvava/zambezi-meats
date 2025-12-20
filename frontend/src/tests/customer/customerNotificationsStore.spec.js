/**
 * Customer Notifications Store Tests
 *
 * Comprehensive tests for the customer notifications Pinia store.
 *
 * @requirement CUST-014 Notifications page
 * @requirement CUST-015 Mark notifications as read
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useCustomerNotificationsStore } from "@/stores/customerNotifications";

// Mock API
vi.mock("@/services/api", () => ({
  default: {
    get: vi.fn(),
    put: vi.fn(),
  },
}));

import api from "@/services/api";

describe("Customer Notifications Store", () => {
  let store;

  const mockNotifications = [
    {
      id: 1,
      title: "Order Shipped",
      message: "Your order #12345 has been shipped",
      type: "order_status",
      is_read: false,
      created_at: "2024-01-20T10:00:00Z",
    },
    {
      id: 2,
      title: "Order Delivered",
      message: "Your order #12344 has been delivered",
      type: "order_delivered",
      is_read: true,
      read_at: "2024-01-19T15:00:00Z",
      created_at: "2024-01-19T14:00:00Z",
    },
    {
      id: 3,
      title: "Special Offer",
      message: "20% off all beef products this weekend",
      type: "promotion",
      is_read: false,
      created_at: "2024-01-18T09:00:00Z",
    },
    {
      id: 4,
      title: "Ticket Response",
      message: "Support team has replied to your ticket",
      type: "support",
      is_read: true,
      read_at: "2024-01-17T12:00:00Z",
      created_at: "2024-01-17T11:00:00Z",
    },
  ];

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useCustomerNotificationsStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty notifications", () => {
      expect(store.notifications).toEqual([]);
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });

    it("starts with isUpdating false", () => {
      expect(store.isUpdating).toBe(false);
    });
  });

  describe("Constants", () => {
    it("has NOTIFICATION_TYPES defined", () => {
      expect(store.NOTIFICATION_TYPES).toBeDefined();
      expect(store.NOTIFICATION_TYPES.order_status).toBeDefined();
      expect(store.NOTIFICATION_TYPES.promotion).toBeDefined();
    });
  });

  describe("Getters", () => {
    beforeEach(() => {
      store.notifications = mockNotifications;
    });

    it("returns correct unreadCount", () => {
      expect(store.unreadCount).toBe(2);
    });

    it("returns unread notifications", () => {
      expect(store.unreadNotifications).toHaveLength(2);
      expect(store.unreadNotifications[0].id).toBe(1);
      expect(store.unreadNotifications[1].id).toBe(3);
    });

    it("returns read notifications", () => {
      expect(store.readNotifications).toHaveLength(2);
      expect(store.readNotifications[0].id).toBe(2);
    });

    it("hasUnread returns true when unread exist", () => {
      expect(store.hasUnread).toBe(true);
    });

    it("hasUnread returns false when all read", () => {
      store.notifications = mockNotifications.map((n) => ({
        ...n,
        is_read: true,
      }));
      expect(store.hasUnread).toBe(false);
    });

    it("returns correct notificationCount", () => {
      expect(store.notificationCount).toBe(4);
    });
  });

  describe("fetchNotifications", () => {
    it("sets isLoading to true during fetch", async () => {
      api.get.mockImplementation(() => new Promise(() => {}));
      store.fetchNotifications();
      expect(store.isLoading).toBe(true);
    });

    it("fetches notifications successfully", async () => {
      api.get.mockResolvedValue({
        data: {
          success: true,
          notifications: mockNotifications,
          unread_count: 2,
        },
      });

      const result = await store.fetchNotifications();

      expect(api.get).toHaveBeenCalledWith("/customer/notifications", {
        params: {},
      });
      expect(result.success).toBe(true);
      expect(store.notifications).toEqual(mockNotifications);
      expect(result.unreadCount).toBe(2);
    });

    it("fetches unread only notifications", async () => {
      api.get.mockResolvedValue({
        data: { success: true, notifications: mockNotifications.slice(0, 1) },
      });

      await store.fetchNotifications({ unreadOnly: true });

      expect(api.get).toHaveBeenCalledWith("/customer/notifications", {
        params: { unread_only: true },
      });
    });

    it("handles fetch error", async () => {
      api.get.mockRejectedValue({
        response: { data: { message: "Failed to fetch notifications" } },
      });

      const result = await store.fetchNotifications();

      expect(result.success).toBe(false);
      expect(store.error).toBe("Failed to fetch notifications");
    });
  });

  describe("markAsRead", () => {
    beforeEach(() => {
      store.notifications = [...mockNotifications];
    });

    it("sets isUpdating to true during update", async () => {
      api.put.mockImplementation(() => new Promise(() => {}));
      store.markAsRead(1);
      expect(store.isUpdating).toBe(true);
    });

    it("marks notification as read successfully", async () => {
      api.put.mockResolvedValue({
        data: { success: true },
      });

      const result = await store.markAsRead(1);

      expect(api.put).toHaveBeenCalledWith("/customer/notifications/1/read");
      expect(result.success).toBe(true);
      expect(store.notifications.find((n) => n.id === 1).is_read).toBe(true);
    });

    it("handles mark as read error", async () => {
      api.put.mockRejectedValue({
        response: { data: { message: "Notification not found" } },
      });

      const result = await store.markAsRead(999);

      expect(result.success).toBe(false);
      expect(store.error).toBe("Notification not found");
    });
  });

  describe("markAllAsRead", () => {
    beforeEach(() => {
      store.notifications = [...mockNotifications];
    });

    it("marks all notifications as read successfully", async () => {
      api.put.mockResolvedValue({
        data: { success: true, message: "All notifications marked as read" },
      });

      const result = await store.markAllAsRead();

      expect(api.put).toHaveBeenCalledWith("/customer/notifications/read-all");
      expect(result.success).toBe(true);
      expect(store.notifications.every((n) => n.is_read)).toBe(true);
    });

    it("handles mark all as read error", async () => {
      api.put.mockRejectedValue({
        response: { data: { message: "Failed to mark all as read" } },
      });

      const result = await store.markAllAsRead();

      expect(result.success).toBe(false);
      expect(store.error).toBe("Failed to mark all as read");
    });
  });

  describe("getTypeConfig", () => {
    it("returns config for order_status type", () => {
      const config = store.getTypeConfig("order_status");
      expect(config.icon).toBe("Package");
      expect(config.color).toBe("blue");
    });

    it("returns config for promotion type", () => {
      const config = store.getTypeConfig("promotion");
      expect(config.icon).toBe("Tag");
      expect(config.color).toBe("purple");
    });

    it("returns default for unknown type", () => {
      const config = store.getTypeConfig("unknown");
      expect(config.icon).toBe("Bell");
      expect(config.color).toBe("gray");
    });
  });

  describe("formatTime", () => {
    it("returns 'Just now' for recent time", () => {
      const now = new Date().toISOString();
      expect(store.formatTime(now)).toBe("Just now");
    });

    it("returns minutes ago for times within an hour", () => {
      const thirtyMinsAgo = new Date(Date.now() - 30 * 60 * 1000).toISOString();
      expect(store.formatTime(thirtyMinsAgo)).toBe("30m ago");
    });

    it("returns hours ago for times within a day", () => {
      const fiveHoursAgo = new Date(
        Date.now() - 5 * 60 * 60 * 1000
      ).toISOString();
      expect(store.formatTime(fiveHoursAgo)).toBe("5h ago");
    });

    it("returns days ago for times within a week", () => {
      const threeDaysAgo = new Date(
        Date.now() - 3 * 24 * 60 * 60 * 1000
      ).toISOString();
      expect(store.formatTime(threeDaysAgo)).toBe("3d ago");
    });

    it("returns date for times older than a week", () => {
      const twoWeeksAgo = new Date(
        Date.now() - 14 * 24 * 60 * 60 * 1000
      ).toISOString();
      const result = store.formatTime(twoWeeksAgo);
      expect(result).toMatch(/\d{1,2} [A-Z][a-z]{2}/);
    });
  });

  describe("clearNotifications", () => {
    it("clears all notifications state", () => {
      store.notifications = mockNotifications;
      store.error = "Error";

      store.clearNotifications();

      expect(store.notifications).toEqual([]);
      expect(store.error).toBeNull();
    });
  });

  describe("clearError", () => {
    it("clears error", () => {
      store.error = "Error";

      store.clearError();

      expect(store.error).toBeNull();
    });
  });
});
