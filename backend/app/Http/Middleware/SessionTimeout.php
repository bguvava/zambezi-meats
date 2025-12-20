<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Session Timeout Middleware
 *
 * Automatically logs out users after a period of inactivity.
 * Default timeout is 5 minutes as per security requirements.
 *
 * @requirement PROJ-INIT-014 Set up session timeout (5 minutes)
 */
class SessionTimeout
{
    /**
     * Session timeout in minutes.
     */
    protected int $timeout;

    /**
     * Create a new middleware instance.
     */
    public function __construct()
    {
        $this->timeout = (int) config('session.lifetime', 5);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity_time');

            if ($lastActivity !== null) {
                $inactiveTime = time() - $lastActivity;
                $timeoutSeconds = $this->timeout * 60;

                if ($inactiveTime > $timeoutSeconds) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Session expired due to inactivity. Please log in again.',
                            'code' => 'SESSION_EXPIRED',
                        ], 401);
                    }

                    return redirect()->route('login')->with('warning', 'Session expired due to inactivity.');
                }
            }

            session(['last_activity_time' => time()]);
        }

        return $next($request);
    }
}
