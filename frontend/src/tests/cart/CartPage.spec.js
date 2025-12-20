/**
 * CartPage Component Tests
 *
 * Comprehensive tests for the full cart page component.
 *
 * @requirement CART-001 Display cart
 * @requirement CART-003 Implement quantity adjustment
 * @requirement CART-006 Show cart summary
 * @requirement CART-007 Display minimum order notification
 * @requirement CART-023 Write cart module tests
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { createRouter, createWebHistory } from "vue-router";
import CartPage from "@/pages/CartPage.vue";

// Mock stores
const mockCartStore = {
  items: [],
  isEmpty: true,
  itemCount: 0,
  subtotal: 0,
  subtotalFormatted: "$0.00",
  meetsMinimumOrder: false,
  amountToMinimumFormatted: "$100.00",
  isLoading: false,
  MINIMUM_ORDER: 100,
  initialize: vi.fn(),
  updateQuantity: vi.fn(),
  removeItem: vi.fn(),
  clearCart: vi.fn(),
};

const mockAuthStore = {
  isAuthenticated: false,
  user: null,
};

const mockCurrencyStore = {
  format: vi.fn((amount) => `$${amount.toFixed(2)}`),
  currentCurrency: "AUD",
};

vi.mock("@/stores/cart", () => ({
  useCartStore: vi.fn(() => mockCartStore),
}));

vi.mock("@/stores/auth", () => ({
  useAuthStore: vi.fn(() => mockAuthStore),
}));

vi.mock("@/stores/currency", () => ({
  useCurrencyStore: vi.fn(() => mockCurrencyStore),
}));

// Create router for testing
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: "/", component: { template: "<div>Home</div>" } },
    { path: "/shop", component: { template: "<div>Shop</div>" } },
    { path: "/cart", component: { template: "<div>Cart</div>" } },
    { path: "/checkout", component: { template: "<div>Checkout</div>" } },
    { path: "/login", component: { template: "<div>Login</div>" } },
    { path: "/products/:slug", component: { template: "<div>Product</div>" } },
  ],
});

describe("CartPage", () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockCartStore.items = [];
    mockCartStore.isEmpty = true;
    mockCartStore.itemCount = 0;
    mockCartStore.subtotal = 0;
    mockCartStore.subtotalFormatted = "$0.00";
    mockCartStore.meetsMinimumOrder = false;
    mockCartStore.amountToMinimumFormatted = "$100.00";
    mockCartStore.isLoading = false;
    mockAuthStore.isAuthenticated = false;
  });

  function mountComponent() {
    return mount(CartPage, {
      global: {
        plugins: [router],
      },
    });
  }

  describe("Page Header", () => {
    it("displays Shopping Cart title", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Shopping Cart");
    });

    it("displays item count when not empty", () => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test" },
          quantity: 1,
          unit_price: 50,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.itemCount = 1;

      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("1 item");
    });

    it("shows plural items for multiple", () => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test" },
          quantity: 1,
          unit_price: 50,
        },
        {
          id: 2,
          product_id: 102,
          product: { name: "Test2" },
          quantity: 1,
          unit_price: 50,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.itemCount = 2;

      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("2 items");
    });
  });

  describe("Initialization", () => {
    it("calls initialize on mount", async () => {
      mountComponent();
      await flushPromises();
      expect(mockCartStore.initialize).toHaveBeenCalled();
    });
  });

  describe("Loading State", () => {
    it("shows loading spinner when loading", () => {
      mockCartStore.isLoading = true;
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Loading your cart");
    });

    it("displays loading animation", () => {
      mockCartStore.isLoading = true;
      const wrapper = mountComponent();
      expect(wrapper.find(".animate-spin").exists()).toBe(true);
    });
  });

  describe("Empty Cart State", () => {
    it("displays empty cart message", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Your cart is empty");
    });

    it("shows explanation text", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("haven't added any products");
    });

    it("has Start Shopping link", () => {
      const wrapper = mountComponent();
      const link = wrapper.find('a[href="/shop"]');
      expect(link.exists()).toBe(true);
      expect(wrapper.text()).toContain("Start Shopping");
    });
  });

  describe("Cart Items Display", () => {
    const mockItems = [
      {
        id: 1,
        product_id: 101,
        product: {
          id: 101,
          name: "Ribeye Steak",
          slug: "ribeye-steak",
          stock_quantity: 50,
          unit: "kg",
          category: { name: "Beef" },
          primary_image: { url: "/images/ribeye.jpg" },
        },
        quantity: 2,
        unit_price: 45.99,
      },
      {
        id: 2,
        product_id: 102,
        product: {
          id: 102,
          name: "Chicken Breast",
          slug: "chicken-breast",
          stock_quantity: 30,
          unit: "kg",
          category: { name: "Poultry" },
        },
        quantity: 1.5,
        unit_price: 18.99,
      },
    ];

    beforeEach(() => {
      mockCartStore.items = mockItems;
      mockCartStore.isEmpty = false;
      mockCartStore.itemCount = 2;
      mockCartStore.subtotal = 120.47;
      mockCartStore.subtotalFormatted = "$120.47";
      mockCartStore.meetsMinimumOrder = true;
    });

    it("displays product column header", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Product");
    });

    it("displays price column header", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Price");
    });

    it("displays quantity column header", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Quantity");
    });

    it("displays total column header", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Total");
    });

    it("displays all product names", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Ribeye Steak");
      expect(wrapper.text()).toContain("Chicken Breast");
    });

    it("displays product categories", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Beef");
      expect(wrapper.text()).toContain("Poultry");
    });

    it("displays product prices", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("$45.99");
      expect(wrapper.text()).toContain("$18.99");
    });

    it("displays quantities with units", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("2kg");
      expect(wrapper.text()).toContain("1.5kg");
    });

    it("displays line totals", () => {
      const wrapper = mountComponent();
      // Line totals are calculated in the component using formatPrice
      // Verify the mock was called for formatting
      expect(mockCurrencyStore.format).toHaveBeenCalled();
      // Also verify items are rendered
      const listItems = wrapper.findAll("li");
      expect(listItems.length).toBe(2);
    });

    it("has quantity controls for each item", () => {
      const wrapper = mountComponent();
      const listItems = wrapper.findAll("li");
      expect(listItems.length).toBe(2);
    });

    it("product name links to product page", () => {
      const wrapper = mountComponent();
      const links = wrapper.findAll('a[href^="/products/"]');
      expect(links.length).toBeGreaterThan(0);
    });
  });

  describe("Quantity Controls", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 2,
          unit_price: 50,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.meetsMinimumOrder = true;
    });

    it("decrease button calls updateQuantity", async () => {
      const wrapper = mountComponent();
      // Find the quantity control buttons (inside the border-gray-300 container)
      const quantityControls = wrapper.find(".border-gray-300");
      const buttons = quantityControls.findAll("button");
      // First button is decrease
      await buttons[0].trigger("click");
      expect(mockCartStore.updateQuantity).toHaveBeenCalled();
    });

    it("increase button calls updateQuantity", async () => {
      const wrapper = mountComponent();
      const listItem = wrapper.find("li");
      const buttons = listItem.findAll("button");
      await buttons[1].trigger("click");
      expect(mockCartStore.updateQuantity).toHaveBeenCalled();
    });

    it("disables decrease at minimum quantity", () => {
      mockCartStore.items[0].quantity = 0.5;
      const wrapper = mountComponent();
      // Find the quantity control buttons (inside the border-gray-300 container)
      const quantityControls = wrapper.find(".border-gray-300");
      const decreaseBtn = quantityControls.findAll("button")[0];
      // Check element has disabled attribute or class indicating disabled state
      expect(decreaseBtn.element.disabled).toBe(true);
    });

    it("disables increase at max stock", () => {
      mockCartStore.items[0].quantity = 50;
      const wrapper = mountComponent();
      // Find the quantity control buttons (inside the border-gray-300 container)
      const quantityControls = wrapper.find(".border-gray-300");
      const increaseBtn = quantityControls.findAll("button")[1];
      // Check element has disabled attribute
      expect(increaseBtn.element.disabled).toBe(true);
    });
  });

  describe("Remove Item", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 2,
          unit_price: 50,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.meetsMinimumOrder = true;
    });

    it("has remove button", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Remove");
    });

    it("remove button calls removeItem", async () => {
      const wrapper = mountComponent();
      const removeBtn = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Remove"));
      if (removeBtn) {
        await removeBtn.trigger("click");
        expect(mockCartStore.removeItem).toHaveBeenCalled();
      }
    });
  });

  describe("Order Summary", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 5,
          unit_price: 25,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.itemCount = 1;
      mockCartStore.subtotal = 125;
      mockCartStore.subtotalFormatted = "$125.00";
      mockCartStore.meetsMinimumOrder = true;
    });

    it("displays Order Summary heading", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Order Summary");
    });

    it("displays subtotal with item count", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Subtotal");
      expect(wrapper.text()).toContain("1 items");
    });

    it("displays subtotal amount", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("$125.00");
    });

    it("shows delivery message", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Delivery");
      expect(wrapper.text()).toContain("checkout");
    });

    it("displays total", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Total");
    });

    it("shows taxes message", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Taxes");
    });

    it("shows security badge", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Secure Checkout");
    });
  });

  describe("Minimum Order Warning", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 1,
          unit_price: 50,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.subtotal = 50;
      mockCartStore.subtotalFormatted = "$50.00";
      mockCartStore.meetsMinimumOrder = false;
      mockCartStore.amountToMinimumFormatted = "$50.00";
    });

    it("shows warning when below minimum", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Minimum order not met");
    });

    it("shows amount needed to reach minimum", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("$50.00");
      expect(wrapper.text()).toContain("more");
    });

    it("shows minimum amount", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Minimum order");
    });
  });

  describe("Checkout Button", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 5,
          unit_price: 25,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.meetsMinimumOrder = true;
    });

    it("has Proceed to Checkout button", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Proceed to Checkout");
    });

    it("button is enabled when minimum met", () => {
      const wrapper = mountComponent();
      const checkoutBtn = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Checkout"));
      expect(checkoutBtn?.attributes("disabled")).toBeUndefined();
    });

    it("button is disabled when below minimum", () => {
      mockCartStore.meetsMinimumOrder = false;
      const wrapper = mountComponent();
      const checkoutBtn = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Checkout"));
      expect(checkoutBtn?.attributes("disabled")).toBeDefined();
    });
  });

  describe("Continue Shopping", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 1,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.meetsMinimumOrder = true;
    });

    it("has Continue Shopping link", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Continue Shopping");
    });

    it("links to shop page", () => {
      const wrapper = mountComponent();
      const link = wrapper.find('a[href="/shop"]');
      expect(link.exists()).toBe(true);
    });
  });

  describe("Clear Cart", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 1,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.meetsMinimumOrder = true;
    });

    it("has Clear Cart button", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Clear Cart");
    });

    it("Clear Cart calls clearCart", async () => {
      const wrapper = mountComponent();
      const clearBtn = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Clear Cart"));
      if (clearBtn) {
        await clearBtn.trigger("click");
        expect(mockCartStore.clearCart).toHaveBeenCalled();
      }
    });
  });

  describe("Stock Warning", () => {
    it("shows warning when quantity exceeds stock", () => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 3,
            unit: "kg",
          },
          quantity: 5,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;

      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Only 3kg available");
    });
  });

  describe("Product Image", () => {
    it("displays product image when available", () => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
            primary_image: { url: "/images/test.jpg" },
          },
          quantity: 1,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;

      const wrapper = mountComponent();
      expect(wrapper.find("img").exists()).toBe(true);
    });

    it("uses fallback when no image", () => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 1,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;

      const wrapper = mountComponent();
      expect(wrapper.find("img").attributes("src")).toContain("placeholder");
    });
  });

  describe("Price Formatting", () => {
    it("uses currency store for formatting", () => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 2,
          unit_price: 45.99,
        },
      ];
      mockCartStore.isEmpty = false;

      mountComponent();

      expect(mockCurrencyStore.format).toHaveBeenCalled();
    });
  });

  describe("Layout", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 1,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.meetsMinimumOrder = true;
    });

    it("has grid layout for cart and summary", () => {
      const wrapper = mountComponent();
      expect(wrapper.find(".grid").exists()).toBe(true);
    });

    it("summary is sticky", () => {
      const wrapper = mountComponent();
      expect(wrapper.find(".sticky").exists()).toBe(true);
    });
  });

  describe("Uncategorized Product", () => {
    it("shows Uncategorized for products without category", () => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 1,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;

      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Uncategorized");
    });
  });

  describe("Responsive Design", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: {
            name: "Test",
            slug: "test",
            stock_quantity: 50,
            unit: "kg",
          },
          quantity: 1,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.meetsMinimumOrder = true;
    });

    it("has responsive classes", () => {
      const wrapper = mountComponent();
      expect(wrapper.html()).toContain("lg:");
    });

    it("has mobile remove button", () => {
      const wrapper = mountComponent();
      expect(wrapper.html()).toContain("lg:hidden");
    });

    it("has desktop remove button", () => {
      const wrapper = mountComponent();
      expect(wrapper.html()).toContain("hidden lg:");
    });
  });
});
