<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cart\AddToCartRequest;
use App\Http\Requests\Api\V1\Cart\UpdateCartItemRequest;
use App\Http\Requests\Api\V1\Cart\SyncCartRequest;
use App\Http\Resources\Api\V1\CartResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Cart Controller
 *
 * Handles cart CRUD operations, stock validation, and cart syncing.
 *
 * @requirement CART-019 Create cart API endpoints
 */
class CartController extends Controller
{
    /**
     * Get the current user's cart.
     *
     * @requirement CART-019 GET /api/v1/cart
     *
     * @param Request $request
     * @return CartResource
     */
    public function show(Request $request): CartResource
    {
        $cart = $this->getOrCreateCart($request->user());

        return new CartResource($cart->load('items.product.images'));
    }

    /**
     * Add an item to the cart.
     *
     * @requirement CART-019 POST /api/v1/cart/items
     * @requirement CART-012 Validate stock on add to cart
     *
     * @param AddToCartRequest $request
     * @return CartResource|JsonResponse
     */
    public function addItem(AddToCartRequest $request): CartResource|JsonResponse
    {
        $product = Product::find($request->product_id);

        if (!$product || !$product->is_active) {
            return response()->json([
                'message' => 'Product not found or unavailable.',
            ], 404);
        }

        // Validate stock
        $quantity = (float) $request->quantity;

        if ($product->stock_quantity < $quantity) {
            return response()->json([
                'message' => 'Insufficient stock available.',
                'available' => $product->stock_quantity,
            ], 422);
        }

        $cart = $this->getOrCreateCart($request->user());

        DB::transaction(function () use ($cart, $product, $quantity) {
            // Check if product already in cart
            $existingItem = $cart->items()->where('product_id', $product->id)->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $quantity;

                // Validate new quantity against stock
                if ($product->stock_quantity < $newQuantity) {
                    throw new \Exception('Total quantity exceeds available stock.');
                }

                $existingItem->update([
                    'quantity' => $newQuantity,
                    'unit_price' => $product->price,
                ]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                ]);
            }

            $cart->touch();
        });

        return new CartResource($cart->fresh()->load('items.product.images'));
    }

    /**
     * Update a cart item's quantity.
     *
     * @requirement CART-019 PUT /api/v1/cart/items/{id}
     * @requirement CART-003 Implement quantity adjustment
     *
     * @param UpdateCartItemRequest $request
     * @param int $itemId
     * @return CartResource|JsonResponse
     */
    public function updateItem(UpdateCartItemRequest $request, int $itemId): CartResource|JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());

        $cartItem = $cart->items()->find($itemId);

        if (!$cartItem) {
            return response()->json([
                'message' => 'Cart item not found.',
            ], 404);
        }

        $product = $cartItem->product;
        $quantity = (float) $request->quantity;

        // Validate stock
        if ($product->stock_quantity < $quantity) {
            return response()->json([
                'message' => 'Insufficient stock available.',
                'available' => $product->stock_quantity,
            ], 422);
        }

        $cartItem->update([
            'quantity' => $quantity,
            'unit_price' => $product->price, // Update to current price
        ]);

        $cart->touch();

        return new CartResource($cart->fresh()->load('items.product.images'));
    }

    /**
     * Remove an item from the cart.
     *
     * @requirement CART-019 DELETE /api/v1/cart/items/{id}
     * @requirement CART-004 Implement item removal
     *
     * @param Request $request
     * @param int $itemId
     * @return CartResource|JsonResponse
     */
    public function removeItem(Request $request, int $itemId): CartResource|JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());

        $cartItem = $cart->items()->find($itemId);

        if (!$cartItem) {
            return response()->json([
                'message' => 'Cart item not found.',
            ], 404);
        }

        $cartItem->delete();
        $cart->touch();

        return new CartResource($cart->fresh()->load('items.product.images'));
    }

    /**
     * Clear the entire cart.
     *
     * @requirement CART-019 DELETE /api/v1/cart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());

        $cart->items()->delete();
        $cart->touch();

        return response()->json([
            'message' => 'Cart cleared successfully.',
        ]);
    }

    /**
     * Validate cart items for checkout.
     *
     * @requirement CART-013 Validate stock on checkout
     * @requirement CART-019 POST /api/v1/cart/validate
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validate(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());
        $cart->load('items.product');

        $issues = [];
        $validItems = [];

        foreach ($cart->items as $item) {
            $product = $item->product;

            // Check if product is still active
            if (!$product || !$product->is_active) {
                $issues[] = [
                    'item_id' => $item->id,
                    'type' => 'unavailable',
                    'message' => "Product '{$product?->name}' is no longer available.",
                ];
                continue;
            }

            // Check stock
            if ($product->stock_quantity < $item->quantity) {
                $issues[] = [
                    'item_id' => $item->id,
                    'type' => 'insufficient_stock',
                    'message' => "Only {$product->stock_quantity}kg of '{$product->name}' available.",
                    'available' => $product->stock_quantity,
                    'requested' => $item->quantity,
                ];
                continue;
            }

            // Check price changes
            if ($item->unit_price != $product->price) {
                $issues[] = [
                    'item_id' => $item->id,
                    'type' => 'price_changed',
                    'message' => "Price for '{$product->name}' has changed from \${$item->unit_price} to \${$product->price}.",
                    'old_price' => $item->unit_price,
                    'new_price' => $product->price,
                ];
            }

            $validItems[] = $item->id;
        }

        return response()->json([
            'valid' => count($issues) === 0,
            'issues' => $issues,
            'valid_items' => $validItems,
            'total_items' => $cart->items->count(),
        ]);
    }

    /**
     * Sync localStorage cart with database on login.
     *
     * @requirement CART-011 Sync cart to database for logged-in users
     * @requirement CART-019 POST /api/v1/cart/sync
     *
     * @param SyncCartRequest $request
     * @return CartResource
     */
    public function sync(SyncCartRequest $request): CartResource
    {
        $cart = $this->getOrCreateCart($request->user());

        $localItems = $request->items ?? [];

        DB::transaction(function () use ($cart, $localItems) {
            foreach ($localItems as $localItem) {
                $product = Product::find($localItem['product_id']);

                if (!$product || !$product->is_active) {
                    continue;
                }

                $quantity = (float) $localItem['quantity'];

                // Validate quantity against stock
                if ($product->stock_quantity < $quantity) {
                    $quantity = $product->stock_quantity;
                }

                if ($quantity <= 0) {
                    continue;
                }

                // Check if product already in cart
                $existingItem = $cart->items()->where('product_id', $product->id)->first();

                if ($existingItem) {
                    // Keep the higher quantity (merge strategy)
                    $newQuantity = max($existingItem->quantity, $quantity);

                    if ($product->stock_quantity < $newQuantity) {
                        $newQuantity = $product->stock_quantity;
                    }

                    $existingItem->update([
                        'quantity' => $newQuantity,
                        'unit_price' => $product->price,
                    ]);
                } else {
                    $cart->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                    ]);
                }
            }

            $cart->touch();
        });

        return new CartResource($cart->fresh()->load('items.product.images'));
    }

    /**
     * Move cart item to wishlist.
     *
     * @requirement CART-022 Implement "Save for Later"
     *
     * @param Request $request
     * @param int $itemId
     * @return JsonResponse
     */
    public function saveForLater(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());

        $cartItem = $cart->items()->find($itemId);

        if (!$cartItem) {
            return response()->json([
                'message' => 'Cart item not found.',
            ], 404);
        }

        $user = $request->user();
        $product = $cartItem->product;

        // Add to wishlist if not already there
        if (!$user->wishlistItems()->where('product_id', $product->id)->exists()) {
            $user->wishlistItems()->create([
                'product_id' => $product->id,
            ]);
        }

        // Remove from cart
        $cartItem->delete();
        $cart->touch();

        return response()->json([
            'message' => 'Item moved to wishlist.',
        ]);
    }

    /**
     * Get or create a cart for the user.
     *
     * @param \App\Models\User $user
     * @return Cart
     */
    private function getOrCreateCart($user): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['user_id' => $user->id]
        );
    }
}
