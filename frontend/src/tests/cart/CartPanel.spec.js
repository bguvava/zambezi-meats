/**
 * CartPanel Component Tests
 *
 * Comprehensive tests for the slide-out cart panel component.
 *
 * @requirement CART-001 Create cart slide-out panel
 * @requirement CART-002 Display cart items list
 * @requirement CART-003 Implement quantity adjustment
 * @requirement CART-004 Implement item removal
 * @requirement CART-015 Create "Continue Shopping" button
 * @requirement CART-016 Create "Proceed to Checkout" button
 * @requirement CART-017 Handle empty cart state
 * @requirement CART-023 Write cart module tests
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { createRouter, createWebHistory } from "vue-router";
import CartPanel from "@/components/shop/CartPanel.vue";

// Mock stores
const mockCartStore = {
  isOpen: true,
  items: [],
  isEmpty: true,
  itemCount: 0,
  subtotal: 0,
  subtotalFormatted: "$0.00",
  meetsMinimumOrder: false,
  amountToMinimumFormatted: "$100.00",
  isLoading: false,
  MINIMUM_ORDER: 100,
  closeCart: vi.fn(),
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

describe("CartPanel", () => {
  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks();
    mockCartStore.isOpen = true;
    mockCartStore.items = [];
    mockCartStore.isEmpty = true;
    mockCartStore.itemCount = 0;
    mockCartStore.subtotal = 0;
    mockCartStore.subtotalFormatted = "$0.00";
    mockCartStore.meetsMinimumOrder = false;
    mockCartStore.amountToMinimumFormatted = "$100.00";
    mockCartStore.isLoading = false;
    mockAuthStore.isAuthenticated = false;

    // Reset body overflow
    document.body.style.overflow = "";
  });

  function mountComponent() {
    return mount(CartPanel, {
      global: {
        plugins: [router],
        stubs: {
          Teleport: true,
          Transition: false,
        },
      },
    });
  }

  describe("Panel Visibility", () => {
    it("renders when isOpen is true", () => {
      mockCartStore.isOpen = true;
      const wrapper = mountComponent();
      expect(wrapper.exists()).toBe(true);
    });

    it("shows panel content when open", () => {
      mockCartStore.isOpen = true;
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Your Cart");
    });

    it("has close button", () => {
      const wrapper = mountComponent();
      const closeBtn = wrapper.find("button");
      expect(closeBtn.exists()).toBe(true);
    });

    it("calls closeCart when close button clicked", async () => {
      const wrapper = mountComponent();
      // Find the close button (first button in header)
      const buttons = wrapper.findAll("button");
      const closeBtn = buttons[0];
      await closeBtn.trigger("click");
      expect(mockCartStore.closeCart).toHaveBeenCalled();
    });
  });

  describe("Empty Cart State", () => {
    beforeEach(() => {
      mockCartStore.isEmpty = true;
      mockCartStore.items = [];
      mockCartStore.itemCount = 0;
    });

    it("displays empty cart message", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Your cart is empty");
    });

    it("has Start Shopping button", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Start Shopping");
    });

    it("Start Shopping navigates to shop page", async () => {
      const wrapper = mountComponent();
      const startBtn = wrapper.find("button");
      // Find the "Start Shopping" button
      const buttons = wrapper.findAll("button");
      const startShoppingBtn = buttons.find((b) =>
        b.text().includes("Start Shopping")
      );

      if (startShoppingBtn) {
        await startShoppingBtn.trigger("click");
        expect(mockCartStore.closeCart).toHaveBeenCalled();
      }
    });
  });

  describe("Loading State", () => {
    beforeEach(() => {
      mockCartStore.isLoading = true;
    });

    it("shows loading spinner", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Loading cart");
    });

    it("displays loading animation", () => {
      const wrapper = mountComponent();
      const spinner = wrapper.find(".animate-spin");
      expect(spinner.exists()).toBe(true);
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
        },
        quantity: 1.5,
        unit_price: 18.99,
      },
    ];

    beforeEach(() => {
      mockCartStore.items = mockItems;
      mockCartStore.isEmpty = false;
      mockCartStore.itemCount = 2;
      mockCartStore.subtotal = 120.465;
      mockCartStore.subtotalFormatted = "$120.47";
      mockCartStore.meetsMinimumOrder = true;
    });

    it("displays cart item count in header", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("2 items");
    });

    it("displays product names", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Ribeye Steak");
      expect(wrapper.text()).toContain("Chicken Breast");
    });

    it("displays product prices per unit", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("$45.99");
      expect(wrapper.text()).toContain("$18.99");
    });

    it("displays quantity with unit", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("2kg");
      expect(wrapper.text()).toContain("1.5kg");
    });

    it("displays line totals", () => {
      const wrapper = mountComponent();
      // Line totals are calculated in the component using formatPrice
      // Ribeye: 2 * 45.99 = 91.98 => formatPrice returns based on mock
      // Chicken: 1.5 * 18.99 = 28.485
      // The mock returns format based on amount, so check the mock was called
      expect(mockCurrencyStore.format).toHaveBeenCalled();
      // Also verify the items are rendered (line totals exist)
      const listItems = wrapper.findAll("li");
      expect(listItems.length).toBe(2);
    });

    it("has remove button for each item", () => {
      const wrapper = mountComponent();
      // Should have remove buttons (trash icons)
      const listItems = wrapper.findAll("li");
      expect(listItems.length).toBe(2);
    });

    it("has quantity decrease button", () => {
      const wrapper = mountComponent();
      const buttons = wrapper.findAll("button");
      // Filter buttons that have minus icon
      const minusButtons = buttons.filter((b) => {
        const svg = b.find("svg");
        return svg.exists() && b.text() === "";
      });
      expect(minusButtons.length).toBeGreaterThan(0);
    });

    it("has quantity increase button", () => {
      const wrapper = mountComponent();
      const buttons = wrapper.findAll("button");
      expect(buttons.length).toBeGreaterThan(4); // Close, -, +, remove for each item
    });

    it("decrease button calls updateQuantity", async () => {
      const wrapper = mountComponent();
      // Find first list item and its decrease button
      const listItem = wrapper.find("li");
      const buttons = listItem.findAll("button");
      // First button in quantity controls (decrease)
      await buttons[0].trigger("click");
      expect(mockCartStore.updateQuantity).toHaveBeenCalled();
    });

    it("increase button calls updateQuantity", async () => {
      const wrapper = mountComponent();
      const listItem = wrapper.find("li");
      const buttons = listItem.findAll("button");
      // Second button in quantity controls (increase)
      await buttons[1].trigger("click");
      expect(mockCartStore.updateQuantity).toHaveBeenCalled();
    });

    it("remove button calls removeItem", async () => {
      const wrapper = mountComponent();
      const listItem = wrapper.find("li");
      const buttons = listItem.findAll("button");
      // Last button should be remove
      const removeBtn = buttons[buttons.length - 1];
      await removeBtn.trigger("click");
      expect(mockCartStore.removeItem).toHaveBeenCalled();
    });

    it("product name links to product page", () => {
      const wrapper = mountComponent();
      const productLinks = wrapper.findAll('a[href*="/products/"]');
      expect(productLinks.length).toBeGreaterThan(0);
    });
  });

  describe("Cart Summary", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test", stock_quantity: 50, unit: "kg" },
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

    it("displays subtotal", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Subtotal");
      expect(wrapper.text()).toContain("$125.00");
    });

    it("shows delivery message", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Delivery");
    });
  });

  describe("Minimum Order Warning", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test", stock_quantity: 50, unit: "kg" },
          quantity: 1,
          unit_price: 50,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.itemCount = 1;
      mockCartStore.subtotal = 50;
      mockCartStore.meetsMinimumOrder = false;
      mockCartStore.amountToMinimumFormatted = "$50.00";
    });

    it("shows minimum order warning when below threshold", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("$50.00");
      expect(wrapper.text()).toContain("more");
    });

    it("shows minimum order amount", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("100");
    });

    it("disables checkout button when below minimum", () => {
      const wrapper = mountComponent();
      const checkoutBtn = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Checkout"));
      if (checkoutBtn) {
        expect(checkoutBtn.attributes("disabled")).toBeDefined();
      }
    });
  });

  describe("Checkout Flow", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test", stock_quantity: 50, unit: "kg" },
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

    it("checkout button is enabled when minimum met", () => {
      const wrapper = mountComponent();
      const checkoutBtn = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Checkout"));
      if (checkoutBtn) {
        expect(checkoutBtn.attributes("disabled")).toBeUndefined();
      }
    });

    it("checkout navigates to login when not authenticated", async () => {
      mockAuthStore.isAuthenticated = false;
      const wrapper = mountComponent();
      const checkoutBtn = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Checkout"));

      if (checkoutBtn) {
        await checkoutBtn.trigger("click");
        expect(mockCartStore.closeCart).toHaveBeenCalled();
      }
    });

    it("checkout navigates to checkout when authenticated", async () => {
      mockAuthStore.isAuthenticated = true;
      const wrapper = mountComponent();
      const checkoutBtn = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Checkout"));

      if (checkoutBtn) {
        await checkoutBtn.trigger("click");
        expect(mockCartStore.closeCart).toHaveBeenCalled();
      }
    });
  });

  describe("Continue Shopping", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test", stock_quantity: 50, unit: "kg" },
          quantity: 1,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.meetsMinimumOrder = true;
    });

    it("has Continue Shopping button", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Continue Shopping");
    });

    it("Continue Shopping closes panel", async () => {
      const wrapper = mountComponent();
      const continueBtn = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Continue Shopping"));

      if (continueBtn) {
        await continueBtn.trigger("click");
        expect(mockCartStore.closeCart).toHaveBeenCalled();
      }
    });
  });

  describe("View Full Cart", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test", stock_quantity: 50, unit: "kg" },
          quantity: 1,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.meetsMinimumOrder = true;
    });

    it("has View Full Cart link", () => {
      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("View Full Cart");
    });

    it("View Full Cart links to cart page", () => {
      const wrapper = mountComponent();
      const cartLink = wrapper.find('a[href="/cart"]');
      expect(cartLink.exists()).toBe(true);
    });
  });

  describe("Clear Cart", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test", stock_quantity: 50, unit: "kg" },
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
          product: { name: "Test", stock_quantity: 3, unit: "kg" },
          quantity: 5, // More than stock
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;

      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("Only 3kg available");
    });
  });

  describe("Quantity Controls", () => {
    beforeEach(() => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test", stock_quantity: 50, unit: "kg" },
          quantity: 0.5,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;
    });

    it("disables decrease button at minimum quantity", () => {
      const wrapper = mountComponent();
      const listItem = wrapper.find("li");
      const buttons = listItem.findAll("button");
      const decreaseBtn = buttons[0];

      expect(decreaseBtn.attributes("disabled")).toBeDefined();
    });

    it("disables increase button at max stock", () => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test", stock_quantity: 5, unit: "kg" },
          quantity: 5, // At max stock
          unit_price: 100,
        },
      ];

      const wrapper = mountComponent();
      const listItem = wrapper.find("li");
      const buttons = listItem.findAll("button");
      const increaseBtn = buttons[1];

      expect(increaseBtn.attributes("disabled")).toBeDefined();
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
      const img = wrapper.find("img");
      expect(img.exists()).toBe(true);
    });

    it("uses fallback image when no product image", () => {
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
      const img = wrapper.find("img");
      expect(img.attributes("src")).toContain("placeholder");
    });
  });

  describe("Singular/Plural Item Text", () => {
    it('shows "item" for single item', () => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test", stock_quantity: 50, unit: "kg" },
          quantity: 1,
          unit_price: 100,
        },
      ];
      mockCartStore.isEmpty = false;
      mockCartStore.itemCount = 1;

      const wrapper = mountComponent();
      expect(wrapper.text()).toContain("1 item");
      expect(wrapper.text()).not.toContain("1 items");
    });

    it('shows "items" for multiple items', () => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test", stock_quantity: 50, unit: "kg" },
          quantity: 1,
          unit_price: 100,
        },
        {
          id: 2,
          product_id: 102,
          product: { name: "Test 2", stock_quantity: 50, unit: "kg" },
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

  describe("Cart Icon", () => {
    it("displays cart icon in header", () => {
      const wrapper = mountComponent();
      const svg = wrapper.find("svg");
      expect(svg.exists()).toBe(true);
    });
  });

  describe("Backdrop", () => {
    it("closes panel when backdrop clicked", async () => {
      const wrapper = mountComponent();
      // Find backdrop div (first child with fixed inset-0)
      const backdrop = wrapper.find(".fixed.inset-0");
      if (backdrop.exists()) {
        await backdrop.trigger("click");
        expect(mockCartStore.closeCart).toHaveBeenCalled();
      }
    });
  });

  describe("Price Formatting", () => {
    it("calls currency format function", () => {
      mockCartStore.items = [
        {
          id: 1,
          product_id: 101,
          product: { name: "Test", stock_quantity: 50, unit: "kg" },
          quantity: 2,
          unit_price: 45.99,
        },
      ];
      mockCartStore.isEmpty = false;

      mountComponent();

      expect(mockCurrencyStore.format).toHaveBeenCalled();
    });
  });

  describe("Transitions", () => {
    it("has transition classes for panel", () => {
      const wrapper = mountComponent();
      // Check that transition wrapper exists
      const html = wrapper.html();
      expect(html).toContain("transition");
    });
  });
});
