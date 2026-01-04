<script setup>
/**
 * Featured Products Section
 *
 * Displays featured products dynamically from database with working actions.
 *
 * @requirement LAND-004 Featured products carousel/grid
 */
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useProductsStore } from '@/stores/products'
import { useWishlistStore } from '@/stores/wishlist'
import { useCartStore } from '@/stores/cart'
import { useCurrencyStore } from '@/stores/currency'
import { toast } from 'vue-sonner'

const productsStore = useProductsStore()
const wishlistStore = useWishlistStore()
const cartStore = useCartStore()
const currencyStore = useCurrencyStore()

const isLoading = ref(false)
const showQuickView = ref(false)
const quickViewProduct = ref(null)
const featuredProductsData = ref([])

// Fetch featured products from database
const featuredProducts = computed(() => featuredProductsData.value)

onMounted(async () => {
  isLoading.value = true
  try {
    const products = await productsStore.fetchFeaturedProducts(8)
    featuredProductsData.value = products || []
  } catch (error) {
    console.error('Failed to load featured products:', error)
    featuredProductsData.value = []
  } finally {
    isLoading.value = false
  }
})

// Intersection observer for scroll animations
const sectionRef = ref(null)
const isVisible = ref(false)

onMounted(() => {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          isVisible.value = true
          observer.unobserve(entry.target)
        }
      })
    },
    { threshold: 0.1 }
  )

  if (sectionRef.value) {
    observer.observe(sectionRef.value)
  }
})

// Format price
function formatPrice(price) {
  return currencyStore.formatPrice(price)
}

// Quick view
function openQuickView(product) {
  quickViewProduct.value = product
  showQuickView.value = true
}

function closeQuickView() {
  showQuickView.value = false
  quickViewProduct.value = null
}

// Wishlist toggle
async function toggleWishlist(product) {
  const isInWishlist = wishlistStore.isInWishlist(product.id)

  if (isInWishlist) {
    const result = await wishlistStore.removeFromWishlist(product.id)
    if (result.success) {
      toast.success('Removed from wishlist')
    }
  } else {
    const result = await wishlistStore.addToWishlist(product.id)
    if (result.success) {
      toast.success('Added to wishlist')
    }
  }
}

// Add to cart
async function addToCart(product) {
  await cartStore.addItem({
    product_id: product.id,
    quantity: 1,
    price: product.price
  })
  toast.success(`${product.name} added to cart`)
}

// Get product image
function getProductImage(product) {
  return product.main_image || product.image || '/images/placeholder-product.jpg'
}
</script>

