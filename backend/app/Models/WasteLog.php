<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WasteLog model.
 *
 * @property int $id
 * @property int $product_id
 * @property int $logged_by
 * @property float $quantity
 * @property string $reason
 * @property string|null $notes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @requirement STAFF-015 Create waste log page
 * @requirement STAFF-016 Submit waste log entry
 */
class WasteLog extends Model
{
    use HasFactory;

    /**
     * Reason constants.
     */
    public const REASON_DAMAGED = 'damaged';
    public const REASON_EXPIRED = 'expired';
    public const REASON_QUALITY = 'quality';
    public const REASON_OTHER = 'other';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'logged_by',
        'quantity',
        'reason',
        'unit_cost',
        'total_cost',
        'notes',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the staff member who logged.
     */
    public function loggedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    /**
     * Alias for loggedByUser for compatibility.
     */
    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    /**
     * Get valid reasons.
     */
    public static function getReasons(): array
    {
        return [
            self::REASON_DAMAGED,
            self::REASON_EXPIRED,
            self::REASON_QUALITY,
            self::REASON_OTHER,
        ];
    }

    /**
     * Scope to filter by reason.
     */
    public function scopeReason($query, string $reason)
    {
        return $query->where('reason', $reason);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get logs for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
