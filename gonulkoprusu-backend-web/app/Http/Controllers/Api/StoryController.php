<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    /** Active stories from the opposite gender (straight matching). */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $stories = Story::active()
            ->with('user')
            ->whereHas('user', fn ($q) => $q->where('gender', $user->oppositeGender())->where('status', 'active'))
            ->whereNotIn('user_id', $user->blockedUserIds())
            ->latest()
            ->get();

        return response()->json(['stories' => $stories]);
    }

    /**
     * Create a story. Allowed ONLY for premium men.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->canPostStories()) {
            return response()->json([
                'message' => 'Story paylaşımı yalnızca Premium erkek üyeler içindir.',
            ], 403);
        }

        $data = $request->validate([
            'media_url' => ['required', 'string', 'max:255'],
        ]);

        $story = $user->stories()->create([
            'media_url'  => $data['media_url'],
            'expires_at' => now()->addHours(24),
        ]);

        return response()->json(['story' => $story], 201);
    }
}
