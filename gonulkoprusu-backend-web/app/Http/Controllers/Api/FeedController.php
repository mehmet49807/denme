<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedController extends Controller
{
    /**
     * Straight-matching feed:
     *   - Women see posts authored by men.
     *   - Men see posts authored by women.
     * Blocked users (either direction) are filtered out.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $posts = Post::with('user')
            ->whereHas('user', function ($q) use ($user) {
                $q->where('gender', $user->oppositeGender())
                  ->where('status', 'active');
            })
            ->whereNotIn('user_id', $user->blockedUserIds())
            ->latest()
            ->paginate(20);

        return PostResource::collection($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'image_url' => ['required', 'string', 'max:255'],
            'caption'   => ['nullable', 'string', 'max:500'],
        ]);

        $post = $request->user()->posts()->create($data);

        return (new PostResource($post->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    /** Idempotent like toggle - keeps likes_count in sync. */
    public function like(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $liked = DB::transaction(function () use ($user, $post) {
            $existing = Like::where('user_id', $user->id)
                ->where('post_id', $post->id)
                ->first();

            if ($existing) {
                $existing->delete();
                $post->decrement('likes_count');
                return false;
            }

            Like::create([
                'user_id'    => $user->id,
                'post_id'    => $post->id,
                'created_at' => now(),
            ]);
            $post->increment('likes_count');
            return true;
        });

        return response()->json([
            'liked'       => $liked,
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->delete();

        return response()->json(['message' => 'Gönderi silindi.']);
    }
}
