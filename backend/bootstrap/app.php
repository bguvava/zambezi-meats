<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\SessionTimeout;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        // Explicitly add CORS and session middleware to API routes
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);

        // Register middleware aliases
        $middleware->alias([
            'role' => EnsureRole::class,
            'session.timeout' => SessionTimeout::class,
        ]);

        // Apply session timeout to web routes
        $middleware->web(append: [
            SessionTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 404 for API routes
        $exceptions->render(function (NotFoundHttpException $e) {
            if (request()->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                    'error' => [
                        'code' => 'NOT_FOUND',
                    ],
                ], 404);
            }
        });

        // Handle validation errors for API routes
        $exceptions->render(function (ValidationException $e) {
            if (request()->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'errors' => $e->errors(),
                    ],
                ], 422);
            }
        });
    })->create();
