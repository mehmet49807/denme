<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    public function users(): JsonResponse
    {
        return response()->json(['data' => User::latest()->paginate(50)]);
    }

    public function updateUser(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:80'],
            'last_name' => ['sometimes', 'string', 'max:80'],
            'email' => ['sometimes', 'email', 'max:191'],
            'phone' => ['sometimes', 'string', 'max:32'],
            'city' => ['sometimes', 'string', 'max:120'],
            'district' => ['sometimes', 'string', 'max:120'],
            'status' => ['sometimes', 'in:active,banned,deleted'],
        ]);

        $user->update($data);

        return response()->json($user->toOwnerArray());
    }

    public function banUser(User $user): JsonResponse
    {
        $user->update(['status' => 'banned']);

        return response()->json(['status' => 'banned']);
    }

    public function deleteUser(User $user): JsonResponse
    {
        $user->update(['status' => 'deleted']);
        $user->delete();

        return response()->json(null, 204);
    }

    public function messages(): JsonResponse
    {
        return response()->json(['data' => \App\Models\Message::with(['sender', 'receiver'])->latest()->paginate(100)]);
    }

    public function reports(): JsonResponse
    {
        return response()->json(['data' => \App\Models\Report::with(['reporter', 'reported'])->latest()->paginate(100)]);
    }

    public function updateReport(Request $request, \App\Models\Report $report): JsonResponse
    {
        $report->update($request->validate(['status' => ['required', 'in:open,reviewing,resolved,dismissed'], 'admin_notes' => ['nullable', 'string']]));

        return response()->json($report);
    }

    public function premium(): JsonResponse
    {
        return response()->json(['data' => \App\Models\PremiumSubscription::where('status', 'active')->get()]);
    }

    public function broadcast(Request $request): JsonResponse
    {
        $broadcast = \App\Models\AdminBroadcast::create($request->validate([
            'title' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string'],
            'audience' => ['required', 'in:all,female,male,premium_men'],
        ]) + ['admin_id' => $request->user()->id]);

        return response()->json($broadcast, 201);
    }
}
