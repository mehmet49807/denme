<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMessageController extends Controller
{
    public function index(Request $request)
    {
        $users = User::select('users.*')
            ->selectRaw('COUNT(DISTINCT CASE WHEN messages.sender_id = users.id THEN messages.id END) as sent_count')
            ->selectRaw('COUNT(DISTINCT CASE WHEN messages.receiver_id = users.id THEN messages.id END) as received_count')
            ->selectRaw('MAX(messages.created_at) as last_message_at')
            ->join('messages', function ($join) {
                $join->on('messages.sender_id', '=', 'users.id')
                    ->orOn('messages.receiver_id', '=', 'users.id');
            })
            ->groupBy('users.id')
            ->orderByDesc('last_message_at')
            ->paginate(30);

        return view('admin.messages.index', compact('users'));
    }

    public function show(Request $request, User $user)
    {
        $messages = Message::where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.messages.show', compact('user', 'messages'));
    }
}
