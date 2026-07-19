<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
    public function __construct(
        private LocationDataService $locations,
        private UserMailService $userMail,
    ) {}

    /**
     * Google'a gitmeden önce cinsiyet + KVKK kaydı (kayıt akışları).
     */
    public function prepare(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gender' => 'required|in:male,female',
            'privacy_accepted' => 'accepted',
            'kvkk_accepted' => 'accepted',
            'city' => 'nullable|string|max:100',
        ], [
            'gender.required' => 'Devam etmek için cinsiyet seçmelisiniz.',
            'privacy_accepted.accepted' => 'Kayıt olmak için Gizlilik Sözleşmesi\'ni kabul etmelisiniz.',
            'kvkk_accepted.accepted' => 'Kayıt olmak için KVKK Aydınlatma Metni\'ni kabul etmelisiniz.',
        ]);

        $city = trim((string) ($validated['city'] ?? ''));
        if ($city !== '' && ! $this->locations->isValidCity('Türkiye', $city)) {
            $city = '';
        }

        session([
            'google_signup_intent' => [
                'gender' => $validated['gender'],
                'privacy_accepted' => true,
                'kvkk_accepted' => true,
                'city' => $city,
            ],
        ]);

        return $this->redirect();
    }

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

            $updates = [];
            if ($photo !== '' && empty($user->profile_photo_url)) {
                $updates['profile_photo_url'] = $photo;
            }
            if ($googleId !== '' && empty($user->google_id)) {
                $updates['google_id'] = $googleId;
            }
            if ($updates !== []) {
                $user->update($updates);
            }

            session()->forget(['google_signup', 'google_signup_intent']);
            Auth::login($user, true);

            return redirect()->intended(route('feed'));
        }

        $payload = [
            'google_id' => $googleId,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'profile_photo_url' => $photo,
        ];

        $intent = session('google_signup_intent');
        if (is_array($intent) && in_array($intent['gender'] ?? '', ['male', 'female'], true)) {
            return $this->createGoogleUser($payload, $intent);
        }

        session(['google_signup' => $payload]);

        return redirect()->route('auth.google.complete');
    }

    public function completeForm(): View|RedirectResponse
    {
        $payload = session('google_signup');
        if (! is_array($payload) || empty($payload['email'])) {
            return redirect()->route('register');
        }

        return view('web.google-complete', [
            'googleSignup' => $payload,
        ]);
    }

    public function complete(Request $request): RedirectResponse
    {
        $payload = session('google_signup');
        if (! is_array($payload) || empty($payload['email'])) {
            return redirect()->route('register');
        }

        $validated = $request->validate([
            'gender' => 'required|in:male,female',
            'privacy_accepted' => 'accepted',
            'kvkk_accepted' => 'accepted',
            'city' => 'nullable|string|max:100',
        ], [
            'privacy_accepted.accepted' => 'Kayıt olmak için Gizlilik Sözleşmesi\'ni kabul etmelisiniz.',
            'kvkk_accepted.accepted' => 'Kayıt olmak için KVKK Aydınlatma Metni\'ni kabul etmelisiniz.',
        ]);

        $city = trim((string) ($validated['city'] ?? ''));
        if ($city !== '' && ! $this->locations->isValidCity('Türkiye', $city)) {
            $city = '';
        }

        return $this->createGoogleUser($payload, [
            'gender' => $validated['gender'],
            'privacy_accepted' => true,
            'kvkk_accepted' => true,
            'city' => $city,
        ]);
    }

    /**
     * @param  array{google_id?:string,email:string,first_name?:string,last_name?:string,profile_photo_url?:string|null}  $payload
     * @param  array{gender:string,city?:string}  $intent
     */
    private function createGoogleUser(array $payload, array $intent): RedirectResponse
    {
        $email = (string) $payload['email'];
        if (User::query()->where('email', $email)->exists()) {
            session()->forget(['google_signup', 'google_signup_intent']);

            return redirect()->route('login')->withErrors(['login' => 'Bu e-posta ile zaten bir hesap var. Giriş yapın.']);
        }

        $username = $this->makeUniqueUsername(
            (string) ($payload['first_name'] ?? ''),
            (string) ($payload['last_name'] ?? ''),
            $email
        );

        $city = trim((string) ($intent['city'] ?? ''));
        $userData = [
            'username' => $username,
            'first_name' => ($payload['first_name'] ?? '') !== '' ? $payload['first_name'] : $username,
            'last_name' => $payload['last_name'] ?? '',
            'email' => $email,
            'google_id' => $payload['google_id'] ?? null,
            'gender' => $intent['gender'],
            'country' => 'Türkiye',
            'city' => $city,
            'district' => '',
            'profile_photo_url' => $payload['profile_photo_url'] ?? null,
            'registration_source' => 'google',
            'email_verified_at' => now(),
        ];

        if ($intent['gender'] === 'male') {
            $userData['trial_ends_at'] = User::trialEndsAtForNewMale();
        }

        $user = User::create($userData);

        try {
            app(\App\Services\UserAttributionService::class)->applyToNewUser($user, 'google');
        } catch (\Throwable) {
            // Atıf/ödül hatası kayıt akışını durdurmasın.
        }

        session()->forget(['google_signup', 'google_signup_intent']);
        Auth::login($user, true);
        session([
            'growth_signed_up' => 1,
            'growth_signed_up_method' => 'google',
            'growth_show_onboarding' => 1,
        ]);

        try {
            $this->userMail->sendWelcome($user);
        } catch (\Throwable) {
            // Kayıt akışını e-posta hatası durdurmasın.
        }

        return redirect()->route('feed')->with('success', 'Google hesabınızla kayıt tamamlandı.');
    }

    private function makeUniqueUsername(string $firstName, string $lastName, string $email): string
    {
        $raw = trim($firstName.' '.$lastName);
        $slug = Str::lower(Str::ascii($raw));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug ?? '') ?? '';
        $slug = trim($slug, '_');

        if ($slug === '') {
            $local = Str::lower(Str::ascii(Str::before($email, '@')));
            $slug = preg_replace('/[^a-z0-9_]+/', '', $local ?? '') ?? '';
            $slug = trim($slug, '_');
        }

        if ($slug === '' || strlen($slug) < 3) {
            $slug = 'user'.random_int(100, 999);
        }

        $slug = substr($slug, 0, 40);
        $candidate = $slug;
        $i = 0;

        while (User::query()->where('username', $candidate)->exists()) {
            $i++;
            $suffix = (string) random_int(10, 99).$i;
            $candidate = substr($slug, 0, max(3, 50 - strlen($suffix) - 1)).'_'.$suffix;
            if ($i > 40) {
                $candidate = 'user_'.Str::lower(Str::random(8));
                break;
            }
        }

        return $candidate;
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
