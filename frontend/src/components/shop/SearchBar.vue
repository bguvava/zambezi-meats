<script setup>
/**
 * SearchBar.vue
 * Product search with autocomplete suggestions.
 *
 * @requirement SHOP-018 Search products with autocomplete
 * @requirement SHOP-019 Display search suggestions
 */
import { ref, watch, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useProductsStore } from '@/stores/products'
import { useCurrencyStore } from '@/stores/currency'
import { useDebounceFn } from '@vueuse/core'

const props = defineProps({
  placeholder: {
    type: String,
    default: 'Search products...'
  },
  showSuggestions: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['search', 'select'])

const router = useRouter()
const productsStore = useProductsStore()
const currencyStore = useCurrencyStore()

// Helper to format price
function formatPrice(price) {
  return currencyStore.format(price)
}

const query = ref('')
const isOpen = ref(false)
const isFocused = ref(false)
const selectedIndex = ref(-1)

const suggestions = computed(() => productsStore.searchResults)
const isSearching = computed(() => productsStore.isSearching)

// Debounced search
const debouncedSearch = useDebounceFn(async (searchQuery) => {
  if (searchQuery && searchQuery.length >= 2) {
    await productsStore.quickSearch(searchQuery)
    if (props.showSuggestions) {
      isOpen.value = true
    }
  } else {
    productsStore.clearSearch()
    isOpen.value = false
  }
}, 300)

// Watch query changes
watch(query, (newQuery) => {
  selectedIndex.value = -1
  debouncedSearch(newQuery)
})

function handleFocus() {
  isFocused.value = true
  if (suggestions.value.length > 0 && props.showSuggestions) {
    isOpen.value = true
  }
}

function handleBlur() {
  isFocused.value = false
  // Delay closing to allow click on suggestion
  setTimeout(() => {
    isOpen.value = false
  }, 200)
}

function handleKeydown(event) {
  if (!isOpen.value || suggestions.value.length === 0) {
    if (event.key === 'Enter') {
      handleSearch()
    }
    return
  }

  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      selectedIndex.value = Math.min(selectedIndex.value + 1, suggestions.value.length - 1)
      break
    case 'ArrowUp':
      event.preventDefault()
      selectedIndex.value = Math.max(selectedIndex.value - 1, -1)
      break
    case 'Enter':
      event.preventDefault()
      if (selectedIndex.value >= 0) {
        selectSuggestion(suggestions.value[selectedIndex.value])
      } else {
        handleSearch()
      }
      break
    case 'Escape':
      isOpen.value = false
      selectedIndex.value = -1
      break
  }
}

function handleSearch() {
  if (query.value.trim()) {
    isOpen.value = false
    emit('search', query.value.trim())
    router.push({
      path: '/shop',
      query: { search: query.value.trim() }
    })
  }
}

function selectSuggestion(product) {
  query.value = product.name
  isOpen.value = false
  emit('select', product)
  router.push(`/products/${product.slug}`)
}

function clearSearch() {
  query.value = ''
  productsStore.clearSearch()
  isOpen.value = false
}
</script>

<template>
  <div class="relative">
    <!-- Search Input -->
    <div class="relative">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg v-if="!isSearching" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <svg v-else class="w-5 h-5 text-primary-600 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
          </path>
        </svg>
      </div>

      <input v-model="query" type="text" :placeholder="placeholder"
        class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-shadow"
        @focus="handleFocus" @blur="handleBlur" @keydown="handleKeydown" />

      <!-- Clear Button -->
      <button v-if="query" @click="clearSearch" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Suggestions Dropdown -->
    <Transition enter-active-class="transition-all duration-200" leave-active-class="transition-all duration-200"
      enter-from-class="opacity-0 translate-y-1" leave-to-class="opacity-0 translate-y-1">
      <div v-if="isOpen && showSuggestions && (suggestions.length > 0 || isSearching)"
        class="absolute z-50 w-full mt-1 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
        <!-- Loading State -->
        <div v-if="isSearching && suggestions.length === 0" class="p-4 text-center">
          <div class="flex items-center justify-center gap-2 text-gray-500">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            <span>Searching...</span>
          </div>
        </div>

        <!-- Suggestions List -->
        <ul v-else class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
          <li v-for="(product, index) in suggestions" :key="product.id" class="transition-colors" :class="[
            selectedIndex === index
              ? 'bg-primary-50'
              : 'hover:bg-gray-50'
          ]">
            <button @click="selectSuggestion(product)" class="w-full flex items-center gap-3 p-3 text-left">
              <!-- Product Image -->
              <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                <img v-if="product.primary_image?.url" :src="product.primary_image.url" :alt="product.name"
                  class="w-full h-full object-cover" />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>

              <!-- Product Info -->
              <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 truncate">{{ product.name }}</p>
                <p class="text-sm text-gray-500">{{ product.category?.name }}</p>
              </div>

              <!-- Price -->
              <div class="text-right">
                <p class="font-semibold text-primary-600">{{ formatPrice(product.price) }}</p>
                <p class="text-xs text-gray-500">/ {{ product.unit || 'kg' }}</p>
              </div>
            </button>
          </li>
        </ul>

        <!-- View All Results -->
        <div v-if="suggestions.length > 0" class="p-3 bg-gray-50 border-t border-gray-100">
          <button @click="handleSearch"
            class="w-full text-center text-sm text-primary-600 hover:text-primary-700 font-medium">
            View all results for "{{ query }}"
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>
