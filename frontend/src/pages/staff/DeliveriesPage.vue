<script setup>
/**
 * DeliveriesPage.vue (Staff)
 * Staff delivery management with route tracking and proof of delivery capture
 */
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useStaffDeliveriesStore } from '@/stores/staffDeliveries'
import {
  Truck,
  MapPin,
  Phone,
  User,
  Clock,
  CheckCircle,
  Camera,
  Package,
  Navigation,
  RefreshCw,
  X,
  AlertTriangle,
  Calendar,
  Play,
  FileCheck,
  Signature
} from 'lucide-vue-next'

const deliveriesStore = useStaffDeliveriesStore()

// Local state
const activeTab = ref('deliveries') // 'deliveries' or 'pickups'
const selectedTimeSlot = ref('')
const showPODModal = ref(false)
const showPickupModal = ref(false)
const selectedDeliveryForPOD = ref(null)
const selectedPickup = ref(null)
const receiverName = ref('')
const signatureDataUrl = ref('')
const photoFile = ref(null)
const photoPreview = ref(null)
const isSubmittingPOD = ref(false)
const signatureCanvas = ref(null)
const isDrawing = ref(false)

// Time slot options
const timeSlots = [
  { value: '', label: 'All Time Slots' },
  { value: '08:00-10:00', label: '8:00 AM - 10:00 AM' },
  { value: '10:00-12:00', label: '10:00 AM - 12:00 PM' },
  { value: '14:00-16:00', label: '2:00 PM - 4:00 PM' },
  { value: '16:00-18:00', label: '4:00 PM - 6:00 PM' }
]

// Status configuration
const statusConfig = {
  scheduled: { label: 'Scheduled', bgColor: 'bg-gray-100', textColor: 'text-gray-800' },
  ready_for_delivery: { label: 'Ready', bgColor: 'bg-purple-100', textColor: 'text-purple-800' },
  out_for_delivery: { label: 'Out for Delivery', bgColor: 'bg-blue-100', textColor: 'text-blue-800' },
  delivered: { label: 'Delivered', bgColor: 'bg-green-100', textColor: 'text-green-800' },
  ready_for_pickup: { label: 'Ready', bgColor: 'bg-purple-100', textColor: 'text-purple-800' },
  picked_up: { label: 'Picked Up', bgColor: 'bg-green-100', textColor: 'text-green-800' }
}

// Computed
const isLoading = computed(() => deliveriesStore.isLoading)
const deliveries = computed(() => {
  if (!selectedTimeSlot.value) return deliveriesStore.deliveries
  return deliveriesStore.deliveries.filter(d => 
    d.delivery_time_slot === selectedTimeSlot.value || d.time_slot === selectedTimeSlot.value
  )
})
const pickups = computed(() => deliveriesStore.pickups)
const deliveryCounts = computed(() => deliveriesStore.deliveryCounts)
const pickupCounts = computed(() => deliveriesStore.pickupCounts)
const slotCounts = computed(() => {
  const counts = {}
  timeSlots.forEach(slot => {
    if (slot.value) {
      counts[slot.value] = deliveriesStore.deliveriesByTimeSlot[slot.value]?.length || 0
    }
  })
  return counts
})

// Lifecycle
onMounted(async () => {
  await fetchAll()
})

// Methods
async function fetchAll() {
  try {
    await deliveriesStore.fetchAll()
  } catch (err) {
    console.error('Failed to fetch data:', err)
  }
}

function getStatusConfig(status) {
  return statusConfig[status] || { label: status, bgColor: 'bg-gray-100', textColor: 'text-gray-800' }
}

function formatTime(timeSlot) {
  const mapping = {
    '08:00-10:00': '8:00 AM - 10:00 AM',
    '10:00-12:00': '10:00 AM - 12:00 PM',
    '14:00-16:00': '2:00 PM - 4:00 PM',
    '16:00-18:00': '4:00 PM - 6:00 PM'
  }
  return mapping[timeSlot] || timeSlot
}

async function startDelivery(delivery) {
  try {
    await deliveriesStore.markOutForDelivery(delivery.order_id || delivery.id)
  } catch (err) {
    console.error('Failed to start delivery:', err)
  }
}

function openPODModal(delivery) {
  selectedDeliveryForPOD.value = delivery
  signatureDataUrl.value = ''
  photoFile.value = null
  photoPreview.value = null
  receiverName.value = ''
  showPODModal.value = true
  // Initialize signature canvas after modal opens
  setTimeout(initSignatureCanvas, 100)
}

function closePODModal() {
  showPODModal.value = false
  selectedDeliveryForPOD.value = null
  clearSignature()
}

