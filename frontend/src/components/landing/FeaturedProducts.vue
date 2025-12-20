<script setup>
/**
 * Featured Products Section
 *
 * Displays featured products with card animations.
 *
 * @requirement LAND-004 Featured products carousel/grid
 */
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'

// Props
defineProps({
  products: {
    type: Array,
    default: () => []
  }
})

// Placeholder products for demo - using Unsplash images
const demoProducts = ref([
  {
    id: 1,
    name: 'Premium Scotch Fillet',
    slug: 'premium-scotch-fillet',
    description: 'Tender, marbled and full of flavor',
    price: 45.99,
    original_price: 52.99,
    image: 'https://images.unsplash.com/photo-1603048297172-c92544798d5a?w=400&h=400&fit=crop',
    category: 'Beef',
    badge: 'Best Seller'
  },
  {
    id: 2,
    name: 'Lamb Cutlets',
    slug: 'lamb-cutlets',
    description: 'Free-range Australian lamb',
    price: 38.99,
    original_price: null,
    image: 'https://images.unsplash.com/photo-1608039829572-67bfc5a4db48?w=400&h=400&fit=crop',
    category: 'Lamb',
    badge: null
  },
  {
    id: 3,
    name: 'Chicken Breast Fillets',
    slug: 'chicken-breast-fillets',
    description: 'Free-range, antibiotic free',
    price: 18.99,
    original_price: null,
    image: 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=400&h=400&fit=crop',
    category: 'Poultry',
    badge: 'Organic'
  },
  {
    id: 4,
    name: 'Pork Belly',
    slug: 'pork-belly',
    description: 'Perfect for slow roasting',
    price: 28.99,
    original_price: 34.99,
    image: 'https://images.unsplash.com/photo-1623047499024-c6f07e6a3d0e?w=400&h=400&fit=crop',
    category: 'Pork',
    badge: 'On Sale'
  }
])

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
  return new Intl.NumberFormat('en-AU', {
    style: 'currency',
    currency: 'AUD'
  }).format(price)
}
</script>

<template>
  <section ref="sectionRef" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div
        class="text-center mb-16 transition-all duration-700"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
      >
        <span class="text-primary-600 font-semibold text-sm tracking-wider uppercase">Our Products</span>
        <h2 class="text-3xl sm:text-4xl font-bold text-secondary-700 mt-2 mb-4">
          Featured Selection
        </h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Hand-picked premium cuts, selected by our expert butchers for exceptional quality and taste.
        </p>
      </div>

      <!-- Products Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
        <div
          v-for="(product, index) in (products.length > 0 ? products : demoProducts)"
          :key="product.id"
          class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-500 hover:-translate-y-2"
          :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
          :style="{ transitionDelay: `${index * 100 + 200}ms` }"
        >
          <!-- Product Image -->
          <div class="relative aspect-square overflow-hidden bg-gray-100">
            <img
              :src="product.image"
              :alt="product.name"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
              loading="lazy"
              @error="($event) => $event.target.src = 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=400&h=400&fit=crop'"
            />

            <!-- Badge -->
            <span
              v-if="product.badge"
              class="absolute top-3 left-3 px-3 py-1 text-xs font-semibold rounded-full"
              :class="{
                'bg-primary-600 text-white': product.badge === 'Best Seller',
                'bg-green-500 text-white': product.badge === 'Organic',
                'bg-primary-500 text-white': product.badge === 'On Sale'
              }"
            >
              {{ product.badge }}
            </span>

            <!-- Quick Actions -->
            <div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              <button
                class="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-primary-600 hover:text-white transition-colors"
                title="Quick View"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
              <button
                class="w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-primary-600 hover:text-white transition-colors"
                title="Add to Wishlist"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="p-5">
            <span class="text-xs text-gray-500 uppercase tracking-wider">{{ product.category }}</span>
            <h3 class="font-semibold text-secondary-700 mt-1 mb-2 group-hover:text-primary-600 transition-colors">
              <RouterLink :to="`/shop/${product.slug}`">
                {{ product.name }}
              </RouterLink>
            </h3>
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ product.description }}</p>

            <!-- Price -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="text-lg font-bold text-primary-600">{{ formatPrice(product.price) }}</span>
                <span
                  v-if="product.original_price"
                  class="text-sm text-gray-400 line-through"
                >
                  {{ formatPrice(product.original_price) }}
                </span>
              </div>
              <button
                class="w-10 h-10 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center hover:bg-primary-600 hover:text-white transition-colors"
                title="Add to Cart"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- View All Button -->
      <div
        class="text-center mt-12 transition-all duration-700 delay-700"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
      >
        <RouterLink
          to="/shop"
          class="inline-flex items-center gap-2 px-8 py-4 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-all duration-300 hover:-translate-y-0.5 shadow-lg"
        >
          <span>View All Products</span>
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
          </svg>
        </RouterLink>
      </div>
    </div>
  </section>
</template>
