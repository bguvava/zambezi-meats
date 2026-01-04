<script setup>
/**
 * Sidebar.vue
 * Collapsible navigation sidebar with localStorage persistence
 */
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
  role: {
    type: String,
    required: true,
    validator: (value) => ['admin', 'staff', 'customer'].includes(value)
  }
})

const route = useRoute()
const isCollapsed = ref(false)

// Load collapse state from localStorage
onMounted(() => {
  const saved = localStorage.getItem('sidebar-collapsed')
  if (saved !== null) {
    isCollapsed.value = saved === 'true'
  }
})

// Save collapse state to localStorage
watch(isCollapsed, (newValue) => {
  localStorage.setItem('sidebar-collapsed', newValue.toString())
})

// Computed classes
const sidebarWidth = computed(() => {
  return isCollapsed.value ? 'w-16' : 'w-64'
})

const logoSize = computed(() => {
  return isCollapsed.value ? 'text-xl' : 'text-2xl'
})

// Toggle collapse
function toggleSidebar() {
  isCollapsed.value = !isCollapsed.value
}
</script>

<template>
  <aside
    :class="[
      'fixed left-0 top-0 h-screen bg-white border-r-2 border-gray-200 transition-all duration-300 ease-in-out z-40',
      sidebarWidth
    ]"
  >
    <!-- Logo Section -->
    <div class="h-16 flex items-center justify-center border-b-2 border-gray-200 px-4">
      <router-link to="/" class="flex items-center space-x-2">
        <img src="/images/logo-white.png" alt="Zambezi Meats" 
          :class="isCollapsed ? 'h-10 w-10' : 'h-12 w-12'" 
          class="object-contain transition-all" />
        <transition name="fade">
          <span v-if="!isCollapsed" :class="['font-bold text-gray-900 transition-all', logoSize]">
            Zambezi Meats
          </span>
        </transition>
      </router-link>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4">
      <div class="px-2 space-y-1">
        <!-- Menu items will be inserted here via slot -->
        <slot name="menu" :isCollapsed="isCollapsed"></slot>
      </div>
    </nav>

    <!-- Collapse Button -->
    <div class="absolute bottom-4 left-0 right-0 px-2">
      <button
        @click="toggleSidebar"
        class="w-full flex items-center justify-center px-3 py-2 rounded-lg bg-gray-100 hover:bg-[#CF0D0F] hover:text-white transition-all duration-200 group"
        :title="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
      >
        <ChevronLeft v-if="!isCollapsed" class="w-5 h-5" />
        <ChevronRight v-if="isCollapsed" class="w-5 h-5" />
        <transition name="fade">
          <span v-if="!isCollapsed" class="ml-2 text-sm font-medium">
            Collapse
          </span>
        </transition>
      </button>
    </div>
  </aside>
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
