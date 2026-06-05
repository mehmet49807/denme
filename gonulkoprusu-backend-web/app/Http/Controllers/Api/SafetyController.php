<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Safety actions available on EVERY profile: Report (Şikayet) & Block (Engelle).
 */
class SafetyController extends Controller
{
    public function report(Request $request, User $user): JsonResponse
    {
        abort_if($user->id === $request->user()->id, 422, 'Kendinizi şikayet edemezsiniz.');

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $report = Report::create([
            'reporter_id' => $request->user()->id,
            'reported_id' => $user->id,
            'reason'      => $data['reason'],
            'status'      => 'pending',
        ]);

        return response()->json([
            'message' => 'Şikayetiniz alındı ve incelenecektir.',
            'report'  => $report,
        ], 201);
    }

    public function block(Request $request, User $user): JsonResponse
    {
        abort_if($user->id === $request->user()->id, 422, 'Kendinizi engelleyemezsiniz.');

        Block::firstOrCreate([
            'blocker_id' => $request->user()->id,
            'blocked_id' => $user->id,
        ], ['created_at' => now()]);

        return response()->json(['message' => 'Kullanıcı engellendi.']);
    }

    public function unblock(Request $request, User $user): JsonResponse
    {
        Block::where('blocker_id', $request->user()->id)
            ->where('blocked_id', $user->id)
            ->delete();

        return response()->json(['message' => 'Engel kaldırıldı.']);
    }
}
