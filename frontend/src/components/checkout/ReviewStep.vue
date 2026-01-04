<script setup>
/**
 * Review Step Component
 *
 * Final review before placing order.
 *
 * @requirement CHK-013 Create order review step
 */
import { computed, ref } from 'vue'
import { useCheckoutStore } from '@/stores/checkout'
import { useCartStore } from '@/stores/cart'
import {
  ClipboardDocumentListIcon,
  MapPinIcon,
  CreditCardIcon,
  TruckIcon,
  ArrowLeftIcon
} from '@heroicons/vue/24/outline'

const checkoutStore = useCheckoutStore()
const cartStore = useCartStore()

const isProcessing = computed(
  () => checkoutStore.isCreatingOrder || checkoutStore.isProcessingPayment
)
const error = computed(() => checkoutStore.error)

const handleBack = () => {
  checkoutStore.previousStep()
}

const handlePlaceOrder = async () => {
  // First create the order
  const orderResult = await checkoutStore.createOrder()

  if (!orderResult.success) {
    return
  }

  // Then process payment
  const paymentResult = await checkoutStore.processPayment()

  if (paymentResult.success) {
    // For COD, order is immediately confirmed with invoice
    if (checkoutStore.paymentMethod === 'cod') {
      // COD completes immediately, move to confirmation
      checkoutStore.nextStep()
    } else if (checkoutStore.paymentMethod === 'stripe') {
      // For Stripe, the client_secret is returned but actual confirmation happens client-side
      // In a real implementation, you would:
      // 1. Load Stripe.js
      // 2. Use stripe.confirmCardPayment(paymentResult.clientSecret)
      // 3. On success, call confirmPayment endpoint
      // 
      // For now, we'll simulate the flow for testing
      console.log('Stripe payment initiated with client_secret:', paymentResult.clientSecret)
      console.log('In production, this would integrate with Stripe Elements')

      // Note: Without Stripe.js integration, we cannot complete this flow
      // The payment will remain pending until confirmed client-side
      checkoutStore.error = 'Stripe integration requires Stripe.js library. Please contact support.'
    } else if (checkoutStore.paymentMethod === 'paypal' || checkoutStore.paymentMethod === 'afterpay') {
      // PayPal and Afterpay redirect to their sites
      // The redirect already happened in processPayment()
      // User will return via /checkout/confirm route
      console.log(`Redirecting to ${checkoutStore.paymentMethod} for payment...`)
      // No action needed - redirect already occurred
    }
  }
}

// Format address for display
const formattedAddress = computed(() => {
  const form = checkoutStore.deliveryForm
  let address = form.streetAddress
  if (form.apartment) address += `, ${form.apartment}`
  address += `\n${form.suburb}, ${form.state} ${form.postcode}`
  return address
})

// Get payment method name
const paymentMethodName = computed(() => {
  const method = checkoutStore.paymentMethods.find(
    m => m.id === checkoutStore.paymentMethod
  )
  return method?.name || 'Unknown'
})
</script>

