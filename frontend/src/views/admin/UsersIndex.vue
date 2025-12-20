<template>
  <div class="px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header Card with Actions and Filters -->
    <div class="bg-white rounded-xl shadow-md border-2 p-6 mb-6" style="border-color: #CF0D0F;">
      <!-- Header Row -->
      <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-3xl font-bold mb-2" style="color: #CF0D0F;">Users Management</h1>
          <p class="text-sm" style="color: #6F6F6F;">Manage system users, roles, and permissions.</p>
        </div>

        <!-- Action Buttons Row -->
        <div class="flex flex-wrap items-center gap-2">
          <button type="button"
            class="inline-flex items-center justify-center rounded-lg px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
            style="background: linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%);" @click="openCreateModal">
            <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
            Add User
          </button>

          <button type="button"
            class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:shadow-lg transition-all duration-200"
            style="background-color: #F6211F;" @click="resetFilters">
            <XMarkIcon class="-ml-0.5 mr-2 h-4 w-4" />
            Clear
          </button>

          <button type="button"
            class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200"
            style="background-color: #EFEFEF; color: #4D4B4C;" @click="exportToPDF" :disabled="userStore.loading">
            <ArrowDownTrayIcon class="-ml-0.5 mr-2 h-4 w-4" />
            Export PDF
          </button>
        </div>
      </div>

      <!-- Filters Row - Single Line -->
      <div class="flex flex-col lg:flex-row gap-3">
        <!-- Search Input -->
        <div class="flex-1 min-w-0">
          <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
              <MagnifyingGlassIcon class="h-5 w-5" style="color: #CF0D0F;" />
            </div>
            <input id="search" v-model="userStore.filters.search" type="search" placeholder="Search by name or email..."
              class="block w-full rounded-lg pl-12 pr-4 py-3 text-sm font-medium focus:outline-none focus:ring-2 transition-all duration-200"
              style="border: 2px solid #CF0D0F; color: #4D4B4C;" @input="debouncedSearch" />
          </div>
        </div>

        <!-- Role Filter -->
        <div class="w-full lg:w-56">
          <select id="role" v-model="userStore.filters.role"
            class="block w-full rounded-lg px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 transition-all duration-200 appearance-none cursor-pointer"
            style="border: 2px solid #CF0D0F; color: #4D4B4C; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M5%207l5%205%205-5%22%20stroke%3D%22%23CF0D0F%22%20stroke-width%3D%222%22%20fill%3D%22none%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25rem;"
            @change="handleFilterChange">
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
            <option value="customer">Customer</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="w-full lg:w-56">
          <select id="status" v-model="userStore.filters.status"
            class="block w-full rounded-lg px-4 py-3 text-sm font-semibold focus:outline-none focus:ring-2 transition-all duration-200 appearance-none cursor-pointer"
            style="border: 2px solid #CF0D0F; color: #4D4B4C; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20d%3D%22M5%207l5%205%205-5%22%20stroke%3D%22%23CF0D0F%22%20stroke-width%3D%222%22%20fill%3D%22none%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25rem;"
            @change="handleFilterChange">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="userStore.loading && !userStore.users.length" class="flex flex-col justify-center items-center py-24">
      <div class="relative">
        <svg class="animate-spin h-16 w-16" style="color: #CF0D0F;" xmlns="http://www.w3.org/2000/svg" fill="none"
          viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
          <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
          </path>
        </svg>
      </div>
      <p class="mt-4 text-base font-semibold" style="color: #6F6F6F;">Loading users...</p>
    </div>

    <!-- Table Container -->
    <div v-else class="bg-white rounded-xl shadow-md border-2 overflow-hidden" style="border-color: #CF0D0F;">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead style="background: linear-gradient(180deg, #EFEFEF 0%, #e5e5e5 100%);">
            <tr>
              <th scope="col" class="py-4 pl-6 pr-3 text-left text-xs font-bold uppercase tracking-wider"
                style="color: #4D4B4C;">
                User
              </th>
              <th scope="col" class="px-3 py-4 text-left text-xs font-bold uppercase tracking-wider"
                style="color: #4D4B4C;">
                Role
              </th>
              <th scope="col" class="px-3 py-4 text-left text-xs font-bold uppercase tracking-wider"
                style="color: #4D4B4C;">
                Status
              </th>
              <th scope="col" class="px-3 py-4 text-left text-xs font-bold uppercase tracking-wider"
                style="color: #4D4B4C;">
                Joined
              </th>
              <th scope="col" class="relative py-4 pl-3 pr-6 text-right">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y" style="border-color: #EFEFEF;">
            <tr v-for="user in userStore.users" :key="user.id" class="transition-colors duration-150 hover:bg-gray-50">
              <td class="whitespace-nowrap py-4 pl-6 pr-3">
                <div class="flex items-center">
                  <UserAvatar :name="user.name" size="md" class="shrink-0" />
                  <div class="ml-4">
                    <div class="text-sm font-bold" style="color: #4D4B4C;">{{ user.name }}</div>
                    <div class="text-sm" style="color: #6F6F6F;">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="whitespace-nowrap px-3 py-4">
                <UserRoleBadge :role="user.role" />
              </td>
              <td class="whitespace-nowrap px-3 py-4">
                <UserStatusBadge :status="user.status" />
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm" style="color: #6F6F6F;">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right">
                <Menu as="div" class="relative inline-block text-left">
                  <MenuButton
                    class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-200"
                    style="background-color: #EFEFEF; color: #4D4B4C;">
                    Actions
                    <ChevronDownIcon class="-mr-1 ml-2 h-4 w-4" />
                  </MenuButton>

                  <transition enter-active-class="transition ease-out duration-100"
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                    <MenuItems
                      class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none"
                      style="background-color: white; border: 2px solid #EFEFEF;">
                      <div class="py-1">
                        <MenuItem v-slot="{ active }">
                        <button type="button"
                          :class="['group flex items-center px-4 py-3 text-sm font-medium w-full transition-colors duration-150', active ? '' : '']"
                          :style="active ? 'background-color: #EFEFEF; color: #4D4B4C;' : 'color: #6F6F6F;'"
                          @click="openViewModal(user)">
                          <EyeIcon class="mr-3 h-5 w-5" style="color: #4D4B4C;" />
                          View User
                        </button>
                        </MenuItem>

                        <MenuItem v-slot="{ active }">
                        <button type="button"
                          :class="['group flex items-center px-4 py-3 text-sm font-medium w-full transition-colors duration-150', active ? '' : '']"
                          :style="active ? 'background-color: #EFEFEF; color: #4D4B4C;' : 'color: #6F6F6F;'"
                          @click="openEditModal(user)">
                          <PencilIcon class="mr-3 h-5 w-5" style="color: #CF0D0F;" />
                          Edit User
                        </button>
                        </MenuItem>

                        <MenuItem v-slot="{ active }">
                        <button type="button"
                          :class="['group flex items-center px-4 py-3 text-sm font-medium w-full transition-colors duration-150', active ? '' : '']"
                          :style="active ? 'background-color: #EFEFEF; color: #4D4B4C;' : 'color: #6F6F6F;'"
                          @click="openStatusMenu(user)">
                          <ArrowPathIcon class="mr-3 h-5 w-5" style="color: #F6211F;" />
                          Change Status
                        </button>
                        </MenuItem>

                        <MenuItem v-slot="{ active }">
                        <button type="button"
                          :class="['group flex items-center px-4 py-3 text-sm font-medium w-full transition-colors duration-150', active ? '' : '']"
                          :style="active ? 'background-color: #EFEFEF; color: #4D4B4C;' : 'color: #6F6F6F;'"
                          @click="handleResetPassword(user)">
                          <KeyIcon class="mr-3 h-5 w-5" style="color: #6F6F6F;" />
                          Reset Password
                        </button>
                        </MenuItem>

                        <MenuItem v-slot="{ active }">
                        <button type="button"
                          :class="['group flex items-center px-4 py-3 text-sm font-medium w-full transition-colors duration-150', active ? '' : '']"
                          :style="active ? 'background-color: #EFEFEF; color: #4D4B4C;' : 'color: #6F6F6F;'"
                          @click="openActivityModal(user)">
                          <ClockIcon class="mr-3 h-5 w-5" style="color: #4D4B4C;" />
                          View Activity
                        </button>
                        </MenuItem>
                      </div>
                    </MenuItems>
                  </transition>
                </Menu>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Empty State -->
        <div v-if="!userStore.users.length" class="text-center py-16 px-4">
          <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-6"
            style="background: linear-gradient(135deg, #EFEFEF 0%, #e5e5e5 100%);">
            <UserGroupIcon class="h-12 w-12" style="color: #6F6F6F;" />
          </div>
          <h3 class="text-xl font-bold mb-2" style="color: #4D4B4C;">No users found</h3>
          <p class="text-sm mb-6" style="color: #6F6F6F;">Get started by creating your first user.</p>
          <button type="button"
            class="inline-flex items-center rounded-lg px-6 py-3 text-sm font-bold text-white shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
            style="background: linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%);" @click="openCreateModal">
            <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
            Add First User
          </button>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="userStore.pagination.lastPage > 1"
      class="flex flex-col sm:flex-row items-center justify-between bg-white px-6 py-4 mt-6 rounded-xl shadow-md border-2 gap-4"
      style="border-color: #CF0D0F;">
      <!-- Mobile Pagination -->
      <div class="flex sm:hidden justify-between w-full gap-2">
        <button type="button" :disabled="userStore.pagination.currentPage === 1"
          class="flex-1 inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold transition-all duration-200 disabled:opacity-50"
          style="background-color: #EFEFEF; color: #4D4B4C;" @click="changePage(userStore.pagination.currentPage - 1)">
          <ChevronLeftIcon class="h-5 w-5 mr-1" />
          Previous
        </button>
        <button type="button" :disabled="userStore.pagination.currentPage === userStore.pagination.lastPage"
          class="flex-1 inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold transition-all duration-200 disabled:opacity-50"
          style="background-color: #EFEFEF; color: #4D4B4C;" @click="changePage(userStore.pagination.currentPage + 1)">
          Next
          <ChevronRightIcon class="h-5 w-5 ml-1" />
        </button>
      </div>

      <!-- Desktop Pagination -->
      <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
          <p class="text-sm font-medium" style="color: #6F6F6F;">
            Showing
            <span class="font-bold" style="color: #CF0D0F;">{{ (userStore.pagination.currentPage - 1) *
              userStore.pagination.perPage + 1 }}</span>
            to
            <span class="font-bold" style="color: #CF0D0F;">{{ Math.min(userStore.pagination.currentPage *
              userStore.pagination.perPage, userStore.pagination.total) }}</span>
            of
            <span class="font-bold" style="color: #CF0D0F;">{{ userStore.pagination.total }}</span>
            results
          </p>
        </div>
        <div class="flex items-center gap-1">
          <button type="button" :disabled="userStore.pagination.currentPage === 1"
            class="inline-flex items-center justify-center w-10 h-10 rounded-lg transition-all duration-200 disabled:opacity-50 hover:shadow-md"
            style="background-color: #EFEFEF; color: #4D4B4C;"
            @click="changePage(userStore.pagination.currentPage - 1)">
            <ChevronLeftIcon class="h-5 w-5" />
          </button>

          <button v-for="page in visiblePages" :key="page" type="button"
            class="inline-flex items-center justify-center min-w-10 h-10 px-3 rounded-lg text-sm font-bold transition-all duration-200 hover:shadow-md"
            :style="page === userStore.pagination.currentPage ? 'background: linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%); color: white;' : 'background-color: #EFEFEF; color: #4D4B4C;'"
            @click="changePage(page)">
            {{ page }}
          </button>

          <button type="button" :disabled="userStore.pagination.currentPage === userStore.pagination.lastPage"
            class="inline-flex items-center justify-center w-10 h-10 rounded-lg transition-all duration-200 disabled:opacity-50 hover:shadow-md"
            style="background-color: #EFEFEF; color: #4D4B4C;"
            @click="changePage(userStore.pagination.currentPage + 1)">
            <ChevronRightIcon class="h-5 w-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <CreateUserModal :open="showCreateModal" @close="showCreateModal = false" @created="handleUserCreated" />

    <EditUserModal :open="showEditModal" :user="selectedUser" @close="showEditModal = false"
      @updated="handleUserUpdated" />

    <ViewUserModal :open="showViewModal" :user="selectedUser" @close="showViewModal = false" />

    <UserActivityModal :open="showActivityModal" :user="selectedUser" @close="showActivityModal = false" />

    <!-- Status Change Modal -->
    <TransitionRoot as="template" :show="showStatusMenu">
      <Dialog as="div" class="relative z-50" @close="showStatusMenu = false">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
          leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild as="template" enter="ease-out duration-300"
              enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
              leave-from="opacity-100 translate-y-0 sm:scale-100"
              leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
              <DialogPanel
                class="relative transform overflow-hidden rounded-xl bg-white px-6 pb-6 pt-5 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-sm"
                style="border: 3px solid #CF0D0F;">
                <div>
                  <DialogTitle as="h3" class="text-xl font-bold mb-6" style="color: #CF0D0F;">
                    Change User Status
                  </DialogTitle>

                  <div class="space-y-2">
                    <button v-for="status in ['active', 'suspended', 'inactive']" :key="status" type="button"
                      class="w-full flex items-center justify-between rounded-lg px-5 py-4 text-sm font-bold transition-all duration-200 hover:shadow-md"
                      :style="selectedUser?.status === status ? 'background: linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%); color: white; border: 2px solid #CF0D0F;' : 'background-color: #EFEFEF; color: #4D4B4C; border: 2px solid #EFEFEF;'"
                      @click="handleStatusChange(status)">
                      <span class="capitalize">{{ status }}</span>
                      <UserStatusBadge :status="status" />
                    </button>
                  </div>

                  <div class="mt-6">
                    <button type="button"
                      class="w-full inline-flex justify-center items-center rounded-lg px-5 py-3 text-sm font-semibold shadow-md transition-all duration-200"
                      style="background-color: #EFEFEF; color: #4D4B4C;" @click="showStatusMenu = false">
                      <XMarkIcon class="h-5 w-5 mr-2" />
                      Cancel
                    </button>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Menu, MenuButton, MenuItem, MenuItems, Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import {
  PlusIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  ArrowDownTrayIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  PencilIcon,
  ArrowPathIcon,
  KeyIcon,
  ClockIcon,
  UserGroupIcon,
  EyeIcon
} from '@heroicons/vue/24/outline'
import { useUserStore } from '@/stores/user'
import { useToast } from '@/composables/useToast'
import UserAvatar from '@/components/user/UserAvatar.vue'
import UserRoleBadge from '@/components/user/UserRoleBadge.vue'
import UserStatusBadge from '@/components/user/UserStatusBadge.vue'
import CreateUserModal from '@/components/user/CreateUserModal.vue'
import EditUserModal from '@/components/user/EditUserModal.vue'
import UserActivityModal from '@/components/user/UserActivityModal.vue'
import ViewUserModal from '@/components/user/ViewUserModal.vue'

