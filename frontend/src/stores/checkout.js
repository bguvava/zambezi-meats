import { defineStore } from "pinia";
import { ref, computed, watch } from "vue";
import api from "@/services/api";
import { useCartStore } from "./cart";
import { useAuthStore } from "./auth";

/**
 * Checkout Store
 *
 * Manages the multi-step checkout process state.
 *
 * @requirement CHK-001 to CHK-030 Checkout module
 * @requirement CHK-029 Create checkout Pinia store
 */
export const useCheckoutStore = defineStore("checkout", () => {
  // Step management
  const currentStep = ref(1);
  const steps = ref([
    { id: 1, name: "Delivery", completed: false },
    { id: 2, name: "Payment", completed: false },
    { id: 3, name: "Review", completed: false },
    { id: 4, name: "Complete", completed: false },
  ]);

  // Delivery form data
  const deliveryForm = ref({
    addressId: null,
    label: "Delivery Address",
    streetAddress: "",
    apartment: "",
    suburb: "",
    state: "NSW",
    postcode: "",
    saveAddress: false,
  });

  // Saved addresses
  const savedAddresses = ref([]);

  // Delivery zone info
  const deliveryZone = ref(null);
  const deliveryFee = ref(0);
  const deliveryFeeFormatted = ref("$0.00");
  const estimatedDays = ref(null);
  const deliversToArea = ref(null);
  const deliveryMessage = ref("");

  // Promo code
  const promoCode = ref("");
  const promoDiscount = ref(0);
  const promoDiscountFormatted = ref(null);
  const promoValid = ref(false);
  const promoMessage = ref("");

  // Payment
  const paymentMethod = ref("stripe");
  const paymentMethods = ref([]);

  // Order notes
  const orderNotes = ref("");
  const deliveryInstructions = ref("");

  // Order result
  const order = ref(null);
  const orderId = ref(null);

  // Loading and error states
  const isLoading = ref(false);
  const isValidatingAddress = ref(false);
  const isCalculatingFee = ref(false);
  const isValidatingPromo = ref(false);
  const isCreatingOrder = ref(false);
  const isProcessingPayment = ref(false);
  const error = ref(null);

  // Computed
  const cartStore = useCartStore();
  const authStore = useAuthStore();

  const subtotal = computed(() => cartStore.subtotal);
  const subtotalFormatted = computed(() => cartStore.subtotalFormatted);

  const total = computed(() => {
    return subtotal.value + deliveryFee.value - promoDiscount.value;
  });

  const totalFormatted = computed(() => {
    return "$" + total.value.toFixed(2);
  });

  const isDeliveryValid = computed(() => {
    if (deliveryForm.value.addressId) {
      return true;
    }
    return (
      deliveryForm.value.streetAddress.trim() !== "" &&
      deliveryForm.value.suburb.trim() !== "" &&
      deliveryForm.value.state.trim() !== "" &&
      deliveryForm.value.postcode.match(/^\d{4}$/) &&
      deliversToArea.value === true
    );
  });

  const isPaymentValid = computed(() => {
    return paymentMethod.value !== null;
  });

  const canProceedToPayment = computed(() => {
    return isDeliveryValid.value && cartStore.items.length > 0;
  });

  const canProceedToReview = computed(() => {
    return canProceedToPayment.value && isPaymentValid.value;
  });

  // Australian states
  const australianStates = [
    { code: "ACT", name: "Australian Capital Territory" },
    { code: "NSW", name: "New South Wales" },
    { code: "NT", name: "Northern Territory" },
    { code: "QLD", name: "Queensland" },
    { code: "SA", name: "South Australia" },
    { code: "TAS", name: "Tasmania" },
    { code: "VIC", name: "Victoria" },
    { code: "WA", name: "Western Australia" },
  ];

  // Actions
  async function initCheckout() {
    isLoading.value = true;
    error.value = null;

    try {
      // Load checkout session data
      const response = await api.get("/checkout/session");

      if (response.data.addresses) {
        savedAddresses.value = response.data.addresses;
      }

      if (response.data.default_address) {
        selectAddress(response.data.default_address.id);
      }

      // Load payment methods
      await loadPaymentMethods();
    } catch (err) {
      console.error("Failed to initialize checkout:", err);
      error.value = "Failed to load checkout data. Please try again.";
    } finally {
      isLoading.value = false;
    }
  }

  async function loadPaymentMethods() {
    try {
      const response = await api.get("/checkout/payment-methods", {
        params: { subtotal: subtotal.value, currency: "AUD" },
      });

      if (response.data.methods) {
        paymentMethods.value = response.data.methods;
      }
    } catch (err) {
      console.error("Failed to load payment methods:", err);
    }
  }

  function selectAddress(addressId) {
    const address = savedAddresses.value.find((a) => a.id === addressId);
    if (address) {
      deliveryForm.value.addressId = addressId;
      deliveryForm.value.streetAddress = address.street_address;
      deliveryForm.value.apartment = address.apartment || "";
      deliveryForm.value.suburb = address.suburb;
      deliveryForm.value.state = address.state;
      deliveryForm.value.postcode = address.postcode;

      // Validate the address
      validateAddress();
    }
  }

  function clearSelectedAddress() {
    deliveryForm.value.addressId = null;
  }

  async function validateAddress() {
    if (
      !deliveryForm.value.postcode ||
      !deliveryForm.value.postcode.match(/^\d{4}$/)
    ) {
      return;
    }

    isValidatingAddress.value = true;
    error.value = null;

    try {
      const response = await api.post("/checkout/validate-address", {
        street_address: deliveryForm.value.streetAddress,
        suburb: deliveryForm.value.suburb,
        state: deliveryForm.value.state,
        postcode: deliveryForm.value.postcode,
      });

      deliversToArea.value = response.data.delivers;
      deliveryMessage.value = response.data.message;

      if (response.data.zone) {
        deliveryZone.value = response.data.zone;
      }

      // Calculate delivery fee if we deliver there
      if (deliversToArea.value) {
        await calculateDeliveryFee();
      } else {
        deliveryFee.value = 0;
        deliveryFeeFormatted.value = "N/A";
      }
    } catch (err) {
      console.error("Failed to validate address:", err);
      deliversToArea.value = false;
      deliveryMessage.value = "Unable to validate address. Please try again.";
    } finally {
      isValidatingAddress.value = false;
    }
  }

  async function calculateDeliveryFee() {
    if (!deliversToArea.value) return;

    isCalculatingFee.value = true;

    try {
      const response = await api.post("/checkout/calculate-fee", {
        suburb: deliveryForm.value.suburb,
        postcode: deliveryForm.value.postcode,
        subtotal: subtotal.value,
      });

      if (response.data.success) {
        deliveryFee.value = response.data.fee;
        deliveryFeeFormatted.value = response.data.is_free
          ? "FREE"
          : response.data.fee_formatted;
        estimatedDays.value = response.data.estimated_days;

        if (response.data.message) {
          deliveryMessage.value = response.data.message;
        }
      }
    } catch (err) {
      console.error("Failed to calculate delivery fee:", err);
    } finally {
      isCalculatingFee.value = false;
    }
  }

  async function validatePromoCode() {
    if (!promoCode.value.trim()) {
      promoValid.value = false;
      promoMessage.value = "";
      promoDiscount.value = 0;
      promoDiscountFormatted.value = null;
      return;
    }

    isValidatingPromo.value = true;

    try {
      const response = await api.post("/checkout/validate-promo", {
        code: promoCode.value,
        subtotal: subtotal.value,
      });

      promoValid.value = response.data.valid;
      promoMessage.value = response.data.message;

      if (response.data.valid) {
        promoDiscount.value = response.data.discount;
        promoDiscountFormatted.value = response.data.discount_formatted;
      } else {
        promoDiscount.value = 0;
        promoDiscountFormatted.value = null;
      }
    } catch (err) {
      console.error("Failed to validate promo code:", err);
      promoValid.value = false;
      promoMessage.value = "Failed to validate promo code.";
    } finally {
      isValidatingPromo.value = false;
    }
  }

  function clearPromoCode() {
    promoCode.value = "";
    promoValid.value = false;
    promoMessage.value = "";
    promoDiscount.value = 0;
    promoDiscountFormatted.value = null;
  }

  async function createOrder() {
    isCreatingOrder.value = true;
    error.value = null;

    try {
      const orderData = {
        payment_method: paymentMethod.value,
        notes: orderNotes.value || null,
        delivery_instructions: deliveryInstructions.value || null,
        promo_code: promoValid.value ? promoCode.value : null,
      };

      // Add address data
      if (deliveryForm.value.addressId) {
        orderData.address_id = deliveryForm.value.addressId;
      } else {
        orderData.street_address = deliveryForm.value.streetAddress;
        orderData.apartment = deliveryForm.value.apartment || null;
        orderData.suburb = deliveryForm.value.suburb;
        orderData.state = deliveryForm.value.state;
        orderData.postcode = deliveryForm.value.postcode;
        orderData.save_address = deliveryForm.value.saveAddress;
        orderData.address_label = deliveryForm.value.label;
      }

      const response = await api.post("/checkout/create-order", orderData);

      if (response.data.success) {
        order.value = response.data.order;
        orderId.value = response.data.order.id;
        return { success: true, order: response.data.order };
      } else {
        throw new Error(response.data.message || "Failed to create order");
      }
    } catch (err) {
      console.error("Failed to create order:", err);
      error.value =
        err.response?.data?.message || err.message || "Failed to create order";
      return { success: false, error: error.value };
    } finally {
      isCreatingOrder.value = false;
    }
  }

  async function processPayment() {
    if (!orderId.value) {
      error.value = "No order to process payment for";
      return { success: false, error: error.value };
    }

    isProcessingPayment.value = true;
    error.value = null;

    try {
      const endpoint = `/checkout/payment/${paymentMethod.value}`;
      const response = await api.post(endpoint, {
        order_id: orderId.value,
        return_url: `${window.location.origin}/checkout/confirm`,
        cancel_url: `${window.location.origin}/checkout/payment`,
      });

      if (response.data.success) {
        // Handle different payment flows
        if (paymentMethod.value === "stripe") {
          return {
            success: true,
            clientSecret: response.data.client_secret,
            paymentIntentId: response.data.payment_intent_id,
            publishableKey: response.data.publishable_key,
          };
        } else if (paymentMethod.value === "paypal") {
          // Redirect to PayPal
          if (response.data.approve_url) {
            window.location.href = response.data.approve_url;
          }
          return { success: true, redirecting: true };
        } else if (paymentMethod.value === "afterpay") {
          // Redirect to Afterpay
          if (response.data.redirect_url) {
            window.location.href = response.data.redirect_url;
          }
          return {
            success: true,
            redirecting: true,
            installments: response.data.installments,
          };
        } else if (paymentMethod.value === "cod") {
          // COD is immediately confirmed
          order.value = response.data.order;
          return { success: true, order: response.data.order };
        }
      }

      throw new Error(response.data.message || "Payment processing failed");
    } catch (err) {
      console.error("Payment processing failed:", err);
      error.value =
        err.response?.data?.message ||
        err.message ||
        "Payment processing failed";
      return { success: false, error: error.value };
    } finally {
      isProcessingPayment.value = false;
    }
  }

  async function confirmPayment(data = {}) {
    isProcessingPayment.value = true;
    error.value = null;

    try {
      const endpoint = `/checkout/payment/${paymentMethod.value}/confirm`;
      const response = await api.post(endpoint, data);

      if (response.data.success) {
        order.value = response.data.order;
        return { success: true, order: response.data.order };
      }

      throw new Error(response.data.message || "Payment confirmation failed");
    } catch (err) {
      console.error("Payment confirmation failed:", err);
      error.value =
        err.response?.data?.message ||
        err.message ||
        "Payment confirmation failed";
      return { success: false, error: error.value };
    } finally {
      isProcessingPayment.value = false;
    }
  }

  function goToStep(step) {
    // Validate before allowing step change
    if (step > currentStep.value) {
      if (step === 2 && !canProceedToPayment.value) return;
      if (step === 3 && !canProceedToReview.value) return;
    }

    currentStep.value = step;
  }

  function nextStep() {
    if (currentStep.value < 4) {
      // Mark current step as completed
      const currentStepObj = steps.value.find(
        (s) => s.id === currentStep.value
      );
      if (currentStepObj) {
        currentStepObj.completed = true;
      }
      currentStep.value++;
    }
  }

  function previousStep() {
    if (currentStep.value > 1) {
      currentStep.value--;
    }
  }

  function reset() {
    currentStep.value = 1;
    steps.value.forEach((step) => {
      step.completed = false;
    });

    deliveryForm.value = {
      addressId: null,
      label: "Delivery Address",
      streetAddress: "",
      apartment: "",
      suburb: "",
      state: "NSW",
      postcode: "",
      saveAddress: false,
    };

    deliveryZone.value = null;
    deliveryFee.value = 0;
    deliveryFeeFormatted.value = "$0.00";
    estimatedDays.value = null;
    deliversToArea.value = null;
    deliveryMessage.value = "";

    promoCode.value = "";
    promoDiscount.value = 0;
    promoDiscountFormatted.value = null;
    promoValid.value = false;
    promoMessage.value = "";

    paymentMethod.value = "stripe";
    orderNotes.value = "";
    deliveryInstructions.value = "";

    order.value = null;
    orderId.value = null;
    error.value = null;
  }

  // Watch for postcode changes to trigger validation
  watch(
    () => deliveryForm.value.postcode,
    (newVal) => {
      if (newVal && newVal.match(/^\d{4}$/)) {
        validateAddress();
      }
    }
  );

  return {
    // State
    currentStep,
    steps,
    deliveryForm,
    savedAddresses,
    deliveryZone,
    deliveryFee,
    deliveryFeeFormatted,
    estimatedDays,
    deliversToArea,
    deliveryMessage,
    promoCode,
    promoDiscount,
    promoDiscountFormatted,
    promoValid,
    promoMessage,
    paymentMethod,
    paymentMethods,
    orderNotes,
    deliveryInstructions,
    order,
    orderId,
    australianStates,

    // Loading states
    isLoading,
    isValidatingAddress,
    isCalculatingFee,
    isValidatingPromo,
    isCreatingOrder,
    isProcessingPayment,
    error,

    // Computed
    subtotal,
    subtotalFormatted,
    total,
    totalFormatted,
    isDeliveryValid,
    isPaymentValid,
    canProceedToPayment,
    canProceedToReview,

    // Actions
    initCheckout,
    loadPaymentMethods,
    selectAddress,
    clearSelectedAddress,
    validateAddress,
    calculateDeliveryFee,
    validatePromoCode,
    clearPromoCode,
    createOrder,
    processPayment,
    confirmPayment,
    goToStep,
    nextStep,
    previousStep,
    reset,
  };
});
