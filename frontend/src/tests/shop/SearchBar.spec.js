/**
 * SearchBar Component Tests
 *
 * Tests for the SearchBar component with autocomplete.
 *
 * @requirement SHOP-004 Implement search functionality
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import { createRouter, createWebHistory } from "vue-router";
import SearchBar from "../../components/shop/SearchBar.vue";

// Mock the products store
vi.mock("../../stores/products", () => ({
  useProductsStore: vi.fn(() => ({
    searchResults: [],
    isSearching: false,
    quickSearch: vi.fn(),
    clearSearch: vi.fn(),
  })),
}));

// Mock the currency store
vi.mock("../../stores/currency", () => ({
  useCurrencyStore: vi.fn(() => ({
    format: vi.fn((value) => `$${Number(value).toFixed(2)}`),
  })),
}));

import { useProductsStore } from "../../stores/products";

describe("SearchBar.vue", () => {
  let pinia;
  let router;
  let mockStore;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: "/", name: "home", component: { template: "<div>Home</div>" } },
        {
          path: "/shop",
          name: "shop",
          component: { template: "<div>Shop</div>" },
        },
        {
          path: "/products/:slug",
          name: "product-detail",
          component: { template: "<div>Product</div>" },
        },
      ],
    });

    mockStore = {
      searchResults: [],
      isSearching: false,
      quickSearch: vi.fn(),
      clearSearch: vi.fn(),
    };

    useProductsStore.mockReturnValue(mockStore);

    vi.useFakeTimers();
    vi.clearAllMocks();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  const mountComponent = (props = {}) => {
    return mount(SearchBar, {
      props: {
        ...props,
      },
      global: {
        plugins: [pinia, router],
        stubs: {
          RouterLink: {
            template: '<a :href="to"><slot /></a>',
            props: ["to"],
          },
        },
      },
    });
  };

  describe("Rendering", () => {
    it("renders search input", () => {
      const wrapper = mountComponent();
      expect(wrapper.find('input[type="text"]').exists()).toBe(true);
    });

    it("has search icon", () => {
      const wrapper = mountComponent();
      expect(wrapper.find("svg").exists()).toBe(true);
    });

    it("has placeholder text", () => {
      const wrapper = mountComponent();
      const input = wrapper.find("input");
      expect(input.attributes("placeholder")).toBe("Search products...");
    });

    it("accepts custom placeholder", () => {
      const wrapper = mountComponent({ placeholder: "Find products..." });
      const input = wrapper.find("input");
      expect(input.attributes("placeholder")).toBe("Find products...");
    });
  });

  describe("Search Input", () => {
    it("updates query on input", async () => {
      const wrapper = mountComponent();
      const input = wrapper.find("input");

      await input.setValue("beef");

      expect(wrapper.vm.query).toBe("beef");
    });

    it("debounces search calls", async () => {
      const wrapper = mountComponent();
      const input = wrapper.find("input");

      await input.setValue("be");
      await input.setValue("bee");
      await input.setValue("beef");

      // Should not have called yet
      expect(mockStore.quickSearch).not.toHaveBeenCalled();

      // Advance timers past debounce
      vi.advanceTimersByTime(350);
      await flushPromises();

      // Should call only once with final value
      expect(mockStore.quickSearch).toHaveBeenCalledTimes(1);
      expect(mockStore.quickSearch).toHaveBeenCalledWith("beef");
    });

    it("does not search for queries less than 2 characters", async () => {
      const wrapper = mountComponent();
      const input = wrapper.find("input");

      await input.setValue("b");
      vi.advanceTimersByTime(350);
      await flushPromises();

      expect(mockStore.quickSearch).not.toHaveBeenCalled();
    });

    it("clears search when query is cleared", async () => {
      const wrapper = mountComponent();
      const input = wrapper.find("input");

      await input.setValue("beef");
      vi.advanceTimersByTime(350);
      await flushPromises();

      await input.setValue("");
      vi.advanceTimersByTime(350);
      await flushPromises();

      expect(mockStore.clearSearch).toHaveBeenCalled();
    });
  });

  describe("Autocomplete Dropdown", () => {
    it("shows dropdown when there are results and isOpen", async () => {
      mockStore.searchResults = [
        { id: 1, name: "Beef Steak", slug: "beef-steak", price: 25.99 },
      ];

      const wrapper = mountComponent();
      wrapper.vm.query = "beef";
      wrapper.vm.isOpen = true;
      await flushPromises();

      expect(wrapper.find(".absolute.z-50").exists()).toBe(true);
    });

    it("shows product names in dropdown", async () => {
      mockStore.searchResults = [
        { id: 1, name: "Beef Steak", slug: "beef-steak", price: 25.99 },
        { id: 2, name: "Beef Mince", slug: "beef-mince", price: 15.99 },
      ];

      const wrapper = mountComponent();
      wrapper.vm.query = "beef";
      wrapper.vm.isOpen = true;
      await flushPromises();

      expect(wrapper.text()).toContain("Beef Steak");
      expect(wrapper.text()).toContain("Beef Mince");
    });

    it("shows product prices in dropdown", async () => {
      mockStore.searchResults = [
        { id: 1, name: "Beef Steak", slug: "beef-steak", price: 25.99 },
      ];

      const wrapper = mountComponent();
      wrapper.vm.query = "beef";
      wrapper.vm.isOpen = true;
      await flushPromises();

      expect(wrapper.text()).toContain("$25.99");
    });
  });

  describe("Loading State", () => {
    it("shows loading indicator when searching", async () => {
      mockStore.isSearching = true;
      mockStore.searchResults = [];

      const wrapper = mountComponent();
      wrapper.vm.query = "beef";
      wrapper.vm.isOpen = true;
      await flushPromises();

      // Should show loading spinner or "Searching" text
      expect(
        wrapper.find(".animate-spin").exists() ||
          wrapper.text().includes("Searching")
      ).toBe(true);
    });
  });

  describe("Keyboard Navigation", () => {
    it("closes dropdown on Escape", async () => {
      mockStore.searchResults = [
        { id: 1, name: "Beef Steak", slug: "beef-steak", price: 25.99 },
      ];

      const wrapper = mountComponent();
      wrapper.vm.isOpen = true;
      await flushPromises();

      await wrapper.find("input").trigger("keydown", { key: "Escape" });

      expect(wrapper.vm.isOpen).toBe(false);
    });

    it("navigates down with arrow key", async () => {
      mockStore.searchResults = [
        { id: 1, name: "Beef Steak", slug: "beef-steak", price: 25.99 },
        { id: 2, name: "Beef Mince", slug: "beef-mince", price: 15.99 },
      ];

      const wrapper = mountComponent();
      wrapper.vm.query = "beef";
      wrapper.vm.isOpen = true;
      await flushPromises();

      await wrapper.find("input").trigger("keydown", { key: "ArrowDown" });

      expect(wrapper.vm.selectedIndex).toBe(0);
    });

    it("navigates up with arrow key", async () => {
      mockStore.searchResults = [
        { id: 1, name: "Beef Steak", slug: "beef-steak", price: 25.99 },
        { id: 2, name: "Beef Mince", slug: "beef-mince", price: 15.99 },
      ];

      const wrapper = mountComponent();
      wrapper.vm.query = "beef";
      wrapper.vm.isOpen = true;
      await flushPromises();

      // First navigate down twice to get to index 1
      await wrapper.find("input").trigger("keydown", { key: "ArrowDown" });
      await wrapper.find("input").trigger("keydown", { key: "ArrowDown" });
      expect(wrapper.vm.selectedIndex).toBe(1);

      // Then navigate up
      await wrapper.find("input").trigger("keydown", { key: "ArrowUp" });

      expect(wrapper.vm.selectedIndex).toBe(0);
    });
  });

  describe("Product Selection", () => {
    it("clears search on product selection", async () => {
      mockStore.searchResults = [
        { id: 1, name: "Beef Steak", slug: "beef-steak", price: 25.99 },
      ];

      const wrapper = mountComponent();
      wrapper.vm.query = "beef";
      wrapper.vm.isOpen = true;
      await flushPromises();

      wrapper.vm.selectSuggestion(mockStore.searchResults[0]);

      expect(wrapper.vm.isOpen).toBe(false);
    });

    it("emits select event on product selection", async () => {
      mockStore.searchResults = [
        { id: 1, name: "Beef Steak", slug: "beef-steak", price: 25.99 },
      ];

      const wrapper = mountComponent();
      wrapper.vm.query = "beef";
      wrapper.vm.isOpen = true;
      await flushPromises();

      wrapper.vm.selectSuggestion(mockStore.searchResults[0]);

      expect(wrapper.emitted("select")).toBeTruthy();
      expect(wrapper.emitted("select")[0][0]).toEqual(
        mockStore.searchResults[0]
      );
    });
  });

  describe("Focus Behavior", () => {
    it("opens dropdown on focus when results exist", async () => {
      mockStore.searchResults = [
        { id: 1, name: "Beef Steak", slug: "beef-steak", price: 25.99 },
      ];

      const wrapper = mountComponent();
      wrapper.vm.query = "beef";

      await wrapper.find("input").trigger("focus");

      expect(wrapper.vm.isFocused).toBe(true);
    });

    it("closes dropdown on blur after delay", async () => {
      mockStore.searchResults = [
        { id: 1, name: "Beef Steak", slug: "beef-steak", price: 25.99 },
      ];

      const wrapper = mountComponent();
      wrapper.vm.isOpen = true;

      await wrapper.find("input").trigger("blur");
      vi.advanceTimersByTime(250);

      expect(wrapper.vm.isOpen).toBe(false);
    });
  });

  describe("Styling", () => {
    it("has rounded input", () => {
      const wrapper = mountComponent();
      expect(wrapper.find(".rounded-lg").exists()).toBe(true);
    });

    it("has border", () => {
      const wrapper = mountComponent();
      expect(wrapper.find(".border").exists()).toBe(true);
    });
  });

  describe("Clear Button", () => {
    it("shows clear button when query is not empty", async () => {
      const wrapper = mountComponent();
      const input = wrapper.find("input");

      await input.setValue("beef");
      await flushPromises();

      // Clear button should appear
      const clearButton = wrapper.find("button");
      expect(clearButton.exists()).toBe(true);
    });

    it("clears query on clear button click", async () => {
      const wrapper = mountComponent();
      wrapper.vm.query = "beef";
      await flushPromises();

      const clearButton = wrapper.find("button");
      await clearButton.trigger("click");

      expect(wrapper.vm.query).toBe("");
      expect(mockStore.clearSearch).toHaveBeenCalled();
    });

    it("hides clear button when query is empty", () => {
      const wrapper = mountComponent();

      // Clear button should not show when query is empty
      expect(wrapper.find("button").exists()).toBe(false);
    });
  });

  describe("Search Submission", () => {
    it("emits search event on enter without selection", async () => {
      const wrapper = mountComponent();
      wrapper.vm.query = "steak";
      wrapper.vm.selectedIndex = -1;

      await wrapper.find("input").trigger("keydown", { key: "Enter" });

      expect(wrapper.emitted("search")).toBeTruthy();
      expect(wrapper.emitted("search")[0][0]).toBe("steak");
    });
  });

  describe("View All Results", () => {
    it("shows view all results button when suggestions exist", async () => {
      mockStore.searchResults = [
        { id: 1, name: "Beef Steak", slug: "beef-steak", price: 25.99 },
      ];

      const wrapper = mountComponent();
      wrapper.vm.query = "beef";
      wrapper.vm.isOpen = true;
      await flushPromises();

      expect(wrapper.text()).toContain("View all results");
    });
  });
});
