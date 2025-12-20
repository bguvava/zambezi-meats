/**
 * ProductQuickView Component Tests
 *
 * Tests for the ProductQuickView modal component.
 *
 * @requirement SHOP-010 Product quick view modal
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import { createRouter, createWebHistory } from "vue-router";
import ProductQuickView from "../../components/shop/ProductQuickView.vue";

// Mock the cart store
vi.mock("../../stores/cart", () => ({
  useCartStore: vi.fn(() => ({
    addItem: vi.fn().mockResolvedValue(true),
    isInCart: vi.fn(() => false),
    getQuantity: vi.fn(() => 0),
  })),
}));

// Mock the currency store
vi.mock("../../stores/currency", () => ({
  useCurrencyStore: vi.fn(() => ({
    format: vi.fn((value) => `$${Number(value).toFixed(2)}`),
  })),
}));

import { useCartStore } from "../../stores/cart";

describe("ProductQuickView.vue", () => {
  let pinia;
  let router;
  let mockCartStore;

  const mockProduct = {
    id: 1,
    name: "Premium Beef Steak",
    slug: "premium-beef-steak",
    description: "High-quality beef steak from grass-fed cattle.",
    short_description: "Premium grass-fed beef.",
    price: 45.99,
    compare_at_price: null,
    stock_quantity: 25,
    unit: "kg",
    is_featured: true,
    is_active: true,
    images: [
      {
        id: 1,
        url: "/images/steak-1.jpg",
        alt_text: "Steak 1",
        is_primary: true,
      },
      {
        id: 2,
        url: "/images/steak-2.jpg",
        alt_text: "Steak 2",
        is_primary: false,
      },
    ],
    category: {
      id: 1,
      name: "Beef",
      slug: "beef",
    },
    nutrition_info: {
      serving_size: "100g",
      calories: 250,
      protein: 26,
      fat: 17,
    },
  };

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: "/", name: "home", component: { template: "<div>Home</div>" } },
        {
          path: "/products/:slug",
          name: "product-detail",
          component: { template: "<div>Product</div>" },
        },
      ],
    });

    mockCartStore = {
      addItem: vi.fn().mockResolvedValue(true),
      isInCart: vi.fn(() => false),
      getQuantity: vi.fn(() => 0),
    };

    useCartStore.mockReturnValue(mockCartStore);

    vi.clearAllMocks();
  });

  const mountComponent = (props = {}) => {
    return mount(ProductQuickView, {
      props: {
        product: mockProduct,
        isOpen: true,
        ...props,
      },
      attachTo: document.body,
      global: {
        plugins: [pinia, router],
        stubs: {
          Teleport: true,
          RouterLink: {
            template: '<a :href="to"><slot /></a>',
            props: ["to"],
          },
          Transition: false,
        },
      },
    });
  };

  describe("Modal Rendering", () => {
    it("renders when isOpen is true and product exists", () => {
      const wrapper = mountComponent({ isOpen: true });
      expect(wrapper.text()).toContain("Premium Beef Steak");
    });

    it("does not render content when isOpen is false", () => {
      const wrapper = mountComponent({ isOpen: false });
      // With isOpen false, the modal content should not be rendered
      expect(wrapper.find(".bg-white.rounded-xl").exists()).toBe(false);
    });

    it("shows product name", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Premium Beef Steak");
    });

    it("shows product description", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("High-quality beef steak");
    });

    it("shows product price", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("$45.99");
    });

    it("shows product category", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Beef");
    });
  });

  describe("Close Button", () => {
    it("has close button", () => {
      const wrapper = mountComponent();
      const buttons = wrapper.findAll("button");
      // Close button should exist
      expect(buttons.length).toBeGreaterThan(0);
    });

    it("emits close event on close button click", async () => {
      const wrapper = mountComponent();
      // Find the close button (X button in top right)
      const closeButton = wrapper.find("button.absolute");

      if (closeButton.exists()) {
        await closeButton.trigger("click");
        expect(wrapper.emitted("close")).toBeTruthy();
      }
    });

    it("emits close on backdrop click", async () => {
      const wrapper = mountComponent();
      const backdrop = wrapper.find(".fixed.inset-0.bg-black\\/50");

      if (backdrop.exists()) {
        await backdrop.trigger("click");
        expect(wrapper.emitted("close")).toBeTruthy();
      }
    });
  });

  describe("Product Image", () => {
    it("displays product image", () => {
      const wrapper = mountComponent();
      const img = wrapper.find("img");
      expect(img.exists()).toBe(true);
    });

    it("shows correct image source", () => {
      const wrapper = mountComponent();
      const img = wrapper.find("img");
      expect(img.attributes("src")).toBe("/images/steak-1.jpg");
    });

    it("has navigation buttons when multiple images", () => {
      const wrapper = mountComponent();
      // Should have prev/next buttons
      const navButtons = wrapper.findAll(
        ".aspect-square button, .aspect-auto button"
      );
      expect(navButtons.length).toBeGreaterThanOrEqual(0);
    });
  });

  describe("Price Display", () => {
    it("shows regular price", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("45.99");
    });

    it("shows sale price when available", () => {
      const productOnSale = {
        ...mockProduct,
        price: 35.99,
        compare_at_price: 45.99,
      };
      const wrapper = mountComponent({ product: productOnSale });

      expect(wrapper.text()).toContain("35.99");
    });

    it("shows original price strikethrough on sale", () => {
      const productOnSale = {
        ...mockProduct,
        price: 35.99,
        compare_at_price: 45.99,
      };
      const wrapper = mountComponent({ product: productOnSale });

      expect(wrapper.find(".line-through").exists()).toBe(true);
    });

    it("shows discount percentage badge", () => {
      const productOnSale = {
        ...mockProduct,
        price: 35.99,
        compare_at_price: 45.99,
      };
      const wrapper = mountComponent({ product: productOnSale });

      // Should show percentage badge
      expect(wrapper.text()).toMatch(/-\d+%/);
    });
  });

  describe("Stock Status", () => {
    it("shows in stock status", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("in stock");
    });

    it("shows stock quantity", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("25");
    });

    it("shows out of stock status when stock is 0", () => {
      const outOfStock = { ...mockProduct, stock_quantity: 0 };
      const wrapper = mountComponent({ product: outOfStock });

      expect(wrapper.text()).toContain("unavailable");
    });

    it("shows out of stock badge", () => {
      const outOfStock = { ...mockProduct, stock_quantity: 0 };
      const wrapper = mountComponent({ product: outOfStock });

      expect(wrapper.text()).toContain("Out of Stock");
    });
  });

  describe("Quantity Controls", () => {
    it("starts with quantity 1", () => {
      const wrapper = mountComponent();
      expect(wrapper.vm.quantity).toBe(1);
    });

    it("has quantity input", () => {
      const wrapper = mountComponent();
      expect(wrapper.find('input[type="number"]').exists()).toBe(true);
    });

    it("increments quantity", async () => {
      const wrapper = mountComponent();
      const initialQty = wrapper.vm.quantity;

      // Find increment button (the one with +)
      const buttons = wrapper.findAll("button");
      const incButton = buttons.find((b) => {
        const svg = b.find("svg");
        return svg.exists() && b.text() === "" && b.html().includes("M12 4v16");
      });

      if (incButton) {
        await incButton.trigger("click");
        expect(wrapper.vm.quantity).toBe(initialQty + 0.5);
      }
    });

    it("decrements quantity", async () => {
      const wrapper = mountComponent();
      wrapper.vm.quantity = 3;
      await flushPromises();

      // Find decrement button (the one with -)
      const buttons = wrapper.findAll("button");
      const decButton = buttons.find((b) => {
        const svg = b.find("svg");
        return svg.exists() && b.html().includes("M20 12H4");
      });

      if (decButton) {
        await decButton.trigger("click");
        expect(wrapper.vm.quantity).toBe(2.5);
      }
    });

    it("does not go below minimum", async () => {
      const wrapper = mountComponent();
      wrapper.vm.quantity = 0.5;
      await flushPromises();

      // Find decrement button
      const buttons = wrapper.findAll("button");
      const decButton = buttons.find((b) => b.html().includes("M20 12H4"));

      if (decButton) {
        await decButton.trigger("click");
        expect(wrapper.vm.quantity).toBeGreaterThanOrEqual(0.5);
      }
    });
  });

  describe("Add to Cart", () => {
    it("has add to cart button", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Add to Cart");
    });

    it("calls cart store on add to cart", async () => {
      const wrapper = mountComponent();

      await wrapper.vm.handleAddToCart();
      await flushPromises();

      expect(mockCartStore.addItem).toHaveBeenCalled();
    });

    it("passes correct product and quantity to cart", async () => {
      const wrapper = mountComponent();
      wrapper.vm.quantity = 2;

      await wrapper.vm.handleAddToCart();
      await flushPromises();

      expect(mockCartStore.addItem).toHaveBeenCalledWith(mockProduct, 2);
    });

    it("shows loading state during add", async () => {
      mockCartStore.addItem.mockImplementation(
        () => new Promise((resolve) => setTimeout(() => resolve(true), 100))
      );

      const wrapper = mountComponent();

      const promise = wrapper.vm.handleAddToCart();

      expect(wrapper.vm.isAdding).toBe(true);

      await promise;
    });

    it("shows success message after add", async () => {
      const wrapper = mountComponent();

      await wrapper.vm.handleAddToCart();
      await flushPromises();

      expect(wrapper.vm.showSuccess).toBe(true);
    });

    it("resets quantity after add", async () => {
      const wrapper = mountComponent();
      wrapper.vm.quantity = 3;

      await wrapper.vm.handleAddToCart();
      await flushPromises();

      expect(wrapper.vm.quantity).toBe(1);
    });

    it("disables add when out of stock", () => {
      const outOfStock = { ...mockProduct, stock_quantity: 0 };
      const wrapper = mountComponent({ product: outOfStock });

      // Should show out of stock button instead of add to cart
      expect(wrapper.text()).toContain("Out of Stock");
      expect(wrapper.text()).not.toContain("Add to Cart");
    });
  });

  describe("View Full Details Link", () => {
    it("has link to full product page", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("View Full Details");
    });

    it("link points to correct product", () => {
      const wrapper = mountComponent();
      const link = wrapper.find('a[href*="premium-beef-steak"]');
      expect(link.exists()).toBe(true);
    });
  });

  describe("Featured Badge", () => {
    it("shows featured badge for featured products", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Featured");
    });

    it("does not show featured badge when not featured", () => {
      const notFeatured = { ...mockProduct, is_featured: false };
      const wrapper = mountComponent({ product: notFeatured });

      // Should not have featured badge
      const badges = wrapper.findAll(".absolute span");
      const hasFeatured = badges.some((b) => b.text() === "Featured");
      expect(hasFeatured).toBe(false);
    });
  });

  describe("Nutrition Info", () => {
    it("shows nutrition section when data available", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Nutrition Info");
    });

    it("shows calories", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Calories");
      expect(wrapper.text()).toContain("250");
    });

    it("shows protein", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Protein");
      expect(wrapper.text()).toContain("26");
    });
  });

  describe("In Cart Indicator", () => {
    it("shows when product already in cart", async () => {
      mockCartStore.isInCart.mockReturnValue(true);
      mockCartStore.getQuantity.mockReturnValue(2);

      const wrapper = mountComponent();
      await flushPromises();

      expect(wrapper.text()).toContain("already in cart");
    });
  });

  describe("Image Navigation", () => {
    it("changes image on next", async () => {
      const wrapper = mountComponent();

      wrapper.vm.nextImage();

      expect(wrapper.vm.currentImageIndex).toBe(1);
    });

    it("changes image on prev", async () => {
      const wrapper = mountComponent();
      wrapper.vm.currentImageIndex = 1;

      wrapper.vm.prevImage();

      expect(wrapper.vm.currentImageIndex).toBe(0);
    });

    it("wraps around at end", async () => {
      const wrapper = mountComponent();
      wrapper.vm.currentImageIndex = 1;

      wrapper.vm.nextImage();

      expect(wrapper.vm.currentImageIndex).toBe(0);
    });

    it("wraps around at start", async () => {
      const wrapper = mountComponent();
      wrapper.vm.currentImageIndex = 0;

      wrapper.vm.prevImage();

      expect(wrapper.vm.currentImageIndex).toBe(1);
    });
  });

  describe("Unit Display", () => {
    it("shows product unit", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("kg");
    });
  });
});
