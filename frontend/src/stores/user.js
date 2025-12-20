import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/services/api";

export const useUserStore = defineStore("user", () => {
  // State
  const users = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const currentUser = ref(null);
  const activityLogs = ref([]);

  // Filters and pagination
  const filters = ref({
    search: "",
    role: "",
    status: "",
  });

  const pagination = ref({
    currentPage: 1,
    perPage: 15,
    total: 0,
    lastPage: 1,
  });

  // Getters
  const filteredUsers = computed(() => users.value);

  const totalPages = computed(() => pagination.value.lastPage);

  const hasUsers = computed(() => users.value.length > 0);

  const getUserById = computed(() => {
    return (id) => users.value.find((user) => user.id === id);
  });

  // Actions
  async function fetchUsers(page = 1) {
    loading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
        ...filters.value,
      };

      // Remove empty filters
      Object.keys(params).forEach((key) => {
        if (
          params[key] === "" ||
          params[key] === null ||
          params[key] === undefined
        ) {
          delete params[key];
        }
      });

      const response = await api.get("/admin/users", { params });

      users.value = response.data.data;
      pagination.value = {
        currentPage: response.data.current_page,
        perPage: response.data.per_page,
        total: response.data.total,
        lastPage: response.data.last_page,
      };

      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to fetch users";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function createUser(userData) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.post("/admin/users", userData);

      // Refresh the list after creation
      await fetchUsers(pagination.value.currentPage);

      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to create user";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function updateUser(userId, userData) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.put(`/admin/users/${userId}`, userData);

      // Update user in the list
      const index = users.value.findIndex((u) => u.id === userId);
      if (index !== -1) {
        users.value[index] = response.data.data;
      }

      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to update user";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function changeStatus(userId, status) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.put(`/admin/users/${userId}/status`, {
        status,
      });

      // Update user status in the list
      const index = users.value.findIndex((u) => u.id === userId);
      if (index !== -1) {
        users.value[index] = response.data.data;
      }

      return response.data;
    } catch (err) {
      error.value =
        err.response?.data?.message || "Failed to change user status";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function resetPassword(userId) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.post(`/admin/users/${userId}/reset-password`);

      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to reset password";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function fetchActivity(userId, page = 1) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/users/${userId}/activity`, {
        params: { page },
      });

      activityLogs.value = response.data.data;

      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to fetch activity";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function exportPDF() {
    loading.value = true;
    error.value = null;

    try {
      const params = { ...filters.value };

      // Remove empty filters
      Object.keys(params).forEach((key) => {
        if (
          params[key] === "" ||
          params[key] === null ||
          params[key] === undefined
        ) {
          delete params[key];
        }
      });

      const response = await api.get("/admin/users/export", {
        params,
        responseType: "blob",
      });

      // Create download link
      const url = window.URL.createObjectURL(
        new Blob([response.data], { type: "application/pdf" })
      );
      const link = document.createElement("a");
      link.href = url;

      // Extract filename from Content-Disposition header or use default
      const contentDisposition = response.headers["content-disposition"];
      let filename = "users-export.pdf";

      if (contentDisposition) {
        const filenameMatch = contentDisposition.match(/filename="?(.+)"?/);
        if (filenameMatch && filenameMatch[1]) {
          filename = filenameMatch[1];
        }
      }

      link.setAttribute("download", filename);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);

      return { success: true, message: "Export downloaded successfully" };
    } catch (err) {
      error.value = err.response?.data?.message || "Failed to export users";
      throw err;
    } finally {
      loading.value = false;
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
  }

  function resetFilters() {
    filters.value = {
      search: "",
      role: "",
      status: "",
    };
  }

  function setCurrentUser(user) {
    currentUser.value = user;
  }

  function clearError() {
    error.value = null;
  }

  return {
    // State
    users,
    loading,
    error,
    currentUser,
    activityLogs,
    filters,
    pagination,

    // Getters
    filteredUsers,
    totalPages,
    hasUsers,
    getUserById,

    // Actions
    fetchUsers,
    createUser,
    updateUser,
    changeStatus,
    resetPassword,
    fetchActivity,
    exportPDF,
    setFilters,
    resetFilters,
    setCurrentUser,
    clearError,
  };
});
