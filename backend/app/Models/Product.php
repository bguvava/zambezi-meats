<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Product model.
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $short_description
 * @property float $price_aud
 * @property float|null $sale_price_aud
 * @property int $stock
 * @property string $sku
 * @property string $unit
 * @property float|null $weight_kg
 * @property bool $is_featured
 * @property bool $is_active
 * @property array|null $meta
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Unit constants.
     */
    public const UNIT_KG = 'kg';
    public const UNIT_PIECE = 'piece';
    public const UNIT_PACK = 'pack';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'short_description',
        'price_aud',
        'sale_price_aud',
        'stock',
        'sku',
        'unit',
        'weight_kg',
        'is_featured',
        'is_active',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_aud' => 'decimal:2',
            'sale_price_aud' => 'decimal:2',
            'stock' => 'integer',
            'weight_kg' => 'decimal:3',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }

    /**
     * Get the category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the product images.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the primary image.
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Get the order items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the inventory logs for this product.
     */
    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Get the wishlist entries for this product.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the current price (sale price if available).
     */
    public function getCurrentPriceAttribute(): float
    {
        return $this->sale_price_aud ?? $this->price_aud;
    }

    /**
     * Alias for price_aud (used by cart).
     */
    public function getPriceAttribute(): float
    {
        return (float) $this->price_aud;
    }

    /**
     * Alias for stock (used by cart).
     */
    public function getStockQuantityAttribute(): ?int
    {
        return $this->stock;
    }

    /**
     * Check if product is on sale.
     */
    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price_aud !== null && $this->sale_price_aud < $this->price_aud;
    }

    /**
     * Check if product is in stock.
     */
    public function getInStockAttribute(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Scope to get only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to get only in-stock products.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope to get products on sale.
     */
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price_aud');
    }
}
