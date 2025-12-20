<script setup>
/**
 * Admin Staff Management Page
 * Create, edit, and manage staff accounts
 *
 * @requirement ADMIN-018 Create staff management page
 * @requirement ADMIN-019 Create/edit staff accounts
 * @requirement ADMIN-020 Activate/deactivate staff
 * @requirement ADMIN-021 View staff activity
 */
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useAdminStaffStore } from '@/stores/adminStaff'
import {
  Users,
  Plus,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  Edit,
  Trash2,
  X,
  AlertTriangle,
  Search,
  Eye,
  UserCheck,
  UserX,
  Shield,
  ShieldCheck,
  Clock,
  Activity
} from 'lucide-vue-next'

const staffStore = useAdminStaffStore()

// Local state
const showStaffModal = ref(false)
const showDeleteModal = ref(false)
const showActivityModal = ref(false)
const isEditing = ref(false)
const staffToDelete = ref(null)
const isSubmitting = ref(false)
const searchQuery = ref('')

// Staff form data
const staffForm = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'staff',
  phone: '',
  is_active: true
})

// Computed
const staff = computed(() => staffStore.staff)
const isLoading = computed(() => staffStore.isLoading)
const pagination = computed(() => staffStore.pagination)
const currentStaff = computed(() => staffStore.currentStaff)
const staffActivity = computed(() => staffStore.staffActivity)
const staffStats = computed(() => staffStore.staffStats)

// Watch search query
let searchTimeout
watch(searchQuery, (newValue) => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    staffStore.setFilters({ search: newValue })
    fetchStaff()
  }, 300)
})

// Lifecycle
onMounted(async () => {
  await fetchStaff()
})

// Methods
async function fetchStaff() {
  try {
    await staffStore.fetchStaff()
  } catch (err) {
    console.error('Failed to fetch staff:', err)
  }
}

function openCreateModal() {
  isEditing.value = false
  resetForm()
  showStaffModal.value = true
}

function openEditModal(member) {
  isEditing.value = true
  staffForm.value = {
    id: member.id,
    name: member.name || '',
    email: member.email || '',
    password: '',
    password_confirmation: '',
    role: member.role || 'staff',
    phone: member.phone || '',
    is_active: member.is_active !== false
  }
  showStaffModal.value = true
}

function closeStaffModal() {
  showStaffModal.value = false
  resetForm()
}

function resetForm() {
  staffForm.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'staff',
    phone: '',
    is_active: true
  }
}

async function saveStaff() {
  isSubmitting.value = true
  try {
    const data = { ...staffForm.value }
    
    // Remove password fields if empty during edit
    if (isEditing.value && !data.password) {
      delete data.password
      delete data.password_confirmation
    }

    if (isEditing.value) {
      await staffStore.updateStaff(staffForm.value.id, data)
    } else {
      await staffStore.createStaff(data)
    }
    
    closeStaffModal()
    await fetchStaff()
  } catch (err) {
    console.error('Failed to save staff:', err)
  } finally {
    isSubmitting.value = false
  }
}

function confirmDelete(member) {
  staffToDelete.value = member
  showDeleteModal.value = true
}

async function deleteStaff() {
  if (!staffToDelete.value) return
  
  isSubmitting.value = true
  try {
    await staffStore.deleteStaff(staffToDelete.value.id)
    showDeleteModal.value = false
    staffToDelete.value = null
    await fetchStaff()
  } catch (err) {
    console.error('Failed to delete staff:', err)
  } finally {
    isSubmitting.value = false
  }
}

async function toggleStatus(member) {
  try {
    await staffStore.toggleStaffStatus(member.id, !member.is_active)
    await fetchStaff()
  } catch (err) {
    console.error('Failed to toggle status:', err)
  }
}

