<script setup>
/**
 * WasteLogPage.vue (Staff)
 * Staff waste logging page for recording damaged/expired products
 */
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useStaffWasteStore } from '@/stores/staffWaste'
import { useStaffStockStore } from '@/stores/staffStock'
import {
  Trash2,
  Search,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  AlertTriangle,
  Plus,
  X,
  Calendar,
  Package,
  DollarSign,
  FileText
} from 'lucide-vue-next'

const wasteStore = useStaffWasteStore()
const stockStore = useStaffStockStore()

// Local state
const searchQuery = ref('')
const selectedReason = ref('')
const showLogModal = ref(false)
const isSubmitting = ref(false)

// Form state
const wasteForm = ref({
  product_id: null,
  quantity: 1,
  reason: 'expired',
  notes: ''
})

// Computed
const isLoading = computed(() => wasteStore.isLoading)
const wasteLogs = computed(() => wasteStore.wasteLogs)
const wasteStats = computed(() => wasteStore.wasteStats)
const wasteReasons = computed(() => wasteStore.wasteReasons)
const products = computed(() => stockStore.products)

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchWasteLogs(),
    stockStore.fetchStockCheck() // Get products for dropdown
  ])
})

// Watch for filter changes
watch([searchQuery, selectedReason], () => {
  wasteStore.setFilters({
    search: searchQuery.value,
    reason: selectedReason.value
  })
  fetchWasteLogs()
})

// Methods
async function fetchWasteLogs() {
  try {
    await wasteStore.fetchWasteLogs()
  } catch (err) {
    console.error('Failed to fetch waste logs:', err)
  }
}

function openLogModal() {
  wasteForm.value = {
    product_id: null,
    quantity: 1,
    reason: 'expired',
    notes: ''
  }
  showLogModal.value = true
}

function closeLogModal() {
  showLogModal.value = false
}

async function submitWasteLog() {
  if (!wasteForm.value.product_id || wasteForm.value.quantity < 1) return
  
  isSubmitting.value = true
  try {
    await wasteStore.logWaste(wasteForm.value)
    closeLogModal()
    await fetchWasteLogs()
  } catch (err) {
    console.error('Failed to log waste:', err)
  } finally {
    isSubmitting.value = false
  }
}

function getReasonLabel(reason) {
  const found = wasteReasons.value.find(r => r.value === reason)
  return found?.label || reason
}

function getReasonStyle(reason) {
  const styles = {
    expired: { bgColor: 'bg-orange-100', textColor: 'text-orange-800' },
    damaged: { bgColor: 'bg-red-100', textColor: 'text-red-800' },
    quality: { bgColor: 'bg-yellow-100', textColor: 'text-yellow-800' },
    spoiled: { bgColor: 'bg-purple-100', textColor: 'text-purple-800' },
    contaminated: { bgColor: 'bg-pink-100', textColor: 'text-pink-800' },
    other: { bgColor: 'bg-gray-100', textColor: 'text-gray-800' }
  }
  return styles[reason] || styles.other
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0)
}

