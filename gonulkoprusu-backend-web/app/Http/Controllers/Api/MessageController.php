<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /** Conversation list with latest message preview. */
    public function conversations(Request $request): JsonResponse
    {
        $me = $request->user()->id;

        $partnerIds = Message::where('sender_id', $me)
            ->orWhere('receiver_id', $me)
            ->get()
            ->map(fn ($m) => $m->sender_id === $me ? $m->receiver_id : $m->sender_id)
            ->unique()
            ->values();

        $conversations = $partnerIds->map(function ($pid) use ($me) {
            $last = Message::where(function ($q) use ($me, $pid) {
                $q->where('sender_id', $me)->where('receiver_id', $pid);
            })->orWhere(function ($q) use ($me, $pid) {
                $q->where('sender_id', $pid)->where('receiver_id', $me);
            })->latest()->first();

            return [
                'partner'      => $pid,
                'last_message' => $last,
                'unread'       => Message::where('sender_id', $pid)
                    ->where('receiver_id', $me)
                    ->where('is_read', false)
                    ->count(),
            ];
        });

        return response()->json(['conversations' => $conversations]);
    }

    /** Full thread with one user. */
    public function thread(Request $request, User $user): JsonResponse
    {
        $me = $request->user()->id;

        abort_if(in_array($user->id, $request->user()->blockedUserIds(), true), 403);

        $messages = Message::where(function ($q) use ($me, $user) {
            $q->where('sender_id', $me)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($me, $user) {
            $q->where('sender_id', $user->id)->where('receiver_id', $me);
        })->orderBy('created_at')->paginate(50);

        // Mark incoming as read.
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $me)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function send(Request $request, User $user): JsonResponse
    {
        $me = $request->user();

        abort_if(in_array($user->id, $me->blockedUserIds(), true), 403, 'Bu kullanıcı ile mesajlaşamazsınız.');

        $data = $request->validate([
            'message_text' => ['required', 'string', 'max:5000'],
        ]);

        $message = Message::create([
            'sender_id'    => $me->id,
            'receiver_id'  => $user->id,
            'message_text' => $data['message_text'],
        ]);

        return response()->json(['message' => $message], 201);
    }
}
