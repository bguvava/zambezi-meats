<script setup>
/**
 * Admin Inventory Page
 * Stock and inventory management for administrators
 *
 * @requirement INV-001 Create inventory dashboard
 * @requirement INV-002 Display stock levels table
 * @requirement INV-003 Implement stock filtering
 * @requirement INV-004 Create stock receive form
 * @requirement INV-005 Create stock adjustment form
 * @requirement INV-008 Set minimum stock thresholds
 * @requirement INV-009 Create low stock alerts
 * @requirement INV-010 Create out of stock alerts
 * @requirement INV-011 Display inventory history
 * @requirement INV-012 View product inventory detail
 * @requirement INV-013 Create waste management section
 * @requirement INV-014 Review and approve waste logs
 * @requirement INV-015 Generate inventory report
 * @requirement INV-016 Export inventory data
 */
import { ref, computed, onMounted, watch } from 'vue'
import { useAdminInventoryStore } from '@/stores/adminInventory'
import { useAdminCategoriesStore } from '@/stores/adminCategories'
import {
  Package,
  Search,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  AlertTriangle,
  AlertCircle,
  Download,
  Plus,
  Minus,
  X,
  Check,
  TrendingUp,
  TrendingDown,
  Trash2,
  Clock,
  Filter,
  Eye,
  Edit,
  BarChart3,
  Archive
} from 'lucide-vue-next'

const inventoryStore = useAdminInventoryStore()
const categoriesStore = useAdminCategoriesStore()

// Active tab
const activeTab = ref('dashboard')
const tabs = [
  { id: 'dashboard', label: 'Dashboard', icon: BarChart3 },
  { id: 'stock', label: 'Stock Levels', icon: Package },
  { id: 'history', label: 'History', icon: Clock },
  { id: 'alerts', label: 'Alerts', icon: AlertTriangle },
  { id: 'waste', label: 'Waste', icon: Trash2 }
]

// Filters
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStatus = ref('')

// Modals
const showReceiveModal = ref(false)
const showAdjustModal = ref(false)
const showDetailModal = ref(false)
const showMinStockModal = ref(false)

// Form data
const receiveForm = ref({ product_id: '', quantity: 1, supplier: '', notes: '' })
const adjustForm = ref({ product_id: '', new_quantity: 0, reason: '', notes: '' })
const minStockForm = ref({ product_id: null, product_name: '', min_stock: 10 })
const selectedProduct = ref(null)

// Computed
const dashboard = computed(() => inventoryStore.dashboard)
const inventory = computed(() => inventoryStore.inventory)
const pagination = computed(() => inventoryStore.pagination)
const history = computed(() => inventoryStore.history)
const historyPagination = computed(() => inventoryStore.historyPagination)
const alerts = computed(() => inventoryStore.alerts)
const alertsSummary = computed(() => inventoryStore.alertsSummary)
const wasteEntries = computed(() => inventoryStore.wasteEntries)
const wastePagination = computed(() => inventoryStore.wastePagination)
const wasteSummary = computed(() => inventoryStore.wasteSummary)
const categories = computed(() => categoriesStore.categories)
const isLoading = computed(() => inventoryStore.isLoading)
const isDashboardLoading = computed(() => inventoryStore.isDashboardLoading)
const isHistoryLoading = computed(() => inventoryStore.isHistoryLoading)
const isWasteLoading = computed(() => inventoryStore.isWasteLoading)

// Status options
const statusOptions = [
  { value: '', label: 'All Status' },
  { value: 'normal', label: 'Normal' },
  { value: 'low', label: 'Low Stock' },
  { value: 'out', label: 'Out of Stock' }
]

// History type options
const historyTypeOptions = [
  { value: '', label: 'All Types' },
  { value: 'addition', label: 'Addition' },
  { value: 'deduction', label: 'Deduction' },
  { value: 'adjustment', label: 'Adjustment' }
]

// Waste reason options
const wasteReasonOptions = [
  { value: '', label: 'All Reasons' },
  { value: 'expired', label: 'Expired' },
  { value: 'damaged', label: 'Damaged' },
  { value: 'spoiled', label: 'Spoiled' },
  { value: 'quality', label: 'Quality Issue' },
  { value: 'other', label: 'Other' }
]

