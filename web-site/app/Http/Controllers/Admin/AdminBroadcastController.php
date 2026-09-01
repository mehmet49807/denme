<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminBroadcast;
use App\Models\User;
use Illuminate\Http\Request;

class AdminBroadcastController extends Controller
{
    public function index(Request $request)
    {
        $broadcasts = AdminBroadcast::with('admin')
            ->latest('created_at')
            ->paginate(20);

        return view('admin.broadcasts.index', compact('broadcasts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:200',
            'message_text'  => 'required|string|max:2000',
            'target_gender' => 'required|in:all,male,female',
        ]);

        $query = User::where('is_banned', false);
        if ($validated['target_gender'] !== 'all') {
            $query->where('gender', $validated['target_gender']);
        }

        $sentCount = $query->count();

        AdminBroadcast::create([
            'admin_id'       => $request->user()->id,
            'title'          => $validated['title'],
            'message_text'   => $validated['message_text'],
            'target_gender'  => $validated['target_gender'],
            'sent_count'     => $sentCount,
            'status'         => 'sent',
            'created_at'     => now(),
        ]);

        return back()->with('success', 'Duyuru gönderildi. Hedef kitle: ' . $sentCount . ' kullanıcı.');
    }
}
