/**
 * CategorySidebar Component Tests
 *
 * Tests for the CategorySidebar component.
 *
 * @requirement SHOP-002 Category sidebar navigation
 * @requirement SHOP-013 Filter by category
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import { createRouter, createMemoryHistory } from "vue-router";
import CategorySidebar from "../../components/shop/CategorySidebar.vue";

// Mock the products store
vi.mock("../../stores/products", () => ({
  useProductsStore: () => ({
    isLoading: false,
  }),
}));

const createMockRouter = () => {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/", component: { template: "<div>Home</div>" } },
      { path: "/shop", component: { template: "<div>Shop</div>" } },
      {
        path: "/shop/:category",
        component: { template: "<div>Category</div>" },
      },
    ],
  });
};

const mockCategories = [
  { id: 1, name: "Beef", slug: "beef", products_count: 12, is_active: true },
  { id: 2, name: "Lamb", slug: "lamb", products_count: 8, is_active: true },
  {
    id: 3,
    name: "Chicken",
    slug: "chicken",
    products_count: 15,
    is_active: true,
  },
  { id: 4, name: "Pork", slug: "pork", products_count: 5, is_active: true },
];

describe("CategorySidebar.vue", () => {
  let router;
  let pinia;

  beforeEach(async () => {
    pinia = createPinia();
    setActivePinia(pinia);
    router = createMockRouter();
    await router.push("/");
    await router.isReady();
    vi.clearAllMocks();
  });

  const mountComponent = (props = {}) => {
    return mount(CategorySidebar, {
      props: {
        categories: mockCategories,
        selectedCategory: null,
        showCount: true,
        ...props,
      },
      global: {
        plugins: [router, pinia],
      },
    });
  };

  describe("Rendering", () => {
    it("renders the sidebar", () => {
      const wrapper = mountComponent();
      expect(wrapper.find(".bg-white").exists()).toBe(true);
    });

    it("displays Categories heading", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Categories");
    });

    it("displays All Products option", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("All Products");
    });

    it("displays all categories", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Beef");
      expect(wrapper.text()).toContain("Lamb");
      expect(wrapper.text()).toContain("Chicken");
      expect(wrapper.text()).toContain("Pork");
    });
  });

  describe("Product Counts", () => {
    it("shows product counts when showCount is true", () => {
      const wrapper = mountComponent({ showCount: true });
      expect(wrapper.text()).toContain("12");
      expect(wrapper.text()).toContain("8");
      expect(wrapper.text()).toContain("15");
    });

    it("hides product counts when showCount is false", () => {
      const wrapper = mountComponent({ showCount: false });
      // The counts should not be visible
      const countBadges = wrapper.findAll(".bg-gray-100");
      expect(countBadges.length).toBe(0);
    });
  });

  describe("Category Selection", () => {
    it("emits select event when category is clicked", async () => {
      const wrapper = mountComponent();

      // Find the Beef category button
      const buttons = wrapper.findAll("button");
      const beefButton = buttons.find((b) => b.text().includes("Beef"));

      await beefButton.trigger("click");

      expect(wrapper.emitted("select")).toBeTruthy();
      expect(wrapper.emitted("select")[0][0]).toBe("beef");
    });

    it("emits null when All Products is clicked", async () => {
      const wrapper = mountComponent({ selectedCategory: "beef" });

      const buttons = wrapper.findAll("button");
      const allButton = buttons.find((b) => b.text().includes("All Products"));

      await allButton.trigger("click");

      expect(wrapper.emitted("select")).toBeTruthy();
      expect(wrapper.emitted("select")[0][0]).toBeNull();
    });

    it("highlights selected category", () => {
      const wrapper = mountComponent({ selectedCategory: "beef" });

      const buttons = wrapper.findAll("button");
      const beefButton = buttons.find((b) => b.text().includes("Beef"));

      expect(beefButton.classes()).toContain("bg-primary-50");
      expect(beefButton.classes()).toContain("text-primary-700");
    });

    it("highlights All Products when no category selected", () => {
      const wrapper = mountComponent({ selectedCategory: null });

      const buttons = wrapper.findAll("button");
      const allButton = buttons.find((b) => b.text().includes("All Products"));

      expect(allButton.classes()).toContain("bg-primary-50");
    });
  });

  describe("Empty State", () => {
    it("shows message when no categories", () => {
      const wrapper = mountComponent({ categories: [] });
      expect(wrapper.text()).toContain("No categories available");
    });
  });

  describe("Category Icons", () => {
    it("displays icon when category has one", () => {
      const categoriesWithIcons = [
        { id: 1, name: "Beef", slug: "beef", products_count: 12, icon: "🥩" },
      ];
      const wrapper = mountComponent({ categories: categoriesWithIcons });
      expect(wrapper.text()).toContain("🥩");
    });

    it("displays default icon when category has no icon", () => {
      const wrapper = mountComponent();
      // Should have SVG fallback icons
      expect(wrapper.findAll("svg").length).toBeGreaterThan(0);
    });
  });

  describe("Button Styling", () => {
    it("applies hover styles to unselected categories", () => {
      const wrapper = mountComponent({ selectedCategory: "beef" });

      const buttons = wrapper.findAll("button");
      const lambButton = buttons.find((b) => b.text().includes("Lamb"));

      expect(lambButton.classes()).toContain("hover:bg-gray-50");
    });

    it("categories have full width", () => {
      const wrapper = mountComponent();

      const buttons = wrapper.findAll("button");
      buttons.forEach((button) => {
        expect(button.classes()).toContain("w-full");
      });
    });
  });

  describe("Accessibility", () => {
    it("category buttons are focusable", () => {
      const wrapper = mountComponent();
      const buttons = wrapper.findAll("button");

      buttons.forEach((button) => {
        expect(button.element.tagName).toBe("BUTTON");
      });
    });

    it("displays category list as unordered list", () => {
      const wrapper = mountComponent();
      expect(wrapper.find("ul").exists()).toBe(true);
    });
  });
});
