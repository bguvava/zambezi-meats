<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Test suite for SettingsController.
 *
 * @requirement SET-030 Write settings module tests
 */
class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    // ==================== Authentication Tests ====================

    /** @test */
    public function test_settings_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/admin/settings');

        $response->assertStatus(401);
    }

    /** @test */
    public function test_settings_index_requires_admin_role(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/admin/settings');

        $response->assertStatus(403);
    }

    // ==================== Get All Settings Tests ====================

    /** @test */
    public function test_admin_can_get_all_settings(): void
    {
        Sanctum::actingAs($this->admin);

        Setting::setValue('store_name', 'Zambezi Meats', 'string', 'store');
        Setting::setValue('minimum_order_amount', '50', 'float', 'delivery');

        $response = $this->getJson('/api/v1/admin/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'settings',
                    'grouped',
                    'groups',
                ],
            ])
            ->assertJsonPath('data.settings.store_name', 'Zambezi Meats');
    }

    /** @test */
    public function test_settings_are_grouped_by_category(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/settings');

        $response->assertStatus(200);

        $groups = $response->json('data.groups');

        $this->assertContains('store', $groups);
        $this->assertContains('payment', $groups);
        $this->assertContains('delivery', $groups);
        $this->assertContains('security', $groups);
    }

    // ==================== Get Settings Group Tests ====================

    /** @test */
    public function test_admin_can_get_settings_group(): void
    {
        Sanctum::actingAs($this->admin);

        Setting::setValue('store_name', 'Zambezi Meats', 'string', 'store');
        Setting::setValue('store_phone', '1234567890', 'string', 'store');

        $response = $this->getJson('/api/v1/admin/settings/group/store');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'group',
                    'settings',
                    'schema',
                ],
            ])
            ->assertJsonPath('data.group', 'store')
            ->assertJsonPath('data.settings.store_name', 'Zambezi Meats');
    }

    /** @test */
    public function test_get_invalid_settings_group_returns_404(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/settings/group/invalid');

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    /** @test */
    public function test_settings_group_includes_schema(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/settings/group/store');

        $response->assertStatus(200);

        $schema = $response->json('data.schema');

        $this->assertArrayHasKey('store_name', $schema);
        $this->assertArrayHasKey('type', $schema['store_name']);
        $this->assertArrayHasKey('required', $schema['store_name']);
    }

    // ==================== Update Settings Group Tests ====================

    /** @test */
    public function test_admin_can_update_settings_group(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/settings/group/store', [
            'store_name' => 'Updated Zambezi Meats',
            'store_tagline' => 'Premium Quality',
            'store_address' => '123 Test Street',
            'store_suburb' => 'Testville',
            'store_state' => 'NSW',
            'store_postcode' => '2000',
            'store_phone' => '0400000000',
            'store_email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Settings updated successfully');

        $this->assertEquals('Updated Zambezi Meats', Setting::getValue('store_name'));
    }

    /** @test */
    public function test_update_settings_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/settings/group/store', [
            'store_name' => '', // Required field
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function test_update_settings_validates_types(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/settings/group/security', [
            'session_timeout_minutes' => 'not-an-integer',
            'password_min_length' => 8,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function test_update_settings_logs_changes(): void
    {
        Sanctum::actingAs($this->admin);

        $this->putJson('/api/v1/admin/settings/group/store', [
            'store_name' => 'New Store Name',
            'store_address' => '456 New Street',
            'store_suburb' => 'Newville',
            'store_state' => 'VIC',
            'store_postcode' => '3000',
            'store_phone' => '0411111111',
            'store_email' => 'new@example.com',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'settings_changed',
            'user_id' => $this->admin->id,
        ]);
    }

    /** @test */
    public function test_update_invalid_settings_group_returns_404(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/settings/group/invalid', [
            'some_setting' => 'value',
        ]);

        $response->assertStatus(404);
    }

    // ==================== Logo Upload Tests ====================

    /** @test */
    public function test_admin_can_upload_store_logo(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->admin);

        // Use create() with image MIME type to avoid GD extension dependency
        $file = UploadedFile::fake()->create('logo.png', 100, 'image/png');

        $response = $this->postJson('/api/v1/admin/settings/logo', [
            'logo' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['logo_url'],
            ]);
    }

    /** @test */
    public function test_logo_upload_validates_file_type(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->admin);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson('/api/v1/admin/settings/logo', [
            'logo' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function test_logo_upload_validates_file_size(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->admin);

        // Use create() with image MIME type and large size to avoid GD extension dependency
        $file = UploadedFile::fake()->create('large-logo.png', 3000, 'image/png'); // 3MB

        $response = $this->postJson('/api/v1/admin/settings/logo', [
            'logo' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function test_logo_upload_requires_file(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/settings/logo', []);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    // ==================== Email Templates Tests ====================

    /** @test */
    public function test_admin_can_get_email_templates(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/settings/email-templates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'templates' => [
                        'order_confirmation',
                        'order_shipped',
                        'order_delivered',
                        'password_reset',
                        'welcome',
                    ],
                    'available_variables',
                ],
            ]);
    }

    /** @test */
    public function test_email_templates_include_subject_and_body(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/v1/admin/settings/email-templates');

        $response->assertStatus(200);

        $template = $response->json('data.templates.order_confirmation');

        $this->assertArrayHasKey('name', $template);
        $this->assertArrayHasKey('subject', $template);
        $this->assertArrayHasKey('body', $template);
    }

    /** @test */
    public function test_admin_can_update_email_template(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/settings/email-templates/order_confirmation', [
            'subject' => 'Custom Order Confirmation - {order_number}',
            'body' => 'Dear {customer_name}, thank you for your order!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.template', 'order_confirmation');
    }

    /** @test */
    public function test_update_email_template_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/settings/email-templates/order_confirmation', [
            'subject' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function test_update_invalid_email_template_returns_404(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/settings/email-templates/invalid_template', [
            'subject' => 'Test',
            'body' => 'Test body',
        ]);

        $response->assertStatus(404);
    }

    // ==================== Test Email Tests ====================

    /** @test */
    public function test_admin_can_send_test_email(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/settings/email-test', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Test email sent successfully');
    }

    /** @test */
    public function test_send_test_email_validates_email(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/settings/email-test', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    /** @test */
    public function test_send_test_email_requires_email(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/settings/email-test', []);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    // ==================== Export/Import Tests ====================

    /** @test */
    public function test_admin_can_export_settings(): void
    {
        Sanctum::actingAs($this->admin);

        Setting::setValue('store_name', 'Zambezi Meats', 'string', 'store');
        Setting::setValue('minimum_order_amount', '100', 'float', 'delivery');

        $response = $this->postJson('/api/v1/admin/settings/export');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'version',
                    'exported_at',
                    'exported_by',
                    'settings',
                ],
            ]);

        $this->assertArrayHasKey('store_name', $response->json('data.settings'));
    }

    /** @test */
    public function test_export_includes_setting_metadata(): void
    {
        Sanctum::actingAs($this->admin);

        Setting::setValue('store_name', 'Test Store', 'string', 'store');

        $response = $this->postJson('/api/v1/admin/settings/export');

        $response->assertStatus(200);

        $setting = $response->json('data.settings.store_name');

        $this->assertArrayHasKey('value', $setting);
        $this->assertArrayHasKey('type', $setting);
        $this->assertArrayHasKey('group', $setting);
    }

    /** @test */
    public function test_admin_can_import_settings(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/settings/import', [
            'settings' => [
                'store_name' => [
                    'value' => 'Imported Store Name',
                    'type' => 'string',
                    'group' => 'store',
                ],
                'minimum_order_amount' => [
                    'value' => 75.0,
                    'type' => 'float',
                    'group' => 'delivery',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.imported', 2)
            ->assertJsonPath('data.skipped', 0);

        $this->assertEquals('Imported Store Name', Setting::getValue('store_name'));
    }

    /** @test */
    public function test_import_skips_invalid_settings(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/settings/import', [
            'settings' => [
                'valid_setting' => [
                    'value' => 'Test',
                    'type' => 'string',
                    'group' => 'store',
                ],
                'invalid_setting' => 'not-proper-format',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.imported', 1)
            ->assertJsonPath('data.skipped', 1);
    }

    /** @test */
    public function test_import_validates_settings_array(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/settings/import', []);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    // ==================== History Tests ====================

    /** @test */
    public function test_admin_can_get_settings_history(): void
    {
        Sanctum::actingAs($this->admin);

        // Create a settings change log
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'settings_changed',
            'model_type' => Setting::class,
            'description' => 'Updated store settings',
            'properties' => ['group' => 'store', 'changes' => []],
        ]);

        $response = $this->getJson('/api/v1/admin/settings/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'total',
                ],
            ]);
    }

    /** @test */
    public function test_history_is_paginated(): void
    {
        Sanctum::actingAs($this->admin);

        // Create multiple history entries
        for ($i = 0; $i < 20; $i++) {
            ActivityLog::create([
                'user_id' => $this->admin->id,
                'action' => 'settings_changed',
                'model_type' => Setting::class,
                'description' => "Change {$i}",
                'properties' => [],
            ]);
        }

        $response = $this->getJson('/api/v1/admin/settings/history?per_page=5');

        $response->assertStatus(200);

        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(20, $response->json('data.total'));
    }

    // ==================== Public Settings Tests ====================

    /** @test */
    public function test_public_settings_accessible_without_auth(): void
    {
        Setting::setValue('store_name', 'Zambezi Meats', 'string', 'store');
        Setting::setValue('store_phone', '1234567890', 'string', 'store');

        $response = $this->getJson('/api/v1/settings/public');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ])
            ->assertJsonPath('data.store_name', 'Zambezi Meats');
    }

    /** @test */
    public function test_public_settings_excludes_sensitive_data(): void
    {
        Setting::setValue('store_name', 'Zambezi Meats', 'string', 'store');
        Setting::setValue('stripe_secret_key', 'sk_test_123', 'string', 'payment');
        Setting::setValue('smtp_password', 'secret123', 'string', 'email');

        $response = $this->getJson('/api/v1/settings/public');

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertArrayNotHasKey('stripe_secret_key', $data);
        $this->assertArrayNotHasKey('smtp_password', $data);
    }

    /** @test */
    public function test_public_settings_includes_store_info(): void
    {
        Setting::setValue('store_name', 'Test Store', 'string', 'store');
        Setting::setValue('store_address', '123 Test St', 'string', 'store');
        Setting::setValue('store_phone', '0400000000', 'string', 'store');

        $response = $this->getJson('/api/v1/settings/public');

        $response->assertStatus(200)
            ->assertJsonPath('data.store_name', 'Test Store')
            ->assertJsonPath('data.store_address', '123 Test St')
            ->assertJsonPath('data.store_phone', '0400000000');
    }

    // ==================== Settings Type Casting Tests ====================

    /** @test */
    public function test_boolean_settings_are_casted_correctly(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/settings/group/features', [
            'enable_wishlist' => true,
            'enable_reviews' => false,
        ]);

        $response->assertStatus(200);

        $this->assertTrue(Setting::getValue('enable_wishlist'));
        $this->assertFalse(Setting::getValue('enable_reviews'));
    }

    /** @test */
    public function test_integer_settings_are_casted_correctly(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/settings/group/security', [
            'session_timeout_minutes' => 10,
            'password_min_length' => 12,
        ]);

        $response->assertStatus(200);

        $this->assertSame(10, Setting::getValue('session_timeout_minutes'));
        $this->assertSame(12, Setting::getValue('password_min_length'));
    }

    /** @test */
    public function test_float_settings_are_casted_correctly(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson('/api/v1/admin/settings/group/delivery', [
            'minimum_order_amount' => 50.50,
            'free_delivery_threshold' => 100.00,
            'default_delivery_fee' => 9.99,
        ]);

        $response->assertStatus(200);

        $this->assertSame(50.50, Setting::getValue('minimum_order_amount'));
        $this->assertSame(100.0, Setting::getValue('free_delivery_threshold'));
    }

    /** @test */
    public function test_json_settings_are_casted_correctly(): void
    {
        Sanctum::actingAs($this->admin);

        $operatingHours = ['open' => '07:00', 'close' => '18:00'];

        $response = $this->putJson('/api/v1/admin/settings/group/operating', [
            'operating_hours_monday' => $operatingHours,
        ]);

        $response->assertStatus(200);

        $value = Setting::getValue('operating_hours_monday');
        $this->assertEquals('07:00', $value['open']);
        $this->assertEquals('18:00', $value['close']);
    }

    // ==================== All Settings Groups Tests ====================

    /** @test */
    public function test_can_access_all_settings_groups(): void
    {
        Sanctum::actingAs($this->admin);

        $groups = [
            'store',
            'operating',
            'payment',
            'email',
            'currency',
            'delivery',
            'security',
            'notifications',
            'features',
            'seo',
            'social',
        ];

        foreach ($groups as $group) {
            $response = $this->getJson("/api/v1/admin/settings/group/{$group}");

            $response->assertStatus(200)
                ->assertJsonPath('data.group', $group);
        }
    }
}
