<script setup>
/**
 * ProductCard.vue
 * Displays product information in a card format for the shop grid.
 *
 * @requirement SHOP-002 Display product grid with cards
 * @requirement SHOP-003 Show product image, name, price
 * @requirement SHOP-005 Implement quick-add to cart
 */
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useCurrencyStore } from '@/stores/currency'
import { useWishlistStore } from '@/stores/wishlist'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  showQuickView: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['quick-view'])

const cartStore = useCartStore()
const currencyStore = useCurrencyStore()
const wishlistStore = useWishlistStore()
const toast = useToast()

const quantity = ref(1)
const isAdding = ref(false)
const showSuccess = ref(false)
const isTogglingWishlist = ref(false)

const isInCart = computed(() => cartStore.isInCart(props.product.id))
const cartQuantity = computed(() => cartStore.getQuantity(props.product.id))
const isInWishlist = computed(() => wishlistStore.isInWishlist(props.product.id))

// Watch quantity to ensure it's always valid
function validateQuantity() {
  // Convert to number if string
  const numValue = Number(quantity.value)
  
  // Ensure quantity is a valid number and at least 0.5
  if (isNaN(numValue) || numValue === null || numValue === undefined || numValue === '' || numValue < 0.5) {
    quantity.value = 0.5
    return
  }
  
  // Round to nearest 0.5
  quantity.value = Math.round(numValue * 2) / 2
  
  // Don't exceed stock
  if (quantity.value > props.product.stock_quantity) {
    quantity.value = props.product.stock_quantity
  }
}

const primaryImage = computed(() => {
  return props.product.primary_image?.url ||
    props.product.images?.[0]?.url ||
    'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=400&h=400&fit=crop'
})

const hasDiscount = computed(() => {
  return props.product.compare_at_price && props.product.compare_at_price > props.product.price
})

const discountPercentage = computed(() => {
  if (!hasDiscount.value) return 0
  return Math.round((1 - props.product.price / props.product.compare_at_price) * 100)
})

const isOutOfStock = computed(() => {
  return props.product.stock_quantity <= 0
})

// Format price using currency store for dynamic currency switching
const formattedPrice = computed(() => currencyStore.format(props.product.price))
const formattedCompareAtPrice = computed(() =>
  hasDiscount.value ? currencyStore.format(props.product.compare_at_price) : ''
)

async function handleAddToCart() {
  if (isOutOfStock.value || isAdding.value) return

  // Validate quantity before adding
  validateQuantity()

  // Double check we have a valid quantity
  if (isNaN(quantity.value) || quantity.value < 0.5) {
    quantity.value = 0.5
  }

  isAdding.value = true

  try {
    const success = await cartStore.addItem(props.product, quantity.value)
    if (success) {
      showSuccess.value = true
      setTimeout(() => {
        showSuccess.value = false
      }, 2000)
    }
  } finally {
    isAdding.value = false
    quantity.value = 1
  }
}

function openQuickView() {
  emit('quick-view', props.product)
}

async function toggleWishlist() {
  if (isTogglingWishlist.value) return
  
  isTogglingWishlist.value = true
  try {
    const result = await wishlistStore.toggleWishlist(props.product.id)
    if (result.success) {
      toast.success(result.message)
    } else {
      toast.error(result.message)
    }
  } catch (error) {
    toast.error('Failed to update wishlist')
  } finally {
    isTogglingWishlist.value = false
  }
}
</script>

