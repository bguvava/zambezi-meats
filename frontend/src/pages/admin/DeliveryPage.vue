<script setup>
/**
 * Admin Delivery Management Page
 * 
 * Full delivery management with zones, assignments, POD, and reporting
 * @requirements DEL-001 to DEL-017
 */
import { ref, computed, onMounted, watch } from 'vue'
import { useAdminDeliveryStore } from '@/stores/adminDelivery'
import { useAdminCategoriesStore } from '@/stores/adminCategories'
import { 
  Truck, 
  MapPin, 
  Clock, 
  AlertTriangle, 
  CheckCircle, 
  Package,
  Search,
  Filter,
  Plus,
  Edit,
  Trash2,
  Eye,
  Download,
  RefreshCw,
  Settings,
  Map,
  FileText,
  UserCheck,
  Camera,
  PenTool,
  X,
  ChevronLeft,
  ChevronRight,
  Calendar
} from 'lucide-vue-next'

// Store
const deliveryStore = useAdminDeliveryStore()

// Active tab
const activeTab = ref('dashboard')
const tabs = [
  { id: 'dashboard', label: 'Dashboard', icon: Package },
  { id: 'deliveries', label: 'All Deliveries', icon: Truck },
  { id: 'zones', label: 'Zones', icon: MapPin },
  { id: 'settings', label: 'Settings', icon: Settings }
]

// Modals
const showDeliveryModal = ref(false)
const showZoneModal = ref(false)
const showAssignModal = ref(false)
const showPodModal = ref(false)
const showIssueModal = ref(false)

// Form states
const zoneForm = ref({
  id: null,
  name: '',
  postcodes: '',
  min_order: 100,
  delivery_fee: 0,
  is_active: true
})

const assignForm = ref({
  delivery_id: null,
  staff_id: '',
  reason: ''
})

const issueForm = ref({
  delivery_id: null,
  resolution: '',
  status: 'resolved'
})

const settingsForm = ref({
  free_delivery_threshold: 100,
  per_km_rate: 0.15,
  base_delivery_fee: 9.95,
  max_delivery_distance: 50
})

// Date filter
const dateFrom = ref('')
const dateTo = ref('')

// Computed
const isLoading = computed(() => 
  deliveryStore.loading.dashboard || 
  deliveryStore.loading.deliveries ||
  deliveryStore.loading.zones ||
  deliveryStore.loading.settings
)

// Status colors
const statusColors = {
  pending: 'bg-yellow-100 text-yellow-800',
  assigned: 'bg-blue-100 text-blue-800',
  out_for_delivery: 'bg-purple-100 text-purple-800',
  delivered: 'bg-green-100 text-green-800',
  failed: 'bg-red-100 text-red-800',
  cancelled: 'bg-gray-100 text-gray-800'
}

const statusLabels = {
  pending: 'Pending',
  assigned: 'Assigned',
  out_for_delivery: 'Out for Delivery',
  delivered: 'Delivered',
  failed: 'Failed',
  cancelled: 'Cancelled'
}

// Methods
const loadDashboard = async () => {
  await deliveryStore.fetchDashboard()
}

const loadDeliveries = async (page = 1) => {
  await deliveryStore.fetchDeliveries(page)
}

const loadZones = async () => {
  await deliveryStore.fetchZones()
}

const loadSettings = async () => {
  await deliveryStore.fetchSettings()
  settingsForm.value = { ...deliveryStore.settings }
}

const loadStaffList = async () => {
  await deliveryStore.fetchStaffList()
}

// Filter handlers
const applyFilters = () => {
  deliveryStore.setFilters({
    date_from: dateFrom.value,
    date_to: dateTo.value
  })
  loadDeliveries()
}

const clearFilters = () => {
  dateFrom.value = ''
  dateTo.value = ''
  deliveryStore.resetFilters()
  loadDeliveries()
}

const handleStatusFilter = (status) => {
  deliveryStore.setFilters({ status })
  loadDeliveries()
}

