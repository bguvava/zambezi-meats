<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder for creating products from JSON data files.
 *
 * Products are organized by category in separate JSON files in the data/ directory.
 * This approach keeps the seeder maintainable and allows easy updates to product data.
 */
class ProductSeeder extends Seeder
{
    /**
     * JSON data files to process.
     *
     * @var array<string>
     */
    private array $dataFiles = [
        'products_beef.json',
        'products_lamb.json',
        'products_pork.json',
        'products_veal.json',
        'products_poultry.json',
        'products_sausages.json',
        'products_deli.json',
        'products_meals.json',
        'products_other.json',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🥩 Starting Zambezi Meats product seeding...');

        foreach ($this->dataFiles as $dataFile) {
            $this->seedFromJsonFile($dataFile);
        }

        $totalProducts = Product::count();
        $this->command->info("✅ Successfully seeded {$totalProducts} products!");
    }

    /**
     * Seed products from a JSON file.
     */
    private function seedFromJsonFile(string $filename): void
    {
        $filePath = database_path("seeders/data/{$filename}");

        if (!file_exists($filePath)) {
            $this->command->warn("⚠️  File not found: {$filename}");
            return;
        }

        $jsonContent = file_get_contents($filePath);
        $products = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error("❌ Invalid JSON in {$filename}: " . json_last_error_msg());
            return;
        }

        $count = 0;
        foreach ($products as $productData) {
            if ($this->createProduct($productData)) {
                $count++;
            }
        }

        $this->command->info("  📦 {$filename}: {$count} products");
    }

    /**
     * Create a product from data array.
     */
    private function createProduct(array $productData): bool
    {
        $categorySlug = $productData['category_slug'] ?? null;

        if (!$categorySlug) {
            $this->command->warn("  ⚠️  Missing category_slug for: {$productData['name']}");
            return false;
        }

        // Find category
        $category = Category::where('slug', $categorySlug)->first();

        if (!$category) {
            $this->command->warn("  ⚠️  Category not found: {$categorySlug}");
            return false;
        }

        // Prepare product data
        $productData['category_id'] = $category->id;
        $productData['slug'] = Str::slug($productData['name']);
        $productData['sku'] = $this->generateSKU($productData['name'], $category->name);
        $productData['is_active'] = $productData['is_active'] ?? true;
        $productData['is_featured'] = $productData['is_featured'] ?? false;
        $productData['stock'] = $productData['stock'] ?? 100;

        // Remove fields not in database
        unset($productData['category_slug']);
        $imagePath = $productData['image_path'] ?? null;
        unset($productData['image_path']);

        // Create or update product
        $product = Product::updateOrCreate(
            ['slug' => $productData['slug']],
            $productData
        );

        // Create product image
        $this->createProductImage($product, $imagePath);

        return true;
    }

    /**
     * Generate a consistent SKU for a product.
     */
    private function generateSKU(string $productName, string $categoryName): string
    {
        // Create deterministic SKU based on product and category names
        $prefix = 'ZM';
        $categoryCode = strtoupper(substr(preg_replace('/[^A-Z]/i', '', $categoryName), 0, 2));
        $productCode = strtoupper(substr(preg_replace('/[^A-Z]/i', '', $productName), 0, 4));
        $hash = substr(md5($productName . $categoryName), 0, 4);

        return "{$prefix}-{$categoryCode}{$productCode}-{$hash}";
    }

    /**
     * Create product image.
     */
    private function createProductImage(Product $product, ?string $imagePath): void
    {
        $imagePath = $imagePath ?? '/images/products/placeholder.jpg';

        ProductImage::updateOrCreate(
            [
                'product_id' => $product->id,
                'is_primary' => true,
            ],
            [
                'product_id' => $product->id,
                'image_path' => $imagePath,
                'alt_text' => $product->name,
                'sort_order' => 0,
                'is_primary' => true,
            ]
        );
    }
}
