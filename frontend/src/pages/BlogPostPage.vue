<script setup>
/**
 * BlogPostPage.vue
 * Single blog post view with SEO meta tags
 */
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { Calendar, Eye, User, ArrowLeft, Share2, Facebook, Twitter, Linkedin } from 'lucide-vue-next'

const route = useRoute()
const post = ref(null)
const relatedPosts = ref([])
const loading = ref(true)
const error = ref(null)

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'

// Fetch blog post
async function fetchPost() {
  try {
    loading.value = true
    error.value = null
    
    const response = await fetch(`${API_BASE_URL}/blog/${route.params.slug}`)
    const data = await response.json()

    if (data.success) {
      post.value = data.data
      relatedPosts.value = data.related_posts || []
      
      // Update meta tags for SEO
      updateMetaTags()
    } else {
      error.value = 'Article not found'
    }
  } catch (err) {
    console.error('Error fetching blog post:', err)
    error.value = 'Failed to load article'
  } finally {
    loading.value = false
  }
}

// Update meta tags for SEO
function updateMetaTags() {
  if (!post.value) return

  // Update title
  document.title = post.value.meta_title || `${post.value.title} | Zambezi Meats Blog`

  // Update or create meta description
  updateOrCreateMeta('description', post.value.meta_description || post.value.excerpt)
  
  // Update or create meta keywords
  if (post.value.meta_keywords && post.value.meta_keywords.length > 0) {
    updateOrCreateMeta('keywords', post.value.meta_keywords.join(', '))
  }

  // Open Graph tags
  updateOrCreateMeta('og:title', post.value.title, 'property')
  updateOrCreateMeta('og:description', post.value.excerpt, 'property')
  updateOrCreateMeta('og:type', 'article', 'property')
  if (post.value.featured_image_url) {
    updateOrCreateMeta('og:image', post.value.featured_image_url, 'property')
  }

  // Twitter Card tags
  updateOrCreateMeta('twitter:card', 'summary_large_image', 'name')
  updateOrCreateMeta('twitter:title', post.value.title, 'name')
  updateOrCreateMeta('twitter:description', post.value.excerpt, 'name')
}

