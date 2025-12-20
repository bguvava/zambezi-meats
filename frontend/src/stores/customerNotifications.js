/**
 * Customer Notifications Store
 *
 * Manages customer notifications state and operations.
 *
 * @requirement CUST-014 Notifications page
 * @requirement CUST-015 Mark notifications as read
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/services/api";

export const useCustomerNotificationsStore = defineStore(
  "customerNotifications",
  () => {
    // State
    const notifications = ref([]);
    const isLoading = ref(false);
    const error = ref(null);
    const isUpdating = ref(false);

    // Notification types with icons
    const NOTIFICATION_TYPES = {
      order_status: { icon: "Package", color: "blue" },
      order_delivered: { icon: "CheckCircle", color: "green" },
      promotion: { icon: "Tag", color: "purple" },
      system: { icon: "Bell", color: "gray" },
      support: { icon: "MessageCircle", color: "yellow" },
    };

    // Getters
    const unreadCount = computed(
      () => notifications.value.filter((n) => !n.is_read).length
    );

    const unreadNotifications = computed(() =>
      notifications.value.filter((n) => !n.is_read)
    );

    const readNotifications = computed(() =>
      notifications.value.filter((n) => n.is_read)
    );

    const hasUnread = computed(() => unreadCount.value > 0);

    const notificationCount = computed(() => notifications.value.length);

    // Actions

    /**
     * Fetch notifications
     * @param {Object} options - Query options
     */
    async function fetchNotifications(options = {}) {
      isLoading.value = true;
      error.value = null;

      try {
        const params = {};
        if (options.unreadOnly) params.unread_only = true;

        const response = await api.get("/customer/notifications", { params });

        if (response.data.success) {
          notifications.value = response.data.notifications || [];
        } else {
          notifications.value = response.data || [];
        }

        return {
          success: true,
          notifications: notifications.value,
          unreadCount: response.data.unread_count || unreadCount.value,
        };
      } catch (err) {
        error.value =
          err.response?.data?.message || "Failed to fetch notifications";
        return { success: false, message: error.value };
      } finally {
        isLoading.value = false;
      }
    }

    /**
     * Mark notification as read
     * @param {number} id - Notification ID
     */
    async function markAsRead(id) {
      isUpdating.value = true;
      error.value = null;

      try {
        const response = await api.put(`/customer/notifications/${id}/read`);

        if (response.data.success) {
          const index = notifications.value.findIndex((n) => n.id === id);
          if (index !== -1) {
            notifications.value[index].is_read = true;
            notifications.value[index].read_at = new Date().toISOString();
          }
        }

        return { success: true, message: "Notification marked as read" };
      } catch (err) {
        error.value =
          err.response?.data?.message || "Failed to mark notification as read";
        return { success: false, message: error.value };
      } finally {
        isUpdating.value = false;
      }
    }

    /**
     * Mark all notifications as read
     */
    async function markAllAsRead() {
      isUpdating.value = true;
      error.value = null;

      try {
        const response = await api.put("/customer/notifications/read-all");

        if (response.data.success) {
          const now = new Date().toISOString();
          notifications.value = notifications.value.map((n) => ({
            ...n,
            is_read: true,
            read_at: n.read_at || now,
          }));
        }

        return {
          success: true,
          message: response.data.message || "All notifications marked as read",
        };
      } catch (err) {
        error.value =
          err.response?.data?.message ||
          "Failed to mark all notifications as read";
        return { success: false, message: error.value };
      } finally {
        isUpdating.value = false;
      }
    }

    /**
     * Get notification type config
     * @param {string} type - Notification type
     */
    function getTypeConfig(type) {
      return NOTIFICATION_TYPES[type] || { icon: "Bell", color: "gray" };
    }

    /**
     * Format notification time
     * @param {string} dateString - Date string
     */
    function formatTime(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 1) return "Just now";
      if (diffMins < 60) return `${diffMins}m ago`;
      if (diffHours < 24) return `${diffHours}h ago`;
      if (diffDays < 7) return `${diffDays}d ago`;

      return date.toLocaleDateString("en-AU", {
        day: "numeric",
        month: "short",
      });
    }

    /**
     * Clear notifications state
     */
    function clearNotifications() {
      notifications.value = [];
      error.value = null;
    }

    /**
     * Clear error
     */
    function clearError() {
      error.value = null;
    }

    return {
      // State
      notifications,
      isLoading,
      error,
      isUpdating,

      // Constants
      NOTIFICATION_TYPES,

      // Getters
      unreadCount,
      unreadNotifications,
      readNotifications,
      hasUnread,
      notificationCount,

      // Actions
      fetchNotifications,
      markAsRead,
      markAllAsRead,
      getTypeConfig,
      formatTime,
      clearNotifications,
      clearError,
    };
  }
);