// Adjustment reasons
const adjustmentReasons = [
  'Physical count correction',
  'Damage/spoilage',
  'System error correction',
  'Inventory audit',
  'Return to supplier',
  'Sample/testing',
  'Other'
]

// Lifecycle
onMounted(async () => {
  await Promise.all([
    inventoryStore.fetchDashboard(),
    inventoryStore.fetchInventory(),
    inventoryStore.fetchAlerts(),
    categoriesStore.fetchCategories()
  ])
})

// Watch filters
watch([searchQuery, selectedCategory, selectedStatus], () => {
  inventoryStore.setFilters({
    search: searchQuery.value,
    category_id: selectedCategory.value || null,
    status: selectedStatus.value || null
  })
  inventoryStore.fetchInventory()
}, { debounce: 300 })

// Tab change handlers
watch(activeTab, async (newTab) => {
  if (newTab === 'history' && history.value.length === 0) {
    await inventoryStore.fetchHistory()
  }
  if (newTab === 'waste' && wasteEntries.value.length === 0) {
    await inventoryStore.fetchWaste()
  }
})

// Methods
async function refreshData() {
  if (activeTab.value === 'dashboard') {
    await inventoryStore.fetchDashboard()
  } else if (activeTab.value === 'stock') {
    await inventoryStore.fetchInventory()
  } else if (activeTab.value === 'history') {
    await inventoryStore.fetchHistory()
  } else if (activeTab.value === 'alerts') {
    await inventoryStore.fetchAlerts()
  } else if (activeTab.value === 'waste') {
    await inventoryStore.fetchWaste()
  }
}

function changePage(page) {
  if (activeTab.value === 'stock') {
    inventoryStore.fetchInventory(page)
  } else if (activeTab.value === 'history') {
    inventoryStore.fetchHistory(page)
  } else if (activeTab.value === 'waste') {
    inventoryStore.fetchWaste(page)
  }
}

// Receive Stock Modal
function openReceiveModal(product = null) {
  if (product) {
    receiveForm.value.product_id = product.id
  } else {
    receiveForm.value.product_id = ''
  }
  receiveForm.value.quantity = 1
  receiveForm.value.supplier = ''
  receiveForm.value.notes = ''
  showReceiveModal.value = true
}

async function submitReceiveStock() {
  if (!receiveForm.value.product_id || receiveForm.value.quantity < 1) return
  
  try {
    await inventoryStore.receiveStock({
      product_id: parseInt(receiveForm.value.product_id),
      quantity: parseInt(receiveForm.value.quantity),
      supplier: receiveForm.value.supplier || null,
      notes: receiveForm.value.notes || null
    })
    showReceiveModal.value = false
    await inventoryStore.fetchInventory()
  } catch (err) {
    console.error('Failed to receive stock:', err)
  }
}

// Adjust Stock Modal
function openAdjustModal(product) {
  adjustForm.value.product_id = product.id
  adjustForm.value.new_quantity = product.stock
  adjustForm.value.reason = ''
  adjustForm.value.notes = ''
  selectedProduct.value = product
  showAdjustModal.value = true
}

async function submitAdjustStock() {
  if (!adjustForm.value.product_id || !adjustForm.value.reason) return
  
  try {
    await inventoryStore.adjustStock(
      adjustForm.value.product_id,
      parseInt(adjustForm.value.new_quantity),
      adjustForm.value.reason,
      adjustForm.value.notes || null
    )
    showAdjustModal.value = false
    await inventoryStore.fetchInventory()
  } catch (err) {
    console.error('Failed to adjust stock:', err)
  }
}

// Min Stock Modal
function openMinStockModal(product) {
  minStockForm.value.product_id = product.id
  minStockForm.value.product_name = product.name
  minStockForm.value.min_stock = product.meta?.min_stock || 10
  showMinStockModal.value = true
}

async function submitMinStock() {
  if (!minStockForm.value.product_id) return
  
  try {
    await inventoryStore.updateMinStock(
      minStockForm.value.product_id,
      parseInt(minStockForm.value.min_stock)
    )
    showMinStockModal.value = false
    await inventoryStore.fetchInventory()
  } catch (err) {
    console.error('Failed to update min stock:', err)
  }
}

