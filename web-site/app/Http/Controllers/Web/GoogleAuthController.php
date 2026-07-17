<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ValidatesLocation;
use App\Models\User;
use App\Services\LocationDataService;
use App\Services\UserMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GoogleAuthController extends Controller
{
    use ValidatesLocation;

    public function __construct(
        private LocationDataService $locations,
        private UserMailService $userMail,
    ) {}

    public function redirect(): RedirectResponse
    {
        $clientId = trim((string) config('services.google.client_id', ''));

        if ($clientId === '') {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google ile giriş şu an yapılandırılmamış. Lütfen e-posta ile giriş yapın veya daha sonra tekrar deneyin.']);
        }

        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google girişi iptal edildi veya reddedildi.']);
        }

        $state = (string) session('google_oauth_state', '');
        session()->forget('google_oauth_state');

        if ($state === '' || ! hash_equals($state, (string) $request->query('state', ''))) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google oturum doğrulaması başarısız. Lütfen tekrar deneyin.']);
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google yetkilendirme kodu alınamadı.']);
        }

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (! $tokenResponse->successful()) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google oturum açılamadı. Lütfen tekrar deneyin.']);
        }

        $accessToken = (string) ($tokenResponse->json('access_token') ?? '');
        if ($accessToken === '') {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google erişim jetonu alınamadı.']);
        }

        $profileResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');
        if (! $profileResponse->successful()) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google profil bilgileri alınamadı.']);
        }

        $googleId = (string) ($profileResponse->json('sub') ?? '');
        $email = strtolower(trim((string) ($profileResponse->json('email') ?? '')));
        $emailVerified = (bool) ($profileResponse->json('email_verified') ?? false);
        $firstName = trim((string) ($profileResponse->json('given_name') ?? ''));
        $lastName = trim((string) ($profileResponse->json('family_name') ?? ''));
        $photo = trim((string) ($profileResponse->json('picture') ?? ''));

        if ($email === '' || ! $emailVerified) {
            return redirect()
                ->route('login')
                ->withErrors(['login' => 'Google hesabınızda doğrulanmış bir e-posta bulunamadı.']);
        }

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            if ($user->is_banned) {
                return redirect()
                    ->route('login')
                    ->withErrors(['login' => 'Hesabınız askıya alınmıştır.']);
            }

            if ($photo !== '' && empty($user->profile_photo_url)) {
                $user->update(['profile_photo_url' => $photo]);
            }

            Auth::login($user, true);

            return redirect()->intended(route('feed'));
        }

        session([
            'google_signup' => [
                'google_id' => $googleId,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'profile_photo_url' => $photo,
            ],
        ]);

        return redirect()->route('auth.google.complete');
    }

    public function completeForm(): View|RedirectResponse
    {
        $payload = session('google_signup');
        if (! is_array($payload) || empty($payload['email'])) {
            return redirect()->route('login');
        }

        return view('web.google-complete', [
            'googleSignup' => $payload,
        ]);
    }

    public function complete(Request $request): RedirectResponse
    {
        $payload = session('google_signup');
        if (! is_array($payload) || empty($payload['email'])) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users|regex:/^[a-zA-Z0-9_]+$/',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:male,female',
            'privacy_accepted' => 'accepted',
            'kvkk_accepted' => 'accepted',
        ], [
            'privacy_accepted.accepted' => 'Kayıt olmak için Gizlilik Sözleşmesi\'ni kabul etmelisiniz.',
            'kvkk_accepted.accepted' => 'Kayıt olmak için KVKK Aydınlatma Metni\'ni kabul etmelisiniz.',
        ]);

        $validated = array_merge($validated, $this->validateLocationInput($request, $this->locations));

        $userData = [
            'username' => $validated['username'],
            'first_name' => $payload['first_name'] ?: $validated['username'],
            'last_name' => $payload['last_name'] ?: '',
            'email' => $payload['email'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'country' => $validated['country'],
            'city' => $validated['city'],
            'district' => $validated['district'] ?? null,
            'profile_photo_url' => $payload['profile_photo_url'] ?? null,
            'registration_source' => 'google',
            'email_verified_at' => now(),
        ];

        if ($validated['gender'] === 'male') {
            $userData['trial_ends_at'] = User::trialEndsAtForNewMale();
        }

        $user = User::create($userData);

        try {
            app(\App\Services\UserAttributionService::class)->applyToNewUser($user, 'google');
        } catch (\Throwable) {
            // Atıf/ödül hatası kayıt akışını durdurmasın.
        }

        session()->forget('google_signup');
        Auth::login($user, true);
        session(['growth_signed_up' => 1]);

        try {
            $this->userMail->sendWelcome($user);
        } catch (\Throwable) {
            // Kayıt akışını e-posta hatası durdurmasın.
        }

        return redirect()->route('feed')->with('success', 'Google hesabınızla kayıt tamamlandı.');
    }

    private function redirectUri(): string
    {
        $configured = trim((string) config('services.google.redirect', ''));
        if ($configured !== '') {
            return $configured;
        }

        return route('auth.google.callback', absolute: true);
    }
}
