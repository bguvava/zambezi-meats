<script setup>
/**
 * BlogListPage.vue
 * Lists all published blog posts with search and pagination
 */
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { Search, Calendar, Eye, ArrowRight } from 'lucide-vue-next'

const posts = ref([])
const loading = ref(true)
const searchQuery = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const totalPosts = ref(0)

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'

// Fetch blog posts
async function fetchPosts() {
  try {
    loading.value = true
    const params = new URLSearchParams({
      page: currentPage.value.toString(),
      per_page: '12'
    })

    if (searchQuery.value) {
      params.append('search', searchQuery.value)
    }

    const response = await fetch(`${API_BASE_URL}/blog?${params}`)
    const data = await response.json()

    if (data.success) {
      posts.value = data.data
      totalPages.value = data.pagination.last_page
      totalPosts.value = data.pagination.total
      currentPage.value = data.pagination.current_page
    }
  } catch (error) {
    console.error('Error fetching blog posts:', error)
  } finally {
    loading.value = false
  }
}

// Search handler
function handleSearch() {
  currentPage.value = 1
  fetchPosts()
}

// Pagination
function goToPage(page) {
  currentPage.value = page
  fetchPosts()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Format date
function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-AU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Get excerpt
function getExcerpt(post) {
  return post.excerpt || post.content.replace(/<[^>]*>/g, '').substring(0, 150) + '...'
}

onMounted(() => {
  fetchPosts()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-[#CF0D0F] to-[#F6211F] text-white py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h1 class="text-4xl md:text-5xl font-bold mb-4">Zambezi Meats Blog</h1>
          <p class="text-xl text-white/90 max-w-2xl mx-auto">
            Expert tips, meat guides, and culinary insights from professional butchers
          </p>
        </div>

        <!-- Search Bar -->
        <div class="mt-8 max-w-2xl mx-auto">
          <div class="relative">
            <Search class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              v-model="searchQuery"
              @keyup.enter="handleSearch"
              type="text"
              placeholder="Search articles..."
              class="w-full pl-12 pr-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-white/50"
            />
            <button
              @click="handleSearch"
              class="absolute right-2 top-1/2 transform -translate-y-1/2 px-4 py-1.5 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#F6211F] transition-colors"
            >
              Search
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Blog Posts Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="i in 6" :key="i" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 animate-pulse">
          <div class="h-48 bg-gray-200 rounded-lg mb-4"></div>
          <div class="h-6 bg-gray-200 rounded mb-2"></div>
          <div class="h-4 bg-gray-200 rounded mb-4"></div>
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
        </div>
      </div>

      <!-- Posts Grid -->
      <div v-else-if="posts.length > 0">
        <!-- Results Count -->
        <div class="mb-6 text-gray-600">
          <span class="font-medium text-gray-900">{{ totalPosts }}</span> articles found
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <article
            v-for="post in posts"
            :key="post.id"
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-xl transition-all duration-300 group"
          >
            <!-- Featured Image -->
            <div class="h-48 bg-gray-200 overflow-hidden">
              <img
                v-if="post.featured_image_url"
                :src="post.featured_image_url"
                :alt="post.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                @error="$event.target.src = '/images/blog-placeholder.jpg'"
              />
              <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#CF0D0F] to-[#F6211F]">
                <span class="text-white text-4xl font-bold">ZM</span>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6">
              <!-- Meta Info -->
              <div class="flex items-center gap-4 text-sm text-gray-500 mb-3">
                <span class="flex items-center gap-1">
                  <Calendar class="w-4 h-4" />
                  {{ formatDate(post.published_at) }}
                </span>
                <span class="flex items-center gap-1">
                  <Eye class="w-4 h-4" />
                  {{ post.views }} views
                </span>
              </div>

              <!-- Title -->
              <h2 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-[#CF0D0F] transition-colors line-clamp-2">
                {{ post.title }}
              </h2>

              <!-- Excerpt -->
              <p class="text-gray-600 mb-4 line-clamp-3">
                {{ getExcerpt(post) }}
              </p>

              <!-- Featured Badge -->
              <div v-if="post.is_featured" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#CF0D0F]/10 text-[#CF0D0F] mb-4">
                Featured
              </div>

              <!-- Read More Link -->
              <RouterLink
                :to="`/blog/${post.slug}`"
                class="inline-flex items-center gap-2 text-[#CF0D0F] font-medium hover:gap-3 transition-all group/link"
              >
                Read Article
                <ArrowRight class="w-4 h-4" />
              </RouterLink>
            </div>
          </article>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="mt-12 flex justify-center gap-2">
          <button
            v-for="page in totalPages"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'px-4 py-2 rounded-lg font-medium transition-colors',
              page === currentPage
                ? 'bg-[#CF0D0F] text-white'
                : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </div>

      <!-- No Results -->
      <div v-else class="text-center py-12">
        <div class="text-gray-400 mb-4">
          <Search class="w-16 h-16 mx-auto" />
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">No articles found</h3>
        <p class="text-gray-600 mb-6">Try adjusting your search terms</p>
        <button
          @click="searchQuery = ''; fetchPosts()"
          class="px-6 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#F6211F] transition-colors"
        >
          Clear Search
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
