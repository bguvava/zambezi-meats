<script setup>
/**
 * AddressesPage.vue
 * Customer delivery addresses management page with full CRUD
 */
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import { toast } from 'vue-sonner'
import { MapPin, Plus, Edit2, Trash2, Star } from 'lucide-vue-next'

const addresses = ref([])
const isLoading = ref(false)
const showAddModal = ref(false)
const showEditModal = ref(false)
const editingAddress = ref(null)

const form = ref({
  label: '',
  street: '',
  suburb: '',
  city: '',
  state: '',
  postcode: '',
  is_default: false
})

const errors = ref({})

onMounted(async () => {
  await fetchAddresses()
})

async function fetchAddresses() {
  isLoading.value = true
  try {
    const response = await api.get('/customer/addresses')
    if (response.data?.success) {
      addresses.value = response.data.addresses || []
    }
  } catch (error) {
    console.error('Failed to fetch addresses:', error)
    toast.error('Failed to load addresses')
  } finally {
    isLoading.value = false
  }
}

function openAddModal() {
  form.value = {
    label: '',
    street: '',
    suburb: '',
    city: '',
    state: '',
    postcode: '',
    is_default: addresses.value.length === 0
  }
  errors.value = {}
  showAddModal.value = true
}

function openEditModal(address) {
  editingAddress.value = address
  form.value = { ...address }
  errors.value = {}
  showEditModal.value = true
}

function closeModals() {
  showAddModal.value = false
  showEditModal.value = false
  editingAddress.value = null
}

async function saveAddress() {
  errors.value = {}
  try {
    const response = await api.post('/customer/addresses', form.value)
    if (response.data?.success) {
      toast.success('Address added successfully')
      await fetchAddresses()
      closeModals()
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
    toast.error(error.response?.data?.message || 'Failed to add address')
  }
}

async function updateAddress() {
  errors.value = {}
  try {
    const response = await api.put(`/customer/addresses/${editingAddress.value.id}`, form.value)
    if (response.data?.success) {
      toast.success('Address updated successfully')
      await fetchAddresses()
      closeModals()
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
    toast.error(error.response?.data?.message || 'Failed to update address')
  }
}

async function deleteAddress(address) {
  if (!confirm(`Are you sure you want to delete ${address.label}?`)) return

  try {
    const response = await api.delete(`/customer/addresses/${address.id}`)
    if (response.data?.success) {
      toast.success('Address deleted successfully')
      await fetchAddresses()
    }
  } catch (error) {
    toast.error('Failed to delete address')
  }
}
</script>

<template>
  <div class="bg-gray-50 py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="mb-6">
        <nav class="text-sm mb-3">
          <RouterLink to="/customer" class="text-gray-500 hover:text-[#CF0D0F]">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">Addresses</span>
        </nav>
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">My Addresses</h1>
            <p class="text-sm text-gray-600">Manage your delivery addresses</p>
          </div>
          <button @click="openAddModal"
            class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors">
            <Plus class="w-4 h-4 mr-2" />
            Add Address
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#CF0D0F]"></div>
        <p class="mt-2 text-gray-600">Loading addresses...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="!addresses.length" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <MapPin class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h2 class="text-xl font-semibold text-gray-800 mb-2">No Addresses Saved</h2>
        <p class="text-gray-600 mb-6">Add a delivery address to make checkout faster.</p>
        <button @click="openAddModal"
          class="inline-flex items-center px-6 py-3 bg-[#CF0D0F] text-white font-medium rounded-lg hover:bg-[#F6211F] transition-colors">
          <Plus class="w-5 h-5 mr-2" />
          Add Your First Address
        </button>
      </div>

      <!-- Addresses Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="address in addresses" :key="address.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-2">
              <span v-if="address.is_default"
                class="inline-flex items-center gap-1 px-2 py-0.5 bg-[#CF0D0F] text-white text-xs rounded">
                <Star class="w-3 h-3" />
                Default
              </span>
              <span class="font-semibold text-gray-800">{{ address.label }}</span>
            </div>
            <div class="flex gap-2">
              <button @click="openEditModal(address)" class="text-gray-500 hover:text-[#CF0D0F] transition-colors"
                title="Edit">
                <Edit2 class="w-4 h-4" />
              </button>
              <button @click="deleteAddress(address)" class="text-gray-500 hover:text-red-600 transition-colors"
                title="Delete">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
          <p class="text-sm text-gray-600 leading-relaxed">
            {{ address.street }}<br />
            <span v-if="address.suburb">{{ address.suburb }}<br /></span>
            {{ address.city }}, {{ address.state }} {{ address.postcode }}
          </p>
        </div>
      </div>

      <!-- Add/Edit Modal -->
      <Teleport to="body">
        <div v-if="showAddModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
          <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModals"></div>

            <div
              class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
              <div class="bg-white px-6 pt-5 pb-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                  {{ showEditModal ? 'Edit Address' : 'Add New Address' }}
                </h3>

                <form @submit.prevent="showEditModal ? updateAddress() : saveAddress()" class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Label</label>
                    <input v-model="form.label" type="text" placeholder="e.g., Home, Office"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#CF0D0F] focus:border-[#CF0D0F] sm:text-sm"
                      required />
                    <p v-if="errors.label" class="mt-1 text-sm text-red-600">{{ errors.label[0] }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                    <input v-model="form.street" type="text" placeholder="123 Main Street"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#CF0D0F] focus:border-[#CF0D0F] sm:text-sm"
                      required />
                    <p v-if="errors.street" class="mt-1 text-sm text-red-600">{{ errors.street[0] }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Suburb (Optional)</label>
                    <input v-model="form.suburb" type="text" placeholder="Suburb name"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#CF0D0F] focus:border-[#CF0D0F] sm:text-sm" />
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                      <input v-model="form.city" type="text" placeholder="Sydney"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#CF0D0F] focus:border-[#CF0D0F] sm:text-sm"
                        required />
                      <p v-if="errors.city" class="mt-1 text-sm text-red-600">{{ errors.city[0] }}</p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                      <input v-model="form.state" type="text" placeholder="NSW"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#CF0D0F] focus:border-[#CF0D0F] sm:text-sm"
                        required />
                      <p v-if="errors.state" class="mt-1 text-sm text-red-600">{{ errors.state[0] }}</p>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                    <input v-model="form.postcode" type="text" placeholder="2000" maxlength="4" pattern="[0-9]{4}"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#CF0D0F] focus:border-[#CF0D0F] sm:text-sm"
                      required />
                    <p v-if="errors.postcode" class="mt-1 text-sm text-red-600">{{ errors.postcode[0] }}</p>
                  </div>

                  <div class="flex items-center">
                    <input id="is_default" v-model="form.is_default" type="checkbox"
                      class="h-4 w-4 text-[#CF0D0F] focus:ring-[#CF0D0F] border-gray-300 rounded" />
                    <label for="is_default" class="ml-2 block text-sm text-gray-700">
                      Set as default address
                    </label>
                  </div>

                  <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="closeModals"
                      class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                      Cancel
                    </button>
                    <button type="submit"
                      class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#F6211F] font-medium transition-colors">
                      {{ showEditModal ? 'Update Address' : 'Save Address' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </Teleport>
    </div>
  </div>
</template>
