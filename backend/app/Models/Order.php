<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Order model.
 *
 * @property int $id
 * @property string $order_number
 * @property int $user_id
 * @property int|null $address_id
 * @property int|null $delivery_zone_id
 * @property string $status
 * @property float $subtotal
 * @property float $delivery_fee
 * @property float $discount
 * @property float $total
 * @property string $currency
 * @property float $exchange_rate
 * @property string|null $promotion_code
 * @property string|null $notes
 * @property string|null $delivery_instructions
 * @property \Carbon\Carbon|null $scheduled_date
 * @property string|null $scheduled_time_slot
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Order extends Model
{
    use HasFactory;

    /**
     * Status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY = 'ready';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'assigned_to',
        'assigned_at',
        'address_id',
        'delivery_zone_id',
        'status',
        'subtotal',
        'delivery_fee',
        'discount',
        'total',
        'currency',
        'exchange_rate',
        'delivery_method',
        'delivery_date',
        'delivery_time_slot',
        'pickup_date',
        'pickup_time_slot',
        'promotion_code',
        'notes',
        'staff_notes',
        'delivery_instructions',
        'scheduled_date',
        'scheduled_time_slot',
        'delivery_issue',
        'delivery_issue_reported_at',
        'delivery_issue_resolved_at',
        'delivery_issue_resolution',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'scheduled_date' => 'date',
            'delivery_date' => 'date',
            'pickup_date' => 'date',
            'assigned_at' => 'datetime',
            'staff_notes' => 'array',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    /**
     * Generate a unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ZM';
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));

        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assigned staff member.
     */
    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Alias for assignedStaff for cleaner queries.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the address.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * Get the delivery zone.
     */
    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    /**
     * Get the order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the status history.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Get the payment.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the invoice.
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Get the delivery proof.
     */
    public function deliveryProof(): HasOne
    {
        return $this->hasOne(DeliveryProof::class);
    }

    /**
     * Get the support tickets.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if order is active.
     */
    public function isActive(): bool
    {
        return !in_array($this->status, [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get active orders.
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Scope to get orders for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
