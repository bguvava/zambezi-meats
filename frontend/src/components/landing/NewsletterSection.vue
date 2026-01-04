<script setup>
/**
 * Newsletter Section
 *
 * Email signup for promotional offers.
 *
 * @requirement LAND-012 Newsletter signup with email capture
 * @requirement ISSUE-004 Store newsletter subscriptions in database
 */
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { toast } from 'vue-sonner'

const sectionRef = ref(null)
const isVisible = ref(false)
const email = ref('')
const isSubmitting = ref(false)
const isSuccess = ref(false)
const error = ref('')

onMounted(() => {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          isVisible.value = true
          observer.unobserve(entry.target)
        }
      })
    },
    { threshold: 0.1 }
  )

  if (sectionRef.value) {
    observer.observe(sectionRef.value)
  }
})

async function handleSubmit() {
  error.value = ''

  // Validate email
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!email.value || !emailRegex.test(email.value)) {
    error.value = 'Please enter a valid email address'
    return
  }

  isSubmitting.value = true

  try {
    const response = await api.post('/newsletter/subscribe', {
      email: email.value
    })

    if (response.data.success) {
      isSuccess.value = true
      toast.success(response.data.message || 'Successfully subscribed to newsletter!')
      email.value = ''
      
      // Reset success message after 5 seconds
      setTimeout(() => {
        isSuccess.value = false
      }, 5000)
    }
  } catch (err) {
    if (err.response?.status === 409) {
      // Email already subscribed
      error.value = err.response.data.message || 'This email is already subscribed'
      toast.error('This email is already subscribed to our newsletter')
    } else if (err.response?.status === 422) {
      // Validation error
      const errors = err.response.data.errors
      error.value = errors?.email?.[0] || 'Please enter a valid email address'
      toast.error(error.value)
    } else {
      error.value = 'Failed to subscribe. Please try again later.'
      toast.error('Failed to subscribe. Please try again later.')
      console.error('Newsletter subscription error:', err)
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <section ref="sectionRef" class="py-20 bg-gradient-to-br from-primary-600 to-primary-800 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute top-10 left-10 w-40 h-40 border border-white rounded-full"></div>
      <div class="absolute bottom-10 right-10 w-60 h-60 border border-white rounded-full"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 border border-white rounded-full"></div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div
        class="text-center transition-all duration-700"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
      >
        <!-- Icon -->
        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>

        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
          Get Exclusive Offers
        </h2>
        <p class="text-white/80 max-w-xl mx-auto mb-8">
          Subscribe to our newsletter and receive exclusive discounts, seasonal specials, and delicious recipes straight to your inbox.
        </p>

        <!-- Success Message -->
        <div
          v-if="isSuccess"
          class="bg-white/20 backdrop-blur-sm rounded-xl p-6 max-w-md mx-auto transition-all duration-500"
        >
          <svg class="w-12 h-12 text-white mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="text-xl font-semibold text-white mb-2">You're Subscribed!</h3>
          <p class="text-white/80 text-sm">
            Thanks for joining! Check your inbox for a special welcome offer.
          </p>
        </div>

        <!-- Newsletter Form -->
        <form
          v-else
          class="max-w-md mx-auto"
          @submit.prevent="handleSubmit"
        >
          <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
              <input
                v-model="email"
                type="email"
                placeholder="Enter your email address"
                class="w-full px-5 py-4 rounded-xl bg-white/10 backdrop-blur-sm border border-white/20 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all"
                :class="{ 'border-red-400': error }"
              />
            </div>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-8 py-4 bg-secondary-700 text-white font-semibold rounded-xl hover:bg-secondary-800 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <svg
                v-if="isSubmitting"
                class="animate-spin h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ isSubmitting ? 'Subscribing...' : 'Subscribe' }}</span>
            </button>
          </div>
          <p v-if="error" class="text-red-200 text-sm mt-2 text-left">{{ error }}</p>
          <p class="text-white/60 text-xs mt-4">
            We respect your privacy. Unsubscribe at any time.
          </p>
        </form>
      </div>
    </div>
  </section>
</template>
