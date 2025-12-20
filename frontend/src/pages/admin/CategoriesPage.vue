<script setup>
/**
 * Admin Categories Page
 * Category management for organizing products
 *
 * @requirement ADMIN-006 Manage categories (CRUD)
 * @requirement ADMIN-007 Category hierarchy
 */
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAdminCategoriesStore } from '@/stores/adminCategories'
import {
  FolderOpen,
  Plus,
  RefreshCw,
  Edit,
  Trash2,
  X,
  AlertTriangle,
  Image,
  Upload,
  ChevronRight,
  Layers
} from 'lucide-vue-next'

const categoriesStore = useAdminCategoriesStore()

// Local state
const showCategoryModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const categoryToDelete = ref(null)
const isSubmitting = ref(false)

// Category form data
const categoryForm = ref({
  name: '',
  slug: '',
  description: '',
  parent_id: null,
  sort_order: 0,
  is_active: true,
  image: null
})

// Computed
const categories = computed(() => categoriesStore.categories)
const isLoading = computed(() => categoriesStore.isLoading)

// Build category tree for display
const categoryTree = computed(() => {
  const tree = []
  const categoryMap = {}

  // First pass: create map
  categories.value.forEach(cat => {
    categoryMap[cat.id] = { ...cat, children: [] }
  })

  // Second pass: build tree
  categories.value.forEach(cat => {
    if (cat.parent_id && categoryMap[cat.parent_id]) {
      categoryMap[cat.parent_id].children.push(categoryMap[cat.id])
    } else {
      tree.push(categoryMap[cat.id])
    }
  })

  return tree
})

// Parent category options (exclude current when editing)
const parentOptions = computed(() => {
  if (isEditing.value) {
    return categories.value.filter(c => c.id !== categoryForm.value.id && !c.parent_id)
  }
  return categories.value.filter(c => !c.parent_id)
})

// Lifecycle
onMounted(async () => {
  await fetchCategories()
})

// Methods
async function fetchCategories() {
  try {
    await categoriesStore.fetchCategories()
  } catch (err) {
    console.error('Failed to fetch categories:', err)
  }
}

function openCreateModal(parentId = null) {
  isEditing.value = false
  resetForm()
  categoryForm.value.parent_id = parentId
  showCategoryModal.value = true
}

function openEditModal(category) {
  isEditing.value = true
  categoryForm.value = {
    id: category.id,
    name: category.name || '',
    slug: category.slug || '',
    description: category.description || '',
    parent_id: category.parent_id || null,
    sort_order: category.sort_order || 0,
    is_active: category.is_active !== false,
    image: null
  }
  showCategoryModal.value = true
}

function closeCategoryModal() {
  showCategoryModal.value = false
  resetForm()
}

function resetForm() {
  categoryForm.value = {
    name: '',
    slug: '',
    description: '',
    parent_id: null,
    sort_order: 0,
    is_active: true,
    image: null
  }
}

function generateSlug() {
  categoryForm.value.slug = categoryForm.value.name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '')
}

function handleImageUpload(event) {
  const file = event.target.files[0]
  if (file) {
    categoryForm.value.image = file
  }
}

async function saveCategory() {
  isSubmitting.value = true
  try {
    if (isEditing.value) {
      await categoriesStore.updateCategory(categoryForm.value.id, categoryForm.value)
    } else {
      await categoriesStore.createCategory(categoryForm.value)
    }
    
    closeCategoryModal()
    await fetchCategories()
  } catch (err) {
    console.error('Failed to save category:', err)
  } finally {
    isSubmitting.value = false
  }
}

function confirmDelete(category) {
  categoryToDelete.value = category
  showDeleteModal.value = true
}

async function deleteCategory() {
  if (!categoryToDelete.value) return
  
  isSubmitting.value = true
  try {
    await categoriesStore.deleteCategory(categoryToDelete.value.id)
    showDeleteModal.value = false
    categoryToDelete.value = null
    await fetchCategories()
  } catch (err) {
    console.error('Failed to delete category:', err)
  } finally {
    isSubmitting.value = false
  }
}

