<script setup>
/**
 * SearchModal.vue
 * Global search modal with keyboard shortcuts
 */
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { Search, X, Clock, TrendingUp } from 'lucide-vue-next'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const router = useRouter()
const searchQuery = ref('')
const selectedIndex = ref(0)
const recentSearches = ref([])

// Mock search results - in production, this would call an API
const searchResults = computed(() => {
  if (!searchQuery.value.trim()) {
    return []
  }

  // Mock results
  return [
    {
      type: 'product',
      title: 'Beef Steak',
      subtitle: 'Premium cuts',
      path: '/products/beef-steak'
    },
    {
      type: 'order',
      title: 'Order #12345',
      subtitle: 'Pending delivery',
      path: '/orders/12345'
    },
    {
      type: 'customer',
      title: 'John Doe',
      subtitle: 'john@example.com',
      path: '/customers/1'
    }
  ].filter(item =>
    item.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    item.subtitle.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Load recent searches from localStorage
onMounted(() => {
  const saved = localStorage.getItem('recent-searches')
  if (saved) {
    recentSearches.value = JSON.parse(saved)
  }
})

// Watch for show prop to reset state
watch(() => props.show, (newValue) => {
  if (newValue) {
    searchQuery.value = ''
    selectedIndex.value = 0
    // Focus input after modal opens
    setTimeout(() => {
      const input = document.getElementById('search-input')
      if (input) input.focus()
    }, 100)
  }
})

// Keyboard navigation
function handleKeyDown(event) {
  if (!props.show) return

  switch (event.key) {
    case 'Escape':
      emit('close')
      break
    case 'ArrowDown':
      event.preventDefault()
      selectedIndex.value = Math.min(selectedIndex.value + 1, searchResults.value.length - 1)
      break
    case 'ArrowUp':
      event.preventDefault()
      selectedIndex.value = Math.max(selectedIndex.value - 1, 0)
      break
    case 'Enter':
      event.preventDefault()
      if (searchResults.value[selectedIndex.value]) {
        selectResult(searchResults.value[selectedIndex.value])
      }
      break
  }
}

// Select result
function selectResult(result) {
  // Save to recent searches
  addToRecentSearches(result.title)
  
  // Navigate to result
  router.push(result.path)
  emit('close')
}

// Add to recent searches
function addToRecentSearches(query) {
  const searches = [query, ...recentSearches.value.filter(s => s !== query)].slice(0, 5)
  recentSearches.value = searches
  localStorage.setItem('recent-searches', JSON.stringify(searches))
}

// Clear recent searches
function clearRecentSearches() {
  recentSearches.value = []
  localStorage.removeItem('recent-searches')
}

// Get result icon
function getResultIcon(type) {
  switch (type) {
    case 'product':
      return '📦'
    case 'order':
      return '🛒'
    case 'customer':
      return '👤'
    default:
      return '🔍'
  }
}

// Setup keyboard listeners
onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
})
</script>

<template>
  <teleport to="body">
    <transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="emit('close')"></div>

        <!-- Modal -->
        <div class="relative w-full max-w-2xl bg-white rounded-lg shadow-2xl border-2 border-[#CF0D0F]">
          <!-- Search Input -->
          <div class="flex items-center border-b-2 border-gray-200 px-4 py-3">
            <Search class="w-5 h-5 text-gray-400 mr-3" />
            <input
              id="search-input"
              v-model="searchQuery"
              type="text"
              placeholder="Search products, orders, customers..."
              class="flex-1 outline-none text-gray-900 placeholder-gray-400"
              @keydown="handleKeyDown"
            />
            <button @click="emit('close')" class="p-1 hover:bg-gray-100 rounded transition-colors">
              <X class="w-5 h-5 text-gray-400" />
            </button>
          </div>

          <!-- Results -->
          <div class="max-h-96 overflow-y-auto">
            <!-- Search Results -->
            <div v-if="searchQuery && searchResults.length > 0" class="py-2">
              <div class="px-4 py-2">
                <p class="text-xs font-semibold text-gray-500 uppercase">Search Results</p>
              </div>
              <button
                v-for="(result, index) in searchResults"
                :key="index"
                @click="selectResult(result)"
                :class="[
                  'w-full flex items-center px-4 py-3 hover:bg-gray-50 transition-colors',
                  { 'bg-gray-100': index === selectedIndex }
                ]"
              >
                <span class="text-2xl mr-3">{{ getResultIcon(result.type) }}</span>
                <div class="flex-1 text-left">
                  <p class="text-sm font-medium text-gray-900">{{ result.title }}</p>
                  <p class="text-xs text-gray-500">{{ result.subtitle }}</p>
                </div>
                <span class="text-xs text-gray-400 capitalize">{{ result.type }}</span>
              </button>
            </div>

            <!-- No Results -->
            <div v-else-if="searchQuery && searchResults.length === 0" class="py-12 text-center">
              <Search class="w-12 h-12 text-gray-300 mx-auto mb-3" />
              <p class="text-gray-500">No results found for "{{ searchQuery }}"</p>
            </div>

            <!-- Recent Searches -->
            <div v-else-if="recentSearches.length > 0" class="py-2">
              <div class="flex items-center justify-between px-4 py-2">
                <p class="text-xs font-semibold text-gray-500 uppercase flex items-center">
                  <Clock class="w-3 h-3 mr-1" />
                  Recent Searches
                </p>
                <button @click="clearRecentSearches"
                  class="text-xs text-[#CF0D0F] hover:text-[#F6211F] font-medium transition-colors">
                  Clear
                </button>
              </div>
              <button
                v-for="(search, index) in recentSearches"
                :key="index"
                @click="searchQuery = search"
                class="w-full flex items-center px-4 py-2 hover:bg-gray-50 transition-colors text-left"
              >
                <Clock class="w-4 h-4 text-gray-400 mr-3" />
                <span class="text-sm text-gray-700">{{ search }}</span>
              </button>
            </div>

            <!-- Empty State -->
            <div v-else class="py-12 text-center">
              <Search class="w-12 h-12 text-gray-300 mx-auto mb-3" />
              <p class="text-gray-500 mb-2">Start typing to search</p>
              <p class="text-xs text-gray-400">Search products, orders, customers, and more</p>
            </div>
          </div>

          <!-- Footer -->
          <div class="border-t-2 border-gray-200 px-4 py-2 flex items-center justify-between text-xs text-gray-500">
            <div class="flex items-center space-x-4">
              <span class="flex items-center">
                <kbd class="px-2 py-1 bg-gray-100 rounded border border-gray-300 mr-1">↑</kbd>
                <kbd class="px-2 py-1 bg-gray-100 rounded border border-gray-300 mr-1">↓</kbd>
                to navigate
              </span>
              <span class="flex items-center">
                <kbd class="px-2 py-1 bg-gray-100 rounded border border-gray-300 mr-1">↵</kbd>
                to select
              </span>
            </div>
            <span class="flex items-center">
              <kbd class="px-2 py-1 bg-gray-100 rounded border border-gray-300 mr-1">esc</kbd>
              to close
            </span>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

kbd {
  font-family: monospace;
  font-size: 11px;
}
</style>
