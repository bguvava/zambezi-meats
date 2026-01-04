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
  router.push('/customer/orders')
}

const handleContinueShopping = () => {
  checkoutStore.reset()
  router.push('/shop')
}
</script>

<template>
  <div class="mx-auto max-w-4xl px-4 py-8">
    <!-- Success Header -->
    <div class="mb-8 text-center">
      <div
        class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-lg">
        <CheckCircleIcon class="h-12 w-12 text-white" />
      </div>
      <h1 class="mb-2 text-3xl font-bold text-gray-900">Order Confirmed!</h1>
      <p v-if="order" class="text-lg text-gray-600">
        Thank you for your order, <span class="font-semibold text-gray-900">{{ order.user?.name || 'Customer' }}</span>!
      </p>
    </div>

    <!-- Order Details Card -->
    <div class="mb-8 overflow-hidden rounded-xl bg-white shadow-md">
      <!-- Order & Invoice Numbers -->
      <div class="border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-6">
        <div class="grid gap-6 md:grid-cols-2">
          <!-- Order Number -->
          <div>
            <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-500">Order Number</p>
            <p class="text-2xl font-bold text-emerald-600">
              {{ order?.order_number || 'ZM-XXXXXXXX-XXXX' }}
            </p>
          </div>

          <!-- Invoice Number -->
          <div v-if="order?.invoice">
            <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-500">Invoice Number</p>
            <div class="flex items-baseline gap-3">
              <p class="text-xl font-bold text-gray-900">
                {{ order.invoice.invoice_number }}
              </p>
              <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold capitalize text-gray-700 shadow-sm">
                {{ order.invoice.status }}
              </span>
            </div>
          </div>
        </div>

        <!-- Email Confirmation -->
        <div class="mt-4 flex items-center gap-2 rounded-lg bg-white/60 px-4 py-3">
          <EnvelopeIcon class="h-5 w-5 flex-shrink-0 text-emerald-600" />
          <p class="text-sm text-gray-700">
            Confirmation email sent to <span class="font-semibold">{{ order?.user?.email || 'your email' }}</span>
          </p>
        </div>
      </div>

      <!-- What's Next Section -->
      <div class="px-6 py-8">
        <h2 class="mb-6 text-center text-lg font-bold text-gray-900">What's Next?</h2>

        <div class="relative">
          <!-- Progress Line -->
          <div class="absolute left-0 top-6 h-1 w-full bg-gray-200" aria-hidden="true">
            <div class="h-full bg-emerald-500 transition-all duration-500" style="width: 25%"></div>
          </div>

          <!-- Steps -->
          <div class="relative grid grid-cols-4 gap-2">
            <div v-for="(step, index) in statusSteps" :key="step.name" class="flex flex-col items-center">
              <!-- Circle -->
              <div
                class="mb-3 flex h-12 w-12 items-center justify-center rounded-full border-4 bg-white shadow-sm transition-all"
                :class="{
                  'border-emerald-500 bg-emerald-500 shadow-emerald-200': step.status === 'complete',
                  'border-gray-200': step.status !== 'complete'
                }">
                <CheckCircleIcon v-if="step.status === 'complete'" class="h-7 w-7 text-white" />
                <span v-else class="text-base font-bold text-gray-400">{{ index + 1 }}</span>
              </div>

              <!-- Label -->
              <span class="text-center text-sm font-semibold" :class="{
                'text-emerald-600': step.status === 'complete',
                'text-gray-500': step.status !== 'complete'
              }">
                {{ step.name }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid gap-8 lg:grid-cols-3">
      <!-- Left Column: Order Summary (2/3 width) -->
      <div v-if="order" class="lg:col-span-2">
        <div class="rounded-xl bg-white p-6 shadow-md">
          <h2 class="mb-4 text-lg font-bold text-gray-900">Order Summary</h2>

          <!-- Items List -->
          <div class="divide-y divide-gray-100">
            <div v-for="item in order.items" :key="item.id" class="flex items-start justify-between gap-4 py-4">
              <div class="flex-1">
                <p class="font-semibold text-gray-900">{{ item.product_name }}</p>
                <p class="mt-1 text-sm text-gray-500">Quantity: {{ item.quantity }}</p>
              </div>
              <p class="whitespace-nowrap font-bold text-gray-900">{{ item.total_price_formatted }}</p>
            </div>
          </div>

          <!-- Pricing Summary -->
          <div class="mt-6 space-y-3 border-t border-gray-200 pt-4">
            <div class="flex justify-between text-gray-600">
              <span>Subtotal</span>
              <span class="font-semibold">{{ order.subtotal_formatted }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>Delivery Fee</span>
              <span class="font-semibold" :class="{ 'text-emerald-600': order.delivery_fee === 0 }">
                {{ order.delivery_fee === 0 ? 'FREE' : order.delivery_fee_formatted }}
              </span>
            </div>
            <div v-if="order.discount > 0" class="flex justify-between text-emerald-600">
              <span>Discount</span>
              <span class="font-semibold">-{{ order.discount_formatted }}</span>
            </div>
            <div class="flex justify-between border-t-2 border-gray-300 pt-3 text-xl font-bold text-gray-900">
              <span>Total</span>
              <span class="text-emerald-600">{{ order.total_formatted }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Delivery & Payment Info (1/3 width) -->
      <div v-if="order" class="space-y-6 lg:col-span-1">
        <!-- Delivery Address -->
        <div class="rounded-xl bg-white p-6 shadow-md">
          <div class="mb-4 flex items-center gap-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
              <TruckIcon class="h-5 w-5 text-emerald-600" />
            </div>
            <h3 class="font-bold text-gray-900">Delivery Address</h3>
          </div>
          <div v-if="order.address" class="text-sm leading-relaxed text-gray-700">
            <p class="font-medium text-gray-900">{{ order.address.street_address }}</p>
            <p>{{ order.address.suburb }}, {{ order.address.state }}</p>
            <p>{{ order.address.postcode }}</p>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="rounded-xl bg-white p-6 shadow-md">
          <div class="mb-4 flex items-center gap-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
              <ShoppingBagIcon class="h-5 w-5 text-blue-600" />
            </div>
            <h3 class="font-bold text-gray-900">Payment Method</h3>
          </div>
          <div v-if="order.payment" class="text-sm">
            <p class="font-semibold text-gray-900">{{ order.payment.gateway_label }}</p>
            <p
              class="mt-2 inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
              {{ order.payment.status_label }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:justify-center">
      <button type="button"
        class="rounded-lg border-2 border-gray-300 bg-white px-8 py-3 font-bold text-gray-700 shadow-sm transition-all hover:border-gray-400 hover:bg-gray-50 hover:shadow-md"
        @click="handleViewOrders">
        View My Orders
      </button>
      <button type="button"
        class="rounded-lg bg-gradient-to-r from-emerald-500 to-emerald-600 px-8 py-3 font-bold text-white shadow-md transition-all hover:from-emerald-600 hover:to-emerald-700 hover:shadow-lg"
        @click="handleContinueShopping">
        Continue Shopping
      </button>
    </div>
  </div>
</template>
