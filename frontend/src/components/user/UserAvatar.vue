<template>
  <div :class="[
    'inline-flex items-center justify-center rounded-full font-semibold',
    sizeClasses,
    colorClasses
  ]" :title="name">
    <img v-if="displaySrc" :src="displaySrc" :alt="name" class="h-full w-full rounded-full object-cover" @error="handleImageError" />
    <span v-else>{{ initials }}</span>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  src: {
    type: String,
    default: null
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  color: {
    type: String,
    default: 'red'
  }
})

const imageError = ref(false)

const displaySrc = computed(() => {
  if (imageError.value) return null
  return props.src || '/images/user.jpg'
})

const initials = computed(() => {
  if (!props.name) return '?'

  const names = props.name.trim().split(' ')
  if (names.length === 1) {
    return names[0].charAt(0).toUpperCase()
  }

  return (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase()
})

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'h-6 w-6 text-xs',
    sm: 'h-8 w-8 text-sm',
    md: 'h-10 w-10 text-base',
    lg: 'h-12 w-12 text-lg',
    xl: 'h-16 w-16 text-xl'
  }
  return sizes[props.size] || sizes.md
})

const colorClasses = computed(() => {
  // If showing image (user avatar or default), no background
  if (displaySrc.value) return ''

  const colors = {
    red: 'bg-red-100 text-red-700',
    blue: 'bg-blue-100 text-blue-700',
    green: 'bg-green-100 text-green-700',
    yellow: 'bg-yellow-100 text-yellow-700',
    purple: 'bg-purple-100 text-purple-700',
    gray: 'bg-gray-100 text-gray-700'
  }
  return colors[props.color] || colors.red
})

function handleImageError() {
  imageError.value = true
}
</script>
