<template>
  <span :class="[
    'inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium',
    roleClasses
  ]">
    <component :is="roleIcon" class="-ml-0.5 mr-1 h-3.5 w-3.5" aria-hidden="true" />
    {{ roleText }}
  </span>
</template>

<script setup>
import { computed } from 'vue'
import {
  ShieldCheckIcon,
  UserGroupIcon,
  UserIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  role: {
    type: String,
    required: true,
    validator: (value) => ['admin', 'staff', 'customer'].includes(value)
  }
})

const roleClasses = computed(() => {
  const classes = {
    admin: 'bg-red-50 text-red-700 border border-red-200',
    staff: 'bg-blue-50 text-blue-700 border border-blue-200',
    customer: 'bg-purple-50 text-purple-700 border border-purple-200'
  }
  return classes[props.role] || classes.customer
})

const roleText = computed(() => {
  const texts = {
    admin: 'Admin',
    staff: 'Staff',
    customer: 'Customer'
  }
  return texts[props.role] || 'Unknown'
})

const roleIcon = computed(() => {
  const icons = {
    admin: ShieldCheckIcon,
    staff: UserGroupIcon,
    customer: UserIcon
  }
  return icons[props.role] || UserIcon
})
</script>
