<script setup>
/**
 * Admin Reports Page
 * Business reports and analytics dashboard
 * @requirements RPT-001 to RPT-022
 */
import { ref, computed, onMounted, watch } from 'vue'
import { useAdminReportsStore } from '@/stores/adminReports'
import {
  DollarSign,
  Users,
  TrendingUp,
  Calendar,
  Download,
  Eye,
  RefreshCw
} from 'lucide-vue-next'
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

// Store
const reportsStore = useAdminReportsStore()

// Local state
const selectedPreset = ref('month')
const customStartDate = ref('')
const customEndDate = ref('')
const showCustomDatePicker = ref(false)

// Date preset options
const datePresets = [
  { value: 'today', label: 'Today' },
  { value: 'yesterday', label: 'Yesterday' },
  { value: 'week', label: 'This Week' },
  { value: 'last_week', label: 'Last Week' },
  { value: 'month', label: 'This Month' },
  { value: 'last_month', label: 'Last Month' },
  { value: 'year', label: 'This Year' },
  { value: 'custom', label: 'Custom Range' }
]

// Report types configuration - Only 3 key business decision reports
const reportTypes = [
  { type: 'revenue', label: 'Revenue Trend', icon: DollarSign, description: 'Revenue analysis over time' },
  { type: 'top_products', label: 'Top Products', icon: TrendingUp, description: 'Best performing products by sales' },
  { type: 'customers', label: 'Top Customers', icon: Users, description: 'Highest spending customers' }
]

// Computed properties
const isLoading = computed(() => reportsStore.loading.dashboard || reportsStore.loading.salesSummary)
const isExporting = computed(() => reportsStore.loading.export)

const quickStats = computed(() => reportsStore.dashboard.quickStats)
const topProducts = computed(() => reportsStore.dashboard.topProducts.slice(0, 5))
const topCustomers = computed(() => reportsStore.dashboard.topCustomers.slice(0, 5))