function updateOrCreateMeta(name, content, attr = 'name') {
  let element = document.querySelector(`meta[${attr}="${name}"]`)
  if (element) {
    element.setAttribute('content', content)
  } else {
    element = document.createElement('meta')
    element.setAttribute(attr, name)
    element.setAttribute('content', content)
    document.head.appendChild(element)
  }
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

// Share functions
function shareOnFacebook() {
  const url = encodeURIComponent(window.location.href)
  window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400')
}

function shareOnTwitter() {
  const url = encodeURIComponent(window.location.href)
  const text = encodeURIComponent(post.value.title)
  window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank', 'width=600,height=400')
}

function shareOnLinkedIn() {
  const url = encodeURIComponent(window.location.href)
  window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${url}`, '_blank', 'width=600,height=400')
}

function copyLink() {
  navigator.clipboard.writeText(window.location.href)
  alert('Link copied to clipboard!')
}

// Watch for route changes
watch(() => route.params.slug, () => {
  if (route.params.slug) {
    fetchPost()
  }
})

onMounted(() => {
  fetchPost()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="animate-pulse">
        <div class="h-8 bg-gray-200 rounded w-3/4 mb-4"></div>
        <div class="h-4 bg-gray-200 rounded w-1/2 mb-8"></div>
        <div class="h-96 bg-gray-200 rounded mb-8"></div>
        <div class="space-y-3">
          <div class="h-4 bg-gray-200 rounded"></div>
          <div class="h-4 bg-gray-200 rounded"></div>
          <div class="h-4 bg-gray-200 rounded w-5/6"></div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ error }}</h2>
      <RouterLink to="/blog" class="text-[#CF0D0F] hover:text-[#F6211F] font-medium">
        ← Back to Blog
      </RouterLink>
    </div>

    <!-- Article Content -->
    <article v-else-if="post" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Back Button -->
      <RouterLink
        to="/blog"
        class="inline-flex items-center gap-2 text-gray-600 hover:text-[#CF0D0F] mb-8 transition-colors"
      >
        <ArrowLeft class="w-4 h-4" />
        Back to Blog
      </RouterLink>

      <!-- Article Header -->
      <header class="mb-8">
        <!-- Featured Badge -->
        <div v-if="post.is_featured" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-[#CF0D0F]/10 text-[#CF0D0F] mb-4">
          Featured Article
        </div>

        <!-- Title -->
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
          {{ post.title }}
        </h1>

        <!-- Meta Info -->
        <div class="flex flex-wrap items-center gap-6 text-gray-600 mb-6">
          <span class="flex items-center gap-2">
            <User class="w-4 h-4" />
            {{ post.author?.name || 'Zambezi Meats Team' }}
          </span>
          <span class="flex items-center gap-2">
            <Calendar class="w-4 h-4" />
            {{ formatDate(post.published_at) }}
          </span>
          <span class="flex items-center gap-2">
            <Eye class="w-4 h-4" />
            {{ post.views }} views
          </span>
          <span v-if="post.reading_time" class="flex items-center gap-2">
            📖 {{ post.reading_time }} min read
          </span>
        </div>

        <!-- Share Buttons -->
        <div class="flex items-center gap-3">
          <span class="text-sm font-medium text-gray-600">Share:</span>
          <button
            @click="shareOnFacebook"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
            title="Share on Facebook"
          >
            <Facebook class="w-5 h-5 text-gray-600" />
          </button>
          <button
            @click="shareOnTwitter"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
            title="Share on Twitter"
          >
            <Twitter class="w-5 h-5 text-gray-600" />
          </button>
          <button
            @click="shareOnLinkedIn"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
            title="Share on LinkedIn"
          >
            <Linkedin class="w-5 h-5 text-gray-600" />
          </button>
          <button
            @click="copyLink"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
            title="Copy link"
          >
            <Share2 class="w-5 h-5 text-gray-600" />
          </button>
        </div>
      </header>

      <!-- Featured Image -->
      <div v-if="post.featured_image_url" class="mb-8 rounded-xl overflow-hidden">
        <img
          :src="post.featured_image_url"
          :alt="post.title"
          class="w-full h-auto"
          @error="$event.target.style.display='none'"
        />
      </div>

      <!-- Article Content -->
      <div 
        class="prose prose-lg max-w-none mb-12"
        v-html="post.content"
      ></div>

      <!-- Tags/Keywords -->
      <div v-if="post.meta_keywords && post.meta_keywords.length > 0" class="mb-8">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Tags:</h3>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="keyword in post.meta_keywords"
            :key="keyword"
            class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm"
          >
            {{ keyword }}
          </span>
        </div>
      </div>

      <!-- Divider -->
      <hr class="my-12 border-gray-200" />

      <!-- Related Posts -->
      <div v-if="relatedPosts.length > 0" class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Articles</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <RouterLink
            v-for="related in relatedPosts"
            :key="related.id"
            :to="`/blog/${related.slug}`"
            class="group bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all"
          >
            <!-- Image -->
            <div class="h-40 bg-gray-200 overflow-hidden">
              <img
                v-if="related.featured_image_url"
                :src="related.featured_image_url"
                :alt="related.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                @error="$event.target.src = '/images/blog-placeholder.jpg'"
              />
              <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#CF0D0F] to-[#F6211F]">
                <span class="text-white text-2xl font-bold">ZM</span>
              </div>
            </div>

            <!-- Content -->
            <div class="p-4">
              <h3 class="font-semibold text-gray-900 group-hover:text-[#CF0D0F] transition-colors line-clamp-2 mb-2">
                {{ related.title }}
              </h3>
              <p class="text-sm text-gray-600 line-clamp-2">
                {{ related.excerpt }}
              </p>
            </div>
          </RouterLink>
        </div>
      </div>

      <!-- CTA Section -->
      <div class="mt-12 bg-gradient-to-r from-[#CF0D0F] to-[#F6211F] rounded-xl p-8 text-center text-white">
        <h3 class="text-2xl font-bold mb-4">Ready to Experience Premium Quality?</h3>
        <p class="mb-6 text-white/90">
          Shop our selection of premium Australian meats, delivered fresh to your door.
        </p>
        <RouterLink
          to="/shop"
          class="inline-block px-8 py-3 bg-white text-[#CF0D0F] font-semibold rounded-lg hover:bg-gray-100 transition-colors"
        >
          Shop Now
        </RouterLink>
      </div>
    </article>
  </div>
</template>

<style scoped>
/* Prose styling for article content */
.prose {
  color: #374151;
}

.prose h2 {
  font-size: 1.875rem;
  font-weight: 700;
  margin-top: 2rem;
  margin-bottom: 1rem;
  color: #111827;
}

.prose h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  color: #111827;
}

.prose h4 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 1.25rem;
  margin-bottom: 0.5rem;
  color: #111827;
}

.prose p {
  margin-bottom: 1.25rem;
  line-height: 1.75;
}

.prose ul,
.prose ol {
  margin-bottom: 1.25rem;
  padding-left: 1.5rem;
}

.prose li {
  margin-bottom: 0.5rem;
}

.prose strong {
  font-weight: 600;
  color: #111827;
}

.prose a {
  color: #CF0D0F;
  text-decoration: underline;
}

.prose a:hover {
  color: #F6211F;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
