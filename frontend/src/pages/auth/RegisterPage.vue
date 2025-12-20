<script setup>
/**
 * Register Page
 *
 * Provides user registration form with validation.
 *
 * @requirement AUTH-017 Create RegisterPage.vue with form validation
 */
import { ref, computed, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

// Composables
const { register, checkEmailAvailability, isLoading } = useAuth()

// Form state
const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: ''
})

// Validation state
const errors = ref({})
const serverError = ref('')
const emailAvailable = ref(null)
const checkingEmail = ref(false)

// Password visibility
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Password strength
const passwordStrength = ref({
  score: 0,
  label: '',
  color: ''
})

// Computed
const isFormValid = computed(() => {
  return (
    form.value.name &&
    form.value.email &&
    form.value.password &&
    form.value.password_confirmation &&
    passwordStrength.value.score >= 3 &&
    !hasErrors.value &&
    emailAvailable.value === true
  )
})

const hasErrors = computed(() => {
  return Object.keys(errors.value).length > 0
})

// Watchers
watch(() => form.value.password, (newPassword) => {
  calculatePasswordStrength(newPassword)
})

// Debounced email check
let emailCheckTimeout = null
watch(() => form.value.email, (newEmail) => {
  emailAvailable.value = null
  clearTimeout(emailCheckTimeout)

  if (newEmail && validateEmail(newEmail)) {
    checkingEmail.value = true
    emailCheckTimeout = setTimeout(async () => {
      emailAvailable.value = await checkEmailAvailability(newEmail)
      checkingEmail.value = false
    }, 500)
  }
})

// Methods
function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function validatePhone(phone) {
  if (!phone) return true
  const phoneRegex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/
  return phoneRegex.test(phone)
}

function calculatePasswordStrength(password) {
  let score = 0

  if (!password) {
    passwordStrength.value = { score: 0, label: '', color: '' }
    return
  }

  // Length check
  if (password.length >= 8) score++
  if (password.length >= 12) score++

  // Character variety
  if (/[a-z]/.test(password)) score++
  if (/[A-Z]/.test(password)) score++
  if (/[0-9]/.test(password)) score++
  if (/[^a-zA-Z0-9]/.test(password)) score++

  // Cap at 5
  score = Math.min(score, 5)

  const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong']
  const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-lime-500', 'bg-green-500']

  passwordStrength.value = {
    score,
    label: labels[score - 1] || '',
    color: colors[score - 1] || ''
  }
}

function validateField(field) {
  const newErrors = { ...errors.value }

  switch (field) {
    case 'name':
      if (!form.value.name) {
        newErrors.name = 'Name is required'
      } else if (form.value.name.length < 2) {
        newErrors.name = 'Name must be at least 2 characters'
      } else {
        delete newErrors.name
      }
      break
    case 'email':
      if (!form.value.email) {
        newErrors.email = 'Email is required'
      } else if (!validateEmail(form.value.email)) {
        newErrors.email = 'Please enter a valid email address'
      } else if (emailAvailable.value === false) {
        newErrors.email = 'This email is already registered'
      } else {
        delete newErrors.email
      }
      break
    case 'phone':
      if (form.value.phone && !validatePhone(form.value.phone)) {
        newErrors.phone = 'Please enter a valid phone number'
      } else {
        delete newErrors.phone
      }
      break
    case 'password':
      if (!form.value.password) {
        newErrors.password = 'Password is required'
      } else if (form.value.password.length < 8) {
        newErrors.password = 'Password must be at least 8 characters'
      } else if (passwordStrength.value.score < 3) {
        newErrors.password = 'Password is too weak'
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
  validateField('name')
  validateField('email')
  validateField('phone')
  validateField('password')
  validateField('password_confirmation')
  return !hasErrors.value
}

async function handleSubmit() {
  serverError.value = ''

  if (!validateForm()) return

  const result = await register({
    name: form.value.name,
    email: form.value.email,
    phone: form.value.phone || null,
    password: form.value.password,
    password_confirmation: form.value.password_confirmation
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
          <div class="w-16 h-16 bg-primary-600 rounded-full flex items-center justify-center font-bold text-2xl text-white">
            ZM
          </div>
        </RouterLink>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">
          Create your account
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Already have an account?
          <RouterLink
            to="/login"
            class="font-medium text-primary-600 hover:text-primary-700 transition-colors"
          >
            Sign in
          </RouterLink>
        </p>
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

      <!-- Register Form -->
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="space-y-4">
          <!-- Name Field -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
              Full Name
            </label>
            <div class="mt-1">
              <input
                id="name"
                v-model="form.name"
                type="text"
                autocomplete="name"
                required
                class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm transition-colors"
                :class="errors.name ? 'border-red-500' : 'border-gray-300'"
                placeholder="John Smith"
                @blur="validateField('name')"
                @input="clearError('name')"
              />
            </div>
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">
              {{ errors.name }}
            </p>
          </div>

          <!-- Email Field -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email address
            </label>
            <div class="mt-1 relative">
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm transition-colors pr-10"
                :class="errors.email ? 'border-red-500' : emailAvailable === true ? 'border-green-500' : 'border-gray-300'"
                placeholder="you@example.com"
                @blur="validateField('email')"
                @input="clearError('email')"
              />
              <!-- Email status indicator -->
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg v-if="checkingEmail" class="animate-spin h-5 w-5 text-gray-400" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <svg v-else-if="emailAvailable === true" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg v-else-if="emailAvailable === false" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
              {{ errors.email }}
            </p>
            <p v-else-if="emailAvailable === false" class="mt-1 text-sm text-red-600">
              This email is already registered.
              <RouterLink to="/login" class="underline">Sign in instead?</RouterLink>
            </p>
          </div>

          <!-- Phone Field (Optional) -->
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">
              Phone Number
              <span class="text-gray-500 font-normal">(Optional)</span>
            </label>
            <div class="mt-1">
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                autocomplete="tel"
                class="appearance-none block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-primary-600 focus:border-primary-600 sm:text-sm transition-colors"
                :class="errors.phone ? 'border-red-500' : 'border-gray-300'"
                placeholder="+61 400 000 000"
                @blur="validateField('phone')"
                @input="clearError('phone')"
              />
            </div>
            <p v-if="errors.phone" class="mt-1 text-sm text-red-600">
              {{ errors.phone }}
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
            <!-- Password strength indicator -->
            <div v-if="form.password" class="mt-2">
              <div class="flex gap-1">
                <div
                  v-for="i in 5"
                  :key="i"
                  class="h-1 flex-1 rounded-full transition-colors"
                  :class="i <= passwordStrength.score ? passwordStrength.color : 'bg-gray-200'"
                ></div>
              </div>
              <p class="mt-1 text-xs" :class="passwordStrength.score >= 3 ? 'text-green-600' : 'text-gray-500'">
                Password strength: {{ passwordStrength.label }}
              </p>
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
              Confirm Password
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
        </div>

        <!-- Terms -->
        <p class="text-xs text-gray-500">
          By creating an account, you agree to our
          <RouterLink to="/terms" class="underline hover:text-gray-700">Terms of Service</RouterLink>
          and
          <RouterLink to="/privacy" class="underline hover:text-gray-700">Privacy Policy</RouterLink>.
        </p>

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
            <span v-if="isLoading">Creating account...</span>
            <span v-else>Create Account</span>
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
