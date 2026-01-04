# Real Product Images Setup Guide

## Overview

This guide provides step-by-step instructions for replacing placeholder images with real product images from free stock photo sites.

## Recommended Free Stock Photo Sites

### 1. **Unsplash** (https://unsplash.com)

- High-quality, royalty-free images
- Search terms: "beef steak", "lamb chops", "pork ribs", "chicken breast", "sausages"
- Download in "Large" or "Original" size

### 2. **Pexels** (https://www.pexels.com)

- Free stock photos and videos
- Search categories: Food > Meat
- Commercial use allowed

### 3. **Pixabay** (https://pixabay.com)

- Free images and videos
- Search: "meat", "steak", "butcher", "raw meat"
- No attribution required

### 4. **Freepik** (https://www.freepik.com)

- Free vectors, photos, and PSD files
- Attribution may be required for free tier
- Premium quality meat images

## Directory Structure

```
frontend/
  public/
    images/
      products/          # Main product images
        beef/
        lamb/
        pork/
        veal/
        poultry/
        sausages/
        deli/
        meals/
        other/
      placeholder-product.jpg  # Default fallback
```

## Step 1: Download Product Images

### Beef Products (15-20 images needed)

Search terms on stock sites:

- "ribeye steak"
- "sirloin steak"
- "t-bone steak"
- "beef rump"
- "scotch fillet"
- "eye fillet"
- "beef brisket"
- "beef ribs"
- "beef mince"
- "beef roast"
- "wagyu beef"

### Lamb Products (10-15 images needed)

- "lamb chops"
- "lamb rack"
- "lamb leg"
- "lamb shoulder"
- "lamb cutlets"
- "lamb shank"
- "lamb mince"

### Pork Products (10-15 images needed)

- "pork chops"
- "pork belly"
- "pork ribs"
- "pork shoulder"
- "pork loin"
- "bacon"
- "pork mince"

### Poultry Products (10-15 images needed)

- "chicken breast"
- "chicken thighs"
- "chicken wings"
- "whole chicken"
- "chicken drumsticks"
- "chicken mince"
- "turkey breast"

### Sausages & Deli (10-15 images needed)

- "sausages"
- "beef sausage"
- "pork sausage"
- "deli meats"
- "salami"
- "ham"

## Step 2: Organize Downloaded Images

1. **Rename files** using a consistent pattern:

   ```
   beef-ribeye-steak-01.jpg
   beef-ribeye-steak-02.jpg
   lamb-rack-01.jpg
   pork-belly-01.jpg
   chicken-breast-01.jpg
   sausages-beef-01.jpg
   ```

