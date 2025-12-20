<template>
  <span :class="[
    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
    statusClasses
  ]">
    <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
      <circle cx="4" cy="4" r="3" />
    </svg>
    {{ statusText }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true,
    validator: (value) => ['active', 'suspended', 'inactive'].includes(value)
  }
})

const statusClasses = computed(() => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    suspended: 'bg-yellow-100 text-yellow-800',
    inactive: 'bg-gray-100 text-gray-800'
  }
  return classes[props.status] || classes.inactive
})

const statusText = computed(() => {
  const texts = {
    active: 'Active',
    suspended: 'Suspended',
    inactive: 'Inactive'
  }
  return texts[props.status] || 'Unknown'
})
</script>
