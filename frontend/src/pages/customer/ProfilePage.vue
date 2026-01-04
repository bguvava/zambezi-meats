<script setup>
/**
 * ProfilePage.vue
 * Customer profile management page - Redesigned
 * Side-by-side Personal Info and Change Password sections
 */
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { toast } from 'vue-sonner'

const authStore = useAuthStore()

// Form states
const profileForm = ref({
  name: '',
  email: '',
  phone: '',
  currency_preference: 'AUD'
})

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})

// UI states
const isLoadingProfile = ref(false)
const isUpdatingProfile = ref(false)
const isChangingPassword = ref(false)
const isUploadingAvatar = ref(false)

// Avatar
const avatarUrl = ref(null)
const avatarInput = ref(null)

// Password visibility
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

// Validation errors
const profileErrors = ref({})
const passwordErrors = ref({})

// Fetch profile data
const fetchProfile = async () => {
  isLoadingProfile.value = true
  try {
    const response = await api.get('/profile')
    if (response.data.success) {
      const user = response.data.data
      profileForm.value = {
        name: user.name,
        email: user.email,
        phone: user.phone || '',
        currency_preference: user.currency_preference || 'AUD'
      }
      avatarUrl.value = user.avatar
    }
  } catch (error) {
    toast.error('Failed to load profile data')
    console.error('Profile fetch error:', error)
  } finally {
    isLoadingProfile.value = false
  }
}

// Update profile
const handleUpdateProfile = async () => {
  profileErrors.value = {}
  isUpdatingProfile.value = true
  
  try {
    const response = await api.put('/profile', profileForm.value)
    if (response.data.success) {
      toast.success('Profile updated successfully')
      // Update auth store
      await authStore.fetchUser()
    }
  } catch (error) {
    if (error.response?.status === 422) {
      profileErrors.value = error.response.data.errors || {}
      toast.error('Please check the form for errors')
    } else {
      toast.error(error.response?.data?.message || 'Failed to update profile')
    }
  } finally {
    isUpdatingProfile.value = false
  }
}

// Change password
const handleChangePassword = async () => {
  passwordErrors.value = {}
  isChangingPassword.value = true
  
  try {
    const response = await api.post('/profile/change-password', passwordForm.value)
    if (response.data.success) {
      toast.success('Password changed successfully')
      // Reset form
      passwordForm.value = {
        current_password: '',
        password: '',
        password_confirmation: ''
      }
    }
  } catch (error) {
    if (error.response?.status === 422) {
      passwordErrors.value = error.response.data.errors || {}
      toast.error(error.response.data.message || 'Please check the form for errors')
    } else {
      toast.error(error.response?.data?.message || 'Failed to change password')
    }
  } finally {
    isChangingPassword.value = false
  }
}

// Avatar upload
const triggerAvatarUpload = () => {
  avatarInput.value?.click()
}

const handleAvatarChange = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  
  // Validate file
  if (!['image/jpeg', 'image/jpg', 'image/png', 'image/gif'].includes(file.type)) {
    toast.error('Please select a JPG, PNG, or GIF image')
    return
  }
  
  if (file.size > 2 * 1024 * 1024) {
    toast.error('Image must be less than 2MB')
    return
  }
  
  isUploadingAvatar.value = true
  
  try {
    const formData = new FormData()
    formData.append('avatar', file)
    
    const response = await api.post('/profile/avatar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    if (response.data.success) {
      avatarUrl.value = response.data.data.avatar
      toast.success('Profile photo updated successfully')
      await authStore.fetchUser()
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to upload photo')
  } finally {
    isUploadingAvatar.value = false
    // Reset input
    if (avatarInput.value) avatarInput.value.value = ''
  }
}

