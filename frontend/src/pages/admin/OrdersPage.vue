<script setup>
/**
 * Admin Orders Page
 * Full order management for administrators - view, filter, assign, refund
 *
 * @requirement ADMIN-009 View all orders
 * @requirement ADMIN-010 Filter and search orders
 * @requirement ADMIN-011 View order details
 * @requirement ADMIN-012 Update order status
 * @requirement ADMIN-013 Assign orders to staff
 * @requirement ADMIN-014 Process refunds
 */
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useAdminOrdersStore } from '@/stores/adminOrders'
import api from '@/services/api'
import {
  ClipboardList,
  Search,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  Eye,
  CheckCircle,
  Clock,
  Package,
  Truck,
  X,
  DollarSign,
  UserCheck,
  AlertTriangle,
  User,
  MapPin,
  Phone,
  Mail,
  FileText,
  RotateCcw,
  Download
} from 'lucide-vue-next'

const ordersStore = useAdminOrdersStore()

// Local state
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedDate = ref('all')
const showOrderModal = ref(false)
const showAssignModal = ref(false)
const showRefundModal = ref(false)
const selectedStaffId = ref('')
const refundReason = ref('')
const refundAmount = ref(0)
const isProcessing = ref(false)

// Staff list (fetched from API)
const staffList = ref([])
const isLoadingStaff = ref(false)

// Status configuration
const statusConfig = {
  pending: { label: 'Pending', bgColor: 'bg-yellow-100', textColor: 'text-yellow-800', icon: Clock },
  confirmed: { label: 'Confirmed', bgColor: 'bg-blue-100', textColor: 'text-blue-800', icon: CheckCircle },
  processing: { label: 'Processing', bgColor: 'bg-indigo-100', textColor: 'text-indigo-800', icon: Package },
  ready_for_pickup: { label: 'Ready for Pickup', bgColor: 'bg-purple-100', textColor: 'text-purple-800', icon: Package },
  ready_for_delivery: { label: 'Ready for Delivery', bgColor: 'bg-purple-100', textColor: 'text-purple-800', icon: Truck },
  out_for_delivery: { label: 'Out for Delivery', bgColor: 'bg-cyan-100', textColor: 'text-cyan-800', icon: Truck },
  delivered: { label: 'Delivered', bgColor: 'bg-green-100', textColor: 'text-green-800', icon: CheckCircle },
  picked_up: { label: 'Picked Up', bgColor: 'bg-green-100', textColor: 'text-green-800', icon: CheckCircle },
  completed: { label: 'Completed', bgColor: 'bg-green-100', textColor: 'text-green-800', icon: CheckCircle },
  cancelled: { label: 'Cancelled', bgColor: 'bg-red-100', textColor: 'text-red-800', icon: X },
  refunded: { label: 'Refunded', bgColor: 'bg-gray-100', textColor: 'text-gray-800', icon: RotateCcw }
}

// Status flow for updates
const allStatuses = [
  { value: 'pending', label: 'Pending' },
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'processing', label: 'Processing' },
  { value: 'ready_for_pickup', label: 'Ready for Pickup' },
  { value: 'ready_for_delivery', label: 'Ready for Delivery' },
  { value: 'out_for_delivery', label: 'Out for Delivery' },
  { value: 'delivered', label: 'Delivered' },
  { value: 'picked_up', label: 'Picked Up' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' }
]

// Date filter options
const dateOptions = [
  { value: 'today', label: 'Today' },
  { value: 'yesterday', label: 'Yesterday' },
  { value: 'week', label: 'This Week' },
  { value: 'month', label: 'This Month' },
  { value: 'all', label: 'All Time' }
]

// Computed
const orders = computed(() => ordersStore.orders)
const isLoading = computed(() => ordersStore.isLoading)
const currentOrder = computed(() => ordersStore.currentOrder)
const pagination = computed(() => ordersStore.pagination)

// Order stats
const orderStats = computed(() => {
  const stats = {
    all: 0,
    pending: 0,
    processing: 0,
    completed: 0
  }
  // These would come from the API in a real implementation
  stats.all = pagination.value.total || 0
  return stats
})

// Lifecycle
onMounted(async () => {
  await fetchOrders()
  await fetchStaffList()
})

// Watch for filter changes
watch([searchQuery, selectedStatus, selectedDate], () => {
  ordersStore.setFilters({
    search: searchQuery.value,
    status: selectedStatus.value,
    date_range: selectedDate.value
  })
  fetchOrders()
}, { debounce: 300 })

// Methods
async function fetchStaffList() {
  isLoadingStaff.value = true
  try {
    const response = await api.get('/admin/users', {
      params: {
        role: 'staff',
        per_page: 100,
        status: 'active'
      }
    })
    staffList.value = response.data.data || response.data || []
  } catch (err) {
    console.error('Failed to fetch staff:', err)
    staffList.value = []
  } finally {
    isLoadingStaff.value = false
  }
}