async function viewActivity(member) {
  try {
    await staffStore.fetchStaffActivity(member.id)
    showActivityModal.value = true
  } catch (err) {
    console.error('Failed to fetch staff activity:', err)
  }
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

function getInitials(name) {
  if (!name) return 'ST'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function getRoleBadge(role) {
  if (role === 'admin') {
    return { color: 'bg-purple-100 text-purple-800', icon: ShieldCheck }
  }
  return { color: 'bg-blue-100 text-blue-800', icon: Shield }
}

function goToPage(page) {
  staffStore.fetchStaff(page)
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
          <span class="text-gray-900">Staff Management</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Staff Management</h1>
            <p class="text-gray-600 mt-1">Manage staff and admin accounts</p>
          </div>
          <div class="flex items-center gap-3 mt-4 md:mt-0">
            <button @click="fetchStaff" 
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
              :disabled="isLoading">
              <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" />
              Refresh
            </button>
            <button @click="openCreateModal" 
              class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors">
              <Plus class="w-4 h-4 mr-2" />
              Add Staff
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-4">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-[#CF0D0F] bg-opacity-10 rounded-lg flex items-center justify-center">
              <Users class="w-6 h-6 text-[#CF0D0F]" />
            </div>
            <div class="ml-4">
              <p class="text-2xl font-bold text-gray-900">{{ pagination.total }}</p>
              <p class="text-sm text-gray-500">Total Staff</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <UserCheck class="w-6 h-6 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-2xl font-bold text-gray-900">{{ staffStore.activeStaffCount }}</p>
              <p class="text-sm text-gray-500">Active</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <ShieldCheck class="w-6 h-6 text-purple-600" />
            </div>
            <div class="ml-4">
              <p class="text-2xl font-bold text-gray-900">{{ staffStore.adminCount }}</p>
              <p class="text-sm text-gray-500">Admins</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Search -->
      <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-4 mb-6">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Search by name or email..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
          />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-16">
        <div class="text-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent"></div>
          <p class="mt-4 text-gray-600">Loading staff...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="staffStore.error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
          <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading staff</h3>
            <p class="text-red-600 text-sm mt-1">{{ staffStore.error }}</p>
          </div>
        </div>
        <button @click="fetchStaff" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
          Try Again
        </button>
      </div>

      <!-- Staff Table -->
      <div v-else>
        <div v-if="staff.length === 0" class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-16 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <Users class="w-8 h-8 text-gray-400" />
          </div>
          <p class="text-gray-500 mb-2">No staff members found</p>
          <p class="text-sm text-gray-400 mb-4">Add your first staff member to get started</p>
          <button @click="openCreateModal" class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D]">
            <Plus class="w-4 h-4 inline mr-2" />
            Add Staff
          </button>
        </div>

        <div v-else class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Member</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Active</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="member in staff" :key="member.id" class="hover:bg-gray-50">
                  <!-- Staff Member Info -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-10 h-10 rounded-full bg-[#CF0D0F] flex items-center justify-center text-white font-semibold">
                        {{ getInitials(member.name) }}
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ member.name }}</div>
                        <div class="text-sm text-gray-500">{{ member.email }}</div>
                      </div>
                    </div>
                  </td>

                  <!-- Role -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getRoleBadge(member.role).color]">
                      <component :is="getRoleBadge(member.role).icon" class="w-3 h-3 mr-1" />
                      {{ member.role === 'admin' ? 'Admin' : 'Staff' }}
                    </span>
                  </td>

                  <!-- Status -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <button
                      @click="toggleStatus(member)"
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer transition-colors',
                        member.is_active !== false 
                          ? 'bg-green-100 text-green-800 hover:bg-green-200' 
                          : 'bg-red-100 text-red-800 hover:bg-red-200'
                      ]"
                    >
                      <UserCheck v-if="member.is_active !== false" class="w-3 h-3 mr-1" />
                      <UserX v-else class="w-3 h-3 mr-1" />
                      {{ member.is_active !== false ? 'Active' : 'Inactive' }}
                    </button>
                  </td>

                  <!-- Joined -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(member.created_at)?.split(',')[0] || '-' }}
                  </td>

                  <!-- Last Active -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span v-if="member.last_active" class="inline-flex items-center">
                      <Clock class="w-3 h-3 mr-1" />
                      {{ formatDate(member.last_active) }}
                    </span>
                    <span v-else class="text-gray-400">Never</span>
                  </td>

                  <!-- Actions -->
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end space-x-2">
                      <button 
                        @click="viewActivity(member)"
                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                        title="View Activity"
                      >
                        <Activity class="w-4 h-4" />
                      </button>
                      <button 
                        @click="openEditModal(member)"
                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                        title="Edit"
                      >
                        <Edit class="w-4 h-4" />
                      </button>
                      <button 
                        @click="confirmDelete(member)"
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        title="Delete"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.lastPage > 1" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-700">
                Showing {{ ((pagination.currentPage - 1) * pagination.perPage) + 1 }} to 
                {{ Math.min(pagination.currentPage * pagination.perPage, pagination.total) }} of 
                {{ pagination.total }} results
              </p>
              <div class="flex items-center space-x-2">
                <button 
                  @click="goToPage(pagination.currentPage - 1)"
                  :disabled="pagination.currentPage === 1"
                  class="p-2 border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                  <ChevronLeft class="w-4 h-4" />
                </button>
                <span class="px-3 py-1 text-sm text-gray-700">
                  {{ pagination.currentPage }} / {{ pagination.lastPage }}
                </span>
                <button 
                  @click="goToPage(pagination.currentPage + 1)"
                  :disabled="pagination.currentPage === pagination.lastPage"
                  class="p-2 border border-gray-300 rounded-lg hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
                  <ChevronRight class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Staff Modal -->
    <Teleport to="body">
      <div v-if="showStaffModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeStaffModal"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                  {{ isEditing ? 'Edit Staff Member' : 'Add Staff Member' }}
                </h3>
                <button @click="closeStaffModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                  <X class="w-5 h-5" />
                </button>
              </div>
            </div>

            <!-- Modal Content -->
            <form @submit.prevent="saveStaff" class="px-6 py-4 max-h-[70vh] overflow-y-auto">
              <div class="space-y-4">
                <!-- Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                  <input 
                    v-model="staffForm.name"
                    type="text" 
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  />
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                  <input 
                    v-model="staffForm.email"
                    type="email"
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  />
                </div>

                <!-- Phone -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                  <input 
                    v-model="staffForm.phone"
                    type="tel"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  />
                </div>

                <!-- Role -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                  <select 
                    v-model="staffForm.role"
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>

                <!-- Password -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ isEditing ? 'New Password (leave blank to keep current)' : 'Password *' }}
                  </label>
                  <input 
                    v-model="staffForm.password"
                    type="password"
                    :required="!isEditing"
                    minlength="8"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  />
                </div>

                <!-- Confirm Password -->
                <div v-if="!isEditing || staffForm.password">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                  <input 
                    v-model="staffForm.password_confirmation"
                    type="password"
                    :required="!isEditing || !!staffForm.password"
                    minlength="8"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  />
                  <p v-if="staffForm.password && staffForm.password !== staffForm.password_confirmation" class="mt-1 text-sm text-red-600">
                    Passwords do not match
                  </p>
                </div>

                <!-- Active Status -->
                <div>
                  <label class="flex items-center">
                    <input 
                      v-model="staffForm.is_active"
                      type="checkbox"
                      class="w-4 h-4 text-[#CF0D0F] border-gray-300 rounded focus:ring-[#CF0D0F]"
                    />
                    <span class="ml-2 text-sm text-gray-700">Account is active</span>
                  </label>
                </div>
              </div>
            </form>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button @click="closeStaffModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="saveStaff"
                :disabled="isSubmitting || !staffForm.name.trim() || !staffForm.email.trim() || (!isEditing && !staffForm.password) || (staffForm.password && staffForm.password !== staffForm.password_confirmation)"
                class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50">
                {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update Staff' : 'Add Staff') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full">
            <div class="px-6 py-4">
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                  <AlertTriangle class="w-6 h-6 text-red-600" />
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-semibold text-gray-900">Delete Staff Member</h3>
                  <p class="text-sm text-gray-500 mt-1">
                    Are you sure you want to delete "{{ staffToDelete?.name }}"? This will remove their account and all associated data.
                  </p>
                </div>
              </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
              <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="deleteStaff"
                :disabled="isSubmitting"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                {{ isSubmitting ? 'Deleting...' : 'Delete' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Activity Modal -->
    <Teleport to="body">
      <div v-if="showActivityModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showActivityModal = false"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-full bg-[#CF0D0F] flex items-center justify-center text-white font-semibold mr-3">
                    {{ getInitials(currentStaff?.name) }}
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ currentStaff?.name }}</h3>
                    <p class="text-sm text-gray-500">Activity Log</p>
                  </div>
                </div>
                <button @click="showActivityModal = false" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                  <X class="w-5 h-5" />
                </button>
              </div>
            </div>

            <!-- Stats Overview -->
            <div v-if="staffStats" class="px-6 py-4 border-b border-gray-200 bg-gray-50">
              <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                  <p class="text-2xl font-bold text-gray-900">{{ staffStats.ordersProcessed || 0 }}</p>
                  <p class="text-xs text-gray-500">Orders Processed</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-gray-900">{{ staffStats.actionsToday || 0 }}</p>
                  <p class="text-xs text-gray-500">Actions Today</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-gray-900">{{ staffStats.totalActions || 0 }}</p>
                  <p class="text-xs text-gray-500">Total Actions</p>
                </div>
              </div>
            </div>

            <!-- Activity List -->
            <div class="px-6 py-4 max-h-[50vh] overflow-y-auto">
              <div v-if="staffActivity.length === 0" class="text-center py-8">
                <Activity class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                <p class="text-gray-500">No activity recorded yet</p>
              </div>
              <div v-else class="space-y-3">
                <div 
                  v-for="activity in staffActivity" 
                  :key="activity.id"
                  class="flex items-start p-3 bg-gray-50 rounded-lg"
                >
                  <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3 flex-shrink-0">
                    <Activity class="w-4 h-4 text-blue-600" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">{{ activity.action }}</p>
                    <p class="text-sm text-gray-500 truncate">{{ activity.description }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ formatDate(activity.created_at) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
              <button @click="showActivityModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
