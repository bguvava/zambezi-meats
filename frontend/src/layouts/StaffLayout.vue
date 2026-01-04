<script setup>
/**
 * Staff Layout
 *
 * Dashboard layout for staff members with order and delivery management.
 * Responsive design with mobile slide-in, tablet auto-collapse, desktop full sidebar.
 *
 * @requirement PROJ-INIT-012 Create base layout components
 */
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import LogoutConfirmModal from '@/components/common/LogoutConfirmModal.vue'
import SessionWarningModal from '@/components/auth/SessionWarningModal.vue'
import DashboardFooter from '@/components/common/DashboardFooter.vue'
import NotificationDropdown from '@/components/common/NotificationDropdown.vue'
import {
  Home,
  Package,
  Truck,
  LogOut,
  Menu,
  X,
  ChevronLeft,
  User,
  Bell,
  Calendar,
  Layers,
  ChevronDown,
  Boxes,
  Trash2,
  Activity,
  ClipboardList,
  MessageSquare,
  FileText,
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const isSidebarOpen = ref(false)
const isSidebarCollapsed = ref(false)
const showLogoutModal = ref(false)

const navigation = [
  { name: 'Dashboard', href: '/staff', icon: Home },
  { name: 'Order Queue', href: '/staff/orders', icon: ClipboardList },
  { name: 'My Deliveries', href: '/staff/deliveries', icon: Truck },
  { name: 'Invoices', href: '/staff/invoices', icon: FileText },
  { name: 'Stock Check', href: '/staff/stock', icon: Boxes },
  { name: 'Waste Log', href: '/staff/waste', icon: Trash2 },
  { name: 'Messages', href: '/staff/messages', icon: MessageSquare },
]

// Handle responsive behavior
function handleResize() {
  const width = window.innerWidth
  // Auto-collapse on tablet (768-1024px)
  if (width >= 768 && width < 1280) {
    isSidebarCollapsed.value = true
  } else if (width >= 1280) {
    isSidebarCollapsed.value = false
  }
  // Close mobile sidebar on resize to desktop
  if (width >= 1024 && isSidebarOpen.value) {
    isSidebarOpen.value = false
  }
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

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
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Header -->
    <header class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-white border-b border-gray-200 h-16">
      <div class="flex items-center justify-between h-full px-4">
        <button @click="toggleSidebar"
          class="p-2 text-gray-600 hover:text-gray-900 active:bg-gray-100 rounded-lg transition-colors">
          <Menu class="w-6 h-6" />
        </button>
        <span class="font-bold text-secondary-900">Staff Portal</span>
        <button class="p-2 text-gray-600 hover:text-gray-900 active:bg-gray-100 rounded-lg relative transition-colors">
          <Bell class="w-6 h-6" />
          <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
      </div>
    </header>

    <!-- Sidebar Overlay (Mobile & Tablet) -->
    <Transition name="fade">
      <div v-if="isSidebarOpen" class="lg:hidden fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
        @click="toggleSidebar"></div>
    </Transition>

    <!-- Sidebar -->
    <aside :class="[
      'fixed top-0 left-0 z-50 h-full bg-secondary-900 text-white transform transition-all duration-300 ease-in-out',
      isSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
      isSidebarCollapsed ? 'lg:w-20' : 'w-64',
    ]">
      <!-- Sidebar Header -->
      <div class="h-16 flex items-center justify-between px-4 border-b border-secondary-800"
        :class="{ 'lg:px-2': isSidebarCollapsed }">
        <div class="flex items-center space-x-2" :class="{ 'lg:justify-center': isSidebarCollapsed }">
          <img src="/images/logo.png" alt="Zambezi Meats" class="w-8 h-8 rounded-lg object-contain flex-shrink-0" />
          <div v-if="!isSidebarCollapsed" class="lg:block">
            <span class="font-bold text-white text-sm">Zambezi Meats</span>
            <span class="block text-xs text-primary-400">Staff Panel</span>
          </div>
        </div>
        <div class="flex items-center gap-1">
          <!-- Collapse Toggle (Desktop) -->
          <button @click="toggleSidebarCollapse"
            class="hidden lg:block p-1.5 text-gray-400 hover:text-white hover:bg-secondary-800 rounded-lg transition-colors"
            :title="isSidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
            <ChevronLeft class="w-4 h-4 transition-transform duration-300"
              :class="{ 'rotate-180': isSidebarCollapsed }" />
          </button>
          <!-- Close (Mobile) -->
          <button @click="toggleSidebar"
            class="lg:hidden p-2 text-gray-400 hover:text-white active:bg-secondary-800 rounded-lg transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- User Info -->
      <div class="p-4 border-b border-secondary-800" :class="{ 'lg:px-2': isSidebarCollapsed }">
        <div class="flex items-center space-x-3" :class="{ 'lg:justify-center': isSidebarCollapsed }">
          <div
            class="w-10 h-10 bg-primary-700/30 rounded-full flex items-center justify-center flex-shrink-0 overflow-hidden">
            <img v-if="authStore.user?.avatar" :src="authStore.user.avatar" :alt="authStore.userName"
              class="w-full h-full object-cover" @error="($event) => $event.target.src = '/images/user.jpg'" />
            <img v-else src="/images/user.jpg" :alt="authStore.userName" class="w-full h-full object-cover" />
          </div>
          <div v-if="!isSidebarCollapsed" class="lg:block overflow-hidden">
            <p class="font-medium text-white truncate">{{ authStore.userName }}</p>
            <p class="text-sm text-gray-400">Staff Member</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 p-4 space-y-1 overflow-y-auto" :class="{ 'lg:px-2': isSidebarCollapsed }">
        <RouterLink v-for="item in navigation" :key="item.name" :to="item.href"
          class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-gray-300 hover:bg-secondary-800 hover:text-white active:bg-secondary-700 transition-all duration-150"
          :class="{ 'lg:justify-center lg:px-2': isSidebarCollapsed }"
          active-class="bg-primary-700 text-white shadow-lg" @click="isSidebarOpen = false"
          :title="isSidebarCollapsed ? item.name : ''">
          <component :is="item.icon" class="w-5 h-5 flex-shrink-0" />
          <span v-if="!isSidebarCollapsed" class="lg:inline">{{ item.name }}</span>
        </RouterLink>
      </nav>

      <!-- Bottom Actions -->
      <div class="border-t border-secondary-800 bg-secondary-900" :class="{ 'lg:px-2': isSidebarCollapsed }">
        <!-- Quick Actions -->
        <div class="p-2 border-b border-secondary-800">
          <RouterLink to="/shop"
            class="flex items-center space-x-2 text-gray-400 hover:text-white hover:bg-secondary-800 px-2 py-1.5 rounded-lg text-sm transition-colors"
            :class="{ 'lg:justify-center lg:px-1.5': isSidebarCollapsed }"
            :title="isSidebarCollapsed ? 'View Store' : ''">
            <ChevronLeft class="w-4 h-4 flex-shrink-0" />
            <span v-if="!isSidebarCollapsed" class="lg:inline">View Store</span>
          </RouterLink>
        </div>

        <!-- Logout -->
        <div class="p-3">
          <button @click="promptLogout"
            class="flex items-center space-x-2.5 w-full px-2.5 py-2 rounded-lg text-gray-300 hover:bg-red-600/20 hover:text-red-400 active:bg-red-600/30 transition-all duration-150 text-sm font-medium"
            :class="{ 'lg:justify-center lg:px-2': isSidebarCollapsed }" :title="isSidebarCollapsed ? 'Logout' : ''">
            <LogOut class="w-4 h-4 flex-shrink-0" />
            <span v-if="!isSidebarCollapsed" class="lg:inline">Logout</span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main :class="[
      'pt-16 lg:pt-0 min-h-screen flex flex-col transition-all duration-300',
      isSidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64',
    ]">
      <!-- Desktop Header -->
      <header class="hidden lg:flex h-16 items-center justify-between px-8 bg-white border-b border-gray-200">
        <h1 class="text-xl font-semibold text-secondary-900">Staff Dashboard</h1>
        <div class="flex items-center space-x-4">
          <NotificationDropdown />
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
  </div>
</template>

<style scoped>
/* Fade transition for overlay */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Smooth scrollbar for navigation */
nav::-webkit-scrollbar {
  width: 4px;
}

nav::-webkit-scrollbar-track {
  background: transparent;
}

nav::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 2px;
}

nav::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}

/* Touch optimization for mobile */
@media (max-width: 1023px) {

  button,
  a {
    -webkit-tap-highlight-color: transparent;
  }
}
</style>
