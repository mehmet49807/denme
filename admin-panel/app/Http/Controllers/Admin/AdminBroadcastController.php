<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminBroadcast;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBroadcastController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => AdminBroadcast::with('admin')->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message_text' => 'required|string',
            'target_gender' => 'required|in:all,male,female',
        ]);

        $query = User::where('role', 'user');
        if ($request->target_gender !== 'all') {
            $query->where('gender', $request->target_gender);
        }
        $sentCount = $query->count();

        $broadcast = AdminBroadcast::create([
            'admin_id' => $request->user()->id,
            'title' => $request->title,
            'message_text' => $request->message_text,
            'target_gender' => $request->target_gender,
            'sent_count' => $sentCount,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Duyuru {$sentCount} kullanıcıya gönderildi.",
            'data' => $broadcast,
        ], 201);
    }
}
