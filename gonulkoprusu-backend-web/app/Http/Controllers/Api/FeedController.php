<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $viewer = $request->user();
        $visibleGender = $viewer->gender === 'female' ? 'male' : 'female';
        $blockedIds = $viewer->blocks()->pluck('blocked_id');
        $blockingIds = $viewer->blockedBy()->pluck('blocker_id');

        $posts = Post::query()
            ->with('user')
            ->whereHas('user', fn ($query) => $query->where('gender', $visibleGender)->where('status', 'active'))
            ->whereNotIn('user_id', $blockedIds)
            ->whereNotIn('user_id', $blockingIds)
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $posts->getCollection()->map->toFeedArray($viewer),
            'meta' => ['next_cursor' => $posts->nextPageUrl()],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['image_url' => ['required', 'url', 'max:500']]);
        $post = $request->user()->posts()->create($data);

        return response()->json($post->load('user')->toFeedArray($request->user()), 201);
    }

    public function like(Request $request, Post $post): JsonResponse
    {
        $post->likes()->firstOrCreate(['user_id' => $request->user()->id]);
        $post->update(['likes_count' => $post->likes()->count()]);

        return response()->json(['post_id' => $post->id, 'liked' => true, 'likes_count' => $post->likes_count]);
    }

    public function unlike(Request $request, Post $post): JsonResponse
    {
        $post->likes()->where('user_id', $request->user()->id)->delete();
        $post->update(['likes_count' => $post->likes()->count()]);

        return response()->json(['post_id' => $post->id, 'liked' => false, 'likes_count' => $post->likes_count]);
    }
}
