<script setup>
/**
 * Product Image Upload Component
 * Multi-image upload with drag-drop, preview, and reordering
 *
 * @requirement PROD-007 Product image upload
 * @requirement PROD-013 Multi-image upload with drag-drop
 * @requirement PROD-011 Set primary image
 * @requirement PROD-012 Drag-drop image reordering
 */
import { ref, computed } from 'vue'
import { Upload, X, Star, Image as ImageIcon, AlertCircle } from 'lucide-vue-next'
import apiClient from '@/services/apiClient'

const props = defineProps({
  productId: {
    type: Number,
    required: true
  },
  existingImages: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['upload-success', 'delete-success', 'reorder-success'])

// State
const selectedFiles = ref([])
const previews = ref([])
const isUploading = ref(false)
const uploadProgress = ref(0)
const error = ref(null)
const dragOver = ref(false)
const images = ref([...props.existingImages])

// Constants
const MAX_FILES = 5
const MAX_SIZE = 2 * 1024 * 1024 // 2MB
const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']

// Computed
const canUploadMore = computed(() => {
  return (images.value.length + selectedFiles.value.length) < MAX_FILES
})

const remainingSlots = computed(() => {
  return MAX_FILES - images.value.length
})

// Methods
function handleFileSelect(event) {
  const files = Array.from(event.target.files)
  addFiles(files)
}

function handleDrop(event) {
  event.preventDefault()
  dragOver.value = false

  const files = Array.from(event.dataTransfer.files)
  addFiles(files)
}

function handleDragOver(event) {
  event.preventDefault()
  dragOver.value = true
}

function handleDragLeave() {
  dragOver.value = false
}

function addFiles(files) {
  error.value = null

  // Filter valid image files
  const validFiles = files.filter(file => {
    if (!ALLOWED_TYPES.includes(file.type)) {
      error.value = 'Only JPEG, PNG, and WEBP images are allowed'
      return false
    }
    if (file.size > MAX_SIZE) {
      error.value = `File ${file.name} exceeds 2MB limit`
      return false
    }
    return true
  })

  // Check if adding these files would exceed the limit
  const totalFiles = images.value.length + selectedFiles.value.length + validFiles.length
  if (totalFiles > MAX_FILES) {
    error.value = `Maximum ${MAX_FILES} images allowed. You can upload ${remainingSlots.value} more image(s)`
    return
  }

  // Add files and create previews
  selectedFiles.value.push(...validFiles)

  validFiles.forEach(file => {
    const reader = new FileReader()
    reader.onload = (e) => {
      previews.value.push({
        file,
        url: e.target.result,
        name: file.name
      })
    }
    reader.readAsDataURL(file)
  })
}

function removeSelectedFile(index) {
  selectedFiles.value.splice(index, 1)
  previews.value.splice(index, 1)
  error.value = null
}

async function uploadImages() {
  if (selectedFiles.value.length === 0) {
    error.value = 'Please select at least one image'
    return
  }

  isUploading.value = true
  error.value = null
  uploadProgress.value = 0

  const formData = new FormData()
  selectedFiles.value.forEach((file, index) => {
    formData.append(`images[${index}]`, file)
  })

  try {
    const response = await apiClient.post(
      `/api/v1/admin/products/${props.productId}/images`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        }
      }
    )

    if (response.data.success) {
      // Update images list
      images.value = response.data.images

      // Clear selected files and previews
      selectedFiles.value = []
      previews.value = []

      emit('upload-success', response.data.images)
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to upload images'
  } finally {
    isUploading.value = false
    uploadProgress.value = 0
  }
}

