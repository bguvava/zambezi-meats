<script setup>
/**
 * Step Indicator Component
 *
 * Shows the checkout progress through all steps.
 *
 * @requirement CHK-002 Implement step indicator
 */
import { computed } from 'vue'
import { CheckIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
  currentStep: {
    type: Number,
    required: true
  },
  steps: {
    type: Array,
    required: true
  }
})

const stepStatus = (step) => {
  if (step.completed) return 'completed'
  if (step.id === props.currentStep) return 'current'
  return 'upcoming'
}
</script>

<template>
  <nav aria-label="Checkout progress">
    <ol class="flex items-center justify-center">
      <li
        v-for="(step, index) in steps"
        :key="step.id"
        :class="[
          index !== steps.length - 1 ? 'pr-8 sm:pr-20' : '',
          'relative'
        ]"
      >
        <!-- Connector line -->
        <div
          v-if="index !== steps.length - 1"
          class="absolute right-0 top-4 hidden h-0.5 w-full sm:block"
          :class="step.completed ? 'bg-emerald-500' : 'bg-gray-200'"
          style="left: 50%; transform: translateX(50%)"
        ></div>

        <!-- Step circle and label -->
        <div class="group relative flex flex-col items-center">
          <!-- Circle -->
          <span
            class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold"
            :class="{
              'bg-emerald-500 text-white': step.completed,
              'border-2 border-emerald-500 bg-white text-emerald-500': stepStatus(step) === 'current',
              'border-2 border-gray-300 bg-white text-gray-400': stepStatus(step) === 'upcoming'
            }"
          >
            <CheckIcon v-if="step.completed" class="h-5 w-5" />
            <span v-else>{{ step.id }}</span>
          </span>

          <!-- Label -->
          <span
            class="mt-2 text-xs font-medium sm:text-sm"
            :class="{
              'text-emerald-600': step.completed || stepStatus(step) === 'current',
              'text-gray-500': stepStatus(step) === 'upcoming'
            }"
          >
            {{ step.name }}
          </span>
        </div>
      </li>
    </ol>
  </nav>
</template>
