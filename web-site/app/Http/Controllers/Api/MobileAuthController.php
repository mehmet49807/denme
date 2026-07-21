<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MobileAuthController extends Controller
{
    private const HANDOFF_TTL_SECONDS = 120;

    /**
     * Native Android login: validate credentials and return a one-time handoff URL.
     * WebView loads the handoff URL to establish a normal web session cookie.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'remember' => 'sometimes|boolean',
        ]);

        $login = trim($validated['login']);

        $user = User::query()
            ->where(function ($query) use ($login) {
                $query->where('email', $login)
                    ->orWhere('username', $login);
            })
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'ok' => false,
                'message' => 'Giriş bilgileri hatalı.',
            ], 422);
        }

        if ($user->is_banned) {
            return response()->json([
                'ok' => false,
                'message' => 'Hesabınız askıya alınmıştır.',
            ], 403);
        }

        $code = Str::random(64);
        $remember = (bool) ($validated['remember'] ?? true);

        Cache::put($this->cacheKey($code), [
            'user_id' => $user->id,
            'remember' => $remember,
        ], now()->addSeconds(self::HANDOFF_TTL_SECONDS));

        $handoffUrl = url('/mobile/session/consume/'.$code);

        return response()->json([
            'ok' => true,
            'handoff_url' => $handoffUrl,
            'expires_in' => self::HANDOFF_TTL_SECONDS,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
            ],
        ]);
    }

    /**
     * Consume one-time handoff code: create web session and redirect into the app shell.
     */
    public function consume(Request $request, string $code): RedirectResponse
    {
        if (! preg_match('/^[A-Za-z0-9]{32,128}$/', $code)) {
            return redirect()->route('login')->withErrors([
                'login' => 'Oturum bağlantısı geçersiz. Lütfen uygulamadan tekrar giriş yapın.',
            ]);
        }

        $payload = Cache::pull($this->cacheKey($code));

        if (! is_array($payload) || empty($payload['user_id'])) {
            return redirect()->route('login')->withErrors([
                'login' => 'Oturum bağlantısının süresi dolmuş. Lütfen tekrar giriş yapın.',
            ]);
        }

        $user = User::query()->find($payload['user_id']);

        if (! $user || $user->is_banned) {
            return redirect()->route('login')->withErrors([
                'login' => 'Hesabınıza giriş yapılamıyor.',
            ]);
        }

        Auth::login($user, (bool) ($payload['remember'] ?? true));
        $request->session()->regenerate();

        if (class_exists(\App\Support\FcmWebPrompt::class)) {
            \App\Support\FcmWebPrompt::arm();
        }

        return redirect()->route('feed');
    }

    private function cacheKey(string $code): string
    {
        return 'mobile_auth_handoff:'.$code;
    }
}