const userStore = useUserStore()
const toast = useToast()

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showActivityModal = ref(false)
const showViewModal = ref(false)
const showStatusMenu = ref(false)
const selectedUser = ref(null)

let searchTimeout = null

const visiblePages = computed(() => {
  const current = userStore.pagination.currentPage
  const last = userStore.pagination.lastPage
  const pages = []

  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) pages.push(i)
      pages.push('...')
      pages.push(last)
    } else if (current >= last - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = last - 4; i <= last; i++) pages.push(i)
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(last)
    }
  }

  return pages.filter(p => p !== '...')
})

onMounted(async () => {
  await loadUsers()
})

async function loadUsers() {
  try {
    await userStore.fetchUsers()
  } catch (error) {
    toast.error('Failed to load users')
  }
}

function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadUsers()
  }, 500)
}

function handleFilterChange() {
  loadUsers()
}

function resetFilters() {
  userStore.resetFilters()
  loadUsers()
}

async function changePage(page) {
  if (page < 1 || page > userStore.pagination.lastPage) return
  try {
    await userStore.fetchUsers(page)
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } catch (error) {
    toast.error('Failed to load page')
  }
}

function openCreateModal() {
  showCreateModal.value = true
}

function openEditModal(user) {
  selectedUser.value = user
  showEditModal.value = true
}

