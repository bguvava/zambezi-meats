<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Blog Controller
 *
 * Handles blog post operations for public and admin users.
 */
class BlogController extends Controller
{
    /**
     * Display a listing of published blog posts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::published()
            ->with('author:id,name')
            ->latest();

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Pagination
        $perPage = $request->input('per_page', 12);
        $posts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    /**
     * Display a single blog post by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::published()
            ->with('author:id,name,email')
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment views
        $post->incrementViews();

        // Get related posts (same author or similar keywords)
        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where(function ($query) use ($post) {
                $query->where('author_id', $post->author_id);

                // If meta_keywords exist, find similar posts
                if ($post->meta_keywords) {
                    foreach ($post->meta_keywords as $keyword) {
                        $query->orWhereJsonContains('meta_keywords', $keyword);
                    }
                }
            })
            ->latest()
            ->limit(3)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $post,
            'related_posts' => $relatedPosts,
        ]);
    }

    /**
     * Store a new blog post (Admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|array',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['author_id'] = auth()->id();

        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $post = BlogPost::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Blog post created successfully',
            'data' => $post->load('author:id,name'),
        ], 201);
    }

    /**
     * Update a blog post (Admin only).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:blog_posts,slug,' . $id,
            'excerpt' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|array',
            'status' => 'sometimes|required|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $post->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Blog post updated successfully',
            'data' => $post->load('author:id,name'),
        ]);
    }

    /**
     * Delete a blog post (Admin only).
     */
    public function destroy(int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog post deleted successfully',
        ]);
    }
}
