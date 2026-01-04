<script setup>
/**
 * Admin Products Page
 * Full product catalog management for administrators
 *
 * @requirement ADMIN-003 Manage products (CRUD)
 * @requirement ADMIN-004 Product image upload
 * @requirement ADMIN-005 Stock management
 */
import { ref, computed, onMounted, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useAdminProductsStore } from '@/stores/adminProducts'
import { useAdminCategoriesStore } from '@/stores/adminCategories'
import {
  Package,
  Search,
  Plus,
  RefreshCw,
  ChevronLeft,
  ChevronRight,
  Eye,
  Edit,
  Trash2,
  X,
  Image,
  AlertTriangle,
  Download,
  Upload,
  DollarSign,
  Tag,
  Layers,
  BarChart3
} from 'lucide-vue-next'

const productsStore = useAdminProductsStore()
const categoriesStore = useAdminCategoriesStore()

// Local state
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStatus = ref('')
const showProductModal = ref(false)
const showDeleteModal = ref(false)
const isEditing = ref(false)
const productToDelete = ref(null)
const isSubmitting = ref(false)

// Product form data
const productForm = ref({
  name: '',
  slug: '',
  sku: '',
  description: '',
  price: 0,
  sale_price: null,
  cost_price: 0,
  category_id: '',
  stock_quantity: 0,
  low_stock_threshold: 10,
  unit: 'kg',
  weight: null,
  is_active: true,
  is_featured: false,
  image: null
})

// Unit options
const unitOptions = [
  { value: 'kg', label: 'Kilogram (kg)' },
  { value: 'g', label: 'Gram (g)' },
  { value: 'lb', label: 'Pound (lb)' },
  { value: 'piece', label: 'Piece' },
  { value: 'pack', label: 'Pack' }
]

// Status options
const statusOptions = [
  { value: '', label: 'All Status' },
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'low_stock', label: 'Low Stock' }
]

// Computed
const products = computed(() => productsStore.products)
const categories = computed(() => categoriesStore.categories)
const isLoading = computed(() => productsStore.isLoading)
const pagination = computed(() => productsStore.pagination)
const currentProduct = computed(() => productsStore.currentProduct)

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchProducts(),
    categoriesStore.fetchCategories()
  ])
})

// Watch for filter changes
watch([searchQuery, selectedCategory, selectedStatus], () => {
  productsStore.setFilters({
    search: searchQuery.value,
    category_id: selectedCategory.value,
    status: selectedStatus.value
  })
  fetchProducts()
}, { debounce: 300 })

// Methods
async function fetchProducts() {
  try {
    await productsStore.fetchProducts()
  } catch (err) {
    console.error('Failed to fetch products:', err)
  }
}

function openCreateModal() {
  isEditing.value = false
  resetForm()
  showProductModal.value = true
}

function openEditModal(product) {
  isEditing.value = true
  productForm.value = {
    id: product.id,
    name: product.name || '',
    slug: product.slug || '',
    sku: product.sku || '',
    description: product.description || '',
    price: product.price || 0,
    sale_price: product.sale_price || null,
    cost_price: product.cost_price || 0,
    category_id: product.category_id || '',
    stock_quantity: product.stock_quantity || 0,
    low_stock_threshold: product.low_stock_threshold || 10,
    unit: product.unit || 'kg',
    weight: product.weight || null,
    is_active: product.is_active !== false,
    is_featured: product.is_featured || false,
    image: null
  }
  showProductModal.value = true
}

function closeProductModal() {
  showProductModal.value = false
  resetForm()
}

function resetForm() {
  productForm.value = {
    name: '',
    slug: '',
    sku: '',
    description: '',
    price: 0,
    sale_price: null,
    cost_price: 0,
    category_id: '',
    stock_quantity: 0,
    low_stock_threshold: 10,
    unit: 'kg',
    weight: null,
    is_active: true,
    is_featured: false,
    image: null
  }
}

function generateSlug() {
  productForm.value.slug = productForm.value.name
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '')
}

function handleImageUpload(event) {
  const file = event.target.files[0]
  if (file) {
    productForm.value.image = file
  }
}

async function saveProduct() {
  isSubmitting.value = true
  try {
    const formData = new FormData()

    // Add all form fields to FormData
    Object.keys(productForm.value).forEach(key => {
      const value = productForm.value[key]

      // Skip null/undefined values and images array (handle separately)
      if (value === null || value === undefined || key === 'images' || key === 'image') {
        return
      }

      // Convert category_id to integer
      if (key === 'category_id') {
        formData.append(key, parseInt(value, 10))
        return
      }

      // Convert boolean values
      if (key === 'is_active' || key === 'is_featured') {
        formData.append(key, value ? '1' : '0')
        return
      }

      // Convert numeric values
      if (key === 'price_aud' || key === 'sale_price_aud' || key === 'stock' || key === 'weight_kg') {
        formData.append(key, value.toString())
        return
      }

      formData.append(key, value)
    })

    // Handle image uploads
    if (productForm.value.images && productForm.value.images.length > 0) {
      productForm.value.images.forEach((image, index) => {
        formData.append(`images[${index}]`, image)
      })
    } else if (productForm.value.image) {
      formData.append('images[0]', productForm.value.image)
    }

    if (isEditing.value) {
      await productsStore.updateProduct(productForm.value.id, formData)
    } else {
      await productsStore.createProduct(formData)
    }

    closeProductModal()
    await fetchProducts()
  } catch (err) {
    console.error('Failed to save product:', err)
  } finally {
    isSubmitting.value = false
  }
}

