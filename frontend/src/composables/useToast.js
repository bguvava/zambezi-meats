/**
 * Toast Notification Composable
 * Simple toast notification system for user feedback
 */
import { ref } from "vue";

const toasts = ref([]);
let nextId = 0;

export function useToast() {
  const show = (message, type = "info", duration = 3000) => {
    const id = nextId++;
    const toast = {
      id,
      message,
      type,
      visible: true,
    };

    toasts.value.push(toast);

    // Auto-dismiss after duration
    if (duration > 0) {
      setTimeout(() => {
        remove(id);
      }, duration);
    }

    return id;
  };

  const remove = (id) => {
    const index = toasts.value.findIndex((t) => t.id === id);
    if (index > -1) {
      toasts.value.splice(index, 1);
    }
  };

  const success = (message, duration = 3000) => {
    return show(message, "success", duration);
  };

  const error = (message, duration = 4000) => {
    return show(message, "error", duration);
  };

  const warning = (message, duration = 3000) => {
    return show(message, "warning", duration);
  };

  const info = (message, duration = 3000) => {
    return show(message, "info", duration);
  };

  return {
    toasts,
    show,
    remove,
    success,
    error,
    warning,
    info,
  };
}
