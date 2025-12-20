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
    description: 'Every cut is hand-selected by our expert butchers from the finest Australian farms, ensuring exceptional quality and taste.',
    color: 'primary'
  },
  {
    icon: 'delivery',
    title: 'Fast Delivery',
    description: 'Same-day delivery available for orders placed before 2pm. Your fresh meats arrive perfectly chilled and ready to cook.',
    color: 'green'
  },
  {
    icon: 'guarantee',
    title: '100% Satisfaction',
    description: 'Not happy with your order? We offer a full refund or replacement, no questions asked. Your satisfaction is our priority.',
    color: 'primary'
  },
  {
    icon: 'expertise',
    title: 'Expert Butchers',
    description: 'With over 30 years of combined experience, our butchers know exactly how to prepare each cut for maximum flavor.',
    color: 'blue'
  },
  {
    icon: 'local',
    title: 'Locally Sourced',
    description: 'We partner with trusted local farms to bring you the freshest produce while supporting Australian agriculture.',
    color: 'emerald'
  },
  {
    icon: 'fresh',
    title: 'Always Fresh',
    description: 'Never frozen, always fresh. Our meats go from farm to your table within days, not weeks.',
    color: 'rose'
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
          We're passionate about providing the best quality meats to Australian families.
          Here's what sets us apart.
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="(feature, index) in features"
          :key="feature.title"
          class="group bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-2"
          :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
          :style="{ transitionDelay: `${index * 100 + 200}ms` }"
        >
          <!-- Icon -->
          <div
            class="w-14 h-14 rounded-xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110"
            :class="{
              'bg-primary-600/10 text-primary-600': feature.color === 'primary',
              'bg-green-100 text-green-600': feature.color === 'green',
              'bg-blue-100 text-blue-600': feature.color === 'blue',
              'bg-emerald-100 text-emerald-600': feature.color === 'emerald',
              'bg-rose-100 text-rose-600': feature.color === 'rose'
            }"
          >
            <!-- Quality Icon -->
            <svg v-if="feature.icon === 'quality'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>

            <!-- Delivery Icon -->
            <svg v-else-if="feature.icon === 'delivery'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
            </svg>

            <!-- Guarantee Icon -->
            <svg v-else-if="feature.icon === 'guarantee'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>

            <!-- Expertise Icon -->
            <svg v-else-if="feature.icon === 'expertise'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>

            <!-- Local Icon -->
            <svg v-else-if="feature.icon === 'local'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>

            <!-- Fresh Icon -->
            <svg v-else-if="feature.icon === 'fresh'" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>

          <!-- Content -->
          <h3 class="text-xl font-bold text-secondary-700 mb-3 group-hover:text-primary-600 transition-colors">
            {{ feature.title }}
          </h3>
          <p class="text-gray-600 leading-relaxed">
            {{ feature.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
