<script setup>
/**
 * NotificationsPage.vue
 * Customer notifications page for viewing and managing notifications
 *
 * @requirement CUST-014 Create notifications page
 * @requirement CUST-015 Implement mark as read functionality
 */
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useCustomerNotificationsStore } from '@/stores/customerNotifications'
import { useNotificationStore } from '@/stores/notificationStore'
import {
  Bell,
  Package,
  CheckCircle,
  Tag,
  MessageCircle,
  Check,
  CheckCheck,
  AlertTriangle,
  RefreshCw,
  Loader2
} from 'lucide-vue-next'

const notificationsStore = useCustomerNotificationsStore()
const toast = useNotificationStore()

const isLoading = ref(true)
const error = ref(null)
const activeTab = ref('all') // 'all', 'unread'

// Icon mapping
const iconMap = {
  Package,
  CheckCircle,
  Tag,
  MessageCircle,
  Bell
}

// Computed
const displayedNotifications = computed(() => {
  if (activeTab.value === 'unread') {
    return notificationsStore.unreadNotifications
  }
  return notificationsStore.notifications
})

const hasNotifications = computed(() => displayedNotifications.value.length > 0)

// Methods
async function fetchNotifications() {
  isLoading.value = true
  error.value = null

  try {
    await notificationsStore.fetchNotifications()
  } catch (err) {
    error.value = 'Failed to load notifications'
    console.error('Failed to fetch notifications:', err)
  } finally {
    isLoading.value = false
  }
}

async function markAsRead(notification) {
  if (notification.is_read) return

  const result = await notificationsStore.markAsRead(notification.id)

  if (result.success) {
    toast.success('Notification marked as read')
  } else {
    toast.error(result.message)
  }
}

async function markAllAsRead() {
  if (!notificationsStore.hasUnread) return

  const result = await notificationsStore.markAllAsRead()

  if (result.success) {
    toast.success('All notifications marked as read')
  } else {
    toast.error(result.message)
  }
}

function getIconComponent(type) {
  const config = notificationsStore.getTypeConfig(type)
  return iconMap[config.icon] || Bell
}

function getIconColorClass(type) {
  const config = notificationsStore.getTypeConfig(type)
  const colors = {
    blue: 'bg-blue-100 text-blue-600',
    green: 'bg-green-100 text-green-600',
    purple: 'bg-purple-100 text-purple-600',
    yellow: 'bg-yellow-100 text-yellow-600',
    gray: 'bg-gray-100 text-gray-600'
  }
  return colors[config.color] || colors.gray
}

// Lifecycle
onMounted(() => {
  fetchNotifications()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <nav class="text-sm mb-4">
          <RouterLink to="/customer" class="text-gray-500 hover:text-[#CF0D0F]">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">Notifications</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
            <p class="text-gray-600 mt-1">
              {{ notificationsStore.unreadCount }} unread notifications
            </p>
          </div>

          <button v-if="notificationsStore.hasUnread" @click="markAllAsRead"
            :disabled="notificationsStore.isUpdating"
            class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
            <CheckCheck v-if="!notificationsStore.isUpdating" class="w-5 h-5 mr-2" />
            <Loader2 v-else class="w-5 h-5 mr-2 animate-spin" />
            Mark All as Read
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-lg shadow-sm border-2 border-gray-200 mb-6">
        <div class="flex border-b border-gray-200">
          <button @click="activeTab = 'all'" :class="[
            'flex-1 px-4 py-3 text-sm font-medium transition-colors',
            activeTab === 'all'
              ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F]'
              : 'text-gray-500 hover:text-gray-700'
          ]">
            All ({{ notificationsStore.notificationCount }})
          </button>
          <button @click="activeTab = 'unread'" :class="[
            'flex-1 px-4 py-3 text-sm font-medium transition-colors',
            activeTab === 'unread'
              ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F]'
              : 'text-gray-500 hover:text-gray-700'
          ]">
            Unread ({{ notificationsStore.unreadCount }})
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <div class="text-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent">
          </div>
          <p class="mt-4 text-gray-600">Loading notifications...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6">
        <div class="flex items-center">
          <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading notifications</h3>
            <p class="text-red-600 text-sm mt-1">{{ error }}</p>
          </div>
        </div>
        <button @click="fetchNotifications"
          class="mt-4 inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
          <RefreshCw class="w-4 h-4 mr-2" />
          Retry
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="!hasNotifications" class="bg-white rounded-lg shadow-sm border-2 border-gray-200 p-12 text-center">
        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <Bell class="w-10 h-10 text-gray-400" />
        </div>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">
          {{ activeTab === 'unread' ? 'No Unread Notifications' : 'No Notifications Yet' }}
        </h2>
        <p class="text-gray-600 mb-6">
          {{ activeTab === 'unread'
            ? "You're all caught up! No unread notifications."
            : 'You will see notifications about your orders and account here.'
          }}
        </p>
        <RouterLink v-if="activeTab === 'unread'" @click="activeTab = 'all'"
          class="text-[#CF0D0F] hover:text-[#F6211F] font-medium">
          View all notifications
        </RouterLink>
      </div>

      <!-- Notifications List -->
      <div v-else class="space-y-4">
        <div v-for="notification in displayedNotifications" :key="notification.id" :class="[
          'bg-white rounded-lg shadow-sm border-2 p-4 transition-all cursor-pointer hover:shadow-md',
          notification.is_read ? 'border-gray-200' : 'border-[#CF0D0F] bg-red-50'
        ]" @click="markAsRead(notification)">
          <div class="flex items-start gap-4">
            <!-- Icon -->
            <div :class="[
              'w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0',
              getIconColorClass(notification.type)
            ]">
              <component :is="getIconComponent(notification.type)" class="w-6 h-6" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between">
                <h3 :class="[
                  'font-medium',
                  notification.is_read ? 'text-gray-700' : 'text-gray-900'
                ]">
                  {{ notification.title }}
                </h3>
                <div class="flex items-center gap-2 ml-4">
                  <span class="text-sm text-gray-500 whitespace-nowrap">
                    {{ notificationsStore.formatTime(notification.created_at) }}
                  </span>
                  <div v-if="!notification.is_read" class="w-2 h-2 bg-[#CF0D0F] rounded-full"></div>
                </div>
              </div>

              <p class="text-gray-600 text-sm mt-1">{{ notification.message }}</p>

              <!-- Action Link -->
              <RouterLink v-if="notification.action_url" :to="notification.action_url" @click.stop
                class="inline-flex items-center text-sm text-[#CF0D0F] hover:text-[#F6211F] font-medium mt-2">
                {{ notification.action_text || 'View Details' }}
              </RouterLink>
            </div>

            <!-- Read Status -->
            <button v-if="!notification.is_read" @click.stop="markAsRead(notification)"
              class="p-2 text-gray-400 hover:text-[#CF0D0F] transition-colors" title="Mark as read">
              <Check class="w-5 h-5" />
            </button>
            <div v-else class="p-2">
              <CheckCheck class="w-5 h-5 text-green-500" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
