<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * CurrencyRate model.
 *
 * @property int $id
 * @property string $base_currency
 * @property string $target_currency
 * @property float $rate
 * @property \Carbon\Carbon $fetched_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CurrencyRate extends Model
{
    use HasFactory;

    /**
     * Currency constants.
     */
    public const CURRENCY_AUD = 'AUD';
    public const CURRENCY_USD = 'USD';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'fetched_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:6',
            'fetched_at' => 'datetime',
        ];
    }

    /**
     * Get the current exchange rate.
     */
    public static function getRate(string $from, string $to): ?float
    {
        $rate = static::where('base_currency', $from)
            ->where('target_currency', $to)
            ->orderBy('fetched_at', 'desc')
            ->first();

        return $rate?->rate;
    }

    /**
     * Convert amount from one currency to another.
     */
    public static function convert(float $amount, string $from, string $to): ?float
    {
        if ($from === $to) {
            return $amount;
        }

        $rate = static::getRate($from, $to);

        if ($rate === null) {
            return null;
        }

        return round($amount * $rate, 2);
    }

    /**
     * Update or create a rate.
     */
    public static function setRate(string $from, string $to, float $rate): self
    {
        return static::updateOrCreate(
            [
                'base_currency' => $from,
                'target_currency' => $to,
            ],
            [
                'rate' => $rate,
                'fetched_at' => now(),
            ]
        );
    }

    /**
     * Check if rate is stale (older than specified hours).
     */
    public function isStale(int $hours = 24): bool
    {
        return $this->fetched_at->addHours($hours)->isPast();
    }

    /**
     * Scope to get latest rates.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('fetched_at', 'desc');
    }

    /**
     * Scope to filter by currency pair.
     */
    public function scopePair($query, string $from, string $to)
    {
        return $query->where('base_currency', $from)
            ->where('target_currency', $to);
    }
}
