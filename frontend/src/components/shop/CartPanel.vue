<script setup>
/**
 * CartPanel.vue
 * Slide-out cart panel showing cart items and checkout button.
 *
 * @requirement CART-001 Display cart icon with badge
 * @requirement CART-003 Implement quantity adjustment
 * @requirement CART-004 Implement item removal
 * @requirement CART-006 Show cart summary
 * @requirement CART-007 Display minimum order notification
 */
import { computed, watch } from 'vue'
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

const isOpen = computed(() => cartStore.isOpen)
const items = computed(() => cartStore.items)
const isEmpty = computed(() => cartStore.isEmpty)
const itemCount = computed(() => cartStore.itemCount)
const subtotal = computed(() => cartStore.subtotal)
const subtotalFormatted = computed(() => cartStore.subtotalFormatted)
const meetsMinimumOrder = computed(() => cartStore.meetsMinimumOrder)
const amountToMinimumFormatted = computed(() => cartStore.amountToMinimumFormatted)
const isLoading = computed(() => cartStore.isLoading)

// Prevent body scroll when panel is open
watch(isOpen, (open) => {
  if (open) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

function closePanel() {
  cartStore.closeCart()
}

function updateQuantity(itemId, quantity) {
  cartStore.updateQuantity(itemId, quantity)
}

function removeItem(itemId) {
  cartStore.removeItem(itemId)
}

function handleCheckout() {
  closePanel()
  if (authStore.isAuthenticated) {
    router.push('/checkout')
  } else {
    router.push('/login?redirect=/checkout')
  }
}

function continueShopping() {
  closePanel()
  router.push('/shop')
}

function getProductImage(item) {
  return item.product?.primary_image?.url ||
    item.product?.images?.[0]?.url ||
    '/images/placeholder-product.jpg'
}
</script>

<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <Transition enter-active-class="transition-opacity duration-300"
      leave-active-class="transition-opacity duration-300" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="isOpen" class="fixed inset-0 bg-black/50 z-40" @click="closePanel"></div>
    </Transition>

    <!-- Panel -->
    <Transition enter-active-class="transition-transform duration-300 ease-out"
      leave-active-class="transition-transform duration-300 ease-in" enter-from-class="translate-x-full"
      leave-to-class="translate-x-full">
      <div v-if="isOpen" class="fixed inset-y-0 right-0 w-full max-w-md bg-white shadow-2xl z-50 flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b">
          <div class="flex items-center gap-2">
            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <h2 class="text-lg font-semibold text-gray-900">Your Cart</h2>
            <span v-if="!isEmpty" class="text-sm text-gray-500">
              ({{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }})
            </span>
          </div>
          <button @click="closePanel"
            class="p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Minimum Order Warning -->
        <div v-if="!isEmpty && !meetsMinimumOrder" class="bg-primary-50 border-b border-primary-100 p-3">
          <div class="flex items-center gap-2 text-primary-800">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-sm">
              Add <strong>{{ amountToMinimumFormatted }}</strong> more to reach the ${{ cartStore.MINIMUM_ORDER }}
              minimum order.
            </p>
          </div>
        </div>

        <!-- Loading Overlay -->
        <div v-if="isLoading" class="flex-1 flex items-center justify-center">
          <div class="text-center">
            <svg class="w-8 h-8 text-primary-600 animate-spin mx-auto mb-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            <p class="text-gray-500">Loading cart...</p>
          </div>
        </div>

        <!-- Empty Cart -->
        <div v-else-if="isEmpty" class="flex-1 flex flex-col items-center justify-center p-8">
          <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h3>
          <p class="text-gray-600 text-center mb-6">
            Looks like you haven't added any products yet.
          </p>
          <button @click="continueShopping"
            class="bg-primary-700 text-white py-3 px-6 rounded-lg font-medium hover:bg-primary-800 transition-colors">
            Start Shopping
          </button>
        </div>

        <!-- Cart Items -->
        <div v-else class="flex-1 overflow-y-auto p-4">
          <ul class="space-y-4">
            <li v-for="item in items" :key="item.id" class="flex gap-4 bg-gray-50 rounded-lg p-3">
              <!-- Product Image -->
              <RouterLink :to="`/products/${item.product?.slug}`" @click="closePanel" class="flex-shrink-0">
                <div class="w-20 h-20 bg-gray-200 rounded-lg overflow-hidden">
                  <img :src="getProductImage(item)" :alt="item.product?.name" class="w-full h-full object-cover" />
                </div>
              </RouterLink>

              <!-- Product Info -->
              <div class="flex-1 min-w-0">
                <RouterLink :to="`/products/${item.product?.slug}`" @click="closePanel"
                  class="font-medium text-gray-900 hover:text-primary-600 block truncate">
                  {{ item.product?.name }}
                </RouterLink>

                <p class="text-sm text-primary-600 font-semibold">
                  {{ formatPrice(item.unit_price) }} / {{ item.product?.unit || 'kg' }}
                </p>

                <!-- Quantity Controls -->
                <div class="flex items-center gap-2 mt-2">
                  <div class="flex items-center border border-gray-300 rounded bg-white">
                    <button @click="updateQuantity(item.id, item.quantity - 0.5)"
                      class="px-2 py-1 text-gray-600 hover:text-gray-900 disabled:opacity-50"
                      :disabled="item.quantity <= 0.5">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                      </svg>
                    </button>
                    <span class="px-2 text-sm font-medium min-w-[40px] text-center">
                      {{ item.quantity }}{{ item.product?.unit || 'kg' }}
                    </span>
                    <button @click="updateQuantity(item.id, item.quantity + 0.5)"
                      class="px-2 py-1 text-gray-600 hover:text-gray-900 disabled:opacity-50"
                      :disabled="item.product && item.quantity >= item.product.stock_quantity">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                    </button>
                  </div>

                  <!-- Remove Button -->
                  <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700 p-1" title="Remove item">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>

                <!-- Stock Warning -->
                <p v-if="item.product && item.quantity > item.product.stock_quantity" class="text-xs text-red-500 mt-1">
                  Only {{ item.product.stock_quantity }}{{ item.product.unit || 'kg' }} available
                </p>
              </div>

              <!-- Line Total -->
              <div class="text-right">
                <p class="font-semibold text-gray-900">
                  {{ formatPrice(item.quantity * item.unit_price) }}
                </p>
              </div>
            </li>
          </ul>
        </div>

        <!-- Footer -->
        <div v-if="!isEmpty && !isLoading" class="border-t p-4 space-y-4">
          <!-- Subtotal -->
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Subtotal</span>
            <span class="text-xl font-bold text-gray-900">{{ subtotalFormatted }}</span>
          </div>

          <p class="text-xs text-gray-500 text-center">
            Delivery calculated at checkout
          </p>

          <!-- Checkout Button -->
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

          <!-- View Full Cart Button -->
          <RouterLink to="/cart" @click="closePanel"
            class="w-full py-2 px-6 rounded-lg font-medium flex items-center justify-center gap-2 border-2 border-primary-700 text-primary-700 hover:bg-primary-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
            <span>View Full Cart</span>
          </RouterLink>

          <!-- Continue Shopping -->
          <button @click="continueShopping"
            class="w-full py-2 text-primary-600 hover:text-primary-700 font-medium text-center">
            Continue Shopping
          </button>

          <!-- Clear Cart -->
          <button @click="cartStore.clearCart()"
            class="w-full py-2 text-gray-500 hover:text-red-500 text-sm text-center transition-colors">
            Clear Cart
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
