<script setup>
/**
 * Service Area Map Section
 *
 * Displays service delivery areas within 50km radius of company address
 * Company Address: 6/1053 Old Princes Highway, Engadine, NSW 2233, Australia
 *
 * @requirement ISSUE-005 Service area map showing 50km radius
 */
import { ref, onMounted } from 'vue'

const sectionRef = ref(null)
const isVisible = ref(false)

// Company address
const companyAddress = {
  street: '6/1053 Old Princes Highway',
  suburb: 'Engadine',
  state: 'NSW',
  postcode: '2233',
  country: 'Australia',
  coordinates: { lat: -34.0654, lng: 151.0115 }
}

// Service areas within 50km radius
const serviceAreas = [
  {
    zone: 'Sydney CBD & Inner Suburbs',
    distance: '35-45km',
    deliveryTime: 'Same day delivery available',
    fee: 'Free over $150',
    areas: ['Sydney CBD', 'Surry Hills', 'Darlinghurst', 'Redfern', 'Paddington', 'Bondi', 'Coogee'],
    icon: '🏙️',
    color: 'from-primary-500 to-primary-600'
  },
  {
    zone: 'Eastern Suburbs',
    distance: '15-30km',
    deliveryTime: 'Same day delivery',
    fee: 'Free over $100',
    areas: ['Maroubra', 'Randwick', 'Kingsford', 'Mascot', 'Kensington', 'Botany'],
    icon: '🏖️',
    color: 'from-blue-500 to-blue-600'
  },
  {
    zone: 'Southern Suburbs',
    distance: '5-20km',
    deliveryTime: '2-3 hours',
    fee: 'Free over $80',
    areas: ['Sutherland', 'Cronulla', 'Miranda', 'Caringbah', 'Gymea', 'Kirrawee', 'Jannali', 'Engadine'],
    icon: '🌳',
    color: 'from-green-500 to-green-600'
  },
  {
    zone: 'St George Area',
    distance: '20-35km',
    deliveryTime: 'Same day delivery',
    fee: 'Free over $100',
    areas: ['Hurstville', 'Kogarah', 'Rockdale', 'Brighton-Le-Sands', 'Sans Souci', 'Arncliffe'],
    icon: '🏡',
    color: 'from-purple-500 to-purple-600'
  },
  {
    zone: 'South West Sydney',
    distance: '25-45km',
    deliveryTime: 'Next day delivery',
    fee: 'Free over $150',
    areas: ['Liverpool', 'Bankstown', 'Campbelltown', 'Revesby', 'Padstow', 'Panania'],
    icon: '🏘️',
    color: 'from-orange-500 to-orange-600'
  },
  {
    zone: 'Illawarra Region',
    distance: '30-50km',
    deliveryTime: 'Next day delivery',
    fee: 'Free over $120',
    areas: ['Wollongong', 'Shellharbour', 'Kiama', 'Dapto', 'Warilla', 'Port Kembla'],
    icon: '⛰️',
    color: 'from-teal-500 to-teal-600'
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

function openGoogleMaps() {
  const url = `https://maps.google.com/?q=6/1053+Old+Princes+Highway+Engadine+NSW+2233+Australia`
  window.open(url, '_blank')
}
</script>

<template>
  <section ref="sectionRef" class="py-20 bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div class="text-center mb-16 transition-all duration-700"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
        <span class="text-primary-600 font-semibold text-sm tracking-wider uppercase">Delivery Coverage</span>
        <h2 class="text-3xl sm:text-4xl font-bold text-secondary-700 mt-2 mb-4">
          We Deliver Across Sydney & Illawarra
        </h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Fresh, premium meats delivered to your door within 50km of our Engadine location.
          Same-day delivery available for most areas.
        </p>
      </div>

      <!-- Company Address Card -->
      <div class="max-w-3xl mx-auto mb-12 transition-all duration-700 delay-100"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
        <div class="bg-white rounded-2xl shadow-lg border-2 border-primary-600 p-6 sm:p-8">
          <div class="flex flex-col sm:flex-row items-center gap-6">
            <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
              <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <div class="flex-1 text-center sm:text-left">
              <h3 class="text-lg font-semibold text-gray-900 mb-1">Our Location</h3>
              <p class="text-gray-600">
                {{ companyAddress.street }}<br>
                {{ companyAddress.suburb }}, {{ companyAddress.state }} {{ companyAddress.postcode }}
              </p>
            </div>
            <button @click="openGoogleMaps"
              class="px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-all duration-300 hover:shadow-lg flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
              </svg>
              Get Directions
            </button>
          </div>
        </div>
      </div>

      <!-- Service Areas Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="(area, index) in serviceAreas" :key="index"
          class="group bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-500 hover:-translate-y-1"
          :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
          :style="{ transitionDelay: `${index * 100 + 200}ms` }">
          <!-- Gradient Header -->
          <div :class="`bg-gradient-to-r ${area.color} p-6 text-white`">
            <div class="flex items-center gap-3 mb-2">
              <span class="text-3xl">{{ area.icon }}</span>
              <div>
                <h3 class="font-bold text-lg">{{ area.zone }}</h3>
                <p class="text-sm text-white/80">{{ area.distance }}</p>
              </div>
            </div>
          </div>

          <!-- Content -->
          <div class="p-6">
            <div class="space-y-3 mb-4">
              <div class="flex items-center gap-2 text-gray-700">
                <svg class="w-5 h-5 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor"
                  viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm">{{ area.deliveryTime }}</span>
              </div>
              <div class="flex items-center gap-2 text-gray-700">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium text-green-600">{{ area.fee }}</span>
              </div>
            </div>

            <!-- Areas -->
            <div class="border-t border-gray-200 pt-4">
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-2">Suburbs Covered</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-for="(suburb, subIndex) in area.areas" :key="subIndex"
                  class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                  {{ suburb }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="text-center mt-16 transition-all duration-700 delay-700"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 max-w-3xl mx-auto">
          <h3 class="text-2xl font-bold text-gray-900 mb-4">
            Don't See Your Area?
          </h3>
          <p class="text-gray-600 mb-6">
            We're constantly expanding our delivery coverage. Contact us to check if we can deliver to your location
            or to discuss special delivery arrangements for bulk orders.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="tel:+61422235020"
              class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              Call Us: +61 422 235 020
            </a>
            <RouterLink to="/contact"
              class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              Send an Inquiry
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
@keyframes float {

  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-10px);
  }
}

.group:hover .text-3xl {
  animation: float 2s ease-in-out infinite;
}
</style>