function goToPage(page) {
  wasteStore.setPage(page)
  fetchWasteLogs()
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
          <span class="text-gray-900">Waste Log</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Waste Log</h1>
            <p class="text-gray-600 mt-1">Record damaged, expired, or spoiled products</p>
          </div>
          <div class="mt-4 md:mt-0 flex items-center space-x-3">
            <button @click="fetchWasteLogs" 
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
              :disabled="isLoading">
              <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" />
              Refresh
            </button>
            <button @click="openLogModal" 
              class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors">
              <Plus class="w-4 h-4 mr-2" />
              Log Waste
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Logged</p>
              <p class="text-2xl font-bold text-gray-900">{{ wasteStats.total }}</p>
            </div>
            <Trash2 class="w-8 h-8 text-[#CF0D0F]" />
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Today</p>
              <p class="text-2xl font-bold text-orange-600">{{ wasteStats.todayCount }}</p>
            </div>
            <Calendar class="w-8 h-8 text-orange-600" />
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 col-span-2">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Waste Value</p>
              <p class="text-2xl font-bold text-red-600">{{ formatCurrency(wasteStats.totalValue) }}</p>
            </div>
            <DollarSign class="w-8 h-8 text-red-600" />
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
              placeholder="Search by product name..." 
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
            />
          </div>
          <select 
            v-model="selectedReason"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
            <option value="">All Reasons</option>
            <option v-for="reason in wasteReasons" :key="reason.value" :value="reason.value">
              {{ reason.label }}
            </option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-16">
        <div class="text-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent"></div>
          <p class="mt-4 text-gray-600">Loading waste logs...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="wasteStore.error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
          <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading waste logs</h3>
            <p class="text-red-600 text-sm mt-1">{{ wasteStore.error }}</p>
          </div>
        </div>
        <button @click="fetchWasteLogs" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
          Try Again
        </button>
      </div>

      <!-- Waste Logs Table -->
      <div v-else class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logged By</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="!wasteLogs.length">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Trash2 class="w-8 h-8 text-gray-400" />
                  </div>
                  <p class="text-gray-500 mb-2">No waste logs found</p>
                  <p class="text-sm text-gray-400">Click "Log Waste" to record waste items</p>
                </td>
              </tr>
              <tr v-for="log in wasteLogs" :key="log.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(log.created_at) }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center mr-3">
                      <Package class="w-4 h-4 text-gray-500" />
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">{{ log.product?.name || 'Unknown Product' }}</p>
                      <p class="text-sm text-gray-500">SKU: {{ log.product?.sku || '-' }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span class="text-lg font-semibold text-gray-900">{{ log.quantity }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                    getReasonStyle(log.reason).bgColor,
                    getReasonStyle(log.reason).textColor
                  ]">
                    {{ getReasonLabel(log.reason) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-red-600 font-medium">
                  {{ formatCurrency(log.value) }}
                </td>
                <td class="px-6 py-4 max-w-xs">
                  <p class="text-sm text-gray-600 truncate" :title="log.notes">
                    {{ log.notes || '-' }}
                  </p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ log.logged_by?.name || log.user?.name || 'Staff' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="wasteStore.pagination.totalPages > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Showing page {{ wasteStore.pagination.currentPage }} of {{ wasteStore.pagination.totalPages }}
          </p>
          <div class="flex items-center space-x-2">
            <button 
              @click="goToPage(wasteStore.pagination.currentPage - 1)"
              :disabled="wasteStore.pagination.currentPage === 1"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <ChevronLeft class="w-4 h-4" />
            </button>
            <button 
              @click="goToPage(wasteStore.pagination.currentPage + 1)"
              :disabled="wasteStore.pagination.currentPage === wasteStore.pagination.totalPages"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Log Waste Modal -->
    <Teleport to="body">
      <div v-if="showLogModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeLogModal"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Log Waste Item</h3>
              <button @click="closeLogModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                <X class="w-5 h-5" />
              </button>
            </div>

            <div class="px-6 py-4 space-y-4">
              <!-- Product Select -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                <select 
                  v-model="wasteForm.product_id"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
                  <option :value="null">Select a product</option>
                  <option v-for="product in products" :key="product.id" :value="product.id">
                    {{ product.name }} ({{ product.sku || 'No SKU' }})
                  </option>
                </select>
              </div>

              <!-- Quantity -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                <input 
                  v-model.number="wasteForm.quantity"
                  type="number" 
                  min="1"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                />
              </div>

              <!-- Reason -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                <select 
                  v-model="wasteForm.reason"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
                  <option v-for="reason in wasteReasons" :key="reason.value" :value="reason.value">
                    {{ reason.label }}
                  </option>
                </select>
              </div>

              <!-- Notes -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                <textarea 
                  v-model="wasteForm.notes"
                  rows="3"
                  placeholder="Additional details about the waste..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                ></textarea>
              </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button @click="closeLogModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="submitWasteLog"
                :disabled="!wasteForm.product_id || wasteForm.quantity < 1 || isSubmitting"
                class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50">
                <FileText class="w-4 h-4 mr-2" />
                <span v-if="isSubmitting">Submitting...</span>
                <span v-else>Log Waste</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
