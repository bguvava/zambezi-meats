<script setup>
/**
 * StockCheckPage.vue (Staff)
 * Staff stock check page for inventory management and stock count updates
 */
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useStaffStockStore } from '@/stores/staffStock'
import {
  Package,
  Search,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  AlertTriangle,
  CheckCircle,
  XCircle,
  Edit3,
  Save,
  X,
  Filter,
  Boxes
} from 'lucide-vue-next'

const stockStore = useStaffStockStore()

// Local state
const searchQuery = ref('')
const selectedStockStatus = ref('')
const showEditModal = ref(false)
const editingProduct = ref(null)
const newQuantity = ref(0)
const updateNotes = ref('')
const isSaving = ref(false)

// Stock status filter options
const stockStatusOptions = [
  { value: '', label: 'All Products' },
  { value: 'low', label: 'Low Stock' },
  { value: 'out', label: 'Out of Stock' },
  { value: 'normal', label: 'In Stock' }
]

// Computed
const isLoading = computed(() => stockStore.isLoading)
const products = computed(() => stockStore.filteredProducts)
const stockStats = computed(() => stockStore.stockStats)

// Lifecycle
onMounted(async () => {
  await fetchStock()
})

// Watch for filter changes
watch([searchQuery, selectedStockStatus], () => {
  stockStore.setFilters({
    search: searchQuery.value,
    stockStatus: selectedStockStatus.value
  })
})

// Methods
async function fetchStock() {
  try {
    await stockStore.fetchStockCheck()
  } catch (err) {
    console.error('Failed to fetch stock:', err)
  }
}

function openEditModal(product) {
  editingProduct.value = { ...product }
  newQuantity.value = product.stock_quantity || 0
  updateNotes.value = ''
  showEditModal.value = true
}

function closeEditModal() {
  showEditModal.value = false
  editingProduct.value = null
  newQuantity.value = 0
  updateNotes.value = ''
}

async function saveStock() {
  if (!editingProduct.value) return
  
  isSaving.value = true
  try {
    await stockStore.updateStock(
      editingProduct.value.id,
      newQuantity.value,
      updateNotes.value || null
    )
    closeEditModal()
  } catch (err) {
    console.error('Failed to update stock:', err)
  } finally {
    isSaving.value = false
  }
}

function getStockStatus(product) {
  const qty = product.stock_quantity || 0
  const threshold = product.low_stock_threshold || 10
  
  if (qty <= 0) {
    return { label: 'Out of Stock', bgColor: 'bg-red-100', textColor: 'text-red-800', icon: XCircle }
  } else if (qty <= threshold) {
    return { label: 'Low Stock', bgColor: 'bg-yellow-100', textColor: 'text-yellow-800', icon: AlertTriangle }
  }
  return { label: 'In Stock', bgColor: 'bg-green-100', textColor: 'text-green-800', icon: CheckCircle }
}

