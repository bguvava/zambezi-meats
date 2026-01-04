<template>
  <div
    class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 transition-all duration-200 hover:shadow-md hover:border-[#CF0D0F]">
    <!-- Header with Icon and Change Badge -->
    <div class="flex items-center justify-between mb-4">
      <!-- Icon -->
      <div class="w-12 h-12 rounded-xl flex items-center justify-center" :style="{ background: iconBackground }">
        <component :is="icon" class="w-6 h-6 text-white" />
      </div>

      <!-- Percentage Change Badge -->
      <span v-if="showChange && change !== null"
        class="text-sm font-bold px-3 py-1 rounded-full flex items-center gap-1" :style="changeBadgeStyle">
        <component :is="changeIcon" class="w-4 h-4" />
        {{ Math.abs(change) }}%
      </span>
    </div>

    <!-- Value -->
    <p class="text-2xl font-bold mb-1 text-gray-900">
      {{ formattedValue }}
    </p>

    <!-- Label -->
    <p class="text-xs font-medium text-gray-600">
      {{ label }}
    </p>

    <!-- Comparison Text (Optional) -->
    <p v-if="comparisonText" class="text-xs mt-2" style="color: #6F6F6F;">
      {{ comparisonText }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { TrendingUpIcon, TrendingDownIcon, MinusIcon } from 'lucide-vue-next'

const props = defineProps({
  // Icon component (from lucide-vue-next or heroicons)
  icon: {
    type: [Object, Function],
    required: true
  },

  // Icon background gradient
  iconBackground: {
    type: String,
    default: 'linear-gradient(135deg, #EFEFEF 0%, #e5e5e5 100%)'
  },

  // Stat label
  label: {
    type: String,
    required: true
  },

  // Stat value (number or string)
  value: {
    type: [Number, String],
    required: true
  },

  // Value prefix (e.g., '$', '#')
  prefix: {
    type: String,
    default: ''
  },

  // Value suffix (e.g., '%', 'kg')
  suffix: {
    type: String,
    default: ''
  },

  // Percentage change (positive or negative number)
  change: {
    type: Number,
    default: null
  },

  // Show change badge
  showChange: {
    type: Boolean,
    default: true
  },

  // Comparison text (e.g., "vs last month")
  comparisonText: {
    type: String,
    default: ''
  },

  // Format value as currency
  isCurrency: {
    type: Boolean,
    default: false
  },

  // Format value with commas
  formatNumber: {
    type: Boolean,
    default: true
  }
})

// Format the value for display
const formattedValue = computed(() => {
  let val = props.value

  // Handle currency formatting
  if (props.isCurrency) {
    const numValue = typeof val === 'string' ? parseFloat(val) : val
    return props.prefix + numValue.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }) + props.suffix
  }

  // Handle number formatting with commas
  if (props.formatNumber && typeof val === 'number') {
    return props.prefix + val.toLocaleString('en-US') + props.suffix
  }

  // Default: just add prefix/suffix
  return props.prefix + val + props.suffix
})

// Determine which icon to show for change
const changeIcon = computed(() => {
  if (props.change === null || props.change === 0) return MinusIcon
  return props.change > 0 ? TrendingUpIcon : TrendingDownIcon
})

// Style for change badge based on positive/negative/neutral
const changeBadgeStyle = computed(() => {
  if (props.change === null || props.change === 0) {
    return {
      backgroundColor: '#EFEFEF',
      color: '#6F6F6F'
    }
  }

  if (props.change > 0) {
    return {
      backgroundColor: '#10B981', // Green
      color: 'white'
    }
  }

  return {
    backgroundColor: '#EF4444', // Red
    color: 'white'
  }
})
</script>
