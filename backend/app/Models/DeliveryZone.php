<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DeliveryZone model.
 *
 * @property int $id
 * @property string $name
 * @property array $suburbs
 * @property float $delivery_fee
 * @property float|null $free_delivery_threshold
 * @property int $estimated_days
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class DeliveryZone extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'suburbs',
        'delivery_fee',
        'free_delivery_threshold',
        'estimated_days',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'suburbs' => 'array',
            'delivery_fee' => 'decimal:2',
            'free_delivery_threshold' => 'decimal:2',
            'estimated_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the orders in this delivery zone.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if delivery is free for the given amount.
     */
    public function isFreeDelivery(float $orderTotal): bool
    {
        if ($this->free_delivery_threshold === null) {
            return false;
        }

        return $orderTotal >= (float) $this->free_delivery_threshold;
    }

    /**
     * Get the delivery fee for the given order total.
     */
    public function getDeliveryFeeFor(float $orderTotal): float
    {
        if ($this->isFreeDelivery($orderTotal)) {
            return 0.00;
        }

        return (float) $this->delivery_fee;
    }

    /**
     * Find a delivery zone by suburb.
     */
    public static function findBySuburb(string $suburb): ?self
    {
        return static::active()
            ->get()
            ->first(function ($zone) use ($suburb) {
                return in_array(strtolower($suburb), array_map('strtolower', $zone->suburbs));
            });
    }

    /**
     * Scope to get only active zones.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
