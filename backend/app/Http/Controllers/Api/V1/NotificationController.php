<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notification Controller
 *
 * Handles user notifications for orders, deliveries, messages, and system alerts.
 *
 * @requirement NOTIF-001 List user notifications with pagination
 * @requirement NOTIF-002 Mark notifications as read
 * @requirement NOTIF-003 Delete notifications
 * @requirement NOTIF-004 Get unread count
 */
class NotificationController extends Controller
{
    /**
     * Get paginated list of user notifications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 50);
        $type = $request->query('type');
        $unreadOnly = $request->query('unread_only', false);

        $query = auth()->user()->notifications()->latest();

        // Filter by type if specified
        if ($type && in_array($type, Notification::types(), true)) {
            $query->ofType($type);
        }

        // Filter unread only
        if ($unreadOnly) {
            $query->unread();
        }

        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Get count of unread notifications
     *
     * @return JsonResponse
     */
    public function unreadCount(): JsonResponse
    {
        $count = auth()->user()->notifications()->unread()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
            ],
        ]);
    }

    /**
     * Mark a notification as read
     *
     * @param Notification $notification
     * @return JsonResponse
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Ensure notification belongs to authenticated user
        if ($notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification->fresh(),
        ]);
    }

    /**
     * Mark all notifications as read
     *
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        $updated = auth()->user()
            ->notifications()
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "Marked {$updated} notifications as read",
            'data' => [
                'updated_count' => $updated,
            ],
        ]);
    }

    /**
     * Delete a notification
     *
     * @param Notification $notification
     * @return JsonResponse
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Ensure notification belongs to authenticated user
        if ($notification->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Delete all read notifications
     *
     * @return JsonResponse
     */
    public function deleteAllRead(): JsonResponse
    {
        $deleted = auth()->user()
            ->notifications()
            ->read()
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} read notifications",
            'data' => [
                'deleted_count' => $deleted,
            ],
        ]);
    }
}
