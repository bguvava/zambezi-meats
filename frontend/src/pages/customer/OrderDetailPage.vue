<script setup>
/**
 * OrderDetailPage.vue
 * Individual order detail page with tracking
 */
import { ref, onMounted, computed } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useOrdersStore } from '@/stores/orders'
import {
  TruckIcon,
  CheckCircleIcon,
  ClockIcon,
  PackageIcon,
  HomeIcon,
  AlertCircleIcon,
  RefreshCwIcon
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const ordersStore = useOrdersStore()
const orderNumber = route.params.orderNumber

const isLoading = ref(true)
const error = ref(null)

const order = computed(() => ordersStore.currentOrder)

// Status progression
const statusSteps = computed(() => {
  if (!order.value) return []

  const steps = [
    { key: 'confirmed', label: 'Confirmed', icon: CheckCircleIcon },
    { key: 'processing', label: 'Processing', icon: PackageIcon },
    { key: 'out_for_delivery', label: 'Out for Delivery', icon: TruckIcon },
    { key: 'delivered', label: 'Delivered', icon: HomeIcon }
  ]

  const statusIndex = {
    'pending': -1,
    'confirmed': 0,
    'processing': 1,
    'ready': 1,
    'out_for_delivery': 2,
    'delivered': 3,
    'cancelled': -2
  }

  const currentIndex = statusIndex[order.value.status] ?? -1

  return steps.map((step, index) => ({
    ...step,
    isComplete: index <= currentIndex,
    isCurrent: index === currentIndex
  }))
})

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    confirmed: 'bg-blue-100 text-blue-800 border-blue-200',
    processing: 'bg-purple-100 text-purple-800 border-purple-200',
    ready: 'bg-teal-100 text-teal-800 border-teal-200',
    out_for_delivery: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    delivered: 'bg-green-100 text-green-800 border-green-200',
    cancelled: 'bg-red-100 text-red-800 border-red-200'
  }
  return colors[status] || 'bg-gray-100 text-gray-800 border-gray-200'
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    processing: 'Processing',
    ready: 'Ready for Delivery',
    out_for_delivery: 'Out for Delivery',
    delivered: 'Delivered',
    cancelled: 'Cancelled'
  }
  return labels[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-AU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (amount) => {
  if (!amount) return 'AU$0.00'
  return new Intl.NumberFormat('en-AU', {
    style: 'currency',
    currency: 'AUD'
  }).format(amount)
}

onMounted(async () => {
  try {
    isLoading.value = true
    error.value = null
    await ordersStore.fetchOrder(orderNumber)
  } catch (err) {
    console.error('Failed to fetch order:', err)
    error.value = 'Failed to load order details'
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Loading State -->
      <div v-if="isLoading" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12">
        <div class="flex flex-col items-center justify-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent">
          </div>
          <p class="mt-4 text-gray-600">Loading order details...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-8">
        <div class="flex items-center">
          <AlertCircleIcon class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading order</h3>
            <p class="text-red-600 text-sm mt-1">{{ error }}</p>
          </div>
        </div>
        <div class="mt-4 flex gap-3">
          <button @click="router.push('/customer/orders')"
            class="px-4 py-2 bg-white border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            Back to Orders
          </button>
          <button @click="fetchOrder"
            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
            <RefreshCwIcon class="w-4 h-4 mr-2" />
            Retry
          </button>
        </div>
      </div>

      <!-- Order Content -->
      <div v-else-if="order">
        <!-- Page Header -->
        <div class="mb-8">
          <nav class="text-sm mb-4">
            <RouterLink to="/customer" class="text-gray-500 hover:text-[#CF0D0F]">Dashboard</RouterLink>
            <span class="text-gray-400 mx-2">/</span>
            <RouterLink to="/customer/orders" class="text-gray-500 hover:text-[#CF0D0F]">Orders</RouterLink>
            <span class="text-gray-400 mx-2">/</span>
            <span class="text-gray-900">{{ order.order_number }}</span>
          </nav>
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Order #{{ order.order_number }}</h1>
              <p class="text-gray-600">Placed on {{ formatDate(order.created_at) }}</p>
            </div>
            <span class="inline-block px-4 py-2 text-sm font-medium rounded-full border-2"
              :class="getStatusColor(order.status)">
              {{ getStatusLabel(order.status) }}
            </span>
          </div>
        </div>

        <!-- Order Tracking (only show if not cancelled) -->
        <div v-if="order.status !== 'cancelled'"
          class="bg-white rounded-lg shadow-sm border-2 border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-6">Order Status</h2>

          <div class="flex items-center justify-between">
            <template v-for="(step, index) in statusSteps" :key="step.key">
              <!-- Step -->
              <div class="flex flex-col items-center">
                <div class="w-12 h-12 rounded-full flex items-center justify-center transition-colors"
                  :class="step.isComplete ? 'bg-[#CF0D0F] text-white' : 'bg-gray-200 text-gray-400'">
                  <component :is="step.icon" class="w-6 h-6" />
                </div>
                <p class="text-sm mt-2 text-center max-w-[80px]"
                  :class="step.isComplete ? 'font-medium text-gray-800' : 'text-gray-500'">
                  {{ step.label }}
                </p>
              </div>

              <!-- Connector Line -->
              <div v-if="index < statusSteps.length - 1" class="flex-1 h-1 mx-4 transition-colors"
                :class="step.isComplete ? 'bg-[#CF0D0F]' : 'bg-gray-200'"></div>
            </template>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Order Items -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border-2 border-gray-200 p-6">
              <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Items</h2>

              <div v-if="order.items && order.items.length > 0" class="space-y-4">
                <div v-for="item in order.items" :key="item.id"
                  class="flex items-center space-x-4 pb-4 border-b border-gray-200 last:border-0">
                  <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                    <img v-if="item.product_image" :src="item.product_image" :alt="item.product_name"
                      class="w-full h-full object-cover" />
                    <PackageIcon v-else class="w-8 h-8 text-gray-400" />
                  </div>
                  <div class="flex-1">
                    <p class="font-medium text-gray-800">{{ item.product_name }}</p>
                    <p class="text-sm text-gray-500">
                      {{ formatCurrency(item.price) }} × {{ item.quantity }}
                    </p>
                  </div>
                  <p class="font-medium text-gray-800">{{ formatCurrency(item.subtotal) }}</p>
                </div>
              </div>

              <div v-else class="py-8 text-center text-gray-500">
                No items in this order
              </div>
            </div>
          </div>

          <!-- Order Summary & Details -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Order Summary -->
            <div class="bg-white rounded-lg shadow-sm border-2 border-gray-200 p-6">
              <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Summary</h2>
              <div class="space-y-3">
                <div class="flex justify-between text-gray-600">
                  <span>Subtotal</span>
                  <span>{{ formatCurrency(order.subtotal) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                  <span>Delivery Fee</span>
                  <span>{{ order.delivery_fee > 0 ? formatCurrency(order.delivery_fee) : 'FREE' }}</span>
                </div>
                <div v-if="order.discount > 0" class="flex justify-between text-green-600">
                  <span>Discount</span>
                  <span>-{{ formatCurrency(order.discount) }}</span>
                </div>
                <div class="border-t-2 border-gray-200 pt-3 flex justify-between font-semibold text-gray-900 text-lg">
                  <span>Total</span>
                  <span class="text-[#CF0D0F]">{{ formatCurrency(order.total) }}</span>
                </div>
              </div>
            </div>

            <!-- Delivery Address -->
            <div v-if="order.address" class="bg-white rounded-lg shadow-sm border-2 border-gray-200 p-6">
              <h2 class="text-lg font-semibold text-gray-800 mb-4">Delivery Address</h2>
              <div class="text-gray-600 space-y-1">
                <p v-if="order.address.address_line_1">{{ order.address.address_line_1 }}</p>
                <p v-if="order.address.address_line_2">{{ order.address.address_line_2 }}</p>
                <p v-if="order.address.suburb">{{ order.address.suburb }}</p>
                <p v-if="order.address.state || order.address.postcode">
                  {{ order.address.state }} {{ order.address.postcode }}
                </p>
                <p v-if="order.address.phone" class="pt-2 border-t border-gray-200 mt-2">
                  {{ order.address.phone }}
                </p>
              </div>
            </div>

            <!-- Delivery Instructions -->
            <div v-if="order.delivery_instructions" class="bg-blue-50 rounded-lg border-2 border-blue-200 p-4">
              <h3 class="text-sm font-semibold text-blue-800 mb-2">Delivery Instructions</h3>
              <p class="text-sm text-blue-700">{{ order.delivery_instructions }}</p>
            </div>

            <!-- Order Notes -->
            <div v-if="order.notes" class="bg-gray-50 rounded-lg border-2 border-gray-200 p-4">
              <h3 class="text-sm font-semibold text-gray-800 mb-2">Order Notes</h3>
              <p class="text-sm text-gray-600">{{ order.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Back Link -->
        <div class="mt-8">
          <RouterLink to="/customer/orders"
            class="inline-flex items-center text-[#CF0D0F] hover:text-[#F6211F] font-medium transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Orders
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
