<script setup>
/**
 * DashboardPage.vue
 * Customer dashboard showing order summary and quick actions
 */
import { ref, computed, onMounted, onUnmounted, markRaw } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { customerDashboard } from '@/services/dashboard'
import StatCard from '@/components/dashboard/StatCard.vue'
import {
  ShoppingBag,
  TruckIcon,
  Heart,
  MessageCircle,
  Bell,
  User,
  MapPin,
  ShoppingCart,
  Package,
  AlertTriangle,
  RefreshCw
} from 'lucide-vue-next'

const router = useRouter()
const isLoading = ref(true)
const error = ref(null)
const dashboardData = ref(null)
let pollingInterval = null

// Computed stats for StatCard components
const stats = computed(() => {
  if (!dashboardData.value) return []

  const data = dashboardData.value.dashboard?.stats || {}

  return [
    {
      icon: markRaw(ShoppingBag),
      iconBackground: 'linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%)',
      label: 'Total Orders',
      value: data.total_orders || 0,
      showChange: false
    },
    {
      icon: markRaw(TruckIcon),
      iconBackground: 'linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%)',
      label: 'Pending Deliveries',
      value: data.pending_deliveries || 0,
      showChange: false
    },
    {
      icon: markRaw(Heart),
      iconBackground: 'linear-gradient(135deg, #EC4899 0%, #F472B6 100%)',
      label: 'Wishlist Items',
      value: data.wishlist_count || 0,
      showChange: false
    },
    {
      icon: markRaw(MessageCircle),
      iconBackground: 'linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%)',
      label: 'Open Tickets',
      value: data.open_tickets || 0,
      showChange: false
    }
  ]
})

// Computed recent orders
const recentOrders = computed(() => {
  if (!dashboardData.value) return []
  return dashboardData.value.dashboard?.recent_orders || []
})

// Computed unread notifications
const unreadNotifications = computed(() => {
  if (!dashboardData.value) return 0
  return dashboardData.value.dashboard?.stats?.unread_notifications || 0
})

onMounted(async () => {
  await fetchDashboardData()
  startPolling()
})

onUnmounted(() => {
  stopPolling()
})

async function fetchDashboardData() {
  try {
    const response = await customerDashboard.getOverview()
    if (response.success) {
      dashboardData.value = response
    }
  } catch (err) {
    console.error('Failed to fetch dashboard data:', err)
    error.value = 'Failed to load dashboard data'
  } finally {
    isLoading.value = false
  }
}

function startPolling() {
  // Poll every 30 seconds
  pollingInterval = setInterval(() => {
    fetchDashboardData()
  }, 30000)
}

function stopPolling() {
  if (pollingInterval) {
    clearInterval(pollingInterval)
    pollingInterval = null
  }
}

// Quick action functions
function shopNow() {
  router.push('/shop')
}

function viewOrders() {
  router.push('/customer/orders')
}

function trackOrder() {
  router.push('/customer/orders')
}

function contactSupport() {
  router.push('/customer/support')
}

// Helper functions
function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function formatCurrency(amount) {
  if (!amount) return '$0.00'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount)
}

function getStatusColor(status) {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    confirmed: 'bg-blue-100 text-blue-800 border-blue-200',
    processing: 'bg-purple-100 text-purple-800 border-purple-200',
    ready: 'bg-teal-100 text-teal-800 border-teal-200',
    out_for_delivery: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    delivered: 'bg-green-100 text-green-800 border-green-200',
    cancelled: 'bg-red-100 text-red-800 border-red-200'
  }
  return colors[status] || 'bg-gray-100 text-gray-800 border-gray-200'
}

