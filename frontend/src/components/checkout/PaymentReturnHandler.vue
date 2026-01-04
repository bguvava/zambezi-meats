<script setup>
/**
 * Payment Return Handler Component
 *
 * Handles return from payment gateways (PayPal, Afterpay) after customer completes payment
 */
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCheckoutStore } from '@/stores/checkout'

const route = useRoute()
const router = useRouter()
const checkoutStore = useCheckoutStore()

const isProcessing = ref(true)
const error = ref(null)
const message = ref('Processing your payment...')

onMounted(async () => {
  // Get query parameters
  const { paypal_order_id, token, gateway } = route.query

  try {
    if (paypal_order_id) {
      // PayPal return
      message.value = 'Confirming PayPal payment...'
      const result = await checkoutStore.confirmPayment({ paypal_order_id })

      if (result.success) {
        // Move to confirmation step
        checkoutStore.order = result.order
        checkoutStore.nextStep()
        router.push('/checkout')
      } else {
        throw new Error(result.error || 'PayPal payment confirmation failed')
      }
    } else if (token && gateway === 'afterpay') {
      // Afterpay return
      message.value = 'Confirming Afterpay payment...'
      const result = await checkoutStore.confirmPayment({ token })

      if (result.success) {
        // Move to confirmation step
        checkoutStore.order = result.order
        checkoutStore.nextStep()
        router.push('/checkout')
      } else {
        throw new Error(result.error || 'Afterpay payment confirmation failed')
      }
    } else {
      throw new Error('Invalid payment return parameters')
    }
  } catch (err) {
    console.error('Payment return handling failed:', err)
    error.value = err.message || 'Payment confirmation failed'
    isProcessing.value = false
  }
})
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-50">
    <div class="w-full max-w-md rounded-lg bg-white p-8 shadow-md">
      <!-- Processing -->
      <div v-if="isProcessing && !error" class="text-center">
        <div class="mx-auto mb-4 h-16 w-16 animate-spin rounded-full border-4 border-emerald-500 border-t-transparent">
        </div>
        <h2 class="mb-2 text-xl font-semibold text-gray-900">{{ message }}</h2>
        <p class="text-gray-600">Please wait while we confirm your payment...</p>
      </div>

      <!-- Error -->
      <div v-if="error" class="text-center">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
          <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <h2 class="mb-2 text-xl font-semibold text-gray-900">Payment Confirmation Failed</h2>
        <p class="mb-6 text-gray-600">{{ error }}</p>
        <button @click="router.push('/checkout')"
          class="rounded-lg bg-emerald-500 px-6 py-3 font-semibold text-white transition-colors hover:bg-emerald-600">
          Return to Checkout
        </button>
      </div>
    </div>
  </div>
</template>