// Chart data
const chartData = computed(() => {
  const dailyRevenue = reportsStore.salesSummary.dailyRevenue || []
  
  return {
    labels: dailyRevenue.map(item => formatChartDate(item.date)),
    datasets: [
      {
        label: 'Revenue',
        data: dailyRevenue.map(item => parseFloat(item.revenue) || 0),
        borderColor: '#CF0D0F',
        backgroundColor: 'rgba(207, 13, 15, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6
      }
    ]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      callbacks: {
        label: (context) => `Revenue: $${context.parsed.y.toFixed(2)}`
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (value) => `$${value.toLocaleString()}`
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}

// Methods
function formatCurrency(value) {
  const num = parseFloat(value) || 0
  return `$${num.toFixed(2)}`
}

function formatNumber(value) {
  const num = parseInt(value) || 0
  return num.toLocaleString()
}

function formatChartDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

function formatChange(change) {
  const num = parseFloat(change) || 0
  return `${num >= 0 ? '+' : ''}${num.toFixed(1)}%`
}

function isPositiveChange(change) {
  return parseFloat(change) >= 0
}

function handlePresetChange() {
  if (selectedPreset.value === 'custom') {
    showCustomDatePicker.value = true
  } else {
    showCustomDatePicker.value = false
    reportsStore.setDatePreset(selectedPreset.value)
  }
}

async function applyDateFilter() {
  if (selectedPreset.value === 'custom' && customStartDate.value && customEndDate.value) {
    reportsStore.setCustomDateRange(customStartDate.value, customEndDate.value)
  } else if (selectedPreset.value !== 'custom') {
    reportsStore.setDatePreset(selectedPreset.value)
  }
  
  await fetchData()
}

async function fetchData() {
  try {
    await Promise.all([
      reportsStore.fetchDashboard(),
      reportsStore.fetchSalesSummary()
    ])
  } catch (error) {
    console.error('Failed to fetch reports data:', error)
  }
}

async function handleExport(type, action) {
  try {
    await reportsStore.exportReport(type, action)
  } catch (error) {
    console.error(`Failed to ${action} ${type} report:`, error)
  }
}

// Initialize
onMounted(() => {
  selectedPreset.value = reportsStore.dateFilters.preset || 'month'
  fetchData()
})

// Watch for preset changes
watch(selectedPreset, handlePresetChange)
</script>

<template>
  <div class="space-y-6">
    <!-- Header with Title and Date Range Selector -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-secondary-900">Reports & Analytics</h1>
        <p class="text-gray-500 mt-1">View sales reports and business analytics</p>
      </div>
      
      <!-- Date Range Selector -->
      <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2">
          <Calendar class="w-5 h-5 text-gray-400" />
          <select
            v-model="selectedPreset"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option v-for="preset in datePresets" :key="preset.value" :value="preset.value">
              {{ preset.label }}
            </option>
          </select>
        </div>
        
        <!-- Custom Date Picker -->
        <template v-if="showCustomDatePicker">
          <input
            v-model="customStartDate"
            type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          />
          <span class="text-gray-400">to</span>
          <input
            v-model="customEndDate"
            type="date"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          />
        </template>
        
        <!-- Apply Button -->
        <button
          @click="applyDateFilter"
          :disabled="isLoading"
          class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <RefreshCw :class="['w-4 h-4', { 'animate-spin': isLoading }]" />
          Apply
        </button>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Revenue Card -->
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Total Revenue</p>
            <p class="text-2xl font-bold text-secondary-900 mt-1">
              {{ formatCurrency(quickStats.revenue.value) }}
            </p>
          </div>
          <div class="p-3 rounded-full bg-green-100">
            <DollarSign class="w-6 h-6 text-green-600" />
          </div>
        </div>
        <div class="mt-4 flex items-center gap-1">
          <component
            :is="isPositiveChange(quickStats.revenue.change) ? TrendingUp : TrendingDown"
            :class="['w-4 h-4', isPositiveChange(quickStats.revenue.change) ? 'text-green-500' : 'text-red-500']"
          />
          <span
            :class="['text-sm font-medium', isPositiveChange(quickStats.revenue.change) ? 'text-green-500' : 'text-red-500']"
          >
            {{ formatChange(quickStats.revenue.change) }}
          </span>
          <span class="text-sm text-gray-400 ml-1">vs last period</span>
        </div>
      </div>

      <!-- Orders Card -->
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Total Orders</p>
            <p class="text-2xl font-bold text-secondary-900 mt-1">
              {{ formatNumber(quickStats.orders.value) }}
            </p>
          </div>
          <div class="p-3 rounded-full bg-blue-100">
            <ShoppingCart class="w-6 h-6 text-blue-600" />
          </div>
        </div>
        <div class="mt-4 flex items-center gap-1">
          <component
            :is="isPositiveChange(quickStats.orders.change) ? TrendingUp : TrendingDown"
            :class="['w-4 h-4', isPositiveChange(quickStats.orders.change) ? 'text-green-500' : 'text-red-500']"
          />
          <span
            :class="['text-sm font-medium', isPositiveChange(quickStats.orders.change) ? 'text-green-500' : 'text-red-500']"
          >
            {{ formatChange(quickStats.orders.change) }}
          </span>
          <span class="text-sm text-gray-400 ml-1">vs last period</span>
        </div>
      </div>

      <!-- Customers Card -->
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Active Customers</p>
            <p class="text-2xl font-bold text-secondary-900 mt-1">
              {{ formatNumber(quickStats.customers.value) }}
            </p>
          </div>
          <div class="p-3 rounded-full bg-purple-100">
            <Users class="w-6 h-6 text-purple-600" />
          </div>
        </div>
        <div class="mt-4 flex items-center gap-1">
          <component
            :is="isPositiveChange(quickStats.customers.change) ? TrendingUp : TrendingDown"
            :class="['w-4 h-4', isPositiveChange(quickStats.customers.change) ? 'text-green-500' : 'text-red-500']"
          />
          <span
            :class="['text-sm font-medium', isPositiveChange(quickStats.customers.change) ? 'text-green-500' : 'text-red-500']"
          >
            {{ formatChange(quickStats.customers.change) }}
          </span>
          <span class="text-sm text-gray-400 ml-1">vs last period</span>
        </div>
      </div>

      <!-- Average Order Card -->
      <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Avg Order Value</p>
            <p class="text-2xl font-bold text-secondary-900 mt-1">
              {{ formatCurrency(quickStats.avgOrder.value) }}
            </p>
          </div>
          <div class="p-3 rounded-full bg-orange-100">
            <BarChart3 class="w-6 h-6 text-orange-600" />
          </div>
        </div>
        <div class="mt-4 flex items-center gap-1">
          <component
            :is="isPositiveChange(quickStats.avgOrder.change) ? TrendingUp : TrendingDown"
            :class="['w-4 h-4', isPositiveChange(quickStats.avgOrder.change) ? 'text-green-500' : 'text-red-500']"
          />
          <span
            :class="['text-sm font-medium', isPositiveChange(quickStats.avgOrder.change) ? 'text-green-500' : 'text-red-500']"
          >
            {{ formatChange(quickStats.avgOrder.change) }}
          </span>
          <span class="text-sm text-gray-400 ml-1">vs last period</span>
        </div>
      </div>
    </div>

    <!-- Revenue Trend Chart -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
      <h2 class="text-lg font-semibold text-secondary-900 mb-4">Revenue Trend</h2>
      <div class="h-80">
        <template v-if="isLoading">
          <div class="flex items-center justify-center h-full">
            <RefreshCw class="w-8 h-8 text-gray-400 animate-spin" />
          </div>
        </template>
        <template v-else-if="chartData.labels.length > 0">
          <Line :data="chartData" :options="chartOptions" />
        </template>
        <template v-else>
          <div class="flex items-center justify-center h-full text-gray-400">
            No data available for the selected period
          </div>
        </template>
      </div>
    </div>

    <!-- Available Reports Grid -->
    <div>
      <h2 class="text-lg font-semibold text-secondary-900 mb-4">Available Reports</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div
          v-for="report in reportTypes"
          :key="report.type"
          class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow"
        >
          <div class="flex items-start gap-4">
            <div class="p-2 rounded-lg bg-gray-100">
              <component :is="report.icon" class="w-5 h-5 text-gray-600" />
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-medium text-secondary-900 truncate">{{ report.label }}</h3>
              <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ report.description }}</p>
            </div>
          </div>
          <div class="mt-4 flex items-center gap-2">
            <button
              @click="handleExport(report.type, 'view')"
              :disabled="isExporting"
              class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 disabled:opacity-50 transition-colors"
            >
              <Eye class="w-4 h-4" />
              View
            </button>
            <button
              @click="handleExport(report.type, 'download')"
              :disabled="isExporting"
              class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50 transition-colors"
            >
              <Download class="w-4 h-4" />
              Download
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Two-Column: Top Products | Top Customers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Top Products -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-secondary-900">Top Products</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Sold</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <template v-if="isLoading">
                <tr>
                  <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                    <RefreshCw class="w-6 h-6 animate-spin mx-auto mb-2" />
                    Loading...
                  </td>
                </tr>
              </template>
              <template v-else-if="topProducts.length > 0">
                <tr v-for="(product, index) in topProducts" :key="product.id || index" class="hover:bg-gray-50">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <span class="shrink-0 w-6 h-6 flex items-center justify-center text-xs font-medium text-gray-500 bg-gray-100 rounded-full">
                        {{ index + 1 }}
                      </span>
                      <span class="font-medium text-secondary-900 truncate">{{ product.name }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right text-gray-600">{{ formatNumber(product.quantity_sold || product.quantity) }}</td>
                  <td class="px-6 py-4 text-right font-medium text-secondary-900">{{ formatCurrency(product.revenue) }}</td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                    No products data available
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top Customers -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-secondary-900">Top Customers</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Orders</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Spent</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <template v-if="isLoading">
                <tr>
                  <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                    <RefreshCw class="w-6 h-6 animate-spin mx-auto mb-2" />
                    Loading...
                  </td>
                </tr>
              </template>
              <template v-else-if="topCustomers.length > 0">
                <tr v-for="(customer, index) in topCustomers" :key="customer.id || index" class="hover:bg-gray-50">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <span class="shrink-0 w-6 h-6 flex items-center justify-center text-xs font-medium text-gray-500 bg-gray-100 rounded-full">
                        {{ index + 1 }}
                      </span>
                      <div class="min-w-0">
                        <p class="font-medium text-secondary-900 truncate">{{ customer.name }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ customer.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right text-gray-600">{{ formatNumber(customer.order_count || customer.orders) }}</td>
                  <td class="px-6 py-4 text-right font-medium text-secondary-900">{{ formatCurrency(customer.total_spent || customer.total) }}</td>
                </tr>
              </template>
              <template v-else>
                <tr>
                  <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                    No customers data available
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