function getStatusText(status) {
  const texts = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    processing: 'Processing',
    ready: 'Ready',
    out_for_delivery: 'Out for Delivery',
    delivered: 'Delivered',
    cancelled: 'Cancelled'
  }
  return texts[status] || status
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header with Quick Actions -->
      <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">My Dashboard</h1>
            <p class="text-gray-600 mt-1">Welcome back! Here's your account overview.</p>
          </div>
          <div class="flex flex-wrap gap-3 mt-4 md:mt-0">
            <button @click="shopNow"
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#CF0D0F] to-[#F6211F] text-white font-medium rounded-lg hover:shadow-lg transition-all duration-200 transform hover:scale-105">
              <ShoppingCart class="w-5 h-5 mr-2" />
              Shop Now
            </button>
            <button @click="trackOrder"
              class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors">
              <Package class="w-5 h-5 mr-2" />
              Track Order
            </button>
            <button @click="contactSupport"
              class="inline-flex items-center px-4 py-2 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
              <MessageCircle class="w-5 h-5 mr-2" />
              Support
            </button>
          </div>
        </div>

        <!-- Notifications Badge -->
        <div v-if="unreadNotifications > 0" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
          <div class="flex items-center">
            <Bell class="w-5 h-5 text-blue-600 mr-2" />
            <span class="text-sm text-blue-800">You have <strong>{{ unreadNotifications }}</strong> unread
              notification{{ unreadNotifications !== 1 ? 's' : '' }}</span>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <div class="text-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent">
          </div>
          <p class="mt-4 text-gray-600">Loading dashboard...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-8">
        <div class="flex items-center">
          <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading dashboard</h3>
            <p class="text-red-600 text-sm mt-1">{{ error }}</p>
          </div>
        </div>
        <button @click="fetchDashboardData"
          class="mt-4 inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
          <RefreshCw class="w-4 h-4 mr-2" />
          Retry
        </button>
      </div>

      <!-- Dashboard Content -->
      <div v-else>
        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          <StatCard v-for="(stat, index) in stats" :key="index" :icon="stat.icon" :icon-background="stat.iconBackground"
            :label="stat.label" :value="stat.value" :show-change="stat.showChange" />
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
          <!-- Recent Orders -->
          <div
            class="lg:col-span-2 bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
              <RouterLink to="/customer/orders"
                class="text-sm text-[#CF0D0F] hover:text-[#F6211F] font-medium transition-colors">
                View All →
              </RouterLink>
            </div>

            <div v-if="recentOrders.length === 0" class="text-center py-12">
              <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <ShoppingBag class="w-8 h-8 text-gray-400" />
              </div>
              <p class="text-gray-500 mb-4">No orders yet</p>
              <button @click="shopNow"
                class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors">
                <ShoppingCart class="w-5 h-5 mr-2" />
                Start Shopping
              </button>
            </div>

            <div v-else class="space-y-4">
              <div v-for="order in recentOrders" :key="order.id"
                class="border-2 border-gray-200 rounded-lg p-4 hover:border-[#CF0D0F] hover:shadow-md transition-all">
                <div class="flex items-start justify-between mb-3">
                  <div>
                    <RouterLink :to="`/customer/orders/${order.order_number}`"
                      class="text-lg font-semibold text-gray-900 hover:text-[#CF0D0F] transition-colors">
                      Order #{{ order.order_number }}
                    </RouterLink>
                    <p class="text-sm text-gray-500 mt-1">{{ formatDate(order.created_at) }}</p>
                  </div>
                  <span class="px-3 py-1 rounded-full text-xs font-semibold border-2"
                    :class="getStatusColor(order.status)">
                    {{ getStatusText(order.status) }}
                  </span>
                </div>

                <div class="bg-gray-50 rounded-lg p-3 mb-3">
                  <div class="text-sm text-gray-600">
                    <strong>{{ order.items?.length || 0 }}</strong> item{{ order.items?.length !== 1 ? 's' : '' }}
                  </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t-2 border-gray-200">
                  <div class="text-2xl font-bold text-[#CF0D0F]">
                    {{ formatCurrency(order.total) }}
                  </div>
                  <div class="flex gap-2">
                    <RouterLink :to="`/customer/orders/${order.order_number}`"
                      class="px-3 py-1.5 text-sm border-2 border-[#CF0D0F] text-[#CF0D0F] font-medium rounded-lg hover:bg-[#CF0D0F] hover:text-white transition-colors">
                      View Details
                    </RouterLink>
                    <button v-if="order.status === 'delivered'" @click="shopNow"
                      class="px-3 py-1.5 text-sm bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors">
                      Reorder
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="lg:col-span-1">
            <div
              class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-6 mb-6 hover:shadow-xl transition-shadow">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>

              <div class="space-y-3">
                <RouterLink to="/customer/profile"
                  class="flex items-center p-3 rounded-lg border-2 border-gray-200 hover:border-[#CF0D0F] hover:bg-gray-50 transition-all group">
                  <div
                    class="w-10 h-10 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mr-3 group-hover:from-[#CF0D0F] group-hover:to-[#F6211F] transition-all">
                    <User class="w-5 h-5 text-gray-600 group-hover:text-white transition-colors" />
                  </div>
                  <span class="text-gray-700 font-medium">Edit Profile</span>
                </RouterLink>

                <RouterLink to="/customer/addresses"
                  class="flex items-center p-3 rounded-lg border-2 border-gray-200 hover:border-[#CF0D0F] hover:bg-gray-50 transition-all group">
                  <div
                    class="w-10 h-10 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mr-3 group-hover:from-[#CF0D0F] group-hover:to-[#F6211F] transition-all">
                    <MapPin class="w-5 h-5 text-gray-600 group-hover:text-white transition-colors" />
                  </div>
                  <span class="text-gray-700 font-medium">Manage Addresses</span>
                </RouterLink>

                <RouterLink to="/customer/wishlist"
                  class="flex items-center p-3 rounded-lg border-2 border-gray-200 hover:border-[#CF0D0F] hover:bg-gray-50 transition-all group">
                  <div
                    class="w-10 h-10 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mr-3 group-hover:from-[#CF0D0F] group-hover:to-[#F6211F] transition-all">
                    <Heart class="w-5 h-5 text-gray-600 group-hover:text-white transition-colors" />
                  </div>
                  <span class="text-gray-700 font-medium">View Wishlist</span>
                </RouterLink>

                <button @click="shopNow"
                  class="w-full flex items-center justify-center p-3 rounded-lg bg-gradient-to-r from-[#CF0D0F] to-[#F6211F] text-white font-semibold hover:shadow-lg transition-all transform hover:scale-105">
                  <ShoppingCart class="w-5 h-5 mr-2" />
                  Shop Now
                </button>
              </div>
            </div>

            <!-- Need Help Section -->
            <div
              class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg shadow-sm border-2 border-blue-200 p-6 hover:shadow-xl transition-shadow">
              <div class="flex items-center mb-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                  <MessageCircle class="w-5 h-5 text-blue-600" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Need Help?</h3>
              </div>
              <p class="text-sm text-gray-600 mb-4">Our support team is here to assist you with any questions or
                concerns.</p>
              <button @click="contactSupport"
                class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                Contact Support
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
