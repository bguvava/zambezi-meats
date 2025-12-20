<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Health Check Controller
 *
 * Provides endpoints for monitoring application health
 * Used by deployment scripts, load balancers, and monitoring tools
 */
class HealthController extends Controller
{
    /**
     * Basic health check
     *
     * Returns 200 if the application is running
     * Used by load balancers and uptime monitoring
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
        ]);
    }

    /**
     * Detailed health check
     *
     * Checks all critical services (database, cache, etc.)
     * Returns detailed status for each component
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'application' => $this->checkApplication(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        $overallStatus = collect($checks)->every(fn($check) => $check['status'] === 'healthy')
            ? 'healthy'
            : 'degraded';

        $statusCode = $overallStatus === 'healthy' ? 200 : 503;

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
            'environment' => app()->environment(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ], $statusCode);
    }

    /**
     * Check application status
     */
    private function checkApplication(): array
    {
        return [
            'status' => 'healthy',
            'message' => 'Application is running',
            'maintenance_mode' => app()->isDownForMaintenance(),
            'debug_mode' => config('app.debug'),
        ];
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'healthy',
                'message' => 'Database connection successful',
                'connection' => config('database.default'),
                'response_time_ms' => $responseTime,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed',
                'error' => app()->environment('production') ? 'Connection error' : $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache connectivity
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . uniqid();
            $value = 'test_' . time();

            $startTime = microtime(true);
            Cache::put($key, $value, 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($retrieved !== $value) {
                throw new \Exception('Cache read/write mismatch');
            }

            return [
                'status' => 'healthy',
                'message' => 'Cache is working',
                'driver' => config('cache.default'),
                'response_time_ms' => $responseTime,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Cache check failed',
                'driver' => config('cache.default'),
                'error' => app()->environment('production') ? 'Cache error' : $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage writability
     */
    private function checkStorage(): array
    {
        try {
            $testFile = storage_path('app/health_check.txt');
            $testContent = 'health_check_' . time();

            file_put_contents($testFile, $testContent);
            $readContent = file_get_contents($testFile);
            unlink($testFile);

            if ($readContent !== $testContent) {
                throw new \Exception('Storage read/write mismatch');
            }

            return [
                'status' => 'healthy',
                'message' => 'Storage is writable',
                'disk' => config('filesystems.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Storage check failed',
                'error' => app()->environment('production') ? 'Storage error' : $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue connectivity
     */
    private function checkQueue(): array
    {
        $driver = config('queue.default');

        // For sync driver, no external service to check
        if ($driver === 'sync') {
            return [
                'status' => 'healthy',
                'message' => 'Queue is configured (sync driver)',
                'driver' => $driver,
            ];
        }

        try {
            // For Redis-based queue, check Redis connection
            if ($driver === 'redis') {
                $startTime = microtime(true);
                Redis::ping();
                $responseTime = round((microtime(true) - $startTime) * 1000, 2);

                return [
                    'status' => 'healthy',
                    'message' => 'Queue connection successful',
                    'driver' => $driver,
                    'response_time_ms' => $responseTime,
                ];
            }

            // For database queue, check connection
            if ($driver === 'database') {
                DB::table('jobs')->count();

                return [
                    'status' => 'healthy',
                    'message' => 'Queue connection successful',
                    'driver' => $driver,
                ];
            }

            return [
                'status' => 'healthy',
                'message' => 'Queue is configured',
                'driver' => $driver,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Queue check failed',
                'driver' => $driver,
                'error' => app()->environment('production') ? 'Queue error' : $e->getMessage(),
            ];
        }
    }

    /**
     * Readiness check (for Kubernetes/container orchestration)
     *
     * Returns 200 only when the application is ready to receive traffic
     */
    public function ready(): JsonResponse
    {
        // Check if in maintenance mode
        if (app()->isDownForMaintenance()) {
            return response()->json([
                'status' => 'not_ready',
                'reason' => 'maintenance_mode',
            ], 503);
        }

        // Check database
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'not_ready',
                'reason' => 'database_unavailable',
            ], 503);
        }

        return response()->json([
            'status' => 'ready',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Liveness check (for Kubernetes/container orchestration)
     *
     * Returns 200 if the process is alive
     * Very lightweight check, should always succeed if the app is running
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'alive',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
