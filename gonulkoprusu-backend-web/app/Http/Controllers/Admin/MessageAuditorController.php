<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Read-only auditing of user-to-user messages for safety/moderation.
 */
class MessageAuditorController extends Controller
{
    public function index(Request $request): View
    {
        $messages = Message::with(['sender', 'receiver'])
            ->when($request->q, fn ($q) => $q->where('message_text', 'like', "%{$request->q}%"))
            ->where('is_broadcast', false)
            ->latest()
            ->paginate(40)
            ->withQueryString();

        return view('admin.messages.index', compact('messages'));
    }
}
