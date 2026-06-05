<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $visibleGender = $request->user()->gender === 'female' ? 'male' : 'female';

        $stories = \App\Models\Story::query()
            ->with('user')
            ->where('expires_at', '>', now())
            ->whereHas('user', fn ($query) => $query->where('gender', $visibleGender)->where('status', 'active'))
            ->latest()
            ->get();

        return response()->json([
            'data' => $stories->map(fn ($story) => [
                'id' => $story->id,
                'author' => $story->user->toPublicArray(),
                'media_url' => $story->media_url,
                'expires_at' => $story->expires_at,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user->gender === 'male' && ! $user->hasActivePremium(), 403, 'story_premium_required');

        $data = $request->validate(['media_url' => ['required', 'url', 'max:500']]);
        $story = $user->stories()->create($data + ['expires_at' => now()->addDay()]);

        return response()->json([
            'id' => $story->id,
            'author' => $user->toPublicArray(),
            'media_url' => $story->media_url,
            'expires_at' => $story->expires_at,
        ], 201);
    }
}
