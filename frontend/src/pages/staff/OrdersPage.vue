<script setup>
/**
 * OrdersPage.vue (Staff)
 * Staff order queue management with filtering, status updates, and order details
 */
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useStaffOrdersStore } from '@/stores/staffOrders'
import {
  ClipboardList,
  Search,
  Filter,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  Eye,
  CheckCircle,
  Clock,
  Package,
  Truck,
  X,
  MessageSquare,
  FileText,
  AlertTriangle,
  Calendar,
  User,
  MapPin,
  Phone,
  Printer
} from 'lucide-vue-next'

const ordersStore = useStaffOrdersStore()

// Local state
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedDate = ref('today')
const showOrderModal = ref(false)
const showNoteModal = ref(false)
const newNote = ref('')
const isUpdatingStatus = ref(false)

// Status configuration
const statusConfig = {
  pending: { label: 'Pending', color: 'yellow', bgColor: 'bg-yellow-100', textColor: 'text-yellow-800' },
  confirmed: { label: 'Confirmed', color: 'blue', bgColor: 'bg-blue-100', textColor: 'text-blue-800' },
  processing: { label: 'Processing', color: 'indigo', bgColor: 'bg-indigo-100', textColor: 'text-indigo-800' },
  ready_for_pickup: { label: 'Ready for Pickup', color: 'purple', bgColor: 'bg-purple-100', textColor: 'text-purple-800' },
  ready_for_delivery: { label: 'Ready for Delivery', color: 'purple', bgColor: 'bg-purple-100', textColor: 'text-purple-800' },
  out_for_delivery: { label: 'Out for Delivery', color: 'cyan', bgColor: 'bg-cyan-100', textColor: 'text-cyan-800' },
  delivered: { label: 'Delivered', color: 'green', bgColor: 'bg-green-100', textColor: 'text-green-800' },
  picked_up: { label: 'Picked Up', color: 'green', bgColor: 'bg-green-100', textColor: 'text-green-800' },
  completed: { label: 'Completed', color: 'green', bgColor: 'bg-green-100', textColor: 'text-green-800' },
  cancelled: { label: 'Cancelled', color: 'red', bgColor: 'bg-red-100', textColor: 'text-red-800' }
}

// Status flow for updates
const statusFlow = [
  { value: 'pending', label: 'Pending' },
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'processing', label: 'Processing' },
  { value: 'ready_for_pickup', label: 'Ready for Pickup' },
  { value: 'ready_for_delivery', label: 'Ready for Delivery' },
  { value: 'out_for_delivery', label: 'Out for Delivery' },
  { value: 'delivered', label: 'Delivered' },
  { value: 'completed', label: 'Completed' }
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
const filteredOrders = computed(() => ordersStore.orders)
const counts = computed(() => ordersStore.orderCounts)
const isLoading = computed(() => ordersStore.isLoading)
const selectedOrder = computed(() => ordersStore.selectedOrder)

// Lifecycle
onMounted(async () => {
  await fetchOrders()
})

// Watch for filter changes
watch([searchQuery, selectedStatus, selectedDate], () => {
  ordersStore.setFilters({
    search: searchQuery.value,
    status: selectedStatus.value,
    date: selectedDate.value
  })
  fetchOrders()
}, { debounce: 300 })

// Methods
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
  ordersStore.clearSelectedOrder()
}

async function updateStatus(orderId, newStatus) {
  isUpdatingStatus.value = true
  try {
    await ordersStore.updateOrderStatus(orderId, newStatus)
    await fetchOrders()
  } catch (err) {
    console.error('Failed to update status:', err)
  } finally {
    isUpdatingStatus.value = false
  }
}

function openNoteModal() {
  newNote.value = ''
  showNoteModal.value = true
}

async function addNote() {
  if (!newNote.value.trim() || !selectedOrder.value) return
  
  try {
    await ordersStore.addNote(selectedOrder.value.id, newNote.value)
    showNoteModal.value = false
    newNote.value = ''
  } catch (err) {
    console.error('Failed to add note:', err)
  }
}

function getStatusConfig(status) {
  return statusConfig[status] || { label: status, bgColor: 'bg-gray-100', textColor: 'text-gray-800' }
}

