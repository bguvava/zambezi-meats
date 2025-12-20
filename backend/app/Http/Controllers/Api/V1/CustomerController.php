<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AddressResource;
use App\Http\Resources\Api\V1\NotificationResource;
use App\Http\Resources\Api\V1\OrderResource;
use App\Http\Resources\Api\V1\SupportTicketResource;
use App\Http\Resources\Api\V1\WishlistResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Customer Controller
 *
 * Handles all customer dashboard endpoints.
 *
 * @requirement CUST-001 to CUST-023 Customer dashboard features
 */
class CustomerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Profile Management
    |--------------------------------------------------------------------------
    |
    | @requirement CUST-008 Create profile management page
    | @requirement CUST-009 Implement password change
    | @requirement CUST-019 Implement currency preference
    */

    /**
     * Get customer profile.
     *
     * @requirement CUST-008 Profile management
     */
    public function getProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'currency_preference' => $user->currency_preference,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update customer profile.
     *
     * @requirement CUST-008 Profile management
     * @requirement CUST-019 Currency preference
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'currency_preference' => ['sometimes', 'string', 'in:AUD,USD'],
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'currency_preference' => $user->currency_preference,
            ],
        ]);
    }

    /**
     * Change customer password.
     *
     * @requirement CUST-009 Implement password change
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Order Management
    |--------------------------------------------------------------------------
    |
    | @requirement CUST-003 Create order history page
    | @requirement CUST-004 Create order detail view
    | @requirement CUST-005 Implement order status timeline
    | @requirement CUST-006 Create "Reorder" functionality
    | @requirement CUST-007 Implement order filtering
    | @requirement CUST-020 Create order invoice download
    */

    /**
     * Get customer orders.
     *
     * @requirement CUST-003 Order history
     * @requirement CUST-007 Order filtering
     */
    public function getOrders(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Order::with(['items.product', 'address', 'payment'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $orders = $query->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'orders' => OrderResource::collection($orders),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get single order details.
     *
     * @requirement CUST-004 Order detail view
     * @requirement CUST-005 Order status timeline
     */
    public function getOrder(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $order = Order::with([
            'items.product',
            'address',
            'payment',
            'statusHistory',
            'deliveryProof',
        ])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Reorder a past order.
     *
     * @requirement CUST-006 Create "Reorder" functionality
     */
    public function reorder(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $order = Order::with('items.product')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // Get or create cart
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $addedItems = [];
        $unavailableItems = [];

        foreach ($order->items as $item) {
            $product = $item->product;

            if (!$product || !$product->is_active) {
                $unavailableItems[] = $item->product_name ?? 'Unknown product';
                continue;
            }

            // Check stock
            $quantityToAdd = min((int) $item->quantity, $product->stock);

            if ($quantityToAdd <= 0) {
                $unavailableItems[] = $product->name;
                continue;
            }

            // Add or update cart item
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            // Get current price (sale price if available, otherwise regular price)
            $unitPrice = $product->sale_price_aud ?? $product->price_aud;

            if ($cartItem) {
                $newQuantity = min($cartItem->quantity + $quantityToAdd, $product->stock);
                $cartItem->update(['quantity' => $newQuantity, 'unit_price' => $unitPrice]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantityToAdd,
                    'unit_price' => $unitPrice,
                ]);
            }

            $addedItems[] = $product->name;
        }

        return response()->json([
            'success' => true,
            'message' => count($addedItems) > 0
                ? count($addedItems) . ' item(s) added to cart.'
                : 'No items could be added to cart.',
            'added_items' => $addedItems,
            'unavailable_items' => $unavailableItems,
        ]);
    }

    /**
     * Download order invoice as PDF.
     *
     * @requirement CUST-020 Create order invoice download
     */
    public function downloadInvoice(Request $request, int $id): mixed
    {
        $user = $request->user();

        $order = Order::with(['items.product', 'address', 'user'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // Generate invoice data
        $invoiceData = [
            'order' => $order,
            'company' => [
                'name' => 'Zambezi Meats',
                'address' => '6/1053 Old Princes Highway, Engadine, NSW 2233',
                'phone' => '(02) XXXX XXXX',
                'email' => 'info@zambezimeats.com.au',
                'abn' => 'XX XXX XXX XXX',
            ],
        ];

        // Check if DomPDF is available
        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('invoices.order', $invoiceData);
            return $pdf->download('invoice-' . $order->order_number . '.pdf');
        }

        // Fallback: Return invoice data as JSON
        return response()->json([
            'success' => true,
            'invoice' => [
                'order_number' => $order->order_number,
                'date' => $order->created_at->format('d/m/Y'),
                'customer' => [
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                    'address' => $order->address?->full_address,
                ],
                'items' => $order->items->map(fn($item) => [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total_price,
                ]),
                'subtotal' => $order->subtotal,
                'delivery_fee' => $order->delivery_fee,
                'discount' => $order->discount,
                'total' => $order->total,
                'currency' => $order->currency,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Address Management
    |--------------------------------------------------------------------------
    |
    | @requirement CUST-010 Create address management page
    | @requirement CUST-011 Create add/edit address modal
    */

    /**
     * Get customer addresses.
     *
     * @requirement CUST-010 Address management
     */
    public function getAddresses(Request $request): JsonResponse
    {
        $user = $request->user();

        $addresses = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();

        return response()->json([
            'success' => true,
            'addresses' => AddressResource::collection($addresses),
        ]);
    }

    /**
     * Add a new address.
     *
     * @requirement CUST-011 Add address
     */
    public function addAddress(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'street' => ['required', 'string', 'max:255'],
            'suburb' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:50'],
            'postcode' => ['required', 'string', 'regex:/^\d{4}$/'],
            'country' => ['sometimes', 'string', 'max:50'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        // If setting as default, unset other defaults
        if (!empty($validated['is_default'])) {
            Address::where('user_id', $user->id)->update(['is_default' => false]);
        }

        $address = Address::create([
            'user_id' => $user->id,
            'label' => $validated['label'],
            'street' => $validated['street'],
            'suburb' => $validated['suburb'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postcode' => $validated['postcode'],
            'country' => $validated['country'] ?? 'Australia',
            'is_default' => $validated['is_default'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully.',
            'address' => new AddressResource($address),
        ], 201);
    }

    /**
     * Update an address.
     *
     * @requirement CUST-011 Edit address
     */
    public function updateAddress(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $address = Address::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'label' => ['sometimes', 'string', 'max:50'],
            'street' => ['sometimes', 'string', 'max:255'],
            'suburb' => ['nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'string', 'max:100'],
            'state' => ['sometimes', 'string', 'max:50'],
            'postcode' => ['sometimes', 'string', 'regex:/^\d{4}$/'],
            'country' => ['sometimes', 'string', 'max:50'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        // If setting as default, unset other defaults
        if (!empty($validated['is_default'])) {
            Address::where('user_id', $user->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully.',
            'address' => new AddressResource($address),
        ]);
    }

    /**
     * Delete an address.
     *
     * @requirement CUST-010 Address management
     */
    public function deleteAddress(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $address = Address::where('user_id', $user->id)->findOrFail($id);

        // Check if address is used in any orders
        if ($address->orders()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete address that has been used in orders.',
            ], 422);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Wishlist Management
    |--------------------------------------------------------------------------
    |
    | @requirement CUST-012 Create wishlist page
    | @requirement CUST-013 Implement wishlist to cart
    */

    /**
     * Get customer wishlist.
     *
     * @requirement CUST-012 Wishlist page
     */
    public function getWishlist(Request $request): JsonResponse
    {
        $user = $request->user();

        $wishlists = Wishlist::with('product')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'wishlist' => WishlistResource::collection($wishlists),
            'count' => $wishlists->count(),
        ]);
    }

    /**
     * Add product to wishlist.
     *
     * @requirement CUST-012 Wishlist management
     */
    public function addToWishlist(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        // Check if already in wishlist
        $exists = Wishlist::where('user_id', $user->id)
            ->where('product_id', $validated['product_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist.',
            ], 422);
        }

        $wishlist = Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $validated['product_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist.',
            'wishlist' => new WishlistResource($wishlist->load('product')),
        ], 201);
    }

    /**
     * Remove product from wishlist.
     *
     * @requirement CUST-012 Wishlist management
     */
    public function removeFromWishlist(Request $request, int $productId): JsonResponse
    {
        $user = $request->user();

        $deleted = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | @requirement CUST-014 Create notifications page
    | @requirement CUST-015 Implement notification preferences
    */

    /**
     * Get customer notifications.
     *
     * @requirement CUST-014 Notifications page
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('unread_only') && $request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        $notifications = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'notifications' => NotificationResource::collection($notifications),
            'unread_count' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Mark notification as read.
     *
     * @requirement CUST-014 Notifications management
     */
    public function markNotificationRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $notification = Notification::where('user_id', $user->id)->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Support Tickets
    |--------------------------------------------------------------------------
    |
    | @requirement CUST-016 Create support/help page
    | @requirement CUST-017 Create support ticket submission
    | @requirement CUST-018 View support ticket history
    */

    /**
     * Get customer support tickets.
     *
     * @requirement CUST-018 View support ticket history
     */
    public function getTickets(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = SupportTicket::with('replies.user')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $tickets = $query->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'tickets' => SupportTicketResource::collection($tickets),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * Create a support ticket.
     *
     * @requirement CUST-017 Create support ticket submission
     */
    public function createTicket(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'priority' => ['sometimes', 'string', 'in:low,medium,high,urgent'],
        ]);

        // If order_id provided, verify it belongs to user
        if (!empty($validated['order_id'])) {
            $orderBelongsToUser = Order::where('id', $validated['order_id'])
                ->where('user_id', $user->id)
                ->exists();

            if (!$orderBelongsToUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.',
                ], 404);
            }
        }

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'order_id' => $validated['order_id'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => SupportTicket::STATUS_OPEN,
            'priority' => $validated['priority'] ?? SupportTicket::PRIORITY_MEDIUM,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully.',
            'ticket' => new SupportTicketResource($ticket),
        ], 201);
    }

    /**
     * Get single ticket details.
     *
     * @requirement CUST-018 View ticket details
     */
    public function getTicket(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $ticket = SupportTicket::with(['replies.user', 'order'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'ticket' => new SupportTicketResource($ticket),
        ]);
    }

    /**
     * Add reply to ticket.
     */
    public function replyToTicket(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $ticket = SupportTicket::where('user_id', $user->id)->findOrFail($id);

        // Only block replies to fully closed tickets - resolved tickets can be reopened
        if ($ticket->status === SupportTicket::STATUS_CLOSED) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot reply to a closed ticket.',
            ], 422);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);

        // Update ticket status if it was resolved
        if ($ticket->status === SupportTicket::STATUS_RESOLVED) {
            $ticket->update(['status' => SupportTicket::STATUS_OPEN]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reply added successfully.',
            'reply' => [
                'id' => $reply->id,
                'message' => $reply->message,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'created_at' => $reply->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard Overview
    |--------------------------------------------------------------------------
    |
    | @requirement CUST-001 Create customer dashboard layout
    | @requirement CUST-002 Create dashboard overview page
    */

    /**
     * Get dashboard overview stats.
     *
     * @requirement CUST-002 Dashboard overview
     */
    public function getDashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalOrders = Order::where('user_id', $user->id)->count();

        $pendingDeliveries = Order::where('user_id', $user->id)
            ->whereIn('status', [
                Order::STATUS_CONFIRMED,
                Order::STATUS_PROCESSING,
                Order::STATUS_READY,
                Order::STATUS_OUT_FOR_DELIVERY,
            ])
            ->count();

        $wishlistCount = Wishlist::where('user_id', $user->id)->count();

        $openTickets = SupportTicket::where('user_id', $user->id)
            ->whereIn('status', [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_IN_PROGRESS])
            ->count();

        $recentOrders = Order::with('items')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'dashboard' => [
                'stats' => [
                    'total_orders' => $totalOrders,
                    'pending_deliveries' => $pendingDeliveries,
                    'wishlist_count' => $wishlistCount,
                    'open_tickets' => $openTickets,
                    'unread_notifications' => $unreadNotifications,
                ],
                'recent_orders' => OrderResource::collection($recentOrders),
            ],
        ]);
    }
}
