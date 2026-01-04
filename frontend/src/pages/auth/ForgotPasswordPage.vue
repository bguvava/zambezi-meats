<script setup>
/**
 * Forgot Password Page
 *
 * Allows users to request a password reset link.
 *
 * @requirement AUTH-018 Create ForgotPasswordPage.vue
 */
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

// Composables
const { forgotPassword, isLoading } = useAuth()

// Form state
const email = ref('')
const error = ref('')
const success = ref(false)
const successMessage = ref('')

// Validation state
const emailError = ref('')

// Computed
const isFormValid = computed(() => {
  return email.value && !emailError.value
})

// Methods
function validateEmail(emailValue) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(emailValue)
}

function validateField() {
  if (!email.value) {
    emailError.value = 'Email is required'
  } else if (!validateEmail(email.value)) {
    emailError.value = 'Please enter a valid email address'
  } else {
    emailError.value = ''
  }
}

async function handleSubmit() {
  error.value = ''
  success.value = false

  validateField()
  if (emailError.value) return

  const result = await forgotPassword(email.value)

  if (result.success) {
    success.value = true
    successMessage.value = result.message || 'Password reset link sent to your email.'
  } else {
    error.value = result.message
    if (result.errors?.email) {
      emailError.value = result.errors.email[0] || result.errors.email
    }
  }
}

function clearError() {
  emailError.value = ''
  error.value = ''
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
          Reset your password
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Enter your email and we'll send you a link to reset your password.
        </p>
      </div>

      <!-- Success Message -->
      <div
        v-if="success"
        class="rounded-md bg-green-50 p-6"
        role="alert"
      >
        <div class="flex flex-col items-center text-center">
          <div class="flex-shrink-0 mb-4">
            <svg class="h-12 w-12 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-green-800 mb-2">
            Check your email
          </h3>
          <p class="text-sm text-green-700 mb-4">
            {{ successMessage }}
          </p>
          <p class="text-xs text-green-600">
            Didn't receive the email? Check your spam folder or
            <button
              type="button"
              class="underline hover:text-green-800"
              @click="success = false"
            >
              try again
            </button>
          </p>
        </div>
      </div>

      <!-- Form -->
      <div v-else>
        <!-- Error Message -->
        <div
          v-if="error"
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
              <p class="text-sm text-red-800">{{ error }}</p>
            </div>
          </div>
        </div>

        <form class="space-y-6" @submit.prevent="handleSubmit">
          <!-- Email Field -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <div class="mt-1">
              <input
                id="email"
                v-model="email"
                type="email"
                autocomplete="email"
                required
                class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm transition-colors"
                :class="emailError ? 'border-red-500' : 'border-gray-300'"
                placeholder="you@example.com"
                @blur="validateField"
                @input="clearError"
              />
            </div>
            <p v-if="emailError" class="mt-1 text-sm text-red-600">
              {{ emailError }}
            </p>
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
              <span v-if="isLoading">Sending...</span>
              <span v-else>Send Reset Link</span>
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
