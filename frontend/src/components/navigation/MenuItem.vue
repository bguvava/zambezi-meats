<script setup>
/**
 * MenuItem.vue
 * Navigation menu item with role-based visibility and active state
 */
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  icon: {
    type: Object,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  to: {
    type: String,
    required: true
  },
  badge: {
    type: [Number, String],
    default: null
  },
  badgeColor: {
    type: String,
    default: 'bg-[#CF0D0F] text-white'
  },
  roles: {
    type: Array,
    default: () => ['admin', 'staff', 'customer']
  },
  isCollapsed: {
    type: Boolean,
    default: false
  }
})

const route = useRoute()

// Check if current route is active
const isActive = computed(() => {
  return route.path === props.to || route.path.startsWith(props.to + '/')
})

// Compute classes for active state
const itemClasses = computed(() => {
  if (isActive.value) {
    return 'bg-gradient-to-r from-[#CF0D0F] to-[#F6211F] text-white border-l-4 border-[#CF0D0F]'
  }
  return 'text-gray-700 hover:bg-gray-100 hover:text-[#CF0D0F] border-l-4 border-transparent'
})

// Icon classes
const iconClasses = computed(() => {
  if (isActive.value) {
    return 'text-white'
  }
  return 'text-gray-500 group-hover:text-[#CF0D0F]'
})
</script>

<template>
  <router-link
    :to="to"
    :class="[
      'flex items-center px-3 py-2.5 rounded-r-lg transition-all duration-200 group',
      itemClasses
    ]"
    :title="isCollapsed ? label : ''"
  >
    <!-- Icon -->
    <component
      :is="icon"
      :class="['w-5 h-5 flex-shrink-0 transition-colors', iconClasses]"
    />

    <!-- Label (hidden when collapsed) -->
    <transition name="fade">
      <span v-if="!isCollapsed" class="ml-3 text-sm font-medium">
        {{ label }}
      </span>
    </transition>

    <!-- Badge (hidden when collapsed) -->
    <transition name="fade">
      <span
        v-if="!isCollapsed && badge"
        :class="[
          'ml-auto px-2 py-0.5 rounded-full text-xs font-semibold',
          badgeColor
        ]"
      >
        {{ badge }}
      </span>
    </transition>
  </router-link>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
