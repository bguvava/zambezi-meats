<script setup>
/**
 * ShopPage.vue
 * Main shop/catalog page for browsing all products.
 *
 * @requirement SHOP-001 Create shop page layout
 * @requirement SHOP-002 Display product grid with cards
 * @requirement SHOP-013 Filter by category
 * @requirement SHOP-017 Sort products
 */
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductsStore } from '@/stores/products'
import ProductCard from '@/components/shop/ProductCard.vue'
import CategorySidebar from '@/components/shop/CategorySidebar.vue'
import ProductFilters from '@/components/shop/ProductFilters.vue'
import SearchBar from '@/components/shop/SearchBar.vue'
import ProductQuickView from '@/components/shop/ProductQuickView.vue'

const route = useRoute()
const router = useRouter()
const productsStore = useProductsStore()

// State
const isFilterOpen = ref(false)
const viewMode = ref('grid') // 'grid' or 'list'
const quickViewProduct = ref(null)
const isQuickViewOpen = ref(false)

// Computed
const products = computed(() => productsStore.products)
const categories = computed(() => productsStore.categories)
const isLoading = computed(() => productsStore.isLoading)
const pagination = computed(() => productsStore.pagination)
const filters = computed(() => productsStore.filters)
const hasProducts = computed(() => productsStore.hasProducts)
const hasMore = computed(() => productsStore.hasMore)

// Initialize
onMounted(async () => {
  // Load main categories only
  await productsStore.fetchCategories({ mainOnly: true })

  // Apply URL query params to filters
  applyUrlFilters()

  // Fetch products
  await productsStore.fetchProducts()
})

// Watch for route query changes
watch(() => route.query, () => {
  applyUrlFilters()
  productsStore.fetchProducts()
}, { deep: true })

function applyUrlFilters() {
  const query = route.query

  productsStore.setFilters({
    category: query.category || null,
    search: query.search || '',
    minPrice: query.min_price ? parseFloat(query.min_price) : null,
    maxPrice: query.max_price ? parseFloat(query.max_price) : null,
    inStock: query.in_stock === 'true' ? true : null,
    sort: query.sort || 'created_at',
    direction: query.direction || 'desc',
  })
}

function updateFilters(newFilters) {
  productsStore.setFilters(newFilters)

  // Update URL
  const query = {}
  if (newFilters.category) query.category = newFilters.category
  if (newFilters.search) query.search = newFilters.search
  if (newFilters.minPrice) query.min_price = newFilters.minPrice
  if (newFilters.maxPrice) query.max_price = newFilters.maxPrice
  if (newFilters.inStock) query.in_stock = 'true'
  if (newFilters.sort && newFilters.sort !== 'created_at') query.sort = newFilters.sort
  if (newFilters.direction && newFilters.direction !== 'desc') query.direction = newFilters.direction

  router.push({ query })
}

function handleCategorySelect(slug) {
  updateFilters({ ...filters.value, category: slug })
}

function handleSearch(query) {
  updateFilters({ ...filters.value, search: query })
}

