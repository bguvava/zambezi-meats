<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\UserExportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;

/**
 * User management controller.
 *
 * @requirement USER-014 Create users API endpoints
 */
class UserController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of users.
     *
     * @requirement USER-001 Create users listing page
     * @requirement USER-002 Implement user search
     * @requirement USER-003 Implement user filters (max 3)
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->status($request->input('status'));
        }

        // Order by creation date (newest first)
        $query->orderBy('created_at', 'desc');

        // Paginate results (15 per page as per requirement)
        $users = $query->paginate(15);

        return UserResource::collection($users);
    }

    /**
     * Store a newly created user.
     *
     * @requirement USER-004 Create new user form
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Set default status
        $validated['status'] = User::STATUS_ACTIVE;
        $validated['is_active'] = true;

        $user = User::create($validated);

        // Log activity
        ActivityLog::log('User created', $user, null, $validated);

        return response()->json([
            'message' => 'User created successfully',
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Display the specified user.
     *
     * @requirement USER-001 Create users listing page
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Update the specified user.
     *
     * @requirement USER-005 Create edit user form
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validated();

        // Hash password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        // Log activity
        ActivityLog::log('User updated', $user, $user->getOriginal(), $validated);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Note: Delete operation is not implemented as per requirement USER-006.
     * Users can only be suspended or deactivated, not deleted.
     */

    /**
     * Update user status.
     *
     * @requirement USER-006 Implement status change functionality
     * @requirement USER-010 Prevent admin self-deletion
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $this->authorize('changeStatus', $user);

        $request->validate([
            'status' => ['required', 'in:active,suspended,inactive'],
        ]);

        $oldStatus = $user->status;
        $newStatus = $request->input('status');

        $user->update([
            'status' => $newStatus,
            'is_active' => $newStatus === User::STATUS_ACTIVE,
        ]);

        // Log activity
        ActivityLog::log(
            "User status changed from {$oldStatus} to {$newStatus}",
            $user,
            ['status' => $oldStatus],
            ['status' => $newStatus]
        );

        return response()->json([
            'message' => 'User status updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Reset user password.
     *
     * @requirement USER-007 Implement password reset by admin
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $this->authorize('resetPassword', $user);

        // Generate new password reset token
        $token = \Illuminate\Support\Str::random(64);

        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send password reset email
        $user->sendPasswordResetNotification($token);

        // Log activity
        ActivityLog::log('Password reset requested by admin', $user);

        return response()->json([
            'message' => 'Password reset email sent successfully',
        ]);
    }

    /**
     * Get user activity history.
     *
     * @requirement USER-008 Create user activity history view
     */
    public function getActivityHistory(Request $request, User $user): JsonResponse
    {
        $this->authorize('viewActivity', $user);

        $activities = ActivityLog::byUser($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $activities,
        ]);
    }

    /**
     * Export users to PDF.
     *
     * @requirement USER-009 Implement user export to PDF
     */
    public function export(Request $request, UserExportService $exportService): \Illuminate\Http\Response
    {
        $this->authorize('viewAny', User::class);

        // Log activity
        ActivityLog::log('Users exported to PDF');

        return $exportService->exportToPdf($request);
    }
}
