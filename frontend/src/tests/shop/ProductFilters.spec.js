/**
 * ProductFilters Component Tests
 *
 * Tests for the ProductFilters component.
 *
 * @requirement SHOP-005 Implement price range filter
 * @requirement SHOP-006 Implement sort functionality
 * @requirement SHOP-007 Implement "In Stock Only" filter
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import ProductFilters from "../../components/shop/ProductFilters.vue";

describe("ProductFilters.vue", () => {
  let pinia;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    vi.clearAllMocks();
  });

  const defaultFilters = {
    minPrice: null,
    maxPrice: null,
    inStock: null,
    sort: "created_at",
    direction: "desc",
  };

  const mountComponent = (props = {}) => {
    return mount(ProductFilters, {
      props: {
        filters: { ...defaultFilters },
        isOpen: false,
        ...props,
      },
      global: {
        plugins: [pinia],
        stubs: {
          Teleport: true,
        },
      },
    });
  };

  describe("Rendering", () => {
    it("renders the desktop filters", () => {
      const wrapper = mountComponent();
      expect(wrapper.find(".lg\\:block").exists()).toBe(true);
    });

    it("displays Filters heading", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Filters");
    });

    it("displays Price Range section", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Price Range");
    });

    it("displays Availability section", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Availability");
    });

    it("displays Sort By section", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Sort By");
    });
  });

  describe("Price Range Inputs", () => {
    it("has min price input", () => {
      const wrapper = mountComponent();
      const inputs = wrapper.findAll('input[type="number"]');
      expect(inputs.length).toBeGreaterThanOrEqual(2);
    });

    it("has max price input", () => {
      const wrapper = mountComponent();
      const inputs = wrapper.findAll('input[type="number"]');
      const maxInput = inputs.find(
        (i) => i.attributes("placeholder") === "Max"
      );
      expect(maxInput).toBeDefined();
    });

    it("shows dollar signs in price inputs", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("$");
    });

    it("updates min price on input", async () => {
      const wrapper = mountComponent();
      const inputs = wrapper.findAll('input[type="number"]');
      const minInput = inputs.find(
        (i) => i.attributes("placeholder") === "Min"
      );

      await minInput.setValue(20);

      expect(wrapper.vm.localFilters.minPrice).toBe(20);
    });

    it("updates max price on input", async () => {
      const wrapper = mountComponent();
      const inputs = wrapper.findAll('input[type="number"]');
      const maxInput = inputs.find(
        (i) => i.attributes("placeholder") === "Max"
      );

      await maxInput.setValue(100);

      expect(wrapper.vm.localFilters.maxPrice).toBe(100);
    });
  });

  describe("Availability Filter", () => {
    it("has In Stock Only checkbox", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("In Stock Only");
    });

    it("checkbox is unchecked by default", () => {
      const wrapper = mountComponent();
      const checkbox = wrapper.find('input[type="checkbox"]');
      expect(checkbox.element.checked).toBe(false);
    });

    it("updates inStock filter on checkbox change", async () => {
      const wrapper = mountComponent();
      const checkbox = wrapper.find('input[type="checkbox"]');

      await checkbox.setValue(true);

      expect(wrapper.vm.localFilters.inStock).toBe(true);
    });
  });

  describe("Sort Options", () => {
    it("has sort select dropdown", () => {
      const wrapper = mountComponent();
      expect(wrapper.find("select").exists()).toBe(true);
    });

    it("has all sort options", () => {
      const wrapper = mountComponent();
      const options = wrapper.findAll("option");

      expect(options.some((o) => o.text() === "Newest First")).toBe(true);
      expect(options.some((o) => o.text() === "Oldest First")).toBe(true);
      expect(options.some((o) => o.text() === "Price: Low to High")).toBe(true);
      expect(options.some((o) => o.text() === "Price: High to Low")).toBe(true);
      expect(options.some((o) => o.text() === "Name: A to Z")).toBe(true);
      expect(options.some((o) => o.text() === "Name: Z to A")).toBe(true);
    });

    it("defaults to Newest First", () => {
      const wrapper = mountComponent();
      expect(wrapper.vm.currentSort).toBe("created_at:desc");
    });

    it("updates sort on change", async () => {
      const wrapper = mountComponent();
      const select = wrapper.find("select");

      await select.setValue("price:asc");

      expect(wrapper.vm.localFilters.sort).toBe("price");
      expect(wrapper.vm.localFilters.direction).toBe("asc");
    });
  });

  describe("Apply Button", () => {
    it("has Apply button", () => {
      const wrapper = mountComponent();
      const buttons = wrapper.findAll("button");
      const applyButton = buttons.find((b) => b.text() === "Apply");
      expect(applyButton).toBeDefined();
    });

    it("emits update event on Apply", async () => {
      const wrapper = mountComponent();

      // Set some filter values
      wrapper.vm.localFilters.minPrice = 10;
      wrapper.vm.localFilters.maxPrice = 50;

      const buttons = wrapper.findAll("button");
      const applyButton = buttons.find((b) => b.text() === "Apply");

      await applyButton.trigger("click");

      expect(wrapper.emitted("update")).toBeTruthy();
      expect(wrapper.emitted("update")[0][0]).toEqual(
        expect.objectContaining({
          minPrice: 10,
          maxPrice: 50,
        })
      );
    });
  });

  describe("Reset Button", () => {
    it("has Reset button", () => {
      const wrapper = mountComponent();
      const buttons = wrapper.findAll("button");
      const resetButton = buttons.find((b) => b.text() === "Reset");
      expect(resetButton).toBeDefined();
    });

    it("resets filters on Reset click", async () => {
      const wrapper = mountComponent({
        filters: {
          minPrice: 10,
          maxPrice: 100,
          inStock: true,
          sort: "price",
          direction: "asc",
        },
      });

      const buttons = wrapper.findAll("button");
      const resetButton = buttons.find((b) => b.text() === "Reset");

      await resetButton.trigger("click");

      expect(wrapper.emitted("update")).toBeTruthy();
      expect(wrapper.emitted("update")[0][0]).toEqual({
        minPrice: null,
        maxPrice: null,
        inStock: null,
        sort: "created_at",
        direction: "desc",
      });
    });
  });

  describe("Props Reactivity", () => {
    it("updates local filters when props change", async () => {
      const wrapper = mountComponent();

      await wrapper.setProps({
        filters: {
          minPrice: 25,
          maxPrice: 75,
          inStock: true,
          sort: "name",
          direction: "asc",
        },
      });

      expect(wrapper.vm.localFilters.minPrice).toBe(25);
      expect(wrapper.vm.localFilters.maxPrice).toBe(75);
      expect(wrapper.vm.localFilters.inStock).toBe(true);
    });
  });

  describe("Computed Sort Value", () => {
    it("computes current sort correctly", () => {
      const wrapper = mountComponent({
        filters: {
          ...defaultFilters,
          sort: "price",
          direction: "desc",
        },
      });

      expect(wrapper.vm.currentSort).toBe("price:desc");
    });

    it("sets sort and direction from combined value", async () => {
      const wrapper = mountComponent();

      wrapper.vm.currentSort = "name:asc";

      expect(wrapper.vm.localFilters.sort).toBe("name");
      expect(wrapper.vm.localFilters.direction).toBe("asc");
    });
  });

  describe("Sort Change Handler", () => {
    it("applies filters immediately on sort change", async () => {
      const wrapper = mountComponent();
      const select = wrapper.find("select");

      await select.setValue("price:desc");
      // handleSortChange should trigger applyFilters

      expect(wrapper.emitted("update")).toBeTruthy();
    });
  });

  describe("Styling", () => {
    it("has rounded corners", () => {
      const wrapper = mountComponent();
      expect(wrapper.find(".rounded-lg").exists()).toBe(true);
    });

    it("has shadow and border", () => {
      const wrapper = mountComponent();
      expect(wrapper.find(".shadow-sm").exists()).toBe(true);
      expect(wrapper.find(".border").exists()).toBe(true);
    });

    it("Apply button has primary styling", () => {
      const wrapper = mountComponent();
      const buttons = wrapper.findAll("button");
      const applyButton = buttons.find((b) => b.text() === "Apply");
      expect(applyButton.classes()).toContain("bg-primary-600");
    });
  });

  describe("Input Validation", () => {
    it("min price input has min=0", () => {
      const wrapper = mountComponent();
      const inputs = wrapper.findAll('input[type="number"]');
      const minInput = inputs.find(
        (i) => i.attributes("placeholder") === "Min"
      );
      expect(minInput.attributes("min")).toBe("0");
    });

    it("max price input has min=0", () => {
      const wrapper = mountComponent();
      const inputs = wrapper.findAll('input[type="number"]');
      const maxInput = inputs.find(
        (i) => i.attributes("placeholder") === "Max"
      );
      expect(maxInput.attributes("min")).toBe("0");
    });
  });
});
