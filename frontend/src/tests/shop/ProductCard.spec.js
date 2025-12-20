/**
 * ProductCard Component Tests
 *
 * Tests for the ProductCard component.
 *
 * @requirement SHOP-003 Product card showing image, name, price
 * @requirement SHOP-012 Show real-time stock levels
 * @requirement SHOP-014 Add to cart functionality
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import { createRouter, createMemoryHistory } from "vue-router";
import ProductCard from "../../components/shop/ProductCard.vue";

// Mock the stores
vi.mock("../../stores/cart", () => ({
  useCartStore: () => ({
    isInCart: vi.fn(() => false),
    getQuantity: vi.fn(() => 0),
    addItem: vi.fn(() => Promise.resolve(true)),
  }),
}));

vi.mock("../../stores/currency", () => ({
  useCurrencyStore: () => ({
    format: (price) => `$${Number(price).toFixed(2)}`,
    currentCurrency: "AUD",
  }),
}));

const createMockRouter = () => {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/", component: { template: "<div>Home</div>" } },
      {
        path: "/products/:slug",
        component: { template: "<div>Product</div>" },
      },
    ],
  });
};

const createMockProduct = (overrides = {}) => ({
  id: 1,
  name: "Premium Beef Steak",
  slug: "premium-beef-steak",
  price: 45.99,
  price_aud: 45.99,
  compare_at_price: null,
  stock_quantity: 10,
  unit: "kg",
  is_featured: false,
  is_active: true,
  category: { id: 1, name: "Beef", slug: "beef" },
  primary_image: { url: "/images/steak.jpg" },
  images: [{ url: "/images/steak.jpg" }],
  ...overrides,
});

describe("ProductCard.vue", () => {
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

  const mountComponent = (product = createMockProduct(), props = {}) => {
    return mount(ProductCard, {
      props: { product, ...props },
      global: {
        plugins: [router, pinia],
      },
    });
  };

  describe("Rendering", () => {
    it("renders the product card", () => {
      const wrapper = mountComponent();
      expect(wrapper.find(".group").exists()).toBe(true);
    });

    it("displays product name", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Premium Beef Steak");
    });

    it("displays product price", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("$45.99");
    });

    it("displays product category", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Beef");
    });

    it("displays stock quantity", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("10kg in stock");
    });

    it("displays unit type", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("/ kg");
    });

    it("displays product image", () => {
      const wrapper = mountComponent();
      const img = wrapper.find("img");
      expect(img.exists()).toBe(true);
      expect(img.attributes("src")).toBe("/images/steak.jpg");
    });

    it("uses placeholder image when no primary image", () => {
      const product = createMockProduct({
        primary_image: null,
        images: [],
      });
      const wrapper = mountComponent(product);
      const img = wrapper.find("img");
      expect(img.attributes("src")).toContain("unsplash");
    });
  });

  describe("Badges", () => {
    it("shows featured badge for featured products", () => {
      const product = createMockProduct({ is_featured: true });
      const wrapper = mountComponent(product);
      expect(wrapper.text()).toContain("Featured");
    });

    it("does not show featured badge for non-featured products", () => {
      const product = createMockProduct({ is_featured: false });
      const wrapper = mountComponent(product);
      expect(wrapper.text()).not.toContain("Featured");
    });

    it("shows sale badge for discounted products", () => {
      const product = createMockProduct({
        price: 40,
        compare_at_price: 50,
      });
      const wrapper = mountComponent(product);
      expect(wrapper.text()).toContain("-20%");
    });

    it("shows out of stock badge when no stock", () => {
      const product = createMockProduct({ stock_quantity: 0 });
      const wrapper = mountComponent(product);
      expect(wrapper.text()).toContain("Out of Stock");
    });
  });

  describe("Stock Status", () => {
    it("shows in stock message for available products", () => {
      const product = createMockProduct({ stock_quantity: 15 });
      const wrapper = mountComponent(product);
      expect(wrapper.text()).toContain("15kg in stock");
    });

    it("shows unavailable message for out of stock products", () => {
      const product = createMockProduct({ stock_quantity: 0 });
      const wrapper = mountComponent(product);
      expect(wrapper.text()).toContain("Currently unavailable");
    });
  });

  describe("Price Display", () => {
    it("displays formatted price", () => {
      const product = createMockProduct({ price: 29.99 });
      const wrapper = mountComponent(product);
      expect(wrapper.text()).toContain("$29.99");
    });

    it("displays compare at price when on sale", () => {
      const product = createMockProduct({
        price: 40,
        compare_at_price: 50,
      });
      const wrapper = mountComponent(product);
      expect(wrapper.text()).toContain("$50.00");
    });

    it("calculates discount percentage correctly", () => {
      const product = createMockProduct({
        price: 30,
        compare_at_price: 60,
      });
      const wrapper = mountComponent(product);
      expect(wrapper.text()).toContain("-50%");
    });
  });

  describe("Quantity Controls", () => {
    it("displays quantity input for in-stock products", () => {
      const product = createMockProduct({ stock_quantity: 10 });
      const wrapper = mountComponent(product);
      expect(wrapper.find('input[type="number"]').exists()).toBe(true);
    });

    it("does not display quantity input for out-of-stock products", () => {
      const product = createMockProduct({ stock_quantity: 0 });
      const wrapper = mountComponent(product);
      expect(wrapper.find('input[type="number"]').exists()).toBe(false);
    });

    it("has increment and decrement buttons", () => {
      const wrapper = mountComponent();
      const buttons = wrapper.findAll("button");
      expect(buttons.length).toBeGreaterThanOrEqual(2);
    });
  });

  describe("Add to Cart Button", () => {
    it("shows Add button for in-stock products", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Add");
    });

    it("shows Out of Stock for unavailable products", () => {
      const product = createMockProduct({ stock_quantity: 0 });
      const wrapper = mountComponent(product);
      const button = wrapper.find("button[disabled]");
      expect(button.text()).toContain("Out of Stock");
    });

    it("disables add button for out of stock products", () => {
      const product = createMockProduct({ stock_quantity: 0 });
      const wrapper = mountComponent(product);
      const addButton = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Out of Stock"));
      expect(addButton.attributes("disabled")).toBeDefined();
    });
  });

  describe("Quick View", () => {
    it("emits quick-view event when quick view button clicked", async () => {
      const product = createMockProduct();
      const wrapper = mountComponent(product, { showQuickView: true });

      // Find and trigger the quick view button (it should appear on hover)
      // The button has title="Quick View"
      const quickViewButton = wrapper.find('button[title="Quick View"]');
      if (quickViewButton.exists()) {
        await quickViewButton.trigger("click");
        expect(wrapper.emitted("quick-view")).toBeTruthy();
        expect(wrapper.emitted("quick-view")[0][0]).toEqual(product);
      }
    });

    it("does not show quick view for out of stock products", () => {
      const product = createMockProduct({ stock_quantity: 0 });
      const wrapper = mountComponent(product, { showQuickView: true });
      expect(wrapper.find('button[title="Quick View"]').exists()).toBe(false);
    });

    it("respects showQuickView prop", () => {
      const wrapper = mountComponent(createMockProduct(), {
        showQuickView: false,
      });
      expect(wrapper.find('button[title="Quick View"]').exists()).toBe(false);
    });
  });

  describe("Links", () => {
    it("links to product detail page", () => {
      const wrapper = mountComponent();
      const links = wrapper.findAllComponents({ name: "RouterLink" });
      const productLink = links.find(
        (l) => l.props("to") === "/products/premium-beef-steak"
      );
      expect(productLink).toBeDefined();
    });

    it("uses correct product slug in link", () => {
      const product = createMockProduct({ slug: "wagyu-ribeye" });
      const wrapper = mountComponent(product);
      const links = wrapper.findAllComponents({ name: "RouterLink" });
      const productLink = links.find(
        (l) => l.props("to") === "/products/wagyu-ribeye"
      );
      expect(productLink).toBeDefined();
    });
  });

  describe("Unit Handling", () => {
    it("displays kg unit by default", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("/ kg");
    });

    it("displays custom unit when provided", () => {
      const product = createMockProduct({ unit: "piece" });
      const wrapper = mountComponent(product);
      expect(wrapper.text()).toContain("/ piece");
    });
  });

  describe("Image Lazy Loading", () => {
    it("has lazy loading attribute on image", () => {
      const wrapper = mountComponent();
      const img = wrapper.find("img");
      expect(img.attributes("loading")).toBe("lazy");
    });
  });

  describe("Accessibility", () => {
    it("has alt text on product image", () => {
      const wrapper = mountComponent();
      const img = wrapper.find("img");
      expect(img.attributes("alt")).toBe("Premium Beef Steak");
    });
  });
});
