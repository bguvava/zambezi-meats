<script setup>
/**
 * CartPage.vue
 * Full cart page showing all cart items and checkout options.
 *
 * @requirement CART-001 Display cart
 * @requirement CART-003 Implement quantity adjustment
 * @requirement CART-006 Show cart summary
 * @requirement CART-007 Display minimum order notification
 */
import { computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useCurrencyStore } from '@/stores/currency'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()
const currencyStore = useCurrencyStore()

// Helper to format prices
function formatPrice(amount) {
  return currencyStore.format(amount)
}

// Initialize cart on mount
onMounted(() => {
  cartStore.initialize()
})

// Computed
const items = computed(() => cartStore.items)
const isEmpty = computed(() => cartStore.isEmpty)
const itemCount = computed(() => cartStore.itemCount)
const subtotal = computed(() => cartStore.subtotal)
const subtotalFormatted = computed(() => cartStore.subtotalFormatted)
const meetsMinimumOrder = computed(() => cartStore.meetsMinimumOrder)
const amountToMinimumFormatted = computed(() => cartStore.amountToMinimumFormatted)
const isLoading = computed(() => cartStore.isLoading)

function updateQuantity(itemId, quantity) {
  cartStore.updateQuantity(itemId, quantity)
}

function removeItem(itemId) {
  cartStore.removeItem(itemId)
}

function handleCheckout() {
  if (authStore.isAuthenticated) {
    router.push('/checkout')
  } else {
    router.push('/login?redirect=/checkout')
  }
}

