<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DeliveryProof model.
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $photo_path
 * @property string|null $signature_path
 * @property string|null $signature_data
 * @property string|null $recipient_name
 * @property string|null $notes
 * @property bool $left_at_door
 * @property int $captured_by
 * @property \Carbon\Carbon|null $captured_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class DeliveryProof extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'photo_path',
        'signature_path',
        'signature_data',
        'recipient_name',
        'notes',
        'left_at_door',
        'captured_by',
        'captured_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'left_at_door' => 'boolean',
            'captured_at' => 'datetime',
        ];
    }

    /**
     * Get the order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the staff member who captured the proof.
     */
    public function capturedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captured_by');
    }

    /**
     * Check if location was captured.
     */
    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Check if photo was captured.
     */
    public function hasPhoto(): bool
    {
        return $this->photo_path !== null;
    }

    /**
     * Check if signature was captured.
     */
    public function hasSignature(): bool
    {
        return $this->signature_path !== null;
    }
}
