<script setup>
/**
 * WishlistPage.vue
 * Customer wishlist page with full functionality
 */
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useWishlistStore } from '@/stores/wishlist'
import { useCartStore } from '@/stores/cart'
import { useCurrencyStore } from '@/stores/currency'
import { toast } from 'vue-sonner'
import { Heart, ShoppingCart, X, Loader2 } from 'lucide-vue-next'

const wishlistStore = useWishlistStore()
const cartStore = useCartStore()
const currencyStore = useCurrencyStore()

const isLoading = ref(false)

const wishlistItems = computed(() => wishlistStore.items)
const hasItems = computed(() => wishlistStore.hasItems)

onMounted(async () => {
  isLoading.value = true
  await wishlistStore.fetchWishlist()
  isLoading.value = false
})

async function removeItem(productId) {
  const result = await wishlistStore.removeFromWishlist(productId)
  if (result.success) {
    toast.success('Removed from wishlist')
  } else {
    toast.error(result.message)
  }
}

async function addToCart(item) {
  await cartStore.addItem({
    product_id: item.product_id,
    quantity: 1,
    price: item.product?.price || item.price
  })
  toast.success('Added to cart')
}

async function moveAllToCart() {
  for (const item of wishlistItems.value) {
    await cartStore.addItem({
      product_id: item.product_id,
      quantity: 1,
      price: item.product?.price || item.price
    })
  }
  toast.success(`${wishlistItems.value.length} items added to cart`)
}

function formatPrice(price) {
  return currencyStore.formatPrice(price)
}

function getImageUrl(product) {
  if (product?.main_image) {
    return product.main_image
  }
  return '/images/placeholder-product.jpg'
}
</script>

<template>
  <div class="bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="mb-6">
        <nav class="text-sm mb-3">
          <RouterLink to="/customer" class="text-gray-500 hover:text-[#CF0D0F]">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">Wishlist</span>
        </nav>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
              <Heart class="w-6 h-6 text-[#CF0D0F]" />
              My Wishlist
              <span v-if="hasItems" class="text-lg font-normal text-gray-500">({{ wishlistItems.length }})</span>
            </h1>
            <p class="text-sm text-gray-600">Products you've saved for later</p>
          </div>
          <button 
            v-if="hasItems"
            @click="moveAllToCart"
            class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors text-sm"
          >
            <ShoppingCart class="w-4 h-4 mr-2" />
            Add All to Cart
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-20">
        <Loader2 class="w-8 h-8 text-[#CF0D0F] animate-spin" />
        <span class="ml-3 text-gray-600">Loading wishlist...</span>
      </div>

      <!-- Empty State -->
      <div v-else-if="!hasItems" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <Heart class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Your Wishlist is Empty</h2>
        <p class="text-gray-600 mb-6">
          Start adding products you love by clicking the heart icon on product pages.
        </p>
        <RouterLink 
          to="/shop" 
          class="inline-flex items-center px-6 py-3 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors"
        >
          <ShoppingCart class="w-5 h-5 mr-2" />
          Browse Products
        </RouterLink>
      </div>

      <!-- Wishlist Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="item in wishlistItems" 
          :key="item.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group"
        >
          <!-- Product Image -->
          <div class="relative">
            <RouterLink :to="`/shop/${item.product?.slug || item.product_id}`" class="block">
              <div class="aspect-square bg-gray-100 overflow-hidden">
                <img 
                  :src="getImageUrl(item.product)" 
                  :alt="item.product?.name || 'Product'"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  @error="$event.target.src = '/images/placeholder-product.jpg'"
                />
              </div>
            </RouterLink>
            
            <!-- Remove Button -->
            <button
              @click="removeItem(item.product_id)"
              class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-red-50 transition-colors"
              title="Remove from wishlist"
            >
              <X class="w-4 h-4 text-red-500" />
            </button>

            <!-- In Stock Badge -->
            <div v-if="item.product?.in_stock" class="absolute top-2 left-2">
              <span class="px-2 py-1 bg-green-500 text-white text-xs font-medium rounded">In Stock</span>
            </div>
            <div v-else class="absolute top-2 left-2">
              <span class="px-2 py-1 bg-gray-500 text-white text-xs font-medium rounded">Out of Stock</span>
            </div>
          </div>

          <!-- Product Info -->
          <div class="p-4">
            <RouterLink 
              :to="`/shop/${item.product?.slug || item.product_id}`"
              class="block mb-2 hover:text-[#CF0D0F] transition-colors"
            >
              <h3 class="font-semibold text-gray-900 line-clamp-2 text-sm">
                {{ item.product?.name || 'Product' }}
              </h3>
            </RouterLink>

            <!-- Category -->
            <p v-if="item.product?.category" class="text-xs text-gray-500 mb-2">
              {{ item.product.category.name }}
            </p>

            <!-- Price -->
            <div class="mb-3">
              <span class="text-[#CF0D0F] font-bold text-lg">
                {{ formatPrice(item.product?.price || 0) }}
              </span>
              <span class="text-gray-500 text-sm ml-1">
                / {{ item.product?.unit || 'kg' }}
              </span>
            </div>

            <!-- Add to Cart Button -->
            <button
              @click="addToCart(item)"
              :disabled="!item.product?.in_stock"
              class="w-full py-2 bg-[#CF0D0F] text-white text-sm font-medium rounded-lg hover:bg-[#F6211F] transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <ShoppingCart class="w-4 h-4" />
              {{ item.product?.in_stock ? 'Add to Cart' : 'Out of Stock' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
