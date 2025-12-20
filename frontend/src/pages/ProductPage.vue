<script setup>
/**
 * ProductPage.vue
 * Individual product detail page with gallery and related products.
 *
 * @requirement SHOP-007 Create product detail page
 * @requirement SHOP-008 Display nutrition info
 * @requirement SHOP-009 Show related products
 */
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useProductsStore } from '@/stores/products'
import { useCartStore } from '@/stores/cart'
import { useCurrencyStore } from '@/stores/currency'
import ProductCard from '@/components/shop/ProductCard.vue'

const route = useRoute()
const productsStore = useProductsStore()
const cartStore = useCartStore()
const currencyStore = useCurrencyStore()

// State
const quantity = ref(1)
const currentImageIndex = ref(0)
const isAdding = ref(false)
const showSuccess = ref(false)
const activeTab = ref('description')

// Computed
const product = computed(() => productsStore.currentProduct)
const relatedProducts = computed(() => productsStore.relatedProducts)
const isLoading = computed(() => productsStore.isLoading)
const error = computed(() => productsStore.error)

const isInCart = computed(() => product.value && cartStore.isInCart(product.value.id))
const cartQuantity = computed(() => product.value ? cartStore.getQuantity(product.value.id) : 0)

const images = computed(() => {
  if (!product.value) return []
  return product.value.images?.length > 0
    ? product.value.images
    : [{ url: '/images/placeholder-product.jpg', alt_text: product.value?.name }]
})

const currentImage = computed(() => images.value[currentImageIndex.value])

const hasDiscount = computed(() => {
  return product.value?.compare_at_price && product.value.compare_at_price > product.value.price
})

const discountPercentage = computed(() => {
  if (!hasDiscount.value) return 0
  return Math.round((1 - product.value.price / product.value.compare_at_price) * 100)
})

const isOutOfStock = computed(() => {
  return product.value && product.value.stock_quantity <= 0
})

// Format prices using currency store for dynamic currency switching
const formattedPrice = computed(() => product.value ? currencyStore.format(product.value.price) : '')
const formattedCompareAtPrice = computed(() =>
  hasDiscount.value && product.value ? currencyStore.format(product.value.compare_at_price) : ''
)

// Fetch product on mount and when slug changes
onMounted(() => loadProduct())
watch(() => route.params.slug, () => loadProduct())

async function loadProduct() {
  const slug = route.params.slug
  if (!slug) return

  currentImageIndex.value = 0
  quantity.value = 1
  showSuccess.value = false

  try {
    await productsStore.fetchProduct(slug)
    if (product.value) {
      await productsStore.fetchRelatedProducts(slug)
    }
  } catch (err) {
    console.error('Failed to load product:', err)
  }
}

function selectImage(index) {
  currentImageIndex.value = index
}

function nextImage() {
  if (currentImageIndex.value < images.value.length - 1) {
    currentImageIndex.value++
  } else {
    currentImageIndex.value = 0
  }
}

function prevImage() {
  if (currentImageIndex.value > 0) {
    currentImageIndex.value--
  } else {
    currentImageIndex.value = images.value.length - 1
  }
}

