<script setup>
/**
 * Admin Dashboard Page
 * Overview of store performance with key metrics and statistics
 * 
 * @requirement DASH-001 Create admin dashboard layout
 * @requirement DASH-006 Implement revenue widget
 * @requirement DASH-007 Implement active users widget  
 * @requirement DASH-008 Implement orders widget
 * @requirement DASH-010 Create Revenue Overview line chart
 * @requirement DASH-011 Create Profit vs Expenses bar chart
 * @requirement DASH-014 Create recent activity feed
 * @requirement DASH-015 Implement quick action buttons
 * @requirement DASH-017 Implement data polling (30 seconds)
 * @requirement DASH-018 Create low stock alert widget
 */
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { DollarSign, Users, ShoppingCart, Package, Plus, FileText, AlertTriangle, Clock } from 'lucide-vue-next'
import { adminDashboard } from '@/services/dashboard'
import { useRouter } from 'vue-router'
import StatCard from '@/components/dashboard/StatCard.vue'
import LineChart from '@/components/charts/LineChart.vue'
import BarChart from '@/components/charts/BarChart.vue'

const router = useRouter()
const isLoading = ref(true)
const error = ref(null)
const dashboardData = ref(null)
let pollingInterval = null

// Stats for cards
const stats = computed(() => {
  if (!dashboardData.value) return []

  const today = dashboardData.value.today || {}

  return [
    {
      icon: DollarSign,
      label: 'Total Revenue',
      value: today.revenue || 0,
      change: today.revenue_change || 0,
      isCurrency: true,
      prefix: '$',
      comparisonText: 'vs yesterday'
    },
    {
      icon: ShoppingCart,
      label: 'Total Orders',
      value: today.orders || 0,
      change: today.orders_change || 0,
      comparisonText: 'vs yesterday'
    },
    {
      icon: Users,
      label: 'New Customers',
      value: today.new_customers || 0,
      change: 0,
      comparisonText: 'registered today'
    },
    {
      icon: Package,
      label: 'Pending Orders',
      value: today.pending_orders || 0,
      change: 0,
      comparisonText: 'require attention'
    },
  ]
})

// Revenue chart data
const revenueChartData = computed(() => {
  if (!dashboardData.value?.revenue_chart) return { labels: [], data: [] }

  return {
    labels: dashboardData.value.revenue_chart.map(item => item.day),
    data: dashboardData.value.revenue_chart.map(item => item.revenue)
  }
})

// Profit vs expenses data (dynamic from backend)
const profitExpensesData = computed(() => {
  if (!dashboardData.value?.profit_expenses_chart) {
    return {
      labels: [],
      datasets: [
        { label: 'Expenses', data: [], backgroundColor: '#6F6F6F' },
        { label: 'Profit', data: [], backgroundColor: '#CF0D0F' }
      ]
    }
  }

  const chartData = dashboardData.value.profit_expenses_chart

  return {
    labels: chartData.map(item => item.month),
    datasets: [
      {
        label: 'Expenses',
        data: chartData.map(item => item.expenses),
        backgroundColor: '#6F6F6F'
      },
      {
        label: 'Profit',
        data: chartData.map(item => item.profit),
        backgroundColor: '#CF0D0F'
      }
    ]
  }
})

// Recent orders
const recentOrders = computed(() => dashboardData.value?.recent_orders || [])

// Low stock products
const lowStockProducts = computed(() => dashboardData.value?.low_stock_products || [])

// Top products
const topProducts = computed(() => dashboardData.value?.top_products || [])

onMounted(async () => {
  await fetchDashboardData()
  startPolling()
})

onUnmounted(() => {
  stopPolling()
})

async function fetchDashboardData() {
  try {
    const response = await adminDashboard.getOverview()
    if (response.success) {
      dashboardData.value = response.dashboard
    }
    error.value = null
  } catch (err) {
    console.error('Failed to fetch dashboard data:', err)
    error.value = 'Failed to load dashboard data'
  } finally {
    isLoading.value = false
  }
}