function getProductImage(item) {
  return item.product?.primary_image?.url ||
    item.product?.images?.[0]?.url ||
    '/images/placeholder-product.jpg'
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
        <p v-if="!isEmpty" class="text-gray-600 mt-1">
          {{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }} in your cart
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-20">
        <div class="text-center">
          <svg class="w-12 h-12 text-primary-600 animate-spin mx-auto mb-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
          <p class="text-gray-600">Loading your cart...</p>
        </div>
      </div>

      <!-- Empty Cart -->
      <div v-else-if="isEmpty" class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h2 class="text-2xl font-semibold text-gray-900 mb-2">Your cart is empty</h2>
          <p class="text-gray-600 mb-8">
            Looks like you haven't added any products yet. Browse our selection of premium quality meats.
          </p>
          <RouterLink to="/shop"
            class="inline-flex items-center px-8 py-3 bg-primary-700 text-white font-medium rounded-lg hover:bg-primary-800 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            Start Shopping
          </RouterLink>
        </div>
      </div>

      <!-- Cart Content -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2 space-y-4">
          <!-- Minimum Order Warning -->
          <div v-if="!meetsMinimumOrder" class="bg-primary-50 border border-primary-200 rounded-lg p-4">
            <div class="flex items-center gap-3 text-primary-800">
              <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <div>
                <p class="font-medium">Minimum order not met</p>
                <p class="text-sm">
                  Add <strong>{{ amountToMinimumFormatted }}</strong> more to reach the {{
                    formatPrice(cartStore.MINIMUM_ORDER) }} minimum order.
                </p>
              </div>
            </div>
          </div>

          <!-- Cart Items List -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 border-b bg-gray-50">
              <div class="grid grid-cols-12 gap-4 text-sm font-medium text-gray-600">
                <div class="col-span-6">Product</div>
                <div class="col-span-2 text-center">Price</div>
                <div class="col-span-2 text-center">Quantity</div>
                <div class="col-span-2 text-right">Total</div>
              </div>
            </div>

            <ul class="divide-y divide-gray-200">
              <li v-for="item in items" :key="item.id" class="p-4">
                <div class="grid grid-cols-12 gap-4 items-center">
                  <!-- Product -->
                  <div class="col-span-6 flex items-center gap-4">
                    <RouterLink :to="`/products/${item.product?.slug}`" class="flex-shrink-0">
                      <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden">
                        <img :src="getProductImage(item)" :alt="item.product?.name"
                          class="w-full h-full object-cover" />
                      </div>
                    </RouterLink>
                    <div class="min-w-0">
                      <RouterLink :to="`/products/${item.product?.slug}`"
                        class="font-medium text-gray-900 hover:text-primary-600 block truncate">
                        {{ item.product?.name }}
                      </RouterLink>
                      <p class="text-sm text-gray-500 mt-1">
                        {{ item.product?.category?.name || 'Uncategorized' }}
                      </p>

                      <!-- Mobile Remove -->
                      <button @click="removeItem(item.id)"
                        class="lg:hidden text-sm text-red-500 hover:text-red-700 mt-2">
                        Remove
                      </button>
                    </div>
                  </div>

                  <!-- Price -->
                  <div class="col-span-2 text-center">
                    <p class="font-medium text-gray-900">
                      {{ formatPrice(item.unit_price) }}
                    </p>
                    <p class="text-xs text-gray-500">/ {{ item.product?.unit || 'kg' }}</p>
                  </div>

                  <!-- Quantity -->
                  <div class="col-span-2 flex justify-center">
                    <div class="flex items-center border border-gray-300 rounded-lg">
                      <button @click="updateQuantity(item.id, item.quantity - 0.5)"
                        class="px-3 py-2 text-gray-600 hover:text-gray-900 disabled:opacity-50"
                        :disabled="item.quantity <= 0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                      </button>
                      <span class="px-3 py-2 font-medium min-w-[60px] text-center">
                        {{ item.quantity }}{{ item.product?.unit || 'kg' }}
                      </span>
                      <button @click="updateQuantity(item.id, item.quantity + 0.5)"
                        class="px-3 py-2 text-gray-600 hover:text-gray-900 disabled:opacity-50"
                        :disabled="item.product && item.quantity >= item.product.stock_quantity">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                      </button>
                    </div>
                  </div>

                  <!-- Total -->
                  <div class="col-span-2 text-right">
                    <p class="font-semibold text-gray-900">
                      {{ formatPrice(item.quantity * item.unit_price) }}
                    </p>

                    <!-- Desktop Remove -->
                    <button @click="removeItem(item.id)"
                      class="hidden lg:inline-flex items-center gap-1 text-sm text-red-500 hover:text-red-700 mt-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Remove
                    </button>
                  </div>
                </div>

                <!-- Stock Warning -->
                <p v-if="item.product && item.quantity > item.product.stock_quantity"
                  class="text-sm text-red-500 mt-2 ml-24">
                  Only {{ item.product.stock_quantity }}{{ item.product.unit || 'kg' }} available
                </p>
              </li>
            </ul>

            <!-- Cart Actions -->
            <div class="p-4 border-t bg-gray-50 flex flex-wrap gap-4 justify-between items-center">
              <RouterLink to="/shop"
                class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Continue Shopping
              </RouterLink>

              <button @click="cartStore.clearCart()"
                class="text-gray-500 hover:text-red-500 font-medium transition-colors">
                Clear Cart
              </button>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Order Summary</h2>

            <div class="space-y-4 mb-6">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal ({{ itemCount }} items)</span>
                <span class="font-medium text-gray-900">{{ subtotalFormatted }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>Delivery</span>
                <span class="text-gray-500">Calculated at checkout</span>
              </div>
            </div>

            <div class="border-t pt-4 mb-6">
              <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-900">Total</span>
                <span class="text-2xl font-bold text-gray-900">{{ subtotalFormatted }}</span>
              </div>
              <p class="text-xs text-gray-500 mt-1">Taxes included where applicable</p>
            </div>

            <button @click="handleCheckout" :disabled="!meetsMinimumOrder"
              class="w-full py-3 px-6 rounded-lg font-medium flex items-center justify-center gap-2 transition-colors"
              :class="[
                meetsMinimumOrder
                  ? 'bg-primary-700 text-white hover:bg-primary-800'
                  : 'bg-gray-200 text-gray-500 cursor-not-allowed'
              ]">
              <span>Proceed to Checkout</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </button>

            <p v-if="!meetsMinimumOrder" class="text-sm text-center text-gray-500 mt-4">
              Minimum order: {{ formatPrice(cartStore.MINIMUM_ORDER) }}
            </p>

            <!-- Security Badge -->
            <div class="mt-6 pt-6 border-t">
              <div class="flex items-center justify-center gap-2 text-gray-500 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Secure Checkout</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