async function fetchOrders() {
  try {
    await ordersStore.fetchOrders()
  } catch (err) {
    console.error('Failed to fetch orders:', err)
  }
}

async function viewOrder(order) {
  try {
    await ordersStore.fetchOrder(order.id)
    showOrderModal.value = true
  } catch (err) {
    console.error('Failed to fetch order details:', err)
  }
}

function closeOrderModal() {
  showOrderModal.value = false
}

async function updateOrderStatus(orderId, newStatus) {
  isProcessing.value = true
  try {
    await ordersStore.updateOrderStatus(orderId, newStatus)
    await fetchOrders()
    if (showOrderModal.value) {
      await ordersStore.fetchOrder(orderId)
    }
  } catch (err) {
    console.error('Failed to update status:', err)
  } finally {
    isProcessing.value = false
  }
}

function openAssignModal() {
  selectedStaffId.value = currentOrder.value?.assigned_staff_id || ''
  showAssignModal.value = true
}

async function assignToStaff() {
  if (!selectedStaffId.value || !currentOrder.value) return
  
  isProcessing.value = true
  try {
    await ordersStore.assignOrder(currentOrder.value.id, parseInt(selectedStaffId.value))
    showAssignModal.value = false
    await ordersStore.fetchOrder(currentOrder.value.id)
  } catch (err) {
    console.error('Failed to assign order:', err)
  } finally {
    isProcessing.value = false
  }
}

function openRefundModal() {
  refundReason.value = ''
  refundAmount.value = currentOrder.value?.total || 0
  showRefundModal.value = true
}

async function processRefund() {
  if (!refundReason.value.trim() || !currentOrder.value) return
  
  isProcessing.value = true
  try {
    await ordersStore.refundOrder(currentOrder.value.id, {
      reason: refundReason.value,
      amount: refundAmount.value
    })
    showRefundModal.value = false
    showOrderModal.value = false
    await fetchOrders()
  } catch (err) {
    console.error('Failed to process refund:', err)
  } finally {
    isProcessing.value = false
  }
}

