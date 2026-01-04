<?php

declare(strict_types=1);

/**
 * Zambezi Meats API Routes
 *
 * This file defines all API routes for the application.
 * Routes are versioned under /api/v1/ prefix.
 *
 * @requirement PROJ-INIT-003 Configure API versioning folder structure
 * @requirement AUTH-001 to AUTH-015 Authentication endpoints
 * @requirement SHOP-021 to SHOP-024 Shop API endpoints
 * @requirement CART-019 Cart API endpoints
 */

use App\Http\Controllers\Api\ContactMessageController;
use App\Http\Controllers\Api\NewsletterSubscriptionController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BlogController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DeliveryController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\StaffController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Version 1 Routes
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api/v1/
| Authentication uses Laravel Sanctum with cookie-based SPA auth.
|
*/

Route::prefix('v1')->group(function () {
    // Health check endpoints (DEP-029)
    Route::get('/health', [HealthController::class, 'check'])
        ->name('api.v1.health');
    Route::get('/health/detailed', [HealthController::class, 'detailed'])
        ->name('api.v1.health.detailed');
    Route::get('/health/ready', [HealthController::class, 'ready'])
        ->name('api.v1.health.ready');
    Route::get('/health/live', [HealthController::class, 'live'])
        ->name('api.v1.health.live');

    // Public routes (no authentication required)
    Route::prefix('public')->group(function () {
        // Categories
        Route::get('/categories', function () {
            return response()->json(['message' => 'Categories endpoint - Coming in Part 3']);
        })->name('api.v1.public.categories');

        // Products
        Route::get('/products', function () {
            return response()->json(['message' => 'Products endpoint - Coming in Part 3']);
        })->name('api.v1.public.products');

        // Delivery zones for landing page
        Route::get('/delivery-zones', function () {
            return response()->json(['message' => 'Delivery zones endpoint - Coming in Part 3']);
        })->name('api.v1.public.delivery-zones');

        // Featured products for landing page
        Route::get('/featured-products', function () {
            return response()->json(['message' => 'Featured products endpoint - Coming in Part 3']);
        })->name('api.v1.public.featured-products');

        // Testimonials for landing page
        Route::get('/testimonials', function () {
            return response()->json(['message' => 'Testimonials endpoint - Coming in Part 3']);
        })->name('api.v1.public.testimonials');

        // Check email availability
        Route::post('/check-email', [AuthController::class, 'checkEmail'])
            ->name('api.v1.public.check-email');

        // Exchange rates for multi-currency support
        Route::get('/exchange-rates', function () {
            $rates = \App\Models\CurrencyRate::all()->mapWithKeys(function ($rate) {
                return [$rate->target_currency => (float) $rate->rate];
            })->toArray();

            // Ensure default rates exist
            if (empty($rates)) {
                $rates = [
                    'AUD' => 1.0,
                    'USD' => 0.65,
                ];
            }

            // Always include AUD as base
            $rates['AUD'] = 1.0;

            return response()->json([
                'success' => true,
                'base' => 'AUD',
                'rates' => $rates,
                'updated_at' => now()->toIso8601String(),
            ]);
        })->name('api.v1.public.exchange-rates');
    });

    /*
    |--------------------------------------------------------------------------
    | Contact & Newsletter Routes (Public)
    |--------------------------------------------------------------------------
    |
    | @requirement ISSUE-004 Contact us form and newsletter subscriptions
    */
    Route::post('/contact', [ContactMessageController::class, 'store'])
        ->name('api.v1.contact.store');

    Route::post('/newsletter/subscribe', [NewsletterSubscriptionController::class, 'subscribe'])
        ->name('api.v1.newsletter.subscribe');

    Route::get('/newsletter/unsubscribe/{token}', [NewsletterSubscriptionController::class, 'unsubscribe'])
        ->name('api.v1.newsletter.unsubscribe');

    /*
    |--------------------------------------------------------------------------
    | Blog Routes (Public)
    |--------------------------------------------------------------------------
    |
    | Public blog routes for listing and viewing blog posts
    */
    Route::prefix('blog')->group(function () {
        Route::get('/', [BlogController::class, 'index'])
            ->name('api.v1.blog.index');

        Route::get('/{slug}', [BlogController::class, 'show'])
            ->name('api.v1.blog.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Shop Routes (Public - No Authentication Required)
    |--------------------------------------------------------------------------
    |
    | @requirement SHOP-021 Products API with filtering/search
    | @requirement SHOP-022 Product detail API
    | @requirement SHOP-023 Categories API
    | @requirement SHOP-024 Product search API
    */
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])
            ->name('api.v1.products.index');

        Route::get('/featured', [ProductController::class, 'featured'])
            ->name('api.v1.products.featured');

        Route::get('/search', [ProductController::class, 'search'])
            ->name('api.v1.products.search');

        Route::get('/{slug}', [ProductController::class, 'show'])
            ->name('api.v1.products.show');

        Route::get('/{slug}/related', [ProductController::class, 'related'])
            ->name('api.v1.products.related');
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])
            ->name('api.v1.categories.index');

        Route::get('/{slug}', [CategoryController::class, 'show'])
            ->name('api.v1.categories.show');

        Route::get('/{slug}/products', [CategoryController::class, 'products'])
            ->name('api.v1.categories.products');
    });

    /*
    |--------------------------------------------------------------------------
    | Checkout Routes (Public - No Authentication Required for validation)
    |--------------------------------------------------------------------------
    |
    | @requirement CHK-006 Address validation
    | @requirement CHK-007 Delivery fee calculation
    | @requirement CHK-015 Promo code validation
    | @requirement CHK-027 Checkout API endpoints
    */
    Route::prefix('checkout')->group(function () {
        // Public checkout endpoints (validation)
        Route::post('/validate-address', [CheckoutController::class, 'validateAddress'])
            ->name('api.v1.checkout.validate-address');

        Route::post('/calculate-fee', [CheckoutController::class, 'calculateFee'])
            ->name('api.v1.checkout.calculate-fee');

        Route::post('/validate-promo', [CheckoutController::class, 'validatePromo'])
            ->name('api.v1.checkout.validate-promo');

        // Get available payment methods
        Route::get('/payment-methods', [PaymentController::class, 'getMethods'])
            ->name('api.v1.checkout.payment-methods');
    });

    /*
    |--------------------------------------------------------------------------
    | Webhook Routes (No Authentication - Verified by signature)
    |--------------------------------------------------------------------------
    |
    | @requirement CHK-028 Payment webhook handlers
    */
    Route::prefix('webhooks')->group(function () {
        Route::post('/stripe', [WebhookController::class, 'handleStripe'])
            ->name('api.v1.webhooks.stripe');

        Route::post('/paypal', [WebhookController::class, 'handlePayPal'])
            ->name('api.v1.webhooks.paypal');
    });

    /*
    |--------------------------------------------------------------------------
    | Authentication Routes
    |--------------------------------------------------------------------------
    |
    | @requirement AUTH-001 POST /api/v1/auth/register - Registration endpoint
    | @requirement AUTH-002 POST /api/v1/auth/login - Login endpoint
    | @requirement AUTH-003 POST /api/v1/auth/logout - Logout endpoint
    | @requirement AUTH-006 POST /api/v1/auth/forgot-password - Password reset
    | @requirement AUTH-007 POST /api/v1/auth/reset-password - Reset with token
    | @requirement AUTH-009 GET /api/v1/auth/user - Get authenticated user
    | @requirement AUTH-005 POST /api/v1/auth/refresh - Session refresh
    */
    Route::prefix('auth')->group(function () {
        // Guest routes (unauthenticated users) - removed guest middleware to allow session
        Route::post('/register', [AuthController::class, 'register'])
            ->name('api.v1.auth.register');

        Route::post('/login', [AuthController::class, 'login'])
            ->name('api.v1.auth.login');

        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
            ->name('api.v1.auth.forgot-password');

        Route::post('/reset-password', [AuthController::class, 'resetPassword'])
            ->name('api.v1.auth.reset-password');

        // Authenticated routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])
                ->name('api.v1.auth.logout');

            Route::get('/user', [AuthController::class, 'user'])
                ->name('api.v1.auth.user');

            Route::post('/refresh', [AuthController::class, 'refresh'])
                ->name('api.v1.auth.refresh');

            Route::post('/unlock', [AuthController::class, 'unlock'])
                ->name('api.v1.auth.unlock');
        });
    });

    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {
        // Get authenticated user (alias)
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('api.v1.user');

        // User activity history (users can view their own)
        Route::get('/users/{user}/activity', [UserController::class, 'getActivityHistory'])
            ->name('api.v1.user.activity');

        /*
        |--------------------------------------------------------------------------
        | Profile Routes
        |--------------------------------------------------------------------------
        */
        Route::prefix('profile')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\ProfileController::class, 'show'])
                ->name('api.v1.profile.show');

            Route::put('/', [\App\Http\Controllers\Api\V1\ProfileController::class, 'update'])
                ->name('api.v1.profile.update');

            Route::post('/avatar', [\App\Http\Controllers\Api\V1\ProfileController::class, 'uploadAvatar'])
                ->name('api.v1.profile.upload-avatar');

            Route::delete('/avatar', [\App\Http\Controllers\Api\V1\ProfileController::class, 'deleteAvatar'])
                ->name('api.v1.profile.delete-avatar');

            Route::post('/change-password', [\App\Http\Controllers\Api\V1\ProfileController::class, 'changePassword'])
                ->name('api.v1.profile.change-password');
        });

        /*
        |--------------------------------------------------------------------------
        | Notification Routes
        |--------------------------------------------------------------------------
        |
        | @requirement NOTIF-001 List user notifications with pagination
        | @requirement NOTIF-002 Mark notifications as read
        | @requirement NOTIF-003 Delete notifications
        | @requirement NOTIF-004 Get unread count
        */
        Route::prefix('notifications')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\NotificationController::class, 'index'])
                ->name('api.v1.notifications.index');

            Route::get('/unread-count', [\App\Http\Controllers\Api\V1\NotificationController::class, 'unreadCount'])
                ->name('api.v1.notifications.unread-count');

            Route::post('/{notification}/read', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead'])
                ->name('api.v1.notifications.read');

            Route::post('/read-all', [\App\Http\Controllers\Api\V1\NotificationController::class, 'markAllAsRead'])
                ->name('api.v1.notifications.read-all');

            Route::delete('/{notification}', [\App\Http\Controllers\Api\V1\NotificationController::class, 'destroy'])
                ->name('api.v1.notifications.destroy');

            Route::delete('/read/all', [\App\Http\Controllers\Api\V1\NotificationController::class, 'deleteAllRead'])
                ->name('api.v1.notifications.delete-all-read');
        });

        /*
        |--------------------------------------------------------------------------
        | Cart Routes (Authenticated Users)
        |--------------------------------------------------------------------------
        |
        | @requirement CART-019 Cart API CRUD endpoints
        | @requirement CART-011 Sync cart on login
        | @requirement CART-012 Stock validation
        | @requirement CART-022 Save for later (wishlist)
        */
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'show'])
                ->name('api.v1.cart.show');

            Route::post('/items', [CartController::class, 'addItem'])
                ->name('api.v1.cart.add');

            Route::put('/items/{itemId}', [CartController::class, 'updateItem'])
                ->name('api.v1.cart.update');

            Route::delete('/items/{itemId}', [CartController::class, 'removeItem'])
                ->name('api.v1.cart.remove');

            Route::delete('/', [CartController::class, 'clear'])
                ->name('api.v1.cart.clear');

            Route::post('/validate', [CartController::class, 'validate'])
                ->name('api.v1.cart.validate');

            Route::post('/sync', [CartController::class, 'sync'])
                ->name('api.v1.cart.sync');

            Route::post('/items/{itemId}/save-for-later', [CartController::class, 'saveForLater'])
                ->name('api.v1.cart.save-for-later');
        });

        /*
        |--------------------------------------------------------------------------
        | Checkout Routes (Authenticated Users)
        |--------------------------------------------------------------------------
        |
        | @requirement CHK-025 Create order
        | @requirement CHK-009 to CHK-012 Payment processing
        */
        Route::prefix('checkout')->group(function () {
            // Get checkout session data
            Route::get('/session', [CheckoutController::class, 'getSession'])
                ->name('api.v1.checkout.session');

            // Create order
            Route::post('/create-order', [CheckoutController::class, 'createOrder'])
                ->name('api.v1.checkout.create-order');

            // Payment processing
            Route::post('/payment/stripe', [PaymentController::class, 'processStripe'])
                ->name('api.v1.checkout.payment.stripe');

            Route::post('/payment/stripe/confirm', [PaymentController::class, 'confirmStripe'])
                ->name('api.v1.checkout.payment.stripe.confirm');

            Route::post('/payment/paypal', [PaymentController::class, 'processPayPal'])
                ->name('api.v1.checkout.payment.paypal');

            Route::post('/payment/paypal/confirm', [PaymentController::class, 'confirmPayPal'])
                ->name('api.v1.checkout.payment.paypal.confirm');

            Route::post('/payment/afterpay', [PaymentController::class, 'processAfterpay'])
                ->name('api.v1.checkout.payment.afterpay');

            Route::post('/payment/afterpay/confirm', [PaymentController::class, 'confirmAfterpay'])
                ->name('api.v1.checkout.payment.afterpay.confirm');

            Route::post('/payment/cod', [PaymentController::class, 'processCod'])
                ->name('api.v1.checkout.payment.cod');

            // Payment status
            Route::get('/payment/status/{orderId}', [PaymentController::class, 'getStatus'])
                ->name('api.v1.checkout.payment.status');
        });

        /*
        |--------------------------------------------------------------------------
        | Customer Routes
        |--------------------------------------------------------------------------
        |
        | @requirement CUST-001 to CUST-023 Customer dashboard features
        */
        Route::middleware('role:customer,admin')->prefix('customer')->group(function () {
            // Dashboard
            Route::get('/dashboard', [CustomerController::class, 'getDashboard'])
                ->name('api.v1.customer.dashboard');

            // Profile
            Route::get('/profile', [CustomerController::class, 'getProfile'])
                ->name('api.v1.customer.profile');
            Route::put('/profile', [CustomerController::class, 'updateProfile'])
                ->name('api.v1.customer.profile.update');
            Route::put('/password', [CustomerController::class, 'changePassword'])
                ->name('api.v1.customer.password');

            // Orders
            Route::get('/orders', [CustomerController::class, 'getOrders'])
                ->name('api.v1.customer.orders');
            Route::get('/orders/{id}', [CustomerController::class, 'getOrder'])
                ->name('api.v1.customer.orders.show');
            Route::post('/orders/{id}/reorder', [CustomerController::class, 'reorder'])
                ->name('api.v1.customer.orders.reorder');
            Route::get('/orders/{id}/invoice', [CustomerController::class, 'downloadInvoice'])
                ->name('api.v1.customer.orders.invoice');

            // Addresses
            Route::get('/addresses', [CustomerController::class, 'getAddresses'])
                ->name('api.v1.customer.addresses');
            Route::post('/addresses', [CustomerController::class, 'addAddress'])
                ->name('api.v1.customer.addresses.store');
            Route::put('/addresses/{id}', [CustomerController::class, 'updateAddress'])
                ->name('api.v1.customer.addresses.update');
            Route::delete('/addresses/{id}', [CustomerController::class, 'deleteAddress'])
                ->name('api.v1.customer.addresses.destroy');

            // Wishlist
            Route::get('/wishlist', [CustomerController::class, 'getWishlist'])
                ->name('api.v1.customer.wishlist');
            Route::post('/wishlist', [CustomerController::class, 'addToWishlist'])
                ->name('api.v1.customer.wishlist.store');
            Route::delete('/wishlist/{productId}', [CustomerController::class, 'removeFromWishlist'])
                ->name('api.v1.customer.wishlist.destroy');

            // Notifications
            Route::get('/notifications', [CustomerController::class, 'getNotifications'])
                ->name('api.v1.customer.notifications');
            Route::put('/notifications/{id}/read', [CustomerController::class, 'markNotificationRead'])
                ->name('api.v1.customer.notifications.read');
            Route::put('/notifications/read-all', [CustomerController::class, 'markAllNotificationsRead'])
                ->name('api.v1.customer.notifications.read-all');

            // Support Tickets
            Route::get('/tickets', [CustomerController::class, 'getTickets'])
                ->name('api.v1.customer.tickets');
            Route::post('/tickets', [CustomerController::class, 'createTicket'])
                ->name('api.v1.customer.tickets.store');
            Route::get('/tickets/{id}', [CustomerController::class, 'getTicket'])
                ->name('api.v1.customer.tickets.show');
            Route::post('/tickets/{id}/reply', [CustomerController::class, 'replyToTicket'])
                ->name('api.v1.customer.tickets.reply');
            Route::delete('/tickets/{id}', [CustomerController::class, 'cancelTicket'])
                ->name('api.v1.customer.tickets.cancel');
        });

        /*
        |--------------------------------------------------------------------------
        | Staff Routes
        |--------------------------------------------------------------------------
        |
        | @requirement STAFF-001 to STAFF-024 Staff dashboard features
        */
        Route::middleware('role:staff,admin')->prefix('staff')->group(function () {
            // Dashboard
            Route::get('/dashboard', [StaffController::class, 'getDashboard'])
                ->name('api.v1.staff.dashboard');

            // Order Queue
            Route::get('/orders', [StaffController::class, 'getOrderQueue'])
                ->name('api.v1.staff.orders');
            Route::get('/orders/{id}', [StaffController::class, 'getOrder'])
                ->name('api.v1.staff.orders.show');
            Route::put('/orders/{id}/status', [StaffController::class, 'updateOrderStatus'])
                ->name('api.v1.staff.orders.status');
            Route::post('/orders/{id}/notes', [StaffController::class, 'addOrderNote'])
                ->name('api.v1.staff.orders.notes');

            // Deliveries
            Route::get('/deliveries/today', [StaffController::class, 'getTodaysDeliveries'])
                ->name('api.v1.staff.deliveries.today');
            Route::put('/orders/{id}/out-for-delivery', [StaffController::class, 'markOutForDelivery'])
                ->name('api.v1.staff.orders.out-for-delivery');

            // Proof of Delivery
            Route::post('/orders/{id}/proof-of-delivery', [StaffController::class, 'uploadProofOfDelivery'])
                ->name('api.v1.staff.orders.pod');
            Route::get('/orders/{id}/proof-of-delivery', [StaffController::class, 'getProofOfDelivery'])
                ->name('api.v1.staff.orders.pod.show');

            // Waste Logging
            Route::post('/waste', [StaffController::class, 'logWaste'])
                ->name('api.v1.staff.waste.store');
            Route::get('/waste', [StaffController::class, 'getWasteLogs'])
                ->name('api.v1.staff.waste');

            // Stock Check
            Route::get('/stock', [StaffController::class, 'getStockCheck'])
                ->name('api.v1.staff.stock');
            Route::put('/stock/{productId}', [StaffController::class, 'updateStock'])
                ->name('api.v1.staff.stock.update');

            // Activity & Performance
            Route::get('/activity', [StaffController::class, 'getActivityLog'])
                ->name('api.v1.staff.activity');
            Route::get('/performance', [StaffController::class, 'getPerformanceStats'])
                ->name('api.v1.staff.performance');

            // Pickups
            Route::get('/pickups/today', [StaffController::class, 'getTodaysPickups'])
                ->name('api.v1.staff.pickups.today');
            Route::put('/orders/{id}/picked-up', [StaffController::class, 'markAsPickedUp'])
                ->name('api.v1.staff.orders.picked-up');

            // Invoices (Read-Only)
            Route::get('/invoices', [StaffController::class, 'getInvoices'])
                ->name('api.v1.staff.invoices.index');
            Route::get('/invoices/stats', [StaffController::class, 'getInvoiceStats'])
                ->name('api.v1.staff.invoices.stats');
            Route::get('/invoices/{id}', [StaffController::class, 'getInvoice'])
                ->name('api.v1.staff.invoices.show');
            Route::get('/invoices/{id}/pdf', [StaffController::class, 'downloadInvoicePDF'])
                ->name('api.v1.staff.invoices.pdf');
        });

        // Admin routes
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            // Dashboard
            Route::get('/dashboard', [AdminController::class, 'getDashboard'])
                ->name('api.v1.admin.dashboard');

            // Order Management
            Route::get('/orders', [AdminController::class, 'getOrders'])
                ->name('api.v1.admin.orders.index');
            Route::get('/orders/{id}', [AdminController::class, 'getOrder'])
                ->name('api.v1.admin.orders.show');
            Route::put('/orders/{id}', [AdminController::class, 'updateOrder'])
                ->name('api.v1.admin.orders.update');
            Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])
                ->name('api.v1.admin.orders.status');
            Route::post('/orders/{id}/assign', [AdminController::class, 'assignOrder'])
                ->name('api.v1.admin.orders.assign');
            Route::post('/orders/{id}/refund', [AdminController::class, 'refundOrder'])
                ->name('api.v1.admin.orders.refund');

            // Invoice Management (Admin)
            Route::get('/invoices', [AdminController::class, 'getInvoices'])
                ->name('api.v1.admin.invoices.index');
            Route::get('/invoices/stats', [AdminController::class, 'getInvoiceStats'])
                ->name('api.v1.admin.invoices.stats');
            Route::get('/invoices/{id}', [AdminController::class, 'getInvoice'])
                ->name('api.v1.admin.invoices.show');
            Route::put('/invoices/{id}/status', [AdminController::class, 'updateInvoiceStatus'])
                ->name('api.v1.admin.invoices.status');
            Route::get('/invoices/{id}/pdf', [AdminController::class, 'downloadInvoicePDF'])
                ->name('api.v1.admin.invoices.pdf');

            // Customer Management (Admin only)
            Route::get('/customers', [AdminController::class, 'getCustomers'])
                ->name('api.v1.admin.customers.index');
            Route::get('/customers/{id}', [AdminController::class, 'getCustomer'])
                ->name('api.v1.admin.customers.show');
            Route::put('/customers/{id}', [AdminController::class, 'updateCustomer'])
                ->name('api.v1.admin.customers.update');

            // Product/Category Delete operations (Admin only)
            Route::delete('/products/{id}', [AdminController::class, 'deleteProduct'])
                ->name('api.v1.admin.products.delete');
            Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory'])
                ->name('api.v1.admin.categories.delete');

            /*
            |--------------------------------------------------------------------------
            | Blog Management Routes (Admin Only)
            |--------------------------------------------------------------------------
            */
            Route::prefix('blog')->group(function () {
                Route::post('/', [BlogController::class, 'store'])
                    ->name('api.v1.admin.blog.store');

                Route::put('/{id}', [BlogController::class, 'update'])
                    ->name('api.v1.admin.blog.update');

                Route::delete('/{id}', [BlogController::class, 'destroy'])
                    ->name('api.v1.admin.blog.destroy');
            });
        });

        // Admin & Staff routes for Product/Category Management
        Route::middleware('role:admin,staff')->prefix('admin')->group(function () {
            // Product Management (Admin & Staff can create/edit)
            Route::get('/products', [AdminController::class, 'getProducts'])
                ->name('api.v1.admin.products.index');
            Route::get('/products/low-stock', [AdminController::class, 'getLowStockProducts'])
                ->name('api.v1.admin.products.low-stock');
            Route::post('/products', [AdminController::class, 'createProduct'])
                ->name('api.v1.admin.products.store');
            Route::get('/products/{id}', [AdminController::class, 'getProduct'])
                ->name('api.v1.admin.products.show');
            Route::put('/products/{id}', [AdminController::class, 'updateProduct'])
                ->name('api.v1.admin.products.update');
            Route::post('/products/{id}/adjust-stock', [AdminController::class, 'adjustStock'])
                ->name('api.v1.admin.products.adjust-stock');
            Route::get('/products/{id}/stock-history', [AdminController::class, 'getStockHistory'])
                ->name('api.v1.admin.products.stock-history');
            Route::post('/products/{id}/images', [AdminController::class, 'uploadProductImages'])
                ->name('api.v1.admin.products.upload-images');
            Route::delete('/products/{id}/images/{imageId}', [AdminController::class, 'deleteProductImage'])
                ->name('api.v1.admin.products.delete-image');
            Route::post('/products/{id}/images/reorder', [AdminController::class, 'reorderProductImages'])
                ->name('api.v1.admin.products.reorder-images');
            Route::post('/products/export', [AdminController::class, 'exportProducts'])
                ->name('api.v1.admin.products.export');

            // Category Management (Admin & Staff can create/edit)
            Route::get('/categories', [AdminController::class, 'getCategories'])
                ->name('api.v1.admin.categories.index');
            Route::post('/categories', [AdminController::class, 'createCategory'])
                ->name('api.v1.admin.categories.store');
            Route::put('/categories/{id}', [AdminController::class, 'updateCategory'])
                ->name('api.v1.admin.categories.update');

            /*
            |--------------------------------------------------------------------------
            | Messages Module Routes (Admin & Staff)
            |--------------------------------------------------------------------------
            |
            | @requirement ISSUE-004 Messages module for admin and staff
            */
            Route::get('/messages', [ContactMessageController::class, 'index'])
                ->name('api.v1.admin.messages.index');
            Route::get('/messages/{message}', [ContactMessageController::class, 'show'])
                ->name('api.v1.admin.messages.show');
            Route::put('/messages/{message}', [ContactMessageController::class, 'update'])
                ->name('api.v1.admin.messages.update');
            Route::delete('/messages/{message}', [ContactMessageController::class, 'destroy'])
                ->name('api.v1.admin.messages.destroy');
            Route::get('/messages-stats', [ContactMessageController::class, 'stats'])
                ->name('api.v1.admin.messages.stats');

            Route::get('/subscriptions', [NewsletterSubscriptionController::class, 'index'])
                ->name('api.v1.admin.subscriptions.index');
            Route::delete('/subscriptions/{subscription}', [NewsletterSubscriptionController::class, 'destroy'])
                ->name('api.v1.admin.subscriptions.destroy');
            Route::get('/subscriptions-stats', [NewsletterSubscriptionController::class, 'stats'])
                ->name('api.v1.admin.subscriptions.stats');

            /*
            |--------------------------------------------------------------------------
            | Support Tickets Management Routes (Admin & Staff)
            |--------------------------------------------------------------------------
            |
            | @requirement MSG-001 Add Tickets/Helpdesk Tab
            */
            Route::get('/tickets', [AdminController::class, 'getTickets'])
                ->name('api.v1.admin.tickets.index');
            Route::get('/tickets/{id}', [AdminController::class, 'getTicket'])
                ->name('api.v1.admin.tickets.show');
            Route::put('/tickets/{id}/status', [AdminController::class, 'updateTicketStatus'])
                ->name('api.v1.admin.tickets.status');
            Route::post('/tickets/{id}/reply', [AdminController::class, 'replyToTicket'])
                ->name('api.v1.admin.tickets.reply');
            Route::delete('/tickets/{id}', [AdminController::class, 'deleteTicket'])
                ->name('api.v1.admin.tickets.delete');
            Route::get('/tickets-stats', [AdminController::class, 'getTicketStats'])
                ->name('api.v1.admin.tickets.stats');
        });

        // Admin-only Service Delete operations
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            /*
            |--------------------------------------------------------------------------
            | User Management Routes
            |--------------------------------------------------------------------------
            |
            | @requirement USER-001 to USER-014 User management
            */
            Route::prefix('users')->group(function () {
                Route::get('/', [UserController::class, 'index'])
                    ->name('api.v1.admin.users.index');
                Route::get('/export', [UserController::class, 'export'])
                    ->name('api.v1.admin.users.export');
                Route::post('/', [UserController::class, 'store'])
                    ->name('api.v1.admin.users.store');
                Route::get('/{user}', [UserController::class, 'show'])
                    ->name('api.v1.admin.users.show');
                Route::put('/{user}', [UserController::class, 'update'])
                    ->name('api.v1.admin.users.update');
                Route::put('/{user}/status', [UserController::class, 'updateStatus'])
                    ->name('api.v1.admin.users.status');
                Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])
                    ->name('api.v1.admin.users.reset-password');
                Route::get('/{user}/activity', [UserController::class, 'getActivityHistory'])
                    ->name('api.v1.admin.users.activity');
            });

            // Staff Management
            Route::get('/staff', [AdminController::class, 'getStaff'])
                ->name('api.v1.admin.staff.index');
            Route::post('/staff', [AdminController::class, 'createStaff'])
                ->name('api.v1.admin.staff.store');
            Route::put('/staff/{id}', [AdminController::class, 'updateStaff'])
                ->name('api.v1.admin.staff.update');
            Route::delete('/staff/{id}', [AdminController::class, 'deleteStaff'])
                ->name('api.v1.admin.staff.delete');
            Route::get('/staff/{id}/activity', [AdminController::class, 'getStaffActivity'])
                ->name('api.v1.admin.staff.activity');

            // Promotion Management
            Route::get('/promotions', [AdminController::class, 'getPromotions'])
                ->name('api.v1.admin.promotions.index');
            Route::post('/promotions', [AdminController::class, 'createPromotion'])
                ->name('api.v1.admin.promotions.store');
            Route::put('/promotions/{id}', [AdminController::class, 'updatePromotion'])
                ->name('api.v1.admin.promotions.update');
            Route::delete('/promotions/{id}', [AdminController::class, 'deletePromotion'])
                ->name('api.v1.admin.promotions.delete');

            // Activity Logs
            Route::get('/logs', [AdminController::class, 'getActivityLogs'])
                ->name('api.v1.admin.logs.index');
            Route::delete('/logs/bulk', [AdminController::class, 'bulkDeleteActivityLogs'])
                ->name('api.v1.admin.logs.bulk-delete');

            /*
            |--------------------------------------------------------------------------
            | Inventory Management Routes
            |--------------------------------------------------------------------------
            |
            | @requirement INV-001 to INV-018 Inventory management
            */
            Route::prefix('inventory')->group(function () {
                Route::get('/dashboard', [InventoryController::class, 'getDashboard'])
                    ->name('api.v1.admin.inventory.dashboard');
                Route::get('/', [InventoryController::class, 'index'])
                    ->name('api.v1.admin.inventory.index');
                Route::get('/history', [InventoryController::class, 'getHistory'])
                    ->name('api.v1.admin.inventory.history');
                Route::get('/low-stock', [InventoryController::class, 'getLowStock'])
                    ->name('api.v1.admin.inventory.low-stock');
                Route::get('/alerts', [InventoryController::class, 'getAlerts'])
                    ->name('api.v1.admin.inventory.alerts');
                Route::get('/report', [InventoryController::class, 'getReport'])
                    ->name('api.v1.admin.inventory.report');
                Route::get('/export', [InventoryController::class, 'export'])
                    ->name('api.v1.admin.inventory.export');
                Route::get('/{productId}', [InventoryController::class, 'show'])
                    ->name('api.v1.admin.inventory.show');
                Route::post('/receive', [InventoryController::class, 'receiveStock'])
                    ->name('api.v1.admin.inventory.receive');
                Route::post('/adjust', [InventoryController::class, 'adjustStock'])
                    ->name('api.v1.admin.inventory.adjust');
                Route::put('/{productId}/min-stock', [InventoryController::class, 'updateMinStock'])
                    ->name('api.v1.admin.inventory.min-stock');
            });

            // Waste Management
            Route::prefix('waste')->group(function () {
                Route::get('/', [InventoryController::class, 'getWaste'])
                    ->name('api.v1.admin.waste.index');
                Route::put('/{id}/approve', [InventoryController::class, 'approveWaste'])
                    ->name('api.v1.admin.waste.approve');
            });

            /*
            |--------------------------------------------------------------------------
            | Delivery Management Routes
            |--------------------------------------------------------------------------
            |
            | @requirement DEL-001 to DEL-019 Delivery management
            */
            Route::prefix('deliveries')->group(function () {
                Route::get('/dashboard', [DeliveryController::class, 'getDashboard'])
                    ->name('api.v1.admin.deliveries.dashboard');
                Route::get('/', [DeliveryController::class, 'index'])
                    ->name('api.v1.admin.deliveries.index');
                Route::get('/map', [DeliveryController::class, 'getMapData'])
                    ->name('api.v1.admin.deliveries.map');
                Route::get('/report', [DeliveryController::class, 'getReport'])
                    ->name('api.v1.admin.deliveries.report');
                Route::get('/export', [DeliveryController::class, 'export'])
                    ->name('api.v1.admin.deliveries.export');
                Route::get('/{id}', [DeliveryController::class, 'show'])
                    ->name('api.v1.admin.deliveries.show');
                Route::put('/{id}/assign', [DeliveryController::class, 'assign'])
                    ->name('api.v1.admin.deliveries.assign');
                Route::get('/{id}/pod', [DeliveryController::class, 'getProofOfDelivery'])
                    ->name('api.v1.admin.deliveries.pod');
                Route::put('/{id}/issue', [DeliveryController::class, 'updateIssue'])
                    ->name('api.v1.admin.deliveries.issue');
            });

            // Delivery Zones
            Route::prefix('delivery-zones')->group(function () {
                Route::get('/', [DeliveryController::class, 'getZones'])
                    ->name('api.v1.admin.delivery-zones.index');
                Route::post('/', [DeliveryController::class, 'createZone'])
                    ->name('api.v1.admin.delivery-zones.store');
                Route::put('/{id}', [DeliveryController::class, 'updateZone'])
                    ->name('api.v1.admin.delivery-zones.update');
                Route::delete('/{id}', [DeliveryController::class, 'deleteZone'])
                    ->name('api.v1.admin.delivery-zones.delete');
            });

            // Delivery Settings
            Route::get('/delivery-settings', [DeliveryController::class, 'getSettings'])
                ->name('api.v1.admin.delivery-settings.show');
            Route::put('/delivery-settings', [DeliveryController::class, 'updateSettings'])
                ->name('api.v1.admin.delivery-settings.update');

            /*
            |--------------------------------------------------------------------------
            | Reports & Analytics Routes
            |--------------------------------------------------------------------------
            |
            | @requirement RPT-001 to RPT-022 Reports & Analytics
            */
            Route::prefix('reports')->group(function () {
                Route::get('/dashboard', [ReportController::class, 'dashboard'])
                    ->name('api.v1.admin.reports.dashboard');
                Route::get('/sales-summary', [ReportController::class, 'salesSummary'])
                    ->name('api.v1.admin.reports.sales-summary');
                Route::get('/revenue', [ReportController::class, 'revenue'])
                    ->name('api.v1.admin.reports.revenue');
                Route::get('/orders', [ReportController::class, 'orders'])
                    ->name('api.v1.admin.reports.orders');
                Route::get('/products', [ReportController::class, 'products'])
                    ->name('api.v1.admin.reports.products');
                Route::get('/categories', [ReportController::class, 'categories'])
                    ->name('api.v1.admin.reports.categories');
                Route::get('/top-products', [ReportController::class, 'topProducts'])
                    ->name('api.v1.admin.reports.top-products');
                Route::get('/low-performing-products', [ReportController::class, 'lowPerformingProducts'])
                    ->name('api.v1.admin.reports.low-performing-products');
                Route::get('/customers', [ReportController::class, 'customers'])
                    ->name('api.v1.admin.reports.customers');
                Route::get('/customer-acquisition', [ReportController::class, 'customerAcquisition'])
                    ->name('api.v1.admin.reports.customer-acquisition');
                Route::get('/staff', [ReportController::class, 'staff'])
                    ->name('api.v1.admin.reports.staff');
                Route::get('/deliveries', [ReportController::class, 'deliveries'])
                    ->name('api.v1.admin.reports.deliveries');
                Route::get('/inventory', [ReportController::class, 'inventory'])
                    ->name('api.v1.admin.reports.inventory');
                Route::get('/financial', [ReportController::class, 'financial'])
                    ->name('api.v1.admin.reports.financial');
                Route::get('/payment-methods', [ReportController::class, 'paymentMethods'])
                    ->name('api.v1.admin.reports.payment-methods');
                Route::get('/{type}/export', [ReportController::class, 'export'])
                    ->name('api.v1.admin.reports.export');
            });

            /*
            |--------------------------------------------------------------------------
            | System Settings Routes
            |--------------------------------------------------------------------------
            |
            | @requirement SET-001 to SET-030 System Settings
            */
            Route::prefix('settings')->group(function () {
                Route::get('/', [SettingsController::class, 'index'])
                    ->name('api.v1.admin.settings.index');
                Route::get('/group/{group}', [SettingsController::class, 'getGroup'])
                    ->name('api.v1.admin.settings.group');
                Route::put('/group/{group}', [SettingsController::class, 'updateGroup'])
                    ->name('api.v1.admin.settings.group.update');
                Route::post('/logo', [SettingsController::class, 'uploadLogo'])
                    ->name('api.v1.admin.settings.logo');
                Route::get('/email-templates', [SettingsController::class, 'getEmailTemplates'])
                    ->name('api.v1.admin.settings.email-templates');
                Route::put('/email-templates/{name}', [SettingsController::class, 'updateEmailTemplate'])
                    ->name('api.v1.admin.settings.email-templates.update');
                Route::post('/email-test', [SettingsController::class, 'sendTestEmail'])
                    ->name('api.v1.admin.settings.email-test');
                Route::post('/export', [SettingsController::class, 'export'])
                    ->name('api.v1.admin.settings.export');
                Route::post('/import', [SettingsController::class, 'import'])
                    ->name('api.v1.admin.settings.import');
                Route::get('/history', [SettingsController::class, 'getHistory'])
                    ->name('api.v1.admin.settings.history');
            });
        });
    });

    // Public settings (no auth required)
    Route::get('/settings/public', [SettingsController::class, 'getPublicSettings'])
        ->name('api.v1.settings.public');
});
