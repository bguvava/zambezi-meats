<script setup>
/**
 * Reset Password Page
 *
 * Allows users to set a new password using the reset token.
 *
 * @requirement AUTH-019 Create ResetPasswordPage.vue
 */
import { ref, computed, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

// Composables
const { resetPassword, isLoading } = useAuth()
const route = useRoute()

// Form state
const form = ref({
  email: '',
  password: '',
  password_confirmation: '',
  token: ''
})

// Validation state
const errors = ref({})
const serverError = ref('')

// Password visibility
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Token validity
const tokenValid = ref(true)

// Lifecycle
onMounted(() => {
  // Get token and email from URL
  form.value.token = route.query.token || ''
  form.value.email = route.query.email || ''

  if (!form.value.token) {
    tokenValid.value = false
  }
})

// Computed
const isFormValid = computed(() => {
  return (
    form.value.email &&
    form.value.password &&
    form.value.password_confirmation &&
    form.value.token &&
    !hasErrors.value
  )
})

const hasErrors = computed(() => {
  return Object.keys(errors.value).length > 0
})

// Methods
function validateField(field) {
  const newErrors = { ...errors.value }

  switch (field) {
    case 'email':
      if (!form.value.email) {
        newErrors.email = 'Email is required'
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
        newErrors.email = 'Please enter a valid email address'
      } else {
        delete newErrors.email
      }
      break
    case 'password':
      if (!form.value.password) {
        newErrors.password = 'Password is required'
      } else if (form.value.password.length < 8) {
        newErrors.password = 'Password must be at least 8 characters'
      } else if (!/[a-z]/.test(form.value.password)) {
        newErrors.password = 'Password must contain a lowercase letter'
      } else if (!/[A-Z]/.test(form.value.password)) {
        newErrors.password = 'Password must contain an uppercase letter'
      } else if (!/[0-9]/.test(form.value.password)) {
        newErrors.password = 'Password must contain a number'
      } else if (!/[^a-zA-Z0-9]/.test(form.value.password)) {
        newErrors.password = 'Password must contain a special character'
      } else {
        delete newErrors.password
      }
      break
    case 'password_confirmation':
      if (!form.value.password_confirmation) {
        newErrors.password_confirmation = 'Please confirm your password'
      } else if (form.value.password !== form.value.password_confirmation) {
        newErrors.password_confirmation = 'Passwords do not match'
      } else {
        delete newErrors.password_confirmation
      }
      break
  }

  errors.value = newErrors
}

function validateForm() {
  validateField('email')
  validateField('password')
  validateField('password_confirmation')
  return !hasErrors.value
}

async function handleSubmit() {
  serverError.value = ''

  if (!validateForm()) return

  const result = await resetPassword({
    email: form.value.email,
    password: form.value.password,
    password_confirmation: form.value.password_confirmation,
    token: form.value.token
  })

  if (!result.success) {
    serverError.value = result.message
    if (result.errors) {
      errors.value = result.errors
    }
  }
}

function clearError(field) {
  const newErrors = { ...errors.value }
  delete newErrors[field]
  errors.value = newErrors
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Logo & Title -->
      <div class="text-center">
        <RouterLink to="/" class="inline-flex items-center justify-center gap-2">
          <img src="/images/logo.png" alt="Zambezi Meats" class="h-24 w-auto object-contain" />
        </RouterLink>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">
          Set new password
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Enter your new password below.
        </p>
      </div>

      <!-- Invalid Token Message -->
      <div
        v-if="!tokenValid"
        class="rounded-md bg-red-50 p-6 text-center"
        role="alert"
      >
        <svg class="mx-auto h-12 w-12 text-red-400 mb-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <h3 class="text-lg font-medium text-red-800 mb-2">
          Invalid or Expired Link
        </h3>
        <p class="text-sm text-red-700 mb-4">
          This password reset link is invalid or has expired.
        </p>
        <RouterLink
          to="/forgot-password"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
        >
          Request New Link
        </RouterLink>
      </div>

      <!-- Reset Form -->
      <div v-else>
        <!-- Error Message -->
        <div
          v-if="serverError"
          class="rounded-md bg-red-50 p-4 mb-6"
          role="alert"
        >
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-800">{{ serverError }}</p>
            </div>
          </div>
        </div>

        <form class="space-y-6" @submit.prevent="handleSubmit">
          <!-- Email Field (pre-filled, usually hidden or read-only) -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <div class="mt-1">
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm transition-colors bg-gray-50"
                :class="errors.email ? 'border-red-500' : 'border-gray-300'"
                placeholder="you@example.com"
                @blur="validateField('email')"
                @input="clearError('email')"
              />
            </div>
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
              {{ errors.email }}
            </p>
          </div>

          <!-- New Password Field -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              New Password
            </label>
            <div class="mt-1 relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm transition-colors pr-10"
                :class="errors.password ? 'border-red-500' : 'border-gray-300'"
                placeholder="••••••••"
                @blur="validateField('password')"
                @input="clearError('password')"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                @click="showPassword = !showPassword"
              >
                <svg v-if="showPassword" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                  <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                  <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
              {{ errors.password }}
            </p>
            <p v-else class="mt-1 text-xs text-gray-500">
              Must contain uppercase, lowercase, number, and symbol
            </p>
          </div>

          <!-- Confirm Password Field -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              Confirm New Password
            </label>
            <div class="mt-1 relative">
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm transition-colors pr-10"
                :class="errors.password_confirmation ? 'border-red-500' : 'border-gray-300'"
                placeholder="••••••••"
                @blur="validateField('password_confirmation')"
                @input="clearError('password_confirmation')"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <svg v-if="showConfirmPassword" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                  <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                </svg>
                <svg v-else class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                  <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">
              {{ errors.password_confirmation }}
            </p>
          </div>

          <!-- Submit Button -->
          <div>
            <button
              type="submit"
              :disabled="isLoading || !isFormValid"
              class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <span v-if="isLoading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              <span v-if="isLoading">Resetting...</span>
              <span v-else>Reset Password</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Back to Login -->
      <div class="text-center">
        <RouterLink
          to="/login"
          class="text-sm text-gray-600 hover:text-gray-900 transition-colors"
        >
          ← Back to Sign In
        </RouterLink>
      </div>
    </div>
  </div>
</template>
