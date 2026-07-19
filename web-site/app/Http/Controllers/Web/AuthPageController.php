<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ValidatesLocation;
use App\Http\Concerns\ValidatesHobbies;
use App\Models\User;
use App\Services\CountryMetaService;
use App\Services\LocationDataService;
use App\Services\MediaUploadService;
use App\Services\ReferralService;
use App\Services\UserAttributionService;
use App\Services\UserMailService;
use App\Support\RelationshipStatus;
use App\Support\SeoHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthPageController extends Controller
{
    use ValidatesLocation;
    use ValidatesHobbies;

    public function __construct(
        private LocationDataService $locations,
        private MediaUploadService $mediaUpload,
        private UserMailService $userMail,
        private CountryMetaService $countryMeta,
    ) {}
    public function registerForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('feed');
        }

        app(UserAttributionService::class)->captureFromRequest(request());

        SeoHelper::setPage('register');
        SeoHelper::set('canonical', url('/register'));

        $referrer = null;
        $refCode = session('growth_ref') ?? request('ref');
        if ($refCode) {
            $referrer = app(ReferralService::class)->findReferrerByCode($refCode);
        }

        return view('web.register', [
            'dialCodes' => $this->countryMeta->dialCodes(),
            'countryMeta' => $this->countryMeta,
            'referrer' => $referrer,
            'refCode' => $refCode,
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        app(UserAttributionService::class)->captureFromRequest($request);

        if ($request->input('relationship_status') === '') {
            $request->merge(['relationship_status' => null]);
        }

        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users|regex:/^[a-zA-Z0-9_]+$/',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'phone_country_code' => 'nullable|string|max:6',
            'phone_local' => 'nullable|string|max:15',
            'gender' => 'required|in:male,female',
            'bio' => 'nullable|string|max:500',
            'relationship_status' => 'nullable|in:'.implode(',', RelationshipStatus::keys()),
            'birth_day' => 'nullable|integer|min:1|max:31',
            'birth_month' => 'nullable|integer|min:1|max:12',
            'birth_year' => 'nullable|integer|min:'.(now()->year - 100).'|max:'.(now()->year - 18),
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'privacy_accepted' => 'accepted',
            'kvkk_accepted' => 'accepted',
        ], [
            'privacy_accepted.accepted' => 'Kayıt olmak için Gizlilik Sözleşmesi\'ni kabul etmelisiniz.',
            'kvkk_accepted.accepted' => 'Kayıt olmak için KVKK Aydınlatma Metni\'ni kabul etmelisiniz.',
            'relationship_status.in' => 'Geçerli bir ilişki durumu seçiniz.',
        ]);

        if (! empty($validated['phone_country_code']) && ! empty($validated['phone_local'])) {
            if (!$this->countryMeta->isValidDialCode($validated['phone_country_code'])) {
                return back()->withErrors(['phone_country_code' => 'Geçersiz ülke telefon kodu.'])->withInput();
            }

            $validated['phone'] = $this->countryMeta->composePhone(
                $validated['phone_country_code'],
                $validated['phone_local']
            );
        } else {
            $validated['phone'] = null;
        }
        unset($validated['phone_country_code'], $validated['phone_local']);

        $validated = array_merge($validated, $this->validateLocationInput($request, $this->locations, false, false));
        $validated = array_merge($validated, $this->validateHobbiesInput($request));
        $validated = array_merge($validated, $this->resolveOptionalBirthDate($request));

        $validated['password'] = Hash::make($validated['password']);
        unset(
            $validated['privacy_accepted'],
            $validated['kvkk_accepted'],
            $validated['birth_day'],
            $validated['birth_month'],
            $validated['birth_year'],
        );

        if (($validated['relationship_status'] ?? null) === '') {
            $validated['relationship_status'] = null;
        }
        if (($validated['bio'] ?? null) === '') {
            $validated['bio'] = null;
        }

        if ($validated['gender'] === 'male') {
            $validated['trial_ends_at'] = User::trialEndsAtForNewMale();
        }

        $photo = $request->file('photo');
        unset($validated['photo']);

        $user = User::create($validated);

        app(UserAttributionService::class)->applyToNewUser($user, 'email');

        if ($photo) {
            try {
                $url = $this->mediaUpload->uploadProfilePhoto($photo);
                $user->update(['profile_photo_url' => $url]);
            } catch (\Throwable) {
                Auth::login($user);

                return redirect()->route('feed')
                    ->with('success', 'Kayıt tamamlandı. Profil fotoğrafı yüklenemedi; profil sayfasından tekrar ekleyebilirsiniz.');
            }
        }

        Auth::login($user);
        session([
            'growth_signed_up' => 1,
            'growth_signed_up_method' => 'email',
            'growth_show_onboarding' => 1,
        ]);

        try {
            $this->userMail->sendWelcome($user);
        } catch (\Throwable) {
            // Kayıt akışını e-posta hatası durdurmasın.
        }

        return redirect()->route('feed');
    }

    public function loginForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('feed');
        }

        app(UserAttributionService::class)->captureFromRequest(request());

        SeoHelper::setPage('login');
        SeoHelper::set('canonical', url('/login'));

        return view('web.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->login;

        $user = User::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('username', $login);
        })->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->is_banned) {
                return back()->withErrors(['login' => 'Hesabınız askıya alınmıştır.']);
            }

            Auth::login($user, $request->boolean('remember'));

            if ($user->isAdmin()) {
                if (\Illuminate\Support\Facades\Route::has('admin.dashboard')) {
                    return redirect()->route('admin.dashboard');
                }

                return redirect()->intended(route('feed'));
            }

            return redirect()->intended(route('feed'));
        }

        return back()->withErrors(['login' => 'Giriş bilgileri hatalı.']);
    }

    public function forgotPasswordForm(): View
    {
        return view('web.forgot-password');
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Bu e-posta adresiyle kayıtlı bir hesap bulunamadı.']);
    }

    public function resetPasswordForm(Request $request, string $token): View
    {
        return view('web.reset-password', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ], [
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifre tekrarı eşleşmiyor.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Şifreniz güncellendi. Yeni şifrenizle giriş yapabilirsiniz.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Şifre sıfırlama bağlantısı geçersiz veya süresi dolmuş. Lütfen tekrar deneyin.']);
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect()->route('home');
    }

    /** @return array{birth_date?: string} */
    private function resolveOptionalBirthDate(Request $request): array
    {
        $day = $request->input('birth_day');
        $month = $request->input('birth_month');
        $year = $request->input('birth_year');

        if ($day === null || $day === '' || $month === null || $month === '' || $year === null || $year === '') {
            return [];
        }

        if (! checkdate((int) $month, (int) $day, (int) $year)) {
            throw ValidationException::withMessages([
                'birth_date' => 'Geçerli bir doğum tarihi seçiniz.',
            ]);
        }

        $date = Carbon::createFromDate((int) $year, (int) $month, (int) $day)->startOfDay();

        if ($date->greaterThan(now()->subYears(18)->startOfDay())) {
            throw ValidationException::withMessages([
                'birth_date' => '18 yaşından büyük olmalısınız.',
            ]);
        }

        return ['birth_date' => $date->toDateString()];
    }
}
