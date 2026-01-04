# Admin Product Image Upload Feature

This guide explains how to use the multi-image upload feature for products in the admin dashboard.

## Features

- ✅ **Multi-Image Upload**: Upload up to 5 images per product
- ✅ **Drag & Drop**: Drag files directly onto the upload zone
- ✅ **Image Preview**: Preview images before uploading
- ✅ **Primary Image**: Set any image as the primary product image
- ✅ **Delete Images**: Remove unwanted images
- ✅ **Reorder Images**: Change image order (primary is always first)
- ✅ **Progress Tracking**: See upload progress in real-time
- ✅ **Validation**: Automatic file type and size validation

## Supported Formats

- **File Types**: JPEG, JPG, PNG, WEBP
- **Maximum Size**: 2MB per image
- **Maximum Images**: 5 images per product

## Usage Instructions

### 1. Navigate to Product Management

1. Log in as Admin or Staff
2. Go to **Admin Dashboard** → **Products**
3. Click **Edit** on any existing product

### 2. Upload Images

#### Method 1: Click to Browse

1. Click the "Drop images here or click to browse" area
2. Select one or multiple images (up to 5 total)
3. Selected images will appear in the preview grid
4. Click "Upload X Image(s)" button

#### Method 2: Drag & Drop

1. Open your file explorer
2. Select image files
3. Drag them onto the upload zone
4. Drop the files
5. Click "Upload X Image(s)" button

### 3. Manage Existing Images

#### View Current Images

- All uploaded images appear at the top
- Primary image has a yellow border and "Primary" badge
- Images are shown as thumbnails in a grid

#### Set Primary Image

1. Hover over any non-primary image
2. Click the **Star** icon
3. Image becomes the new primary (moved to first position)

#### Delete Image

1. Hover over the image to delete
2. Click the **X** icon
3. Confirm deletion in the popup
4. Image is permanently deleted

### 4. Best Practices

#### Image Quality

- Use high-resolution images (at least 800x800px)
- Ensure good lighting and clear product visibility
- Use consistent background across product images
- Avoid watermarks or text overlays

#### File Optimization

- Compress images before uploading (use tools like TinyPNG)
- Keep file sizes under 500KB when possible
- Use JPEG for photos, PNG for graphics with transparency
- Consider WEBP for best compression and quality balance

#### Image Organization

- Upload multiple angles of the product
- Include close-up shots of important details
- Add lifestyle images showing product in use
- Maintain consistent image ratios (square works best)

#### Primary Image Selection

- Choose the most appealing shot as primary
- Primary image appears in product listings
- Use front-facing or hero shot as primary

## API Endpoints

### Upload Images

```
POST /api/v1/admin/products/{id}/images
Content-Type: multipart/form-data

Body:
{
  images[0]: File
  images[1]: File
  ...
}

Response:
{
  success: true,
  message: "2 image(s) uploaded successfully.",
  images: [
    {
      id: 1,
      product_id: 5,
      image_path: "/storage/products/beef-1234.jpg",
      alt_text: "Premium Beef",
      sort_order: 0,
      is_primary: true
    },
    ...
  ]
}
```

### Delete Image

```
DELETE /api/v1/admin/products/{id}/images/{imageId}

Response:
{
  success: true,
  message: "Image deleted successfully."
}
```

### Reorder Images

```
POST /api/v1/admin/products/{id}/images/reorder

Body:
{
  image_ids: [3, 1, 2, 4, 5]  // New order (first is primary)
}

Response:
{
  success: true,
  message: "Images reordered successfully.",
  images: [...]
}
```

## Component Integration

### Using ProductImageUpload Component

```vue
<script setup>
import ProductImageUpload from "@/components/admin/ProductImageUpload.vue";

const productId = ref(5);
const existingImages = ref([
  {
    id: 1,
    image_path: "/storage/products/image1.jpg",
    alt_text: "Product Image 1",
    is_primary: true,
    sort_order: 0,
  },
]);

function handleUploadSuccess(images) {
  console.log("New images uploaded:", images);
  // Refresh product data or update UI
}

function handleDeleteSuccess(imageId) {
  console.log("Image deleted:", imageId);
  // Update UI or show notification
}

function handleReorderSuccess(images) {
  console.log("Images reordered:", images);
  // Update UI with new order
}
</script>

<template>
  <ProductImageUpload
    :product-id="productId"
    :existing-images="existingImages"
    @upload-success="handleUploadSuccess"
    @delete-success="handleDeleteSuccess"
    @reorder-success="handleReorderSuccess"
  />
</template>
```

### Props

| Prop             | Type   | Required | Description                         |
| ---------------- | ------ | -------- | ----------------------------------- |
| `productId`      | Number | Yes      | The product ID to upload images for |
| `existingImages` | Array  | No       | Array of existing product images    |

### Events

| Event             | Payload         | Description                                   |
| ----------------- | --------------- | --------------------------------------------- |
| `upload-success`  | Array of images | Emitted when images are successfully uploaded |
| `delete-success`  | Image ID        | Emitted when an image is deleted              |
| `reorder-success` | Array of images | Emitted when images are reordered             |

## Troubleshooting

### Upload Fails

**Problem**: Images don't upload or error message appears

**Solutions**:

- Check file size (must be under 2MB)
- Verify file format (JPEG, PNG, or WEBP only)
- Ensure you haven't exceeded 5 images limit
- Check internet connection
- Verify you're logged in as Admin or Staff

### Images Not Appearing

**Problem**: Uploaded images don't show in the grid

**Solutions**:

- Refresh the page
- Check browser console for errors
- Verify upload actually completed (check for success message)
- Ensure storage directory has write permissions

### Can't Delete Image

**Problem**: Delete button doesn't work or throws error

**Solutions**:

- Ensure you have admin/staff permissions
- Check if image file still exists on server
- Try refreshing the page
- Verify you're not trying to delete the only image (at least one required)

### Primary Image Not Changing

**Problem**: Star button doesn't set image as primary

**Solutions**:

- Refresh the page
- Check network tab for failed API calls
- Verify image exists in database
- Try reordering manually instead

## Storage Structure

Images are stored in the following directory structure:

```
storage/app/public/products/
├── beef-premium-1234567890-abc123.jpg          # Original images
├── lamb-chops-1234567891-def456.jpg
├── thumbnails/
│   ├── beef-premium-1234567890-abc123.jpg      # 150x150 thumbnails
│   └── lamb-chops-1234567891-def456.jpg
└── medium/
    ├── beef-premium-1234567890-abc123.jpg      # 500x500 medium size
    └── lamb-chops-1234567891-def456.jpg
```

## Security & Permissions

- Only **Admin** and **Staff** users can upload/delete images
- Files are validated server-side for type and size
- Filenames are sanitized and made unique to prevent conflicts
- Images are stored outside the web root for security

## Performance Tips

1. **Batch Uploads**: Upload multiple images at once instead of one by one
2. **Pre-Optimize**: Resize and compress images before uploading
3. **Use WEBP**: Modern browsers support WEBP for better compression
4. **Delete Unused**: Remove old/unused images to save storage space
5. **CDN**: Consider using a CDN for production environments

## Related Documentation

- [Product Management Guide](./product-management.md)
- [Real Product Images Guide](../setup/real-product-images-guide.md)
- [Image Optimization Best Practices](../setup/image-optimization.md)
