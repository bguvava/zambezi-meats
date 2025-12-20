<script setup>
/**
 * Header.vue
 * Application header with search, notifications, and user menu
 */
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  Menu,
  Search,
  Bell,
  Moon,
  Sun,
  User,
  Settings,
  LogOut,
  ChevronRight
} from 'lucide-vue-next'

const props = defineProps({
  sidebarCollapsed: {
    type: Boolean,
    default: false
  },
  showMobileMenu: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['toggle-mobile-menu', 'toggle-search'])

const router = useRouter()
const authStore = useAuthStore()

const showUserMenu = ref(false)
const showNotifications = ref(false)
const isDarkMode = ref(false)
const unreadNotifications = ref(3) // Mock data

// Computed breadcrumbs from route
const breadcrumbs = computed(() => {
  const route = router.currentRoute.value
  const paths = route.path.split('/').filter(Boolean)
  
  return paths.map((path, index) => ({
    name: path.charAt(0).toUpperCase() + path.slice(1),
    path: '/' + paths.slice(0, index + 1).join('/')
  }))
})

// User info
const userName = computed(() => {
  return authStore.user?.name || 'User'
})

const userRole = computed(() => {
  return authStore.user?.role || 'customer'
})

// Toggle dark mode
function toggleDarkMode() {
  isDarkMode.value = !isDarkMode.value
  // Implement dark mode toggle logic here
  document.documentElement.classList.toggle('dark')
}

// Logout
async function logout() {
  await authStore.logout()
  router.push('/login')
}

// Navigate to profile
function goToProfile() {
  router.push(`/${userRole.value}/profile`)
  showUserMenu.value = false
}

// Navigate to settings
function goToSettings() {
  router.push(`/${userRole.value}/settings`)
  showUserMenu.value = false
}

// Close dropdowns when clicking outside
function closeDropdowns() {
  showUserMenu.value = false
  showNotifications.value = false
}
</script>

<template>
  <header class="fixed top-0 right-0 h-16 bg-white border-b-2 border-gray-200 z-30 transition-all duration-300"
    :style="{ left: sidebarCollapsed ? '4rem' : '16rem' }">
    <div class="h-full flex items-center justify-between px-6">
      <!-- Left Section: Mobile Menu + Breadcrumbs -->
      <div class="flex items-center space-x-4">
        <!-- Mobile Menu Button -->
        <button @click="emit('toggle-mobile-menu')"
          class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
          <Menu class="w-5 h-5 text-gray-600" />
        </button>

        <!-- Breadcrumbs -->
        <nav class="hidden md:flex items-center space-x-2 text-sm">
          <span class="text-gray-400">Home</span>
          <template v-for="(crumb, index) in breadcrumbs" :key="crumb.path">
            <ChevronRight class="w-4 h-4 text-gray-400" />
            <router-link v-if="index < breadcrumbs.length - 1" :to="crumb.path"
              class="text-gray-600 hover:text-[#CF0D0F] transition-colors">
              {{ crumb.name }}
            </router-link>
            <span v-else class="text-gray-900 font-medium">
              {{ crumb.name }}
            </span>
          </template>
        </nav>
      </div>

      <!-- Right Section: Actions -->
      <div class="flex items-center space-x-3">
        <!-- Search Button -->
        <button @click="emit('toggle-search')"
          class="p-2 rounded-lg hover:bg-gray-100 hover:text-[#CF0D0F] transition-all group">
          <Search class="w-5 h-5 text-gray-600 group-hover:text-[#CF0D0F]" />
        </button>

        <!-- Notifications Dropdown -->
        <div class="relative">
          <button @click="showNotifications = !showNotifications"
            class="relative p-2 rounded-lg hover:bg-gray-100 hover:text-[#CF0D0F] transition-all group">
            <Bell class="w-5 h-5 text-gray-600 group-hover:text-[#CF0D0F]" />
            <span v-if="unreadNotifications > 0"
              class="absolute top-1 right-1 w-4 h-4 bg-[#CF0D0F] rounded-full flex items-center justify-center text-white text-[10px] font-bold">
              {{ unreadNotifications }}
            </span>
          </button>

          <!-- Notifications Dropdown Menu -->
          <transition name="dropdown">
            <div v-if="showNotifications" v-click-away="closeDropdowns"
              class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border-2 border-gray-200 py-2 max-h-96 overflow-y-auto">
              <div class="px-4 py-2 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
              </div>
              <div class="py-2">
                <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors">
                  <p class="text-sm text-gray-900 font-medium">New order received</p>
                  <p class="text-xs text-gray-500 mt-1">Order #12345 from John Doe</p>
                  <p class="text-xs text-gray-400 mt-1">2 minutes ago</p>
                </div>
                <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors">
                  <p class="text-sm text-gray-900 font-medium">Low stock alert</p>
                  <p class="text-xs text-gray-500 mt-1">Product "Beef Steak" is running low</p>
                  <p class="text-xs text-gray-400 mt-1">1 hour ago</p>
                </div>
                <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors">
                  <p class="text-sm text-gray-900 font-medium">Order delivered</p>
                  <p class="text-xs text-gray-500 mt-1">Order #12340 has been delivered</p>
                  <p class="text-xs text-gray-400 mt-1">3 hours ago</p>
                </div>
              </div>
              <div class="px-4 py-2 border-t border-gray-200">
                <button
                  class="text-sm text-[#CF0D0F] hover:text-[#F6211F] font-medium transition-colors">
                  View all notifications
                </button>
              </div>
            </div>
          </transition>
        </div>

        <!-- Theme Toggle -->
        <button @click="toggleDarkMode"
          class="p-2 rounded-lg hover:bg-gray-100 hover:text-[#CF0D0F] transition-all group">
          <Moon v-if="!isDarkMode" class="w-5 h-5 text-gray-600 group-hover:text-[#CF0D0F]" />
          <Sun v-else class="w-5 h-5 text-gray-600 group-hover:text-[#CF0D0F]" />
        </button>

        <!-- User Dropdown -->
        <div class="relative">
          <button @click="showUserMenu = !showUserMenu"
            class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-all group">
            <div
              class="w-8 h-8 bg-gradient-to-br from-[#CF0D0F] to-[#F6211F] rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-bold">{{ userName.charAt(0) }}</span>
            </div>
            <span class="hidden md:block text-sm font-medium text-gray-900">{{ userName }}</span>
          </button>

          <!-- User Dropdown Menu -->
          <transition name="dropdown">
            <div v-if="showUserMenu" v-click-away="closeDropdowns"
              class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border-2 border-gray-200 py-2">
              <div class="px-4 py-2 border-b border-gray-200">
                <p class="text-sm font-semibold text-gray-900">{{ userName }}</p>
                <p class="text-xs text-gray-500 capitalize">{{ userRole }}</p>
              </div>
              <button @click="goToProfile"
                class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-[#CF0D0F] transition-colors">
                <User class="w-4 h-4 mr-3" />
                Profile
              </button>
              <button @click="goToSettings"
                class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-[#CF0D0F] transition-colors">
                <Settings class="w-4 h-4 mr-3" />
                Settings
              </button>
              <div class="border-t border-gray-200 my-1"></div>
              <button @click="logout"
                class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                <LogOut class="w-4 h-4 mr-3" />
                Logout
              </button>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
