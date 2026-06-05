<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admin Broadcast System - send an official system message to users.
 */
class BroadcastController extends Controller
{
    public function index(): View
    {
        $recent = Message::where('is_broadcast', true)
            ->latest()
            ->take(20)
            ->get();

        return view('admin.broadcast.index', compact('recent'));
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'message_text' => ['required', 'string', 'max:5000'],
            'audience'     => ['required', Rule::in(['all', 'male', 'female', 'premium'])],
        ]);

        $admin = $request->user();

        $recipients = User::query()
            ->when($data['audience'] === 'male', fn ($q) => $q->where('gender', 'male'))
            ->when($data['audience'] === 'female', fn ($q) => $q->where('gender', 'female'))
            ->when($data['audience'] === 'premium', fn ($q) => $q->where('is_premium', true))
            ->where('role', 'user')
            ->pluck('id');

        $rows = $recipients->map(fn ($id) => [
            'sender_id'    => $admin->id,
            'receiver_id'  => $id,
            'message_text' => $data['message_text'],
            'is_broadcast' => true,
            'is_read'      => false,
            'created_at'   => now(),
            'updated_at'   => now(),
        ])->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            Message::insert($chunk);
        }

        return back()->with('status', "Duyuru {$recipients->count()} kullanıcıya gönderildi.");
    }
}
