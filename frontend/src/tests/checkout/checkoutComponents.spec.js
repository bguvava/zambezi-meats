/**
 * Checkout Components Tests
 *
 * Comprehensive tests for checkout UI components.
 *
 * @requirement CHK-001 to CHK-030 Checkout module
 * @requirement CHK-030 Write checkout module tests
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { mount, shallowMount } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";

// Mock vue-router
vi.mock("vue-router", () => ({
  useRouter: vi.fn(() => ({
    push: vi.fn(),
  })),
  useRoute: vi.fn(() => ({
    params: {},
    query: {},
  })),
}));

// Mock heroicons
vi.mock("@heroicons/vue/24/outline", () => ({
  MapPinIcon: { template: "<svg />" },
  CheckCircleIcon: { template: "<svg />" },
  ExclamationCircleIcon: { template: "<svg />" },
  TruckIcon: { template: "<svg />" },
  CreditCardIcon: { template: "<svg />" },
  ArrowLeftIcon: { template: "<svg />" },
  ClipboardDocumentListIcon: { template: "<svg />" },
  TagIcon: { template: "<svg />" },
  XCircleIcon: { template: "<svg />" },
  XMarkIcon: { template: "<svg />" },
  EnvelopeIcon: { template: "<svg />" },
  ShoppingBagIcon: { template: "<svg />" },
}));

vi.mock("@heroicons/vue/24/solid", () => ({
  CheckIcon: { template: "<svg />" },
}));

// Mock currency store
vi.mock("@/stores/currency", () => ({
  useCurrencyStore: vi.fn(() => ({
    currentCurrency: "AUD",
    format: vi.fn((amount) => `$${amount.toFixed(2)}`),
  })),
}));

// Mock cart store
vi.mock("@/stores/cart", () => ({
  useCartStore: vi.fn(() => ({
    items: [],
    subtotal: 0,
    subtotalFormatted: "$0.00",
  })),
}));

// Mock checkout store for OrderSummary tests
const mockCheckoutStore = {
  currentStep: 1,
  steps: [
    { id: 1, name: "Delivery", completed: false },
    { id: 2, name: "Payment", completed: false },
    { id: 3, name: "Review", completed: false },
    { id: 4, name: "Complete", completed: false },
  ],
  deliveryForm: {
    streetAddress: "",
    apartment: "",
    suburb: "",
    state: "NSW",
    postcode: "",
    addressId: null,
  },
  savedAddresses: [],
  deliveryZone: null,
  deliveryFee: 0,
  deliveryFeeFormatted: "$0.00",
  estimatedDays: null,
  deliversToArea: null,
  deliveryMessage: "",
  promoCode: "",
  promoDiscount: 0,
  promoDiscountFormatted: null,
  promoValid: false,
  promoMessage: "",
  paymentMethod: "stripe",
  paymentMethods: [],
  orderNotes: "",
  deliveryInstructions: "",
  order: null,
  orderId: null,
  isLoading: false,
  isValidatingAddress: false,
  isCalculatingFee: false,
  isValidatingPromo: false,
  isCreatingOrder: false,
  isProcessingPayment: false,
  error: null,
  subtotal: 0,
  subtotalFormatted: "$0.00",
  total: 0,
  totalFormatted: "$0.00",
};

import StepIndicator from "@/components/checkout/StepIndicator.vue";
import PromoCodeInput from "@/components/checkout/PromoCodeInput.vue";
import PaymentMethodSelector from "@/components/checkout/PaymentMethodSelector.vue";
import OrderSummary from "@/components/checkout/OrderSummary.vue";
import { useCheckoutStore } from "@/stores/checkout";
import { useCartStore } from "@/stores/cart";

describe("StepIndicator", () => {
  const steps = [
    { id: 1, name: "Delivery", completed: false },
    { id: 2, name: "Payment", completed: false },
    { id: 3, name: "Review", completed: false },
    { id: 4, name: "Complete", completed: false },
  ];

  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("renders all steps", () => {
    const wrapper = mount(StepIndicator, {
      props: {
        currentStep: 1,
        steps: steps,
      },
    });

    expect(wrapper.text()).toContain("Delivery");
    expect(wrapper.text()).toContain("Payment");
    expect(wrapper.text()).toContain("Review");
    expect(wrapper.text()).toContain("Complete");
  });

  it("shows correct step number for non-completed steps", () => {
    const wrapper = mount(StepIndicator, {
      props: {
        currentStep: 1,
        steps: steps,
      },
    });

    const stepCircles = wrapper.findAll("span.rounded-full");
    expect(stepCircles[0].text()).toBe("1");
    expect(stepCircles[1].text()).toBe("2");
    expect(stepCircles[2].text()).toBe("3");
    expect(stepCircles[3].text()).toBe("4");
  });

  it("highlights current step", () => {
    const wrapper = mount(StepIndicator, {
      props: {
        currentStep: 2,
        steps: steps,
      },
    });

    const stepCircles = wrapper.findAll("span.rounded-full");
    expect(stepCircles[1].classes()).toContain("border-emerald-500");
  });

  it("shows check icon for completed steps", () => {
    const completedSteps = [
      { id: 1, name: "Delivery", completed: true },
      { id: 2, name: "Payment", completed: false },
      { id: 3, name: "Review", completed: false },
      { id: 4, name: "Complete", completed: false },
    ];

    const wrapper = mount(StepIndicator, {
      props: {
        currentStep: 2,
        steps: completedSteps,
      },
    });

    const firstStep = wrapper.findAll("span.rounded-full")[0];
    expect(firstStep.classes()).toContain("bg-emerald-500");
  });

  it("shows upcoming steps with gray styling", () => {
    const wrapper = mount(StepIndicator, {
      props: {
        currentStep: 1,
        steps: steps,
      },
    });

    const stepCircles = wrapper.findAll("span.rounded-full");
    expect(stepCircles[2].classes()).toContain("border-gray-300");
    expect(stepCircles[3].classes()).toContain("border-gray-300");
  });
});

describe("PromoCodeInput", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
  });

  it("renders promo code input field", () => {
    const wrapper = mount(PromoCodeInput);
    const input = wrapper.find("input");

    expect(input.exists()).toBe(true);
    expect(input.attributes("placeholder")).toBe("Enter promo code");
  });

  it("renders apply button", () => {
    const wrapper = mount(PromoCodeInput);
    const button = wrapper.find("button");

    expect(button.exists()).toBe(true);
    expect(button.text()).toContain("Apply");
  });

  it("disables apply button when input is empty", () => {
    const wrapper = mount(PromoCodeInput);
    const button = wrapper.find("button");

    expect(button.attributes("disabled")).toBeDefined();
  });

  it("updates store promo code on input", async () => {
    const wrapper = mount(PromoCodeInput);
    const store = useCheckoutStore();

    const input = wrapper.find("input");
    await input.setValue("SAVE10");

    expect(store.promoCode).toBe("SAVE10");
  });

  it("shows success message when promo is valid", async () => {
    const wrapper = mount(PromoCodeInput);
    const store = useCheckoutStore();

    store.promoValid = true;
    store.promoMessage = "Code applied! You save $10";
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain("Code applied! You save $10");
    expect(wrapper.find(".text-green-600").exists()).toBe(true);
  });

  it("shows error message when promo is invalid", async () => {
    const wrapper = mount(PromoCodeInput);
    const store = useCheckoutStore();

    store.promoValid = false;
    store.promoMessage = "Invalid or expired code";
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain("Invalid or expired code");
    expect(wrapper.find(".text-red-600").exists()).toBe(true);
  });

  it("disables input when promo is applied", async () => {
    const wrapper = mount(PromoCodeInput);
    const store = useCheckoutStore();

    store.promoValid = true;
    store.promoCode = "SAVE10";
    await wrapper.vm.$nextTick();

    const input = wrapper.find("input");
    expect(input.attributes("disabled")).toBeDefined();
  });

  it("shows loading spinner when validating", async () => {
    const wrapper = mount(PromoCodeInput);
    const store = useCheckoutStore();

    store.promoCode = "SAVE10";
    store.isValidatingPromo = true;
    await wrapper.vm.$nextTick();

    expect(wrapper.find(".animate-spin").exists()).toBe(true);
  });
});

describe("PaymentMethodSelector", () => {
  const paymentMethods = [
    { id: "stripe", name: "Credit Card", icon: "credit-card" },
    { id: "paypal", name: "PayPal", icon: "paypal" },
    { id: "cod", name: "Cash on Delivery", icon: "cash" },
  ];

  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("renders all payment methods", () => {
    const wrapper = mount(PaymentMethodSelector, {
      props: {
        methods: paymentMethods,
        selected: "stripe",
      },
    });

    expect(wrapper.text()).toContain("Credit Card");
    expect(wrapper.text()).toContain("PayPal");
    expect(wrapper.text()).toContain("Cash on Delivery");
  });

  it("highlights selected payment method", () => {
    const wrapper = mount(PaymentMethodSelector, {
      props: {
        methods: paymentMethods,
        selected: "paypal",
      },
    });

    // Find labels and check the one for paypal has the selected class
    const labels = wrapper.findAll("label");
    const paypalLabel = labels.find((l) => l.text().includes("PayPal"));
    expect(paypalLabel.classes()).toContain("border-emerald-500");
  });

  it("emits select event on input change", async () => {
    const wrapper = mount(PaymentMethodSelector, {
      props: {
        methods: paymentMethods,
        selected: "stripe",
      },
    });

    // Find the radio input for cod
    const inputs = wrapper.findAll('input[type="radio"]');
    // Third input should be cod
    await inputs[2].trigger("change");

    expect(wrapper.emitted().select).toBeTruthy();
    expect(wrapper.emitted().select[0]).toEqual(["cod"]);
  });

  it("renders empty when no methods provided", () => {
    const wrapper = mount(PaymentMethodSelector, {
      props: {
        methods: [],
        selected: null,
      },
    });

    // No labels should be rendered
    expect(wrapper.findAll("label").length).toBe(0);
  });
});

describe("OrderSummary", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("renders order summary title", () => {
    const wrapper = mount(OrderSummary);

    expect(wrapper.text()).toContain("Order Summary");
  });

  it("displays subtotal section", async () => {
    const wrapper = mount(OrderSummary);

    expect(wrapper.text()).toContain("Subtotal");
  });

  it("displays delivery section", async () => {
    const wrapper = mount(OrderSummary);

    expect(wrapper.text()).toContain("Delivery");
  });

  it("displays delivery fee from store", async () => {
    const wrapper = mount(OrderSummary);
    const checkoutStore = useCheckoutStore();

    checkoutStore.deliveryFee = 10;
    checkoutStore.deliveryFeeFormatted = "$10.00";
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain("$10.00");
  });

  it("displays FREE when delivery is free", async () => {
    const wrapper = mount(OrderSummary);
    const checkoutStore = useCheckoutStore();

    checkoutStore.deliveryFee = 0;
    checkoutStore.deliveryFeeFormatted = "FREE";
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain("FREE");
  });

  it("displays promo discount when applied", async () => {
    const wrapper = mount(OrderSummary);
    const checkoutStore = useCheckoutStore();

    checkoutStore.promoValid = true;
    checkoutStore.promoCode = "SAVE10";
    checkoutStore.promoDiscountFormatted = "-$10.00";
    await wrapper.vm.$nextTick();

    expect(wrapper.text()).toContain("SAVE10");
    expect(wrapper.text()).toContain("-$10.00");
  });

  it("displays total section", async () => {
    const wrapper = mount(OrderSummary);

    expect(wrapper.text()).toContain("Total");
  });

  it("shows secure checkout badge", () => {
    const wrapper = mount(OrderSummary);

    expect(wrapper.text()).toContain("Secure Checkout");
  });
});