function handlePageChange(page) {
  productsStore.fetchProducts({ page })
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function openQuickView(product) {
  quickViewProduct.value = product
  isQuickViewOpen.value = true
}

function closeQuickView() {
  isQuickViewOpen.value = false
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header -->
    <div class="bg-white border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Shop</h1>
            <p class="text-gray-600 mt-1">
              Browse our selection of premium quality meats
            </p>
          </div>

          <!-- Search Bar -->
          <div class="w-full md:w-96">
            <SearchBar @search="handleSearch" placeholder="Search products..." />
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar -->
        <aside class="lg:w-64 flex-shrink-0 space-y-6">
          <!-- Categories -->
          <CategorySidebar :categories="categories" :selected-category="filters.category"
            @select="handleCategorySelect" />

          <!-- Filters (Desktop) -->
          <ProductFilters :filters="filters" @update="updateFilters" />
        </aside>

        <!-- Main Content -->
        <main class="flex-1">
          <!-- Toolbar -->
          <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <!-- Results Count -->
            <p class="text-sm text-gray-600">
              <span v-if="!isLoading">
                Showing <strong>{{ products.length }}</strong> of
                <strong>{{ pagination.total }}</strong> products
              </span>
              <span v-else>Loading products...</span>
            </p>

            <div class="flex items-center gap-4">
              <!-- Mobile Filter Button -->
              <button @click="isFilterOpen = true"
                class="lg:hidden flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filters
              </button>

              <!-- View Mode Toggle -->
              <div class="hidden sm:flex items-center border border-gray-300 rounded-lg overflow-hidden">
                <button @click="viewMode = 'grid'" class="p-2 transition-colors"
                  :class="viewMode === 'grid' ? 'bg-primary-100 text-primary-700' : 'bg-white text-gray-500 hover:bg-gray-50'">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                  </svg>
                </button>
                <button @click="viewMode = 'list'" class="p-2 transition-colors"
                  :class="viewMode === 'list' ? 'bg-primary-100 text-primary-700' : 'bg-white text-gray-500 hover:bg-gray-50'">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Active Filters -->
          <div v-if="filters.category || filters.search || filters.minPrice || filters.maxPrice || filters.inStock"
            class="flex flex-wrap gap-2 mb-6">
            <span v-if="filters.category"
              class="inline-flex items-center gap-1 px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-sm">
              Category: {{ filters.category }}
              <button @click="updateFilters({ ...filters, category: null })" class="ml-1 hover:text-primary-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </span>

            <span v-if="filters.search"
              class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
              Search: "{{ filters.search }}"
              <button @click="updateFilters({ ...filters, search: '' })" class="ml-1 hover:text-blue-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </span>

            <span v-if="filters.minPrice || filters.maxPrice"
              class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
              Price: ${{ filters.minPrice || 0 }} - ${{ filters.maxPrice || '∞' }}
              <button @click="updateFilters({ ...filters, minPrice: null, maxPrice: null })"
                class="ml-1 hover:text-green-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </span>

            <span v-if="filters.inStock"
              class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-sm">
              In Stock Only
              <button @click="updateFilters({ ...filters, inStock: null })" class="ml-1 hover:text-emerald-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </span>

            <button @click="productsStore.resetFilters(); router.push({ query: {} })"
              class="text-sm text-gray-600 hover:text-gray-900 underline">
              Clear all
            </button>
          </div>

          <!-- Loading State -->
          <div v-if="isLoading" class="grid gap-6"
            :class="viewMode === 'grid' ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' : 'grid-cols-1'">
            <div v-for="n in 6" :key="n"
              class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden animate-pulse">
              <div class="aspect-square bg-gray-200"></div>
              <div class="p-4 space-y-3">
                <div class="h-4 bg-gray-200 rounded w-1/4"></div>
                <div class="h-5 bg-gray-200 rounded w-3/4"></div>
                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                <div class="h-10 bg-gray-200 rounded"></div>
              </div>
            </div>
          </div>

          <!-- Products Grid -->
          <div v-else-if="hasProducts" class="grid gap-6"
            :class="viewMode === 'grid' ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' : 'grid-cols-1'">
            <ProductCard v-for="product in products" :key="product.id" :product="product" @quick-view="openQuickView" />
          </div>

          <!-- No Products -->
          <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No products found</h3>
            <p class="text-gray-600 mb-6">
              Try adjusting your filters or search terms.
            </p>
            <button @click="productsStore.resetFilters(); router.push({ query: {} })"
              class="bg-primary-700 text-white py-2 px-6 rounded-lg font-medium hover:bg-primary-800 transition-colors">
              Clear Filters
            </button>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.lastPage > 1 && !isLoading" class="flex items-center justify-center gap-2 mt-8">
            <button @click="handlePageChange(pagination.currentPage - 1)" :disabled="pagination.currentPage === 1"
              class="p-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <template v-for="page in pagination.lastPage" :key="page">
              <button v-if="page === 1 || page === pagination.lastPage || Math.abs(page - pagination.currentPage) <= 1"
                @click="handlePageChange(page)" class="w-10 h-10 rounded-lg font-medium transition-colors" :class="[
                  page === pagination.currentPage
                    ? 'bg-primary-700 text-white'
                    : 'border border-gray-300 text-gray-700 hover:bg-gray-50'
                ]">
                {{ page }}
              </button>
              <span v-else-if="page === 2 && pagination.currentPage > 3" class="px-2 text-gray-400">
                ...
              </span>
              <span v-else-if="page === pagination.lastPage - 1 && pagination.currentPage < pagination.lastPage - 2"
                class="px-2 text-gray-400">
                ...
              </span>
            </template>

            <button @click="handlePageChange(pagination.currentPage + 1)"
              :disabled="pagination.currentPage === pagination.lastPage"
              class="p-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </main>
      </div>
    </div>

    <!-- Mobile Filters Panel (only renders when isFilterOpen is true) -->
    <ProductFilters v-if="isFilterOpen" :filters="filters" :is-open="isFilterOpen" @update="updateFilters"
      @close="isFilterOpen = false" />

    <!-- Quick View Modal -->
    <ProductQuickView :product="quickViewProduct" :is-open="isQuickViewOpen" @close="closeQuickView" />
  </div>
</template>
