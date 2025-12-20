/**
 * Checkout Store Tests
 *
 * Comprehensive tests for the checkout Pinia store.
 *
 * @requirement CHK-001 to CHK-030 Checkout module
 * @requirement CHK-029 Create checkout Pinia store
 * @requirement CHK-030 Write checkout module tests
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useCheckoutStore } from "@/stores/checkout";
import { useCartStore } from "@/stores/cart";
import { useAuthStore } from "@/stores/auth";

// Mock API - must be hoisted
vi.mock("@/services/api", () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

// Mock cart store
vi.mock("@/stores/cart", () => ({
  useCartStore: vi.fn(() => ({
    items: [],
    subtotal: 0,
    subtotalFormatted: "$0.00",
  })),
}));

// Mock auth store
vi.mock("@/stores/auth", () => ({
  useAuthStore: vi.fn(() => ({
    isAuthenticated: true,
    user: { id: 1, email: "test@example.com" },
  })),
}));

// Import the mocked api after vi.mock
import api from "@/services/api";

describe("Checkout Store", () => {
  let store;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useCheckoutStore();
  });

  describe("Initial State", () => {
    it("starts at step 1", () => {
      expect(store.currentStep).toBe(1);
    });

    it("has 4 checkout steps", () => {
      expect(store.steps).toHaveLength(4);
      expect(store.steps[0].name).toBe("Delivery");
      expect(store.steps[1].name).toBe("Payment");
      expect(store.steps[2].name).toBe("Review");
      expect(store.steps[3].name).toBe("Complete");
    });

    it("has empty delivery form", () => {
      expect(store.deliveryForm.streetAddress).toBe("");
      expect(store.deliveryForm.suburb).toBe("");
      expect(store.deliveryForm.postcode).toBe("");
      expect(store.deliveryForm.addressId).toBeNull();
    });

    it("has default state NSW", () => {
      expect(store.deliveryForm.state).toBe("NSW");
    });

    it("has no saved addresses", () => {
      expect(store.savedAddresses).toEqual([]);
    });

    it("has null delivery zone", () => {
      expect(store.deliveryZone).toBeNull();
    });

    it("has zero delivery fee", () => {
      expect(store.deliveryFee).toBe(0);
      expect(store.deliveryFeeFormatted).toBe("$0.00");
    });

    it("has null estimated days", () => {
      expect(store.estimatedDays).toBeNull();
    });

    it("has null deliversToArea", () => {
      expect(store.deliversToArea).toBeNull();
    });

    it("has empty delivery message", () => {
      expect(store.deliveryMessage).toBe("");
    });

    it("has empty promo code", () => {
      expect(store.promoCode).toBe("");
    });

    it("has zero promo discount", () => {
      expect(store.promoDiscount).toBe(0);
    });

    it("has promo not valid", () => {
      expect(store.promoValid).toBe(false);
    });

    it("has stripe as default payment method", () => {
      expect(store.paymentMethod).toBe("stripe");
    });

    it("has empty payment methods array", () => {
      expect(store.paymentMethods).toEqual([]);
    });

    it("has empty order notes", () => {
      expect(store.orderNotes).toBe("");
    });

    it("has empty delivery instructions", () => {
      expect(store.deliveryInstructions).toBe("");
    });

    it("has null order", () => {
      expect(store.order).toBeNull();
    });

    it("has null orderId", () => {
      expect(store.orderId).toBeNull();
    });

    it("has isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("has isValidatingAddress false", () => {
      expect(store.isValidatingAddress).toBe(false);
    });

    it("has isCalculatingFee false", () => {
      expect(store.isCalculatingFee).toBe(false);
    });

    it("has isValidatingPromo false", () => {
      expect(store.isValidatingPromo).toBe(false);
    });

    it("has isCreatingOrder false", () => {
      expect(store.isCreatingOrder).toBe(false);
    });

    it("has isProcessingPayment false", () => {
      expect(store.isProcessingPayment).toBe(false);
    });

    it("has null error", () => {
      expect(store.error).toBeNull();
    });

    it("has Australian states list", () => {
      expect(store.australianStates).toHaveLength(8);
      expect(
        store.australianStates.find((s) => s.code === "NSW")
      ).toBeDefined();
      expect(
        store.australianStates.find((s) => s.code === "VIC")
      ).toBeDefined();
    });
  });

  describe("Computed Properties", () => {
    it("subtotal comes from cart store", () => {
      // Mock must be set up before store is created
      useCartStore.mockReturnValue({
        items: [{ id: 1, quantity: 1, unit_price: 50 }],
        subtotal: 50,
        subtotalFormatted: "$50.00",
      });

      // Need fresh pinia to pick up the new mock
      setActivePinia(createPinia());
      const newStore = useCheckoutStore();
      expect(newStore.subtotal).toBe(50);
    });

    it("calculates total with delivery and discount", () => {
      useCartStore.mockReturnValue({
        items: [{ id: 1 }],
        subtotal: 100,
        subtotalFormatted: "$100.00",
      });

      // Need fresh pinia to pick up the new mock
      setActivePinia(createPinia());
      const newStore = useCheckoutStore();
      newStore.deliveryFee = 10;
      newStore.promoDiscount = 15;

      expect(newStore.total).toBe(95);
    });

    it("formats total correctly", () => {
      useCartStore.mockReturnValue({
        items: [{ id: 1 }],
        subtotal: 100,
        subtotalFormatted: "$100.00",
      });

      setActivePinia(createPinia());
      const newStore = useCheckoutStore();
      newStore.deliveryFee = 10;

      expect(newStore.totalFormatted).toBe("$110.00");
    });

    describe("isDeliveryValid", () => {
      it("returns true if addressId is set", () => {
        store.deliveryForm.addressId = 123;
        expect(store.isDeliveryValid).toBe(true);
      });

      it("returns true with complete valid form", () => {
        store.deliveryForm.streetAddress = "123 Test St";
        store.deliveryForm.suburb = "Sydney";
        store.deliveryForm.state = "NSW";
        store.deliveryForm.postcode = "2000";
        store.deliversToArea = true;

        expect(store.isDeliveryValid).toBe(true);
      });

      it("returns false with incomplete form", () => {
        store.deliveryForm.streetAddress = "123 Test St";
        store.deliveryForm.suburb = "";
        store.deliveryForm.postcode = "2000";
        store.deliversToArea = true;

        expect(store.isDeliveryValid).toBe(false);
      });

      it("returns false with invalid postcode", () => {
        store.deliveryForm.streetAddress = "123 Test St";
        store.deliveryForm.suburb = "Sydney";
        store.deliveryForm.state = "NSW";
        store.deliveryForm.postcode = "123"; // Must be 4 digits
        store.deliversToArea = true;

        // null postcode match returns falsy, whole expression is false
        expect(store.isDeliveryValid).toBeFalsy();
      });

      it("returns false if deliversToArea is false", () => {
        store.deliveryForm.streetAddress = "123 Test St";
        store.deliveryForm.suburb = "Remote";
        store.deliveryForm.state = "NT";
        store.deliveryForm.postcode = "0800";
        store.deliversToArea = false;

        expect(store.isDeliveryValid).toBe(false);
      });
    });

    describe("isPaymentValid", () => {
      it("returns true when payment method is set", () => {
        store.paymentMethod = "stripe";
        expect(store.isPaymentValid).toBe(true);
      });

      it("returns false when payment method is null", () => {
        store.paymentMethod = null;
        expect(store.isPaymentValid).toBe(false);
      });
    });

    describe("canProceedToPayment", () => {
      it("returns true with valid delivery and cart items", () => {
        useCartStore.mockReturnValue({
          items: [{ id: 1 }],
          subtotal: 100,
          subtotalFormatted: "$100.00",
        });

        setActivePinia(createPinia());
        const newStore = useCheckoutStore();
        newStore.deliveryForm.addressId = 123;

        expect(newStore.canProceedToPayment).toBe(true);
      });

      it("returns false with empty cart", () => {
        useCartStore.mockReturnValue({
          items: [],
          subtotal: 0,
          subtotalFormatted: "$0.00",
        });

        setActivePinia(createPinia());
        const newStore = useCheckoutStore();
        newStore.deliveryForm.addressId = 123;

        expect(newStore.canProceedToPayment).toBe(false);
      });
    });

    describe("canProceedToReview", () => {
      it("returns true with valid delivery and payment", () => {
        useCartStore.mockReturnValue({
          items: [{ id: 1 }],
          subtotal: 100,
          subtotalFormatted: "$100.00",
        });

        setActivePinia(createPinia());
        const newStore = useCheckoutStore();
        newStore.deliveryForm.addressId = 123;
        newStore.paymentMethod = "stripe";

        expect(newStore.canProceedToReview).toBe(true);
      });
    });
  });

  describe("Step Navigation", () => {
    it("goToStep changes current step", () => {
      store.deliveryForm.addressId = 123;
      useCartStore.mockReturnValue({
        items: [{ id: 1 }],
        subtotal: 100,
        subtotalFormatted: "$100.00",
      });

      store.goToStep(2);
      expect(store.currentStep).toBe(2);
    });

    it("goToStep respects validation for forward navigation", () => {
      useCartStore.mockReturnValue({
        items: [],
        subtotal: 0,
        subtotalFormatted: "$0.00",
      });

      store.goToStep(2);
      expect(store.currentStep).toBe(1); // Should not change
    });

    it("goToStep allows backward navigation", () => {
      store.currentStep = 3;
      store.goToStep(1);
      expect(store.currentStep).toBe(1);
    });

    it("nextStep increments current step", () => {
      store.currentStep = 1;
      store.nextStep();
      expect(store.currentStep).toBe(2);
    });

    it("nextStep marks current step as completed", () => {
      store.currentStep = 1;
      store.nextStep();
      expect(store.steps[0].completed).toBe(true);
    });

    it("nextStep does not exceed step 4", () => {
      store.currentStep = 4;
      store.nextStep();
      expect(store.currentStep).toBe(4);
    });

    it("previousStep decrements current step", () => {
      store.currentStep = 3;
      store.previousStep();
      expect(store.currentStep).toBe(2);
    });

    it("previousStep does not go below step 1", () => {
      store.currentStep = 1;
      store.previousStep();
      expect(store.currentStep).toBe(1);
    });
  });

  describe("selectAddress", () => {
    it("populates form from saved address", () => {
      store.savedAddresses = [
        {
          id: 123,
          street_address: "123 Main St",
          apartment: "Unit 5",
          suburb: "Sydney",
          state: "NSW",
          postcode: "2000",
        },
      ];

      api.post.mockResolvedValue({
        data: { delivers: true, message: "We deliver here!" },
      });

      store.selectAddress(123);

      expect(store.deliveryForm.addressId).toBe(123);
      expect(store.deliveryForm.streetAddress).toBe("123 Main St");
      expect(store.deliveryForm.apartment).toBe("Unit 5");
      expect(store.deliveryForm.suburb).toBe("Sydney");
      expect(store.deliveryForm.state).toBe("NSW");
      expect(store.deliveryForm.postcode).toBe("2000");
    });

    it("does nothing if address not found", () => {
      store.savedAddresses = [];
      store.selectAddress(999);
      expect(store.deliveryForm.addressId).toBeNull();
    });
  });

  describe("clearSelectedAddress", () => {
    it("clears addressId", () => {
      store.deliveryForm.addressId = 123;
      store.clearSelectedAddress();
      expect(store.deliveryForm.addressId).toBeNull();
    });
  });

  describe("initCheckout", () => {
    it("sets isLoading during init", async () => {
      api.get.mockResolvedValue({
        data: {
          addresses: [],
          default_address: null,
        },
      });

      const promise = store.initCheckout();
      expect(store.isLoading).toBe(true);
      await promise;
      expect(store.isLoading).toBe(false);
    });

    it("loads saved addresses", async () => {
      api.get.mockResolvedValue({
        data: {
          addresses: [
            { id: 1, street_address: "123 Test St" },
            { id: 2, street_address: "456 Other St" },
          ],
          default_address: null,
        },
      });

      await store.initCheckout();

      expect(store.savedAddresses).toHaveLength(2);
    });

    it("selects default address", async () => {
      api.get.mockResolvedValue({
        data: {
          addresses: [
            {
              id: 1,
              street_address: "123 Test St",
              suburb: "Sydney",
              state: "NSW",
              postcode: "2000",
            },
          ],
          default_address: { id: 1 },
        },
      });
      api.post.mockResolvedValue({
        data: { delivers: true, message: "We deliver!" },
      });

      await store.initCheckout();

      expect(store.deliveryForm.addressId).toBe(1);
    });

    it("handles init error", async () => {
      api.get.mockRejectedValue(new Error("Network error"));

      await store.initCheckout();

      expect(store.error).toBe(
        "Failed to load checkout data. Please try again."
      );
    });
  });

  describe("loadPaymentMethods", () => {
    it("loads payment methods from API", async () => {
      api.get.mockResolvedValue({
        data: {
          methods: [
            { id: "stripe", name: "Credit Card" },
            { id: "paypal", name: "PayPal" },
          ],
        },
      });

      await store.loadPaymentMethods();

      expect(store.paymentMethods).toHaveLength(2);
    });

    it("handles error gracefully", async () => {
      api.get.mockRejectedValue(new Error("Failed"));

      await store.loadPaymentMethods();

      // Should not throw, just log error
      expect(store.paymentMethods).toEqual([]);
    });
  });

  describe("validateAddress", () => {
    it("sets isValidatingAddress during validation", async () => {
      store.deliveryForm.postcode = "2000";
      api.post.mockResolvedValue({
        data: { delivers: true, message: "We deliver!" },
      });

      const promise = store.validateAddress();
      expect(store.isValidatingAddress).toBe(true);
      await promise;
      expect(store.isValidatingAddress).toBe(false);
    });

    it("does nothing with invalid postcode", async () => {
      store.deliveryForm.postcode = "12"; // Invalid
      await store.validateAddress();
      expect(api.post).not.toHaveBeenCalled();
    });

    it("sets deliversToArea on success", async () => {
      store.deliveryForm.postcode = "2000";
      api.post.mockResolvedValue({
        data: {
          delivers: true,
          message: "We deliver!",
          zone: { id: 1, name: "Sydney Metro" },
        },
      });

      await store.validateAddress();

      expect(store.deliversToArea).toBe(true);
      expect(store.deliveryMessage).toBe("We deliver!");
    });

    it("stores zone info", async () => {
      store.deliveryForm.postcode = "2000";
      api.post.mockResolvedValue({
        data: {
          delivers: true,
          message: "We deliver!",
          zone: { id: 1, name: "Sydney Metro", estimated_days: 2 },
        },
      });

      await store.validateAddress();

      expect(store.deliveryZone).toEqual({
        id: 1,
        name: "Sydney Metro",
        estimated_days: 2,
      });
    });

    it("handles non-delivery area", async () => {
      store.deliveryForm.postcode = "9999";
      api.post.mockResolvedValue({
        data: { delivers: false, message: "Sorry, we don't deliver here" },
      });

      await store.validateAddress();

      expect(store.deliversToArea).toBe(false);
      expect(store.deliveryFee).toBe(0);
      expect(store.deliveryFeeFormatted).toBe("N/A");
    });

    it("handles validation error", async () => {
      store.deliveryForm.postcode = "2000";
      api.post.mockRejectedValue(new Error("Network error"));

      await store.validateAddress();

      expect(store.deliversToArea).toBe(false);
      expect(store.deliveryMessage).toBe(
        "Unable to validate address. Please try again."
      );
    });
  });

  describe("calculateDeliveryFee", () => {
    beforeEach(() => {
      store.deliversToArea = true;
    });

    it("does nothing if area not deliverable", async () => {
      store.deliversToArea = false;
      await store.calculateDeliveryFee();
      expect(api.post).not.toHaveBeenCalled();
    });

    it("sets isCalculatingFee during calculation", async () => {
      api.post.mockResolvedValue({
        data: {
          success: true,
          fee: 10,
          fee_formatted: "$10.00",
          is_free: false,
        },
      });

      const promise = store.calculateDeliveryFee();
      expect(store.isCalculatingFee).toBe(true);
      await promise;
      expect(store.isCalculatingFee).toBe(false);
    });

    it("sets delivery fee on success", async () => {
      api.post.mockResolvedValue({
        data: {
          success: true,
          fee: 15,
          fee_formatted: "$15.00",
          is_free: false,
          estimated_days: 2,
        },
      });

      await store.calculateDeliveryFee();

      expect(store.deliveryFee).toBe(15);
      expect(store.deliveryFeeFormatted).toBe("$15.00");
      expect(store.estimatedDays).toBe(2);
    });

    it("shows FREE for free delivery", async () => {
      api.post.mockResolvedValue({
        data: { success: true, fee: 0, fee_formatted: "$0.00", is_free: true },
      });

      await store.calculateDeliveryFee();

      expect(store.deliveryFee).toBe(0);
      expect(store.deliveryFeeFormatted).toBe("FREE");
    });

    it("updates delivery message", async () => {
      api.post.mockResolvedValue({
        data: {
          success: true,
          fee: 10,
          is_free: false,
          message: "Add $20 more for FREE delivery!",
        },
      });

      await store.calculateDeliveryFee();

      expect(store.deliveryMessage).toBe("Add $20 more for FREE delivery!");
    });
  });

  describe("validatePromoCode", () => {
    it("clears promo if code is empty", async () => {
      store.promoCode = "";
      await store.validatePromoCode();

      expect(store.promoValid).toBe(false);
      expect(store.promoDiscount).toBe(0);
      expect(api.post).not.toHaveBeenCalled();
    });

    it("sets isValidatingPromo during validation", async () => {
      store.promoCode = "SAVE10";
      api.post.mockResolvedValue({
        data: {
          valid: true,
          message: "Code applied!",
          discount: 10,
          discount_formatted: "-$10.00",
        },
      });

      const promise = store.validatePromoCode();
      expect(store.isValidatingPromo).toBe(true);
      await promise;
      expect(store.isValidatingPromo).toBe(false);
    });

    it("sets promo values on valid code", async () => {
      store.promoCode = "SAVE10";
      api.post.mockResolvedValue({
        data: {
          valid: true,
          message: "Code applied!",
          discount: 10,
          discount_formatted: "-$10.00",
        },
      });

      await store.validatePromoCode();

      expect(store.promoValid).toBe(true);
      expect(store.promoMessage).toBe("Code applied!");
      expect(store.promoDiscount).toBe(10);
      expect(store.promoDiscountFormatted).toBe("-$10.00");
    });

    it("handles invalid code", async () => {
      store.promoCode = "INVALID";
      api.post.mockResolvedValue({
        data: { valid: false, message: "Invalid or expired code" },
      });

      await store.validatePromoCode();

      expect(store.promoValid).toBe(false);
      expect(store.promoMessage).toBe("Invalid or expired code");
      expect(store.promoDiscount).toBe(0);
    });

    it("handles API error", async () => {
      store.promoCode = "SAVE10";
      api.post.mockRejectedValue(new Error("Network error"));

      await store.validatePromoCode();

      expect(store.promoValid).toBe(false);
      expect(store.promoMessage).toBe("Failed to validate promo code.");
    });
  });

  describe("clearPromoCode", () => {
    it("clears all promo state", () => {
      store.promoCode = "SAVE10";
      store.promoValid = true;
      store.promoMessage = "Applied!";
      store.promoDiscount = 10;
      store.promoDiscountFormatted = "-$10.00";

      store.clearPromoCode();

      expect(store.promoCode).toBe("");
      expect(store.promoValid).toBe(false);
      expect(store.promoMessage).toBe("");
      expect(store.promoDiscount).toBe(0);
      expect(store.promoDiscountFormatted).toBeNull();
    });
  });

  describe("createOrder", () => {
    it("sets isCreatingOrder during creation", async () => {
      api.post.mockResolvedValue({
        data: { success: true, order: { id: 1, order_number: "ZM-123" } },
      });

      const promise = store.createOrder();
      expect(store.isCreatingOrder).toBe(true);
      await promise;
      expect(store.isCreatingOrder).toBe(false);
    });

    it("sends order data with addressId", async () => {
      store.deliveryForm.addressId = 123;
      store.paymentMethod = "cod";
      store.orderNotes = "Test note";
      store.deliveryInstructions = "Leave at door";

      api.post.mockResolvedValue({
        data: { success: true, order: { id: 1 } },
      });

      await store.createOrder();

      expect(api.post).toHaveBeenCalledWith("/checkout/create-order", {
        payment_method: "cod",
        notes: "Test note",
        delivery_instructions: "Leave at door",
        promo_code: null,
        address_id: 123,
      });
    });

    it("sends order data with new address", async () => {
      store.deliveryForm.addressId = null;
      store.deliveryForm.streetAddress = "123 Test St";
      store.deliveryForm.apartment = "Unit 5";
      store.deliveryForm.suburb = "Sydney";
      store.deliveryForm.state = "NSW";
      store.deliveryForm.postcode = "2000";
      store.deliveryForm.label = "Home";
      store.deliveryForm.saveAddress = true;
      store.paymentMethod = "stripe";

      api.post.mockResolvedValue({
        data: { success: true, order: { id: 1 } },
      });

      await store.createOrder();

      expect(api.post).toHaveBeenCalledWith(
        "/checkout/create-order",
        expect.objectContaining({
          street_address: "123 Test St",
          apartment: "Unit 5",
          suburb: "Sydney",
          state: "NSW",
          postcode: "2000",
          save_address: true,
          address_label: "Home",
        })
      );
    });

    it("includes valid promo code", async () => {
      store.promoCode = "SAVE10";
      store.promoValid = true;
      store.paymentMethod = "cod";

      api.post.mockResolvedValue({
        data: { success: true, order: { id: 1 } },
      });

      await store.createOrder();

      expect(api.post).toHaveBeenCalledWith(
        "/checkout/create-order",
        expect.objectContaining({
          promo_code: "SAVE10",
        })
      );
    });

    it("stores order on success", async () => {
      api.post.mockResolvedValue({
        data: { success: true, order: { id: 99, order_number: "ZM-99" } },
      });

      const result = await store.createOrder();

      expect(result.success).toBe(true);
      expect(store.order).toEqual({ id: 99, order_number: "ZM-99" });
      expect(store.orderId).toBe(99);
    });

    it("handles create failure", async () => {
      api.post.mockResolvedValue({
        data: { success: false, message: "Cart is empty" },
      });

      const result = await store.createOrder();

      expect(result.success).toBe(false);
      expect(store.error).toBe("Cart is empty");
    });

    it("handles API error", async () => {
      api.post.mockRejectedValue({
        response: { data: { message: "Stock unavailable" } },
      });

      const result = await store.createOrder();

      expect(result.success).toBe(false);
      expect(store.error).toBe("Stock unavailable");
    });
  });

  describe("processPayment", () => {
    beforeEach(() => {
      store.orderId = 123;
    });

    it("returns error if no orderId", async () => {
      store.orderId = null;

      const result = await store.processPayment();

      expect(result.success).toBe(false);
      expect(result.error).toBe("No order to process payment for");
    });

    it("sets isProcessingPayment during processing", async () => {
      api.post.mockResolvedValue({
        data: { success: true, order: {} },
      });
      store.paymentMethod = "cod";

      const promise = store.processPayment();
      expect(store.isProcessingPayment).toBe(true);
      await promise;
      expect(store.isProcessingPayment).toBe(false);
    });

    it("returns client secret for Stripe", async () => {
      store.paymentMethod = "stripe";
      api.post.mockResolvedValue({
        data: {
          success: true,
          client_secret: "pi_secret_123",
          payment_intent_id: "pi_123",
          publishable_key: "pk_test_123",
        },
      });

      const result = await store.processPayment();

      expect(result.success).toBe(true);
      expect(result.clientSecret).toBe("pi_secret_123");
      expect(result.paymentIntentId).toBe("pi_123");
    });

    it("returns order for COD", async () => {
      store.paymentMethod = "cod";
      api.post.mockResolvedValue({
        data: { success: true, order: { id: 123, status: "pending" } },
      });

      const result = await store.processPayment();

      expect(result.success).toBe(true);
      expect(result.order).toEqual({ id: 123, status: "pending" });
    });

    it("handles payment error", async () => {
      store.paymentMethod = "stripe";
      api.post.mockRejectedValue({
        response: { data: { message: "Card declined" } },
      });

      const result = await store.processPayment();

      expect(result.success).toBe(false);
      expect(store.error).toBe("Card declined");
    });
  });

  describe("confirmPayment", () => {
    beforeEach(() => {
      store.paymentMethod = "stripe";
    });

    it("sets isProcessingPayment during confirmation", async () => {
      api.post.mockResolvedValue({
        data: { success: true, order: {} },
      });

      const promise = store.confirmPayment({ payment_intent_id: "pi_123" });
      expect(store.isProcessingPayment).toBe(true);
      await promise;
      expect(store.isProcessingPayment).toBe(false);
    });

    it("stores order on success", async () => {
      api.post.mockResolvedValue({
        data: { success: true, order: { id: 123, status: "paid" } },
      });

      const result = await store.confirmPayment({
        payment_intent_id: "pi_123",
      });

      expect(result.success).toBe(true);
      expect(store.order).toEqual({ id: 123, status: "paid" });
    });

    it("handles confirmation error", async () => {
      api.post.mockRejectedValue({
        response: { data: { message: "Payment not found" } },
      });

      const result = await store.confirmPayment({
        payment_intent_id: "pi_invalid",
      });

      expect(result.success).toBe(false);
      expect(store.error).toBe("Payment not found");
    });
  });

  describe("reset", () => {
    it("resets step to 1", () => {
      store.currentStep = 4;
      store.reset();
      expect(store.currentStep).toBe(1);
    });

    it("resets all steps to not completed", () => {
      store.steps.forEach((s) => (s.completed = true));
      store.reset();
      expect(store.steps.every((s) => !s.completed)).toBe(true);
    });

    it("resets delivery form", () => {
      store.deliveryForm.streetAddress = "123 Test St";
      store.deliveryForm.suburb = "Sydney";
      store.deliveryForm.postcode = "2000";
      store.deliveryForm.addressId = 123;

      store.reset();

      expect(store.deliveryForm.streetAddress).toBe("");
      expect(store.deliveryForm.suburb).toBe("");
      expect(store.deliveryForm.postcode).toBe("");
      expect(store.deliveryForm.addressId).toBeNull();
    });

    it("resets delivery zone info", () => {
      store.deliveryZone = { id: 1 };
      store.deliveryFee = 10;
      store.deliveryFeeFormatted = "$10.00";
      store.estimatedDays = 2;
      store.deliversToArea = true;
      store.deliveryMessage = "Test";

      store.reset();

      expect(store.deliveryZone).toBeNull();
      expect(store.deliveryFee).toBe(0);
      expect(store.deliveryFeeFormatted).toBe("$0.00");
      expect(store.estimatedDays).toBeNull();
      expect(store.deliversToArea).toBeNull();
      expect(store.deliveryMessage).toBe("");
    });

    it("resets promo code", () => {
      store.promoCode = "SAVE10";
      store.promoDiscount = 10;
      store.promoValid = true;

      store.reset();

      expect(store.promoCode).toBe("");
      expect(store.promoDiscount).toBe(0);
      expect(store.promoValid).toBe(false);
    });

    it("resets payment and notes", () => {
      store.paymentMethod = "paypal";
      store.orderNotes = "Test";
      store.deliveryInstructions = "Leave at door";

      store.reset();

      expect(store.paymentMethod).toBe("stripe");
      expect(store.orderNotes).toBe("");
      expect(store.deliveryInstructions).toBe("");
    });

    it("resets order", () => {
      store.order = { id: 123 };
      store.orderId = 123;
      store.error = "Some error";

      store.reset();

      expect(store.order).toBeNull();
      expect(store.orderId).toBeNull();
      expect(store.error).toBeNull();
    });
  });
});
