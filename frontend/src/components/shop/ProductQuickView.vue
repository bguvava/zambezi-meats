<script setup>
/**
 * ProductQuickView.vue
 * Modal for quick product preview without leaving the shop page.
 *
 * @requirement SHOP-020 Implement quick-view modal
 */
import { ref, computed, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useCurrencyStore } from '@/stores/currency'

const props = defineProps({
  product: {
    type: Object,
    default: null
  },
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const cartStore = useCartStore()
const currencyStore = useCurrencyStore()

const quantity = ref(1)
const currentImageIndex = ref(0)
const isAdding = ref(false)
const showSuccess = ref(false)

const isInCart = computed(() => props.product && cartStore.isInCart(props.product.id))
const cartQuantity = computed(() => props.product ? cartStore.getQuantity(props.product.id) : 0)

const images = computed(() => {
  if (!props.product) return []
  return props.product.images?.length > 0
    ? props.product.images
    : [{ url: '/images/placeholder-product.jpg', alt_text: props.product.name }]
})

const currentImage = computed(() => images.value[currentImageIndex.value])

const hasDiscount = computed(() => {
  return props.product?.compare_at_price && props.product.compare_at_price > props.product.price
})

const discountPercentage = computed(() => {
  if (!hasDiscount.value) return 0
  return Math.round((1 - props.product.price / props.product.compare_at_price) * 100)
})

// Format prices using currency store for dynamic currency switching
const formattedPrice = computed(() => props.product ? currencyStore.format(props.product.price) : '')
const formattedCompareAtPrice = computed(() =>
  hasDiscount.value && props.product ? currencyStore.format(props.product.compare_at_price) : ''
)

const isOutOfStock = computed(() => {
  return props.product && props.product.stock_quantity <= 0
})

// Reset state when product changes
watch(() => props.product, () => {
  quantity.value = 1
  currentImageIndex.value = 0
  showSuccess.value = false
})

// Reset when closed
watch(() => props.isOpen, (isOpen) => {
  if (!isOpen) {
    quantity.value = 1
    currentImageIndex.value = 0
    showSuccess.value = false
  }
})

// Prevent body scroll when modal is open
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})

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

function selectImage(index) {
  currentImageIndex.value = index
}

