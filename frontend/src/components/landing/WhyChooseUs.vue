<script setup>
/**
 * Why Choose Us Section
 *
 * Displays key selling points with icons and animations.
 *
 * @requirement LAND-005 "Why Choose Us" section with icons
 */
import { ref, onMounted } from 'vue'

const sectionRef = ref(null)
const isVisible = ref(false)

const features = [
  {
    icon: 'quality',
    title: 'Premium Quality',
    description: 'Hand-selected from the finest Australian farms',
    color: 'primary'
  },
  {
    icon: 'expertise',
    title: 'Expert Butchers',
    description: 'Over 30 years of combined experience',
    color: 'blue'
  },
  {
    icon: 'fresh',
    title: 'Always Fresh',
    description: 'Never frozen, delivered within days',
    color: 'rose'
  },
  {
    icon: 'local',
    title: 'Australian Sourced',
    description: 'Supporting local farmers and communities',
    color: 'emerald'
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
  <section ref="sectionRef" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div
        class="text-center mb-16 transition-all duration-700"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
      >
        <span class="text-primary-600 font-semibold text-sm tracking-wider uppercase">Why Zambezi Meats</span>
        <h2 class="text-3xl sm:text-4xl font-bold text-secondary-700 mt-2 mb-4">
          The Difference is Quality
        </h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Premium Australian meats from trusted local farms, delivered fresh daily
        </p>
      </div>

      <!-- Features Grid - Icon-Based Design -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-6xl mx-auto">
        <div
          v-for="(feature, index) in features"
          :key="feature.title"
          class="group text-center transition-all duration-500 hover:-translate-y-2"
          :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
          :style="{ transitionDelay: `${index * 100 + 200}ms` }"
        >
          <!-- Large Icon Circle -->
          <div class="flex justify-center mb-6">
            <div
              class="w-24 h-24 rounded-full flex items-center justify-center transition-all duration-300 group-hover:scale-110 group-hover:shadow-lg"
              :class="{
                'bg-primary-600 text-white': feature.color === 'primary',
                'bg-blue-600 text-white': feature.color === 'blue',
                'bg-emerald-600 text-white': feature.color === 'emerald',
                'bg-rose-600 text-white': feature.color === 'rose'
              }"
            >
              <!-- Quality Icon -->
              <svg v-if="feature.icon === 'quality'" class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
              </svg>

              <!-- Expertise Icon -->
              <svg v-else-if="feature.icon === 'expertise'" class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
              </svg>

              <!-- Local Icon -->
              <svg v-else-if="feature.icon === 'local'" class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>

              <!-- Fresh Icon -->
              <svg v-else-if="feature.icon === 'fresh'" class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
          </div>

          <!-- Content -->
          <h3 class="text-xl font-bold text-secondary-700 mb-2 group-hover:text-primary-600 transition-colors">
            {{ feature.title }}
          </h3>
          <p class="text-gray-600 leading-relaxed text-sm">
            {{ feature.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
