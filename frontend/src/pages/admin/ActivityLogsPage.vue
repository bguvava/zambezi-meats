<script setup>
/**
 * Admin Activity Logs Page
 * View and manage system activity logs with bulk delete
 *
 * @requirement ADMIN-023 Create audit/activity logs page
 * @requirement ADMIN-024 Implement bulk delete for activity logs
 */
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useAdminLogsStore } from '@/stores/adminLogs'
import {
  FileText,
  Search,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  Trash2,
  X,
  AlertTriangle,
  Filter,
  Calendar,
  User,
  Activity,
  CheckSquare,
  Square,
  Download
} from 'lucide-vue-next'

const logsStore = useAdminLogsStore()

// Local state
const searchQuery = ref('')
const selectedAction = ref('')
const selectedUserId = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const showDeleteModal = ref(false)
const isDeleting = ref(false)

// Computed
const logs = computed(() => logsStore.logs)
const isLoading = computed(() => logsStore.isLoading)
const pagination = computed(() => logsStore.pagination)
const hasSelectedLogs = computed(() => logsStore.hasSelectedLogs)
const selectedCount = computed(() => logsStore.selectedLogsCount)
const isAllSelected = computed(() => logsStore.isAllSelected)
const actionTypes = computed(() => logsStore.actionTypes)

// Lifecycle
onMounted(async () => {
  await fetchLogs()
})

// Watch for filter changes
watch([selectedAction, selectedUserId, dateFrom, dateTo], () => {
  logsStore.setFilters({
    action: selectedAction.value || null,
    userId: selectedUserId.value || null,
    dateFrom: dateFrom.value || null,
    dateTo: dateTo.value || null
  })
  fetchLogs()
})

// Methods
async function fetchLogs(page = 1) {
  try {
    await logsStore.fetchLogs(page)
  } catch (err) {
    console.error('Failed to fetch logs:', err)
  }
}

function toggleLogSelection(logId) {
  logsStore.toggleLogSelection(logId)
}

function toggleAllSelection() {
  logsStore.toggleAllSelection()
}

function isLogSelected(logId) {
  return logsStore.selectedLogIds.includes(logId)
}

function openDeleteModal() {
  if (hasSelectedLogs.value) {
    showDeleteModal.value = true
  }
}

async function confirmDelete() {
  isDeleting.value = true
  try {
    await logsStore.deleteSelectedLogs()
    showDeleteModal.value = false
  } catch (err) {
    console.error('Failed to delete logs:', err)
  } finally {
    isDeleting.value = false
  }
}

function resetFilters() {
  selectedAction.value = ''
  selectedUserId.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  logsStore.resetFilters()
  fetchLogs()
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

function getActionLabel(action) {
  return logsStore.getActionLabel(action)
}

function getActionColor(action) {
  if (action.includes('created')) return 'bg-green-100 text-green-800'
  if (action.includes('updated')) return 'bg-blue-100 text-blue-800'
  if (action.includes('deleted')) return 'bg-red-100 text-red-800'
  if (action.includes('refunded')) return 'bg-orange-100 text-orange-800'
  return 'bg-gray-100 text-gray-800'
}

function goToPage(page) {
  fetchLogs(page)
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
          <span class="text-gray-900">Activity Logs</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Activity Logs</h1>
            <p class="text-gray-600 mt-1">Track and audit all system activities</p>
          </div>
          <div class="flex items-center gap-3 mt-4 md:mt-0">
            <button 
              v-if="hasSelectedLogs"
              @click="openDeleteModal" 
              class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
              <Trash2 class="w-4 h-4 mr-2" />
              Delete Selected ({{ selectedCount }})
            </button>
            <button @click="fetchLogs()" 
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
              :disabled="isLoading">
              <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" />
              Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
          <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Action Type</label>
            <select 
              v-model="selectedAction"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
              <option value="">All Actions</option>
              <option v-for="action in actionTypes" :key="action.value" :value="action.value">
                {{ action.label }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
            <input 
              v-model="dateFrom"
              type="date"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
            <input 
              v-model="dateTo"
              type="date"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
            />
          </div>
          <div class="self-end">
            <button @click="resetFilters" 
              class="inline-flex items-center px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors">
              <X class="w-4 h-4 mr-1" />
              Clear
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-16">
        <div class="text-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent"></div>
          <p class="mt-4 text-gray-600">Loading activity logs...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="logsStore.error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
          <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading logs</h3>
            <p class="text-red-600 text-sm mt-1">{{ logsStore.error }}</p>
          </div>
        </div>
        <button @click="fetchLogs()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
          Try Again
        </button>
      </div>

      <!-- Logs Table -->
      <div v-else class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left">
                  <button 
                    @click="toggleAllSelection"
                    class="p-1 hover:bg-gray-200 rounded transition-colors">
                    <CheckSquare v-if="isAllSelected && logs.length > 0" class="w-5 h-5 text-[#CF0D0F]" />
                    <Square v-else class="w-5 h-5 text-gray-400" />
                  </button>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="!logs.length">
                <td colspan="6" class="px-6 py-16 text-center">
                  <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <FileText class="w-8 h-8 text-gray-400" />
                  </div>
                  <p class="text-gray-500 mb-2">No activity logs found</p>
                  <p class="text-sm text-gray-400">Logs matching your filters will appear here</p>
                </td>
              </tr>
              <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-4">
                  <button 
                    @click="toggleLogSelection(log.id)"
                    class="p-1 hover:bg-gray-200 rounded transition-colors">
                    <CheckSquare v-if="isLogSelected(log.id)" class="w-5 h-5 text-[#CF0D0F]" />
                    <Square v-else class="w-5 h-5 text-gray-400" />
                  </button>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(log.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                      <User class="w-4 h-4 text-gray-500" />
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900">{{ log.user?.name || 'System' }}</p>
                      <p class="text-xs text-gray-500">{{ log.user?.email || '' }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex px-2 py-1 text-xs font-medium rounded-full', getActionColor(log.action)]">
                    {{ getActionLabel(log.action) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <p class="text-sm text-gray-600 max-w-md truncate" :title="log.description || log.details">
                    {{ log.description || log.details || '-' }}
                  </p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ log.ip_address || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.lastPage > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Showing {{ ((pagination.currentPage - 1) * pagination.perPage) + 1 }} to {{ Math.min(pagination.currentPage * pagination.perPage, pagination.total) }} of {{ pagination.total }} logs
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

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full">
            <div class="px-6 py-4">
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                  <AlertTriangle class="w-6 h-6 text-red-600" />
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-semibold text-gray-900">Delete Activity Logs</h3>
                  <p class="text-sm text-gray-500 mt-1">
                    Are you sure you want to delete {{ selectedCount }} selected log(s)? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
              <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="confirmDelete"
                :disabled="isDeleting"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                {{ isDeleting ? 'Deleting...' : 'Delete' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
