<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->toOwnerArray());
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:80'],
            'last_name' => ['sometimes', 'string', 'max:80'],
            'email' => ['sometimes', 'email', 'max:191'],
            'phone' => ['sometimes', 'string', 'max:32'],
            'city' => ['sometimes', 'string', 'max:120'],
            'district' => ['sometimes', 'string', 'max:120'],
            'profile_photo_url' => ['sometimes', 'nullable', 'url', 'max:500'],
        ]);

        $request->user()->update($data);

        return response()->json($request->user()->refresh()->toOwnerArray());
    }

    public function show(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->canViewPublicProfile($user), 404);

        return response()->json([
            'user' => $user->toPublicArray(),
            'posts' => $user->posts()->latest()->get()->map->toFeedArray($request->user()),
        ]);
    }

    public function report(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:2000']]);
        $report = $user->reportsAgainst()->create([
            'reporter_id' => $request->user()->id,
            'reason' => $data['reason'],
        ]);

        return response()->json(['id' => $report->id, 'status' => $report->status], 201);
    }

    public function block(Request $request, User $user): JsonResponse
    {
        $request->user()->blocks()->firstOrCreate(['blocked_id' => $user->id]);

        return response()->json(['blocked' => true], 201);
    }

    public function unblock(Request $request, User $user): JsonResponse
    {
        $request->user()->blocks()->where('blocked_id', $user->id)->delete();

        return response()->json(['blocked' => false]);
    }
}
