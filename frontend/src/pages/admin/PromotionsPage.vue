<script setup>
/**
 * Admin Promotions Page
 * Manage promotional offers and discount codes
 *
 * @requirement ADMIN-022 Create promotions management
 */
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAdminPromotionsStore } from '@/stores/adminPromotions'
import {
  Tag,
  Plus,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  Edit,
  Trash2,
  X,
  AlertTriangle,
  Percent,
  DollarSign,
  Calendar,
  ToggleLeft,
  ToggleRight,
  Copy,
  Check
} from 'lucide-vue-next'

const promotionsStore = useAdminPromotionsStore()

// Local state
const showPromotionModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const promotionToDelete = ref(null)
const isSubmitting = ref(false)
const copiedCode = ref(null)

// Promotion form data
const promotionForm = ref({
  name: '',
  code: '',
  type: 'percentage',
  value: 0,
  min_order_amount: 0,
  max_uses: null,
  max_uses_per_user: 1,
  start_date: '',
  end_date: '',
  is_active: true,
  description: ''
})

// Computed
const promotions = computed(() => promotionsStore.promotions)
const isLoading = computed(() => promotionsStore.isLoading)
const pagination = computed(() => promotionsStore.pagination)
const promotionTypes = computed(() => promotionsStore.promotionTypes)

// Lifecycle
onMounted(async () => {
  await fetchPromotions()
})

// Methods
async function fetchPromotions() {
  try {
    await promotionsStore.fetchPromotions()
  } catch (err) {
    console.error('Failed to fetch promotions:', err)
  }
}

function openCreateModal() {
  isEditing.value = false
  resetForm()
  // Set default dates
  const today = new Date()
  const nextMonth = new Date()
  nextMonth.setMonth(nextMonth.getMonth() + 1)
  promotionForm.value.start_date = today.toISOString().split('T')[0]
  promotionForm.value.end_date = nextMonth.toISOString().split('T')[0]
  showPromotionModal.value = true
}

function openEditModal(promotion) {
  isEditing.value = true
  promotionForm.value = {
    id: promotion.id,
    name: promotion.name || '',
    code: promotion.code || '',
    type: promotion.type || 'percentage',
    value: promotion.value || 0,
    min_order_amount: promotion.min_order_amount || 0,
    max_uses: promotion.max_uses || null,
    max_uses_per_user: promotion.max_uses_per_user || 1,
    start_date: promotion.start_date?.split('T')[0] || '',
    end_date: promotion.end_date?.split('T')[0] || '',
    is_active: promotion.is_active !== false,
    description: promotion.description || ''
  }
  showPromotionModal.value = true
}

function closePromotionModal() {
  showPromotionModal.value = false
  resetForm()
}

function resetForm() {
  promotionForm.value = {
    name: '',
    code: '',
    type: 'percentage',
    value: 0,
    min_order_amount: 0,
    max_uses: null,
    max_uses_per_user: 1,
    start_date: '',
    end_date: '',
    is_active: true,
    description: ''
  }
}

function generateCode() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
  let code = ''
  for (let i = 0; i < 8; i++) {
    code += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  promotionForm.value.code = code
}

async function savePromotion() {
  isSubmitting.value = true
  try {
    if (isEditing.value) {
      await promotionsStore.updatePromotion(promotionForm.value.id, promotionForm.value)
    } else {
      await promotionsStore.createPromotion(promotionForm.value)
    }
    
    closePromotionModal()
    await fetchPromotions()
  } catch (err) {
    console.error('Failed to save promotion:', err)
  } finally {
    isSubmitting.value = false
  }
}

function confirmDelete(promotion) {
  promotionToDelete.value = promotion
  showDeleteModal.value = true
}

async function deletePromotion() {
  if (!promotionToDelete.value) return
  
  isSubmitting.value = true
  try {
    await promotionsStore.deletePromotion(promotionToDelete.value.id)
    showDeleteModal.value = false
    promotionToDelete.value = null
    await fetchPromotions()
  } catch (err) {
    console.error('Failed to delete promotion:', err)
  } finally {
    isSubmitting.value = false
  }
}

async function toggleStatus(promotion) {
  try {
    await promotionsStore.togglePromotionStatus(promotion.id, !promotion.is_active)
    await fetchPromotions()
  } catch (err) {
    console.error('Failed to toggle status:', err)
  }
}

