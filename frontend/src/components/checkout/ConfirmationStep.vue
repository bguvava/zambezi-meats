<script setup>
/**
 * Confirmation Step Component
 *
 * Order confirmation page after successful checkout.
 *
 * @requirement CHK-016 Handle payment success
 */
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCheckoutStore } from '@/stores/checkout'
import { useCartStore } from '@/stores/cart'
import {
  CheckCircleIcon,
  EnvelopeIcon,
  TruckIcon,
  ShoppingBagIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const checkoutStore = useCheckoutStore()
const cartStore = useCartStore()

const order = computed(() => checkoutStore.order)

// Order status steps
const statusSteps = [
  { name: 'Accepted', status: 'complete' },
  { name: 'Preparing', status: 'upcoming' },
  { name: 'Delivery', status: 'upcoming' },
  { name: 'Delivered', status: 'upcoming' }
]

onMounted(() => {
  // Clear cart after successful order
  cartStore.clearCart()
})

const handleViewOrders = () => {
  router.push('/account/orders')
}

const handleContinueShopping = () => {
  checkoutStore.reset()
  router.push('/shop')
}
</script>

<template>
  <div class="mx-auto max-w-2xl">
    <div class="rounded-lg bg-white p-8 text-center shadow-sm">
      <!-- Success Icon -->
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
        <CheckCircleIcon class="h-10 w-10 text-green-500" />
      </div>

      <!-- Title -->
      <h1 class="mt-6 text-2xl font-bold text-gray-900">Order Confirmed!</h1>
      <p v-if="order" class="mt-2 text-gray-600">
        Thank you for your order, {{ order.user?.name || 'Customer' }}!
      </p>

      <!-- Order Number -->
      <div class="mt-6 rounded-lg bg-gray-50 p-4">
        <p class="text-sm text-gray-600">Order Number</p>
        <p class="text-xl font-bold text-emerald-600">
          {{ order?.order_number || 'ZM-XXXXXXXX-XXXX' }}
        </p>
      </div>

      <!-- Email notification -->
      <div class="mt-6 flex items-center justify-center text-sm text-gray-600">
        <EnvelopeIcon class="mr-2 h-5 w-5 text-gray-400" />
        <span>We've sent a confirmation email to <strong>{{ order?.user?.email || 'your email' }}</strong></span>
      </div>
    </div>

    <!-- Order Status Tracker -->
    <div class="mt-8 rounded-lg bg-white p-6 shadow-sm">
      <h2 class="mb-6 text-center font-semibold text-gray-900">What's Next?</h2>

      <div class="flex items-center justify-between">
        <div
          v-for="(step, index) in statusSteps"
          :key="step.name"
          class="flex flex-1 flex-col items-center"
        >
          <!-- Circle -->
          <div
            class="flex h-10 w-10 items-center justify-center rounded-full"
            :class="{
              'bg-emerald-500': step.status === 'complete',
              'bg-gray-200': step.status !== 'complete'
            }"
          >
            <CheckCircleIcon v-if="step.status === 'complete'" class="h-6 w-6 text-white" />
            <span v-else class="text-gray-500">{{ index + 1 }}</span>
          </div>

          <!-- Label -->
          <span
            class="mt-2 text-xs font-medium"
            :class="{
              'text-emerald-600': step.status === 'complete',
              'text-gray-500': step.status !== 'complete'
            }"
          >
            {{ step.name }}
          </span>

          <!-- Connector -->
          <div
            v-if="index < statusSteps.length - 1"
            class="absolute h-0.5 w-full"
            :class="{
              'bg-emerald-500': step.status === 'complete',
              'bg-gray-200': step.status !== 'complete'
            }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Order Summary -->
    <div v-if="order" class="mt-8 rounded-lg bg-white p-6 shadow-sm">
      <h2 class="mb-4 font-semibold text-gray-900">Order Summary</h2>

      <!-- Items -->
      <div class="divide-y divide-gray-200">
        <div
          v-for="item in order.items"
          :key="item.id"
          class="flex items-center justify-between py-3"
        >
          <div>
            <p class="font-medium text-gray-900">{{ item.product_name }}</p>
            <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
          </div>
          <p class="font-medium text-gray-900">{{ item.total_price_formatted }}</p>
        </div>
      </div>

      <!-- Totals -->
      <div class="mt-4 space-y-2 border-t border-gray-200 pt-4">
        <div class="flex justify-between text-gray-600">
          <span>Subtotal</span>
          <span>{{ order.subtotal_formatted }}</span>
        </div>
        <div class="flex justify-between text-gray-600">
          <span>Delivery</span>
          <span :class="{ 'text-emerald-600': order.delivery_fee === 0 }">
            {{ order.delivery_fee_formatted }}
          </span>
        </div>
        <div v-if="order.discount > 0" class="flex justify-between text-emerald-600">
          <span>Discount</span>
          <span>{{ order.discount_formatted }}</span>
        </div>
        <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-bold text-gray-900">
          <span>Total</span>
          <span>{{ order.total_formatted }}</span>
        </div>
      </div>
    </div>

    <!-- Delivery Address & Payment -->
    <div v-if="order" class="mt-8 grid gap-6 sm:grid-cols-2">
      <!-- Delivery Address -->
      <div class="rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-3 flex items-center">
          <TruckIcon class="mr-2 h-5 w-5 text-gray-400" />
          <h3 class="font-semibold text-gray-900">Delivery Address</h3>
        </div>
        <p v-if="order.address" class="text-gray-600">
          {{ order.address.street_address }}<br />
          {{ order.address.suburb }}, {{ order.address.state }} {{ order.address.postcode }}
        </p>
      </div>

      <!-- Payment Method -->
      <div class="rounded-lg bg-white p-6 shadow-sm">
        <div class="mb-3 flex items-center">
          <ShoppingBagIcon class="mr-2 h-5 w-5 text-gray-400" />
          <h3 class="font-semibold text-gray-900">Payment</h3>
        </div>
        <p v-if="order.payment" class="text-gray-600">
          {{ order.payment.gateway_label }}<br />
          <span class="text-sm text-gray-500">
            Status: {{ order.payment.status_label }}
          </span>
        </p>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-center">
      <button
        type="button"
        class="rounded-lg border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 transition-colors hover:bg-gray-50"
        @click="handleViewOrders"
      >
        View My Orders
      </button>
      <button
        type="button"
        class="rounded-lg bg-emerald-500 px-6 py-3 font-semibold text-white transition-colors hover:bg-emerald-600"
        @click="handleContinueShopping"
      >
        Continue Shopping
      </button>
    </div>
  </div>
</template>
