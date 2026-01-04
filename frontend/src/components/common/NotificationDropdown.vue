<template>
  <div class="relative">
    <!-- Bell Icon Button -->
    <button 
      @click="toggleDropdown" 
      class="relative p-2 text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white active:bg-gray-100 dark:active:bg-gray-700 rounded-lg transition-colors"
      :aria-label="`Notifications${unreadCount > 0 ? ` (${unreadCount} unread)` : ''}`"
    >
      <Bell class="w-5 h-5" />
      
      <!-- Unread Badge -->
      <span 
        v-if="unreadCount > 0" 
        class="absolute top-1 right-1 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown Panel -->
    <Transition name="dropdown">
      <div 
        v-if="isOpen" 
        v-click-outside="closeDropdown"
        class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 max-h-[600px] flex flex-col"
      >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h3 class="font-semibold text-gray-900 dark:text-white">Notifications</h3>
          <div class="flex items-center space-x-2">
            <button 
              v-if="unreadCount > 0"
              @click="markAllAsRead" 
              class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium"
              title="Mark all as read"
            >
              Mark all read
            </button>
            <button 
              @click="fetchNotifications" 
              class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded"
              title="Refresh notifications"
            >
              <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
            </button>
          </div>
        </div>

        <!-- Filter Tabs -->
        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex space-x-2">
          <button 
            v-for="filter in filters" 
            :key="filter.value"
            @click="activeFilter = filter.value"
            class="px-3 py-1 text-sm rounded-md transition-colors"
            :class="activeFilter === filter.value 
              ? 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300 font-medium' 
              : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
          >
            {{ filter.label }}
          </button>
        </div>

        <!-- Notifications List -->
        <div class="flex-1 overflow-y-auto" style="max-height: 400px;">
          <!-- Loading State -->
          <div v-if="loading && notifications.length === 0" class="px-4 py-8 text-center">
            <LoaderCircle class="w-6 h-6 mx-auto text-gray-400 animate-spin" />
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading notifications...</p>
          </div>

          <!-- Empty State -->
          <div v-else-if="filteredNotifications.length === 0" class="px-4 py-12 text-center">
            <Bell class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600" />
            <p class="mt-3 text-sm font-medium text-gray-900 dark:text-white">No notifications</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              {{ activeFilter === 'unread' ? 'You\'re all caught up!' : 'No notifications yet' }}
            </p>
          </div>

          <!-- Notification Items -->
          <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
            <div 
              v-for="notification in filteredNotifications" 
              :key="notification.id"
              class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer"
              :class="{ 'bg-primary-50/30 dark:bg-primary-900/10': !notification.is_read }"
              @click="handleNotificationClick(notification)"
            >
              <div class="flex items-start space-x-3">
                <!-- Icon -->
                <div 
                  class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                  :class="getNotificationIconClass(notification.type)"
                >
                  <component :is="getNotificationIcon(notification.type)" class="w-4 h-4" />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ notification.title }}
                    </p>
                    <!-- Unread Indicator -->
                    <div v-if="!notification.is_read" class="w-2 h-2 bg-primary-600 rounded-full flex-shrink-0 ml-2"></div>
                  </div>
                  <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                    {{ notification.message }}
                  </p>
                  <div class="mt-2 flex items-center space-x-3">
                    <span class="text-xs text-gray-500 dark:text-gray-500">
                      {{ formatTime(notification.created_at) }}
                    </span>
                    <button 
                      v-if="!notification.is_read"
                      @click.stop="markAsRead(notification.id)"
                      class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium"
                    >
                      Mark read
                    </button>
                    <button 
                      @click.stop="deleteNotification(notification.id)"
                      class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 font-medium"
                    >
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <button 
            v-if="notifications.length > 0"
            @click="deleteAllRead"
            class="text-xs text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400"
          >
            Delete all read
          </button>
          <RouterLink 
            to="/customer/notifications" 
            class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium"
            @click="closeDropdown"
          >
            View all notifications →
          </RouterLink>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { 
  Bell, 
  RefreshCw, 
  LoaderCircle,
  ShoppingCart,
  Truck,
  MessageSquare,
  AlertCircle,
  Tag
} from 'lucide-vue-next'

const router = useRouter()

// State
const isOpen = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const loading = ref(false)
const activeFilter = ref('all') // 'all', 'unread', 'order', 'delivery', 'message'

// Filters
const filters = [
  { label: 'All', value: 'all' },
  { label: 'Unread', value: 'unread' },
  { label: 'Orders', value: 'order' },
  { label: 'Deliveries', value: 'delivery' },
  { label: 'Messages', value: 'message' },
]

