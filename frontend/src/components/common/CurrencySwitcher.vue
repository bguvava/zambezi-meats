<script setup>
/**
 * Currency Switcher Component
 * 
 * Allows users to switch between USD and AUD currencies
 * Issue 7 from issues003.md
 */
import { ref, onMounted, onUnmounted } from 'vue'
import { useCurrencyStore } from '@/stores/currency'

const currencyStore = useCurrencyStore()
const isOpen = ref(false)

const props = defineProps({
  theme: {
    type: String,
    default: 'light', // 'light' or 'dark'
  },
})

onMounted(() => {
  currencyStore.loadFromStorage()
  currencyStore.fetchExchangeRates()
})

function toggleDropdown() {
  isOpen.value = !isOpen.value
}

function selectCurrency(code) {
  currencyStore.setCurrency(code)
  isOpen.value = false
}

function closeDropdown(event) {
  if (!event.target.closest('.currency-switcher')) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
})
</script>

<template>
  <div class="relative currency-switcher">
    <button @click="toggleDropdown" class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors"
      :class="theme === 'dark' ? 'text-white/90 hover:text-white hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100'">
      <span class="text-sm font-medium">{{ currencyStore.currentCurrency }}</span>
      <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor"
        viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown -->
    <Transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
      <div v-if="isOpen" class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-50">
        <button v-for="currency in currencyStore.availableCurrencies" :key="currency.code"
          @click="selectCurrency(currency.code)"
          class="w-full flex items-center justify-between px-4 py-2 text-sm hover:bg-gray-100 transition-colors"
          :class="{ 'bg-primary-50 text-primary-600': currencyStore.currentCurrency === currency.code }">
          <span class="flex items-center gap-2">
            <span class="font-medium">{{ currency.code }}</span>
            <span class="text-gray-500 text-xs">{{ currency.name }}</span>
          </span>
          <svg v-if="currencyStore.currentCurrency === currency.code" class="w-5 h-5 text-primary-600"
            fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
              clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </Transition>
  </div>
</template>
