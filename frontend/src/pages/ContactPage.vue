<script setup>
/**
 * ContactPage.vue
 * Contact page with contact form and company information
 */
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { toast } from 'vue-sonner'
import axios from 'axios'

// Form data
const form = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
  website: '' // Honeypot field for spam detection
})

// Form state
const isSubmitting = ref(false)
const submitted = ref(false)

// Form validation
const isValid = computed(() => {
  return (
    form.value.name.trim() !== '' &&
    form.value.email.trim() !== '' &&
    form.value.subject.trim() !== '' &&
    form.value.message.trim() !== ''
  )
})

// Handle form submission
const handleSubmit = async () => {
  if (!isValid.value) {
    toast.error('Please fill in all required fields')
    return
  }

  isSubmitting.value = true

  try {
    const response = await axios.post('/api/v1/contact', {
      name: form.value.name,
      email: form.value.email,
      phone: form.value.phone || null,
      subject: form.value.subject,
      message: form.value.message,
      honeypot: form.value.website // Send honeypot
    })
    
    if (response.data.success) {
      toast.success('Thank you! Your message has been sent successfully.')
      submitted.value = true
      
      // Reset form
      form.value = {
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: '',
        website: ''
      }
    }
  } catch (error) {
    console.error('Contact form error:', error)
    toast.error(error.response?.data?.message || 'Failed to send message. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}

// Reset submitted state to send another message
const sendAnother = () => {
  submitted.value = false
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-primary-700 to-primary-900 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Contact Us</h1>
        <p class="text-xl text-primary-100 max-w-2xl mx-auto">
          We'd love to hear from you. Get in touch with our team.
        </p>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Contact Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
          <!-- Success Message -->
          <div v-if="submitted" class="text-center py-8">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Message Sent!</h3>
            <p class="text-gray-600 mb-6">Thank you for reaching out. We'll get back to you within 24 hours.</p>
            <button 
              @click="sendAnother"
              class="inline-flex items-center px-6 py-3 bg-[#CF0D0F] text-white rounded-lg font-medium hover:bg-[#F6211F] transition-colors"
            >
              Send Another Message
            </button>
          </div>

          <!-- Contact Form -->
          <template v-else>
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Honeypot field (hidden from users, spam bots will fill it) -->
              <input 
                v-model="form.website" 
                type="text" 
                name="website"
                autocomplete="off"
                tabindex="-1"
                class="absolute opacity-0 pointer-events-none"
                style="position: absolute; left: -9999px;"
                aria-hidden="true"
              />

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input 
                  v-model="form.name"
                  type="text" 
                  placeholder="John Doe"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F] transition-colors"
                />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                  <input 
                    v-model="form.email"
                    type="email" 
                    placeholder="john@example.com"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F] transition-colors"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Phone (Optional)</label>
                  <input 
                    v-model="form.phone"
                    type="tel" 
                    placeholder="+61 XXX XXX XXX"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F] transition-colors"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                <input 
                  v-model="form.subject"
                  type="text" 
                  placeholder="How can we help?"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F] transition-colors"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                <textarea 
                  v-model="form.message"
                  rows="5" 
                  placeholder="Your message here..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F] transition-colors resize-none"
                ></textarea>
              </div>

              <button 
                type="submit"
                :disabled="isSubmitting || !isValid"
                class="w-full bg-[#CF0D0F] text-white py-3 px-6 rounded-lg font-medium hover:bg-[#F6211F] transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center justify-center"
              >
                <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isSubmitting ? 'Sending...' : 'Send Message' }}
              </button>
            </form>
          </template>
        </div>

        <!-- Contact Information -->
        <div class="space-y-6">
          <!-- Info Card -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Get in Touch</h2>
            
            <div class="space-y-6">
              <div class="flex items-start space-x-4">
                <div class="w-10 h-10 bg-[#EFEFEF] rounded-full flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-[#CF0D0F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-medium text-gray-800">Address</h3>
                  <p class="text-gray-600">6/1053 Old Princes Highway<br />Engadine, NSW 2233, Australia</p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="w-10 h-10 bg-[#EFEFEF] rounded-full flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-[#CF0D0F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-medium text-gray-800">Phone</h3>
                  <p class="text-gray-600">
                    <a href="tel:+61422235020" class="hover:text-[#CF0D0F] transition-colors">+61 422 235 020</a><br />
                    <a href="tel:+61426531457" class="hover:text-[#CF0D0F] transition-colors">+61 426 531 457</a>
                  </p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="w-10 h-10 bg-[#EFEFEF] rounded-full flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-[#CF0D0F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-medium text-gray-800">Email</h3>
                  <p class="text-gray-600">
                    <a href="mailto:info@zambezimeats.com.au" class="hover:text-[#CF0D0F] transition-colors">info@zambezimeats.com.au</a>
                  </p>
                </div>
              </div>

              <!-- WhatsApp -->
              <div class="flex items-start space-x-4">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                  </svg>
                </div>
                <div>
                  <h3 class="font-medium text-gray-800">WhatsApp</h3>
                  <p class="text-gray-600">
                    <a href="https://wa.me/61422235020" target="_blank" rel="noopener noreferrer" class="hover:text-green-600 transition-colors">Chat with us on WhatsApp</a>
                  </p>
                </div>
              </div>
            </div>

            <!-- Social Media Links -->
            <div class="mt-8 pt-6 border-t border-gray-200">
              <h3 class="font-medium text-gray-800 mb-4">Follow Us</h3>
              <div class="flex space-x-4">
                <a 
                  href="https://facebook.com/zambezimeats" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  class="w-10 h-10 bg-[#EFEFEF] rounded-full flex items-center justify-center hover:bg-[#CF0D0F] hover:text-white transition-all"
                  aria-label="Facebook"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                  </svg>
                </a>
                <a 
                  href="https://instagram.com/zambezimeats" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  class="w-10 h-10 bg-[#EFEFEF] rounded-full flex items-center justify-center hover:bg-[#CF0D0F] hover:text-white transition-all"
                  aria-label="Instagram"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                  </svg>
                </a>
                <a 
                  href="https://tiktok.com/@zambezimeats" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  class="w-10 h-10 bg-[#EFEFEF] rounded-full flex items-center justify-center hover:bg-[#CF0D0F] hover:text-white transition-all"
                  aria-label="TikTok"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                  </svg>
                </a>
              </div>
            </div>
          </div>

          <!-- Business Hours -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Business Hours</h2>
            <div class="space-y-2 text-gray-600">
              <div class="flex justify-between">
                <span>Monday - Sunday</span>
                <span class="font-medium">7:00 AM - 6:00 PM AEST</span>
              </div>
            </div>
          </div>

          <!-- Service Area -->
          <div class="bg-[#EFEFEF] rounded-lg p-6 border border-gray-200">
            <h3 class="font-semibold text-[#4D4B4C] mb-2">Service Area</h3>
            <p class="text-[#6F6F6F] text-sm">
              We deliver to Menangle Park, Engadine, City of Campbelltown, and greater Sydney, Australia.
            </p>
          </div>
        </div>
      </div>

      <!-- Back Link -->
      <div class="mt-12 text-center">
        <RouterLink 
          to="/" 
          class="inline-flex items-center text-[#CF0D0F] hover:text-[#F6211F] font-medium"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Home
        </RouterLink>
      </div>
    </div>
  </div>
</template>
