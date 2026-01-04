<script setup>
/**
 * OrdersPage.vue
 * Customer order history page
 */
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useOrdersStore } from '@/stores/orders'
import { 
  MagnifyingGlassIcon, 
  ClipboardDocumentListIcon,
  ChevronRightIcon 
} from '@heroicons/vue/24/outline'

const ordersStore = useOrdersStore()
const searchQuery = ref('')
const statusFilter = ref('all')
const dateFilter = ref('all')

// Computed
const filteredOrders = computed(() => {
  let filtered = ordersStore.orders

  // Filter by search query
  if (searchQuery.value) {
    filtered = filtered.filter(order => 
      order.order_number.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Filter by status
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(order => order.status === statusFilter.value)
  }

  // Sort by date (newest first)
  return filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

const hasOrders = computed(() => ordersStore.orders.length > 0)

// Methods
const getStatusColor = (status) => {
  const config = ordersStore.getStatusConfig(status)
  const colors = {
    yellow: 'bg-yellow-100 text-yellow-800',
    blue: 'bg-blue-100 text-blue-800',
    indigo: 'bg-indigo-100 text-indigo-800',
    purple: 'bg-purple-100 text-purple-800',
    orange: 'bg-orange-100 text-orange-800',
    green: 'bg-green-100 text-green-800',
    red: 'bg-red-100 text-red-800',
    gray: 'bg-gray-100 text-gray-800'
  }
  return colors[config.color] || colors.gray
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-AU', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

// Lifecycle
onMounted(async () => {
  await ordersStore.fetchOrders()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Page Header -->
      <div class="mb-8">
        <nav class="text-sm mb-4">
          <RouterLink to="/customer" class="text-gray-500 hover:text-[#CF0D0F]">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">Orders</span>
        </nav>
        <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
        <p class="text-gray-600">View and track your order history</p>
      </div>

      <!-- Filters -->
      <div v-if="hasOrders" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
          <div class="flex-1 min-w-[200px] relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search by order number..." 
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#CF0D0F] focus:border-transparent"
            />
          </div>
          <select 
            v-model="statusFilter"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#CF0D0F] focus:border-transparent"
          >
            <option value="all">All Status</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="processing">Processing</option>
            <option value="out_for_delivery">Out for Delivery</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="ordersStore.isLoading" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12">
        <div class="flex flex-col items-center justify-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#CF0D0F] mb-4"></div>
          <p class="text-gray-600">Loading your orders...</p>
        </div>
      </div>

      <!-- Orders List -->
      <div v-else-if="hasOrders" class="space-y-4">
        <div 
          v-for="order in filteredOrders" 
          :key="order.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="font-semibold text-gray-900 text-lg">{{ order.order_number }}</h3>
              <p class="text-sm text-gray-500">Placed on {{ formatDate(order.created_at) }}</p>
            </div>
            <div class="text-right">
              <span 
                class="inline-block px-3 py-1 text-sm font-medium rounded-full"
                :class="getStatusColor(order.status)"
              >
                {{ ordersStore.getStatusConfig(order.status).label }}
              </span>
              <p class="text-sm text-gray-500 mt-2">
                {{ order.items?.length || 0 }} item{{ (order.items?.length || 0) !== 1 ? 's' : '' }} • 
                {{ order.total_formatted || order.total }}
              </p>
            </div>
          </div>

          <!-- Order Items Preview -->
          <div v-if="order.items && order.items.length > 0" class="border-t border-gray-200 pt-4 mb-4">
            <div class="flex gap-2 overflow-x-auto">
              <div 
                v-for="(item, index) in order.items.slice(0, 3)" 
                :key="index"
                class="flex-shrink-0 text-sm text-gray-600"
              >
                <span class="font-medium">{{ item.product_name }}</span>
                <span class="text-gray-400"> × {{ item.quantity }}</span>
                <span v-if="index < Math.min(2, order.items.length - 1)" class="mx-2">•</span>
              </div>
              <span v-if="order.items.length > 3" class="text-sm text-gray-400">
                +{{ order.items.length - 3 }} more
              </span>
            </div>
          </div>

          <!-- View Details Button -->
          <RouterLink
            :to="`/customer/orders/${order.order_number}`"
            class="inline-flex items-center text-[#CF0D0F] hover:text-[#F6211F] font-medium text-sm"
          >
            View Order Details
            <ChevronRightIcon class="ml-1 h-4 w-4" />
          </RouterLink>
        </div>

        <!-- No Results -->
        <div v-if="filteredOrders.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
          <p class="text-gray-600">No orders found matching your filters.</p>
          <button 
            @click="searchQuery = ''; statusFilter = 'all'"
            class="mt-4 text-[#CF0D0F] hover:text-[#F6211F] font-medium"
          >
            Clear Filters
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="text-center py-16">
          <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <ClipboardDocumentListIcon class="w-10 h-10 text-gray-400" />
          </div>
          <h2 class="text-xl font-semibold text-gray-800 mb-2">No Orders Yet</h2>
          <p class="text-gray-600 mb-6">
            You haven't placed any orders yet. Start shopping to see your orders here.
          </p>
          <RouterLink 
            to="/shop" 
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-[#CF0D0F] to-[#F6211F] text-white font-medium rounded-lg hover:shadow-lg transition-all"
          >
            Start Shopping
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
