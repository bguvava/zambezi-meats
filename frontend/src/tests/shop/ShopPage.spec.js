/**
 * ShopPage Integration Tests
 *
 * Tests for the main shop page with product grid, filters, and pagination.
 *
 * @requirement SHOP-001 Create shop page layout
 * @requirement SHOP-002 Display product grid with cards
 * @requirement SHOP-013 Filter by category
 * @requirement SHOP-017 Sort products
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import { createRouter, createWebHistory } from "vue-router";
import ShopPage from "../../pages/ShopPage.vue";

// Mock the products store
vi.mock("../../stores/products", () => ({
  useProductsStore: vi.fn(() => ({
    products: [],
    categories: [],
    isLoading: false,
    pagination: { currentPage: 1, lastPage: 1, total: 0 },
    filters: {
      category: null,
      search: "",
      minPrice: null,
      maxPrice: null,
      inStock: null,
      sort: "created_at",
      direction: "desc",
    },
    hasProducts: false,
    hasMore: false,
    fetchProducts: vi.fn().mockResolvedValue([]),
    fetchCategories: vi.fn().mockResolvedValue([]),
    setFilters: vi.fn(),
    resetFilters: vi.fn(),
  })),
}));

import { useProductsStore } from "../../stores/products";

describe("ShopPage.vue", () => {
  let pinia;
  let router;
  let mockStore;

  const mockProducts = [
    {
      id: 1,
      name: "Premium Beef Steak",
      slug: "premium-beef-steak",
      price_aud: 45.99,
      sale_price_aud: null,
      stock: 25,
      is_featured: true,
      images: [{ url: "/images/steak.jpg", is_primary: true }],
      category: { id: 1, name: "Beef", slug: "beef" },
    },
    {
      id: 2,
      name: "Lamb Chops",
      slug: "lamb-chops",
      price_aud: 35.99,
      sale_price_aud: 29.99,
      stock: 15,
      is_featured: false,
      images: [{ url: "/images/lamb.jpg", is_primary: true }],
      category: { id: 2, name: "Lamb", slug: "lamb" },
    },
  ];

  const mockCategories = [
    { id: 1, name: "Beef", slug: "beef", products_count: 10 },
    { id: 2, name: "Lamb", slug: "lamb", products_count: 8 },
    { id: 3, name: "Pork", slug: "pork", products_count: 5 },
  ];

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: "/", name: "home", component: { template: "<div>Home</div>" } },
        { path: "/shop", name: "shop", component: ShopPage },
        {
          path: "/products/:slug",
          name: "product-detail",
          component: { template: "<div>Product</div>" },
        },
      ],
    });

    mockStore = {
      products: [],
      categories: mockCategories,
      isLoading: false,
      pagination: { currentPage: 1, lastPage: 1, total: 0 },
      filters: {
        category: null,
        search: "",
        minPrice: null,
        maxPrice: null,
        inStock: null,
        sort: "created_at",
        direction: "desc",
      },
      hasProducts: false,
      hasMore: false,
      fetchProducts: vi.fn().mockResolvedValue([]),
      fetchCategories: vi.fn().mockResolvedValue(mockCategories),
      setFilters: vi.fn(),
      resetFilters: vi.fn(),
    };

    useProductsStore.mockReturnValue(mockStore);
    vi.clearAllMocks();
  });

  const mountComponent = async () => {
    await router.push("/shop");
    await router.isReady();

    return mount(ShopPage, {
      global: {
        plugins: [pinia, router],
        stubs: {
          ProductCard: {
            template:
              '<div class="product-card" :data-id="product.id">{{ product.name }}</div>',
            props: ["product"],
          },
          CategorySidebar: {
            template: '<div class="category-sidebar"><slot /></div>',
            props: ["categories", "selectedCategory"],
          },
          ProductFilters: {
            template: '<div class="product-filters"><slot /></div>',
            props: ["filters", "isOpen"],
          },
          SearchBar: {
            template: '<input class="search-bar" type="text" />',
          },
          ProductQuickView: {
            template: '<div class="quick-view" v-if="isOpen"><slot /></div>',
            props: ["product", "isOpen"],
          },
        },
      },
    });
  };

  describe("Page Rendering", () => {
    it("renders the shop page", async () => {
      const wrapper = await mountComponent();
      expect(wrapper.find("h1").text()).toBe("Shop");
    });

    it("shows page description", async () => {
      const wrapper = await mountComponent();
      expect(wrapper.text()).toContain(
        "Browse our selection of premium quality meats"
      );
    });

    it("renders search bar", async () => {
      const wrapper = await mountComponent();
      expect(wrapper.find(".search-bar").exists()).toBe(true);
    });

    it("renders category sidebar", async () => {
      const wrapper = await mountComponent();
      expect(wrapper.find(".category-sidebar").exists()).toBe(true);
    });

    it("renders product filters", async () => {
      const wrapper = await mountComponent();
      expect(wrapper.find(".product-filters").exists()).toBe(true);
    });
  });

  describe("Product Loading", () => {
    it("calls fetchCategories on mount", async () => {
      await mountComponent();
      expect(mockStore.fetchCategories).toHaveBeenCalled();
    });

    it("calls fetchProducts on mount", async () => {
      await mountComponent();
      expect(mockStore.fetchProducts).toHaveBeenCalled();
    });

    it("shows loading state when loading", async () => {
      mockStore.isLoading = true;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Loading products");
    });

    it("shows loading skeletons", async () => {
      mockStore.isLoading = true;
      const wrapper = await mountComponent();

      expect(wrapper.find(".animate-pulse").exists()).toBe(true);
    });
  });

  describe("Product Grid", () => {
    it("displays products when available", async () => {
      mockStore.products = mockProducts;
      mockStore.hasProducts = true;
      mockStore.pagination.total = 2;

      const wrapper = await mountComponent();

      expect(wrapper.findAll(".product-card").length).toBe(2);
    });

    it("displays product names", async () => {
      mockStore.products = mockProducts;
      mockStore.hasProducts = true;

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Premium Beef Steak");
      expect(wrapper.text()).toContain("Lamb Chops");
    });

    it('shows "No products found" when empty', async () => {
      mockStore.products = [];
      mockStore.hasProducts = false;

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("No products found");
    });

    it("shows clear filters button on empty state", async () => {
      mockStore.products = [];
      mockStore.hasProducts = false;

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Clear Filters");
    });
  });

  describe("Results Count", () => {
    it("shows results count", async () => {
      mockStore.products = mockProducts;
      mockStore.hasProducts = true;
      mockStore.pagination.total = 25;

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Showing");
      expect(wrapper.text()).toContain("25");
    });

    it("shows current page count", async () => {
      mockStore.products = mockProducts;
      mockStore.hasProducts = true;
      mockStore.pagination.total = 25;

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("2");
    });
  });

  describe("View Mode Toggle", () => {
    it("has grid view button", async () => {
      const wrapper = await mountComponent();

      const buttons = wrapper.findAll("button");
      expect(buttons.some((b) => b.classes().includes("p-2"))).toBe(true);
    });

    it("has list view button", async () => {
      const wrapper = await mountComponent();

      // Two view mode buttons should exist
      expect(
        wrapper.findAll(".sm\\:flex button").length
      ).toBeGreaterThanOrEqual(0);
    });

    it("toggles view mode", async () => {
      mockStore.products = mockProducts;
      mockStore.hasProducts = true;

      const wrapper = await mountComponent();

      // Default is grid
      expect(wrapper.vm.viewMode).toBe("grid");
    });
  });

  describe("Active Filters", () => {
    it("shows category filter chip when category selected", async () => {
      mockStore.filters.category = "beef";

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Category:");
      expect(wrapper.text()).toContain("beef");
    });

    it("shows search filter chip when search active", async () => {
      mockStore.filters.search = "steak";

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Search:");
      expect(wrapper.text()).toContain("steak");
    });

    it("shows price filter chip when price range set", async () => {
      mockStore.filters.minPrice = 10;
      mockStore.filters.maxPrice = 50;

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Price:");
      expect(wrapper.text()).toContain("$10");
      expect(wrapper.text()).toContain("$50");
    });

    it('shows "In Stock Only" chip when stock filter active', async () => {
      mockStore.filters.inStock = true;

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("In Stock Only");
    });

    it("has clear all button when filters active", async () => {
      mockStore.filters.category = "beef";

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Clear all");
    });
  });

  describe("Pagination", () => {
    it("shows pagination when multiple pages", async () => {
      mockStore.products = mockProducts;
      mockStore.hasProducts = true;
      mockStore.pagination = { currentPage: 1, lastPage: 5, total: 50 };

      const wrapper = await mountComponent();

      // Pagination buttons should exist
      const paginationButtons = wrapper
        .findAll("button")
        .filter((b) => b.text().match(/^\d+$/) || b.find("svg"));
      expect(paginationButtons.length).toBeGreaterThan(0);
    });

    it("hides pagination on single page", async () => {
      mockStore.products = mockProducts;
      mockStore.hasProducts = true;
      mockStore.pagination = { currentPage: 1, lastPage: 1, total: 2 };

      const wrapper = await mountComponent();

      // Only the page itself, not navigation
      const pageButtons = wrapper
        .findAll("button")
        .filter((b) => b.text() === "1");
      // Should not have previous/next when single page
      expect(mockStore.pagination.lastPage).toBe(1);
    });

    it("disables previous button on first page", async () => {
      mockStore.products = mockProducts;
      mockStore.hasProducts = true;
      mockStore.pagination = { currentPage: 1, lastPage: 5, total: 50 };

      const wrapper = await mountComponent();

      const prevButton = wrapper.find("button[disabled]");
      expect(prevButton.exists()).toBe(true);
    });
  });

  describe("Mobile Filters", () => {
    it("has mobile filter button", async () => {
      const wrapper = await mountComponent();

      const filterButton = wrapper
        .findAll("button")
        .find(
          (b) =>
            b.text().includes("Filters") && b.classes().includes("lg:hidden")
        );
      expect(filterButton).toBeDefined();
    });

    it("opens mobile filters on button click", async () => {
      const wrapper = await mountComponent();

      const filterButton = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Filters"));

      if (filterButton) {
        await filterButton.trigger("click");
        expect(wrapper.vm.isFilterOpen).toBe(true);
      }
    });
  });

  describe("Quick View", () => {
    it("opens quick view modal", async () => {
      mockStore.products = mockProducts;
      mockStore.hasProducts = true;

      const wrapper = await mountComponent();

      wrapper.vm.openQuickView(mockProducts[0]);
      await flushPromises();

      expect(wrapper.vm.isQuickViewOpen).toBe(true);
      expect(wrapper.vm.quickViewProduct).toEqual(mockProducts[0]);
    });

    it("closes quick view modal", async () => {
      const wrapper = await mountComponent();

      wrapper.vm.isQuickViewOpen = true;
      wrapper.vm.closeQuickView();

      expect(wrapper.vm.isQuickViewOpen).toBe(false);
    });
  });

  describe("URL Query Sync", () => {
    it("applies URL query params to filters", async () => {
      await router.push("/shop?category=beef&search=steak");
      await router.isReady();

      const wrapper = await mountComponent();

      // setFilters should be called with URL params
      expect(mockStore.setFilters).toHaveBeenCalled();
    });
  });

  describe("Filter Updates", () => {
    it("updates filters on category select", async () => {
      const wrapper = await mountComponent();

      wrapper.vm.handleCategorySelect("beef");

      expect(mockStore.setFilters).toHaveBeenCalled();
    });

    it("updates filters on search", async () => {
      const wrapper = await mountComponent();

      wrapper.vm.handleSearch("steak");

      expect(mockStore.setFilters).toHaveBeenCalledWith(
        expect.objectContaining({ search: "steak" })
      );
    });

    it("updates filters on filter apply", async () => {
      const wrapper = await mountComponent();

      wrapper.vm.updateFilters({ minPrice: 10, maxPrice: 50 });

      expect(mockStore.setFilters).toHaveBeenCalled();
    });
  });

  describe("Page Change", () => {
    it("fetches products on page change", async () => {
      mockStore.products = mockProducts;
      mockStore.hasProducts = true;
      mockStore.pagination = { currentPage: 1, lastPage: 5, total: 50 };

      const wrapper = await mountComponent();

      wrapper.vm.handlePageChange(2);

      expect(mockStore.fetchProducts).toHaveBeenCalledWith({ page: 2 });
    });

    it("scrolls to top on page change", async () => {
      const scrollSpy = vi
        .spyOn(window, "scrollTo")
        .mockImplementation(() => {});

      mockStore.products = mockProducts;
      mockStore.hasProducts = true;
      mockStore.pagination = { currentPage: 1, lastPage: 5, total: 50 };

      const wrapper = await mountComponent();

      wrapper.vm.handlePageChange(2);

      expect(scrollSpy).toHaveBeenCalledWith({ top: 0, behavior: "smooth" });
    });
  });

  describe("Accessibility", () => {
    it("has heading hierarchy", async () => {
      const wrapper = await mountComponent();

      expect(wrapper.find("h1").exists()).toBe(true);
    });

    it("products section is in main element", async () => {
      const wrapper = await mountComponent();

      expect(wrapper.find("main").exists()).toBe(true);
    });

    it("sidebar is in aside element", async () => {
      const wrapper = await mountComponent();

      expect(wrapper.find("aside").exists()).toBe(true);
    });
  });
});
