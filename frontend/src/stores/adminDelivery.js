/**
 * Admin Delivery Store
 *
 * Pinia store for managing admin delivery state
 * @module stores/adminDelivery
 * @requirements DEL-001 to DEL-019
 */
import { defineStore } from "pinia";
import { adminDashboard } from "@/services/dashboard";

export const useAdminDeliveryStore = defineStore("adminDelivery", {
  state: () => ({
    // Dashboard stats
    dashboard: {
      todaysDeliveries: 0,
      pending: 0,
      inProgress: 0,
      completed: 0,
      issues: 0,
      recentDeliveries: [],
    },

    // Deliveries list
    deliveries: [],
    pagination: {
      currentPage: 1,
      lastPage: 1,
      perPage: 15,
      total: 0,
    },

    // Filters
    filters: {
      status: "",
      staff_id: "",
      date_from: "",
      date_to: "",
      search: "",
    },

    // Current delivery detail
    currentDelivery: null,
    proofOfDelivery: null,

    // Delivery zones
    zones: [],
    currentZone: null,

    // Delivery settings
    settings: {
      free_delivery_threshold: 100,
      per_km_rate: 0.15,
      base_delivery_fee: 9.95,
      max_delivery_distance: 50,
    },

    // Map data
    mapData: {
      deliveries: [],
      storeLocation: {
        lat: -34.0577,
        lng: 151.0158,
        address: "6/1053 Old Princes Highway, Engadine, NSW 2233",
      },
    },

    // Report data
    report: null,

    // Staff list for assignments
    staffList: [],

    // Loading states
    loading: {
      dashboard: false,
      deliveries: false,
      detail: false,
      zones: false,
      settings: false,
      map: false,
      report: false,
      action: false,
    },

    // Error state
    error: null,
  }),

  getters: {
    /**
     * Check if deliveries exist
     */
    hasDeliveries: (state) => state.deliveries.length > 0,

    /**
     * Check if zones exist
     */
    hasZones: (state) => state.zones.length > 0,

    /**
     * Get active zones only
     */
    activeZones: (state) => state.zones.filter((z) => z.is_active),

    /**
     * Get pending deliveries
     */
    pendingDeliveries: (state) =>
      state.deliveries.filter((d) => d.status === "pending"),

    /**
     * Get in-progress deliveries
     */
    inProgressDeliveries: (state) =>
      state.deliveries.filter((d) => d.status === "out_for_delivery"),

    /**
     * Get deliveries with issues
     */
    deliveriesWithIssues: (state) =>
      state.deliveries.filter((d) => d.has_issue),

    /**
     * Check if has active filters
     */
    hasActiveFilters: (state) =>
      Object.values(state.filters).some((v) => v !== ""),
  },

  actions: {
    // ==================== DASHBOARD ====================

    /**
     * Fetch delivery dashboard data
     * @requirement DEL-001 Create delivery dashboard
     */
    async fetchDashboard() {
      this.loading.dashboard = true;
      this.error = null;
      try {
        const response = await adminDashboard.getDeliveryDashboard();
        this.dashboard = {
          todaysDeliveries: response.data.todays_deliveries || 0,
          pending: response.data.pending || 0,
          inProgress: response.data.in_progress || 0,
          completed: response.data.completed || 0,
          issues: response.data.issues || 0,
          recentDeliveries: response.data.recent_deliveries || [],
        };
      } catch (error) {
        this.error = error.message || "Failed to fetch delivery dashboard";
        console.error("Failed to fetch delivery dashboard:", error);
      } finally {
        this.loading.dashboard = false;
      }
    },

    // ==================== DELIVERIES LIST ====================

    /**
     * Fetch deliveries list with filters
     * @requirement DEL-002 Display all deliveries list
     */
    async fetchDeliveries(page = 1) {
      this.loading.deliveries = true;
      this.error = null;
      try {
        const params = {
          page,
          per_page: this.pagination.perPage,
          ...Object.fromEntries(
            Object.entries(this.filters).filter(([, v]) => v !== "")
          ),
        };
        const response = await adminDashboard.getDeliveries(params);
        this.deliveries = response.data.data || response.data || [];
        if (response.data.meta) {
          this.pagination = {
            currentPage: response.data.meta.current_page,
            lastPage: response.data.meta.last_page,
            perPage: response.data.meta.per_page,
            total: response.data.meta.total,
          };
        }
      } catch (error) {
        this.error = error.message || "Failed to fetch deliveries";
        console.error("Failed to fetch deliveries:", error);
      } finally {
        this.loading.deliveries = false;
      }
    },

    /**
     * Set filters and refetch
     * @requirement DEL-003 Filter deliveries
     */
    setFilters(filters) {
      this.filters = { ...this.filters, ...filters };
    },

    /**
     * Reset all filters
     */
    resetFilters() {
      this.filters = {
        status: "",
        staff_id: "",
        date_from: "",
        date_to: "",
        search: "",
      };
    },

    // ==================== DELIVERY DETAIL ====================

    /**
     * Fetch single delivery details
     * @requirement DEL-004 View delivery detail
     */
    async fetchDelivery(id) {
      this.loading.detail = true;
      this.error = null;
      try {
        const response = await adminDashboard.getDelivery(id);
        this.currentDelivery = response.data;
      } catch (error) {
        this.error = error.message || "Failed to fetch delivery details";
        console.error("Failed to fetch delivery details:", error);
        throw error;
      } finally {
        this.loading.detail = false;
      }
    },

    /**
     * Clear current delivery
     */
    clearCurrentDelivery() {
      this.currentDelivery = null;
      this.proofOfDelivery = null;
    },

    // ==================== DELIVERY ASSIGNMENT ====================

    /**
     * Assign delivery to staff member
     * @requirement DEL-005 Assign delivery to staff
     */
    async assignDelivery(deliveryId, staffId) {
      this.loading.action = true;
      this.error = null;
      try {
        const response = await adminDashboard.assignDelivery(deliveryId, {
          staff_id: staffId,
        });
        // Update in list if exists
        const index = this.deliveries.findIndex((d) => d.id === deliveryId);
        if (index !== -1) {
          this.deliveries[index] = response.data;
        }
        if (this.currentDelivery?.id === deliveryId) {
          this.currentDelivery = response.data;
        }
        return response.data;
      } catch (error) {
        this.error = error.message || "Failed to assign delivery";
        console.error("Failed to assign delivery:", error);
        throw error;
      } finally {
        this.loading.action = false;
      }
    },

    /**
     * Reassign delivery to different staff
     * @requirement DEL-006 Reassign delivery
     */
    async reassignDelivery(deliveryId, staffId, reason) {
      this.loading.action = true;
      this.error = null;
      try {
        const response = await adminDashboard.assignDelivery(deliveryId, {
          staff_id: staffId,
          reason,
        });
        // Update in list if exists
        const index = this.deliveries.findIndex((d) => d.id === deliveryId);
        if (index !== -1) {
          this.deliveries[index] = response.data;
        }
        if (this.currentDelivery?.id === deliveryId) {
          this.currentDelivery = response.data;
        }
        return response.data;
      } catch (error) {
        this.error = error.message || "Failed to reassign delivery";
        console.error("Failed to reassign delivery:", error);
        throw error;
      } finally {
        this.loading.action = false;
      }
    },

    // ==================== PROOF OF DELIVERY ====================

    /**
     * Fetch proof of delivery
     * @requirement DEL-007 View proof of delivery
     */
    async fetchProofOfDelivery(deliveryId) {
      this.loading.detail = true;
      this.error = null;
      try {
        const response = await adminDashboard.getDeliveryPod(deliveryId);
        this.proofOfDelivery = response.data;
        return response.data;
      } catch (error) {
        this.error = error.message || "Failed to fetch proof of delivery";
        console.error("Failed to fetch proof of delivery:", error);
        throw error;
      } finally {
        this.loading.detail = false;
      }
    },

    /**
     * Download POD as PDF
     * @requirement DEL-008 Download POD document
     */
    async downloadPodPdf(deliveryId) {
      this.loading.action = true;
      try {
        const response = await adminDashboard.downloadDeliveryPod(deliveryId);
        // Handle blob download
        const url = window.URL.createObjectURL(new Blob([response]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", `pod-${deliveryId}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      } catch (error) {
        this.error = error.message || "Failed to download POD";
        console.error("Failed to download POD:", error);
        throw error;
      } finally {
        this.loading.action = false;
      }
    },

    // ==================== DELIVERY ISSUES ====================

    /**
     * Resolve delivery issue
     * @requirement DEL-009 Handle delivery issues
     */
    async resolveIssue(deliveryId, resolution, status = "resolved") {
      this.loading.action = true;
      this.error = null;
      try {
        const response = await adminDashboard.resolveDeliveryIssue(deliveryId, {
          resolution,
          status,
        });
        // Update in list if exists
        const index = this.deliveries.findIndex((d) => d.id === deliveryId);
        if (index !== -1) {
          this.deliveries[index] = response.data;
        }
        if (this.currentDelivery?.id === deliveryId) {
          this.currentDelivery = response.data;
        }
        return response.data;
      } catch (error) {
        this.error = error.message || "Failed to resolve issue";
        console.error("Failed to resolve issue:", error);
        throw error;
      } finally {
        this.loading.action = false;
      }
    },

    // ==================== DELIVERY ZONES ====================

    /**
     * Fetch all delivery zones
     * @requirement DEL-010 Create delivery zones management
     */
    async fetchZones(params = {}) {
      this.loading.zones = true;
      this.error = null;
      try {
        const response = await adminDashboard.getDeliveryZones(params);
        this.zones = response.data.data || response.data || [];
      } catch (error) {
        this.error = error.message || "Failed to fetch delivery zones";
        console.error("Failed to fetch delivery zones:", error);
      } finally {
        this.loading.zones = false;
      }
    },

    /**
     * Create new delivery zone
     * @requirement DEL-011 Add delivery zone
     */
    async createZone(data) {
      this.loading.action = true;
      this.error = null;
      try {
        const response = await adminDashboard.createDeliveryZone(data);
        this.zones.push(response.data);
        return response.data;
      } catch (error) {
        this.error = error.message || "Failed to create zone";
        console.error("Failed to create zone:", error);
        throw error;
      } finally {
        this.loading.action = false;
      }
    },

    /**
     * Update existing delivery zone
     * @requirement DEL-012 Edit delivery zone
     */
    async updateZone(zoneId, data) {
      this.loading.action = true;
      this.error = null;
      try {
        const response = await adminDashboard.updateDeliveryZone(zoneId, data);
        const index = this.zones.findIndex((z) => z.id === zoneId);
        if (index !== -1) {
          this.zones[index] = response.data;
        }
        return response.data;
      } catch (error) {
        this.error = error.message || "Failed to update zone";
        console.error("Failed to update zone:", error);
        throw error;
      } finally {
        this.loading.action = false;
      }
    },

    /**
     * Delete delivery zone
     * @requirement DEL-013 Delete delivery zone
     */
    async deleteZone(zoneId) {
      this.loading.action = true;
      this.error = null;
      try {
        await adminDashboard.deleteDeliveryZone(zoneId);
        this.zones = this.zones.filter((z) => z.id !== zoneId);
      } catch (error) {
        this.error = error.message || "Failed to delete zone";
        console.error("Failed to delete zone:", error);
        throw error;
      } finally {
        this.loading.action = false;
      }
    },

    // ==================== DELIVERY SETTINGS ====================

    /**
     * Fetch delivery settings
     * @requirement DEL-014 Configure delivery fees
     */
    async fetchSettings() {
      this.loading.settings = true;
      this.error = null;
      try {
        const response = await adminDashboard.getDeliverySettings();
        this.settings = response.data;
      } catch (error) {
        this.error = error.message || "Failed to fetch delivery settings";
        console.error("Failed to fetch delivery settings:", error);
      } finally {
        this.loading.settings = false;
      }
    },

    /**
     * Update delivery settings
     * @requirement DEL-014 Configure delivery fees
     */
    async updateSettings(data) {
      this.loading.action = true;
      this.error = null;
      try {
        const response = await adminDashboard.updateDeliverySettings(data);
        this.settings = response.data;
        return response.data;
      } catch (error) {
        this.error = error.message || "Failed to update delivery settings";
        console.error("Failed to update delivery settings:", error);
        throw error;
      } finally {
        this.loading.action = false;
      }
    },

    // ==================== MAP DATA ====================

    /**
     * Fetch delivery map data
     * @requirement DEL-015 View delivery map
     */
    async fetchMapData(date = null) {
      this.loading.map = true;
      this.error = null;
      try {
        const params = date ? { date } : {};
        const response = await adminDashboard.getDeliveryMapData(params);
        this.mapData = {
          deliveries: response.data.deliveries || [],
          storeLocation:
            response.data.store_location || this.mapData.storeLocation,
        };
      } catch (error) {
        this.error = error.message || "Failed to fetch map data";
        console.error("Failed to fetch map data:", error);
      } finally {
        this.loading.map = false;
      }
    },

    // ==================== REPORTS ====================

    /**
     * Fetch delivery report
     * @requirement DEL-016 Generate delivery report
     */
    async fetchReport(params = {}) {
      this.loading.report = true;
      this.error = null;
      try {
        const response = await adminDashboard.getDeliveryReport(params);
        this.report = response.data;
        return response.data;
      } catch (error) {
        this.error = error.message || "Failed to fetch delivery report";
        console.error("Failed to fetch delivery report:", error);
        throw error;
      } finally {
        this.loading.report = false;
      }
    },

    /**
     * Export deliveries data
     * @requirement DEL-017 Export delivery data
     */
    async exportDeliveries(params = {}) {
      this.loading.action = true;
      try {
        const response = await adminDashboard.exportDeliveries(params);
        // Handle as JSON download or return data
        if (params.format === "pdf") {
          const url = window.URL.createObjectURL(new Blob([response]));
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", `deliveries-export-${Date.now()}.pdf`);
          document.body.appendChild(link);
          link.click();
          link.remove();
          window.URL.revokeObjectURL(url);
        }
        return response;
      } catch (error) {
        this.error = error.message || "Failed to export deliveries";
        console.error("Failed to export deliveries:", error);
        throw error;
      } finally {
        this.loading.action = false;
      }
    },

    // ==================== STAFF ====================

    /**
     * Fetch staff list for assignments
     */
    async fetchStaffList() {
      try {
        const response = await adminDashboard.getStaff({
          role: "staff",
          status: "active",
        });
        this.staffList = response.staff || [];
      } catch (error) {
        console.error("Failed to fetch staff list:", error);
        this.staffList = [];
      }
    },

    // ==================== UTILITY ====================

    /**
     * Clear error state
     */
    clearError() {
      this.error = null;
    },

    /**
     * Reset store state
     */
    $reset() {
      this.dashboard = {
        todaysDeliveries: 0,
        pending: 0,
        inProgress: 0,
        completed: 0,
        issues: 0,
        recentDeliveries: [],
      };
      this.deliveries = [];
      this.pagination = {
        currentPage: 1,
        lastPage: 1,
        perPage: 15,
        total: 0,
      };
      this.filters = {
        status: "",
        staff_id: "",
        date_from: "",
        date_to: "",
        search: "",
      };
      this.currentDelivery = null;
      this.proofOfDelivery = null;
      this.zones = [];
      this.currentZone = null;
      this.settings = {
        free_delivery_threshold: 100,
        per_km_rate: 0.15,
        base_delivery_fee: 9.95,
        max_delivery_distance: 50,
      };
      this.mapData = {
        deliveries: [],
        storeLocation: {
          lat: -34.0577,
          lng: 151.0158,
          address: "6/1053 Old Princes Highway, Engadine, NSW 2233",
        },
      };
      this.report = null;
      this.staffList = [];
      this.loading = {
        dashboard: false,
        deliveries: false,
        detail: false,
        zones: false,
        settings: false,
        map: false,
        report: false,
        action: false,
      };
      this.error = null;
    },
  },
});