const handleSearch = (event) => {
  deliveryStore.setFilters({ search: event.target.value })
  loadDeliveries()
}

// Delivery detail
const viewDelivery = async (delivery) => {
  await deliveryStore.fetchDelivery(delivery.id)
  showDeliveryModal.value = true
}

const closeDeliveryModal = () => {
  showDeliveryModal.value = false
  deliveryStore.clearCurrentDelivery()
}

// Assignment
const openAssignModal = (delivery) => {
  assignForm.value = {
    delivery_id: delivery.id,
    staff_id: delivery.assigned_staff_id || '',
    reason: ''
  }
  showAssignModal.value = true
}

const submitAssign = async () => {
  try {
    if (assignForm.value.reason) {
      await deliveryStore.reassignDelivery(
        assignForm.value.delivery_id,
        assignForm.value.staff_id,
        assignForm.value.reason
      )
    } else {
      await deliveryStore.assignDelivery(
        assignForm.value.delivery_id,
        assignForm.value.staff_id
      )
    }
    showAssignModal.value = false
    loadDeliveries()
  } catch (error) {
    console.error('Assignment failed:', error)
  }
}

// Proof of Delivery
const viewPod = async (delivery) => {
  try {
    await deliveryStore.fetchProofOfDelivery(delivery.id)
    showPodModal.value = true
  } catch (error) {
    console.error('Failed to load POD:', error)
  }
}

const downloadPod = async () => {
  if (deliveryStore.currentDelivery) {
    await deliveryStore.downloadPodPdf(deliveryStore.currentDelivery.id)
  }
}

// Issue handling
const openIssueModal = (delivery) => {
  issueForm.value = {
    delivery_id: delivery.id,
    resolution: '',
    status: 'resolved'
  }
  showIssueModal.value = true
}

const submitIssue = async () => {
  try {
    await deliveryStore.resolveIssue(
      issueForm.value.delivery_id,
      issueForm.value.resolution,
      issueForm.value.status
    )
    showIssueModal.value = false
    loadDeliveries()
  } catch (error) {
    console.error('Issue resolution failed:', error)
  }
}

// Zone management
const openZoneModal = (zone = null) => {
  if (zone) {
    zoneForm.value = {
      id: zone.id,
      name: zone.name,
      postcodes: Array.isArray(zone.postcodes) ? zone.postcodes.join(', ') : zone.postcodes,
      min_order: zone.min_order,
      delivery_fee: zone.delivery_fee,
      is_active: zone.is_active
    }
  } else {
    zoneForm.value = {
      id: null,
      name: '',
      postcodes: '',
      min_order: 100,
      delivery_fee: 0,
      is_active: true
    }
  }
  showZoneModal.value = true
}

const submitZone = async () => {
  try {
    const data = {
      ...zoneForm.value,
      postcodes: zoneForm.value.postcodes.split(',').map(p => p.trim()).filter(p => p)
    }
    
    if (zoneForm.value.id) {
      await deliveryStore.updateZone(zoneForm.value.id, data)
    } else {
      await deliveryStore.createZone(data)
    }
    showZoneModal.value = false
    loadZones()
  } catch (error) {
    console.error('Zone save failed:', error)
  }
}

const deleteZone = async (zone) => {
  if (confirm(`Are you sure you want to delete zone "${zone.name}"?`)) {
    try {
      await deliveryStore.deleteZone(zone.id)
    } catch (error) {
      console.error('Zone deletion failed:', error)
    }
  }
}

// Settings
const saveSettings = async () => {
  try {
    await deliveryStore.updateSettings(settingsForm.value)
    alert('Settings saved successfully!')
  } catch (error) {
    console.error('Settings save failed:', error)
  }
}

// Export
const exportDeliveries = async () => {
  try {
    await deliveryStore.exportDeliveries({ format: 'json' })
  } catch (error) {
    console.error('Export failed:', error)
  }
}