function initSignatureCanvas() {
  const canvas = signatureCanvas.value
  if (!canvas) return
  
  const ctx = canvas.getContext('2d')
  ctx.strokeStyle = '#000'
  ctx.lineWidth = 2
  ctx.lineCap = 'round'
  ctx.fillStyle = '#fff'
  ctx.fillRect(0, 0, canvas.width, canvas.height)
}

function startDrawing(e) {
  isDrawing.value = true
  const canvas = signatureCanvas.value
  const ctx = canvas.getContext('2d')
  const rect = canvas.getBoundingClientRect()
  const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left
  const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top
  ctx.beginPath()
  ctx.moveTo(x, y)
}

function draw(e) {
  if (!isDrawing.value) return
  const canvas = signatureCanvas.value
  const ctx = canvas.getContext('2d')
  const rect = canvas.getBoundingClientRect()
  const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left
  const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top
  ctx.lineTo(x, y)
  ctx.stroke()
}

function stopDrawing() {
  isDrawing.value = false
  if (signatureCanvas.value) {
    signatureDataUrl.value = signatureCanvas.value.toDataURL('image/png')
  }
}

function clearSignature() {
  const canvas = signatureCanvas.value
  if (canvas) {
    const ctx = canvas.getContext('2d')
    ctx.fillStyle = '#fff'
    ctx.fillRect(0, 0, canvas.width, canvas.height)
  }
  signatureDataUrl.value = ''
}

