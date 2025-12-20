/**
 * ProductPage Integration Tests
 *
 * Tests for the individual product detail page with gallery and related products.
 *
 * @requirement SHOP-007 Create product detail page
 * @requirement SHOP-008 Display nutrition info
 * @requirement SHOP-009 Show related products
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import { createRouter, createWebHistory } from "vue-router";
import ProductPage from "../../pages/ProductPage.vue";

// Mock stores
vi.mock("../../stores/products", () => ({
  useProductsStore: vi.fn(() => ({
    currentProduct: null,
    relatedProducts: [],
    isLoading: false,
    error: null,
    fetchProduct: vi.fn().mockResolvedValue(null),
    fetchRelatedProducts: vi.fn().mockResolvedValue([]),
  })),
}));

vi.mock("../../stores/cart", () => ({
  useCartStore: vi.fn(() => ({
    addItem: vi.fn().mockResolvedValue(true),
    isInCart: vi.fn(() => false),
    getQuantity: vi.fn(() => 0),
  })),
}));

vi.mock("../../stores/currency", () => ({
  useCurrencyStore: vi.fn(() => ({
    format: vi.fn((value) => `$${value.toFixed(2)}`),
  })),
}));

import { useProductsStore } from "../../stores/products";
import { useCartStore } from "../../stores/cart";
import { useCurrencyStore } from "../../stores/currency";

describe("ProductPage.vue", () => {
  let pinia;
  let router;
  let mockProductsStore;
  let mockCartStore;
  let mockCurrencyStore;

  const mockProduct = {
    id: 1,
    name: "Premium Beef Steak",
    slug: "premium-beef-steak",
    description:
      "High-quality beef steak from grass-fed cattle. Perfect for grilling.",
    short_description: "Premium quality grass-fed beef steak.",
    price: 45.99,
    compare_at_price: null,
    stock_quantity: 25,
    unit: "kg",
    is_featured: true,
    is_active: true,
    min_order_quantity: 0.5,
    max_order_quantity: 10,
    images: [
      {
        id: 1,
        url: "/images/steak-1.jpg",
        alt_text: "Beef Steak Front",
        is_primary: true,
      },
      {
        id: 2,
        url: "/images/steak-2.jpg",
        alt_text: "Beef Steak Side",
        is_primary: false,
      },
      {
        id: 3,
        url: "/images/steak-3.jpg",
        alt_text: "Beef Steak Grilled",
        is_primary: false,
      },
    ],
    category: { id: 1, name: "Beef", slug: "beef" },
    nutrition_info: {
      serving_size: "100g",
      calories: 250,
      protein: 26,
      fat: 17,
      saturated_fat: 7,
      carbohydrates: 0,
      fiber: 0,
      sodium: 55,
      cholesterol: 80,
    },
  };

  const mockRelatedProducts = [
    {
      id: 2,
      name: "Ribeye Steak",
      slug: "ribeye-steak",
      price_aud: 55.99,
      stock: 15,
    },
    {
      id: 3,
      name: "T-Bone Steak",
      slug: "t-bone-steak",
      price_aud: 49.99,
      stock: 20,
    },
    {
      id: 4,
      name: "Beef Mince",
      slug: "beef-mince",
      price_aud: 18.99,
      stock: 30,
    },
  ];

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
          component: ProductPage,
        },
      ],
    });

    mockProductsStore = {
      currentProduct: null,
      relatedProducts: [],
      isLoading: false,
      error: null,
      fetchProduct: vi.fn().mockResolvedValue(mockProduct),
      fetchRelatedProducts: vi.fn().mockResolvedValue(mockRelatedProducts),
    };

    mockCartStore = {
      addItem: vi.fn().mockResolvedValue(true),
      isInCart: vi.fn(() => false),
      getQuantity: vi.fn(() => 0),
    };

    mockCurrencyStore = {
      format: vi.fn((value) => `$${value.toFixed(2)}`),
    };

    useProductsStore.mockReturnValue(mockProductsStore);
    useCartStore.mockReturnValue(mockCartStore);
    useCurrencyStore.mockReturnValue(mockCurrencyStore);

    vi.clearAllMocks();
  });

  const mountComponent = async (slug = "premium-beef-steak") => {
    await router.push(`/products/${slug}`);
    await router.isReady();

    return mount(ProductPage, {
      global: {
        plugins: [pinia, router],
        stubs: {
          RouterLink: {
            template: '<a :href="to"><slot /></a>',
            props: ["to"],
          },
          ProductCard: {
            template:
              '<div class="product-card" :data-id="product.id">{{ product.name }}</div>',
            props: ["product", "showQuickView"],
          },
        },
      },
    });
  };

  describe("Page Loading", () => {
    it("shows loading state initially", async () => {
      mockProductsStore.isLoading = true;
      const wrapper = await mountComponent();

      expect(wrapper.find(".animate-pulse").exists()).toBe(true);
    });

    it("calls fetchProduct on mount", async () => {
      await mountComponent();
      expect(mockProductsStore.fetchProduct).toHaveBeenCalledWith(
        "premium-beef-steak"
      );
    });

    it("calls fetchRelatedProducts after product loads", async () => {
      mockProductsStore.currentProduct = mockProduct;
      await mountComponent();
      await flushPromises();

      expect(mockProductsStore.fetchRelatedProducts).toHaveBeenCalled();
    });
  });

  describe("Error State", () => {
    it("shows error message when product not found", async () => {
      mockProductsStore.error = "Product not found";
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Product Not Found");
    });

    it("shows back to shop link on error", async () => {
      mockProductsStore.error = "Product not found";
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Back to Shop");
    });
  });

  describe("Breadcrumb", () => {
    it("shows breadcrumb navigation", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.find("nav").exists()).toBe(true);
      expect(wrapper.text()).toContain("Home");
      expect(wrapper.text()).toContain("Shop");
    });

    it("includes category in breadcrumb", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Beef");
    });

    it("includes product name in breadcrumb", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Premium Beef Steak");
    });
  });

  describe("Product Details", () => {
    it("displays product name", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.find("h1").text()).toBe("Premium Beef Steak");
    });

    it("displays product price", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("$45.99");
    });

    it("displays product unit", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("kg");
    });

    it("displays category link", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Beef");
    });

    it("displays short description", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Premium quality grass-fed beef steak");
    });
  });

  describe("Price with Discount", () => {
    it("shows sale price when on discount", async () => {
      mockProductsStore.currentProduct = {
        ...mockProduct,
        price: 35.99,
        compare_at_price: 45.99,
      };
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("$35.99");
    });

    it("shows original price with strikethrough", async () => {
      mockProductsStore.currentProduct = {
        ...mockProduct,
        price: 35.99,
        compare_at_price: 45.99,
      };
      const wrapper = await mountComponent();

      expect(wrapper.find(".line-through").exists()).toBe(true);
    });

    it("shows discount badge", async () => {
      mockProductsStore.currentProduct = {
        ...mockProduct,
        price: 35.99,
        compare_at_price: 45.99,
      };
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("OFF");
    });
  });

  describe("Image Gallery", () => {
    it("displays main product image", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.find("img").exists()).toBe(true);
    });

    it("shows thumbnails for multiple images", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      const thumbnails = wrapper.findAll("button img");
      expect(thumbnails.length).toBe(3);
    });

    it("changes image on thumbnail click", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      const thumbnails = wrapper.findAll("button img");
      await thumbnails[1].trigger("click");

      expect(wrapper.vm.currentImageIndex).toBe(1);
    });

    it("has next image button", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      const buttons = wrapper.findAll(".aspect-square button");
      expect(buttons.length).toBe(2); // prev and next
    });

    it("navigates to next image", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      wrapper.vm.nextImage();
      expect(wrapper.vm.currentImageIndex).toBe(1);
    });

    it("navigates to previous image", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      wrapper.vm.currentImageIndex = 2;
      wrapper.vm.prevImage();
      expect(wrapper.vm.currentImageIndex).toBe(1);
    });

    it("wraps around at end", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      wrapper.vm.currentImageIndex = 2;
      wrapper.vm.nextImage();
      expect(wrapper.vm.currentImageIndex).toBe(0);
    });
  });

  describe("Stock Status", () => {
    it("shows in stock status", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("in stock");
    });

    it("shows stock quantity", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("25");
    });

    it("shows out of stock status", async () => {
      mockProductsStore.currentProduct = { ...mockProduct, stock_quantity: 0 };
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("unavailable");
    });

    it("shows out of stock badge", async () => {
      mockProductsStore.currentProduct = { ...mockProduct, stock_quantity: 0 };
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Out of Stock");
    });
  });

  describe("Quantity Controls", () => {
    it("has quantity input", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.find('input[type="number"]').exists()).toBe(true);
    });

    it("starts with quantity 1", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.vm.quantity).toBe(1);
    });

    it("increments quantity", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      const buttons = wrapper.findAll(".border-gray-300 button");
      // Find increment button (has + icon)
      const incButton = buttons[buttons.length - 1];
      await incButton.trigger("click");

      expect(wrapper.vm.quantity).toBe(1.5);
    });

    it("decrements quantity", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      wrapper.vm.quantity = 3;
      const buttons = wrapper.findAll(".border-gray-300 button");
      const decButton = buttons[0];
      await decButton.trigger("click");

      expect(wrapper.vm.quantity).toBe(2.5);
    });

    it("respects minimum quantity", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      wrapper.vm.quantity = 0.5;
      const buttons = wrapper.findAll(".border-gray-300 button");
      const decButton = buttons[0];
      await decButton.trigger("click");

      expect(wrapper.vm.quantity).toBeGreaterThanOrEqual(0.5);
    });
  });

  describe("Add to Cart", () => {
    it("has add to cart button", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Add to Cart");
    });

    it("calls cart store on add", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      await wrapper.vm.handleAddToCart();
      await flushPromises();

      expect(mockCartStore.addItem).toHaveBeenCalled();
    });

    it("passes correct quantity to cart", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      wrapper.vm.quantity = 2;
      await wrapper.vm.handleAddToCart();
      await flushPromises();

      expect(mockCartStore.addItem).toHaveBeenCalledWith(mockProduct, 2);
    });

    it("shows loading state during add", async () => {
      mockProductsStore.currentProduct = mockProduct;
      mockCartStore.addItem.mockImplementation(
        () => new Promise((resolve) => setTimeout(() => resolve(true), 100))
      );

      const wrapper = await mountComponent();

      wrapper.vm.handleAddToCart();

      expect(wrapper.vm.isAdding).toBe(true);
    });

    it("shows success message after add", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      await wrapper.vm.handleAddToCart();
      await flushPromises();

      expect(wrapper.vm.showSuccess).toBe(true);
    });

    it("resets quantity after add", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      wrapper.vm.quantity = 3;
      await wrapper.vm.handleAddToCart();
      await flushPromises();

      expect(wrapper.vm.quantity).toBe(1);
    });

    it("disables add button when out of stock", async () => {
      mockProductsStore.currentProduct = { ...mockProduct, stock_quantity: 0 };
      const wrapper = await mountComponent();

      const addButton = wrapper.find("button[disabled]");
      expect(addButton.exists()).toBe(true);
    });
  });

  describe("In Cart Indicator", () => {
    it("shows when product already in cart", async () => {
      mockProductsStore.currentProduct = mockProduct;
      mockCartStore.isInCart.mockReturnValue(true);
      mockCartStore.getQuantity.mockReturnValue(2);

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("already in cart");
    });
  });

  describe("Tabs", () => {
    it("has description tab", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Description");
    });

    it("has nutrition tab when data available", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Nutrition Info");
    });

    it("description tab is active by default", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.vm.activeTab).toBe("description");
    });

    it("shows description content", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain(
        "High-quality beef steak from grass-fed cattle"
      );
    });

    it("switches to nutrition tab", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      const nutritionTab = wrapper
        .findAll("button")
        .find((b) => b.text().includes("Nutrition"));
      await nutritionTab.trigger("click");

      expect(wrapper.vm.activeTab).toBe("nutrition");
    });
  });

  describe("Nutrition Info", () => {
    it("shows calories", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      wrapper.vm.activeTab = "nutrition";
      await flushPromises();

      expect(wrapper.text()).toContain("Calories");
      expect(wrapper.text()).toContain("250");
    });

    it("shows protein", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      wrapper.vm.activeTab = "nutrition";
      await flushPromises();

      expect(wrapper.text()).toContain("Protein");
      expect(wrapper.text()).toContain("26");
    });

    it("shows serving size", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      wrapper.vm.activeTab = "nutrition";
      await flushPromises();

      expect(wrapper.text()).toContain("100g");
    });
  });

  describe("Related Products", () => {
    it("shows related products section", async () => {
      mockProductsStore.currentProduct = mockProduct;
      mockProductsStore.relatedProducts = mockRelatedProducts;

      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Related Products");
    });

    it("displays related product cards", async () => {
      mockProductsStore.currentProduct = mockProduct;
      mockProductsStore.relatedProducts = mockRelatedProducts;

      const wrapper = await mountComponent();

      expect(wrapper.findAll(".product-card").length).toBe(3);
    });

    it("hides section when no related products", async () => {
      mockProductsStore.currentProduct = mockProduct;
      mockProductsStore.relatedProducts = [];

      const wrapper = await mountComponent();

      expect(wrapper.text()).not.toContain("Related Products");
    });
  });

  describe("Badges", () => {
    it("shows featured badge", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Featured");
    });

    it("does not show featured badge when not featured", async () => {
      mockProductsStore.currentProduct = { ...mockProduct, is_featured: false };
      const wrapper = await mountComponent();

      // Should not have featured badge in gallery area
      const badges = wrapper.findAll(".absolute span");
      const hasFeatured = badges.some((b) => b.text().includes("Featured"));
      expect(hasFeatured).toBe(false);
    });
  });

  describe("Product Meta", () => {
    it("shows minimum order quantity", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Min Order");
      expect(wrapper.text()).toContain("0.5");
    });

    it("shows maximum order quantity", async () => {
      mockProductsStore.currentProduct = mockProduct;
      const wrapper = await mountComponent();

      expect(wrapper.text()).toContain("Max Order");
      expect(wrapper.text()).toContain("10");
    });
  });
});
