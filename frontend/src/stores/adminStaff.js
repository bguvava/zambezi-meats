/**
 * Admin Staff Store
 *
 * Manages admin staff management state and actions.
 *
 * @requirement ADMIN-018 Create staff management page
 * @requirement ADMIN-019 Create/edit staff accounts
 * @requirement ADMIN-020 Activate/deactivate staff
 * @requirement ADMIN-021 View staff activity
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { adminDashboard } from "@/services/dashboard";

export const useAdminStaffStore = defineStore("adminStaff", () => {
  // State
  const staff = ref([]);
  const currentStaff = ref(null);
  const staffActivity = ref([]);
  const staffStats = ref(null);
  const isLoading = ref(false);
  const error = ref(null);
  const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });
  const filters = ref({
    search: "",
  });

  // Getters
  const hasStaff = computed(() => staff.value.length > 0);

  const activeStaffCount = computed(
    () => staff.value.filter((s) => s.is_active !== false).length
  );

  const adminCount = computed(
    () => staff.value.filter((s) => s.role === "admin").length
  );

  const staffCount = computed(
    () => staff.value.filter((s) => s.role === "staff").length
  );

  // Actions
  async function fetchStaff(page = 1) {
    isLoading.value = true;
    error.value = null;

    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
      };

      if (filters.value.search) {
        params.search = filters.value.search;
      }

      const response = await adminDashboard.getStaff(params);

      if (response.success) {
        staff.value = response.staff || [];
        pagination.value = {
          currentPage: response.pagination?.current_page || 1,
          lastPage: response.pagination?.last_page || 1,
          perPage: response.pagination?.per_page || 15,
          total: response.pagination?.total || 0,
        };
      }
    } catch (err) {
      console.error("Failed to fetch staff:", err);
      error.value = err.message || "Failed to load staff";
    } finally {
      isLoading.value = false;
    }
  }

  async function createStaff(data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.createStaff(data);

      if (response.success && response.staff) {
        staff.value.unshift(response.staff);
        pagination.value.total += 1;
      }

      return response;
    } catch (err) {
      console.error("Failed to create staff:", err);
      error.value = err.message || "Failed to create staff";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateStaff(id, data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updateStaff(id, data);

      if (response.success && response.staff) {
        const index = staff.value.findIndex((s) => s.id === id);
        if (index !== -1) {
          staff.value[index] = response.staff;
        }
        currentStaff.value = response.staff;
      }

      return response;
    } catch (err) {
      console.error("Failed to update staff:", err);
      error.value = err.message || "Failed to update staff";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function deleteStaff(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.deleteStaff(id);

      if (response.success) {
        const index = staff.value.findIndex((s) => s.id === id);
        if (index !== -1) {
          staff.value.splice(index, 1);
          pagination.value.total -= 1;
        }
      }

      return response;
    } catch (err) {
      console.error("Failed to delete staff:", err);
      error.value = err.message || "Failed to delete staff";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function toggleStaffStatus(id, isActive) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updateStaff(id, {
        is_active: isActive,
      });

      if (response.success && response.staff) {
        const index = staff.value.findIndex((s) => s.id === id);
        if (index !== -1) {
          staff.value[index] = response.staff;
        }
      }

      return response;
    } catch (err) {
      console.error("Failed to toggle staff status:", err);
      error.value = err.message || "Failed to update staff status";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchStaffActivity(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getStaffActivity(id);

      if (response.success) {
        currentStaff.value = response.staff;
        staffActivity.value = response.activity || [];
        staffStats.value = response.stats || null;
      }

      return response;
    } catch (err) {
      console.error("Failed to fetch staff activity:", err);
      error.value = err.message || "Failed to load staff activity";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters };
  }

  function resetFilters() {
    filters.value = {
      search: "",
    };
  }

  function setCurrentStaff(staffMember) {
    currentStaff.value = staffMember;
  }

  function clearError() {
    error.value = null;
  }

  function $reset() {
    staff.value = [];
    currentStaff.value = null;
    staffActivity.value = [];
    staffStats.value = null;
    isLoading.value = false;
    error.value = null;
    pagination.value = {
      currentPage: 1,
      lastPage: 1,
      perPage: 15,
      total: 0,
    };
    resetFilters();
  }

  return {
    // State
    staff,
    currentStaff,
    staffActivity,
    staffStats,
    isLoading,
    error,
    pagination,
    filters,

    // Getters
    hasStaff,
    activeStaffCount,
    adminCount,
    staffCount,

    // Actions
    fetchStaff,
    createStaff,
    updateStaff,
    deleteStaff,
    toggleStaffStatus,
    fetchStaffActivity,
    setFilters,
    resetFilters,
    setCurrentStaff,
    clearError,
    $reset,
  };
});
