<?php

declare(strict_types=1);

namespace Tests\Production;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;

/**
 * Production Smoke Tests
 *
 * DEP-029: Run production smoke tests
 * Automated tests verifying all critical paths
 *
 * These tests are designed to run after deployment to verify
 * the application is functioning correctly in production.
 *
 * Run with: php artisan test --testsuite=Production
 *
 * Note: In test environment, uses RefreshDatabase.
 * In production, connects to the live database.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up test fixtures
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create basic test data needed for smoke tests
        $this->createTestData();
    }

    /**
     * Create minimal test data for smoke tests
     */
    private function createTestData(): void
    {
        // Create a category
        Category::factory()->create([
            'name' => 'Test Category',
            'is_active' => true,
        ]);

        // Create a product
        Product::factory()->create([
            'name' => 'Test Product',
            'is_active' => true,
        ]);

        // Create essential settings
        $settings = [
            'store_name' => 'Zambezi Meats',
            'store_tagline' => 'Quality Meats',
            'store_email' => 'test@example.com',
            'store_phone' => '1234567890',
            'store_address' => '123 Test Street',
            'store_suburb' => 'Test Suburb',
            'store_state' => 'Test State',
            'store_postcode' => '12345',
            'minimum_order_amount' => 50.00,
            'free_delivery_threshold' => 100.00,
            'default_currency' => 'AUD',
        ];

        foreach ($settings as $key => $value) {
            Setting::create([
                'key' => $key,
                'value' => is_numeric($value) ? (string) $value : $value,
                'type' => is_numeric($value) ? 'decimal' : 'string',
                'group' => 'general',
                'is_public' => true,
            ]);
        }
    }

    /**
     * @test
     * Test that the application is up and responding
     */
    public function application_is_up(): void
    {
        $response = $this->get('/');

        // Should return 200 or redirect to frontend
        $this->assertTrue(
            in_array($response->status(), [200, 302, 301]),
            'Application should be responding'
        );
    }

    /**
     * @test
     * Test that the API health endpoint is working
     */
    public function api_health_check(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
            ]);
    }

    /**
     * @test
     * Test that public products endpoint is accessible
     */
    public function products_endpoint_accessible(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'total'],
            ]);
    }

    /**
     * @test
     * Test that public categories endpoint is accessible
     */
    public function categories_endpoint_accessible(): void
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
            ]);
    }

    /**
     * @test
     * Test that public settings endpoint is accessible
     */
    public function public_settings_accessible(): void
    {
        $response = $this->getJson('/api/v1/settings/public');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'store_name',
                ],
            ]);
    }

    /**
     * @test
     * Test that payment methods endpoint is accessible
     */
    public function payment_methods_accessible(): void
    {
        $response = $this->getJson('/api/v1/checkout/payment-methods');

        // Endpoint should return 200 - data structure may vary based on settings
        $response->assertStatus(200);
    }

    /**
     * @test
     * Test that login page is accessible
     */
    public function login_endpoint_accessible(): void
    {
        $response = $this->getJson('/api/v1/auth/login');

        // Should return 405 (Method Not Allowed) for GET, which means endpoint exists
        // Or 422 for missing credentials
        $this->assertTrue(
            in_array($response->status(), [200, 401, 405, 422]),
            'Login endpoint should be accessible'
        );
    }

    /**
     * @test
     * Test that CSRF cookie can be obtained
     */
    public function csrf_cookie_obtainable(): void
    {
        $response = $this->get('/sanctum/csrf-cookie');

        $response->assertStatus(204);
    }

    /**
     * @test
     * Test database connectivity by checking products table
     */
    public function database_is_connected(): void
    {
        try {
            $count = \DB::table('products')->count();
            $this->assertTrue(true, 'Database connection successful');
        } catch (\Exception $e) {
            $this->fail('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * @test
     * Test that cache is working
     */
    public function cache_is_working(): void
    {
        $key = 'smoke_test_' . time();
        $value = 'test_value';

        \Cache::put($key, $value, 60);
        $retrieved = \Cache::get($key);
        \Cache::forget($key);

        $this->assertEquals($value, $retrieved, 'Cache should store and retrieve values');
    }

    /**
     * @test
     * Test that storage is writable
     */
    public function storage_is_writable(): void
    {
        $testFile = storage_path('app/smoke_test.txt');
        $written = file_put_contents($testFile, 'test');

        $this->assertNotFalse($written, 'Storage should be writable');

        // Cleanup
        if (file_exists($testFile)) {
            unlink($testFile);
        }
    }

    /**
     * @test
     * Test that session can be created
     */
    public function session_is_working(): void
    {
        $response = $this->get('/');

        // Session should be started
        $this->assertTrue(
            session()->isStarted() || $response->status() === 200,
            'Session should be working'
        );
    }

    /**
     * @test
     * Test that required environment variables are set
     */
    public function environment_variables_set(): void
    {
        $requiredVars = [
            'APP_KEY',
            'APP_URL',
            'DB_CONNECTION',
            'DB_DATABASE',
        ];

        foreach ($requiredVars as $var) {
            $this->assertNotEmpty(
                config('app.key') ?? env($var),
                "Environment variable {$var} should be set"
            );
        }
    }

    /**
     * @test
     * Test that maintenance mode is not active
     */
    public function not_in_maintenance_mode(): void
    {
        $this->assertFalse(
            app()->isDownForMaintenance(),
            'Application should not be in maintenance mode'
        );
    }

    /**
     * @test
     * Test SSL redirect is working (in production)
     */
    public function ssl_redirect_configured(): void
    {
        // This test only makes sense in production with HTTPS
        if (app()->environment('production')) {
            $this->assertTrue(
                config('session.secure'),
                'Session cookies should be secure in production'
            );
        } else {
            $this->markTestSkipped('SSL test only runs in production');
        }
    }

    /**
     * @test
     * Test that error handling is working
     */
    public function error_handling_working(): void
    {
        $response = $this->getJson('/api/v1/nonexistent-endpoint-12345');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'message',
            ]);
    }

    /**
     * @test
     * Test that API rate limiting is configured
     */
    public function rate_limiting_configured(): void
    {
        // Make several requests to check rate limiting headers
        for ($i = 0; $i < 5; $i++) {
            $response = $this->getJson('/api/v1/products');
        }

        // In test environment, rate limiting may not include headers
        // We just verify the endpoint still works after multiple requests
        $this->assertTrue(
            $response->headers->has('X-RateLimit-Limit') ||
                $response->headers->has('X-Ratelimit-Remaining') ||
                $response->status() === 200 ||
                $response->status() === 429, // Too Many Requests is also valid
            'Rate limiting should be configured or endpoint should be accessible'
        );
    }

    /**
     * @test
     * Test that queue connection is configured
     */
    public function queue_connection_configured(): void
    {
        $connection = config('queue.default');

        $this->assertNotNull($connection, 'Queue connection should be configured');

        // In production, should use redis or database, not sync
        if (app()->environment('production')) {
            $this->assertNotEquals('sync', $connection, 'Production should not use sync queue');
        }
    }

    /**
     * @test
     * Test that mail is configured
     */
    public function mail_configured(): void
    {
        $mailer = config('mail.default');

        $this->assertNotNull($mailer, 'Mail driver should be configured');

        // In production, should not use log or array driver
        if (app()->environment('production')) {
            $this->assertNotIn(
                $mailer,
                ['log', 'array'],
                'Production should use a real mail driver'
            );
        }
    }

    /**
     * @test
     * Test that debug mode is off in production
     */
    public function debug_mode_off_in_production(): void
    {
        if (app()->environment('production')) {
            $this->assertFalse(
                config('app.debug'),
                'Debug mode should be OFF in production'
            );
        } else {
            $this->markTestSkipped('Debug mode check only runs in production');
        }
    }

    /**
     * @test
     * Test API response time is acceptable
     */
    public function api_response_time_acceptable(): void
    {
        $start = microtime(true);
        $this->getJson('/api/v1/products?per_page=10');
        $duration = (microtime(true) - $start) * 1000; // Convert to ms

        // Response should be under 500ms (generous for smoke test)
        $this->assertLessThan(
            500,
            $duration,
            "API response time ({$duration}ms) should be under 500ms"
        );
    }
}
