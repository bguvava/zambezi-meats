<script setup>
/**
 * Order Summary Component
 *
 * Sidebar showing cart items and totals during checkout.
 *
 * @requirement CHK-013 Order summary
 */
import { computed } from 'vue'
import { useCheckoutStore } from '@/stores/checkout'
import { useCartStore } from '@/stores/cart'
import { useCurrencyStore } from '@/stores/currency'
import { ShoppingBagIcon } from '@heroicons/vue/24/outline'

const checkoutStore = useCheckoutStore()
const cartStore = useCartStore()
const currencyStore = useCurrencyStore()

const items = computed(() => cartStore.items)

// Placeholder image path
const PLACEHOLDER_IMAGE = '/images/placeholder.jpg'

/**
 * Format price using currency store
 */
function formatPrice(amount) {
  return currencyStore.format(amount)
}

/**
 * Get the product image URL with proper fallback
 */
function getProductImage(item) {
  // Try multiple possible image sources
  const imageSrc =
    item.thumbnail ||
    item.image ||
    item.primary_image ||
    item.product?.primary_image ||
    item.product?.thumbnail ||
    item.product?.image ||
    item.product?.images?.[0] ||
    null

  // If we have an image source, check if it needs a base URL
  if (imageSrc) {
    // If it's already an absolute URL, return as-is
    if (imageSrc.startsWith('http://') || imageSrc.startsWith('https://')) {
      return imageSrc
    }
    // If it's a relative path starting with /, return as-is (public folder)
    if (imageSrc.startsWith('/')) {
      return imageSrc
    }
    // Otherwise, it's a storage path - prepend the backend URL
    return `http://localhost:8000/storage/${imageSrc}`
  }

  return PLACEHOLDER_IMAGE
}

/**
 * Handle image loading error - fallback to placeholder
 */
function handleImageError(event) {
  event.target.src = PLACEHOLDER_IMAGE
}
</script>

<template>
  <div class="sticky top-8 rounded-lg bg-white p-6 shadow-sm">
    <div class="mb-6 flex items-center">
      <ShoppingBagIcon class="mr-3 h-6 w-6 text-gray-400" />
      <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
    </div>

    <!-- Cart Items -->
    <div class="max-h-72 space-y-4 overflow-y-auto">
      <div v-for="item in items" :key="item.id" class="flex items-center space-x-4">
        <!-- Thumbnail -->
        <div class="relative h-16 w-16 flex-shrink-0">
          <img :src="getProductImage(item)" :alt="item.name || item.product?.name || 'Product'"
            class="h-full w-full rounded-lg object-cover" @error="handleImageError" />
          <!-- Quantity badge -->
          <span
            class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-gray-500 text-xs font-medium text-white">
            {{ item.quantity || 0 }}
          </span>
        </div>

        <!-- Item details -->
        <div class="flex-1 min-w-0">
          <p class="truncate font-medium text-gray-900">{{ item.name || item.product?.name || 'Product' }}</p>
          <p class="text-sm text-gray-500">
            {{ item.quantity || 0 }} x {{ formatPrice(item.price || item.unit_price || 0) }}
          </p>
        </div>

        <!-- Item total -->
        <p class="font-medium text-gray-900">
          {{ formatPrice((item.price || item.unit_price || 0) * (item.quantity || 0)) }}
        </p>
      </div>
    </div>

    <!-- Divider -->
    <div class="my-6 border-t border-gray-200"></div>

    <!-- Totals -->
    <div class="space-y-3">
      <!-- Subtotal -->
      <div class="flex justify-between text-gray-600">
        <span>Subtotal</span>
        <span>{{ checkoutStore.subtotalFormatted }}</span>
      </div>

      <!-- Delivery -->
      <div class="flex justify-between text-gray-600">
        <span>Delivery</span>
        <span v-if="checkoutStore.deliversToArea !== false"
          :class="{ 'text-emerald-600 font-medium': checkoutStore.deliveryFee === 0 }">
          {{ checkoutStore.deliveryFeeFormatted }}
        </span>
        <span v-else class="text-gray-400">-</span>
      </div>

      <!-- Promo discount -->
      <div v-if="checkoutStore.promoValid" class="flex justify-between text-emerald-600">
        <span>Discount ({{ checkoutStore.promoCode }})</span>
        <span>{{ checkoutStore.promoDiscountFormatted }}</span>
      </div>

      <!-- Divider -->
      <div class="border-t border-gray-200"></div>

      <!-- Total -->
      <div class="flex justify-between text-lg font-bold text-gray-900">
        <span>Total</span>
        <span>{{ checkoutStore.totalFormatted }} {{ currencyStore.currentCurrency }}</span>
      </div>
    </div>

    <!-- Free delivery message -->
    <div
      v-if="checkoutStore.deliversToArea && checkoutStore.deliveryFee > 0 && checkoutStore.deliveryZone?.free_delivery_threshold"
      class="mt-4 rounded-lg bg-emerald-50 p-3 text-center text-sm text-emerald-700">
      Add {{ formatPrice(checkoutStore.deliveryZone.free_delivery_threshold - checkoutStore.subtotal) }} more for FREE
      delivery!
    </div>

    <!-- Secure checkout badge -->
    <div class="mt-6 flex items-center justify-center text-xs text-gray-500">
      <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd"
          d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
          clip-rule="evenodd" />
      </svg>
      Secure Checkout
    </div>
  </div>
</template>
