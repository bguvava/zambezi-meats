<script setup>
/**
 * LockScreen.vue
 * Lock screen component shown after 5 minutes of inactivity
 * Allows user to unlock session with password or logout completely
 */
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue-sonner'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['unlock', 'logout'])

const authStore = useAuthStore()
const password = ref('')
const isUnlocking = ref(false)
const showPassword = ref(false)

// Get user info from store
const user = computed(() => authStore.user)
const userInitials = computed(() => {
  if (!user.value) return 'U'
  const names = user.value.name.split(' ')
  return names.length > 1 
    ? names[0][0] + names[names.length - 1][0]
    : names[0][0]
})

// Handle unlock with password
const handleUnlock = async () => {
  if (!password.value) {
    toast.error('Please enter your password')
    return
  }

  isUnlocking.value = true

  try {
    const result = await authStore.unlockSession(password.value)

    if (result.success) {
      toast.success('Session unlocked successfully')
      password.value = ''
      emit('unlock')
    } else {
      toast.error(result.message || 'Incorrect password. Please try again.')
      password.value = ''
    }
  } catch (error) {
    console.error('Unlock error:', error)
    toast.error('Failed to unlock session. Please try again.')
    password.value = ''
  } finally {
    isUnlocking.value = false
  }
}

// Handle logout
const handleLogout = () => {
  emit('logout')
}

// Toggle password visibility
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

// Handle enter key
const handleKeydown = (event) => {
  if (event.key === 'Enter') {
    handleUnlock()
  }
}
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 p-4"
      >
        <div class="w-full max-w-md">
          <!-- Lock Screen Card - Compact Design -->
          <div class="bg-white rounded-lg shadow-2xl overflow-hidden">
            <!-- Header - Reduced Padding -->
            <div class="bg-gradient-to-r from-[#CF0D0F] to-[#F6211F] px-6 py-5 text-center">
              <div class="flex justify-center mb-3">
                <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center shadow-lg">
                  <svg class="w-8 h-8 text-[#CF0D0F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
              </div>
              <h2 class="text-xl font-bold text-white">Session Locked</h2>
            </div>

            <!-- Body - Compact -->
            <div class="p-6">
              <!-- User Info - Compact -->
              <div class="text-center mb-4">
                <div class="inline-flex items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-[#EFEFEF] flex items-center justify-center text-[#4D4B4C] text-lg font-semibold overflow-hidden">
                    <img
                      v-if="userAvatar"
                      :src="userAvatar"
                      :alt="user?.name"
                      class="w-full h-full object-cover"
                      @error="($event) => $event.target.style.display = 'none'"
                    />
                    <span v-else>{{ userInitials }}</span>
                  </div>
                  <div class="text-left">
                    <h3 class="text-base font-semibold text-gray-900">{{ user?.name }}</h3>
                    <p class="text-xs text-gray-500">{{ user?.role || 'Customer' }}</p>
                  </div>
                </div>
              </div>

              <!-- Inactivity Message - Compact -->
              <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mb-4">
                <div class="flex items-start gap-2">
                  <svg class="w-4 h-4 text-yellow-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                  <div>
                    <h4 class="text-xs font-medium text-yellow-800">Session Locked Due to Inactivity</h4>
                    <p class="text-xs text-yellow-700 mt-0.5">
                      Your session was locked after 5 minutes of inactivity for security purposes.
                      Enter your password to continue or logout.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Password Input - Compact -->
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <div class="relative">
                  <input
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="Enter your password"
                    @keydown="handleKeydown"
                    class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F] transition-colors"
                    :disabled="isUnlocking"
                    autofocus
                  />
                  <button
                    type="button"
                    @click="togglePasswordVisibility"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    :disabled="isUnlocking"
                  >
                    <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Action Buttons - Compact -->
              <div class="space-y-2.5">
                <button
                  @click="handleUnlock"
                  :disabled="isUnlocking || !password"
                  class="w-full bg-[#CF0D0F] text-white py-2.5 px-4 rounded-md text-sm font-medium hover:bg-[#F6211F] transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center justify-center"
                >
                  <svg v-if="isUnlocking" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isUnlocking ? 'Unlocking...' : 'Unlock Session' }}
                </button>

                <button
                  @click="handleLogout"
                  :disabled="isUnlocking"
                  class="w-full bg-white border border-gray-300 text-gray-700 py-2.5 px-4 rounded-md text-sm font-medium hover:bg-gray-50 hover:border-gray-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Logout
                </button>
              </div>

              <!-- Help Text - Compact -->
              <p class="text-xs text-gray-500 text-center mt-4">
                If you've forgotten your password, please logout and use the "Forgot Password" link on the login page.
              </p>
            </div>
          </div>

          <!-- Footer - Compact -->
          <div class="text-center mt-4">
            <p class="text-xs text-gray-400">
              Zambezi Meats - Session locked for your security
            </p>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