<template>
  <div
    class="group bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300">
    <!-- Product Image -->
    <RouterLink :to="`/products/${product.slug}`" class="block relative aspect-square overflow-hidden bg-gray-100">
      <img :src="primaryImage" :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />

      <!-- Badges -->
      <div class="absolute top-3 left-3 flex flex-col gap-2">
        <!-- Sale Badge -->
        <span v-if="hasDiscount" class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
          -{{ discountPercentage }}%
        </span>

        <!-- Featured Badge -->
        <span v-if="product.is_featured" class="bg-primary-500 text-white text-xs font-bold px-2 py-1 rounded">
          Featured
        </span>

        <!-- Out of Stock Badge -->
        <span v-if="isOutOfStock" class="bg-gray-800 text-white text-xs font-bold px-2 py-1 rounded">
          Out of Stock
        </span>
      </div>

      <!-- Wishlist Button -->
      <button 
        @click.prevent="toggleWishlist"
        :disabled="isTogglingWishlist"
        class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm p-2 rounded-full shadow-md transition-all duration-200 hover:bg-white hover:scale-110"
        :class="{ 'text-red-500': isInWishlist, 'text-gray-400': !isInWishlist }"
        title="Add to Wishlist">
        <svg class="w-5 h-5" :fill="isInWishlist ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
      </button>

      <!-- Quick View Button -->
      <button v-if="showQuickView && !isOutOfStock" @click.prevent="openQuickView"
        class="absolute bottom-3 right-3 bg-white/90 backdrop-blur-sm text-gray-800 p-2 rounded-full shadow-md opacity-0 group-hover:opacity-100 transition-opacity duration-200 hover:bg-white"
        title="Quick View">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
      </button>
    </RouterLink>

    <!-- Product Info -->
    <div class="p-4">
      <!-- Category -->
      <p class="text-xs text-primary-600 font-medium mb-1">
        {{ product.category?.name || 'Uncategorized' }}
      </p>

      <!-- Product Name -->
      <RouterLink :to="`/products/${product.slug}`">
        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 hover:text-primary-600 transition-colors">
          {{ product.name }}
        </h3>
      </RouterLink>

      <!-- Price -->
      <div class="flex items-center gap-2 mb-3">
        <span class="text-lg font-bold text-gray-900">
          {{ formattedPrice }}
        </span>
        <span class="text-sm text-gray-500">/ {{ product.unit || 'kg' }}</span>
        <span v-if="hasDiscount" class="text-sm text-gray-400 line-through">
          {{ formattedCompareAtPrice }}
        </span>
      </div>

      <!-- Stock Status -->
      <p v-if="!isOutOfStock" class="text-xs text-green-600 mb-3">
        {{ product.stock_quantity }}{{ product.unit || 'kg' }} in stock
      </p>
      <p v-else class="text-xs text-red-500 mb-3">
        Currently unavailable
      </p>

      <!-- Add to Cart -->
      <div class="flex items-center gap-2">
        <!-- Quantity Input -->
        <div v-if="!isOutOfStock" class="flex items-center border border-gray-300 rounded-lg">
          <button @click="quantity = Math.max(0.5, quantity - 0.5)" class="px-2 py-1 text-gray-600 hover:text-gray-900"
            :disabled="quantity <= 0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
            </svg>
          </button>
          <input v-model.number="quantity" type="number" min="0.5" step="0.5" :max="product.stock_quantity"
            class="w-12 text-center text-sm border-0 focus:ring-0" @blur="validateQuantity" @input="validateQuantity" />
          <button @click="quantity = Math.min(product.stock_quantity, quantity + 0.5)"
            class="px-2 py-1 text-gray-600 hover:text-gray-900" :disabled="quantity >= product.stock_quantity">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </button>
        </div>

        <!-- Add to Cart Button -->
        <button @click="handleAddToCart" :disabled="isOutOfStock || isAdding"
          class="flex-1 py-2 px-4 rounded-lg font-medium text-sm transition-colors duration-200 flex items-center justify-center gap-2"
          :class="[
            isOutOfStock
              ? 'bg-gray-200 text-gray-500 cursor-not-allowed'
              : showSuccess
                ? 'bg-green-600 text-white'
                : 'bg-primary-700 text-white hover:bg-primary-800'
          ]">
          <svg v-if="isAdding" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
          <svg v-else-if="showSuccess" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span>
            {{ isOutOfStock ? 'Out of Stock' : showSuccess ? 'Added!' : 'Add' }}
          </span>
        </button>
      </div>

      <!-- In Cart Indicator -->
      <p v-if="isInCart && !showSuccess" class="text-xs text-primary-600 mt-2 text-center">
        {{ cartQuantity }}{{ product.unit || 'kg' }} in cart
      </p>
    </div>
  </div>
</template>
