<script setup>
/**
 * DashboardPage.vue (Staff)
 * Staff dashboard showing today's orders and delivery overview
 */
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { staffDashboard } from '@/services/dashboard'
import StatCard from '@/components/dashboard/StatCard.vue'
import BarChart from '@/components/charts/BarChart.vue'
import {
  ClipboardList,
  TruckIcon,
  CheckCircle,
  Clock,
  PackageIcon,
  PackageCheck,
  PackageX,
  AlertTriangle,
  FileText,
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

  const data = dashboardData.value.dashboard || {}
  const today = data.today || {}
  const queue = data.queue || {}

  return [
    {
      icon: ClipboardList,
      iconBackground: 'linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%)',
      label: 'Orders Today',
      value: today.orders_today || 0,
      showChange: false
    },
    {
      icon: TruckIcon,
      iconBackground: 'linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%)',
      label: 'Deliveries Today',
      value: today.deliveries_today || 0,
      showChange: false
    },
    {
      icon: CheckCircle,
      iconBackground: 'linear-gradient(135deg, #10B981 0%, #34D399 100%)',
      label: 'Completed Today',
      value: today.completed_today || 0,
      showChange: false
    },
    {
      icon: Clock,
      iconBackground: 'linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%)',
      label: 'Pending Orders',
      value: queue.pending || 0,
      showChange: false
    },
    {
      icon: PackageIcon,
      iconBackground: 'linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%)',
      label: 'Processing',
      value: queue.processing || 0,
      showChange: false
    },
    {
      icon: PackageCheck,
      iconBackground: 'linear-gradient(135deg, #14B8A6 0%, #2DD4BF 100%)',
      label: 'Ready for Pickup',
      value: queue.ready_for_pickup || 0,
      showChange: false
    },
    {
      icon: TruckIcon,
      iconBackground: 'linear-gradient(135deg, #06B6D4 0%, #22D3EE 100%)',
      label: 'Out for Delivery',
      value: queue.out_for_delivery || 0,
      showChange: false
    }
  ]
})

// Computed weekly summary chart data
const weeklyChartData = computed(() => {
  if (!dashboardData.value) {
    return {
      labels: ['Orders', 'Deliveries', 'Waste Logs'],
      datasets: [
        {
          label: 'This Week',
          data: [0, 0, 0],
          backgroundColor: '#CF0D0F'
        }
      ]
    }
  }

  const summary = dashboardData.value.dashboard?.weekly_summary || {}
  return {
    labels: ['Orders', 'Deliveries', 'Waste Logs'],
    datasets: [
      {
        label: 'This Week',
        data: [
          summary.orders_this_week || 0,
          summary.deliveries_this_week || 0,
          summary.waste_logs_this_week || 0
        ],
        backgroundColor: '#CF0D0F'
      }
    ]
  }
})

