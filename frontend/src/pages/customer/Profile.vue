<template>
  <div class="bg-gray-50 py-6">
    <div class="container mx-auto px-4 max-w-6xl">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
        <p class="mt-1 text-sm text-gray-600">Manage your personal information and account settings</p>
      </div>

      <!-- Two-Column Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column: Profile Info & Avatar -->
        <div class="space-y-6">
          <!-- Avatar Section -->
          <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Photo</h2>
            
            <div class="flex items-start space-x-4">
              <!-- Avatar Display -->
              <div class="relative flex-shrink-0">
                <div v-if="previewAvatar || profile.avatar" class="w-20 h-20 rounded-full overflow-hidden bg-gray-200 ring-2 ring-gray-100">
                  <img :src="previewAvatar || profile.avatar" alt="Avatar" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-20 h-20 rounded-full overflow-hidden bg-gray-200 ring-2 ring-gray-100">
                  <img src="/images/user.jpg" alt="Default Avatar" class="w-full h-full object-cover" />
                </div>
              </div>

              <!-- Avatar Actions -->
              <div class="flex-1">
                <div class="flex flex-wrap gap-2">
                  <label class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-medium text-xs text-gray-700 hover:bg-gray-50 transition-colors">
                    <input type="file" accept="image/*" @change="handleAvatarChange" class="hidden" />
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Upload
                  </label>
                  <button
                    v-if="profile.avatar"
                    @click="deleteAvatar"
                    :disabled="isDeleting"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md font-medium text-xs text-gray-700 bg-white hover:bg-gray-50 transition-colors disabled:opacity-50"
                  >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{ isDeleting ? 'Removing...' : 'Remove' }}
                  </button>
                </div>
                <p class="mt-2 text-xs text-gray-500">JPG, PNG or GIF. Max 2MB.</p>
              </div>
            </div>
          </div>

          <!-- Personal Information -->
          <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h2>
            
            <form @submit.prevent="updateProfile" class="space-y-4">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#CF0D0F] focus:ring-[#CF0D0F] sm:text-sm"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
              </div>

              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#CF0D0F] focus:ring-[#CF0D0F] sm:text-sm"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
              </div>

              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input
                  id="phone"
                  v-model="form.phone"
                  type="tel"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#CF0D0F] focus:ring-[#CF0D0F] sm:text-sm"
                />
                <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone[0] }}</p>
              </div>

              <div class="flex justify-end pt-2">
                <button
                  type="submit"
                  :disabled="isSaving"
                  class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] border border-transparent rounded-md font-medium text-sm text-white hover:bg-[#F6211F] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#CF0D0F] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <svg v-if="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isSaving ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Right Column: Change Password -->
        <div class="space-y-6">
          <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h2>
            
            <form @submit.prevent="changePassword" class="space-y-4">
              <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                <div class="mt-1 relative">
                  <input
                    id="current_password"
                    v-model="passwordForm.current_password"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    required
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#CF0D0F] focus:ring-[#CF0D0F] sm:text-sm"
                  />
                  <button
                    type="button"
                    @click="showCurrentPassword = !showCurrentPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                  >
                    <svg v-if="!showCurrentPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
                </div>
                <p v-if="passwordErrors.current_password" class="mt-1 text-sm text-red-600">{{ passwordErrors.current_password[0] }}</p>
              </div>

              <div>
                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                <div class="mt-1 relative">
                  <input
                    id="password"
                    v-model="passwordForm.password"
                    :type="showNewPassword ? 'text' : 'password'"
                    required
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#CF0D0F] focus:ring-[#CF0D0F] sm:text-sm"
                  />
                  <button
                    type="button"
                    @click="showNewPassword = !showNewPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                  >
                    <svg v-if="!showNewPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
                </div>
                <p v-if="passwordErrors.password" class="mt-1 text-sm text-red-600">{{ passwordErrors.password[0] }}</p>
                <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
              </div>

              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <div class="mt-1 relative">
                  <input
                    id="password_confirmation"
                    v-model="passwordForm.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    required
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#CF0D0F] focus:ring-[#CF0D0F] sm:text-sm"
                  />
                  <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                  >
                    <svg v-if="!showConfirmPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="flex justify-end pt-2">
                <button
                  type="submit"
                  :disabled="isChangingPassword"
                  class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] border border-transparent rounded-md font-medium text-sm text-white hover:bg-[#F6211F] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#CF0D0F] disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

const authStore = useAuthStore()
const toast = useToast()

// Profile data
const profile = ref({
  name: '',
  email: '',
  phone: '',
  avatar: null,
})

// Form states
const form = ref({
  name: '',
  email: '',
  phone: '',
})

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

// Loading states
const isSaving = ref(false)
const isChangingPassword = ref(false)
const isDeleting = ref(false)

// Error states
const errors = ref({})
const passwordErrors = ref({})

// Avatar upload
const previewAvatar = ref(null)
const selectedFile = ref(null)

// Password visibility
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

// Computed
const userInitials = computed(() => {
  if (!profile.value.name) return '?'
  const names = profile.value.name.split(' ')
  if (names.length >= 2) {
    return `${names[0][0]}${names[1][0]}`.toUpperCase()
  }
  return profile.value.name.substring(0, 2).toUpperCase()
})

// Methods
const fetchProfile = async () => {
  try {
    const response = await axios.get('/api/v1/profile')
    if (response.data?.success) {
      profile.value = response.data.data
      form.value = {
        name: profile.value.name,
        email: profile.value.email,
        phone: profile.value.phone || '',
      }
    }
  } catch (error) {
    console.error('Error fetching profile:', error)
    toast.error('Failed to load profile')
  }
}

const updateProfile = async () => {
  isSaving.value = true
  errors.value = {}

  try {
    const response = await axios.put('/api/v1/profile', form.value)
    
    if (response.data?.success) {
      profile.value.name = form.value.name
      profile.value.email = form.value.email
      profile.value.phone = form.value.phone
      
      // Update auth store
      authStore.user.name = form.value.name
      authStore.user.email = form.value.email
      
      toast.success('Profile updated successfully')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
    toast.error(error.response?.data?.message || 'Failed to update profile')
  } finally {
    isSaving.value = false
  }
}

const handleAvatarChange = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file size (2MB)
  if (file.size > 2 * 1024 * 1024) {
    toast.error('Image must be less than 2MB')
    return
  }

  // Validate file type
  if (!file.type.startsWith('image/')) {
    toast.error('Please select an image file')
    return
  }

  selectedFile.value = file

  // Show preview
  const reader = new FileReader()
  reader.onload = (e) => {
    previewAvatar.value = e.target.result
  }
  reader.readAsDataURL(file)

  // Upload avatar
  await uploadAvatar(file)
}

const uploadAvatar = async (file) => {
  const formData = new FormData()
  formData.append('avatar', file)

  try {
    const response = await axios.post('/api/v1/profile/avatar', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    if (response.data?.success) {
      profile.value.avatar = response.data.data.avatar
      authStore.user.avatar = response.data.data.avatar
      toast.success('Avatar uploaded successfully')
    }
  } catch (error) {
    console.error('Error uploading avatar:', error)
    toast.error('Failed to upload avatar')
    previewAvatar.value = null
  }
}

const deleteAvatar = async () => {
  if (!confirm('Are you sure you want to remove your profile photo?')) {
    return
  }

  isDeleting.value = true

  try {
    const response = await axios.delete('/api/v1/profile/avatar')
    
    if (response.data?.success) {
      profile.value.avatar = null
      authStore.user.avatar = null
      previewAvatar.value = null
      toast.success('Avatar removed successfully')
    }
  } catch (error) {
    console.error('Error deleting avatar:', error)
    toast.error('Failed to remove avatar')
  } finally {
    isDeleting.value = false
  }
}

const changePassword = async () => {
  isChangingPassword.value = true
  passwordErrors.value = {}

  try {
    const response = await axios.post('/api/v1/profile/change-password', passwordForm.value)
    
    if (response.data?.success) {
      passwordForm.value = {
        current_password: '',
        password: '',
        password_confirmation: '',
      }
      toast.success('Password changed successfully')
    }
  } catch (error) {
    if (error.response?.data?.errors) {
      passwordErrors.value = error.response.data.errors
    }
    toast.error(error.response?.data?.message || 'Failed to change password')
  } finally {
    isChangingPassword.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchProfile()
})
</script>