function handlePhotoChange(event) {
  const file = event.target.files[0]
  if (file) {
    photoFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      photoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

async function submitPOD() {
  if (!selectedDeliveryForPOD.value || !receiverName.value.trim()) return
  
  isSubmittingPOD.value = true
  try {
    const formData = new FormData()
    formData.append('receiver_name', receiverName.value)
    
    if (signatureDataUrl.value) {
      // Convert base64 to blob
      const signatureBlob = await fetch(signatureDataUrl.value).then(r => r.blob())
      formData.append('signature', signatureBlob, 'signature.png')
    }
    
    if (photoFile.value) {
      formData.append('photo', photoFile.value)
    }

    await deliveriesStore.uploadProofOfDelivery(
      selectedDeliveryForPOD.value.order_id || selectedDeliveryForPOD.value.id,
      formData
    )
    
    closePODModal()
    await fetchAll()
  } catch (err) {
    console.error('Failed to submit POD:', err)
  } finally {
    isSubmittingPOD.value = false
  }
}

function openPickupModal(pickup) {
  selectedPickup.value = pickup
  receiverName.value = ''
  showPickupModal.value = true
}

function closePickupModal() {
  showPickupModal.value = false
  selectedPickup.value = null
}

async function confirmPickup() {
  if (!selectedPickup.value || !receiverName.value.trim()) return
  
  try {
    await deliveriesStore.markAsPickedUp(
      selectedPickup.value.order_id || selectedPickup.value.id,
      receiverName.value
    )
    closePickupModal()
    await fetchAll()
  } catch (err) {
    console.error('Failed to confirm pickup:', err)
  }
}

function openMaps(address) {
  const encodedAddress = encodeURIComponent(address)
  window.open(`https://www.google.com/maps/search/?api=1&query=${encodedAddress}`, '_blank')
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0)
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <nav class="text-sm mb-4">
          <RouterLink to="/staff" class="text-gray-500 hover:text-[#CF0D0F]">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">My Deliveries</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">My Deliveries & Pickups</h1>
            <p class="text-gray-600 mt-1">
              <Calendar class="w-4 h-4 inline mr-1" />
              {{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
            </p>
          </div>
          <button @click="fetchAll" 
            class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors"
            :disabled="isLoading">
            <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" />
            Refresh
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] mb-6">
        <div class="flex border-b border-gray-200">
          <button 
            @click="activeTab = 'deliveries'"
            :class="[
              'flex-1 px-6 py-4 text-sm font-medium transition-colors',
              activeTab === 'deliveries' 
                ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F] bg-red-50' 
                : 'text-gray-500 hover:text-gray-700'
            ]">
            <Truck class="w-5 h-5 inline mr-2" />
            Deliveries ({{ deliveryCounts.total }})
          </button>
          <button 
            @click="activeTab = 'pickups'"
            :class="[
              'flex-1 px-6 py-4 text-sm font-medium transition-colors',
              activeTab === 'pickups' 
                ? 'text-[#CF0D0F] border-b-2 border-[#CF0D0F] bg-red-50' 
                : 'text-gray-500 hover:text-gray-700'
            ]">
            <Package class="w-5 h-5 inline mr-2" />
            Pickups ({{ pickupCounts.total }})
          </button>
        </div>
      </div>

      <!-- Deliveries Tab -->
      <div v-if="activeTab === 'deliveries'">
        <!-- Time Slots Overview -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div v-for="slot in timeSlots.filter(s => s.value)" :key="slot.value"
            @click="selectedTimeSlot = selectedTimeSlot === slot.value ? '' : slot.value"
            :class="[
              'bg-white rounded-lg shadow-sm border-2 p-4 cursor-pointer transition-all',
              selectedTimeSlot === slot.value ? 'border-[#CF0D0F] ring-2 ring-red-100' : 'border-gray-200 hover:border-gray-300'
            ]">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-500">{{ slot.label }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ slotCounts[slot.value] }}</p>
              </div>
              <Clock class="w-8 h-8 text-gray-400" />
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center py-16">
          <div class="text-center">
            <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent"></div>
            <p class="mt-4 text-gray-600">Loading deliveries...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="deliveriesStore.error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
          <div class="flex items-center">
            <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
            <div>
              <h3 class="text-red-800 font-medium">Error loading deliveries</h3>
              <p class="text-red-600 text-sm mt-1">{{ deliveriesStore.error }}</p>
            </div>
          </div>
        </div>

        <!-- Deliveries List -->
        <div v-else class="space-y-4">
          <div v-if="!deliveries.length" class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-12 text-center">
            <Truck class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Deliveries</h3>
            <p class="text-gray-500">No deliveries scheduled for today</p>
          </div>

          <div v-for="delivery in deliveries" :key="delivery.id"
            class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-6 hover:shadow-lg transition-shadow">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
              <div class="flex-1">
                <div class="flex items-start justify-between mb-4">
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                      Order #{{ delivery.order_number || delivery.order?.order_number || delivery.id }}
                    </h3>
                    <span :class="[
                      'inline-flex px-2 py-1 text-xs font-medium rounded-full mt-1',
                      getStatusConfig(delivery.status).bgColor,
                      getStatusConfig(delivery.status).textColor
                    ]">
                      {{ getStatusConfig(delivery.status).label }}
                    </span>
                  </div>
                  <div class="text-right">
                    <p class="text-sm text-gray-500">
                      <Clock class="w-4 h-4 inline mr-1" />
                      {{ formatTime(delivery.delivery_time_slot || delivery.time_slot) }}
                    </p>
                    <p class="font-bold text-[#CF0D0F] mt-1">{{ formatCurrency(delivery.total || delivery.order?.total) }}</p>
                  </div>
                </div>

                <div class="space-y-2 text-sm">
                  <div class="flex items-center text-gray-600">
                    <User class="w-4 h-4 mr-2 text-gray-400" />
                    {{ delivery.customer?.name || delivery.customer_name || 'Customer' }}
                  </div>
                  <div class="flex items-center text-gray-600">
                    <Phone class="w-4 h-4 mr-2 text-gray-400" />
                    {{ delivery.customer?.phone || delivery.phone || '-' }}
                  </div>
                  <div class="flex items-start text-gray-600">
                    <MapPin class="w-4 h-4 mr-2 mt-0.5 text-gray-400" />
                    <span>{{ delivery.delivery_address || delivery.address || '-' }}</span>
                  </div>
                </div>
              </div>

              <div class="mt-4 lg:mt-0 lg:ml-6 flex flex-col sm:flex-row gap-2">
                <button 
                  v-if="delivery.delivery_address || delivery.address"
                  @click="openMaps(delivery.delivery_address || delivery.address)"
                  class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                  <Navigation class="w-4 h-4 mr-2" />
                  Navigate
                </button>
                
                <button 
                  v-if="delivery.status === 'ready_for_delivery' || delivery.status === 'scheduled'"
                  @click="startDelivery(delivery)"
                  :disabled="deliveriesStore.isUpdating"
                  class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                  <Play class="w-4 h-4 mr-2" />
                  Start Delivery
                </button>

                <button 
                  v-if="delivery.status === 'out_for_delivery'"
                  @click="openPODModal(delivery)"
                  class="inline-flex items-center justify-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors">
                  <Camera class="w-4 h-4 mr-2" />
                  Complete & POD
                </button>

                <div v-if="delivery.status === 'delivered'" class="inline-flex items-center text-green-600">
                  <CheckCircle class="w-5 h-5 mr-2" />
                  Delivered
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pickups Tab -->
      <div v-if="activeTab === 'pickups'">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center py-16">
          <div class="text-center">
            <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent"></div>
            <p class="mt-4 text-gray-600">Loading pickups...</p>
          </div>
        </div>

        <!-- Pickups List -->
        <div v-else class="space-y-4">
          <div v-if="!pickups.length" class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-12 text-center">
            <Package class="w-16 h-16 text-gray-300 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Pickups</h3>
            <p class="text-gray-500">No pickups ready for today</p>
          </div>

          <div v-for="pickup in pickups" :key="pickup.id"
            class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-6 hover:shadow-lg transition-shadow">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
              <div class="flex-1">
                <div class="flex items-start justify-between mb-4">
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                      Order #{{ pickup.order_number || pickup.order?.order_number || pickup.id }}
                    </h3>
                    <span :class="[
                      'inline-flex px-2 py-1 text-xs font-medium rounded-full mt-1',
                      getStatusConfig(pickup.status).bgColor,
                      getStatusConfig(pickup.status).textColor
                    ]">
                      {{ getStatusConfig(pickup.status).label }}
                    </span>
                  </div>
                  <p class="font-bold text-[#CF0D0F]">{{ formatCurrency(pickup.total || pickup.order?.total) }}</p>
                </div>

                <div class="space-y-2 text-sm">
                  <div class="flex items-center text-gray-600">
                    <User class="w-4 h-4 mr-2 text-gray-400" />
                    {{ pickup.customer?.name || pickup.customer_name || 'Customer' }}
                  </div>
                  <div class="flex items-center text-gray-600">
                    <Phone class="w-4 h-4 mr-2 text-gray-400" />
                    {{ pickup.customer?.phone || pickup.phone || '-' }}
                  </div>
                </div>
              </div>

              <div class="mt-4 lg:mt-0 lg:ml-6">
                <button 
                  v-if="pickup.status === 'ready_for_pickup'"
                  @click="openPickupModal(pickup)"
                  class="inline-flex items-center justify-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors">
                  <FileCheck class="w-4 h-4 mr-2" />
                  Confirm Pickup
                </button>

                <div v-if="pickup.status === 'picked_up'" class="inline-flex items-center text-green-600">
                  <CheckCircle class="w-5 h-5 mr-2" />
                  Picked Up
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- POD Modal -->
    <Teleport to="body">
      <div v-if="showPODModal && selectedDeliveryForPOD" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closePODModal"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Proof of Delivery</h3>
              <button @click="closePODModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                <X class="w-5 h-5" />
              </button>
            </div>

            <div class="px-6 py-4 space-y-6">
              <!-- Receiver Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Receiver Name *</label>
                <input 
                  v-model="receiverName"
                  type="text" 
                  placeholder="Enter receiver's name"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                />
              </div>

              <!-- Signature -->
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="block text-sm font-medium text-gray-700">
                    <Signature class="w-4 h-4 inline mr-1" />
                    Signature
                  </label>
                  <button @click="clearSignature" class="text-sm text-[#CF0D0F] hover:underline">Clear</button>
                </div>
                <div class="border-2 border-dashed border-gray-300 rounded-lg overflow-hidden">
                  <canvas 
                    ref="signatureCanvas"
                    width="400" 
                    height="150"
                    class="w-full touch-none cursor-crosshair bg-white"
                    @mousedown="startDrawing"
                    @mousemove="draw"
                    @mouseup="stopDrawing"
                    @mouseleave="stopDrawing"
                    @touchstart.prevent="startDrawing"
                    @touchmove.prevent="draw"
                    @touchend="stopDrawing"
                  ></canvas>
                </div>
              </div>

              <!-- Photo -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  <Camera class="w-4 h-4 inline mr-1" />
                  Photo (Optional)
                </label>
                <div v-if="photoPreview" class="relative mb-2">
                  <img :src="photoPreview" alt="Preview" class="w-full h-40 object-cover rounded-lg" />
                  <button 
                    @click="photoFile = null; photoPreview = null"
                    class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full">
                    <X class="w-4 h-4" />
                  </button>
                </div>
                <input 
                  type="file" 
                  accept="image/*"
                  capture="environment"
                  @change="handlePhotoChange"
                  class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#CF0D0F] file:text-white hover:file:bg-[#B00B0D]"
                />
              </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button @click="closePODModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="submitPOD"
                :disabled="!receiverName.trim() || isSubmittingPOD"
                class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50">
                <span v-if="isSubmittingPOD">Submitting...</span>
                <span v-else>Submit POD</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Pickup Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showPickupModal && selectedPickup" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closePickupModal"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Confirm Pickup</h3>
              <button @click="closePickupModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                <X class="w-5 h-5" />
              </button>
            </div>

            <div class="px-6 py-4">
              <p class="text-gray-600 mb-4">Order #{{ selectedPickup.order_number || selectedPickup.id }}</p>
              <label class="block text-sm font-medium text-gray-700 mb-1">Receiver Name *</label>
              <input 
                v-model="receiverName"
                type="text" 
                placeholder="Enter name of person collecting"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
              />
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button @click="closePickupModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="confirmPickup"
                :disabled="!receiverName.trim() || deliveriesStore.isUpdating"
                class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50">
                Confirm Pickup
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