// DASH-017: Implement 30-second polling
function startPolling() {
  pollingInterval = setInterval(() => {
    fetchDashboardData()
  }, 30000) // 30 seconds
}

function stopPolling() {
  if (pollingInterval) {
    clearInterval(pollingInterval)
    pollingInterval = null
  }
}

// Quick actions navigation
function createOrder() {
  router.push('/admin/orders/create')
}

function addProduct() {
  router.push('/admin/products/create')
}

function viewReports() {
  router.push('/admin/reports')
}

function manageInventory() {
  router.push('/admin/inventory')
}

// Format date helper
function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Format currency
function formatCurrency(value) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value)
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header Card -->
    <div class="bg-white rounded-xl shadow-md border-2 p-6 mb-6" style="border-color: #CF0D0F;">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold" style="color: #CF0D0F;">Dashboard Overview</h1>
          <p class="text-sm mt-2" style="color: #6F6F6F;">Track your store's performance and key metrics.</p>
        </div>

        <!-- Quick Actions - DASH-015 -->
        <div class="flex flex-wrap items-center gap-2">
          <button @click="createOrder"
            class="inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-bold text-white shadow-md hover:shadow-lg transition-all duration-200"
            style="background: linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%);">
            <Plus class="w-4 h-4 mr-2" />
            Create Order
          </button>

          <button @click="addProduct"
            class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200"
            style="background-color: #F6211F; color: white;">
            <Package class="w-4 h-4 mr-2" />
            Add Product
          </button>

          <button @click="viewReports"
            class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200"
            style="background-color: #EFEFEF; color: #4D4B4C;">
            <FileText class="w-4 h-4 mr-2" />
            Reports
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col justify-center items-center py-24">
      <div class="relative">
        <svg class="animate-spin h-16 w-16" style="color: #CF0D0F;" xmlns="http://www.w3.org/2000/svg" fill="none"
          viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
          <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
          </path>
        </svg>
      </div>
      <p class="mt-4 text-base font-semibold" style="color: #6F6F6F;">Loading dashboard...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-white rounded-xl shadow-md border-2 p-6 mb-8" style="border-color: #CF0D0F;">
      <div class="flex items-center mb-4">
        <svg class="w-8 h-8 mr-3" style="color: #CF0D0F;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="text-lg font-bold" style="color: #CF0D0F;">Error Loading Dashboard</h3>
          <p class="text-sm mt-1" style="color: #6F6F6F;">{{ error }}</p>
        </div>
      </div>
      <button @click="fetchDashboardData"
        class="px-6 py-3 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
        style="background: linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%);">
        Retry
      </button>
    </div>

    <!-- Dashboard Content -->
    <div v-else>
      <!-- Stats Cards - DASH-001, DASH-006, DASH-007, DASH-008 -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard v-for="stat in stats" :key="stat.label" :icon="stat.icon" :label="stat.label" :value="stat.value"
          :change="stat.change" :prefix="stat.prefix" :is-currency="stat.isCurrency"
          :comparison-text="stat.comparisonText" />
      </div>

      <!-- Charts Row - DASH-010, DASH-011 -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Revenue Overview Line Chart -->
        <div class="bg-white rounded-xl p-6 shadow-md border-2" style="border-color: #CF0D0F;">
          <h3 class="text-lg font-bold mb-4" style="color: #4D4B4C;">Revenue Overview (Last 7 Days)</h3>
          <div style="height: 300px;">
            <LineChart v-if="revenueChartData.labels.length > 0" :labels="revenueChartData.labels"
              :data="revenueChartData.data" label="Daily Revenue" color="#CF0D0F" fill-color="rgba(207, 13, 15, 0.1)" />
            <div v-else class="flex items-center justify-center h-full text-gray-400">
              No revenue data available
            </div>
          </div>
        </div>

        <!-- Profit vs Expenses Bar Chart -->
        <div class="bg-white rounded-xl p-6 shadow-md border-2" style="border-color: #CF0D0F;">
          <h3 class="text-lg font-bold mb-4" style="color: #4D4B4C;">Profit vs Expenses (Monthly)</h3>
          <div style="height: 300px;">
            <BarChart :labels="profitExpensesData.labels" :datasets="profitExpensesData.datasets" />
          </div>
        </div>
      </div>

      <!-- Three Column Widgets -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders - DASH-014 -->
        <div class="bg-white rounded-xl p-6 shadow-md border-2" style="border-color: #CF0D0F;">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold" style="color: #4D4B4C;">Recent Orders</h3>
            <Clock class="w-5 h-5" style="color: #CF0D0F;" />
          </div>

          <div v-if="recentOrders.length > 0" class="space-y-3">
            <div v-for="order in recentOrders" :key="order.id"
              class="flex items-center justify-between p-3 rounded-lg transition-colors hover:bg-gray-50"
              style="border-left: 3px solid #CF0D0F;">
              <div class="flex-1">
                <p class="text-sm font-bold" style="color: #4D4B4C;">{{ order.order_number }}</p>
                <p class="text-xs" style="color: #6F6F6F;">{{ order.customer_name }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold" style="color: #CF0D0F;">{{ formatCurrency(order.total) }}</p>
                <span class="inline-block px-2 py-1 text-xs font-semibold rounded" :class="{
                  'bg-green-100 text-green-800': order.status === 'delivered',
                  'bg-blue-100 text-blue-800': order.status === 'processing',
                  'bg-yellow-100 text-yellow-800': order.status === 'pending'
                }">
                  {{ order.status }}
                </span>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-8 text-gray-400">
            No recent orders
          </div>
        </div>

        <!-- Low Stock Alert - DASH-018 -->
        <div class="bg-white rounded-xl p-6 shadow-md border-2" style="border-color: #CF0D0F;">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold" style="color: #4D4B4C;">Low Stock Alert</h3>
            <AlertTriangle class="w-5 h-5" style="color: #F6211F;" />
          </div>

          <div v-if="lowStockProducts.length > 0" class="space-y-3">
            <div v-for="product in lowStockProducts" :key="product.id"
              class="flex items-center justify-between p-3 rounded-lg"
              style="background-color: #FEF2F2; border-left: 3px solid #F6211F;">
              <div class="flex-1">
                <p class="text-sm font-bold" style="color: #4D4B4C;">{{ product.name }}</p>
                <p class="text-xs" style="color: #6F6F6F;">Stock remaining</p>
              </div>
              <div class="text-right">
                <p class="text-lg font-bold" style="color: #F6211F;">{{ product.stock }}</p>
                <button @click="manageInventory" class="text-xs font-semibold hover:underline" style="color: #CF0D0F;">
                  Restock
                </button>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-8 text-gray-400">
            All products in stock
          </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-xl p-6 shadow-md border-2" style="border-color: #CF0D0F;">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold" style="color: #4D4B4C;">Top Products Today</h3>
            <Package class="w-5 h-5" style="color: #CF0D0F;" />
          </div>

          <div v-if="topProducts.length > 0" class="space-y-3">
            <div v-for="(product, index) in topProducts" :key="product.id"
              class="flex items-center justify-between p-3 rounded-lg transition-colors hover:bg-gray-50">
              <div class="flex items-center gap-3 flex-1">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm"
                  style="background: linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%);">
                  {{ index + 1 }}
                </div>
                <div>
                  <p class="text-sm font-bold" style="color: #4D4B4C;">{{ product.name }}</p>
                  <p class="text-xs" style="color: #6F6F6F;">Units sold</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-lg font-bold" style="color: #CF0D0F;">{{ product.total_sold }}</p>
              </div>
            </div>
          </div>

          <div v-else class="text-center py-8 text-gray-400">
            No sales today
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
