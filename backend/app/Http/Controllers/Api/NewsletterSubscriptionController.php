<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterSubscriptionController extends Controller
{
    /**
     * Subscribe to newsletter.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if already subscribed
        $existing = NewsletterSubscription::where('email', $request->email)->first();

        if ($existing) {
            if ($existing->status === 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already subscribed to our newsletter.',
                ], 409);
            }

            // Resubscribe if previously unsubscribed
            $existing->resubscribe();

            return response()->json([
                'success' => true,
                'message' => 'Welcome back! You have been resubscribed to our newsletter.',
            ]);
        }

        // Create new subscription
        $subscription = NewsletterSubscription::create([
            'email' => $request->email,
            'name' => $request->name,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for subscribing to our newsletter!',
            'data' => ['id' => $subscription->id],
        ], 201);
    }

    /**
     * Unsubscribe from newsletter.
     */
    public function unsubscribe(string $token): JsonResponse
    {
        $subscription = NewsletterSubscription::where('unsubscribe_token', $token)->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid unsubscribe link.',
            ], 404);
        }

        if ($subscription->status === 'unsubscribed') {
            return response()->json([
                'success' => false,
                'message' => 'This email is already unsubscribed.',
            ], 409);
        }

        $subscription->unsubscribe();

        return response()->json([
            'success' => true,
            'message' => 'You have been unsubscribed from our newsletter.',
        ]);
    }

    /**
     * Get all subscriptions (Admin/Staff only).
     */
    public function index(Request $request): JsonResponse
    {
        $query = NewsletterSubscription::orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->paginate(20);

        return response()->json(['success' => true, 'data' => $subscriptions]);
    }

    /**
     * Delete a subscription.
     */
    public function destroy(NewsletterSubscription $subscription): JsonResponse
    {
        $subscription->delete();
        return response()->json(['success' => true, 'message' => 'Subscription deleted successfully']);
    }

    /**
     * Get subscription statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => NewsletterSubscription::count(),
            'active' => NewsletterSubscription::active()->count(),
            'unsubscribed' => NewsletterSubscription::unsubscribed()->count(),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }
}
