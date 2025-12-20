/**
 * Notification Store
 *
 * Pinia store for managing toast notifications
 * @module stores/notificationStore
 */

import { defineStore } from "pinia";
import { toast } from "vue-sonner";

export const useNotificationStore = defineStore("notification", {
  actions: {
    /**
     * Show success notification
     * @param {string} message - Success message to display
     */
    success(message) {
      toast.success(message);
    },

    /**
     * Show error notification
     * @param {string} message - Error message to display
     */
    error(message) {
      toast.error(message);
    },

    /**
     * Show info notification
     * @param {string} message - Info message to display
     */
    info(message) {
      toast.info(message);
    },

    /**
     * Show warning notification
     * @param {string} message - Warning message to display
     */
    warning(message) {
      toast.warning(message);
    },

    /**
     * Show loading notification
     * @param {string} message - Loading message to display
     * @returns {string|number} Toast ID for dismissal
     */
    loading(message) {
      return toast.loading(message);
    },

    /**
     * Dismiss a specific notification
     * @param {string|number} id - Toast ID to dismiss
     */
    dismiss(id) {
      toast.dismiss(id);
    },
  },
});
