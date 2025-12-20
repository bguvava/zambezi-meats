<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Promotion model.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $type
 * @property float $value
 * @property float $min_order
 * @property int|null $max_uses
 * @property int $uses_count
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Promotion extends Model
{
    use HasFactory;

    /**
     * Type constants.
     */
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'min_order',
        'max_uses',
        'uses_count',
        'start_date',
        'end_date',
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
            'value' => 'decimal:2',
            'min_order' => 'decimal:2',
            'max_uses' => 'integer',
            'uses_count' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Find a valid promotion by code.
     */
    public static function findValidByCode(string $code): ?self
    {
        return static::where('code', strtoupper($code))
            ->active()
            ->valid()
            ->first();
    }

    /**
     * Calculate the discount for an order total.
     */
    public function calculateDiscount(float $orderTotal): float
    {
        $minOrder = (float) $this->min_order;
        $value = (float) $this->value;

        if ($orderTotal < $minOrder) {
            return 0.00;
        }

        if ($this->type === self::TYPE_PERCENTAGE) {
            return round($orderTotal * ($value / 100), 2);
        }

        return min($value, $orderTotal);
    }

    /**
     * Check if promotion can be used.
     */
    public function canBeUsed(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->start_date > $today || $this->end_date < $today) {
            return false;
        }

        return true;
    }

    /**
     * Increment uses count.
     */
    public function incrementUsage(): void
    {
        $this->increment('uses_count');
    }

    /**
     * Scope to get only active promotions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get valid promotions (within date range and under max uses).
     */
    public function scopeValid($query)
    {
        $today = now()->startOfDay();

        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereColumn('uses_count', '<', 'max_uses');
            });
    }
}