// Computed low stock alerts
const lowStockProducts = computed(() => {
  if (!dashboardData.value) return []
  return dashboardData.value.dashboard?.low_stock_alerts || []
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
    const response = await staffDashboard.getOverview()
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
function manageOrders() {
  router.push('/staff/orders')
}

function viewDeliveries() {
  router.push('/staff/deliveries')
}

function viewInventory() {
  router.push('/staff/inventory')
}

function updateStatus() {
  router.push('/staff/orders')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header with Quick Actions -->
      <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Staff Dashboard</h1>
            <p class="text-gray-600 mt-1">Manage today's orders and deliveries</p>
          </div>
          <div class="flex flex-wrap gap-3 mt-4 md:mt-0">
            <button @click="manageOrders"
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-[#CF0D0F] to-[#F6211F] text-white font-medium rounded-lg hover:shadow-lg transition-all duration-200 transform hover:scale-105">
              <ClipboardList class="w-5 h-5 mr-2" />
              Manage Orders
            </button>
            <button @click="viewDeliveries"
              class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors">
              <TruckIcon class="w-5 h-5 mr-2" />
              View Deliveries
            </button>
            <button @click="viewInventory"
              class="inline-flex items-center px-4 py-2 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
              <FileText class="w-5 h-5 mr-2" />
              Inventory
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <div class="text-center">
          <div
            class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent">
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
        <!-- Today's Stats -->
        <div class="mb-8">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Today's Overview</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <StatCard v-for="(stat, index) in stats.slice(0, 4)" :key="index" :icon="stat.icon"
              :icon-background="stat.iconBackground" :label="stat.label" :value="stat.value"
              :show-change="stat.showChange" />
          </div>
        </div>

        <!-- Queue Status -->
        <div class="mb-8">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Queue Status</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <StatCard v-for="(stat, index) in stats.slice(4)" :key="index" :icon="stat.icon"
              :icon-background="stat.iconBackground" :label="stat.label" :value="stat.value"
              :show-change="stat.showChange" />
          </div>
        </div>

        <!-- Charts and Widgets -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          <!-- Weekly Summary Chart -->
          <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-lg font-semibold text-gray-900">Weekly Summary</h2>
              <div class="text-sm text-gray-500">Last 7 days</div>
            </div>
            <BarChart :labels="weeklyChartData.labels" :datasets="weeklyChartData.datasets" :height="300" />
          </div>

          <!-- Low Stock Alerts -->
          <div
            class="bg-gradient-to-br from-red-50 to-orange-50 rounded-lg shadow-sm border-2 border-[#CF0D0F] p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                  <AlertTriangle class="w-5 h-5 text-red-600" />
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Low Stock Alert</h2>
              </div>
            </div>

            <div v-if="lowStockProducts.length === 0" class="text-center py-8">
              <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <CheckCircle class="w-8 h-8 text-green-600" />
              </div>
              <p class="text-gray-600">All products are well stocked!</p>
            </div>

            <div v-else class="space-y-3">
              <div v-for="product in lowStockProducts" :key="product.id"
                class="bg-white rounded-lg p-4 border-l-4 border-red-500 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h3 class="font-semibold text-gray-900">{{ product.name }}</h3>
                    <p class="text-sm text-gray-500">SKU: {{ product.sku }}</p>
                  </div>
                  <div class="text-right ml-4">
                    <div class="text-2xl font-bold text-red-600">{{ product.stock }}</div>
                    <div class="text-xs text-gray-500">units left</div>
                  </div>
                </div>
                <button @click="viewInventory"
                  class="mt-3 w-full px-3 py-2 bg-[#CF0D0F] text-white text-sm font-medium rounded-lg hover:bg-[#F6211F] transition-colors">
                  View in Inventory
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Stats Summary -->
        <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <button @click="manageOrders"
              class="p-4 text-left border-2 border-gray-200 rounded-lg hover:border-[#CF0D0F] hover:bg-gray-50 transition-all">
              <ClipboardList class="w-8 h-8 text-[#CF0D0F] mb-2" />
              <h3 class="font-semibold text-gray-900">Manage Orders</h3>
              <p class="text-sm text-gray-500 mt-1">View and update order status</p>
            </button>

            <button @click="viewDeliveries"
              class="p-4 text-left border-2 border-gray-200 rounded-lg hover:border-[#CF0D0F] hover:bg-gray-50 transition-all">
              <TruckIcon class="w-8 h-8 text-[#CF0D0F] mb-2" />
              <h3 class="font-semibold text-gray-900">View Deliveries</h3>
              <p class="text-sm text-gray-500 mt-1">Track delivery status</p>
            </button>

            <button @click="updateStatus"
              class="p-4 text-left border-2 border-gray-200 rounded-lg hover:border-[#CF0D0F] hover:bg-gray-50 transition-all">
              <RefreshCw class="w-8 h-8 text-[#CF0D0F] mb-2" />
              <h3 class="font-semibold text-gray-900">Update Status</h3>
              <p class="text-sm text-gray-500 mt-1">Change order/delivery status</p>
            </button>

            <button @click="viewInventory"
              class="p-4 text-left border-2 border-gray-200 rounded-lg hover:border-[#CF0D0F] hover:bg-gray-50 transition-all">
              <PackageIcon class="w-8 h-8 text-[#CF0D0F] mb-2" />
              <h3 class="font-semibold text-gray-900">Inventory</h3>
              <p class="text-sm text-gray-500 mt-1">Check stock levels</p>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
