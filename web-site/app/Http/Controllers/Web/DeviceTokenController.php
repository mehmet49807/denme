<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\FcmPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request, FcmPushService $fcm): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|min:20|max:4096',
            'platform' => 'nullable|string|max:40',
        ]);

        $platform = strtolower(trim((string) ($validated['platform'] ?? 'android')));
        if (! in_array($platform, ['android', 'ios', 'web'], true)) {
            $platform = 'android';
        }

        $fcm->registerToken($request->user(), $validated['token'], $platform);

        return response()->json([
            'ok' => true,
            'configured' => $fcm->isConfigured(),
            'devices' => $fcm->registeredDeviceCount(),
        ]);
    }

    public function destroy(Request $request, FcmPushService $fcm): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'nullable|string|max:4096',
        ]);

        $fcm->removeToken($request->user(), $validated['token'] ?? null);

        return response()->json(['ok' => true]);
    }

    public function ackPrompt(Request $request): JsonResponse
    {
        \App\Support\FcmWebPrompt::clear();

        return response()->json(['ok' => true]);
    }
}
