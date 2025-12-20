<script setup>
/**
 * ActivityPage.vue (Staff)
 * Staff activity log and performance tracking page
 */
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useStaffActivityStore } from '@/stores/staffActivity'
import {
  Activity,
  Search,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  AlertTriangle,
  Calendar,
  ClipboardList,
  Truck,
  Package,
  Boxes,
  Trash2,
  CheckCircle,
  Camera,
  FileText,
  TrendingUp,
  Clock,
  Award
} from 'lucide-vue-next'

const activityStore = useStaffActivityStore()

// Local state
const selectedType = ref('')
const selectedDateRange = ref('week')

// Date range options
const dateRangeOptions = [
  { value: 'today', label: 'Today' },
  { value: 'week', label: 'This Week' },
  { value: 'month', label: 'This Month' },
  { value: 'all', label: 'All Time' }
]

// Activity type icons
const activityIcons = {
  order_status_updated: ClipboardList,
  order_note_added: FileText,
  delivery_started: Truck,
  delivery_completed: CheckCircle,
  pod_uploaded: Camera,
  pickup_completed: Package,
  stock_updated: Boxes,
  waste_logged: Trash2
}

// Computed
const isLoading = computed(() => activityStore.isLoading)
const activities = computed(() => activityStore.activities)
const performanceStats = computed(() => activityStore.performanceStats)
const activityTypes = computed(() => activityStore.activityTypes)
const activityStats = computed(() => activityStore.activityStats)

// Lifecycle
onMounted(async () => {
  await fetchAll()
})

// Watch for filter changes
watch([selectedType, selectedDateRange], () => {
  activityStore.setFilters({
    type: selectedType.value,
    dateRange: selectedDateRange.value
  })
  fetchAll()
})

// Methods
async function fetchAll() {
  try {
    await activityStore.fetchAll({
      type: selectedType.value,
      dateRange: selectedDateRange.value
    })
  } catch (err) {
    console.error('Failed to fetch data:', err)
  }
}

function getActivityIcon(type) {
  return activityIcons[type] || Activity
}

function getActivityColor(type) {
  const colors = {
    order_status_updated: 'bg-blue-100 text-blue-600',
    order_note_added: 'bg-gray-100 text-gray-600',
    delivery_started: 'bg-indigo-100 text-indigo-600',
    delivery_completed: 'bg-green-100 text-green-600',
    pod_uploaded: 'bg-purple-100 text-purple-600',
    pickup_completed: 'bg-teal-100 text-teal-600',
    stock_updated: 'bg-yellow-100 text-yellow-600',
    waste_logged: 'bg-red-100 text-red-600'
  }
  return colors[type] || 'bg-gray-100 text-gray-600'
}

function getActivityLabel(type) {
  const found = activityTypes.value.find(t => t.value === type)
  return found?.label || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
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

function formatRelativeTime(dateString) {
  if (!dateString) return '-'
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)
  
  if (minutes < 1) return 'Just now'
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  if (days < 7) return `${days}d ago`
  return formatDate(dateString)
}

function goToPage(page) {
  activityStore.setPage(page)
  activityStore.fetchActivityLog()
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
          <span class="text-gray-900">My Activity</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">My Activity</h1>
            <p class="text-gray-600 mt-1">Track your work history and performance</p>
          </div>
          <button @click="fetchAll" 
            class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors"
            :disabled="isLoading">
            <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" />
            Refresh
          </button>
        </div>
      </div>

      <!-- Performance Stats -->
      <div v-if="performanceStats" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Actions</p>
              <p class="text-2xl font-bold text-gray-900">{{ performanceStats.total_actions || activityStats.total }}</p>
            </div>
            <Activity class="w-8 h-8 text-[#CF0D0F]" />
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Orders Processed</p>
              <p class="text-2xl font-bold text-blue-600">{{ performanceStats.orders_processed || 0 }}</p>
            </div>
            <ClipboardList class="w-8 h-8 text-blue-600" />
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Deliveries Completed</p>
              <p class="text-2xl font-bold text-green-600">{{ performanceStats.deliveries_completed || 0 }}</p>
            </div>
            <Truck class="w-8 h-8 text-green-600" />
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Avg. Delivery Time</p>
              <p class="text-2xl font-bold text-purple-600">{{ performanceStats.avg_delivery_time || '-' }}</p>
            </div>
            <Clock class="w-8 h-8 text-purple-600" />
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
          <select 
            v-model="selectedType"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
            <option value="">All Activity Types</option>
            <option v-for="type in activityTypes" :key="type.value" :value="type.value">
              {{ type.label }}
            </option>
          </select>
          <select 
            v-model="selectedDateRange"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
            <option v-for="option in dateRangeOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-16">
        <div class="text-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent"></div>
          <p class="mt-4 text-gray-600">Loading activity...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="activityStore.error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
          <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading activity</h3>
            <p class="text-red-600 text-sm mt-1">{{ activityStore.error }}</p>
          </div>
        </div>
        <button @click="fetchAll" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
          Try Again
        </button>
      </div>

      <!-- Activity Timeline -->
      <div v-else class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Activity Timeline</h2>
        </div>

        <div v-if="!activities.length" class="p-12 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <Activity class="w-8 h-8 text-gray-400" />
          </div>
          <p class="text-gray-500 mb-2">No activity found</p>
          <p class="text-sm text-gray-400">Your work history will appear here</p>
        </div>

        <div v-else class="divide-y divide-gray-100">
          <div v-for="activity in activities" :key="activity.id" 
            class="px-6 py-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-start">
              <!-- Icon -->
              <div :class="[
                'w-10 h-10 rounded-full flex items-center justify-center mr-4 flex-shrink-0',
                getActivityColor(activity.type)
              ]">
                <component :is="getActivityIcon(activity.type)" class="w-5 h-5" />
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-gray-900">
                    {{ getActivityLabel(activity.type) }}
                  </p>
                  <span class="text-xs text-gray-500">{{ formatRelativeTime(activity.created_at) }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">{{ activity.description }}</p>
                <div v-if="activity.metadata" class="mt-2 flex flex-wrap gap-2">
                  <span v-if="activity.metadata.order_number" 
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                    Order #{{ activity.metadata.order_number }}
                  </span>
                  <span v-if="activity.metadata.product_name" 
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                    {{ activity.metadata.product_name }}
                  </span>
                  <span v-if="activity.metadata.status_to" 
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                    → {{ activity.metadata.status_to }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="activityStore.pagination.totalPages > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Showing page {{ activityStore.pagination.currentPage }} of {{ activityStore.pagination.totalPages }}
          </p>
          <div class="flex items-center space-x-2">
            <button 
              @click="goToPage(activityStore.pagination.currentPage - 1)"
              :disabled="activityStore.pagination.currentPage === 1"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <ChevronLeft class="w-4 h-4" />
            </button>
            <button 
              @click="goToPage(activityStore.pagination.currentPage + 1)"
              :disabled="activityStore.pagination.currentPage === activityStore.pagination.totalPages"
              class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
