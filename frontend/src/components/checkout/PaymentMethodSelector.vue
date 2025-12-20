<script setup>
/**
 * Payment Method Selector Component
 *
 * Displays available payment methods with icons.
 *
 * @requirement CHK-008 Create payment method selection
 */
import { computed } from 'vue'
import {
  CreditCardIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  methods: {
    type: Array,
    required: true
  },
  selected: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['select'])

const getMethodIcon = (id) => {
  switch (id) {
    case 'stripe':
      return 'credit-card'
    case 'paypal':
      return 'paypal'
    case 'afterpay':
      return 'afterpay'
    case 'cod':
      return 'cash'
    default:
      return 'credit-card'
  }
}

const handleSelect = (methodId) => {
  emit('select', methodId)
}
</script>

<template>
  <div class="space-y-3">
    <label
      v-for="method in methods"
      :key="method.id"
      class="relative flex cursor-pointer rounded-lg border p-4 transition-colors"
      :class="{
        'border-emerald-500 bg-emerald-50': selected === method.id,
        'border-gray-200 hover:border-gray-300 hover:bg-gray-50': selected !== method.id
      }"
    >
      <input
        type="radio"
        :checked="selected === method.id"
        class="sr-only"
        @change="handleSelect(method.id)"
      />

      <div class="flex w-full items-center">
        <!-- Radio button -->
        <div
          class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full border-2"
          :class="{
            'border-emerald-500 bg-emerald-500': selected === method.id,
            'border-gray-300': selected !== method.id
          }"
        >
          <div
            v-if="selected === method.id"
            class="h-2 w-2 rounded-full bg-white"
          ></div>
        </div>

        <!-- Icon -->
        <div class="ml-4 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100">
          <!-- Stripe -->
          <svg v-if="method.id === 'stripe'" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
            <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
          </svg>

          <!-- PayPal -->
          <svg v-else-if="method.id === 'paypal'" class="h-6 w-6" viewBox="0 0 24 24" fill="#003087">
            <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.93 4.778-4.005 7.201-9.138 7.201h-2.19a.563.563 0 0 0-.556.479l-1.187 7.527h-.506l-.24 1.516a.56.56 0 0 0 .554.647h3.882c.46 0 .85-.334.922-.788.06-.26.76-4.852.816-5.09a.932.932 0 0 1 .923-.788h.58c3.76 0 6.705-1.528 7.565-5.946.36-1.847.174-3.388-.777-4.471z"/>
          </svg>

          <!-- Afterpay -->
          <svg v-else-if="method.id === 'afterpay'" class="h-6 w-6" viewBox="0 0 24 24" fill="#B2FCE4">
            <circle cx="12" cy="12" r="12" fill="#B2FCE4"/>
            <path d="M7 17V7h3.5c2.5 0 4 1.5 4 3.5S13 14 10.5 14H9v3H7z" fill="#000"/>
          </svg>

          <!-- Cash -->
          <CreditCardIcon v-else class="h-6 w-6 text-gray-600" />
        </div>

        <!-- Content -->
        <div class="ml-4 flex-1">
          <div class="flex items-center justify-between">
            <span class="font-medium text-gray-900">{{ method.name }}</span>
          </div>
          <p class="mt-1 text-sm text-gray-500">{{ method.description }}</p>

          <!-- Afterpay installments -->
          <p
            v-if="method.id === 'afterpay' && method.installments"
            class="mt-1 text-sm font-medium text-emerald-600"
          >
            4 x {{ method.installments.installment_formatted }} interest-free
          </p>
        </div>
      </div>
    </label>
  </div>
</template>