function getStatusConfig(status) {
  return statusConfig[status] || { label: status, bgColor: 'bg-gray-100', textColor: 'text-gray-800', icon: Package }
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

function setStatusFilter(status) {
  selectedStatus.value = status
}

function goToPage(page) {
  ordersStore.setPage(page)
  fetchOrders()
}

function exportOrders() {
  // TODO: Implement CSV export
  console.log('Export orders')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <nav class="text-sm mb-4">
          <RouterLink to="/admin" class="text-gray-500 hover:text-[#CF0D0F]">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">All Orders</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Order Management</h1>
            <p class="text-gray-600 mt-1">View, manage, and process all customer orders</p>
          </div>
          <div class="flex items-center gap-3 mt-4 md:mt-0">
            <button @click="exportOrders" 
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
              <Download class="w-4 h-4 mr-2" />
              Export
            </button>
            <button @click="fetchOrders" 
              class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors"
              :disabled="isLoading">
              <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" />
              Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Status Tabs -->
      <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] mb-6 overflow-x-auto">
        <div class="flex border-b border-gray-200 min-w-max">
          <button 
            @click="setStatusFilter('')"
            :class="[
              'px-6 py-3 text-sm font-medium transition-colors whitespace-nowrap',
              selectedStatus === '' 
                ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F] bg-red-50' 
                : 'text-gray-500 hover:text-gray-700'
            ]">
            All Orders
          </button>
          <button 
            @click="setStatusFilter('pending')"
            :class="[
              'px-6 py-3 text-sm font-medium transition-colors whitespace-nowrap',
              selectedStatus === 'pending' 
                ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F] bg-red-50' 
                : 'text-gray-500 hover:text-gray-700'
            ]">
            Pending
          </button>
          <button 
            @click="setStatusFilter('processing')"
            :class="[
              'px-6 py-3 text-sm font-medium transition-colors whitespace-nowrap',
              selectedStatus === 'processing' 
                ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F] bg-red-50' 
                : 'text-gray-500 hover:text-gray-700'
            ]">
            Processing
          </button>
          <button 
            @click="setStatusFilter('completed')"
            :class="[
              'px-6 py-3 text-sm font-medium transition-colors whitespace-nowrap',
              selectedStatus === 'completed' 
                ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F] bg-red-50' 
                : 'text-gray-500 hover:text-gray-700'
            ]">
            Completed
          </button>
          <button 
            @click="setStatusFilter('cancelled')"
            :class="[
              'px-6 py-3 text-sm font-medium transition-colors whitespace-nowrap',
              selectedStatus === 'cancelled' 
                ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F] bg-red-50' 
                : 'text-gray-500 hover:text-gray-700'
            ]">
            Cancelled
          </button>
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
              placeholder="Search by order ID, customer name, or email..." 
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
            />
          </div>
          <select 
            v-model="selectedDate"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
            <option v-for="option in dateOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-16">
        <div class="text-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent"></div>
          <p class="mt-4 text-gray-600">Loading orders...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="ordersStore.error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
          <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading orders</h3>
            <p class="text-red-600 text-sm mt-1">{{ ordersStore.error }}</p>
          </div>
        </div>
        <button @click="fetchOrders" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
          Try Again
        </button>
      </div>

      <!-- Orders Table -->
      <div v-else class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="!orders.length">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <ClipboardList class="w-8 h-8 text-gray-400" />
                  </div>
                  <p class="text-gray-500 mb-2">No orders found</p>
                  <p class="text-sm text-gray-400">Orders matching your filters will appear here</p>
                </td>
              </tr>
              <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="font-medium text-gray-900">#{{ order.order_number || order.id }}</p>
                  <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-gray-900">{{ order.customer?.name || order.customer_name || 'Guest' }}</p>
                  <p class="text-sm text-gray-500">{{ order.customer?.email || order.email || '-' }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-gray-900">{{ order.items_count || order.items?.length || 0 }} items</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="font-medium text-gray-900">{{ formatCurrency(order.total) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                    getStatusConfig(order.status).bgColor,
                    getStatusConfig(order.status).textColor
                  ]">
                    {{ getStatusConfig(order.status).label }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="order.assigned_staff" class="text-gray-900">{{ order.assigned_staff.name }}</span>
                  <span v-else class="text-gray-400 italic">Unassigned</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <button 
                    @click="viewOrder(order)"
                    class="inline-flex items-center px-3 py-1.5 bg-[#CF0D0F] text-white text-sm rounded-lg hover:bg-[#B00B0D] transition-colors">
                    <Eye class="w-4 h-4 mr-1" />
                    View
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.lastPage > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Showing {{ ((pagination.currentPage - 1) * pagination.perPage) + 1 }} to {{ Math.min(pagination.currentPage * pagination.perPage, pagination.total) }} of {{ pagination.total }} orders
          </p>
          <div class="flex items-center space-x-2">
            <button 
              @click="goToPage(pagination.currentPage - 1)"
              :disabled="pagination.currentPage === 1"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <ChevronLeft class="w-4 h-4" />
            </button>
            <span class="px-3 py-1 text-sm text-gray-700">
              Page {{ pagination.currentPage }} of {{ pagination.lastPage }}
            </span>
            <button 
              @click="goToPage(pagination.currentPage + 1)"
              :disabled="pagination.currentPage === pagination.lastPage"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <Teleport to="body">
      <div v-if="showOrderModal && currentOrder" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeOrderModal"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-4xl sm:w-full">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">
                    Order #{{ currentOrder.order_number || currentOrder.id }}
                  </h3>
                  <p class="text-sm text-gray-500">{{ formatDate(currentOrder.created_at) }}</p>
                </div>
                <div class="flex items-center space-x-2">
                  <span :class="[
                    'inline-flex px-3 py-1 text-sm font-medium rounded-full',
                    getStatusConfig(currentOrder.status).bgColor,
                    getStatusConfig(currentOrder.status).textColor
                  ]">
                    {{ getStatusConfig(currentOrder.status).label }}
                  </span>
                  <button @click="closeOrderModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                    <X class="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Modal Content -->
            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer Info -->
                <div>
                  <h4 class="text-sm font-medium text-gray-500 uppercase mb-3">Customer Information</h4>
                  <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <div class="flex items-center">
                      <User class="w-4 h-4 text-gray-400 mr-2" />
                      <span class="text-gray-900">{{ currentOrder.customer?.name || currentOrder.customer_name || 'Guest' }}</span>
                    </div>
                    <div class="flex items-center">
                      <Mail class="w-4 h-4 text-gray-400 mr-2" />
                      <span class="text-gray-600">{{ currentOrder.customer?.email || currentOrder.email || '-' }}</span>
                    </div>
                    <div class="flex items-center">
                      <Phone class="w-4 h-4 text-gray-400 mr-2" />
                      <span class="text-gray-600">{{ currentOrder.customer?.phone || currentOrder.phone || '-' }}</span>
                    </div>
                    <div v-if="currentOrder.delivery_type === 'delivery'" class="flex items-start">
                      <MapPin class="w-4 h-4 text-gray-400 mr-2 mt-0.5" />
                      <span class="text-gray-600">{{ currentOrder.delivery_address || '-' }}</span>
                    </div>
                  </div>
                </div>

                <!-- Order Details -->
                <div>
                  <h4 class="text-sm font-medium text-gray-500 uppercase mb-3">Order Details</h4>
                  <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                      <span class="text-gray-500">Delivery Type:</span>
                      <span class="text-gray-900 font-medium capitalize">{{ currentOrder.delivery_type || 'Pickup' }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-500">Payment Status:</span>
                      <span class="text-green-600 font-medium">{{ currentOrder.payment_status || 'Paid' }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-500">Assigned Staff:</span>
                      <span v-if="currentOrder.assigned_staff" class="text-gray-900 font-medium">
                        {{ currentOrder.assigned_staff.name }}
                      </span>
                      <span v-else class="text-gray-400 italic">Unassigned</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Order Items -->
              <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-500 uppercase mb-3">Order Items</h4>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <tr v-for="item in (currentOrder.items || [])" :key="item.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                          <p class="font-medium text-gray-900">{{ item.product?.name || item.name }}</p>
                          <p class="text-sm text-gray-500">SKU: {{ item.product?.sku || item.sku || '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-900">{{ item.quantity }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ formatCurrency(item.price) }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ formatCurrency(item.price * item.quantity) }}</td>
                      </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                      <tr>
                        <td colspan="3" class="px-4 py-3 text-right font-medium text-gray-900">Subtotal:</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ formatCurrency(currentOrder.subtotal) }}</td>
                      </tr>
                      <tr v-if="currentOrder.discount">
                        <td colspan="3" class="px-4 py-3 text-right font-medium text-gray-900">Discount:</td>
                        <td class="px-4 py-3 text-right text-green-600">-{{ formatCurrency(currentOrder.discount) }}</td>
                      </tr>
                      <tr v-if="currentOrder.delivery_fee">
                        <td colspan="3" class="px-4 py-3 text-right font-medium text-gray-900">Delivery Fee:</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ formatCurrency(currentOrder.delivery_fee) }}</td>
                      </tr>
                      <tr>
                        <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-900">Total:</td>
                        <td class="px-4 py-3 text-right font-bold text-[#CF0D0F] text-lg">{{ formatCurrency(currentOrder.total) }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <!-- Status Update Section -->
              <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-500 uppercase mb-3">Update Status</h4>
                <div class="flex flex-wrap gap-2">
                  <button 
                    v-for="status in allStatuses"
                    :key="status.value"
                    @click="updateOrderStatus(currentOrder.id, status.value)"
                    :disabled="currentOrder.status === status.value || isProcessing"
                    :class="[
                      'px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                      currentOrder.status === status.value 
                        ? 'bg-[#CF0D0F] text-white cursor-default'
                        : 'border border-gray-300 text-gray-700 hover:bg-gray-100 disabled:opacity-50'
                    ]">
                    {{ status.label }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <button 
                  @click="openAssignModal"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                  <UserCheck class="w-4 h-4 mr-2" />
                  Assign Staff
                </button>
                <button 
                  v-if="currentOrder.status !== 'refunded' && currentOrder.status !== 'cancelled'"
                  @click="openRefundModal"
                  class="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-red-700 hover:bg-red-50">
                  <RotateCcw class="w-4 h-4 mr-2" />
                  Process Refund
                </button>
              </div>
              <button @click="closeOrderModal" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Assign Staff Modal -->
    <Teleport to="body">
      <div v-if="showAssignModal" class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showAssignModal = false"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Assign Staff Member</h3>
            </div>
            <div class="px-6 py-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Select Staff Member</label>
              <select 
                v-model="selectedStaffId"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
                <option value="">-- Select Staff --</option>
                <option v-for="staff in staffList" :key="staff.id" :value="staff.id">
                  {{ staff.name }}
                </option>
              </select>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button @click="showAssignModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="assignToStaff"
                :disabled="!selectedStaffId || isProcessing"
                class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50">
                Assign
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Refund Modal -->
    <Teleport to="body">
      <div v-if="showRefundModal" class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showRefundModal = false"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Process Refund</h3>
            </div>
            <div class="px-6 py-4 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Refund Amount</label>
                <div class="relative">
                  <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                  <input 
                    v-model.number="refundAmount"
                    type="number"
                    step="0.01"
                    :max="currentOrder?.total"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  />
                </div>
                <p class="text-sm text-gray-500 mt-1">Maximum: {{ formatCurrency(currentOrder?.total) }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Refund</label>
                <textarea 
                  v-model="refundReason"
                  rows="3"
                  placeholder="Enter refund reason..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                ></textarea>
              </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button @click="showRefundModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="processRefund"
                :disabled="!refundReason.trim() || refundAmount <= 0 || isProcessing"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                Process Refund
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