async function deleteImage(imageId) {
  if (!confirm('Are you sure you want to delete this image?')) {
    return
  }

  try {
    const response = await apiClient.delete(
      `/api/v1/admin/products/${props.productId}/images/${imageId}`
    )

    if (response.data.success) {
      images.value = images.value.filter(img => img.id !== imageId)
      emit('delete-success', imageId)
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete image'
  }
}

async function setPrimaryImage(imageId) {
  // Reorder images to put selected one first
  const imageIds = images.value.map(img => img.id)
  const currentIndex = imageIds.indexOf(imageId)

  if (currentIndex > 0) {
    imageIds.splice(currentIndex, 1)
    imageIds.unshift(imageId)

    await reorderImages(imageIds)
  }
}

async function reorderImages(imageIds) {
  try {
    const response = await apiClient.post(
      `/api/v1/admin/products/${props.productId}/images/reorder`,
      { image_ids: imageIds }
    )

    if (response.data.success) {
      images.value = response.data.images
      emit('reorder-success', response.data.images)
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to reorder images'
  }
}
</script>

<template>
  <div class="space-y-4">
    <!-- Existing Images -->
    <div v-if="images.length > 0" class="space-y-2">
      <h4 class="text-sm font-medium text-gray-700">Current Images ({{ images.length }}/{{ MAX_FILES }})</h4>
      <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div v-for="image in images" :key="image.id"
          class="relative group aspect-square rounded-lg overflow-hidden border-2"
          :class="image.is_primary ? 'border-yellow-400' : 'border-gray-200'">
          <img :src="image.image_path" :alt="image.alt_text" class="w-full h-full object-cover" />

          <!-- Primary Badge -->
          <div v-if="image.is_primary"
            class="absolute top-1 left-1 bg-yellow-400 text-white px-2 py-1 rounded text-xs font-medium flex items-center gap-1">
            <Star :size="12" class="fill-current" />
            Primary
          </div>

          <!-- Actions Overlay -->
          <div
            class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
            <button v-if="!image.is_primary" @click="setPrimaryImage(image.id)"
              class="bg-white text-gray-700 p-2 rounded-lg hover:bg-yellow-50 transition-colors" title="Set as primary">
              <Star :size="16" />
            </button>
            <button @click="deleteImage(image.id)"
              class="bg-white text-red-600 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Delete image">
              <X :size="16" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Upload Area -->
    <div v-if="canUploadMore" class="space-y-2">
      <h4 class="text-sm font-medium text-gray-700">
        Upload Images
        <span class="text-gray-500 font-normal">({{ remainingSlots }} remaining)</span>
      </h4>

      <!-- Drag & Drop Zone -->
      <div @drop="handleDrop" @dragover="handleDragOver" @dragleave="handleDragLeave"
        class="border-2 border-dashed rounded-lg p-6 text-center transition-colors"
        :class="dragOver ? 'border-[#CF0D0F] bg-red-50' : 'border-gray-300 hover:border-gray-400'">
        <input type="file" accept="image/jpeg,image/jpg,image/png,image/webp" multiple :max="remainingSlots"
          @change="handleFileSelect" class="hidden" id="image-upload" :disabled="!canUploadMore" />
        <label for="image-upload" class="cursor-pointer block">
          <Upload class="w-12 h-12 text-gray-400 mx-auto mb-3" />
          <p class="text-sm font-medium text-gray-700 mb-1">
            Drop images here or click to browse
          </p>
          <p class="text-xs text-gray-500">
            JPEG, PNG, or WEBP • Max {{ MAX_FILES }} images • Max 2MB each
          </p>
        </label>
      </div>

      <!-- Selected Files Preview -->
      <div v-if="previews.length > 0" class="space-y-2">
        <h4 class="text-sm font-medium text-gray-700">Selected Images ({{ previews.length }})</h4>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
          <div v-for="(preview, index) in previews" :key="index"
            class="relative group aspect-square rounded-lg overflow-hidden border-2 border-blue-200">
            <img :src="preview.url" :alt="preview.name" class="w-full h-full object-cover" />

            <!-- Remove Button -->
            <button @click="removeSelectedFile(index)"
              class="absolute top-1 right-1 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
              title="Remove">
              <X :size="14" />
            </button>

            <!-- File Name -->
            <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 truncate">
              {{ preview.name }}
            </div>
          </div>
        </div>

        <!-- Upload Button -->
        <button @click="uploadImages" :disabled="isUploading || selectedFiles.length === 0"
          class="w-full bg-[#CF0D0F] text-white px-4 py-2 rounded-lg hover:bg-[#A00A0C] disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2">
          <Upload v-if="!isUploading" :size="18" />
          <span v-if="isUploading">Uploading... {{ uploadProgress }}%</span>
          <span v-else>Upload {{ selectedFiles.length }} Image(s)</span>
        </button>
      </div>
    </div>

    <!-- Max Images Reached -->
    <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
      <AlertCircle class="w-8 h-8 text-yellow-600 mx-auto mb-2" />
      <p class="text-sm text-yellow-800 font-medium">
        Maximum {{ MAX_FILES }} images reached
      </p>
      <p class="text-xs text-yellow-700 mt-1">
        Delete an existing image to upload a new one
      </p>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3 flex items-start gap-2">
      <AlertCircle class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" />
      <p class="text-sm text-red-800">{{ error }}</p>
    </div>

    <!-- Upload Progress -->
    <div v-if="isUploading" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm text-blue-800 font-medium">Uploading images...</span>
        <span class="text-sm text-blue-600">{{ uploadProgress }}%</span>
      </div>
      <div class="w-full bg-blue-200 rounded-full h-2 overflow-hidden">
        <div class="bg-blue-600 h-full transition-all duration-300 rounded-full"
          :style="{ width: uploadProgress + '%' }"></div>
      </div>
    </div>
  </div>
</template>
