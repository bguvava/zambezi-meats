<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\CheckEmailRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Authentication Controller
 *
 * Handles all authentication-related endpoints for the SPA.
 * Uses Laravel Sanctum with cookie-based authentication.
 */
class AuthController extends Controller
{
    /**
     * Register a new customer account.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Welcome to Zambezi Meats!',
            'data' => [
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    /**
     * Authenticate user and create session.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (!Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], $validated['remember'] ?? false)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Log the user out and invalidate session.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Logout from the web guard (session-based authentication)
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        // Invalidate the session and regenerate CSRF token
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Refresh the session to prevent timeout.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Session refreshed.',
            'data' => [
                'expires_at' => now()->addMinutes(config('session.lifetime'))->toIso8601String(),
            ],
        ]);
    }

    /**
     * Unlock locked session with password verification.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function unlock(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|regex:/^\S+$/',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        // Regenerate session to prevent session fixation
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Session unlocked successfully.',
            'data' => [
                'expires_at' => now()->addMinutes(config('session.lifetime'))->toIso8601String(),
            ],
        ]);
    }

    /**
     * Send password reset link.
     *
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    /**
     * Reset password with token.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    /**
     * Check if email is available for registration.
     *
     * @param CheckEmailRequest $request
     * @return JsonResponse
     */
    public function checkEmail(CheckEmailRequest $request): JsonResponse
    {
        // Case-insensitive email check
        $exists = User::whereRaw('LOWER(email) = ?', [strtolower($request->input('email'))])->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'available' => !$exists,
            ],
        ]);
    }
}
