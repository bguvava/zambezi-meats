<script setup>
/**
 * Contact Section
 *
 * Contact form and information.
 *
 * @requirement LAND-009 Contact/inquiry section with form
 */
import { ref, onMounted } from 'vue'

const sectionRef = ref(null)
const isVisible = ref(false)

const form = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
})

const errors = ref({})
const isSubmitting = ref(false)
const isSuccess = ref(false)

const contactInfo = [
  {
    icon: 'phone',
    label: 'Phone',
    value: '+61 422 235 020',
    subvalue: '+61 426 531 457',
    href: 'tel:+61422235020'
  },
  {
    icon: 'email',
    label: 'Email',
    value: 'info@zambezimeats.com.au',
    subvalue: null,
    href: 'mailto:info@zambezimeats.com.au'
  },
  {
    icon: 'location',
    label: 'Address',
    value: '6/1053 Old Princes Highway',
    subvalue: 'Engadine, NSW 2233, Australia',
    href: 'https://maps.google.com/?q=6/1053+Old+Princes+Highway+Engadine+NSW+2233'
  },
  {
    icon: 'hours',
    label: 'Hours',
    value: 'Mon - Sun: 7am - 6pm AEST',
    subvalue: null,
    href: null
  }
]

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

function validateForm() {
  const newErrors = {}

  if (!form.value.name) {
    newErrors.name = 'Name is required'
  }

  if (!form.value.email) {
    newErrors.email = 'Email is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    newErrors.email = 'Please enter a valid email'
  }

  if (!form.value.message) {
    newErrors.message = 'Message is required'
  }

  errors.value = newErrors
  return Object.keys(newErrors).length === 0
}

async function handleSubmit() {
  if (!validateForm()) return

  isSubmitting.value = true

  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 1500))

  isSuccess.value = true
  isSubmitting.value = false

  // Reset form
  form.value = { name: '', email: '', phone: '', subject: '', message: '' }
}
</script>

<template>
  <section id="contact" ref="sectionRef" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div class="text-center mb-16 transition-all duration-700"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
        <span class="text-primary-600 font-semibold text-sm tracking-wider uppercase">Contact Us</span>
        <h2 class="text-3xl sm:text-4xl font-bold text-secondary-700 mt-2 mb-4">
          Get In Touch
        </h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Have a question about our products or delivery? We'd love to hear from you.
        </p>
      </div>

      <div class="grid lg:grid-cols-2 gap-12">
        <!-- Contact Information -->
        <div class="transition-all duration-700 delay-200"
          :class="isVisible ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-10'">
          <h3 class="text-2xl font-bold text-secondary-700 mb-6">Contact Information</h3>
          <p class="text-gray-600 mb-8">
            Our team is here to help with any questions about orders, products, or deliveries.
          </p>

          <div class="space-y-6">
            <div v-for="info in contactInfo" :key="info.label" class="flex items-start gap-4">
              <div class="w-12 h-12 bg-primary-600/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <!-- Phone Icon -->
                <svg v-if="info.icon === 'phone'" class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <!-- Email Icon -->
                <svg v-else-if="info.icon === 'email'" class="w-5 h-5 text-primary-600" fill="none"
                  stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <!-- Location Icon -->
                <svg v-else-if="info.icon === 'location'" class="w-5 h-5 text-primary-600" fill="none"
                  stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <!-- Hours Icon -->
                <svg v-else-if="info.icon === 'hours'" class="w-5 h-5 text-primary-600" fill="none"
                  stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500 mb-1">{{ info.label }}</p>
                <component :is="info.href ? 'a' : 'p'" :href="info.href"
                  :target="info.href?.startsWith('http') ? '_blank' : undefined"
                  class="font-semibold text-secondary-700 hover:text-primary-600 transition-colors">
                  {{ info.value }}
                </component>
                <p v-if="info.subvalue" class="text-sm text-gray-600">{{ info.subvalue }}</p>
              </div>
            </div>
          </div>

          <!-- Social Links -->
          <div class="mt-8 pt-8 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-4">Follow us on social media</p>
            <div class="flex gap-4">
              <!-- Facebook -->
              <a href="https://www.facebook.com/share/17hrbEMpKY/" target="_blank" rel="noopener noreferrer"
                class="w-10 h-10 bg-secondary-700 text-white rounded-full flex items-center justify-center hover:bg-primary-600 transition-colors"
                title="Facebook">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
                </svg>
              </a>
              <!-- Instagram -->
              <a href="https://www.instagram.com/zambezi_meats?igsh=OXZkNjVvb2w2enll" target="_blank"
                rel="noopener noreferrer"
                class="w-10 h-10 bg-secondary-700 text-white rounded-full flex items-center justify-center hover:bg-primary-600 transition-colors"
                title="Instagram">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                </svg>
              </a>
              <!-- TikTok -->
              <a href="https://www.tiktok.com/@zambezi.meats?_r=1&_t=ZS-92Jw9xNcw8O" target="_blank"
                rel="noopener noreferrer"
                class="w-10 h-10 bg-secondary-700 text-white rounded-full flex items-center justify-center hover:bg-primary-600 transition-colors"
                title="TikTok">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0011.14-4.02v-6.95a8.16 8.16 0 004.65 1.46v-3.4a4.84 4.84 0 01-1.2-.5z" />
                </svg>
              </a>
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="transition-all duration-700 delay-400"
          :class="isVisible ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-10'">
          <!-- Success Message -->
          <div v-if="isSuccess" class="bg-white rounded-2xl shadow-xl p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-secondary-700 mb-4">Message Sent!</h3>
            <p class="text-gray-600 mb-6">
              Thank you for reaching out. We'll get back to you within 24 hours.
            </p>
            <button
              class="px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors"
              @click="isSuccess = false">
              Send Another Message
            </button>
          </div>

          <!-- Form -->
          <form v-else class="bg-white rounded-2xl shadow-xl p-8" @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
              <!-- Name -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                  Full Name *
                </label>
                <input id="name" v-model="form.name" type="text"
                  class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent transition-colors"
                  :class="errors.name ? 'border-red-500' : 'border-gray-200'" placeholder="John Smith" />
                <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                  Email Address *
                </label>
                <input id="email" v-model="form.email" type="email"
                  class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent transition-colors"
                  :class="errors.email ? 'border-red-500' : 'border-gray-200'" placeholder="john@example.com" />
                <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
              </div>

              <!-- Phone -->
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                  Phone Number
                </label>
                <input id="phone" v-model="form.phone" type="tel"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent transition-colors"
                  placeholder="+61 400 000 000" />
              </div>

              <!-- Subject -->
              <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                  Subject
                </label>
                <select id="subject" v-model="form.subject"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent transition-colors">
                  <option value="">Select a subject</option>
                  <option value="order">Order Inquiry</option>
                  <option value="delivery">Delivery Question</option>
                  <option value="product">Product Information</option>
                  <option value="feedback">Feedback</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>

            <!-- Message -->
            <div class="mb-6">
              <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                Message *
              </label>
              <textarea id="message" v-model="form.message" rows="5"
                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-600 focus:border-transparent transition-colors resize-none"
                :class="errors.message ? 'border-red-500' : 'border-gray-200'"
                placeholder="How can we help you?"></textarea>
              <p v-if="errors.message" class="text-red-500 text-sm mt-1">{{ errors.message }}</p>
            </div>

            <!-- Submit Button -->
            <button type="submit" :disabled="isSubmitting"
              class="w-full py-4 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
              <svg v-if="isSubmitting" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              <span>{{ isSubmitting ? 'Sending...' : 'Send Message' }}</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>
