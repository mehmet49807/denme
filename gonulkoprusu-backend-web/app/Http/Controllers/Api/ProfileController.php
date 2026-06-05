<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\PrivateUserResource;
use App\Http\Resources\PublicUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /** Other users -> only public fields (username, photo, city/district). */
    public function show(Request $request, User $user): JsonResponse
    {
        $viewer = $request->user();

        if (in_array($user->id, $viewer->blockedUserIds(), true)) {
            abort(404);
        }

        $posts = $user->posts()->latest()->paginate(20);

        return response()->json([
            'user'  => new PublicUserResource($user),
            'posts' => PostResource::collection($posts),
        ]);
    }

    /** Owner's own full profile (includes PRIVATE fields). */
    public function showSelf(Request $request): PrivateUserResource
    {
        return new PrivateUserResource($request->user());
    }

    /**
     * Update profile. The username is intentionally NOT accepted here -
     * it is strictly read-only and can never be changed.
     */
    public function update(Request $request): PrivateUserResource
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name'    => ['sometimes', 'string', 'max:100'],
            'last_name'     => ['sometimes', 'string', 'max:100'],
            'email'         => ['sometimes', 'email', 'max:190', "unique:users,email,{$user->id}"],
            'phone'         => ['sometimes', 'string', 'max:30'],
            'city'          => ['sometimes', 'string', 'max:80'],
            'district'      => ['sometimes', 'string', 'max:80'],
            'profile_photo' => ['sometimes', 'nullable', 'string', 'max:255'],
            'bio'           => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        // Hard guard: even if a client sends "username", we drop it.
        unset($data['username']);

        $user->update($data);

        return new PrivateUserResource($user);
    }
}
