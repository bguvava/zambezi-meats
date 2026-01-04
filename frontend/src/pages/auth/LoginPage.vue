<script setup>
/**
 * Login Page
 *
 * Provides user authentication form with email/password.
 *
 * @requirement AUTH-016 Create LoginPage.vue with form validation
 */
import { ref, computed, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

// Composables
const { login, isLoading } = useAuth()
const route = useRoute()

// Form state
const form = ref({
  email: '',
  password: '',
  remember: false
})

// Password visibility toggle
const showPassword = ref(false)

// Validation state
const errors = ref({})
const serverError = ref('')
const successMessage = ref('')

// Computed
const isFormValid = computed(() => {
  return form.value.email && form.value.password && !hasErrors.value
})

const hasErrors = computed(() => {
  return Object.keys(errors.value).length > 0
})

// Lifecycle
onMounted(() => {
  // Check for redirect messages
  const message = route.query.message
  if (message === 'logged_out') {
    successMessage.value = 'You have been logged out successfully.'
  } else if (message === 'password_reset') {
    successMessage.value = 'Your password has been reset. Please log in with your new password.'
  } else if (message === 'session_expired') {
    serverError.value = 'Your session has expired. Please log in again.'
  }
})

// Methods
function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function validateField(field) {
  const newErrors = { ...errors.value }

  switch (field) {
    case 'email':
      if (!form.value.email) {
        newErrors.email = 'Email is required'
      } else if (!validateEmail(form.value.email)) {
        newErrors.email = 'Please enter a valid email address'
      } else {
        delete newErrors.email
      }
      break
    case 'password':
      if (!form.value.password) {
        newErrors.password = 'Password is required'
      } else {
        delete newErrors.password
      }
      break
  }

  errors.value = newErrors
}

function validateForm() {
  validateField('email')
  validateField('password')
  return !hasErrors.value
}

async function handleSubmit() {
  successMessage.value = ''
  serverError.value = ''

  if (!validateForm()) return

  const result = await login({
    email: form.value.email,
    password: form.value.password,
    remember: form.value.remember
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
          Sign in to your account
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Or
          <RouterLink
            to="/register"
            class="font-medium text-primary-600 hover:text-primary-700 transition-colors"
          >
            create a new account
          </RouterLink>
        </p>
      </div>

      <!-- Success Message -->
      <div
        v-if="successMessage"
        class="rounded-md bg-green-50 p-4"
        role="alert"
      >
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-green-800">{{ successMessage }}</p>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div
        v-if="serverError"
        class="rounded-md bg-red-50 p-4"
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

      <!-- Login Form -->
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="space-y-4">
          <!-- Email Field -->
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
                class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm transition-colors"
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

          <!-- Password Field -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              Password
            </label>
            <div class="mt-1 relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                class="appearance-none block w-full px-3 py-2 pr-10 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm transition-colors"
                :class="errors.password ? 'border-red-500' : 'border-gray-300'"
                placeholder="••••••••"
                @blur="validateField('password')"
                @input="clearError('password')"
              />
              <!-- Password Toggle Button -->
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
              >
                <!-- Eye Icon (show password) -->
                <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <!-- Eye Off Icon (hide password) -->
                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">
              {{ errors.password }}
            </p>
          </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 text-primary-600 focus:ring-primary-600 border-gray-300 rounded"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-900">
              Remember me
            </label>
          </div>

          <div class="text-sm">
            <RouterLink
              to="/forgot-password"
              class="font-medium text-primary-600 hover:text-primary-700 transition-colors"
            >
              Forgot your password?
            </RouterLink>
          </div>
        </div>

        <!-- Submit Button -->
        <div>
          <button
            type="submit"
            :disabled="isLoading || !isFormValid"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-md"
          >
            <span v-if="isLoading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            <span v-if="isLoading">Signing in...</span>
            <span v-else>Sign in</span>
          </button>
        </div>
      </form>

      <!-- Back to Home -->
      <div class="text-center">
        <RouterLink
          to="/"
          class="text-sm text-gray-600 hover:text-gray-900 transition-colors"
        >
          ← Back to Home
        </RouterLink>
      </div>
    </div>
  </div>
</template>
