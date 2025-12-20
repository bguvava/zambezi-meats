/**
 * Admin Categories Store
 *
 * Manages admin category management state and actions.
 *
 * @requirement ADMIN-014 Create categories management
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { adminDashboard } from "@/services/dashboard";

export const useAdminCategoriesStore = defineStore("adminCategories", () => {
  // State
  const categories = ref([]);
  const currentCategory = ref(null);
  const isLoading = ref(false);
  const error = ref(null);

  // Getters
  const hasCategories = computed(() => categories.value.length > 0);

  const totalProducts = computed(() =>
    categories.value.reduce((acc, cat) => acc + (cat.products_count || 0), 0)
  );

  const categoryOptions = computed(() =>
    categories.value.map((cat) => ({
      value: cat.id,
      label: cat.name,
    }))
  );

  // Actions
  async function fetchCategories() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.getCategories();

      if (response.success) {
        categories.value = response.categories || [];
      }
    } catch (err) {
      console.error("Failed to fetch categories:", err);
      error.value = err.message || "Failed to load categories";
    } finally {
      isLoading.value = false;
    }
  }

  async function createCategory(formData) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.createCategory(formData);

      if (response.success && response.category) {
        categories.value.push(response.category);
      }

      return response;
    } catch (err) {
      console.error("Failed to create category:", err);
      error.value = err.message || "Failed to create category";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateCategory(id, formData) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.updateCategory(id, formData);

      if (response.success && response.category) {
        const index = categories.value.findIndex((c) => c.id === id);
        if (index !== -1) {
          categories.value[index] = response.category;
        }
        currentCategory.value = response.category;
      }

      return response;
    } catch (err) {
      console.error("Failed to update category:", err);
      error.value = err.message || "Failed to update category";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function deleteCategory(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await adminDashboard.deleteCategory(id);

      if (response.success) {
        const index = categories.value.findIndex((c) => c.id === id);
        if (index !== -1) {
          categories.value.splice(index, 1);
        }
      }

      return response;
    } catch (err) {
      console.error("Failed to delete category:", err);
      error.value = err.message || "Failed to delete category";
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  function setCurrentCategory(category) {
    currentCategory.value = category;
  }

  function clearError() {
    error.value = null;
  }

  function $reset() {
    categories.value = [];
    currentCategory.value = null;
    isLoading.value = false;
    error.value = null;
  }

  return {
    // State
    categories,
    currentCategory,
    isLoading,
    error,

    // Getters
    hasCategories,
    totalProducts,
    categoryOptions,

    // Actions
    fetchCategories,
    createCategory,
    updateCategory,
    deleteCategory,
    setCurrentCategory,
    clearError,
    $reset,
  };
});