// Computed
const filteredNotifications = computed(() => {
  let filtered = notifications.value

  if (activeFilter.value === 'unread') {
    filtered = filtered.filter(n => !n.is_read)
  } else if (activeFilter.value !== 'all') {
    filtered = filtered.filter(n => n.type === activeFilter.value)
  }

  return filtered
})

// Methods
const toggleDropdown = () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    fetchNotifications()
  }
}

const closeDropdown = () => {
  isOpen.value = false
}

const fetchNotifications = async () => {
  loading.value = true
  try {
    const [notificationsRes, countRes] = await Promise.all([
      axios.get('/api/v1/notifications', { params: { per_page: 20 } }),
      axios.get('/api/v1/notifications/unread-count')
    ])

    if (notificationsRes.data?.success) {
      notifications.value = notificationsRes.data.data
    }

    if (countRes.data?.success) {
      unreadCount.value = countRes.data.data.count
    }
  } catch (error) {
    console.error('Error fetching notifications:', error)
  } finally {
    loading.value = false
  }
}

const markAsRead = async (id) => {
  try {
    const response = await axios.post(`/api/v1/notifications/${id}/read`)
    
    if (response.data?.success) {
      const notification = notifications.value.find(n => n.id === id)
      if (notification && !notification.is_read) {
        notification.is_read = true
        notification.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    }
  } catch (error) {
    console.error('Error marking notification as read:', error)
  }
}

const markAllAsRead = async () => {
  try {
    const response = await axios.post('/api/v1/notifications/read-all')
    
    if (response.data?.success) {
      notifications.value.forEach(n => {
        n.is_read = true
        n.read_at = new Date().toISOString()
      })
      unreadCount.value = 0
    }
  } catch (error) {
    console.error('Error marking all as read:', error)
  }
}

const deleteNotification = async (id) => {
  try {
    const response = await axios.delete(`/api/v1/notifications/${id}`)
    
    if (response.data?.success) {
      const notification = notifications.value.find(n => n.id === id)
      if (notification && !notification.is_read) {
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
      notifications.value = notifications.value.filter(n => n.id !== id)
    }
  } catch (error) {
    console.error('Error deleting notification:', error)
  }
}

const deleteAllRead = async () => {
  if (!confirm('Delete all read notifications?')) return

  try {
    const response = await axios.delete('/api/v1/notifications/read/all')
    
    if (response.data?.success) {
      notifications.value = notifications.value.filter(n => !n.is_read)
    }
  } catch (error) {
    console.error('Error deleting read notifications:', error)
  }
}

const handleNotificationClick = (notification) => {
  // Mark as read if unread
  if (!notification.is_read) {
    markAsRead(notification.id)
  }

  // Navigate based on notification type and data
  if (notification.data?.link) {
    router.push(notification.data.link)
    closeDropdown()
  }
}

const getNotificationIcon = (type) => {
  switch (type) {
    case 'order': return ShoppingCart
    case 'delivery': return Truck
    case 'message': return MessageSquare
    case 'promotion': return Tag
    default: return AlertCircle
  }
}

const getNotificationIconClass = (type) => {
  switch (type) {
    case 'order': return 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'
    case 'delivery': return 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400'
    case 'message': return 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400'
    case 'promotion': return 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400'
    default: return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
  }
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diffInSeconds = Math.floor((now - date) / 1000)

  if (diffInSeconds < 60) return 'Just now'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`
  
  return date.toLocaleDateString('en-AU', { month: 'short', day: 'numeric' })
}

// Click outside directive
const clickOutsideDirective = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value()
      }
    }
    document.addEventListener('click', el.clickOutsideEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent)
  }
}

const vClickOutside = clickOutsideDirective

// Polling for new notifications (every 30 seconds)
let pollingInterval = null

onMounted(() => {
  fetchNotifications()
  
  // Poll for new notifications every 30 seconds
  pollingInterval = setInterval(() => {
    if (!isOpen.value) {
      // Only fetch unread count when dropdown is closed to save bandwidth
      axios.get('/api/v1/notifications/unread-count')
        .then(response => {
          if (response.data?.success) {
            unreadCount.value = response.data.data.count
          }
        })
        .catch(error => console.error('Error polling notifications:', error))
    }
  }, 30000)
})

onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }
})
</script>

<style scoped>
/* Dropdown transition */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth scrolling */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 transparent;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #cbd5e0;
  border-radius: 3px;
}

.dark .overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #4b5563;
}
</style>