function openViewModal(user) {
  selectedUser.value = user
  showViewModal.value = true
}

function openActivityModal(user) {
  selectedUser.value = user
  showActivityModal.value = true
}

function openStatusMenu(user) {
  selectedUser.value = user
  showStatusMenu.value = true
}

async function handleStatusChange(status) {
  if (!selectedUser.value || selectedUser.value.status === status) {
    showStatusMenu.value = false
    return
  }

  try {
    await userStore.changeStatus(selectedUser.value.id, status)
    toast.success('User status updated successfully')
    showStatusMenu.value = false
    await loadUsers()
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to update status')
  }
}

async function handleResetPassword(user) {
  if (!confirm(`Are you sure you want to reset password for ${user.name}?`)) {
    return
  }

  try {
    await userStore.resetPassword(user.id)
    toast.success('Password reset successfully')
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to reset password')
  }
}

async function exportToPDF() {
  try {
    await userStore.exportPDF()
    toast.success('Users exported successfully')
  } catch (error) {
    toast.error('Failed to export users')
  }
}

function handleUserCreated() {
  loadUsers()
}

function handleUserUpdated() {
  loadUsers()
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>

<style scoped>
/* Smooth scrolling for better UX */
html {
  scroll-behavior: smooth;
}

/* Custom focus states */
input:focus,
select:focus {
  outline: 2px solid #F6211F !important;
  outline-offset: 2px;
}

/* Remove default select arrow for custom styling */
select {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

/* Optimize rendering */
* {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Hardware acceleration for animations */
.transition-all {
  will-change: transform, box-shadow;
}
</style>
