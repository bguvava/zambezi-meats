<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\StockReservation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Stock Reservation Service
 *
 * Handles temporary stock reservation during checkout process.
 * Reservations expire after 15 minutes.
 *
 * @requirement CHK-022 Reserve stock during checkout
 */
class StockReservationService
{
    /**
     * Reservation duration in minutes.
     */
    private const RESERVATION_MINUTES = 15;

    /**
     * Reserve stock for a product.
     *
     * @param int $productId
     * @param int $quantity
     * @param int $orderId
     * @return bool
     */
    public function reserve(int $productId, int $quantity, int $orderId): bool
    {
        try {
            // Check if product has stock management enabled
            $product = Product::find($productId);
            if (!$product || $product->stock === null) {
                // No stock management, skip reservation
                return true;
            }

            // Check available stock
            $availableStock = $this->getAvailableStock($productId);
            if ($availableStock < $quantity) {
                return false;
            }

            // Create reservation in cache
            $reservationKey = $this->getReservationKey($productId, $orderId);
            $expiresAt = now()->addMinutes(self::RESERVATION_MINUTES);

            Cache::put($reservationKey, [
                'product_id' => $productId,
                'order_id' => $orderId,
                'quantity' => $quantity,
                'expires_at' => $expiresAt->toIso8601String(),
            ], $expiresAt);

            // Update product stock
            $product->decrement('stock', $quantity);

            Log::info('Stock reserved', [
                'product_id' => $productId,
                'order_id' => $orderId,
                'quantity' => $quantity,
                'expires_at' => $expiresAt->toIso8601String(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reserve stock', [
                'product_id' => $productId,
                'order_id' => $orderId,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Release a stock reservation.
     *
     * @param int $productId
     * @param int $orderId
     * @return bool
     */
    public function release(int $productId, int $orderId): bool
    {
        try {
            $reservationKey = $this->getReservationKey($productId, $orderId);
            $reservation = Cache::get($reservationKey);

            if (!$reservation) {
                return false;
            }

            // Restore stock
            Product::where('id', $productId)
                ->whereNotNull('stock')
                ->increment('stock', $reservation['quantity']);

            // Remove reservation
            Cache::forget($reservationKey);

            Log::info('Stock reservation released', [
                'product_id' => $productId,
                'order_id' => $orderId,
                'quantity' => $reservation['quantity'],
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to release stock reservation', [
                'product_id' => $productId,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Confirm a stock reservation (make it permanent).
     *
     * @param int $productId
     * @param int $orderId
     * @return bool
     */
    public function confirm(int $productId, int $orderId): bool
    {
        try {
            $reservationKey = $this->getReservationKey($productId, $orderId);
            $reservation = Cache::get($reservationKey);

            if (!$reservation) {
                // Already confirmed or expired
                return true;
            }

            // Remove reservation from cache (stock already decremented)
            Cache::forget($reservationKey);

            Log::info('Stock reservation confirmed', [
                'product_id' => $productId,
                'order_id' => $orderId,
                'quantity' => $reservation['quantity'],
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to confirm stock reservation', [
                'product_id' => $productId,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get available stock for a product (accounting for reservations).
     *
     * @param int $productId
     * @return int
     */
    public function getAvailableStock(int $productId): int
    {
        $product = Product::find($productId);
        if (!$product || $product->stock === null) {
            return PHP_INT_MAX; // Unlimited stock
        }

        return max(0, $product->stock);
    }

    /**
     * Check if sufficient stock is available.
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function hasStock(int $productId, int $quantity): bool
    {
        return $this->getAvailableStock($productId) >= $quantity;
    }

    /**
     * Get the cache key for a reservation.
     *
     * @param int $productId
     * @param int $orderId
     * @return string
     */
    private function getReservationKey(int $productId, int $orderId): string
    {
        return "stock_reservation:{$productId}:{$orderId}";
    }
}
