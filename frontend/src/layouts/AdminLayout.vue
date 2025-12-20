<script setup>
/**
 * Admin Layout
 *
 * Dashboard layout for administrators with full system access.
 *
 * @requirement PROJ-INIT-012 Create base layout components
 */
import { ref } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LogoutConfirmModal from '@/components/common/LogoutConfirmModal.vue'
import SessionWarningModal from '@/components/auth/SessionWarningModal.vue'
import DashboardFooter from '@/components/common/DashboardFooter.vue'
import ToastContainer from '@/components/ToastContainer.vue'
import {
  Home,
  Users,
  Package,
  FolderTree,
  ShoppingCart,
  Warehouse,
  BarChart3,
  Settings,
  LogOut,
  Menu,
  X,
  ChevronLeft,
  User,
  Bell,
  Search,
  Calendar,
  Layers,
  TrendingUp,
  ChevronDown,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const isSidebarOpen = ref(false)
const isSidebarCollapsed = ref(false)
const showLogoutModal = ref(false)

const navigation = [
  { name: 'Dashboard', href: '/admin', icon: Home },
  { name: 'Users', href: '/admin/users', icon: Users },
  { name: 'Products', href: '/admin/products', icon: Package },
  { name: 'Categories', href: '/admin/categories', icon: FolderTree },
  { name: 'Orders', href: '/admin/orders', icon: ShoppingCart },
  { name: 'Inventory', href: '/admin/inventory', icon: Warehouse },
  { name: 'Reports', href: '/admin/reports', icon: BarChart3 },
  { name: 'Settings', href: '/admin/settings', icon: Settings },
]

function promptLogout() {
  showLogoutModal.value = true
}

async function handleLogout() {
  showLogoutModal.value = false
  await authStore.logout()
  // Redirect to homepage after logout
  router.push('/')
}

function cancelLogout() {
  showLogoutModal.value = false
}

function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}

function toggleSidebarCollapse() {
  isSidebarCollapsed.value = !isSidebarCollapsed.value
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Mobile Header -->
    <header class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-white border-b border-gray-200 h-16">
      <div class="flex items-center justify-between h-full px-4">
        <button @click="toggleSidebar" class="p-2 text-gray-600 hover:text-gray-900">
          <Menu class="w-6 h-6" />
        </button>
        <span class="font-bold text-secondary-900">Admin Panel</span>
        <button class="p-2 text-gray-600 hover:text-gray-900 relative">
          <Bell class="w-6 h-6" />
          <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
      </div>
    </header>

    <!-- Sidebar Overlay -->
    <div v-if="isSidebarOpen" class="lg:hidden fixed inset-0 z-40 bg-black/50" @click="toggleSidebar"></div>

    <!-- Sidebar -->
    <aside :class="[
      'fixed top-0 left-0 z-50 h-full bg-secondary-900 text-white transform transition-all duration-200 ease-in-out',
      isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      isSidebarCollapsed ? 'lg:w-20' : 'w-64',
    ]">
      <!-- Sidebar Header -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-secondary-800">
        <div class="flex items-center space-x-2" :class="{ 'lg:justify-center lg:w-full': isSidebarCollapsed }">
          <img src="/images/logo.png" alt="Zambezi Meats" class="w-8 h-8 rounded-lg object-contain flex-shrink-0" />
          <div v-if="!isSidebarCollapsed" class="lg:block">
            <span class="font-bold text-white text-sm">Zambezi Meats</span>
            <span class="block text-xs text-primary-400">Admin Panel</span>
          </div>
        </div>
        <button @click="toggleSidebar" class="lg:hidden p-2 text-gray-400 hover:text-white">
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- User Info -->
      <div class="p-4 border-b border-secondary-800" :class="{ 'lg:px-2': isSidebarCollapsed }">
        <div class="flex items-center space-x-3" :class="{ 'lg:justify-center': isSidebarCollapsed }">
          <div class="w-10 h-10 bg-primary-700 rounded-full flex items-center justify-center flex-shrink-0">
            <User class="w-5 h-5 text-white" />
          </div>
          <div v-if="!isSidebarCollapsed" class="lg:block">
            <p class="font-medium text-white text-sm">{{ authStore.userName }}</p>
            <p class="text-xs text-gray-400">Administrator</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="p-4 space-y-1" :class="{ 'lg:px-2': isSidebarCollapsed }">
        <RouterLink v-for="item in navigation" :key="item.name" :to="item.href"
          class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-gray-300 hover:bg-secondary-800 hover:text-white transition-colors"
          :class="{ 'lg:justify-center lg:px-2': isSidebarCollapsed }" active-class="bg-primary-700 text-white"
          @click="isSidebarOpen = false" :title="isSidebarCollapsed ? item.name : ''">
          <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
          <span v-if="!isSidebarCollapsed" class="lg:inline">{{ item.name }}</span>
        </RouterLink>
      </nav>

      <!-- Collapse Toggle (Desktop) -->
      <div class="hidden lg:block p-4 border-t border-secondary-800">
        <button @click="toggleSidebarCollapse"
          class="flex items-center justify-center w-full p-2 rounded-lg text-gray-400 hover:bg-secondary-800 hover:text-white transition-colors">
          <ChevronLeft class="w-5 h-5 transition-transform" :class="{ 'rotate-180': isSidebarCollapsed }" />
        </button>
      </div>

      <!-- Logout -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-secondary-800"
        :class="{ 'lg:px-2': isSidebarCollapsed }">
        <button @click="promptLogout"
          class="flex items-center space-x-3 w-full px-3 py-2.5 rounded-lg text-gray-300 hover:bg-red-600/20 hover:text-red-400 transition-colors"
          :class="{ 'lg:justify-center lg:px-2': isSidebarCollapsed }" :title="isSidebarCollapsed ? 'Logout' : ''">
          <LogOut class="w-5 h-5 flex-shrink-0" />
          <span v-if="!isSidebarCollapsed" class="lg:inline">Logout</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main :class="[
      'pt-16 lg:pt-0 min-h-screen flex flex-col transition-all duration-200',
      isSidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64',
    ]">
      <!-- Desktop Header -->
      <header class="hidden lg:flex h-16 items-center justify-between px-8 bg-white border-b border-gray-200">
        <div class="flex items-center space-x-4 flex-1">
          <!-- Search -->
          <div class="relative max-w-md flex-1">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input type="text" placeholder="Search..."
              class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <button class="p-2 text-gray-600 hover:text-gray-900 relative">
            <Bell class="w-5 h-5" />
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
          </button>
          <RouterLink to="/shop" class="text-sm text-gray-600 hover:text-primary-700 font-medium">
            View Store →
          </RouterLink>
        </div>
      </header>

      <div class="flex-1 p-4 sm:p-6 lg:p-8">
        <RouterView />
      </div>

      <!-- Dashboard Footer -->
      <DashboardFooter />
    </main>

    <!-- Session Warning Modal -->
    <SessionWarningModal />

    <!-- Logout Confirmation Modal -->
    <LogoutConfirmModal :is-open="showLogoutModal" @confirm="handleLogout" @cancel="cancelLogout" />

    <!-- Toast Notifications -->
    <ToastContainer />
  </div>
</template>