2. **Optimize images** for web:

   - Resize to max 1200px width (maintain aspect ratio)
   - Compress to 80-85% quality
   - Use tools like:
     - **TinyPNG** (https://tinypng.com) - Online compression
     - **ImageOptim** (Mac) - Desktop app
     - **RIOT** (Windows) - Desktop app
     - **Squoosh** (https://squoosh.app) - Online tool

3. **Move files** to appropriate directories:

   ```bash
   # From project root
   cd frontend/public/images/products

   # Create category directories if they don't exist
   mkdir -p beef lamb pork veal poultry sausages deli meals other

   # Move images to respective folders
   mv beef-*.jpg beef/
   mv lamb-*.jpg lamb/
   mv pork-*.jpg pork/
   # etc...
   ```

## Step 3: Update Product Seeder JSON Files

The product data files are located in:

```
backend/database/seeders/data/
```

### Example: Update `products_beef.json`

**Before:**

```json
{
  "name": "Ribeye Steak",
  "slug": "ribeye-steak",
  "category_slug": "beef",
  "description": "Premium ribeye steak...",
  "price_aud": 45.99,
  "unit": "kg",
  "images": ["/images/placeholder-product.jpg"]
}
```

**After:**

```json
{
  "name": "Ribeye Steak",
  "slug": "ribeye-steak",
  "category_slug": "beef",
  "description": "Premium ribeye steak...",
  "price_aud": 45.99,
  "unit": "kg",
  "images": [
    "/images/products/beef/beef-ribeye-steak-01.jpg",
    "/images/products/beef/beef-ribeye-steak-02.jpg"
  ],
  "main_image": "/images/products/beef/beef-ribeye-steak-01.jpg"
}
```

### Batch Update Process

1. **Open each JSON file** in `backend/database/seeders/data/`
2. **Update the `images` array** with real image paths
3. **Add `main_image` property** pointing to the primary product image
4. **Save the file**

Files to update:

- ✅ `products_beef.json`
- ✅ `products_lamb.json`
- ✅ `products_pork.json`
- ✅ `products_veal.json`
- ✅ `products_poultry.json`
- ✅ `products_sausages.json`
- ✅ `products_deli.json`
- ✅ `products_meals.json`
- ✅ `products_other.json`

## Step 4: Database Schema Update

The `products` table already has an `image_url` column. Product images are stored in the `product_images` table.

### Verify Schema

Check if these columns exist:

**products table:**

```sql
image_url VARCHAR(255)  -- Main product image
```

**product_images table:**

```sql
product_id BIGINT
image_url VARCHAR(255)
alt_text VARCHAR(255)
sort_order INT
is_primary BOOLEAN
```

If the schema is correct, proceed to Step 5.

## Step 5: Re-seed the Database

After updating all JSON files with real image paths:

```bash
cd backend

# Clear existing products (this will cascade delete product_images)
php artisan db:seed --class=ProductSeeder

# Or refresh entire database
php artisan migrate:fresh --seed
```

**⚠️ WARNING:** This will delete all existing products and orders. Only do this in development!

### Production Alternative: Update Without Re-seeding

For production environments, manually update products:

```bash
cd backend
php artisan tinker
```

Then in tinker:

```php
// Update a single product
$product = Product::where('slug', 'ribeye-steak')->first();
$product->image_url = '/images/products/beef/beef-ribeye-steak-01.jpg';
$product->save();

// Add additional images
$product->images()->create([
    'image_url' => '/images/products/beef/beef-ribeye-steak-02.jpg',
    'alt_text' => 'Ribeye Steak - Alternate View',
    'sort_order' => 2,
    'is_primary' => false
]);
```

## Step 6: Verify Images Display Correctly

### Frontend Verification

1. **Start development server:**

   ```bash
   cd frontend
   npm run dev
   ```

2. **Check these pages:**

   - Homepage Featured Products: http://localhost:5173/
   - Shop Page: http://localhost:5173/shop
   - Individual Product Pages: http://localhost:5173/shop/ribeye-steak
   - Cart Page: http://localhost:5173/cart
   - Wishlist Page: http://localhost:5173/customer/wishlist

3. **Verify fallback works:**
   - Remove an image file temporarily
   - Check that placeholder-product.jpg displays instead
   - The ProductCard component has this logic:
     ```vue
     @error="($event) => $event.target.src = '/images/placeholder-product.jpg'"
     ```

### Backend API Verification

Test the products API:

```bash
curl http://localhost:8000/api/v1/products | jq
```

Check response includes image URLs:

```json
{
  "data": [
    {
      "id": 1,
      "name": "Ribeye Steak",
      "slug": "ribeye-steak",
      "main_image": "/images/products/beef/beef-ribeye-steak-01.jpg",
      "images": [
        {
          "id": 1,
          "image_url": "/images/products/beef/beef-ribeye-steak-01.jpg",
          "alt_text": "Ribeye Steak",
          "is_primary": true
        }
      ]
    }
  ]
}
```

## Step 7: Image Attribution (Optional)

If using images that require attribution:

1. **Create attribution file:**

   ```
   frontend/public/images/products/ATTRIBUTION.md
   ```

2. **Add attribution details:**

   ```markdown
   # Image Attribution

   ## Beef Products

   - beef-ribeye-steak-01.jpg: Photo by [Photographer Name](photographer-url) on [Unsplash](unsplash-url)
   - beef-ribeye-steak-02.jpg: Photo by...

   ## Lamb Products

   - lamb-rack-01.jpg: Photo by...
   ```

3. **Add attribution link in footer:**
   Update `frontend/src/components/common/FooterSection.vue` if required.

## Automation Script (Optional)

For bulk processing, create a Node.js script:

**`scripts/update-product-images.js`:**

```javascript
const fs = require("fs");
const path = require("path");

const productsDir = path.join(__dirname, "../backend/database/seeders/data");
const imageMapping = {
  beef: {
    "ribeye-steak": ["beef-ribeye-steak-01.jpg", "beef-ribeye-steak-02.jpg"],
    "sirloin-steak": ["beef-sirloin-steak-01.jpg"],
    // Add more mappings...
  },
  lamb: {
    // ...
  },
};

function updateProductImages() {
  const files = fs.readdirSync(productsDir).filter((f) => f.endsWith(".json"));

  files.forEach((file) => {
    const filePath = path.join(productsDir, file);
    const products = JSON.parse(fs.readFileSync(filePath, "utf8"));

    products.forEach((product) => {
      const category = product.category_slug;
      const slug = product.slug;

      if (imageMapping[category] && imageMapping[category][slug]) {
        product.images = imageMapping[category][slug].map(
          (img) => `/images/products/${category}/${img}`
        );
        product.main_image = product.images[0];
      }
    });

    fs.writeFileSync(filePath, JSON.stringify(products, null, 2));
    console.log(`✅ Updated ${file}`);
  });
}

updateProductImages();
```

Run it:

```bash
node scripts/update-product-images.js
```

## Checklist

- [ ] Downloaded 80-100 high-quality meat images from stock sites
- [ ] Organized images into category folders
- [ ] Optimized images for web (compressed, resized)
- [ ] Renamed images with consistent naming pattern
- [ ] Updated all 9 product JSON seeder files with real image paths
- [ ] Re-seeded database with new product data
- [ ] Verified images display correctly on frontend
- [ ] Tested image fallback for missing images
- [ ] Added image attribution if required
- [ ] Committed changes to version control

## Image Specifications

| Property         | Specification              |
| ---------------- | -------------------------- |
| **Format**       | JPG (preferred), PNG, WebP |
| **Max Width**    | 1200px                     |
| **Aspect Ratio** | 1:1 (square) or 4:3        |
| **File Size**    | < 200KB per image          |
| **Quality**      | 80-85%                     |
| **Color Space**  | sRGB                       |
| **DPI**          | 72 (web standard)          |

## Troubleshooting

### Images Not Displaying

1. **Check file paths:**

   ```bash
   ls -la frontend/public/images/products/beef/
   ```

2. **Verify permissions:**

   ```bash
   chmod 644 frontend/public/images/products/*/*.jpg
   ```

3. **Check browser console** for 404 errors

4. **Clear browser cache:**
   - Chrome: Ctrl+Shift+Del
   - Firefox: Ctrl+Shift+Del

### Images Too Large

Use image compression:

```bash
# Using ImageMagick
convert input.jpg -quality 85 -resize 1200x output.jpg

# Using TinyPNG CLI
tinypng input.jpg --output output.jpg
```

### Database Not Updating

1. **Clear cache:**

   ```bash
   cd backend
   php artisan cache:clear
   php artisan config:clear
   ```

2. **Re-run seeder:**

   ```bash
   php artisan db:seed --class=ProductSeeder --force
   ```

3. **Check logs:**
   ```bash
   tail -f backend/storage/logs/laravel.log
   ```

## Performance Optimization

### Lazy Loading

Images already use lazy loading in ProductCard component:

```vue
<img loading="lazy" ... />
```

### Responsive Images

Consider adding srcset for different screen sizes:

```vue
<img
  :src="product.main_image"
  :srcset="`
    ${product.main_image_small} 400w,
    ${product.main_image} 800w,
    ${product.main_image_large} 1200w
  `"
  sizes="(max-width: 640px) 400px, (max-width: 1024px) 800px, 1200px"
  loading="lazy"
/>
```

### Image CDN (Future Enhancement)

For production, consider using a CDN like:

- **Cloudinary** - Image hosting and optimization
- **Imgix** - Real-time image processing
- **AWS S3 + CloudFront** - Scalable storage and delivery

## Next Steps

1. **Implement image upload** in admin dashboard for adding new products
2. **Add image optimization** pipeline for automated compression
3. **Set up CDN** for faster global image delivery
4. **Add WebP support** with JPEG fallback for better compression
5. **Implement progressive image loading** with blur-up effect

## Resources

- [Unsplash API](https://unsplash.com/developers) - Programmatic image access
- [Sharp](https://sharp.pixelplumbing.com/) - Node.js image processing
- [Image Optimization Best Practices](https://web.dev/fast/#optimize-your-images)
- [Responsive Images Guide](https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images)

## Support

For issues or questions:

1. Check the [main documentation](../README.md)
2. Review product seeder code in `backend/database/seeders/ProductSeeder.php`
3. Check ProductCard component in `frontend/src/components/shop/ProductCard.vue`
4. Contact the development team

---

**Last Updated:** January 3, 2026
**Version:** 1.0.0