async function handleAddToCart() {
  if (!product.value || isOutOfStock.value || isAdding.value) return

  isAdding.value = true

  try {
    const success = await cartStore.addItem(product.value, quantity.value)
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
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="isLoading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="animate-pulse">
        <div class="h-6 bg-gray-200 rounded w-64 mb-8"></div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <div class="aspect-square bg-gray-200 rounded-lg"></div>
          <div class="space-y-4">
            <div class="h-4 bg-gray-200 rounded w-24"></div>
            <div class="h-8 bg-gray-200 rounded w-3/4"></div>
            <div class="h-6 bg-gray-200 rounded w-32"></div>
            <div class="h-24 bg-gray-200 rounded"></div>
            <div class="h-12 bg-gray-200 rounded"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Product Not Found</h2>
        <p class="text-gray-600 mb-6">{{ error }}</p>
        <RouterLink to="/shop"
          class="inline-flex items-center px-6 py-3 bg-primary-700 text-white font-medium rounded-lg hover:bg-primary-800 transition-colors">
          Back to Shop
        </RouterLink>
      </div>
    </div>

    <!-- Product Content -->
    <div v-else-if="product" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Breadcrumb -->
      <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <RouterLink to="/" class="text-gray-500 hover:text-primary-600">Home</RouterLink>
          </li>
          <li class="text-gray-400">/</li>
          <li>
            <RouterLink to="/shop" class="text-gray-500 hover:text-primary-600">Shop</RouterLink>
          </li>
          <li v-if="product.category" class="text-gray-400">/</li>
          <li v-if="product.category">
            <RouterLink :to="`/shop?category=${product.category.slug}`" class="text-gray-500 hover:text-primary-600">
              {{ product.category.name }}
            </RouterLink>
          </li>
          <li class="text-gray-400">/</li>
          <li class="text-gray-900 font-medium">{{ product.name }}</li>
        </ol>
      </nav>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2">
          <!-- Image Gallery -->
          <div class="relative bg-gray-100">
            <!-- Main Image -->
            <div class="aspect-square relative">
              <img :src="currentImage?.url" :alt="currentImage?.alt_text || product.name"
                class="w-full h-full object-cover" />

              <!-- Navigation Arrows -->
              <div v-if="images.length > 1"
                class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-4">
                <button @click="prevImage"
                  class="p-2 bg-white/90 rounded-full shadow-md text-gray-700 hover:bg-white transition-colors">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                </button>
                <button @click="nextImage"
                  class="p-2 bg-white/90 rounded-full shadow-md text-gray-700 hover:bg-white transition-colors">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </div>

              <!-- Badges -->
              <div class="absolute top-4 left-4 flex flex-col gap-2">
                <span v-if="hasDiscount" class="bg-red-500 text-white text-sm font-bold px-3 py-1 rounded">
                  -{{ discountPercentage }}% OFF
                </span>
                <span v-if="product.is_featured" class="bg-primary-500 text-white text-sm font-bold px-3 py-1 rounded">
                  Featured
                </span>
                <span v-if="isOutOfStock" class="bg-gray-800 text-white text-sm font-bold px-3 py-1 rounded">
                  Out of Stock
                </span>
              </div>
            </div>

            <!-- Thumbnails -->
            <div v-if="images.length > 1" class="p-4 flex gap-2 overflow-x-auto">
              <button v-for="(img, index) in images" :key="index" @click="selectImage(index)"
                class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden border-2 transition-colors" :class="[
                  currentImageIndex === index
                    ? 'border-primary-500'
                    : 'border-gray-200 hover:border-gray-300'
                ]">
                <img :src="img.url" :alt="`Thumbnail ${index + 1}`" class="w-full h-full object-cover" />
              </button>
            </div>
          </div>

          <!-- Product Details -->
          <div class="p-6 lg:p-8">
            <!-- Category -->
            <RouterLink v-if="product.category" :to="`/shop?category=${product.category.slug}`"
              class="inline-block text-sm text-primary-600 font-medium mb-2 hover:text-primary-700">
              {{ product.category.name }}
            </RouterLink>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
              {{ product.name }}
            </h1>

            <!-- Price -->
            <div class="flex items-center gap-3 mb-6">
              <span class="text-3xl font-bold text-gray-900">
                {{ formattedPrice }}
              </span>
              <span class="text-lg text-gray-500">/ {{ product.unit || 'kg' }}</span>
              <span v-if="hasDiscount" class="text-xl text-gray-400 line-through">
                {{ formattedCompareAtPrice }}
              </span>
            </div>

            <!-- Stock Status -->
            <div class="mb-6">
              <p v-if="!isOutOfStock" class="text-green-600 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ product.stock_quantity }}{{ product.unit || 'kg' }} in stock
              </p>
              <p v-else class="text-red-500 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Currently unavailable
              </p>
            </div>

            <!-- Short Description -->
            <p v-if="product.short_description" class="text-gray-600 mb-6">
              {{ product.short_description }}
            </p>

            <!-- Add to Cart Section -->
            <div v-if="!isOutOfStock" class="mb-8">
              <div class="flex items-center gap-4 mb-4">
                <label class="font-medium text-gray-700">Quantity:</label>
                <div class="flex items-center border border-gray-300 rounded-lg">
                  <button @click="quantity = Math.max(0.5, quantity - 0.5)"
                    class="px-4 py-2 text-gray-600 hover:text-gray-900" :disabled="quantity <= 0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                  </button>
                  <input v-model.number="quantity" type="number" min="0.5" step="0.5" :max="product.stock_quantity"
                    class="w-20 text-center border-0 focus:ring-0 text-lg font-medium" />
                  <button @click="quantity = Math.min(product.stock_quantity, quantity + 0.5)"
                    class="px-4 py-2 text-gray-600 hover:text-gray-900" :disabled="quantity >= product.stock_quantity">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                </div>
                <span class="text-gray-500">{{ product.unit || 'kg' }}</span>
              </div>

              <button @click="handleAddToCart" :disabled="isAdding"
                class="w-full py-4 px-6 rounded-lg font-semibold text-lg flex items-center justify-center gap-3 transition-colors"
                :class="[
                  showSuccess
                    ? 'bg-green-600 text-white'
                    : 'bg-primary-700 text-white hover:bg-primary-800'
                ]">
                <svg v-if="isAdding" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <svg v-else-if="showSuccess" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ showSuccess ? 'Added to Cart!' : 'Add to Cart' }}
              </button>

              <!-- In Cart Indicator -->
              <p v-if="isInCart && !showSuccess" class="text-primary-600 text-center mt-3">
                {{ cartQuantity }}{{ product.unit || 'kg' }} already in cart
              </p>
            </div>

            <!-- Out of Stock Button -->
            <button v-else disabled
              class="w-full py-4 px-6 rounded-lg font-semibold text-lg bg-gray-200 text-gray-500 cursor-not-allowed mb-8">
              Out of Stock
            </button>

            <!-- Product Meta -->
            <div class="border-t pt-6 space-y-2 text-sm text-gray-600">
              <p v-if="product.min_order_quantity">
                <span class="font-medium">Min Order:</span> {{ product.min_order_quantity }}{{ product.unit || 'kg' }}
              </p>
              <p v-if="product.max_order_quantity">
                <span class="font-medium">Max Order:</span> {{ product.max_order_quantity }}{{ product.unit || 'kg' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="border-t">
          <div class="flex border-b">
            <button @click="activeTab = 'description'" class="px-6 py-4 font-medium transition-colors" :class="[
              activeTab === 'description'
                ? 'text-primary-600 border-b-2 border-primary-600'
                : 'text-gray-600 hover:text-gray-900'
            ]">
              Description
            </button>
            <button v-if="product.nutrition_info" @click="activeTab = 'nutrition'"
              class="px-6 py-4 font-medium transition-colors" :class="[
                activeTab === 'nutrition'
                  ? 'text-primary-600 border-b-2 border-primary-600'
                  : 'text-gray-600 hover:text-gray-900'
              ]">
              Nutrition Info
            </button>
          </div>

          <div class="p-6">
            <!-- Description Tab -->
            <div v-if="activeTab === 'description'">
              <div v-if="product.description" class="prose max-w-none text-gray-600">
                {{ product.description }}
              </div>
              <p v-else class="text-gray-500">No description available.</p>
            </div>

            <!-- Nutrition Tab -->
            <div v-if="activeTab === 'nutrition' && product.nutrition_info">
              <h3 class="font-semibold text-gray-900 mb-4">
                Nutritional Information (per {{ product.nutrition_info.serving_size || '100g' }})
              </h3>
              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                <div v-if="product.nutrition_info.calories" class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-500">Calories</p>
                  <p class="text-lg font-semibold text-gray-900">{{ product.nutrition_info.calories }}</p>
                </div>
                <div v-if="product.nutrition_info.protein" class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-500">Protein</p>
                  <p class="text-lg font-semibold text-gray-900">{{ product.nutrition_info.protein }}g</p>
                </div>
                <div v-if="product.nutrition_info.fat" class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-500">Fat</p>
                  <p class="text-lg font-semibold text-gray-900">{{ product.nutrition_info.fat }}g</p>
                </div>
                <div v-if="product.nutrition_info.saturated_fat" class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-500">Saturated Fat</p>
                  <p class="text-lg font-semibold text-gray-900">{{ product.nutrition_info.saturated_fat }}g</p>
                </div>
                <div v-if="product.nutrition_info.carbohydrates" class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-500">Carbohydrates</p>
                  <p class="text-lg font-semibold text-gray-900">{{ product.nutrition_info.carbohydrates }}g</p>
                </div>
                <div v-if="product.nutrition_info.fiber" class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-500">Fiber</p>
                  <p class="text-lg font-semibold text-gray-900">{{ product.nutrition_info.fiber }}g</p>
                </div>
                <div v-if="product.nutrition_info.sodium" class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-500">Sodium</p>
                  <p class="text-lg font-semibold text-gray-900">{{ product.nutrition_info.sodium }}mg</p>
                </div>
                <div v-if="product.nutrition_info.cholesterol" class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-sm text-gray-500">Cholesterol</p>
                  <p class="text-lg font-semibold text-gray-900">{{ product.nutrition_info.cholesterol }}mg</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <section v-if="relatedProducts.length > 0" class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <ProductCard v-for="relProduct in relatedProducts" :key="relProduct.id" :product="relProduct"
            :show-quick-view="false" />
        </div>
      </section>
    </div>
  </div>
</template>
