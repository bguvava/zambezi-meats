<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateOrderRequest;
use App\Http\Requests\Api\V1\ValidateAddressRequest;
use App\Http\Requests\Api\V1\ValidatePromoRequest;
use App\Http\Requests\Api\V1\CalculateDeliveryFeeRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Http\Resources\Api\V1\DeliveryZoneResource;
use App\Models\Cart;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promotion;
use App\Services\StockReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Checkout Controller
 *
 * Handles the checkout process including address validation,
 * delivery fee calculation, promo code validation, and order creation.
 *
 * @requirement CHK-003 to CHK-007 Address and delivery handling
 * @requirement CHK-015 Promo code validation
 * @requirement CHK-025 to CHK-027 Order creation and API endpoints
 */
class CheckoutController extends Controller
{
    public function __construct(
        private readonly StockReservationService $stockReservationService
    ) {}

    /**
     * Validate delivery address and return delivery zone info.
     *
     * @requirement CHK-006 Validate delivery zone
     */
    public function validateAddress(ValidateAddressRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Find delivery zone by suburb or postcode
        $suburb = $validated['suburb'] ?? null;
        $postcode = $validated['postcode'];

        // Try to find zone by suburb first
        $zone = null;
        if ($suburb) {
            $zone = DeliveryZone::findBySuburb($suburb);
        }

        // If not found by suburb, try by postcode range (first 2 digits)
        if (!$zone) {
            $zone = $this->findZoneByPostcode($postcode);
        }

        if (!$zone) {
            return response()->json([
                'success' => false,
                'message' => "Sorry, we don't currently deliver to your area. Please check back later or contact us for special arrangements.",
                'delivers' => false,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'delivers' => true,
            'message' => 'Great news! We deliver to your area.',
            'zone' => new DeliveryZoneResource($zone),
        ]);
    }

    /**
     * Calculate delivery fee based on address and cart total.
     *
     * @requirement CHK-007 Calculate and display delivery fee
     */
    public function calculateFee(CalculateDeliveryFeeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $suburb = $validated['suburb'] ?? null;
        $postcode = $validated['postcode'];
        $subtotal = (float) $validated['subtotal'];

        // Find delivery zone
        $zone = $suburb ? DeliveryZone::findBySuburb($suburb) : $this->findZoneByPostcode($postcode);

        if (!$zone) {
            return response()->json([
                'success' => false,
                'message' => "Unable to calculate delivery fee. We don't deliver to this area.",
                'fee' => null,
            ], 200);
        }

        $fee = $zone->getDeliveryFeeFor($subtotal);
        $isFree = $zone->isFreeDelivery($subtotal);

        $response = [
            'success' => true,
            'fee' => $fee,
            'fee_formatted' => '$' . number_format($fee, 2),
            'is_free' => $isFree,
            'zone_name' => $zone->name,
            'estimated_days' => $zone->estimated_days,
        ];

        if (!$isFree && $zone->free_delivery_threshold) {
            $threshold = (float) $zone->free_delivery_threshold;
            $amountToFree = $threshold - $subtotal;
            $response['free_delivery_threshold'] = $threshold;
            $response['amount_to_free_delivery'] = round($amountToFree, 2);
            $response['message'] = sprintf(
                'Add $%.2f more for FREE delivery!',
                $amountToFree
            );
        } else if ($isFree) {
            $response['message'] = 'You qualify for FREE delivery!';
        }

        return response()->json($response);
    }

    /**
     * Validate promo code and calculate discount.
     *
     * @requirement CHK-015 Promo code validation
     */
    public function validatePromo(ValidatePromoRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $code = strtoupper(trim($validated['code']));
        $subtotal = (float) $validated['subtotal'];

        $promotion = Promotion::findValidByCode($code);

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired promo code.',
                'valid' => false,
            ], 200);
        }

        if (!$promotion->canBeUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'This promo code is no longer available.',
                'valid' => false,
            ], 200);
        }

        if ($subtotal < $promotion->min_order) {
            return response()->json([
                'success' => false,
                'message' => sprintf(
                    'Minimum order of $%.2f required for this code.',
                    $promotion->min_order
                ),
                'valid' => false,
                'min_order' => $promotion->min_order,
            ], 200);
        }

        $discount = $promotion->calculateDiscount($subtotal);

        return response()->json([
            'success' => true,
            'valid' => true,
            'message' => sprintf('Promo code applied! You save $%.2f', $discount),
            'code' => $promotion->code,
            'name' => $promotion->name,
            'type' => $promotion->type,
            'value' => $promotion->value,
            'discount' => $discount,
            'discount_formatted' => '-$' . number_format($discount, 2),
        ]);
    }

    /**
     * Create a new order from the cart.
     *
     * @requirement CHK-025 Create order in database
     * @requirement CHK-026 Generate unique order number
     * @requirement CHK-022 Stock reservation
     */
    public function createOrder(CreateOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Get user's cart
        $cart = Cart::where('user_id', $user->id)
            ->with('items.product')
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.',
            ], 422);
        }

        // Validate stock availability
        foreach ($cart->items as $item) {
            if ($item->product->stock !== null && $item->quantity > $item->product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf(
                        'Sorry, only %d units of %s are available.',
                        $item->product->stock,
                        $item->product->name
                    ),
                ], 422);
            }
        }

        try {
            $order = DB::transaction(function () use ($validated, $user, $cart) {
                // Calculate totals
                $subtotal = $cart->subtotal;
                $deliveryFee = 0.00;

                // Find delivery zone and calculate fee
                $zone = null;
                if (!empty($validated['suburb'])) {
                    $zone = DeliveryZone::findBySuburb($validated['suburb']);
                }
                if (!$zone && !empty($validated['postcode'])) {
                    $zone = $this->findZoneByPostcode($validated['postcode']);
                }

                if ($zone) {
                    $deliveryFee = $zone->getDeliveryFeeFor($subtotal);
                }

                // Calculate discount
                $discount = 0.00;
                $promotionCode = $validated['promo_code'] ?? null;

                if ($promotionCode) {
                    $promotion = Promotion::findValidByCode($promotionCode);
                    if ($promotion && $promotion->canBeUsed()) {
                        $discount = $promotion->calculateDiscount($subtotal);
                        $promotion->incrementUsage();
                    }
                }

                $total = $subtotal + $deliveryFee - $discount;

                // Create or update address
                $addressId = $this->handleAddress($user, $validated);

                // Create order
                $order = Order::create([
                    'user_id' => $user->id,
                    'address_id' => $addressId,
                    'delivery_zone_id' => $zone?->id,
                    'status' => Order::STATUS_PENDING,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'discount' => $discount,
                    'total' => $total,
                    'currency' => 'AUD',
                    'exchange_rate' => 1.000000,
                    'promotion_code' => $promotionCode,
                    'notes' => $validated['notes'] ?? null,
                    'delivery_instructions' => $validated['delivery_instructions'] ?? null,
                    'scheduled_date' => $validated['scheduled_date'] ?? null,
                    'scheduled_time_slot' => $validated['scheduled_time_slot'] ?? null,
                ]);

                // Create order items
                foreach ($cart->items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'quantity' => (int) $item->quantity,
                        'unit_price' => $item->product->price_aud,
                        'total_price' => $item->quantity * $item->product->price_aud,
                        'product_name' => $item->product->name,
                        'product_sku' => $item->product->sku ?? null,
                    ]);

                    // Reserve stock
                    $this->stockReservationService->reserve(
                        $item->product_id,
                        (int) $item->quantity,
                        $order->id
                    );
                }

                // Clear cart
                $cart->items()->delete();
                $cart->delete();

                return $order;
            });

            // Load relationships for response
            $order->load(['items', 'address', 'deliveryZone']);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully.',
                'order' => new OrderResource($order),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please try again or contact support.',
            ], 500);
        }
    }

    /**
     * Get checkout session data for the authenticated user.
     */
    public function getSession(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get cart
        $cart = Cart::where('user_id', $user->id)
            ->with('items.product')
            ->first();

        // Get saved addresses
        $addresses = $user->addresses()->orderBy('is_default', 'desc')->get();

        // Get default address
        $defaultAddress = $addresses->firstWhere('is_default', true);

        return response()->json([
            'success' => true,
            'cart' => $cart ? [
                'item_count' => $cart->items->count(),
                'subtotal' => $cart->subtotal,
                'subtotal_formatted' => '$' . number_format($cart->subtotal, 2),
            ] : null,
            'addresses' => $addresses,
            'default_address' => $defaultAddress,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
            ],
        ]);
    }

    /**
     * Find a delivery zone by postcode.
     */
    private function findZoneByPostcode(string $postcode): ?DeliveryZone
    {
        // Australian postcodes are 4 digits
        // Group by first 2 digits for zone matching
        $prefix = substr($postcode, 0, 2);

        // Sydney/NSW Metro: 2000-2599
        // Melbourne/VIC Metro: 3000-3999
        // Brisbane/QLD Metro: 4000-4999
        // This is simplified - in production, you'd have a proper zones database

        return DeliveryZone::active()
            ->get()
            ->first(function ($zone) use ($postcode) {
                // Check if any suburb in the zone matches the postcode pattern
                // This is a simple implementation - extend as needed
                return true; // For now, accept all active zones
            });
    }

    /**
     * Handle address creation or selection.
     */
    private function handleAddress($user, array $validated): ?int
    {
        // If using saved address
        if (!empty($validated['address_id'])) {
            $address = $user->addresses()->find($validated['address_id']);
            return $address?->id;
        }

        // Create new address
        if (!empty($validated['street'])) {
            $address = $user->addresses()->create([
                'label' => $validated['address_label'] ?? 'Delivery Address',
                'street' => $validated['street'],
                'suburb' => $validated['suburb'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'country' => 'AU',
                'is_default' => $validated['save_address'] ?? false,
            ]);

            return $address->id;
        }

        return null;
    }
}