<template>
  <section ref="sectionRef" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div class="text-center mb-16 transition-all duration-700"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
        <span class="text-primary-600 font-semibold text-sm tracking-wider uppercase">Our Products</span>
        <h2 class="text-3xl sm:text-4xl font-bold text-secondary-700 mt-2 mb-4">
          Featured Selection
        </h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Hand-picked premium cuts, selected by our expert butchers for exceptional quality and taste.
        </p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
        <div v-for="i in 4" :key="i"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-pulse">
          <div class="aspect-square bg-gray-200"></div>
          <div class="p-5">
            <div class="h-3 bg-gray-200 rounded w-1/4 mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
            <div class="h-3 bg-gray-200 rounded w-full mb-4"></div>
            <div class="h-6 bg-gray-200 rounded w-1/3"></div>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
        <div v-for="(product, index) in featuredProducts" :key="product.id"
          class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-500 hover:-translate-y-2"
          :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
          :style="{ transitionDelay: `${index * 100 + 200}ms` }">
          <!-- Product Image -->
          <div class="relative aspect-square overflow-hidden bg-gray-100">
            <img :src="getProductImage(product)" :alt="product.name"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy"
              @error="($event) => $event.target.src = '/images/placeholder-product.jpg'" />

            <!-- Badge -->
            <span v-if="product.badge || product.is_featured"
              class="absolute top-3 left-3 px-3 py-1 text-xs font-semibold rounded-full" :class="{
                'bg-primary-600 text-white': product.badge === 'Best Seller',
                'bg-green-500 text-white': product.badge === 'Organic',
                'bg-primary-500 text-white': product.badge === 'On Sale' || !product.badge
              }">
              {{ product.badge || 'Featured' }}
            </span>

            <!-- Quick Actions -->
            <div
              class="absolute inset-0 bg-black/40 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              <button @click="openQuickView(product)"
                class="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-primary-600 hover:text-white transition-colors"
                title="Quick View">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
              <button @click="toggleWishlist(product)" :class="[
                'w-10 h-10 rounded-full flex items-center justify-center transition-colors',
                wishlistStore.isInWishlist(product.id)
                  ? 'bg-primary-600 text-white'
                  : 'bg-white hover:bg-primary-600 hover:text-white'
              ]" title="Add to Wishlist">
                <svg class="w-5 h-5" :fill="wishlistStore.isInWishlist(product.id) ? 'currentColor' : 'none'"
                  stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="p-5">
            <span class="text-xs text-gray-500 uppercase tracking-wider">{{ product.category_name || product.category
              }}</span>
            <h3 class="font-semibold text-secondary-700 mt-1 mb-2 group-hover:text-primary-600 transition-colors">
              <RouterLink :to="`/shop/${product.slug}`">
                {{ product.name }}
              </RouterLink>
            </h3>
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ product.description || 'Premium quality meat product'
              }}</p>

            <!-- Price -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="text-lg font-bold text-primary-600">{{ formatPrice(product.price) }}</span>
                <span v-if="product.original_price && product.original_price > product.price"
                  class="text-sm text-gray-400 line-through">
                  {{ formatPrice(product.original_price) }}
                </span>
              </div>
              <button @click="addToCart(product)"
                class="w-10 h-10 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center hover:bg-primary-600 hover:text-white transition-colors"
                title="Add to Cart">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!isLoading && featuredProducts.length === 0" class="text-center py-12">
        <p class="text-gray-500 mb-4">No featured products available at the moment.</p>
        <RouterLink to="/shop"
          class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
          Browse All Products
        </RouterLink>
      </div>

      <!-- View All Button -->
      <div v-if="featuredProducts.length > 0" class="text-center mt-12 transition-all duration-700 delay-700"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
        <RouterLink to="/shop"
          class="inline-flex items-center gap-2 px-8 py-4 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-all duration-300 hover:-translate-y-0.5 shadow-lg">
          <span>View All Products</span>
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
          </svg>
        </RouterLink>
      </div>
    </div>

    <!-- Quick View Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showQuickView" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
          @click.self="closeQuickView">
          <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <div
              class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
              <h3 class="text-xl font-bold text-secondary-700">Quick View</h3>
              <button @click="closeQuickView"
                class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div v-if="quickViewProduct" class="p-6">
              <div class="grid md:grid-cols-2 gap-8">
                <!-- Product Image -->
                <div class="aspect-square rounded-xl overflow-hidden bg-gray-100">
                  <img :src="getProductImage(quickViewProduct)" :alt="quickViewProduct.name"
                    class="w-full h-full object-cover" />
                </div>

                <!-- Product Details -->
                <div>
                  <span class="text-sm text-gray-500 uppercase tracking-wider">
                    {{ quickViewProduct.category_name || quickViewProduct.category }}
                  </span>
                  <h2 class="text-3xl font-bold text-secondary-700 mt-2 mb-4">
                    {{ quickViewProduct.name }}
                  </h2>
                  <p class="text-gray-600 mb-6">
                    {{ quickViewProduct.description || 'Premium quality meat product' }}
                  </p>

                  <!-- Price -->
                  <div class="flex items-center gap-3 mb-6">
                    <span class="text-3xl font-bold text-primary-600">
                      {{ formatPrice(quickViewProduct.price) }}
                    </span>
                    <span
                      v-if="quickViewProduct.original_price && quickViewProduct.original_price > quickViewProduct.price"
                      class="text-xl text-gray-400 line-through">
                      {{ formatPrice(quickViewProduct.original_price) }}
                    </span>
                  </div>

                  <!-- Actions -->
                  <div class="flex gap-4">
                    <button @click="addToCart(quickViewProduct); closeQuickView()"
                      class="flex-1 px-6 py-4 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
                      Add to Cart
                    </button>
                    <button @click="toggleWishlist(quickViewProduct)" :class="[
                      'px-6 py-4 rounded-xl font-semibold transition-colors',
                      wishlistStore.isInWishlist(quickViewProduct.id)
                        ? 'bg-primary-600 text-white hover:bg-primary-700'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    ]">
                      {{ wishlistStore.isInWishlist(quickViewProduct.id) ? 'In Wishlist' : 'Add to Wishlist' }}
                    </button>
                  </div>

                  <!-- View Full Details -->
                  <RouterLink :to="`/shop/${quickViewProduct.slug}`"
                    class="block mt-4 text-center text-primary-600 hover:text-primary-700 font-medium"
                    @click="closeQuickView">
                    View Full Details →
                  </RouterLink>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </section>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active>div,
.modal-leave-active>div {
  transition: transform 0.3s ease;
}

.modal-enter-from>div,
.modal-leave-to>div {
  transform: scale(0.9);
}
</style>
