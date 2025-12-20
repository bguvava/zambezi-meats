<script setup>
/**
 * WhatsAppButton Component
 * 
 * Floating WhatsApp button at bottom-left for customer contact
 */
import { ref, onMounted, onUnmounted } from 'vue'

const isVisible = ref(false)
const isHovered = ref(false)

// WhatsApp number from project_description.md
const whatsappNumber = '61422235020'
const whatsappUrl = `https://wa.me/${whatsappNumber}?text=Hi%20Zambezi%20Meats!%20I%20have%20a%20question.`

const checkScroll = () => {
  isVisible.value = window.scrollY > 100
}

onMounted(() => {
  window.addEventListener('scroll', checkScroll, { passive: true })
  // Show after a short delay for smoother UX
  setTimeout(() => {
    isVisible.value = true
  }, 1000)
})

onUnmounted(() => {
  window.removeEventListener('scroll', checkScroll)
})
</script>

<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 -translate-x-4"
    enter-to-class="opacity-100 translate-x-0"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100 translate-x-0"
    leave-to-class="opacity-0 -translate-x-4"
  >
    <a
      v-show="isVisible"
      :href="whatsappUrl"
      target="_blank"
      rel="noopener noreferrer"
      @mouseenter="isHovered = true"
      @mouseleave="isHovered = false"
      class="fixed bottom-6 left-6 z-50 flex items-center gap-3 bg-[#25D366] hover:bg-[#20BD5A] text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300"
      :class="isHovered ? 'pr-4' : ''"
      aria-label="Chat on WhatsApp"
    >
      <div class="w-12 h-12 flex items-center justify-center">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
      </div>
      <Transition
        enter-active-class="transition-all duration-200"
        enter-from-class="opacity-0 w-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-all duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0 w-0"
      >
        <span v-if="isHovered" class="text-sm font-medium whitespace-nowrap">
          Chat with us
        </span>
      </Transition>
    </a>
  </Transition>
</template>
