<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function conversations(Request $request): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    public function index(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->canViewPublicProfile($user), 404);

        $messages = \App\Models\Message::query()
            ->where(fn ($query) => $query->where('sender_id', $request->user()->id)->where('receiver_id', $user->id))
            ->orWhere(fn ($query) => $query->where('sender_id', $user->id)->where('receiver_id', $request->user()->id))
            ->oldest()
            ->get();

        return response()->json(['data' => $messages]);
    }

    public function store(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->canViewPublicProfile($user), 404);

        $data = $request->validate(['message_text' => ['required', 'string', 'max:5000']]);
        $message = $request->user()->sentMessages()->create($data + ['receiver_id' => $user->id]);

        return response()->json($message, 201);
    }
}