function getProductCount(category) {
  return category.products_count || 0
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <nav class="text-sm mb-4">
          <RouterLink to="/admin" class="text-gray-500 hover:text-[#CF0D0F]">Dashboard</RouterLink>
          <span class="text-gray-400 mx-2">/</span>
          <span class="text-gray-900">Categories</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Category Management</h1>
            <p class="text-gray-600 mt-1">Organize your products into categories</p>
          </div>
          <div class="flex items-center gap-3 mt-4 md:mt-0">
            <button @click="fetchCategories" 
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
              :disabled="isLoading">
              <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': isLoading }" />
              Refresh
            </button>
            <button @click="openCreateModal()" 
              class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors">
              <Plus class="w-4 h-4 mr-2" />
              Add Category
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-16">
        <div class="text-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent"></div>
          <p class="mt-4 text-gray-600">Loading categories...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="categoriesStore.error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
          <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading categories</h3>
            <p class="text-red-600 text-sm mt-1">{{ categoriesStore.error }}</p>
          </div>
        </div>
        <button @click="fetchCategories" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
          Try Again
        </button>
      </div>

      <!-- Categories List -->
      <div v-else>
        <div v-if="categories.length === 0" class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-16 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <FolderOpen class="w-8 h-8 text-gray-400" />
          </div>
          <p class="text-gray-500 mb-2">No categories found</p>
          <p class="text-sm text-gray-400 mb-4">Get started by creating your first category</p>
          <button @click="openCreateModal()" class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D]">
            <Plus class="w-4 h-4 inline mr-2" />
            Add Category
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Category Card -->
          <div 
            v-for="category in categoryTree" 
            :key="category.id"
            class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] overflow-hidden hover:shadow-md transition-shadow"
          >
            <!-- Category Header -->
            <div class="p-4 bg-gradient-to-r from-[#CF0D0F] to-[#F6211F]">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <FolderOpen class="w-5 h-5 text-white" />
                  </div>
                  <div class="ml-3">
                    <h3 class="text-lg font-bold text-white">{{ category.name }}</h3>
                    <p class="text-sm text-white text-opacity-80">{{ getProductCount(category) }} products</p>
                  </div>
                </div>
                <span :class="[
                  'px-2 py-1 text-xs font-medium rounded-full',
                  category.is_active 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-gray-100 text-gray-800'
                ]">
                  {{ category.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>

            <!-- Category Body -->
            <div class="p-4">
              <p v-if="category.description" class="text-sm text-gray-600 mb-4">
                {{ category.description }}
              </p>
              <p v-else class="text-sm text-gray-400 italic mb-4">No description</p>

              <!-- Subcategories -->
              <div v-if="category.children && category.children.length > 0" class="mb-4">
                <h4 class="text-xs font-medium text-gray-500 uppercase mb-2">Subcategories</h4>
                <div class="space-y-2">
                  <div 
                    v-for="child in category.children" 
                    :key="child.id"
                    class="flex items-center justify-between p-2 bg-gray-50 rounded-lg"
                  >
                    <div class="flex items-center">
                      <ChevronRight class="w-4 h-4 text-gray-400" />
                      <span class="text-sm text-gray-700 ml-1">{{ child.name }}</span>
                      <span class="text-xs text-gray-400 ml-2">({{ getProductCount(child) }})</span>
                    </div>
                    <div class="flex items-center space-x-1">
                      <button 
                        @click="openEditModal(child)"
                        class="p-1 text-blue-600 hover:bg-blue-50 rounded">
                        <Edit class="w-3.5 h-3.5" />
                      </button>
                      <button 
                        @click="confirmDelete(child)"
                        class="p-1 text-red-600 hover:bg-red-50 rounded">
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <button 
                  @click="openCreateModal(category.id)"
                  class="text-sm text-[#CF0D0F] hover:underline">
                  + Add Subcategory
                </button>
                <div class="flex items-center space-x-2">
                  <button 
                    @click="openEditModal(category)"
                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                    <Edit class="w-4 h-4" />
                  </button>
                  <button 
                    @click="confirmDelete(category)"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Category Modal -->
    <Teleport to="body">
      <div v-if="showCategoryModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeCategoryModal"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                  {{ isEditing ? 'Edit Category' : 'Add New Category' }}
                </h3>
                <button @click="closeCategoryModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                  <X class="w-5 h-5" />
                </button>
              </div>
            </div>

            <!-- Modal Content -->
            <form @submit.prevent="saveCategory" class="px-6 py-4">
              <div class="space-y-4">
                <!-- Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                  <input 
                    v-model="categoryForm.name"
                    @blur="!isEditing && generateSlug()"
                    type="text" 
                    required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  />
                </div>

                <!-- Slug -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                  <input 
                    v-model="categoryForm.slug"
                    type="text"
                    placeholder="auto-generated-from-name"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  />
                </div>

                <!-- Parent Category -->
                <div v-if="parentOptions.length > 0 || categoryForm.parent_id">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                  <select 
                    v-model="categoryForm.parent_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
                    <option :value="null">None (Top Level)</option>
                    <option v-for="parent in parentOptions" :key="parent.id" :value="parent.id">
                      {{ parent.name }}
                    </option>
                  </select>
                </div>

                <!-- Description -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <textarea 
                    v-model="categoryForm.description"
                    rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  ></textarea>
                </div>

                <!-- Sort Order -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                  <input 
                    v-model.number="categoryForm.sort_order"
                    type="number"
                    min="0"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"
                  />
                  <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                </div>

                <!-- Image Upload -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Category Image</label>
                  <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-[#CF0D0F] transition-colors">
                    <input 
                      type="file" 
                      accept="image/*"
                      @change="handleImageUpload"
                      class="hidden"
                      id="category-image"
                    />
                    <label for="category-image" class="cursor-pointer">
                      <Upload class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                      <p class="text-sm text-gray-600">Click to upload</p>
                    </label>
                    <p v-if="categoryForm.image" class="text-sm text-green-600 mt-2">
                      Selected: {{ categoryForm.image.name }}
                    </p>
                  </div>
                </div>

                <!-- Active Toggle -->
                <div>
                  <label class="flex items-center">
                    <input 
                      v-model="categoryForm.is_active"
                      type="checkbox"
                      class="w-4 h-4 text-[#CF0D0F] border-gray-300 rounded focus:ring-[#CF0D0F]"
                    />
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                  </label>
                </div>
              </div>
            </form>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button @click="closeCategoryModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="saveCategory"
                :disabled="isSubmitting || !categoryForm.name.trim()"
                class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50">
                {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update Category' : 'Add Category') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showDeleteModal" class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
          
          <div class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full">
            <div class="px-6 py-4">
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                  <AlertTriangle class="w-6 h-6 text-red-600" />
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-semibold text-gray-900">Delete Category</h3>
                  <p class="text-sm text-gray-500 mt-1">
                    Are you sure you want to delete "{{ categoryToDelete?.name }}"? 
                    <span v-if="categoryToDelete?.children?.length" class="text-red-600 font-medium">
                      This will also delete {{ categoryToDelete.children.length }} subcategories.
                    </span>
                  </p>
                </div>
              </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
              <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button 
                @click="deleteCategory"
                :disabled="isSubmitting"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                {{ isSubmitting ? 'Deleting...' : 'Delete' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