// Product Detail Modal
async function viewProductDetail(product) {
  try {
    await inventoryStore.fetchProductInventory(product.id)
    selectedProduct.value = inventoryStore.currentProduct
    showDetailModal.value = true
  } catch (err) {
    console.error('Failed to fetch product detail:', err)
  }
}

// Waste approval
async function approveWasteEntry(wasteId) {
  try {
    await inventoryStore.approveWaste(wasteId, true)
  } catch (err) {
    console.error('Failed to approve waste:', err)
  }
}

async function rejectWasteEntry(wasteId) {
  try {
    await inventoryStore.approveWaste(wasteId, false, 'Rejected by admin')
  } catch (err) {
    console.error('Failed to reject waste:', err)
  }
}

// Export
async function exportInventory() {
  try {
    const data = await inventoryStore.exportInventory()
    // Create downloadable JSON for now
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `inventory-export-${new Date().toISOString().split('T')[0]}.json`
    a.click()
    URL.revokeObjectURL(url)
  } catch (err) {
    console.error('Failed to export:', err)
  }
}

// Helpers
function getStockStatusClass(item) {
  const stock = item.stock
  const minStock = item.meta?.min_stock || 10
  if (stock <= 0) return 'bg-red-100 text-red-800'
  if (stock <= minStock) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

function getStockStatusLabel(item) {
  const stock = item.stock
  const minStock = item.meta?.min_stock || 10
  if (stock <= 0) return 'Out of Stock'
  if (stock <= minStock) return 'Low Stock'
  return 'In Stock'
}

function getMovementTypeClass(type) {
  switch (type) {
    case 'addition': return 'bg-green-100 text-green-800'
    case 'deduction': return 'bg-red-100 text-red-800'
    case 'adjustment': return 'bg-blue-100 text-blue-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('en-AU', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-AU', {
    style: 'currency',
    currency: 'AUD'
  }).format(amount || 0)
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-secondary-900">Inventory Management</h1>
      <div class="flex items-center gap-3">
        <button
          @click="exportInventory"
          class="flex items-center gap-2 px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          <Download class="w-4 h-4" />
          Export
        </button>
        <button
          @click="openReceiveModal()"
          class="flex items-center gap-2 px-4 py-2 text-white bg-primary-600 rounded-lg hover:bg-primary-700"
        >
          <Plus class="w-4 h-4" />
          Receive Stock
        </button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
      <nav class="flex gap-4">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 -mb-px transition-colors',
            activeTab === tab.id
              ? 'border-primary-600 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          <component :is="tab.icon" class="w-4 h-4" />
          {{ tab.label }}
          <span
            v-if="tab.id === 'alerts' && alertsSummary.total_alerts > 0"
            class="ml-1 px-2 py-0.5 text-xs font-medium bg-red-100 text-red-800 rounded-full"
          >
            {{ alertsSummary.total_alerts }}
          </span>
        </button>
      </nav>
    </div>

    <!-- Dashboard Tab -->
    <div v-if="activeTab === 'dashboard'" class="space-y-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Products</p>
              <p class="text-2xl font-bold text-gray-900">{{ dashboard.total_products }}</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg">
              <Package class="w-6 h-6 text-blue-600" />
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Low Stock</p>
              <p class="text-2xl font-bold text-yellow-600">{{ dashboard.low_stock_count }}</p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-lg">
              <AlertTriangle class="w-6 h-6 text-yellow-600" />
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Out of Stock</p>
              <p class="text-2xl font-bold text-red-600">{{ dashboard.out_of_stock_count }}</p>
            </div>
            <div class="p-3 bg-red-100 rounded-lg">
              <AlertCircle class="w-6 h-6 text-red-600" />
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Waste This Month</p>
              <p class="text-2xl font-bold text-gray-900">{{ dashboard.waste_this_month?.quantity || 0 }}</p>
              <p class="text-xs text-gray-500">{{ formatCurrency(dashboard.waste_this_month?.value) }}</p>
            </div>
            <div class="p-3 bg-gray-100 rounded-lg">
              <Trash2 class="w-6 h-6 text-gray-600" />
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Movements -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-gray-900">Recent Stock Movements</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock After</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="movement in dashboard.recent_movements" :key="movement.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(movement.date) }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ movement.product }}</td>
                <td class="px-6 py-4">
                  <span :class="['px-2 py-1 text-xs font-medium rounded-full', getMovementTypeClass(movement.type)]">
                    {{ movement.type }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm">
                  <span :class="movement.type === 'deduction' ? 'text-red-600' : 'text-green-600'">
                    {{ movement.type === 'deduction' ? '-' : '+' }}{{ movement.quantity }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ movement.stock_after }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ movement.user }}</td>
              </tr>
              <tr v-if="!dashboard.recent_movements?.length">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No recent movements</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Stock Levels Tab -->
    <div v-if="activeTab === 'stock'" class="space-y-4">
      <!-- Filters -->
      <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <div class="flex flex-wrap items-center gap-4">
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search products..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              />
            </div>
          </div>
          <select
            v-model="selectedCategory"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
          <select
            v-model="selectedStatus"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
          >
            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
          </select>
          <button @click="refreshData" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
            <RefreshCw class="w-5 h-5" :class="{ 'animate-spin': isLoading }" />
          </button>
        </div>
      </div>

      <!-- Stock Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="item in inventory" :key="item.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="font-medium text-gray-900">{{ item.name }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ item.sku || '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ item.category?.name || '-' }}</td>
                <td class="px-6 py-4">
                  <span class="font-medium">{{ item.stock }}</span>
                  <span class="text-gray-500 text-sm ml-1">{{ item.unit }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ item.meta?.min_stock || 10 }}</td>
                <td class="px-6 py-4">
                  <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStockStatusClass(item)]">
                    {{ getStockStatusLabel(item) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <button @click="viewProductDetail(item)" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded" title="View Details">
                      <Eye class="w-4 h-4" />
                    </button>
                    <button @click="openReceiveModal(item)" class="p-1.5 text-green-600 hover:bg-green-50 rounded" title="Receive Stock">
                      <Plus class="w-4 h-4" />
                    </button>
                    <button @click="openAdjustModal(item)" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded" title="Adjust Stock">
                      <Edit class="w-4 h-4" />
                    </button>
                    <button @click="openMinStockModal(item)" class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded" title="Set Min Stock">
                      <AlertTriangle class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!inventory.length && !isLoading">
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">No inventory items found</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.lastPage > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
          <p class="text-sm text-gray-600">
            Showing {{ (pagination.currentPage - 1) * pagination.perPage + 1 }} to 
            {{ Math.min(pagination.currentPage * pagination.perPage, pagination.total) }} of {{ pagination.total }}
          </p>
          <div class="flex items-center gap-2">
            <button
              @click="changePage(pagination.currentPage - 1)"
              :disabled="pagination.currentPage === 1"
              class="p-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
            >
              <ChevronLeft class="w-4 h-4" />
            </button>
            <span class="px-3 py-1 text-sm">{{ pagination.currentPage }} / {{ pagination.lastPage }}</span>
            <button
              @click="changePage(pagination.currentPage + 1)"
              :disabled="pagination.currentPage === pagination.lastPage"
              class="p-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
            >
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- History Tab -->
    <div v-if="activeTab === 'history'" class="space-y-4">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Before</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">After</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="log in history" :key="log.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(log.date) }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ log.product?.name || 'Unknown' }}</td>
                <td class="px-6 py-4">
                  <span :class="['px-2 py-1 text-xs font-medium rounded-full', getMovementTypeClass(log.type)]">
                    {{ log.type }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm font-medium">{{ log.quantity }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ log.stock_before }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ log.stock_after }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ log.reason || '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ log.user }}</td>
              </tr>
              <tr v-if="!history.length && !isHistoryLoading">
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">No history records</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="historyPagination.lastPage > 1" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
          <p class="text-sm text-gray-600">Page {{ historyPagination.currentPage }} of {{ historyPagination.lastPage }}</p>
          <div class="flex items-center gap-2">
            <button
              @click="changePage(historyPagination.currentPage - 1)"
              :disabled="historyPagination.currentPage === 1"
              class="p-2 rounded-lg border border-gray-300 disabled:opacity-50 hover:bg-gray-50"
            >
              <ChevronLeft class="w-4 h-4" />
            </button>
            <button
              @click="changePage(historyPagination.currentPage + 1)"
              :disabled="historyPagination.currentPage === historyPagination.lastPage"
              class="p-2 rounded-lg border border-gray-300 disabled:opacity-50 hover:bg-gray-50"
            >
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Alerts Tab -->
    <div v-if="activeTab === 'alerts'" class="space-y-6">
      <!-- Low Stock Alerts -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-yellow-700 flex items-center gap-2">
            <AlertTriangle class="w-5 h-5" />
            Low Stock ({{ alerts.low_stock?.length || 0 }})
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-yellow-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase">Current Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase">Min Stock</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="item in alerts.low_stock" :key="item.id" class="hover:bg-yellow-50">
                <td class="px-6 py-4 font-medium text-gray-900">{{ item.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ item.category || '-' }}</td>
                <td class="px-6 py-4 text-sm font-medium text-yellow-700">{{ item.stock }} {{ item.unit }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ item.min_stock }}</td>
                <td class="px-6 py-4">
                  <button @click="openReceiveModal(item)" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                    Receive Stock
                  </button>
                </td>
              </tr>
              <tr v-if="!alerts.low_stock?.length">
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">No low stock alerts</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Out of Stock Alerts -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-red-700 flex items-center gap-2">
            <AlertCircle class="w-5 h-5" />
            Out of Stock ({{ alerts.out_of_stock?.length || 0 }})
          </h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-red-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase">SKU</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-red-700 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="item in alerts.out_of_stock" :key="item.id" class="hover:bg-red-50">
                <td class="px-6 py-4 font-medium text-gray-900">{{ item.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ item.sku || '-' }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ item.category || '-' }}</td>
                <td class="px-6 py-4">
                  <button @click="openReceiveModal(item)" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                    Receive Stock
                  </button>
                </td>
              </tr>
              <tr v-if="!alerts.out_of_stock?.length">
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">No out of stock alerts</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Waste Tab -->
    <div v-if="activeTab === 'waste'" class="space-y-4">
      <!-- Waste Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
          <p class="text-sm text-gray-500">Total Entries</p>
          <p class="text-xl font-bold text-gray-900">{{ wasteSummary.total_entries }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
          <p class="text-sm text-gray-500">Total Quantity Wasted</p>
          <p class="text-xl font-bold text-red-600">{{ wasteSummary.total_quantity }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
          <p class="text-sm text-gray-500">Total Value Lost</p>
          <p class="text-xl font-bold text-red-600">{{ formatCurrency(wasteSummary.total_value) }}</p>
        </div>
      </div>

      <!-- Waste Table -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Logged By</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="waste in wasteEntries" :key="waste.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(waste.date) }}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ waste.product?.name || 'Unknown' }}</td>
                <td class="px-6 py-4 text-sm text-red-600 font-medium">{{ waste.quantity }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ formatCurrency(waste.total_cost) }}</td>
                <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ waste.reason }}</td>
                <td class="px-6 py-4 text-sm text-gray-600">{{ waste.logged_by }}</td>
                <td class="px-6 py-4">
                  <span :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    waste.approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                  ]">
                    {{ waste.approved ? 'Approved' : 'Pending' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div v-if="!waste.approved" class="flex items-center gap-2">
                    <button @click="approveWasteEntry(waste.id)" class="p-1.5 text-green-600 hover:bg-green-50 rounded" title="Approve">
                      <Check class="w-4 h-4" />
                    </button>
                    <button @click="rejectWasteEntry(waste.id)" class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Reject">
                      <X class="w-4 h-4" />
                    </button>
                  </div>
                  <span v-else class="text-xs text-gray-400">-</span>
                </td>
              </tr>
              <tr v-if="!wasteEntries.length && !isWasteLoading">
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">No waste entries found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Receive Stock Modal -->
    <Teleport to="body">
      <div v-if="showReceiveModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Receive Stock</h3>
            <button @click="showReceiveModal = false" class="p-1 text-gray-400 hover:text-gray-600">
              <X class="w-5 h-5" />
            </button>
          </div>
          <form @submit.prevent="submitReceiveStock" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
              <select v-model="receiveForm.product_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">Select product...</option>
                <option v-for="item in inventory" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
              <input v-model.number="receiveForm.quantity" type="number" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Supplier (optional)</label>
              <input v-model="receiveForm.supplier" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
              <textarea v-model="receiveForm.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4">
              <button type="button" @click="showReceiveModal = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
              <button type="submit" :disabled="isLoading" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
                {{ isLoading ? 'Saving...' : 'Receive Stock' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Adjust Stock Modal -->
    <Teleport to="body">
      <div v-if="showAdjustModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Adjust Stock</h3>
            <button @click="showAdjustModal = false" class="p-1 text-gray-400 hover:text-gray-600">
              <X class="w-5 h-5" />
            </button>
          </div>
          <form @submit.prevent="submitAdjustStock" class="p-6 space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-600">Product: <span class="font-medium text-gray-900">{{ selectedProduct?.name }}</span></p>
              <p class="text-sm text-gray-600">Current Stock: <span class="font-medium text-gray-900">{{ selectedProduct?.stock }}</span></p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Quantity</label>
              <input v-model.number="adjustForm.new_quantity" type="number" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
              <select v-model="adjustForm.reason" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                <option value="">Select reason...</option>
                <option v-for="reason in adjustmentReasons" :key="reason" :value="reason">{{ reason }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
              <textarea v-model="adjustForm.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4">
              <button type="button" @click="showAdjustModal = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
              <button type="submit" :disabled="isLoading" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                {{ isLoading ? 'Saving...' : 'Adjust Stock' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Min Stock Modal -->
    <Teleport to="body">
      <div v-if="showMinStockModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Set Minimum Stock</h3>
            <button @click="showMinStockModal = false" class="p-1 text-gray-400 hover:text-gray-600">
              <X class="w-5 h-5" />
            </button>
          </div>
          <form @submit.prevent="submitMinStock" class="p-6 space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-sm text-gray-600">Product: <span class="font-medium text-gray-900">{{ minStockForm.product_name }}</span></p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock Threshold</label>
              <input v-model.number="minStockForm.min_stock" type="number" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500" />
              <p class="text-xs text-gray-500 mt-1">Alert will trigger when stock falls below this level</p>
            </div>
            <div class="flex justify-end gap-3 pt-4">
              <button type="button" @click="showMinStockModal = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
              <button type="submit" :disabled="isLoading" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 disabled:opacity-50">
                {{ isLoading ? 'Saving...' : 'Update Threshold' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Product Detail Modal -->
    <Teleport to="body">
      <div v-if="showDetailModal && selectedProduct" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[80vh] overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">{{ selectedProduct.product?.name || 'Product Details' }}</h3>
            <button @click="showDetailModal = false" class="p-1 text-gray-400 hover:text-gray-600">
              <X class="w-5 h-5" />
            </button>
          </div>
          <div class="p-6 overflow-y-auto max-h-[60vh]">
            <!-- Product Info -->
            <div class="grid grid-cols-2 gap-4 mb-6">
              <div>
                <p class="text-sm text-gray-500">Current Stock</p>
                <p class="text-xl font-bold text-gray-900">{{ selectedProduct.product?.stock }} {{ selectedProduct.product?.unit }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Min Stock Threshold</p>
                <p class="text-xl font-bold text-gray-900">{{ selectedProduct.product?.min_stock }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Category</p>
                <p class="text-lg font-medium text-gray-700">{{ selectedProduct.product?.category || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Status</p>
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  selectedProduct.product?.status === 'out' ? 'bg-red-100 text-red-800' :
                  selectedProduct.product?.status === 'low' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-green-100 text-green-800'
                ]">
                  {{ selectedProduct.product?.status === 'out' ? 'Out of Stock' : selectedProduct.product?.status === 'low' ? 'Low Stock' : 'In Stock' }}
                </span>
              </div>
            </div>

            <!-- Recent History -->
            <div>
              <h4 class="font-medium text-gray-900 mb-3">Recent History</h4>
              <div class="space-y-2">
                <div v-for="log in selectedProduct.history?.slice(0, 10)" :key="log.id" class="flex items-center justify-between py-2 border-b border-gray-100">
                  <div>
                    <span :class="['px-2 py-0.5 text-xs font-medium rounded', getMovementTypeClass(log.type)]">{{ log.type }}</span>
                    <span class="ml-2 text-sm text-gray-600">{{ log.reason }}</span>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-medium">{{ log.stock_before }} → {{ log.stock_after }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(log.date) }}</p>
                  </div>
                </div>
                <p v-if="!selectedProduct.history?.length" class="text-sm text-gray-500 text-center py-4">No history available</p>
              </div>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button @click="showDetailModal = false" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Close</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
