/**
 * Admin Promotions Store Tests
 *
 * Comprehensive tests for the adminPromotions Pinia store.
 *
 * @requirement ADMIN-022 Create promotions management
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAdminPromotionsStore } from "@/stores/adminPromotions";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  adminDashboard: {
    getPromotions: vi.fn(),
    createPromotion: vi.fn(),
    updatePromotion: vi.fn(),
    deletePromotion: vi.fn(),
  },
}));

import { adminDashboard } from "@/services/dashboard";

describe("Admin Promotions Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useAdminPromotionsStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty promotions array", () => {
      expect(store.promotions).toEqual([]);
    });

    it("starts with null currentPromotion", () => {
      expect(store.currentPromotion).toBeNull();
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });

    it("starts with default pagination", () => {
      expect(store.pagination.currentPage).toBe(1);
      expect(store.pagination.perPage).toBe(15);
    });

    it("starts with default filters", () => {
      expect(store.filters.status).toBe("all");
    });

    it("has promotionTypes defined", () => {
      expect(store.promotionTypes).toHaveLength(2);
      expect(store.promotionTypes[0].value).toBe("percentage");
      expect(store.promotionTypes[1].value).toBe("fixed");
    });
  });

  describe("Computed Properties", () => {
    it("hasPromotions returns false when empty", () => {
      expect(store.hasPromotions).toBe(false);
    });

    it("hasPromotions returns true when promotions exist", () => {
      store.promotions = [{ id: 1 }];
      expect(store.hasPromotions).toBe(true);
    });

    it("activePromotionsCount counts valid active promotions", () => {
      const today = new Date();
      const yesterday = new Date(today);
      yesterday.setDate(yesterday.getDate() - 1);
      const tomorrow = new Date(today);
      tomorrow.setDate(tomorrow.getDate() + 1);
      const lastWeek = new Date(today);
      lastWeek.setDate(lastWeek.getDate() - 7);

      store.promotions = [
        {
          id: 1,
          is_active: true,
          start_date: yesterday.toISOString(),
          end_date: tomorrow.toISOString(),
        },
        {
          id: 2,
          is_active: false,
          start_date: yesterday.toISOString(),
          end_date: tomorrow.toISOString(),
        },
        {
          id: 3,
          is_active: true,
          start_date: lastWeek.toISOString(),
          end_date: yesterday.toISOString(),
        },
      ];
      expect(store.activePromotionsCount).toBe(1);
    });

    it("expiredPromotionsCount counts expired promotions", () => {
      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);

      store.promotions = [
        { id: 1, end_date: yesterday.toISOString() },
        { id: 2, end_date: tomorrow.toISOString() },
      ];
      expect(store.expiredPromotionsCount).toBe(1);
    });
  });

  describe("fetchPromotions", () => {
    it("sets isLoading during fetch", async () => {
      adminDashboard.getPromotions.mockResolvedValue({
        success: true,
        promotions: [],
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      });

      const promise = store.fetchPromotions();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores promotions from response", async () => {
      const mockPromotions = [
        { id: 1, name: "Summer Sale", code: "SUMMER20" },
        { id: 2, name: "Winter Sale", code: "WINTER10" },
      ];
      adminDashboard.getPromotions.mockResolvedValue({
        success: true,
        promotions: mockPromotions,
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
      });

      await store.fetchPromotions();
      expect(store.promotions).toEqual(mockPromotions);
    });

    it("applies status filter", async () => {
      adminDashboard.getPromotions.mockResolvedValue({
        success: true,
        promotions: [],
        pagination: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
      });

      store.setFilters({ status: "active" });
      await store.fetchPromotions();

      expect(adminDashboard.getPromotions).toHaveBeenCalledWith(
        expect.objectContaining({ status: "active" })
      );
    });

    it("handles API errors", async () => {
      adminDashboard.getPromotions.mockRejectedValue(
        new Error("Network error")
      );

      await store.fetchPromotions();
      expect(store.error).toBe("Network error");
    });
  });

  describe("createPromotion", () => {
    it("creates promotion and adds to list", async () => {
      const newPromotion = { id: 1, name: "New Promo", code: "NEW20" };
      adminDashboard.createPromotion.mockResolvedValue({
        success: true,
        promotion: newPromotion,
      });

      await store.createPromotion({
        name: "New Promo",
        code: "NEW20",
        type: "percentage",
        value: 20,
        start_date: "2024-01-01",
        end_date: "2024-12-31",
      });

      expect(store.promotions[0]).toEqual(newPromotion);
      expect(store.pagination.total).toBe(1);
    });
  });

  describe("updatePromotion", () => {
    it("updates promotion in list", async () => {
      store.promotions = [{ id: 1, value: 10 }];
      const updatedPromotion = { id: 1, value: 25 };

      adminDashboard.updatePromotion.mockResolvedValue({
        success: true,
        promotion: updatedPromotion,
      });

      await store.updatePromotion(1, { value: 25 });
      expect(store.promotions[0].value).toBe(25);
      expect(store.currentPromotion.value).toBe(25);
    });
  });

  describe("deletePromotion", () => {
    it("removes promotion from list", async () => {
      store.promotions = [
        { id: 1, name: "Promo A" },
        { id: 2, name: "Promo B" },
      ];
      store.pagination.total = 2;

      adminDashboard.deletePromotion.mockResolvedValue({
        success: true,
      });

      await store.deletePromotion(1);
      expect(store.promotions).toHaveLength(1);
      expect(store.promotions[0].id).toBe(2);
      expect(store.pagination.total).toBe(1);
    });
  });

  describe("togglePromotionStatus", () => {
    it("toggles promotion active status", async () => {
      store.promotions = [{ id: 1, is_active: true }];
      const updatedPromotion = { id: 1, is_active: false };

      adminDashboard.updatePromotion.mockResolvedValue({
        success: true,
        promotion: updatedPromotion,
      });

      await store.togglePromotionStatus(1, false);
      expect(store.promotions[0].is_active).toBe(false);
    });
  });

  describe("setCurrentPromotion", () => {
    it("sets current promotion", () => {
      const promotion = { id: 1, name: "Test" };
      store.setCurrentPromotion(promotion);
      expect(store.currentPromotion).toEqual(promotion);
    });
  });

  describe("Filter Management", () => {
    it("setFilters updates filter values", () => {
      store.setFilters({ status: "active" });
      expect(store.filters.status).toBe("active");
    });

    it("resetFilters restores defaults", () => {
      store.setFilters({ status: "active" });
      store.resetFilters();
      expect(store.filters.status).toBe("all");
    });
  });

  describe("$reset", () => {
    it("resets all state to initial values", () => {
      store.promotions = [{ id: 1 }];
      store.currentPromotion = { id: 1 };
      store.error = "Error";

      store.$reset();

      expect(store.promotions).toEqual([]);
      expect(store.currentPromotion).toBeNull();
      expect(store.error).toBeNull();
    });
  });
});