// Delete avatar
const handleDeleteAvatar = async () => {
  if (!confirm('Are you sure you want to remove your profile photo?')) return
  
  try {
    const response = await api.delete('/profile/avatar')
    if (response.data.success) {
      avatarUrl.value = null
      toast.success('Profile photo removed successfully')
      await authStore.fetchUser()
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to remove photo')
  }
}

// User initials for avatar
const userInitials = computed(() => {
  const names = profileForm.value.name.split(' ')
  return names.length > 1
    ? names[0][0] + names[names.length - 1][0]
    : names[0][0]
})

onMounted(() => {
  fetchProfile()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-6">
        <nav class="text-sm mb-3">
          <RouterLink to="/customer" class="text-gray-500 hover:text-primary-700">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">My Profile</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
        <p class="text-sm text-gray-600 mt-1">Manage your personal information and account settings</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoadingProfile" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        <p class="text-gray-600 mt-3">Loading profile...</p>
      </div>

      <!-- Profile Content -->
      <div v-else>
        <!-- Profile Photo Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Photo</h2>
          <div class="flex items-center gap-6">
            <!-- Avatar Display -->
            <div class="relative">
              <div v-if="avatarUrl" class="w-20 h-20 rounded-full overflow-hidden">
                <img :src="avatarUrl" :alt="profileForm.name" class="w-full h-full object-cover" />
              </div>
              <div v-else class="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center">
                <span class="text-2xl font-semibold text-primary-700">{{ userInitials }}</span>
              </div>
              
              <!-- Upload indicator -->
              <div v-if="isUploadingAvatar" class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-white"></div>
              </div>
            </div>

            <!-- Upload Actions -->
            <div class="flex-1">
              <h3 class="font-medium text-gray-800 mb-1">{{ profileForm.name }}</h3>
              <p class="text-sm text-gray-500 mb-3">JPG, PNG or GIF. Max 2MB.</p>
              <div class="flex gap-3">
                <button
                  @click="triggerAvatarUpload"
                  :disabled="isUploadingAvatar"
                  class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed"
                >
                  {{ isUploadingAvatar ? 'Uploading...' : 'Upload Photo' }}
                </button>
                <button
                  v-if="avatarUrl"
                  @click="handleDeleteAvatar"
                  :disabled="isUploadingAvatar"
                  class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Remove
                </button>
              </div>
              <input
                ref="avatarInput"
                type="file"
                accept="image/jpeg,image/jpg,image/png,image/gif"
                @change="handleAvatarChange"
                class="hidden"
              />
            </div>
          </div>
        </div>

        <!-- Two-Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Personal Information (Left Column) -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-5">Personal Information</h2>
            
            <form @submit.prevent="handleUpdateProfile" class="space-y-4">
              <!-- Full Name -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                <input
                  v-model="profileForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors"
                  :class="profileErrors.name ? 'border-red-500' : 'border-gray-300'"
                  placeholder="John Doe"
                />
                <p v-if="profileErrors.name" class="mt-1 text-xs text-red-600">
                  {{ profileErrors.name[0] }}
                </p>
              </div>

              <!-- Email Address -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                <input
                  v-model="profileForm.email"
                  type="email"
                  required
                  class="w-full px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors"
                  :class="profileErrors.email ? 'border-red-500' : 'border-gray-300'"
                  placeholder="john@example.com"
                />
                <p v-if="profileErrors.email" class="mt-1 text-xs text-red-600">
                  {{ profileErrors.email[0] }}
                </p>
              </div>

              <!-- Phone Number -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                <input
                  v-model="profileForm.phone"
                  type="tel"
                  class="w-full px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors"
                  :class="profileErrors.phone ? 'border-red-500' : 'border-gray-300'"
                  placeholder="+61 XXX XXX XXX"
                />
                <p v-if="profileErrors.phone" class="mt-1 text-xs text-red-600">
                  {{ profileErrors.phone[0] }}
                </p>
              </div>

              <!-- Currency Preference -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Currency Preference</label>
                <select
                  v-model="profileForm.currency_preference"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors"
                >
                  <option value="AUD">AU$ (Australian Dollar)</option>
                  <option value="USD">US$ (US Dollar)</option>
                </select>
              </div>

              <!-- Save Button -->
              <div class="pt-2">
                <button
                  type="submit"
                  :disabled="isUpdatingProfile"
                  class="w-full px-4 py-2.5 bg-[#CF0D0F] text-white font-medium text-sm rounded-md hover:bg-[#F6211F] transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center justify-center"
                >
                  <svg v-if="isUpdatingProfile" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isUpdatingProfile ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Change Password (Right Column) -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-5">Change Password</h2>
            
            <form @submit.prevent="handleChangePassword" class="space-y-4">
              <!-- Current Password -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Current Password</label>
                <div class="relative">
                  <input
                    v-model="passwordForm.current_password"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    required
                    class="w-full px-3 py-2 pr-10 border rounded-md text-sm focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors"
                    :class="passwordErrors.current_password ? 'border-red-500' : 'border-gray-300'"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    @click="showCurrentPassword = !showCurrentPassword"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                  >
                    <svg v-if="!showCurrentPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
                </div>
                <p v-if="passwordErrors.current_password" class="mt-1 text-xs text-red-600">
                  {{ passwordErrors.current_password[0] }}
                </p>
              </div>

              <!-- New Password -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password</label>
                <div class="relative">
                  <input
                    v-model="passwordForm.password"
                    :type="showNewPassword ? 'text' : 'password'"
                    required
                    class="w-full px-3 py-2 pr-10 border rounded-md text-sm focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors"
                    :class="passwordErrors.password ? 'border-red-500' : 'border-gray-300'"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    @click="showNewPassword = !showNewPassword"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                  >
                    <svg v-if="!showNewPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
                </div>
                <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
                <p v-if="passwordErrors.password" class="mt-1 text-xs text-red-600">
                  {{ passwordErrors.password[0] }}
                </p>
              </div>

              <!-- Confirm Password -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm New Password</label>
                <div class="relative">
                  <input
                    v-model="passwordForm.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    required
                    class="w-full px-3 py-2 pr-10 border rounded-md text-sm focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-colors"
                    :class="passwordErrors.password_confirmation ? 'border-red-500' : 'border-gray-300'"
                    placeholder="••••••••"
                  />
                  <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                  >
                    <svg v-if="!showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
                </div>
                <p v-if="passwordErrors.password_confirmation" class="mt-1 text-xs text-red-600">
                  {{ passwordErrors.password_confirmation[0] }}
                </p>
              </div>

              <!-- Change Password Button -->
              <div class="pt-2">
                <button
                  type="submit"
                  :disabled="isChangingPassword"
                  class="w-full px-4 py-2.5 bg-[#CF0D0F] text-white font-medium text-sm rounded-md hover:bg-[#F6211F] transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center justify-center"
                >
                  <svg v-if="isChangingPassword" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isChangingPassword ? 'Changing...' : 'Change Password' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