function goToPage(page) {
  stockStore.setPage(page)
  fetchStock()
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <nav class="text-sm mb-4">
          <RouterLink to="/staff" class="text-gray-500 hover:text-[#CF0D0F]">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">Stock Check</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Stock Check</h1>
            <p class="text-gray-600 mt-1">View and update product stock levels</p>
          </div>
          <button @click="fetchStock" 
            class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors"
            :disabled="isLoading">
            <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" />
            Refresh
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Products</p>
              <p class="text-2xl font-bold text-gray-900">{{ stockStats.total }}</p>
            </div>
            <Boxes class="w-8 h-8 text-[#CF0D0F]" />
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">In Stock</p>
              <p class="text-2xl font-bold text-green-600">{{ stockStats.normal }}</p>
            </div>
            <CheckCircle class="w-8 h-8 text-green-600" />
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Low Stock</p>
              <p class="text-2xl font-bold text-yellow-600">{{ stockStats.lowStock }}</p>
            </div>
            <AlertTriangle class="w-8 h-8 text-yellow-600" />
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Out of Stock</p>
              <p class="text-2xl font-bold text-red-600">{{ stockStats.outOfStock }}</p>
            </div>
            <XCircle class="w-8 h-8 text-red-600" />
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
          <div class="flex-1 min-w-[200px] relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search by product name or SKU..." 
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
            />
          </div>
          <select 
            v-model="selectedStockStatus"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
            <option v-for="option in stockStatusOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-16">
        <div class="text-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent"></div>
          <p class="mt-4 text-gray-600">Loading stock...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="stockStore.error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
          <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading stock</h3>
            <p class="text-red-600 text-sm mt-1">{{ stockStore.error }}</p>
          </div>
        </div>
        <button @click="fetchStock" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
          Try Again
        </button>
      </div>

      <!-- Stock Table -->
      <div v-else class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Threshold</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="!products.length">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Package class="w-8 h-8 text-gray-400" />
                  </div>
                  <p class="text-gray-500 mb-2">No products found</p>
                  <p class="text-sm text-gray-400">Products matching your filters will appear here</p>
                </td>
              </tr>
              <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                      <Package class="w-5 h-5 text-gray-500" />
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">{{ product.name }}</p>
                      <p class="text-sm text-gray-500">{{ product.category?.name || '-' }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                  {{ product.sku || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span :class="[
                    'text-xl font-bold',
                    product.stock_quantity <= 0 ? 'text-red-600' : 
                    product.stock_quantity <= (product.low_stock_threshold || 10) ? 'text-yellow-600' : 'text-gray-900'
                  ]">
                    {{ product.stock_quantity || 0 }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                  {{ product.low_stock_threshold || 10 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span :class="[
                    'inline-flex items-center px-2 py-1 text-xs font-medium rounded-full',
                    getStockStatus(product).bgColor,
                    getStockStatus(product).textColor
                  ]">
                    <component :is="getStockStatus(product).icon" class="w-3 h-3 mr-1" />
                    {{ getStockStatus(product).label }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <button 
                    @click="openEditModal(product)"
                    class="inline-flex items-center px-3 py-1.5 bg-[#CF0D0F] text-white text-sm rounded-lg hover:bg-[#B00B0D] transition-colors">
                    <Edit3 class="w-4 h-4 mr-1" />
                    Update
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="stockStore.pagination.totalPages > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Showing page {{ stockStore.pagination.currentPage }} of {{ stockStore.pagination.totalPages }}
          </p>
          <div class="flex items-center space-x-2">
            <button 
              @click="goToPage(stockStore.pagination.currentPage - 1)"
              :disabled="stockStore.pagination.currentPage === 1"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <ChevronLeft class="w-4 h-4" />
            </button>
            <button 
              @click="goToPage(stockStore.pagination.currentPage + 1)"
              :disabled="stockStore.pagination.currentPage === stockStore.pagination.totalPages"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Stock Modal -->
    <Teleport to="body">
      <div v-if="showEditModal && editingProduct" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeEditModal"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Update Stock</h3>
              <button @click="closeEditModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                <X class="w-5 h-5" />
              </button>
            </div>

            <div class="px-6 py-4 space-y-4">
              <!-- Product Info -->
              <div class="bg-gray-50 rounded-lg p-4">
                <p class="font-medium text-gray-900">{{ editingProduct.name }}</p>
                <p class="text-sm text-gray-500">SKU: {{ editingProduct.sku || '-' }}</p>
                <p class="text-sm text-gray-500 mt-1">
                  Current Stock: <span class="font-medium">{{ editingProduct.stock_quantity || 0 }}</span>
                </p>
              </div>

              <!-- New Quantity -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Stock Quantity *</label>
                <input 
                  v-model.number="newQuantity"
                  type="number" 
                  min="0"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                />
              </div>

              <!-- Notes -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                <textarea 
                  v-model="updateNotes"
                  rows="3"
                  placeholder="Reason for stock adjustment..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                ></textarea>
              </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button @click="closeEditModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="saveStock"
                :disabled="isSaving"
                class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50">
                <Save class="w-4 h-4 mr-2" />
                <span v-if="isSaving">Saving...</span>
                <span v-else>Save Changes</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