async function handleAddToCart() {
  if (!props.product || isOutOfStock.value || isAdding.value) return

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

function handleClose() {
  emit('close')
}
</script>

<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <Transition enter-active-class="transition-opacity duration-300"
      leave-active-class="transition-opacity duration-300" enter-from-class="opacity-0" leave-to-class="opacity-0">
      <div v-if="isOpen" class="fixed inset-0 bg-black/50 z-50" @click="handleClose"></div>
    </Transition>

    <!-- Modal -->
    <Transition enter-active-class="transition-all duration-300" leave-active-class="transition-all duration-300"
      enter-from-class="opacity-0 scale-95" leave-to-class="opacity-0 scale-95">
      <div v-if="isOpen && product" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="handleClose">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
          <!-- Close Button -->
          <button @click="handleClose"
            class="absolute top-4 right-4 z-10 p-2 bg-white/90 backdrop-blur-sm rounded-full shadow-md text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
            <!-- Image Section -->
            <div class="relative bg-gray-100 aspect-square md:aspect-auto md:h-full">
              <!-- Main Image -->
              <img :src="currentImage?.url" :alt="currentImage?.alt_text || product.name"
                class="w-full h-full object-cover" />

              <!-- Image Navigation -->
              <div v-if="images.length > 1"
                class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-4">
                <button @click="prevImage"
                  class="p-2 bg-white/90 rounded-full shadow-md text-gray-700 hover:bg-white transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                </button>
                <button @click="nextImage"
                  class="p-2 bg-white/90 rounded-full shadow-md text-gray-700 hover:bg-white transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </div>

              <!-- Thumbnails -->
              <div v-if="images.length > 1" class="absolute bottom-4 inset-x-4 flex justify-center gap-2">
                <button v-for="(img, index) in images" :key="index" @click="selectImage(index)"
                  class="w-12 h-12 rounded-lg overflow-hidden border-2 transition-colors" :class="[
                    currentImageIndex === index
                      ? 'border-primary-500'
                      : 'border-white/50 hover:border-white'
                  ]">
                  <img :src="img.url" :alt="`Thumbnail ${index + 1}`" class="w-full h-full object-cover" />
                </button>
              </div>

              <!-- Badges -->
              <div class="absolute top-4 left-4 flex flex-col gap-2">
                <span v-if="hasDiscount" class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                  -{{ discountPercentage }}%
                </span>
                <span v-if="product.is_featured" class="bg-primary-500 text-white text-xs font-bold px-2 py-1 rounded">
                  Featured
                </span>
                <span v-if="isOutOfStock" class="bg-gray-800 text-white text-xs font-bold px-2 py-1 rounded">
                  Out of Stock
                </span>
              </div>
            </div>

            <!-- Details Section -->
            <div class="p-6 overflow-y-auto max-h-[90vh] md:max-h-none">
              <!-- Category -->
              <p class="text-sm text-primary-600 font-medium mb-2">
                {{ product.category?.name || 'Uncategorized' }}
              </p>

              <!-- Title -->
              <h2 class="text-2xl font-bold text-gray-900 mb-4">
                {{ product.name }}
              </h2>

              <!-- Price -->
              <div class="flex items-center gap-3 mb-4">
                <span class="text-2xl font-bold text-gray-900">
                  {{ formattedPrice }}
                </span>
                <span class="text-sm text-gray-500">/ {{ product.unit || 'kg' }}</span>
                <span v-if="hasDiscount" class="text-lg text-gray-400 line-through">
                  {{ formattedCompareAtPrice }}
                </span>
              </div>

              <!-- Stock Status -->
              <p v-if="!isOutOfStock" class="text-sm text-green-600 mb-4">
                ✓ {{ product.stock_quantity }}{{ product.unit || 'kg' }} in stock
              </p>
              <p v-else class="text-sm text-red-500 mb-4">
                ✕ Currently unavailable
              </p>

              <!-- Description -->
              <div class="mb-6">
                <h3 class="font-medium text-gray-900 mb-2">Description</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                  {{ product.description || product.short_description || 'No description available.' }}
                </p>
              </div>

              <!-- Nutrition Info -->
              <div v-if="product.nutrition_info" class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-900 mb-2">Nutrition Info (per {{ product.nutrition_info.serving_size ||
                  '100g' }})</h3>
                <div class="grid grid-cols-3 gap-2 text-sm">
                  <div v-if="product.nutrition_info.calories">
                    <span class="text-gray-500">Calories:</span>
                    <span class="ml-1 text-gray-900">{{ product.nutrition_info.calories }}</span>
                  </div>
                  <div v-if="product.nutrition_info.protein">
                    <span class="text-gray-500">Protein:</span>
                    <span class="ml-1 text-gray-900">{{ product.nutrition_info.protein }}g</span>
                  </div>
                  <div v-if="product.nutrition_info.fat">
                    <span class="text-gray-500">Fat:</span>
                    <span class="ml-1 text-gray-900">{{ product.nutrition_info.fat }}g</span>
                  </div>
                </div>
              </div>

              <!-- Quantity & Add to Cart -->
              <div v-if="!isOutOfStock" class="space-y-4">
                <div class="flex items-center gap-4">
                  <label class="text-sm font-medium text-gray-700">Quantity:</label>
                  <div class="flex items-center border border-gray-300 rounded-lg">
                    <button @click="quantity = Math.max(0.5, quantity - 0.5)"
                      class="px-3 py-2 text-gray-600 hover:text-gray-900" :disabled="quantity <= 0.5">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                      </svg>
                    </button>
                    <input v-model.number="quantity" type="number" min="0.5" step="0.5" :max="product.stock_quantity"
                      class="w-16 text-center text-sm border-0 focus:ring-0" />
                    <button @click="quantity = Math.min(product.stock_quantity, quantity + 0.5)"
                      class="px-3 py-2 text-gray-600 hover:text-gray-900"
                      :disabled="quantity >= product.stock_quantity">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                    </button>
                  </div>
                  <span class="text-sm text-gray-500">{{ product.unit || 'kg' }}</span>
                </div>

                <button @click="handleAddToCart" :disabled="isAdding"
                  class="w-full py-3 px-6 rounded-lg font-medium flex items-center justify-center gap-2 transition-colors"
                  :class="[
                    showSuccess
                      ? 'bg-green-600 text-white'
                      : 'bg-primary-700 text-white hover:bg-primary-800'
                  ]">
                  <svg v-if="isAdding" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                  <svg v-else-if="showSuccess" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  {{ showSuccess ? 'Added to Cart!' : 'Add to Cart' }}
                </button>

                <!-- In Cart Indicator -->
                <p v-if="isInCart && !showSuccess" class="text-sm text-primary-600 text-center">
                  {{ cartQuantity }}{{ product.unit || 'kg' }} already in cart
                </p>
              </div>

              <!-- Out of Stock Button -->
              <button v-else disabled
                class="w-full py-3 px-6 rounded-lg font-medium bg-gray-200 text-gray-500 cursor-not-allowed">
                Out of Stock
              </button>

              <!-- View Full Details Link -->
              <RouterLink :to="`/products/${product.slug}`" @click="handleClose"
                class="block mt-4 text-center text-primary-600 hover:text-primary-700 font-medium">
                View Full Details →
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
