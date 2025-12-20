<script setup>
/**
 * CategorySidebar.vue
 * Displays category navigation for filtering products.
 *
 * @requirement SHOP-013 Filter by category
 * @requirement SHOP-014 Display category sidebar
 */
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useProductsStore } from '@/stores/products'

const props = defineProps({
  categories: {
    type: Array,
    default: () => []
  },
  selectedCategory: {
    type: String,
    default: null
  },
  showCount: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['select'])

const route = useRoute()
const productsStore = useProductsStore()

const isLoading = computed(() => productsStore.isLoading)

function selectCategory(slug) {
  emit('select', slug)
}

function isActive(categorySlug) {
  return props.selectedCategory === categorySlug || route.params.category === categorySlug
}
</script>

<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
      <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
      </svg>
      Categories
    </h3>

    <!-- Loading State -->
    <div v-if="isLoading" class="space-y-2">
      <div v-for="n in 5" :key="n" class="h-8 bg-gray-100 rounded animate-pulse"></div>
    </div>

    <!-- Category List -->
    <ul v-else class="space-y-1">
      <!-- All Products -->
      <li>
        <button
          @click="selectCategory(null)"
          class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-left transition-colors"
          :class="[
            !selectedCategory 
              ? 'bg-primary-50 text-primary-700 font-medium'
              : 'text-gray-700 hover:bg-gray-50'
          ]"
        >
          <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            All Products
          </span>
        </button>
      </li>

      <!-- Categories -->
      <li v-for="category in categories" :key="category.id">
        <button
          @click="selectCategory(category.slug)"
          class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-left transition-colors"
          :class="[
            isActive(category.slug) 
              ? 'bg-primary-50 text-primary-700 font-medium'
              : 'text-gray-700 hover:bg-gray-50'
          ]"
        >
          <span class="flex items-center gap-2">
            <!-- Category Icon -->
            <span 
              v-if="category.icon" 
              class="w-4 h-4 flex items-center justify-center"
            >
              {{ category.icon }}
            </span>
            <svg 
              v-else 
              class="w-4 h-4" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
            </svg>
            {{ category.name }}
          </span>

          <!-- Product Count -->
          <span 
            v-if="showCount && category.products_count !== undefined"
            class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"
          >
            {{ category.products_count }}
          </span>
        </button>
      </li>
    </ul>

    <!-- No Categories Message -->
    <p 
      v-if="!isLoading && categories.length === 0" 
      class="text-sm text-gray-500 text-center py-4"
    >
      No categories available
    </p>
  </div>
</template>