function confirmDelete(product) {
  productToDelete.value = product
  showDeleteModal.value = true
}

async function deleteProduct() {
  if (!productToDelete.value) return

  isSubmitting.value = true
  try {
    await productsStore.deleteProduct(productToDelete.value.id)
    showDeleteModal.value = false
    productToDelete.value = null
    await fetchProducts()
  } catch (err) {
    console.error('Failed to delete product:', err)
  } finally {
    isSubmitting.value = false
  }
}

function getCategoryName(categoryId) {
  const category = categories.value.find(c => c.id === categoryId)
  return category?.name || '-'
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0)
}

function goToPage(page) {
  productsStore.setPage(page)
  fetchProducts()
}

async function exportProducts() {
  try {
    await productsStore.exportProducts()
  } catch (err) {
    console.error('Failed to export products:', err)
  }
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
          <span class="text-gray-900">Products</span>
        </nav>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Product Management</h1>
            <p class="text-gray-600 mt-1">Manage your product catalog</p>
          </div>
          <div class="flex items-center gap-3 mt-4 md:mt-0">
            <button @click="exportProducts"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
              <Download class="w-4 h-4 mr-2" />
              Export
            </button>
            <button @click="openCreateModal"
              class="inline-flex items-center px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] transition-colors">
              <Plus class="w-4 h-4 mr-2" />
              Add Product
            </button>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
          <div class="flex-1 min-w-[200px] relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input v-model="searchQuery" type="text" placeholder="Search products by name or SKU..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]" />
          </div>
          <select v-model="selectedCategory"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <select v-model="selectedStatus"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          <button @click="fetchProducts"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            :disabled="isLoading">
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-16">
        <div class="text-center">
          <div class="inline-block h-12 w-12 animate-spin rounded-full border-4 border-[#CF0D0F] border-t-transparent">
          </div>
          <p class="mt-4 text-gray-600">Loading products...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="productsStore.error" class="bg-red-50 border-2 border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
          <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
          <div>
            <h3 class="text-red-800 font-medium">Error loading products</h3>
            <p class="text-red-600 text-sm mt-1">{{ productsStore.error }}</p>
          </div>
        </div>
        <button @click="fetchProducts" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
          Try Again
        </button>
      </div>

      <!-- Products Grid -->
      <div v-else>
        <div v-if="products.length === 0"
          class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] p-16 text-center">
          <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <Package class="w-8 h-8 text-gray-400" />
          </div>
          <p class="text-gray-500 mb-2">No products found</p>
          <p class="text-sm text-gray-400 mb-4">Get started by adding your first product</p>
          <button @click="openCreateModal" class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D]">
            <Plus class="w-4 h-4 inline mr-2" />
            Add Product
          </button>
        </div>

        <div v-else class="bg-white rounded-lg shadow-sm border-2 border-[#CF0D0F] overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-12 w-12">
                        <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
                          class="h-12 w-12 rounded-lg object-cover" />
                        <div v-else class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                          <Package class="w-6 h-6 text-gray-400" />
                        </div>
                      </div>
                      <div class="ml-4">
                        <p class="font-medium text-gray-900">{{ product.name }}</p>
                        <p class="text-sm text-gray-500">SKU: {{ product.sku || '-' }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-gray-900">{{ getCategoryName(product.category_id) }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <p class="font-medium text-gray-900">{{ formatCurrency(product.price) }}</p>
                      <p v-if="product.sale_price" class="text-sm text-green-600">
                        Sale: {{ formatCurrency(product.sale_price) }}
                      </p>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'font-medium',
                      product.stock_quantity <= product.low_stock_threshold ? 'text-red-600' : 'text-gray-900'
                    ]">
                      {{ product.stock_quantity || 0 }} {{ product.unit || 'units' }}
                    </span>
                    <p v-if="product.stock_quantity <= product.low_stock_threshold" class="text-xs text-red-500">
                      Low Stock
                    </p>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                      product.is_active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-gray-100 text-gray-800'
                    ]">
                      {{ product.is_active ? 'Active' : 'Inactive' }}
                    </span>
                    <span v-if="product.is_featured"
                      class="ml-2 inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                      Featured
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="flex items-center justify-end space-x-2">
                      <button @click="openEditModal(product)"
                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        <Edit class="w-4 h-4" />
                      </button>
                      <button @click="confirmDelete(product)"
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.lastPage > 1"
            class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-500">
              Showing {{ ((pagination.currentPage - 1) * pagination.perPage) + 1 }} to {{
                Math.min(pagination.currentPage * pagination.perPage, pagination.total) }} of {{ pagination.total }}
              products
            </p>
            <div class="flex items-center space-x-2">
              <button @click="goToPage(pagination.currentPage - 1)" :disabled="pagination.currentPage === 1"
                class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                <ChevronLeft class="w-4 h-4" />
              </button>
              <span class="px-3 py-1 text-sm text-gray-700">
                Page {{ pagination.currentPage }} of {{ pagination.lastPage }}
              </span>
              <button @click="goToPage(pagination.currentPage + 1)"
                :disabled="pagination.currentPage === pagination.lastPage"
                class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                <ChevronRight class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Product Modal -->
    <Teleport to="body">
      <div v-if="showProductModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeProductModal"></div>

          <div
            class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full">
            <!-- Modal Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                  {{ isEditing ? 'Edit Product' : 'Add New Product' }}
                </h3>
                <button @click="closeProductModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                  <X class="w-5 h-5" />
                </button>
              </div>
            </div>

            <!-- Modal Content -->
            <form @submit.prevent="saveProduct" class="px-6 py-4 max-h-[70vh] overflow-y-auto">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Name -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                  <input v-model="productForm.name" @blur="!isEditing && generateSlug()" type="text" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]" />
                </div>

                <!-- SKU -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                  <input v-model="productForm.sku" type="text"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]" />
                </div>

                <!-- Category -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                  <select v-model="productForm.category_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
                    <option value="">Select Category</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                </div>

                <!-- Price -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                  <div class="relative">
                    <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input v-model.number="productForm.price" type="number" step="0.01" min="0" required
                      class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]" />
                  </div>
                </div>

                <!-- Sale Price -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Sale Price</label>
                  <div class="relative">
                    <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input v-model.number="productForm.sale_price" type="number" step="0.01" min="0"
                      class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]" />
                  </div>
                </div>

                <!-- Stock Quantity -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                  <input v-model.number="productForm.stock_quantity" type="number" min="0"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]" />
                </div>

                <!-- Unit -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                  <select v-model="productForm.unit"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]">
                    <option v-for="unit in unitOptions" :key="unit.value" :value="unit.value">
                      {{ unit.label }}
                    </option>
                  </select>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <textarea v-model="productForm.description" rows="3"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#CF0D0F] focus:border-[#CF0D0F]"></textarea>
                </div>

                <!-- Image Upload -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                  <div
                    class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-[#CF0D0F] transition-colors">
                    <input type="file" accept="image/*" @change="handleImageUpload" class="hidden" id="product-image" />
                    <label for="product-image" class="cursor-pointer">
                      <Upload class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                      <p class="text-sm text-gray-600">Click to upload product image</p>
                      <p class="text-xs text-gray-400 mt-1">PNG, JPG up to 2MB</p>
                    </label>
                    <p v-if="productForm.image" class="text-sm text-green-600 mt-2">
                      Selected: {{ productForm.image.name }}
                    </p>
                  </div>
                </div>

                <!-- Toggles -->
                <div class="md:col-span-2 flex items-center space-x-6">
                  <label class="flex items-center">
                    <input v-model="productForm.is_active" type="checkbox"
                      class="w-4 h-4 text-[#CF0D0F] border-gray-300 rounded focus:ring-[#CF0D0F]" />
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                  </label>
                  <label class="flex items-center">
                    <input v-model="productForm.is_featured" type="checkbox"
                      class="w-4 h-4 text-[#CF0D0F] border-gray-300 rounded focus:ring-[#CF0D0F]" />
                    <span class="ml-2 text-sm text-gray-700">Featured</span>
                  </label>
                </div>
              </div>
            </form>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
              <button @click="closeProductModal"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button @click="saveProduct" :disabled="isSubmitting"
                class="px-4 py-2 bg-[#CF0D0F] text-white rounded-lg hover:bg-[#B00B0D] disabled:opacity-50">
                {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update Product' : 'Add Product') }}
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

          <div
            class="relative inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full">
            <div class="px-6 py-4">
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                  <AlertTriangle class="w-6 h-6 text-red-600" />
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-semibold text-gray-900">Delete Product</h3>
                  <p class="text-sm text-gray-500 mt-1">
                    Are you sure you want to delete "{{ productToDelete?.name }}"? This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
              <button @click="showDeleteModal = false"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100">
                Cancel
              </button>
              <button @click="deleteProduct" :disabled="isSubmitting"
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