// Pagination
const goToPage = (page) => {
  if (page >= 1 && page <= deliveryStore.pagination.lastPage) {
    loadDeliveries(page)
  }
}

// Format helpers
const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-AU', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-AU', {
    style: 'currency',
    currency: 'AUD'
  }).format(amount || 0)
}

// Watch tab changes
watch(activeTab, (newTab) => {
  switch (newTab) {
    case 'dashboard':
      loadDashboard()
      break
    case 'deliveries':
      loadDeliveries()
      loadStaffList()
      break
    case 'zones':
      loadZones()
      break
    case 'settings':
      loadSettings()
      break
  }
})

// Initialize
onMounted(() => {
  loadDashboard()
  loadStaffList()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
      <div class="px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Delivery Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage deliveries, zones, and settings</p>
          </div>
          <div class="flex items-center space-x-3">
            <button
              @click="exportDeliveries"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
            >
              <Download class="w-4 h-4 mr-2" />
              Export
            </button>
            <button
              @click="activeTab === 'zones' ? openZoneModal() : null"
              v-if="activeTab === 'zones'"
              class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-[#CF0D0F] hover:bg-[#B00C0E] transition-colors"
            >
              <Plus class="w-4 h-4 mr-2" />
              Add Zone
            </button>
          </div>
        </div>

        <!-- Tabs -->
        <div class="mt-4 flex space-x-1 border-b border-gray-200 -mb-px">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'flex items-center px-4 py-3 text-sm font-medium border-b-2 transition-colors',
              activeTab === tab.id
                ? 'border-[#CF0D0F] text-[#CF0D0F]'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <component :is="tab.icon" class="w-4 h-4 mr-2" />
            {{ tab.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6">
      <!-- Loading -->
      <div v-if="isLoading" class="flex justify-center py-12">
        <RefreshCw class="w-8 h-8 text-gray-400 animate-spin" />
      </div>

      <!-- Dashboard Tab -->
      <div v-else-if="activeTab === 'dashboard'" class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <Truck class="w-6 h-6 text-blue-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">Today's Deliveries</p>
                <p class="text-2xl font-bold text-gray-900">{{ deliveryStore.dashboard.todaysDeliveries }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-lg bg-yellow-50 flex items-center justify-center">
                <Clock class="w-6 h-6 text-yellow-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">Pending</p>
                <p class="text-2xl font-bold text-gray-900">{{ deliveryStore.dashboard.pending }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
                <Truck class="w-6 h-6 text-purple-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">In Progress</p>
                <p class="text-2xl font-bold text-gray-900">{{ deliveryStore.dashboard.inProgress }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <CheckCircle class="w-6 h-6 text-green-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">Completed</p>
                <p class="text-2xl font-bold text-gray-900">{{ deliveryStore.dashboard.completed }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center">
                <AlertTriangle class="w-6 h-6 text-red-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm text-gray-500">Issues</p>
                <p class="text-2xl font-bold text-gray-900">{{ deliveryStore.dashboard.issues }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Deliveries -->
        <div class="bg-white rounded-xl border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Recent Deliveries</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr 
                  v-for="delivery in deliveryStore.dashboard.recentDeliveries" 
                  :key="delivery.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 text-sm font-medium text-gray-900">
                    {{ delivery.order_number }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    {{ delivery.customer_name }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                    {{ delivery.address }}
                  </td>
                  <td class="px-6 py-4">
                    <span :class="[statusColors[delivery.status], 'px-2 py-1 rounded-full text-xs font-medium']">
                      {{ statusLabels[delivery.status] || delivery.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    {{ delivery.assigned_staff?.name || '-' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500">
                    {{ formatDate(delivery.updated_at) }}
                  </td>
                </tr>
                <tr v-if="!deliveryStore.dashboard.recentDeliveries?.length">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    No recent deliveries
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Deliveries Tab -->
      <div v-else-if="activeTab === 'deliveries'" class="space-y-6">
        <!-- Filters -->
        <div class="bg-white rounded-xl border border-gray-200 p-4">
          <div class="flex flex-wrap gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[200px]">
              <div class="relative">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                  type="text"
                  placeholder="Search orders, customers..."
                  @input="handleSearch"
                  class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                />
              </div>
            </div>

            <!-- Status Filter -->
            <select
              @change="handleStatusFilter($event.target.value)"
              class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="assigned">Assigned</option>
              <option value="out_for_delivery">Out for Delivery</option>
              <option value="delivered">Delivered</option>
              <option value="failed">Failed</option>
            </select>

            <!-- Date Range -->
            <div class="flex items-center gap-2">
              <input
                v-model="dateFrom"
                type="date"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
              />
              <span class="text-gray-400">to</span>
              <input
                v-model="dateTo"
                type="date"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
              />
            </div>

            <!-- Filter Buttons -->
            <button
              @click="applyFilters"
              class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg text-sm font-medium hover:bg-[#B00C0E] transition-colors"
            >
              <Filter class="w-4 h-4 inline mr-1" />
              Apply
            </button>
            <button
              @click="clearFilters"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Clear
            </button>
          </div>
        </div>

        <!-- Deliveries Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr 
                  v-for="delivery in deliveryStore.deliveries" 
                  :key="delivery.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4">
                    <span class="font-medium text-gray-900">{{ delivery.order_number }}</span>
                    <span v-if="delivery.has_issue" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                      <AlertTriangle class="w-3 h-3 mr-1" />
                      Issue
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    {{ delivery.customer_name }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" :title="delivery.address">
                    {{ delivery.address }}
                  </td>
                  <td class="px-6 py-4">
                    <span :class="[statusColors[delivery.status], 'px-2 py-1 rounded-full text-xs font-medium']">
                      {{ statusLabels[delivery.status] || delivery.status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    {{ delivery.assigned_staff?.name || '-' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500">
                    {{ formatDate(delivery.scheduled_date) }}
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-end space-x-2">
                      <button
                        @click="viewDelivery(delivery)"
                        class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors"
                        title="View Details"
                      >
                        <Eye class="w-4 h-4" />
                      </button>
                      <button
                        @click="openAssignModal(delivery)"
                        class="p-1.5 text-gray-400 hover:text-green-600 rounded-lg hover:bg-green-50 transition-colors"
                        title="Assign/Reassign"
                      >
                        <UserCheck class="w-4 h-4" />
                      </button>
                      <button
                        v-if="delivery.has_pod"
                        @click="viewPod(delivery)"
                        class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition-colors"
                        title="View POD"
                      >
                        <Camera class="w-4 h-4" />
                      </button>
                      <button
                        v-if="delivery.has_issue"
                        @click="openIssueModal(delivery)"
                        class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                        title="Resolve Issue"
                      >
                        <AlertTriangle class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!deliveryStore.deliveries.length">
                  <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <Truck class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                    <p>No deliveries found</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="deliveryStore.pagination.lastPage > 1" class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-500">
                Showing {{ ((deliveryStore.pagination.currentPage - 1) * deliveryStore.pagination.perPage) + 1 }} 
                to {{ Math.min(deliveryStore.pagination.currentPage * deliveryStore.pagination.perPage, deliveryStore.pagination.total) }}
                of {{ deliveryStore.pagination.total }} deliveries
              </p>
              <div class="flex items-center space-x-2">
                <button
                  @click="goToPage(deliveryStore.pagination.currentPage - 1)"
                  :disabled="deliveryStore.pagination.currentPage === 1"
                  class="p-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronLeft class="w-4 h-4" />
                </button>
                <span class="text-sm text-gray-600">
                  Page {{ deliveryStore.pagination.currentPage }} of {{ deliveryStore.pagination.lastPage }}
                </span>
                <button
                  @click="goToPage(deliveryStore.pagination.currentPage + 1)"
                  :disabled="deliveryStore.pagination.currentPage === deliveryStore.pagination.lastPage"
                  class="p-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronRight class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Zones Tab -->
      <div v-else-if="activeTab === 'zones'" class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Zone Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Postcodes</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Min Order</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery Fee</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr 
                  v-for="zone in deliveryStore.zones" 
                  :key="zone.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-6 py-4 font-medium text-gray-900">
                    {{ zone.name }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    <div class="flex flex-wrap gap-1">
                      <span 
                        v-for="postcode in (Array.isArray(zone.postcodes) ? zone.postcodes.slice(0, 3) : zone.postcodes?.split(',').slice(0, 3))" 
                        :key="postcode"
                        class="px-2 py-0.5 bg-gray-100 rounded text-xs"
                      >
                        {{ postcode }}
                      </span>
                      <span 
                        v-if="(Array.isArray(zone.postcodes) ? zone.postcodes.length : zone.postcodes?.split(',').length) > 3"
                        class="px-2 py-0.5 bg-gray-100 rounded text-xs text-gray-500"
                      >
                        +{{ (Array.isArray(zone.postcodes) ? zone.postcodes.length : zone.postcodes?.split(',').length) - 3 }} more
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    {{ formatCurrency(zone.min_order) }}
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span v-if="zone.delivery_fee === 0" class="font-semibold text-green-600">FREE</span>
                    <span v-else class="text-gray-600">{{ formatCurrency(zone.delivery_fee) }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <span 
                      :class="zone.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                      class="px-2 py-1 rounded-full text-xs font-medium"
                    >
                      {{ zone.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-end space-x-2">
                      <button
                        @click="openZoneModal(zone)"
                        class="p-1.5 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors"
                        title="Edit Zone"
                      >
                        <Edit class="w-4 h-4" />
                      </button>
                      <button
                        @click="deleteZone(zone)"
                        class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                        title="Delete Zone"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!deliveryStore.zones.length">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                    <MapPin class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                    <p>No delivery zones configured</p>
                    <button
                      @click="openZoneModal()"
                      class="mt-3 text-[#CF0D0F] hover:underline text-sm font-medium"
                    >
                      Add your first zone
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Zone Note -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <p class="text-sm text-blue-700">
            <strong>Note:</strong> Orders outside defined zones will use the per-kilometer rate from the store location:
            <span class="font-medium">6/1053 Old Princes Highway, Engadine, NSW 2233</span>
          </p>
        </div>
      </div>

      <!-- Settings Tab -->
      <div v-else-if="activeTab === 'settings'" class="max-w-2xl">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Delivery Fee Settings</h3>
          
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Free Delivery Threshold (AUD)
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                <input
                  v-model.number="settingsForm.free_delivery_threshold"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                />
              </div>
              <p class="mt-1 text-xs text-gray-500">Orders above this amount qualify for free delivery in designated zones</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Base Delivery Fee (AUD)
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                <input
                  v-model.number="settingsForm.base_delivery_fee"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                />
              </div>
              <p class="mt-1 text-xs text-gray-500">Default delivery fee for orders below threshold</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Per-Kilometer Rate (AUD)
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                <input
                  v-model.number="settingsForm.per_km_rate"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                />
              </div>
              <p class="mt-1 text-xs text-gray-500">Fee per kilometer for deliveries outside defined zones</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Maximum Delivery Distance (km)
              </label>
              <input
                v-model.number="settingsForm.max_delivery_distance"
                type="number"
                min="1"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
              />
              <p class="mt-1 text-xs text-gray-500">Maximum distance from store for deliveries</p>
            </div>

            <div class="pt-4 border-t border-gray-200">
              <button
                @click="saveSettings"
                :disabled="deliveryStore.loading.action"
                class="w-full py-2.5 px-4 bg-[#CF0D0F] text-white rounded-lg font-medium hover:bg-[#B00C0E] transition-colors disabled:opacity-50"
              >
                {{ deliveryStore.loading.action ? 'Saving...' : 'Save Settings' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delivery Detail Modal -->
    <Teleport to="body">
      <div v-if="showDeliveryModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50" @click="closeDeliveryModal"></div>
          <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Delivery Details</h3>
              <button @click="closeDeliveryModal" class="p-2 hover:bg-gray-100 rounded-lg">
                <X class="w-5 h-5 text-gray-400" />
              </button>
            </div>
            <div class="p-6" v-if="deliveryStore.currentDelivery">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Order Number</p>
                  <p class="font-medium">{{ deliveryStore.currentDelivery.order_number }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Status</p>
                  <span :class="[statusColors[deliveryStore.currentDelivery.status], 'px-2 py-1 rounded-full text-xs font-medium']">
                    {{ statusLabels[deliveryStore.currentDelivery.status] }}
                  </span>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Customer</p>
                  <p class="font-medium">{{ deliveryStore.currentDelivery.customer_name }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Phone</p>
                  <p class="font-medium">{{ deliveryStore.currentDelivery.customer_phone || '-' }}</p>
                </div>
                <div class="col-span-2">
                  <p class="text-sm text-gray-500">Delivery Address</p>
                  <p class="font-medium">{{ deliveryStore.currentDelivery.address }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Assigned Staff</p>
                  <p class="font-medium">{{ deliveryStore.currentDelivery.assigned_staff?.name || 'Unassigned' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Scheduled Date</p>
                  <p class="font-medium">{{ formatDate(deliveryStore.currentDelivery.scheduled_date) }}</p>
                </div>
              </div>
              
              <!-- Notes -->
              <div v-if="deliveryStore.currentDelivery.notes" class="mt-4 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Notes</p>
                <p class="text-sm text-gray-700">{{ deliveryStore.currentDelivery.notes }}</p>
              </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button
                @click="closeDeliveryModal"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Zone Modal -->
    <Teleport to="body">
      <div v-if="showZoneModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50" @click="showZoneModal = false"></div>
          <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">
                {{ zoneForm.id ? 'Edit Zone' : 'Add Zone' }}
              </h3>
              <button @click="showZoneModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                <X class="w-5 h-5 text-gray-400" />
              </button>
            </div>
            <form @submit.prevent="submitZone" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Zone Name *</label>
                <input
                  v-model="zoneForm.name"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                  placeholder="e.g., Engadine Local"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Postcodes *</label>
                <textarea
                  v-model="zoneForm.postcodes"
                  required
                  rows="2"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                  placeholder="2233, 2234, 2235"
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">Comma-separated list of postcodes</p>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Min Order ($)</label>
                  <input
                    v-model.number="zoneForm.min_order"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Fee ($)</label>
                  <input
                    v-model.number="zoneForm.delivery_fee"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                  />
                  <p class="mt-1 text-xs text-gray-500">Set to 0 for free delivery</p>
                </div>
              </div>
              <div class="flex items-center">
                <input
                  v-model="zoneForm.is_active"
                  type="checkbox"
                  id="is_active"
                  class="w-4 h-4 text-[#CF0D0F] border-gray-300 rounded focus:ring-[#CF0D0F]"
                />
                <label for="is_active" class="ml-2 text-sm text-gray-700">Active zone</label>
              </div>
              <div class="pt-4 border-t border-gray-200 flex justify-end space-x-3">
                <button
                  type="button"
                  @click="showZoneModal = false"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="deliveryStore.loading.action"
                  class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg text-sm font-medium hover:bg-[#B00C0E] disabled:opacity-50"
                >
                  {{ deliveryStore.loading.action ? 'Saving...' : 'Save Zone' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Assign Modal -->
    <Teleport to="body">
      <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50" @click="showAssignModal = false"></div>
          <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Assign Delivery</h3>
              <button @click="showAssignModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                <X class="w-5 h-5 text-gray-400" />
              </button>
            </div>
            <form @submit.prevent="submitAssign" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign to Staff *</label>
                <select
                  v-model="assignForm.staff_id"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                >
                  <option value="">Select staff member</option>
                  <option v-for="staff in deliveryStore.staffList" :key="staff.id" :value="staff.id">
                    {{ staff.name }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason (for reassignment)</label>
                <textarea
                  v-model="assignForm.reason"
                  rows="2"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                  placeholder="Optional reason for reassignment"
                ></textarea>
              </div>
              <div class="pt-4 border-t border-gray-200 flex justify-end space-x-3">
                <button
                  type="button"
                  @click="showAssignModal = false"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="deliveryStore.loading.action"
                  class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg text-sm font-medium hover:bg-[#B00C0E] disabled:opacity-50"
                >
                  {{ deliveryStore.loading.action ? 'Assigning...' : 'Assign' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- POD Modal -->
    <Teleport to="body">
      <div v-if="showPodModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50" @click="showPodModal = false"></div>
          <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Proof of Delivery</h3>
              <button @click="showPodModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                <X class="w-5 h-5 text-gray-400" />
              </button>
            </div>
            <div class="p-6" v-if="deliveryStore.proofOfDelivery">
              <!-- Signature -->
              <div v-if="deliveryStore.proofOfDelivery.signature_url" class="mb-6">
                <p class="text-sm text-gray-500 mb-2">Signature</p>
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                  <img 
                    :src="deliveryStore.proofOfDelivery.signature_url" 
                    alt="Signature" 
                    class="max-h-32 mx-auto"
                  />
                </div>
              </div>

              <!-- Photo -->
              <div v-if="deliveryStore.proofOfDelivery.photo_url" class="mb-6">
                <p class="text-sm text-gray-500 mb-2">Photo</p>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                  <img 
                    :src="deliveryStore.proofOfDelivery.photo_url" 
                    alt="Delivery Photo" 
                    class="w-full"
                  />
                </div>
              </div>

              <!-- Details -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Received By</p>
                  <p class="font-medium">{{ deliveryStore.proofOfDelivery.receiver_name || '-' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Delivered At</p>
                  <p class="font-medium">{{ formatDate(deliveryStore.proofOfDelivery.delivered_at) }}</p>
                </div>
              </div>

              <div v-if="deliveryStore.proofOfDelivery.notes" class="mt-4 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">Notes</p>
                <p class="text-sm text-gray-700">{{ deliveryStore.proofOfDelivery.notes }}</p>
              </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button
                @click="downloadPod"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50"
              >
                <Download class="w-4 h-4 inline mr-1" />
                Download PDF
              </button>
              <button
                @click="showPodModal = false"
                class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg text-sm font-medium hover:bg-[#B00C0E]"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Issue Modal -->
    <Teleport to="body">
      <div v-if="showIssueModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50" @click="showIssueModal = false"></div>
          <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">Resolve Delivery Issue</h3>
              <button @click="showIssueModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
                <X class="w-5 h-5 text-gray-400" />
              </button>
            </div>
            <form @submit.prevent="submitIssue" class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Resolution *</label>
                <textarea
                  v-model="issueForm.resolution"
                  required
                  rows="3"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                  placeholder="Describe how the issue was resolved..."
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select
                  v-model="issueForm.status"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#CF0D0F]/20 focus:border-[#CF0D0F]"
                >
                  <option value="resolved">Resolved</option>
                  <option value="rescheduled">Rescheduled</option>
                  <option value="refunded">Refunded</option>
                </select>
              </div>
              <div class="pt-4 border-t border-gray-200 flex justify-end space-x-3">
                <button
                  type="button"
                  @click="showIssueModal = false"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="deliveryStore.loading.action"
                  class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg text-sm font-medium hover:bg-[#B00C0E] disabled:opacity-50"
                >
                  {{ deliveryStore.loading.action ? 'Saving...' : 'Save Resolution' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
/* Custom scrollbar for tables */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>