function copyCode(code) {
  navigator.clipboard.writeText(code)
  copiedCode.value = code
  setTimeout(() => {
    copiedCode.value = null
  }, 2000)
}

function formatDate(dateString) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0)
}

function formatValue(promotion) {
  if (promotion.type === 'percentage') {
    return `${promotion.value}%`
  }
  return formatCurrency(promotion.value)
}

function getStatus(promotion) {
  const now = new Date()
  const start = new Date(promotion.start_date)
  const end = new Date(promotion.end_date)

  if (!promotion.is_active) return { label: 'Inactive', color: 'bg-gray-100 text-gray-800' }
  if (now < start) return { label: 'Scheduled', color: 'bg-blue-100 text-blue-800' }
  if (now > end) return { label: 'Expired', color: 'bg-red-100 text-red-800' }
  return { label: 'Active', color: 'bg-green-100 text-green-800' }
}

function goToPage(page) {
  promotionsStore.setPage(page)
  fetchPromotions()
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
          <span class="text-gray-900">Promotions</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Promotions Management</h1>
            <p class="text-gray-600 mt-1">Create and manage discount codes and promotional offers</p>
          </div>
          <div class="flex items-center gap-3 mt-4 md:mt-0">
            <button @click="fetchPromotions" 
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
              :disabled="isLoading">
              <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" />
              Refresh
            </button>
            <button @click="openCreateModal" 
              class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors">
              <Plus class="w-4 h-4 mr-2" />
              Add Promotion
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-16">
        <div class="text-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent"></div>
          <p class="mt-4 text-gray-600">Loading promotions...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="promotionsStore.error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
          <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading promotions</h3>
            <p class="text-red-600 text-sm mt-1">{{ promotionsStore.error }}</p>
          </div>
        </div>
        <button @click="fetchPromotions" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
          Try Again
        </button>
      </div>

      <!-- Promotions Grid -->
      <div v-else>
        <div v-if="promotions.length === 0" class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-16 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <Tag class="w-8 h-8 text-gray-400" />
          </div>
          <p class="text-gray-500 mb-2">No promotions found</p>
          <p class="text-sm text-gray-400 mb-4">Get started by creating your first promotion</p>
          <button @click="openCreateModal" class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D]">
            <Plus class="w-4 h-4 inline mr-2" />
            Add Promotion
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Promotion Card -->
          <div 
            v-for="promotion in promotions" 
            :key="promotion.id"
            class="bg-white rounded-lg shadow-sm border-2 overflow-hidden hover:shadow-md transition-shadow"
            :class="promotion.is_active ? 'border-[#CF0D0F]' : 'border-gray-300'"
          >
            <!-- Card Header -->
            <div class="p-4 bg-gradient-to-r" :class="promotion.is_active ? 'from-[#CF0D0F] to-[#F6211F]' : 'from-gray-400 to-gray-500'">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-bold text-white">{{ promotion.name }}</h3>
                  <div class="flex items-center mt-1">
                    <button 
                      @click="copyCode(promotion.code)"
                      class="flex items-center text-white text-opacity-90 hover:text-opacity-100 text-sm font-mono bg-white bg-opacity-20 px-2 py-0.5 rounded"
                    >
                      {{ promotion.code }}
                      <Check v-if="copiedCode === promotion.code" class="w-3 h-3 ml-1" />
                      <Copy v-else class="w-3 h-3 ml-1" />
                    </button>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-2xl font-bold text-white">{{ formatValue(promotion) }}</p>
                  <p class="text-sm text-white text-opacity-80">{{ promotion.type === 'percentage' ? 'OFF' : 'Discount' }}</p>
                </div>
              </div>
            </div>

            <!-- Card Body -->
            <div class="p-4">
              <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-gray-500">Status:</span>
                  <span :class="['inline-flex px-2 py-1 text-xs font-medium rounded-full', getStatus(promotion).color]">
                    {{ getStatus(promotion).label }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-500">Valid:</span>
                  <span class="text-gray-900">{{ formatDate(promotion.start_date) }} - {{ formatDate(promotion.end_date) }}</span>
                </div>
                <div v-if="promotion.min_order_amount > 0" class="flex items-center justify-between">
                  <span class="text-gray-500">Min Order:</span>
                  <span class="text-gray-900">{{ formatCurrency(promotion.min_order_amount) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-500">Uses:</span>
                  <span class="text-gray-900">{{ promotion.uses_count || 0 }} / {{ promotion.max_uses || '∞' }}</span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-100">
                <button 
                  @click="toggleStatus(promotion)"
                  class="flex items-center text-sm"
                  :class="promotion.is_active ? 'text-green-600' : 'text-gray-500'"
                >
                  <ToggleRight v-if="promotion.is_active" class="w-5 h-5 mr-1" />
                  <ToggleLeft v-else class="w-5 h-5 mr-1" />
                  {{ promotion.is_active ? 'Active' : 'Inactive' }}
                </button>
                <div class="flex items-center space-x-2">
                  <button 
                    @click="openEditModal(promotion)"
                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                    <Edit class="w-4 h-4" />
                  </button>
                  <button 
                    @click="confirmDelete(promotion)"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.lastPage > 1" class="mt-6 flex items-center justify-center space-x-2">
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

    <!-- Create/Edit Promotion Modal -->
    <Teleport to="body">
      <div v-if="showPromotionModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closePromotionModal"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                  {{ isEditing ? 'Edit Promotion' : 'Create Promotion' }}
                </h3>
                <button @click="closePromotionModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                  <X class="w-5 h-5" />
                </button>
              </div>
            </div>

            <!-- Modal Content -->
            <form @submit.prevent="savePromotion" class="px-6 py-4 max-h-[70vh] overflow-y-auto">
              <div class="space-y-4">
                <!-- Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Promotion Name *</label>
                  <input 
                    v-model="promotionForm.name"
                    type="text" 
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  />
                </div>

                <!-- Code -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Promo Code *</label>
                  <div class="flex space-x-2">
                    <input 
                      v-model="promotionForm.code"
                      type="text"
                      required
                      class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F] uppercase font-mono"
                    />
                    <button 
                      type="button"
                      @click="generateCode"
                      class="px-3 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">
                      Generate
                    </button>
                  </div>
                </div>

                <!-- Type and Value -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Discount Type *</label>
                    <select 
                      v-model="promotionForm.type"
                      required
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
                      <option v-for="type in promotionTypes" :key="type.value" :value="type.value">
                        {{ type.label }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Value *</label>
                    <div class="relative">
                      <Percent v-if="promotionForm.type === 'percentage'" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                      <DollarSign v-else class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                      <input 
                        v-model.number="promotionForm.value"
                        type="number"
                        step="0.01"
                        min="0"
                        :max="promotionForm.type === 'percentage' ? 100 : undefined"
                        required
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                      />
                    </div>
                  </div>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                    <input 
                      v-model="promotionForm.start_date"
                      type="date"
                      required
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                    <input 
                      v-model="promotionForm.end_date"
                      type="date"
                      required
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                    />
                  </div>
                </div>

                <!-- Min Order Amount -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Order Amount</label>
                  <div class="relative">
                    <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input 
                      v-model.number="promotionForm.min_order_amount"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                    />
                  </div>
                </div>

                <!-- Usage Limits -->
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Total Uses</label>
                    <input 
                      v-model.number="promotionForm.max_uses"
                      type="number"
                      min="0"
                      placeholder="Unlimited"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Per User</label>
                    <input 
                      v-model.number="promotionForm.max_uses_per_user"
                      type="number"
                      min="1"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                    />
                  </div>
                </div>

                <!-- Description -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <textarea 
                    v-model="promotionForm.description"
                    rows="2"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  ></textarea>
                </div>

                <!-- Active Toggle -->
                <div>
                  <label class="flex items-center">
                    <input 
                      v-model="promotionForm.is_active"
                      type="checkbox"
                      class="w-4 h-4 text-[#CF0D0F] border-gray-300 rounded focus:ring-[#CF0D0F]"
                    />
                    <span class="ml-2 text-sm text-gray-700">Active immediately</span>
                  </label>
                </div>
              </div>
            </form>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button @click="closePromotionModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="savePromotion"
                :disabled="isSubmitting || !promotionForm.name.trim() || !promotionForm.code.trim()"
                class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50">
                {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update Promotion' : 'Create Promotion') }}
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
                  <h3 class="text-lg font-semibold text-gray-900">Delete Promotion</h3>
                  <p class="text-sm text-gray-500 mt-1">
                    Are you sure you want to delete "{{ promotionToDelete?.name }}"? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
              <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="deletePromotion"
                :disabled="isSubmitting"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                {{ isSubmitting ? 'Deleting...' : 'Delete' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
