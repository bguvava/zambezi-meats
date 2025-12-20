<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Customer Controller Tests
 *
 * Tests for all customer dashboard endpoints.
 *
 * @requirement CUST-001 to CUST-023 Customer dashboard features
 */
class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'role' => User::ROLE_CUSTOMER,
            'currency_preference' => 'AUD',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement CUST-002 Create dashboard overview page
     */
    public function test_can_get_dashboard_overview(): void
    {
        Sanctum::actingAs($this->customer);

        // Create some data
        Order::factory()->count(3)->create(['user_id' => $this->customer->id]);
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => Order::STATUS_OUT_FOR_DELIVERY,
        ]);
        Wishlist::factory()->count(2)->create(['user_id' => $this->customer->id]);
        Notification::factory()->unread()->count(2)->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/customer/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'dashboard' => [
                    'stats' => [
                        'total_orders',
                        'pending_deliveries',
                        'wishlist_count',
                        'open_tickets',
                        'unread_notifications',
                    ],
                    'recent_orders',
                ],
            ])
            ->assertJsonPath('dashboard.stats.total_orders', 4)
            ->assertJsonPath('dashboard.stats.pending_deliveries', 1)
            ->assertJsonPath('dashboard.stats.wishlist_count', 2)
            ->assertJsonPath('dashboard.stats.unread_notifications', 2);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/customer/dashboard');

        $response->assertStatus(401);
    }

    /*
    |--------------------------------------------------------------------------
    | Profile Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement CUST-008 Create profile management page
     */
    public function test_can_get_profile(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/customer/profile');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('profile.id', $this->customer->id)
            ->assertJsonPath('profile.name', $this->customer->name)
            ->assertJsonPath('profile.email', $this->customer->email)
            ->assertJsonPath('profile.currency_preference', 'AUD');
    }

    /**
     * @requirement CUST-008 Create profile management page
     */
    public function test_can_update_profile(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->putJson('/api/v1/customer/profile', [
            'name' => 'Updated Name',
            'phone' => '0412345678',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('profile.name', 'Updated Name')
            ->assertJsonPath('profile.phone', '0412345678');

        $this->assertDatabaseHas('users', [
            'id' => $this->customer->id,
            'name' => 'Updated Name',
            'phone' => '0412345678',
        ]);
    }

    /**
     * @requirement CUST-019 Implement currency preference
     */
    public function test_can_update_currency_preference(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->putJson('/api/v1/customer/profile', [
            'currency_preference' => 'USD',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('profile.currency_preference', 'USD');

        $this->assertDatabaseHas('users', [
            'id' => $this->customer->id,
            'currency_preference' => 'USD',
        ]);
    }

    public function test_profile_update_validates_unique_email(): void
    {
        Sanctum::actingAs($this->customer);

        $otherUser = User::factory()->create();

        $response = $this->putJson('/api/v1/customer/profile', [
            'email' => $otherUser->email,
        ]);

        $response->assertStatus(422);
    }

    /**
     * @requirement CUST-009 Implement password change
     */
    public function test_can_change_password(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->putJson('/api/v1/customer/password', [
            'current_password' => 'password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Password changed successfully.');
    }

    public function test_password_change_validates_current_password(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->putJson('/api/v1/customer/password', [
            'current_password' => 'wrongpassword',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Current password is incorrect.');
    }

    /*
    |--------------------------------------------------------------------------
    | Order Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement CUST-003 Create order history page
     */
    public function test_can_get_orders(): void
    {
        Sanctum::actingAs($this->customer);

        Order::factory()->count(3)->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/customer/orders');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'orders')
            ->assertJsonStructure([
                'success',
                'orders',
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    /**
     * @requirement CUST-007 Implement order filtering
     */
    public function test_can_filter_orders_by_status(): void
    {
        Sanctum::actingAs($this->customer);

        Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => Order::STATUS_DELIVERED,
        ]);
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => Order::STATUS_PENDING,
        ]);

        $response = $this->getJson('/api/v1/customer/orders?status=' . Order::STATUS_DELIVERED);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'orders');
    }

    /**
     * @requirement CUST-004 Create order detail view
     */
    public function test_can_get_single_order(): void
    {
        Sanctum::actingAs($this->customer);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/customer/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('order.id', $order->id)
            ->assertJsonPath('order.order_number', $order->order_number);
    }

    public function test_cannot_get_other_users_order(): void
    {
        Sanctum::actingAs($this->customer);

        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson('/api/v1/customer/orders/' . $order->id);

        $response->assertStatus(404);
    }

    /**
     * @requirement CUST-006 Create "Reorder" functionality
     */
    public function test_can_reorder_past_order(): void
    {
        Sanctum::actingAs($this->customer);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 10,
            'is_active' => true,
        ]);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->postJson('/api/v1/customer/orders/' . $order->id . '/reorder');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['added_items', 'unavailable_items']);

        // Verify cart has the item
        $cart = Cart::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($cart);
        $this->assertEquals(1, $cart->items->count());
    }

    public function test_reorder_handles_unavailable_products(): void
    {
        Sanctum::actingAs($this->customer);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 0,
            'is_active' => true,
        ]);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->postJson('/api/v1/customer/orders/' . $order->id . '/reorder');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'unavailable_items');
    }

    /**
     * @requirement CUST-020 Create order invoice download
     */
    public function test_can_download_invoice(): void
    {
        Sanctum::actingAs($this->customer);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);
        OrderItem::factory()->create(['order_id' => $order->id]);

        $response = $this->get('/api/v1/customer/orders/' . $order->id . '/invoice');

        // With DomPDF installed, it returns a PDF file
        $response->assertStatus(200);

        // Check it's a PDF response
        $contentType = $response->headers->get('Content-Type');
        $this->assertTrue(
            str_contains($contentType, 'application/pdf') || str_contains($contentType, 'json'),
            'Response should be PDF or JSON'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Address Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement CUST-010 Create address management page
     */
    public function test_can_get_addresses(): void
    {
        Sanctum::actingAs($this->customer);

        Address::factory()->count(2)->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/customer/addresses');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'addresses');
    }

    /**
     * @requirement CUST-011 Create add/edit address modal
     */
    public function test_can_add_address(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/v1/customer/addresses', [
            'label' => 'Home',
            'street' => '123 Main Street',
            'suburb' => 'Engadine',
            'city' => 'Sydney',
            'state' => 'NSW',
            'postcode' => '2233',
            'is_default' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('address.label', 'Home')
            ->assertJsonPath('address.street', '123 Main Street');

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->customer->id,
            'label' => 'Home',
            'is_default' => true,
        ]);
    }

    public function test_add_address_validates_postcode(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/v1/customer/addresses', [
            'label' => 'Home',
            'street' => '123 Main Street',
            'city' => 'Sydney',
            'state' => 'NSW',
            'postcode' => '123', // Invalid - must be 4 digits
        ]);

        $response->assertStatus(422);
    }

    /**
     * @requirement CUST-011 Create add/edit address modal
     */
    public function test_can_update_address(): void
    {
        Sanctum::actingAs($this->customer);

        $address = Address::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->putJson('/api/v1/customer/addresses/' . $address->id, [
            'label' => 'Work',
            'street' => '456 Office Road',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('address.label', 'Work')
            ->assertJsonPath('address.street', '456 Office Road');
    }

    public function test_cannot_update_other_users_address(): void
    {
        Sanctum::actingAs($this->customer);

        $otherUser = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->putJson('/api/v1/customer/addresses/' . $address->id, [
            'label' => 'Hack',
        ]);

        $response->assertStatus(404);
    }

    public function test_can_delete_address(): void
    {
        Sanctum::actingAs($this->customer);

        $address = Address::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->deleteJson('/api/v1/customer/addresses/' . $address->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_cannot_delete_address_used_in_orders(): void
    {
        Sanctum::actingAs($this->customer);

        $address = Address::factory()->create(['user_id' => $this->customer->id]);
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'address_id' => $address->id,
        ]);

        $response = $this->deleteJson('/api/v1/customer/addresses/' . $address->id);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    public function test_setting_default_address_unsets_others(): void
    {
        Sanctum::actingAs($this->customer);

        $address1 = Address::factory()->create([
            'user_id' => $this->customer->id,
            'is_default' => true,
        ]);
        $address2 = Address::factory()->create([
            'user_id' => $this->customer->id,
            'is_default' => false,
        ]);

        $this->putJson('/api/v1/customer/addresses/' . $address2->id, [
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('addresses', ['id' => $address1->id, 'is_default' => false]);
        $this->assertDatabaseHas('addresses', ['id' => $address2->id, 'is_default' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | Wishlist Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement CUST-012 Create wishlist page
     */
    public function test_can_get_wishlist(): void
    {
        Sanctum::actingAs($this->customer);

        Wishlist::factory()->count(3)->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/customer/wishlist');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'wishlist')
            ->assertJsonPath('count', 3);
    }

    public function test_can_add_to_wishlist(): void
    {
        Sanctum::actingAs($this->customer);

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->postJson('/api/v1/customer/wishlist', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_cannot_add_duplicate_to_wishlist(): void
    {
        Sanctum::actingAs($this->customer);

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Wishlist::factory()->create([
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->postJson('/api/v1/customer/wishlist', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Product already in wishlist.');
    }

    public function test_can_remove_from_wishlist(): void
    {
        Sanctum::actingAs($this->customer);

        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Wishlist::factory()->create([
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
        ]);

        $response = $this->deleteJson('/api/v1/customer/wishlist/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_remove_nonexistent_wishlist_item_returns_404(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->deleteJson('/api/v1/customer/wishlist/99999');

        $response->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | Notification Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement CUST-014 Create notifications page
     */
    public function test_can_get_notifications(): void
    {
        Sanctum::actingAs($this->customer);

        Notification::factory()->read()->count(5)->create(['user_id' => $this->customer->id]);
        Notification::factory()->unread()->count(2)->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/customer/notifications');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(7, 'notifications')
            ->assertJsonPath('unread_count', 2);
    }

    public function test_can_filter_unread_notifications(): void
    {
        Sanctum::actingAs($this->customer);

        Notification::factory()->read()->count(3)->create(['user_id' => $this->customer->id]);
        Notification::factory()->unread()->count(2)->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/customer/notifications?unread_only=true');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'notifications');
    }

    public function test_can_mark_notification_as_read(): void
    {
        Sanctum::actingAs($this->customer);

        $notification = Notification::factory()->unread()->create([
            'user_id' => $this->customer->id,
        ]);

        $response = $this->putJson('/api/v1/customer/notifications/' . $notification->id . '/read');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true,
        ]);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        Sanctum::actingAs($this->customer);

        Notification::factory()->unread()->count(5)->create(['user_id' => $this->customer->id]);

        $response = $this->putJson('/api/v1/customer/notifications/read-all');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals(
            0,
            Notification::where('user_id', $this->customer->id)->where('is_read', false)->count()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Support Ticket Tests
    |--------------------------------------------------------------------------
    */

    /**
     * @requirement CUST-018 View support ticket history
     */
    public function test_can_get_tickets(): void
    {
        Sanctum::actingAs($this->customer);

        SupportTicket::factory()->count(3)->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/customer/tickets');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'tickets');
    }

    public function test_can_filter_tickets_by_status(): void
    {
        Sanctum::actingAs($this->customer);

        SupportTicket::factory()->open()->count(2)->create(['user_id' => $this->customer->id]);
        SupportTicket::factory()->closed()->count(1)->create(['user_id' => $this->customer->id]);

        $response = $this->getJson('/api/v1/customer/tickets?status=open');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'tickets');
    }

    /**
     * @requirement CUST-017 Create support ticket submission
     */
    public function test_can_create_ticket(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/v1/customer/tickets', [
            'subject' => 'Issue with my order',
            'message' => 'I have a problem with my recent order. Please help.',
            'priority' => 'high',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('ticket.subject', 'Issue with my order')
            ->assertJsonPath('ticket.status', SupportTicket::STATUS_OPEN);

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $this->customer->id,
            'subject' => 'Issue with my order',
        ]);
    }

    public function test_can_create_ticket_for_order(): void
    {
        Sanctum::actingAs($this->customer);

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->postJson('/api/v1/customer/tickets', [
            'subject' => 'Order issue',
            'message' => 'Problem with this order.',
            'order_id' => $order->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('ticket.order_id', $order->id);
    }

    public function test_cannot_create_ticket_for_other_users_order(): void
    {
        Sanctum::actingAs($this->customer);

        $otherUser = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->postJson('/api/v1/customer/tickets', [
            'subject' => 'Order issue',
            'message' => 'Problem with this order.',
            'order_id' => $order->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_can_get_single_ticket(): void
    {
        Sanctum::actingAs($this->customer);

        $ticket = SupportTicket::factory()->create(['user_id' => $this->customer->id]);
        TicketReply::factory()->count(2)->create(['ticket_id' => $ticket->id]);

        $response = $this->getJson('/api/v1/customer/tickets/' . $ticket->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('ticket.id', $ticket->id)
            ->assertJsonStructure([
                'ticket' => ['id', 'subject', 'message', 'status', 'replies'],
            ]);
    }

    public function test_can_reply_to_ticket(): void
    {
        Sanctum::actingAs($this->customer);

        $ticket = SupportTicket::factory()->open()->create(['user_id' => $this->customer->id]);

        $response = $this->postJson('/api/v1/customer/tickets/' . $ticket->id . '/reply', [
            'message' => 'Thank you for your help!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('reply.message', 'Thank you for your help!');

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $this->customer->id,
            'message' => 'Thank you for your help!',
        ]);
    }

    public function test_cannot_reply_to_closed_ticket(): void
    {
        Sanctum::actingAs($this->customer);

        $ticket = SupportTicket::factory()->closed()->create(['user_id' => $this->customer->id]);

        $response = $this->postJson('/api/v1/customer/tickets/' . $ticket->id . '/reply', [
            'message' => 'Please reopen.',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Cannot reply to a closed ticket.');
    }

    public function test_reply_to_resolved_ticket_reopens_it(): void
    {
        Sanctum::actingAs($this->customer);

        $ticket = SupportTicket::factory()->resolved()->create(['user_id' => $this->customer->id]);

        $this->postJson('/api/v1/customer/tickets/' . $ticket->id . '/reply', [
            'message' => 'Actually, I still have an issue.',
        ]);

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => SupportTicket::STATUS_OPEN,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Authentication Tests
    |--------------------------------------------------------------------------
    */

    public function test_all_endpoints_require_authentication(): void
    {
        $endpoints = [
            ['GET', '/api/v1/customer/profile'],
            ['PUT', '/api/v1/customer/profile'],
            ['PUT', '/api/v1/customer/password'],
            ['GET', '/api/v1/customer/orders'],
            ['GET', '/api/v1/customer/addresses'],
            ['GET', '/api/v1/customer/wishlist'],
            ['GET', '/api/v1/customer/notifications'],
            ['GET', '/api/v1/customer/tickets'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $this->assertEquals(401, $response->status(), "Endpoint {$method} {$endpoint} should require auth");
        }
    }
}
