<script setup>
/**
 * Checkout Page
 *
 * Multi-step checkout process with delivery, payment, review, and confirmation.
 *
 * @requirement CHK-001 Create checkout page layout
 * @requirement CHK-002 Implement step indicator
 */
import { onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCheckoutStore } from '@/stores/checkout'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import StepIndicator from '@/components/checkout/StepIndicator.vue'
import DeliveryStep from '@/components/checkout/DeliveryStep.vue'
import PaymentStep from '@/components/checkout/PaymentStep.vue'
import ReviewStep from '@/components/checkout/ReviewStep.vue'
import ConfirmationStep from '@/components/checkout/ConfirmationStep.vue'
import OrderSummary from '@/components/checkout/OrderSummary.vue'

const router = useRouter()
const checkoutStore = useCheckoutStore()
const cartStore = useCartStore()
const authStore = useAuthStore()

// Redirect if cart is empty
const hasItems = computed(() => cartStore.items.length > 0)

onMounted(async () => {
  // Check if user is logged in
  if (!authStore.isAuthenticated) {
    router.push('/login?redirect=/checkout')
    return
  }

  // Check if cart has items
  if (!hasItems.value) {
    router.push('/cart')
    return
  }

  // Initialize checkout
  await checkoutStore.initCheckout()
})

const currentStep = computed(() => checkoutStore.currentStep)
const isLoading = computed(() => checkoutStore.isLoading)
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading overlay -->
    <div
      v-if="isLoading"
      class="fixed inset-0 z-50 flex items-center justify-center bg-white bg-opacity-75"
    >
      <div class="text-center">
        <div
          class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-emerald-500 border-t-transparent"
        ></div>
        <p class="mt-4 text-gray-600">Loading checkout...</p>
      </div>
    </div>

    <!-- Main checkout content -->
    <div v-else class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
        <p class="mt-2 text-gray-600">Complete your order securely</p>
      </div>

      <!-- Step Indicator -->
      <StepIndicator :current-step="currentStep" :steps="checkoutStore.steps" class="mb-8" />

      <!-- Checkout Grid -->
      <div class="lg:grid lg:grid-cols-12 lg:gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-7">
          <!-- Step 1: Delivery -->
          <DeliveryStep v-if="currentStep === 1" />

          <!-- Step 2: Payment -->
          <PaymentStep v-else-if="currentStep === 2" />

          <!-- Step 3: Review -->
          <ReviewStep v-else-if="currentStep === 3" />

          <!-- Step 4: Confirmation -->
          <ConfirmationStep v-else-if="currentStep === 4" />
        </div>

        <!-- Order Summary Sidebar -->
        <div v-if="currentStep < 4" class="mt-8 lg:col-span-5 lg:mt-0">
          <OrderSummary />
        </div>
      </div>
    </div>
  </div>
</template>
