<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactMessageController extends Controller
{
    /**
     * Store a new contact message.
     * Includes honeypot spam detection.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'honeypot' => 'nullable|string', // Bot trap - should be empty
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Add IP address and user agent for tracking
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();

        $message = ContactMessage::create($data);

        // Check if message is spam (honeypot filled)
        if ($message->isSpam()) {
            // Silently accept but don't notify admin
            return response()->json([
                'success' => true,
                'message' => 'Thank you for your message. We will get back to you soon.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your message. We will get back to you soon.',
            'data' => ['id' => $message->id],
        ], 201);
    }

    /**
     * Get all messages (Admin/Staff only).
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContactMessage::notSpam()
            ->with('repliedBy:id,name')
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $messages = $query->paginate(20);

        return response()->json(['success' => true, 'data' => $messages]);
    }

    /**
     * Get a single message.
     */
    public function show(ContactMessage $message): JsonResponse
    {
        $message->markAsRead();
        $message->load('repliedBy:id,name');

        return response()->json(['success' => true, 'data' => $message]);
    }

    /**
     * Update message status.
     */
    public function update(Request $request, ContactMessage $message): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:new,read,replied,archived',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $message->update([
            'status' => $request->status,
            'replied_by' => $request->status === 'replied' ? auth()->id() : null,
            'replied_at' => $request->status === 'replied' ? now() : null,
        ]);

        return response()->json(['success' => true, 'message' => 'Message updated successfully', 'data' => $message]);
    }

    /**
     * Delete a message.
     */
    public function destroy(ContactMessage $message): JsonResponse
    {
        $message->delete();
        return response()->json(['success' => true, 'message' => 'Message deleted successfully']);
    }

    /**
     * Get message statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => ContactMessage::notSpam()->count(),
            'new' => ContactMessage::notSpam()->unread()->count(),
            'read' => ContactMessage::notSpam()->where('status', 'read')->count(),
            'replied' => ContactMessage::notSpam()->where('status', 'replied')->count(),
            'archived' => ContactMessage::notSpam()->where('status', 'archived')->count(),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }
}
