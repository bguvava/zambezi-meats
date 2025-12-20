<script setup>
/**
 * Testimonials Section
 *
 * Customer testimonials with carousel/cards.
 *
 * @requirement LAND-007 Testimonials section with carousel
 */
import { ref, onMounted, onUnmounted } from 'vue'

const sectionRef = ref(null)
const isVisible = ref(false)
const currentSlide = ref(0)
const autoplayInterval = ref(null)

const testimonials = [
  {
    id: 1,
    name: 'Sarah Mitchell',
    location: 'Sydney, NSW',
    avatar: '/images/avatars/avatar-1.jpg',
    rating: 5,
    text: "The quality of meat from Zambezi is incredible! My family can really taste the difference. The scotch fillet was the best we've ever had at home.",
    product: 'Premium Scotch Fillet'
  },
  {
    id: 2,
    name: 'James Wilson',
    location: 'Melbourne, VIC',
    avatar: '/images/avatars/avatar-2.jpg',
    rating: 5,
    text: "Fast delivery and always fresh. I've been ordering from Zambezi for 6 months now and they've never disappointed. Highly recommend!",
    product: 'Weekly Meat Box'
  },
  {
    id: 3,
    name: 'Emma Thompson',
    location: 'Brisbane, QLD',
    avatar: '/images/avatars/avatar-3.jpg',
    rating: 5,
    text: 'As someone who cares about where my food comes from, I love that Zambezi sources locally. The lamb cutlets are absolutely divine!',
    product: 'Free-Range Lamb Cutlets'
  },
  {
    id: 4,
    name: 'Michael Chen',
    location: 'Perth, WA',
    avatar: '/images/avatars/avatar-4.jpg',
    rating: 5,
    text: "The convenience of having premium quality meats delivered to my door is amazing. Customer service is top-notch too!",
    product: 'BBQ Selection Pack'
  }
]

onMounted(() => {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          isVisible.value = true
          startAutoplay()
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

onUnmounted(() => {
  stopAutoplay()
})

function startAutoplay() {
  autoplayInterval.value = setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % testimonials.length
  }, 5000)
}

function stopAutoplay() {
  if (autoplayInterval.value) {
    clearInterval(autoplayInterval.value)
    autoplayInterval.value = null
  }
}

function goToSlide(index) {
  currentSlide.value = index
  stopAutoplay()
  startAutoplay()
}

function nextSlide() {
  currentSlide.value = (currentSlide.value + 1) % testimonials.length
  stopAutoplay()
  startAutoplay()
}

function prevSlide() {
  currentSlide.value = (currentSlide.value - 1 + testimonials.length) % testimonials.length
  stopAutoplay()
  startAutoplay()
}
</script>

<template>
  <section ref="sectionRef" class="py-20 bg-secondary-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div
        class="text-center mb-16 transition-all duration-700"
        :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
      >
        <span class="text-primary-600 font-semibold text-sm tracking-wider uppercase">Testimonials</span>
        <h2 class="text-3xl sm:text-4xl font-bold text-white mt-2 mb-4">
          What Our Customers Say
        </h2>
        <p class="text-gray-400 max-w-2xl mx-auto">
          Don't just take our word for it. Here's what our happy customers have to say about their Zambezi experience.
        </p>
      </div>

      <!-- Testimonials Carousel -->
      <div class="relative">
        <!-- Cards Container -->
        <div class="overflow-hidden">
          <div
            class="flex transition-transform duration-500 ease-out"
            :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
          >
            <div
              v-for="testimonial in testimonials"
              :key="testimonial.id"
              class="w-full flex-shrink-0 px-4"
            >
              <div class="max-w-3xl mx-auto">
                <div
                  class="bg-white/5 backdrop-blur-sm rounded-2xl p-8 md:p-12 border border-white/10 transition-all duration-500"
                  :class="isVisible ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
                >
                  <!-- Quote Icon -->
                  <svg class="w-12 h-12 text-primary-600/30 mb-6" fill="currentColor" viewBox="0 0 32 32">
                    <path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z" />
                  </svg>

                  <!-- Testimonial Text -->
                  <p class="text-xl md:text-2xl text-white/90 leading-relaxed mb-8">
                    "{{ testimonial.text }}"
                  </p>

                  <!-- Rating -->
                  <div class="flex gap-1 mb-6">
                    <svg
                      v-for="star in 5"
                      :key="star"
                      class="w-5 h-5"
                      :class="star <= testimonial.rating ? 'text-yellow-400' : 'text-gray-600'"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                  </div>

                  <!-- Author -->
                  <div class="flex items-center gap-4">
                    <img
                      :src="testimonial.avatar"
                      :alt="testimonial.name"
                      class="w-14 h-14 rounded-full object-cover border-2 border-primary-600"
                      @error="($event) => $event.target.src = '/images/placeholder-avatar.jpg'"
                    />
                    <div>
                      <h4 class="text-white font-semibold">{{ testimonial.name }}</h4>
                      <p class="text-gray-400 text-sm">{{ testimonial.location }}</p>
                      <p class="text-primary-600 text-sm mt-1">Purchased: {{ testimonial.product }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation Arrows -->
        <button
          class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-2 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors hidden md:flex"
          @click="prevSlide"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <button
          class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-2 w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-colors hidden md:flex"
          @click="nextSlide"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>

        <!-- Dots Navigation -->
        <div class="flex justify-center gap-2 mt-8">
          <button
            v-for="(_, index) in testimonials"
            :key="index"
            class="w-3 h-3 rounded-full transition-all duration-300"
            :class="index === currentSlide ? 'bg-primary-600 w-8' : 'bg-white/30 hover:bg-white/50'"
            @click="goToSlide(index)"
          ></button>
        </div>
      </div>
    </div>
  </section>
</template>
