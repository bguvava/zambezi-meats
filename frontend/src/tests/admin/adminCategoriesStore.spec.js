/**
 * Admin Categories Store Tests
 *
 * Comprehensive tests for the adminCategories Pinia store.
 *
 * @requirement ADMIN-014 Create categories management
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useAdminCategoriesStore } from "@/stores/adminCategories";

// Mock dashboard service
vi.mock("@/services/dashboard", () => ({
  adminDashboard: {
    getCategories: vi.fn(),
    createCategory: vi.fn(),
    updateCategory: vi.fn(),
    deleteCategory: vi.fn(),
  },
}));

import { adminDashboard } from "@/services/dashboard";

describe("Admin Categories Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useAdminCategoriesStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty categories array", () => {
      expect(store.categories).toEqual([]);
    });

    it("starts with null currentCategory", () => {
      expect(store.currentCategory).toBeNull();
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });
  });

  describe("Computed Properties", () => {
    it("hasCategories returns false when empty", () => {
      expect(store.hasCategories).toBe(false);
    });

    it("hasCategories returns true when categories exist", () => {
      store.categories = [{ id: 1 }];
      expect(store.hasCategories).toBe(true);
    });

    it("totalProducts sums all products_count", () => {
      store.categories = [
        { id: 1, products_count: 10 },
        { id: 2, products_count: 5 },
        { id: 3, products_count: 15 },
      ];
      expect(store.totalProducts).toBe(30);
    });

    it("categoryOptions returns formatted options", () => {
      store.categories = [
        { id: 1, name: "Beef" },
        { id: 2, name: "Pork" },
      ];
      expect(store.categoryOptions).toEqual([
        { value: 1, label: "Beef" },
        { value: 2, label: "Pork" },
      ]);
    });
  });

  describe("fetchCategories", () => {
    it("sets isLoading during fetch", async () => {
      adminDashboard.getCategories.mockResolvedValue({
        success: true,
        categories: [],
      });

      const promise = store.fetchCategories();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("stores categories from response", async () => {
      const mockCategories = [
        { id: 1, name: "Beef" },
        { id: 2, name: "Pork" },
      ];
      adminDashboard.getCategories.mockResolvedValue({
        success: true,
        categories: mockCategories,
      });

      await store.fetchCategories();
      expect(store.categories).toEqual(mockCategories);
    });

    it("handles API errors", async () => {
      adminDashboard.getCategories.mockRejectedValue(
        new Error("Network error")
      );

      await store.fetchCategories();
      expect(store.error).toBe("Network error");
    });
  });

  describe("createCategory", () => {
    it("creates category and adds to list", async () => {
      const newCategory = { id: 1, name: "Poultry" };
      adminDashboard.createCategory.mockResolvedValue({
        success: true,
        category: newCategory,
      });

      const formData = new FormData();
      formData.append("name", "Poultry");

      await store.createCategory(formData);
      expect(store.categories).toContainEqual(newCategory);
    });
  });

  describe("updateCategory", () => {
    it("updates category in list", async () => {
      store.categories = [{ id: 1, name: "Old Name" }];
      const updatedCategory = { id: 1, name: "New Name" };

      adminDashboard.updateCategory.mockResolvedValue({
        success: true,
        category: updatedCategory,
      });

      const formData = new FormData();
      await store.updateCategory(1, formData);

      expect(store.categories[0].name).toBe("New Name");
      expect(store.currentCategory.name).toBe("New Name");
    });
  });

  describe("deleteCategory", () => {
    it("removes category from list", async () => {
      store.categories = [
        { id: 1, name: "Beef" },
        { id: 2, name: "Pork" },
      ];

      adminDashboard.deleteCategory.mockResolvedValue({
        success: true,
      });

      await store.deleteCategory(1);
      expect(store.categories).toHaveLength(1);
      expect(store.categories[0].id).toBe(2);
    });
  });

  describe("setCurrentCategory", () => {
    it("sets current category", () => {
      const category = { id: 1, name: "Test" };
      store.setCurrentCategory(category);
      expect(store.currentCategory).toEqual(category);
    });
  });

  describe("$reset", () => {
    it("resets all state to initial values", () => {
      store.categories = [{ id: 1 }];
      store.currentCategory = { id: 1 };
      store.error = "Error";

      store.$reset();

      expect(store.categories).toEqual([]);
      expect(store.currentCategory).toBeNull();
      expect(store.error).toBeNull();
    });
  });
});