function getNextStatus(currentStatus) {
  const currentIndex = statusFlow.findIndex(s => s.value === currentStatus)
  if (currentIndex >= 0 && currentIndex < statusFlow.length - 1) {
    return statusFlow[currentIndex + 1]
  }
  return null
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

function printPackingSlip() {
  window.print()
}

function setStatusFilter(status) {
  selectedStatus.value = status
}

function goToPage(page) {
  ordersStore.setPage(page)
  fetchOrders()
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
          <span class="text-gray-900">Order Queue</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Order Queue</h1>
            <p class="text-gray-600 mt-1">Process and manage customer orders</p>
          </div>
          <button @click="fetchOrders" 
            class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors"
            :disabled="isLoading">
            <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" />
            Refresh
          </button>
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
            All ({{ counts.all }})
          </button>
          <button 
            @click="setStatusFilter('pending')"
            :class="[
              'px-6 py-3 text-sm font-medium transition-colors whitespace-nowrap',
              selectedStatus === 'pending' 
                ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F] bg-red-50' 
                : 'text-gray-500 hover:text-gray-700'
            ]">
            Pending ({{ counts.pending }})
          </button>
          <button 
            @click="setStatusFilter('processing')"
            :class="[
              'px-6 py-3 text-sm font-medium transition-colors whitespace-nowrap',
              selectedStatus === 'processing' 
                ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F] bg-red-50' 
                : 'text-gray-500 hover:text-gray-700'
            ]">
            Processing ({{ counts.processing }})
          </button>
          <button 
            @click="setStatusFilter('ready_for_pickup')"
            :class="[
              'px-6 py-3 text-sm font-medium transition-colors whitespace-nowrap',
              selectedStatus === 'ready_for_pickup' 
                ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F] bg-red-50' 
                : 'text-gray-500 hover:text-gray-700'
            ]">
            Ready ({{ counts.ready }})
          </button>
          <button 
            @click="setStatusFilter('out_for_delivery')"
            :class="[
              'px-6 py-3 text-sm font-medium transition-colors whitespace-nowrap',
              selectedStatus === 'out_for_delivery' 
                ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F] bg-red-50' 
                : 'text-gray-500 hover:text-gray-700'
            ]">
            Out for Delivery ({{ counts.outForDelivery }})
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
              placeholder="Search by order ID or customer..." 
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="!filteredOrders.length">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <ClipboardList class="w-8 h-8 text-gray-400" />
                  </div>
                  <p class="text-gray-500 mb-2">No orders found</p>
                  <p class="text-sm text-gray-400">Orders matching your filters will appear here</p>
                </td>
              </tr>
              <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="font-medium text-gray-900">#{{ order.order_number || order.id }}</p>
                  <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <p class="text-gray-900">{{ order.customer?.name || order.customer_name || 'Guest' }}</p>
                  <p class="text-sm text-gray-500">{{ order.customer?.phone || order.phone || '-' }}</p>
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
                  <div class="flex items-center text-sm text-gray-500">
                    <component :is="order.delivery_type === 'delivery' ? Truck : Package" class="w-4 h-4 mr-1" />
                    {{ order.delivery_type === 'delivery' ? 'Delivery' : 'Pickup' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <div class="flex items-center justify-end space-x-2">
                    <button 
                      @click="viewOrder(order)"
                      class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors">
                      <Eye class="w-4 h-4 mr-1" />
                      View
                    </button>
                    <button 
                      v-if="getNextStatus(order.status)"
                      @click="updateStatus(order.id, getNextStatus(order.status).value)"
                      :disabled="isUpdatingStatus"
                      class="inline-flex items-center px-3 py-1.5 bg-[#CF0D0F] text-white text-sm rounded-lg hover:bg-[#B00B0D] transition-colors disabled:opacity-50">
                      <CheckCircle class="w-4 h-4 mr-1" />
                      {{ getNextStatus(order.status).label }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="ordersStore.pagination.totalPages > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Showing page {{ ordersStore.pagination.currentPage }} of {{ ordersStore.pagination.totalPages }}
          </p>
          <div class="flex items-center space-x-2">
            <button 
              @click="goToPage(ordersStore.pagination.currentPage - 1)"
              :disabled="ordersStore.pagination.currentPage === 1"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <ChevronLeft class="w-4 h-4" />
            </button>
            <button 
              @click="goToPage(ordersStore.pagination.currentPage + 1)"
              :disabled="ordersStore.pagination.currentPage === ordersStore.pagination.totalPages"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <Teleport to="body">
      <div v-if="showOrderModal && selectedOrder" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeOrderModal"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-3xl sm:w-full">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">
                    Order #{{ selectedOrder.order_number || selectedOrder.id }}
                  </h3>
                  <p class="text-sm text-gray-500">{{ formatDate(selectedOrder.created_at) }}</p>
                </div>
                <div class="flex items-center space-x-2">
                  <span :class="[
                    'inline-flex px-3 py-1 text-sm font-medium rounded-full',
                    getStatusConfig(selectedOrder.status).bgColor,
                    getStatusConfig(selectedOrder.status).textColor
                  ]">
                    {{ getStatusConfig(selectedOrder.status).label }}
                  </span>
                  <button @click="closeOrderModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                    <X class="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Modal Content -->
            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
              <!-- Customer Info -->
              <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-500 uppercase mb-3">Customer Information</h4>
                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                  <div class="flex items-center">
                    <User class="w-4 h-4 text-gray-400 mr-2" />
                    <span class="text-gray-900">{{ selectedOrder.customer?.name || selectedOrder.customer_name || 'Guest' }}</span>
                  </div>
                  <div class="flex items-center">
                    <Phone class="w-4 h-4 text-gray-400 mr-2" />
                    <span class="text-gray-600">{{ selectedOrder.customer?.phone || selectedOrder.phone || '-' }}</span>
                  </div>
                  <div v-if="selectedOrder.delivery_type === 'delivery'" class="flex items-start">
                    <MapPin class="w-4 h-4 text-gray-400 mr-2 mt-0.5" />
                    <span class="text-gray-600">{{ selectedOrder.delivery_address || '-' }}</span>
                  </div>
                </div>
              </div>

              <!-- Order Items (Packing Checklist) -->
              <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-500 uppercase mb-3">Items (Packing Checklist)</h4>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <tr v-for="item in (selectedOrder.items || [])" :key="item.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                          <div class="flex items-center">
                            <input type="checkbox" class="w-4 h-4 text-[#CF0D0F] border-gray-300 rounded mr-3 focus:ring-[#CF0D0F]" />
                            <div>
                              <p class="font-medium text-gray-900">{{ item.product?.name || item.name }}</p>
                              <p class="text-sm text-gray-500">SKU: {{ item.product?.sku || item.sku || '-' }}</p>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-900">{{ item.quantity }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">{{ formatCurrency(item.price * item.quantity) }}</td>
                      </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                      <tr>
                        <td colspan="2" class="px-4 py-3 text-right font-medium text-gray-900">Total:</td>
                        <td class="px-4 py-3 text-right font-bold text-[#CF0D0F]">{{ formatCurrency(selectedOrder.total) }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

              <!-- Order Notes -->
              <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="text-sm font-medium text-gray-500 uppercase">Notes</h4>
                  <button @click="openNoteModal" class="text-sm text-[#CF0D0F] hover:underline">+ Add Note</button>
                </div>
                <div v-if="selectedOrder.notes?.length" class="space-y-2">
                  <div v-for="note in selectedOrder.notes" :key="note.id" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-gray-900">{{ note.content || note.note }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ note.created_by?.name || 'Staff' }} - {{ formatDate(note.created_at) }}</p>
                  </div>
                </div>
                <p v-else class="text-sm text-gray-500 italic">No notes added</p>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
              <button @click="printPackingSlip" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                <Printer class="w-4 h-4 mr-2" />
                Print Packing Slip
              </button>
              <div class="flex items-center space-x-3">
                <button @click="closeOrderModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                  Close
                </button>
                <button 
                  v-if="getNextStatus(selectedOrder.status)"
                  @click="updateStatus(selectedOrder.id, getNextStatus(selectedOrder.status).value)"
                  :disabled="isUpdatingStatus"
                  class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50">
                  <CheckCircle class="w-4 h-4 mr-2" />
                  Mark as {{ getNextStatus(selectedOrder.status).label }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Add Note Modal -->
    <Teleport to="body">
      <div v-if="showNoteModal" class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showNoteModal = false"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Add Note</h3>
            </div>
            <div class="px-6 py-4">
              <textarea 
                v-model="newNote"
                rows="4"
                placeholder="Enter note..."
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
              ></textarea>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button @click="showNoteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="addNote"
                :disabled="!newNote.trim() || ordersStore.isUpdating"
                class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50">
                Add Note
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