<template>
  <div class="rounded-lg bg-white p-6 shadow-sm">
    <div class="mb-6 flex items-center">
      <ClipboardDocumentListIcon class="mr-3 h-6 w-6 text-emerald-500" />
      <h2 class="text-xl font-semibold text-gray-900">Review Your Order</h2>
    </div>

    <!-- Error message -->
    <div v-if="error" class="mb-6 rounded-lg bg-red-50 p-4 text-red-700">
      <p class="font-medium">{{ error }}</p>
      <p class="mt-1 text-sm">Please try again or contact support.</p>
    </div>

    <div class="space-y-6">
      <!-- Delivery Address -->
      <div class="rounded-lg border border-gray-200 p-4">
        <div class="mb-3 flex items-center justify-between">
          <div class="flex items-center">
            <MapPinIcon class="mr-2 h-5 w-5 text-gray-400" />
            <h3 class="font-medium text-gray-900">Delivery Address</h3>
          </div>
          <button type="button" class="text-sm font-medium text-emerald-600 hover:text-emerald-700"
            @click="checkoutStore.goToStep(1)">
            Edit
          </button>
        </div>
        <p class="whitespace-pre-line text-gray-600">{{ formattedAddress }}</p>
        <div v-if="checkoutStore.deliveryInstructions" class="mt-2 text-sm text-gray-500">
          <span class="font-medium">Instructions:</span> {{ checkoutStore.deliveryInstructions }}
        </div>
      </div>

      <!-- Delivery Info -->
      <div class="rounded-lg border border-gray-200 p-4">
        <div class="flex items-center">
          <TruckIcon class="mr-2 h-5 w-5 text-gray-400" />
          <h3 class="font-medium text-gray-900">Delivery</h3>
        </div>
        <div class="mt-3 flex items-center justify-between text-gray-600">
          <span>{{ checkoutStore.deliveryZone?.name || 'Standard Delivery' }}</span>
          <span class="font-medium" :class="{
            'text-emerald-600': checkoutStore.deliveryFee === 0
          }">
            {{ checkoutStore.deliveryFeeFormatted }}
          </span>
        </div>
        <p v-if="checkoutStore.estimatedDays" class="mt-1 text-sm text-gray-500">
          Estimated delivery: {{ checkoutStore.estimatedDays === 1 ? 'Next business day' :
            `${checkoutStore.estimatedDays} business days` }}
        </p>
      </div>

      <!-- Payment Method -->
      <div class="rounded-lg border border-gray-200 p-4">
        <div class="mb-3 flex items-center justify-between">
          <div class="flex items-center">
            <CreditCardIcon class="mr-2 h-5 w-5 text-gray-400" />
            <h3 class="font-medium text-gray-900">Payment Method</h3>
          </div>
          <button type="button" class="text-sm font-medium text-emerald-600 hover:text-emerald-700"
            @click="checkoutStore.goToStep(2)">
            Edit
          </button>
        </div>
        <p class="text-gray-600">{{ paymentMethodName }}</p>
      </div>

      <!-- Order Items -->
      <div class="rounded-lg border border-gray-200 p-4">
        <h3 class="mb-4 font-medium text-gray-900">Order Items</h3>
        <div class="space-y-3">
          <div v-for="item in cartStore.items" :key="item.id" class="flex items-center justify-between">
            <div class="flex items-center">
              <img :src="item.image || '/images/placeholder.jpg'" :alt="item.product_name"
                class="h-12 w-12 rounded-lg object-cover" />
              <div class="ml-3">
                <p class="font-medium text-gray-900">{{ item.product_name }}</p>
                <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
              </div>
            </div>
            <p class="font-medium text-gray-900">
              ${{ (item.unit_price * item.quantity).toFixed(2) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Order Notes -->
      <div v-if="checkoutStore.orderNotes" class="rounded-lg border border-gray-200 p-4">
        <h3 class="mb-2 font-medium text-gray-900">Order Notes</h3>
        <p class="text-gray-600">{{ checkoutStore.orderNotes }}</p>
      </div>
    </div>

    <!-- Totals Summary -->
    <div class="mt-6 border-t border-gray-200 pt-6">
      <div class="space-y-2">
        <div class="flex justify-between text-gray-600">
          <span>Subtotal</span>
          <span>{{ checkoutStore.subtotalFormatted }}</span>
        </div>
        <div class="flex justify-between text-gray-600">
          <span>Delivery</span>
          <span :class="{ 'text-emerald-600': checkoutStore.deliveryFee === 0 }">
            {{ checkoutStore.deliveryFeeFormatted }}
          </span>
        </div>
        <div v-if="checkoutStore.promoValid" class="flex justify-between text-emerald-600">
          <span>Discount ({{ checkoutStore.promoCode }})</span>
          <span>{{ checkoutStore.promoDiscountFormatted }}</span>
        </div>
        <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-semibold text-gray-900">
          <span>Total</span>
          <span>{{ checkoutStore.totalFormatted }} AUD</span>
        </div>
      </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="mt-8 flex justify-between">
      <button type="button" :disabled="isProcessing"
        class="flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
        @click="handleBack">
        <ArrowLeftIcon class="mr-2 h-5 w-5" />
        Back
      </button>

      <button type="button" :disabled="isProcessing"
        class="rounded-lg bg-emerald-500 px-8 py-3 font-semibold text-white transition-colors hover:bg-emerald-600 disabled:cursor-not-allowed disabled:bg-emerald-400"
        @click="handlePlaceOrder">
        <span v-if="isProcessing" class="flex items-center">
          <span
            class="mr-2 inline-block h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
          Processing...
        </span>
        <span v-else>
          Place Order - {{ checkoutStore.totalFormatted }}
        </span>
      </button>
    </div>
  </div>
</template>
