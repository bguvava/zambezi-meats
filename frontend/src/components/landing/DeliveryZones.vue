<script setup>
/**
 * Delivery Zones Section
 *
 * Interactive map/list of delivery areas.
 *
 * @requirement LAND-008 Delivery zones preview section
 */
import { ref, onMounted } from 'vue'

const sectionRef = ref(null)
const isVisible = ref(false)
const activeZone = ref(0)

const deliveryZones = [
  {
    id: 1,
    name: 'Sydney Metro',
    description: 'Same-day delivery for orders before 2pm',
    fee: 'FREE over $100',
    areas: ['CBD', 'Eastern Suburbs', 'Inner West', 'North Shore', 'Northern Beaches'],
    icon: '🏙️'
  },
  {
    id: 2,
    name: 'Greater Sydney',
    description: 'Next-day delivery available',
    fee: '$12.95 flat rate',
    areas: ['Parramatta', 'Penrith', 'Campbelltown', 'Liverpool', 'Sutherland'],
    icon: '🚚'
  },
  {
    id: 3,
    name: 'Regional NSW',
    description: 'Delivery within 2-3 business days',
    fee: '$18.95 flat rate',
    areas: ['Newcastle', 'Wollongong', 'Central Coast', 'Blue Mountains'],
    icon: '📦'
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
</script>

<template>
  <section ref="sectionRef" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div
        class="text-center mb-16 transition-all duration-700"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
      >
        <span class="text-primary-600 font-semibold text-sm tracking-wider uppercase">Delivery</span>
        <h2 class="text-3xl sm:text-4xl font-bold text-secondary-700 mt-2 mb-4">
          We Deliver to Your Area
        </h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Fresh, premium meats delivered right to your doorstep. Check our delivery zones and rates below.
        </p>
      </div>

      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <!-- Delivery Zones Cards -->
        <div
          class="space-y-4 transition-all duration-700 delay-200"
          :class="isVisible ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-10'"
        >
          <button
            v-for="(zone, index) in deliveryZones"
            :key="zone.id"
            class="w-full text-left p-6 rounded-2xl border-2 transition-all duration-300"
            :class="activeZone === index
              ? 'border-primary-600 bg-primary-600/5 shadow-lg'
              : 'border-gray-200 hover:border-primary-600/50 hover:shadow-md'"
            @click="activeZone = index"
          >
            <div class="flex items-start gap-4">
              <span class="text-3xl">{{ zone.icon }}</span>
              <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                  <h3 class="text-lg font-bold text-secondary-700">{{ zone.name }}</h3>
                  <span class="text-sm font-semibold text-primary-600">{{ zone.fee }}</span>
                </div>
                <p class="text-gray-600 text-sm mb-3">{{ zone.description }}</p>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="area in zone.areas"
                    :key="area"
                    class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full"
                  >
                    {{ area }}
                  </span>
                </div>
              </div>
            </div>
          </button>
        </div>

        <!-- Map Placeholder / Info Card -->
        <div
          class="relative transition-all duration-700 delay-400"
          :class="isVisible ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-10'"
        >
          <div class="bg-gradient-to-br from-secondary-700 to-secondary-900 rounded-2xl p-8 text-white">
            <div class="mb-6">
              <span class="text-4xl">{{ deliveryZones[activeZone].icon }}</span>
            </div>
            <h3 class="text-2xl font-bold mb-4">{{ deliveryZones[activeZone].name }}</h3>
            <p class="text-gray-300 mb-6">{{ deliveryZones[activeZone].description }}</p>

            <div class="space-y-4 mb-8">
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Temperature-controlled delivery</span>
              </div>
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Real-time tracking available</span>
              </div>
              <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>Flexible delivery windows</span>
              </div>
            </div>

            <div class="flex items-baseline gap-2 mb-6">
              <span class="text-3xl font-bold text-primary-500">{{ deliveryZones[activeZone].fee }}</span>
            </div>

            <a
              href="#contact"
              class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition-colors"
            >
              <span>Check Your Postcode</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
            </a>
          </div>

          <!-- Decorative elements -->
          <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-primary-600/20 rounded-full blur-2xl -z-10"></div>
          <div class="absolute -top-4 -left-4 w-24 h-24 bg-accent-100/20 rounded-full blur-xl -z-10"></div>
        </div>
      </div>
    </div>
  </section>
</template>
