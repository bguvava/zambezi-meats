<script setup>
/**
 * ProductFilters.vue
 * Provides filtering and sorting options for products.
 *
 * @requirement SHOP-013 Filter by category
 * @requirement SHOP-015 Filter by price range
 * @requirement SHOP-016 Filter by availability
 * @requirement SHOP-017 Sort products
 */
import { ref, computed, watch } from 'vue'

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({
      minPrice: null,
      maxPrice: null,
      inStock: null,
      sort: 'created_at',
      direction: 'desc'
    })
  },
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update', 'close'])

// Local filter state
const localFilters = ref({ ...props.filters })

// Sort options
const sortOptions = [
  { value: 'created_at:desc', label: 'Newest First' },
  { value: 'created_at:asc', label: 'Oldest First' },
  { value: 'price:asc', label: 'Price: Low to High' },
  { value: 'price:desc', label: 'Price: High to Low' },
  { value: 'name:asc', label: 'Name: A to Z' },
  { value: 'name:desc', label: 'Name: Z to A' },
]

const currentSort = computed({
  get: () => `${localFilters.value.sort || 'created_at'}:${localFilters.value.direction || 'desc'}`,
  set: (value) => {
    const [sort, direction] = value.split(':')
    localFilters.value.sort = sort
    localFilters.value.direction = direction
  }
})

// Watch for prop changes
watch(() => props.filters, (newFilters) => {
  localFilters.value = { ...newFilters }
}, { deep: true })

function applyFilters() {
  emit('update', { ...localFilters.value })
}

function resetFilters() {
  localFilters.value = {
    minPrice: null,
    maxPrice: null,
    inStock: null,
    sort: 'created_at',
    direction: 'desc'
  }
  emit('update', { ...localFilters.value })
}

function handleSortChange(event) {
  currentSort.value = event.target.value
  applyFilters()
}
</script>

<template>
  <!-- Desktop Filters -->
  <div class="hidden lg:block bg-white rounded-lg shadow-sm border border-gray-200 p-5">
    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
      <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
      </svg>
      Filters
    </h3>

    <!-- Price Range -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-3">Price Range</label>
      <div class="flex items-center gap-2">
        <div class="relative flex-1">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
          <input
            v-model.number="localFilters.minPrice"
            type="number"
            placeholder="Min"
            min="0"
            class="w-full pl-7 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
          />
        </div>
        <span class="text-gray-400 font-medium">-</span>
        <div class="relative flex-1">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
          <input
            v-model.number="localFilters.maxPrice"
            type="number"
            placeholder="Max"
            min="0"
            class="w-full pl-7 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
          />
        </div>
      </div>
    </div>

    <!-- Availability -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-3">Availability</label>
      <div class="space-y-2">
        <label class="flex items-center gap-3 cursor-pointer group">
          <input
            v-model="localFilters.inStock"
            type="checkbox"
            :true-value="true"
            :false-value="null"
            class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
          />
          <span class="text-sm text-gray-700 group-hover:text-gray-900 transition-colors">In Stock Only</span>
        </label>
      </div>
    </div>

    <!-- Sort -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-3">Sort By</label>
      <select
        :value="currentSort"
        @change="handleSortChange"
        class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white transition-colors"
      >
        <option 
          v-for="option in sortOptions" 
          :key="option.value" 
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
    </div>

    <!-- Apply/Reset Buttons -->
    <div class="flex gap-3">
      <button
        @click="applyFilters"
        class="flex-1 bg-primary-600 text-white py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors shadow-sm"
      >
        Apply
      </button>
      <button
        @click="resetFilters"
        class="flex-1 border border-gray-300 text-gray-700 py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors"
      >
        Reset
      </button>
    </div>
  </div>

  <!-- Mobile Filters (Slide-out) -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-300"
      leave-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="lg:hidden fixed inset-0 bg-black/50 z-50"
        @click="emit('close')"
      ></div>
    </Transition>

    <Transition
      enter-active-class="transition-transform duration-300"
      leave-active-class="transition-transform duration-300"
      enter-from-class="translate-x-full"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="isOpen"
        class="lg:hidden fixed inset-y-0 right-0 w-80 max-w-full bg-white shadow-xl z-50 overflow-y-auto"
      >
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b">
          <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
          <button
            @click="emit('close')"
            class="p-2 text-gray-500 hover:text-gray-700"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="p-4">
          <!-- Price Range -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Price Range</label>
            <div class="flex items-center gap-2">
              <div class="relative flex-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
                <input
                  v-model.number="localFilters.minPrice"
                  type="number"
                  placeholder="Min"
                  min="0"
                  class="w-full pl-7 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
              <span class="text-gray-400 font-medium">-</span>
              <div class="relative flex-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">$</span>
                <input
                  v-model.number="localFilters.maxPrice"
                  type="number"
                  placeholder="Max"
                  min="0"
                  class="w-full pl-7 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
            </div>
          </div>

          <!-- Availability -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Availability</label>
            <label class="flex items-center gap-3 cursor-pointer group">
              <input
                v-model="localFilters.inStock"
                type="checkbox"
                :true-value="true"
                :false-value="null"
                class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
              />
              <span class="text-sm text-gray-700 group-hover:text-gray-900">In Stock Only</span>
            </label>
          </div>

          <!-- Sort -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Sort By</label>
            <select
              v-model="currentSort"
              class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
            >
              <option 
                v-for="option in sortOptions" 
                :key="option.value" 
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="p-4 border-t">
          <div class="flex gap-3">
            <button
              @click="resetFilters"
              class="flex-1 border border-gray-300 text-gray-700 py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Reset
            </button>
            <button
              @click="applyFilters(); emit('close')"
              class="flex-1 bg-primary-600 text-white py-2.5 px-4 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors shadow-sm"
            >
              Apply Filters
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
