<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ValidatesHobbies;
use App\Http\Concerns\ValidatesLocation;
use App\Models\Like;
use App\Models\Post;
use App\Models\ProfileView;
use App\Services\LocationDataService;
use App\Services\MediaUploadService;
use App\Services\StoryGroupService;
use App\Support\LocaleManager;
use App\Support\RelationshipStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use RuntimeException;

class ProfilePageController extends Controller
{
    use ValidatesHobbies;
    use ValidatesLocation;

    public function __construct(
        private LocationDataService $locations,
        private MediaUploadService $mediaUpload,
        private StoryGroupService $storyGroups,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $posts = Post::where('user_id', $user->id)
            ->where('is_active', true)
            ->latest()
            ->limit(36)
            ->get();

        $ownStoryGroup = $this->storyGroups->loadOwnStoryGroup($user);

        $likedPostIds = Like::where('user_id', $user->id)
            ->whereIn('post_id', $posts->pluck('id'))
            ->pluck('post_id')
            ->all();

        $profileViews = collect();
        $profileViewsCount = 0;
        if ($user->canAccessWhoViewed()) {
            $viewerScope = function ($query) {
                $query->where(function ($inner) {
                    $inner->whereNull('role')->orWhere('role', '!=', 'admin');
                });
            };

            $profileViewsCount = ProfileView::query()
                ->where('viewed_id', $user->id)
                ->whereHas('viewer', $viewerScope)
                ->count();

            // Sayfa yükünü sabit tut: kapalı panel + sınırlı liste.
            $profileViews = ProfileView::query()
                ->with('viewer:id,username,profile_photo_url,city,district,country,gender,is_verified,last_active_at,role,birth_date')
                ->where('viewed_id', $user->id)
                ->whereHas('viewer', $viewerScope)
                ->latest()
                ->limit(40)
                ->get();
        }

        return view('web.profile', compact('user', 'posts', 'ownStoryGroup', 'likedPostIds', 'profileViews', 'profileViewsCount'));
    }

    public function update(Request $request): RedirectResponse
    {
        if ($request->has('relationship_status') && $request->input('relationship_status') === '') {
            $request->merge(['relationship_status' => null]);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,'.$request->user()->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'bio' => 'sometimes|nullable|string|max:500',
            'relationship_status' => 'sometimes|nullable|in:'.implode(',', RelationshipStatus::keys()),
            'relationship_expectation' => 'sometimes|nullable|string|max:120',
            'birth_day' => 'sometimes|nullable|integer|min:1|max:31',
            'birth_month' => 'sometimes|nullable|integer|min:1|max:12',
            'birth_year' => 'sometimes|nullable|integer|min:'.(now()->year - 100).'|max:'.(now()->year - 18),
            'birth_date' => 'sometimes|nullable|date|before:-18 years',
            'visibility' => 'sometimes|in:everyone,matches,premium,nobody',
            'quiet_hours_enabled' => 'sometimes|boolean',
            'quiet_hours_start' => 'sometimes|nullable|date_format:H:i',
            'quiet_hours_end' => 'sometimes|nullable|date_format:H:i',
            'read_receipts_enabled' => 'sometimes|boolean',
            'theme_preference' => 'sometimes|in:light,dark,system',
        ], [
            'relationship_status.in' => 'Geçerli bir ilişki durumu seçiniz.',
            'birth_date.before' => 'Kayıt için 18 yaşından büyük olmalısınız.',
        ]);

        if ($request->has('quiet_hours_enabled')) {
            $validated['quiet_hours_enabled'] = $request->boolean('quiet_hours_enabled');
        }
        if ($request->has('read_receipts_enabled')) {
            $validated['read_receipts_enabled'] = $request->boolean('read_receipts_enabled');
        }

        if ($request->hasAny(['country', 'city', 'district'])) {
            $validated = array_merge($validated, $this->validateLocationInput($request, $this->locations, false));
        }

        $validated = array_merge($validated, $this->validateHobbiesInput($request));
        $validated = array_merge($validated, $this->resolveBirthDateInput($request));

        unset($validated['birth_day'], $validated['birth_month'], $validated['birth_year']);

        if ($request->has('bio') && ! array_key_exists('bio', $validated)) {
            $validated['bio'] = null;
        }
        if ($request->has('relationship_status') && ($validated['relationship_status'] ?? null) === '') {
            $validated['relationship_status'] = null;
        }

        $request->user()->update($validated);

        return $this->settingsRedirect($request, 'Profil bilgileriniz güncellendi.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|confirmed|min:8',
        ], [
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Şifre tekrarı eşleşmiyor.',
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Mevcut şifre hatalı.'])
                ->with('settings_panel', 'password');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $this->settingsRedirect($request, 'Şifreniz güncellendi.');
    }

    public function updateLocale(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:'.implode(',', LocaleManager::codes()),
        ]);

        return $this->persistLocale($request, $validated['locale']);
    }

    public function switchLocale(Request $request, string $locale): RedirectResponse
    {
        if (! LocaleManager::isSupported($locale)) {
            abort(404);
        }

        return $this->persistLocale($request, $locale);
    }

    public function uploadPhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        $user = $request->user();

        try {
            $this->mediaUpload->deleteByUrl($user->profile_photo_url);
            $url = $this->mediaUpload->uploadProfilePhoto($request->file('photo'));
            $user->update(['profile_photo_url' => $url]);
        } catch (RuntimeException $e) {
            return back()->withErrors(['photo' => 'Profil fotoğrafı yüklenemedi.']);
        }

        return back()->with('success', 'Profil fotoğrafınız güncellendi.');
    }

    public function uploadGallery(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canManageProfileGallery()) {
            return redirect()
                ->route('premium')
                ->withErrors(['gallery_photo' => 'Galeri özelliği Premium üyeler içindir.']);
        }

        $request->validate([
            'gallery_photo' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        $photos = $user->galleryPhotos();

        if (count($photos) >= 6) {
            return back()->withErrors(['gallery_photo' => 'En fazla 6 galeri fotoğrafı ekleyebilirsiniz.']);
        }

        try {
            $url = $this->mediaUpload->uploadProfilePhoto($request->file('gallery_photo'));
            $photos[] = $url;
            $user->update(['gallery_photos' => $photos]);
        } catch (RuntimeException $e) {
            return back()->withErrors(['gallery_photo' => 'Galeri fotoğrafı yüklenemedi.']);
        }

        return back()->with('success', 'Galeri fotoğrafı eklendi.');
    }

    public function destroyGallery(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canManageProfileGallery()) {
            return redirect()
                ->route('premium')
                ->withErrors(['gallery_photo' => 'Galeri özelliği Premium üyeler içindir.']);
        }

        $validated = $request->validate([
            'url' => 'required|string|max:500',
        ]);

        $photos = array_values(array_filter(
            $user->galleryPhotos(),
            fn ($url) => $url !== $validated['url']
        ));

        if (count($photos) !== count($user->galleryPhotos())) {
            try {
                $this->mediaUpload->deleteByUrl($validated['url']);
            } catch (\Throwable) {
            }
            $user->update(['gallery_photos' => $photos]);
        }

        return back()->with('success', 'Galeri fotoğrafı silindi.');
    }

    public function boost(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canUseProfileBoost()) {
            return back()->withErrors(['boost' => 'Profil öne çıkarma Gold ve Platinum paketlerine özeldir.']);
        }

        if (! $user->canBoostToday()) {
            return back()->withErrors(['boost' => 'Günlük boost hakkını bugün kullandın. Yarın tekrar dene.']);
        }

        $hours = $user->hasPackageAtLeast('platinum') ? 24 : 12;
        $user->activateDailyBoost($hours);

        return back()->with('success', "Profilin {$hours} saat boyunca öne çıkarılacak.");
    }

    private function persistLocale(Request $request, string $locale): RedirectResponse
    {
        LocaleManager::remember($request, $locale);
        LocaleManager::apply($locale);

        return back()
            ->with('success', 'Dil tercihiniz kaydedildi.')
            ->with('settings_panel', 'language')
            ->withCookie(LocaleManager::makeCookie($locale, $request->isSecure()));
    }

    private function settingsRedirect(Request $request, string $message): RedirectResponse
    {
        $panel = $request->input('settings_panel', 'menu');

        return back()
            ->with('success', $message)
            ->with('settings_panel', $panel);
    }

    /** @return array{birth_date?: string|null} */
    private function resolveBirthDateInput(Request $request): array
    {
        if (! $request->hasAny(['birth_day', 'birth_month', 'birth_year'])) {
            return [];
        }

        $day = $request->input('birth_day');
        $month = $request->input('birth_month');
        $year = $request->input('birth_year');

        if ($day === null || $day === '' || $month === null || $month === '' || $year === null || $year === '') {
            if ($request->input('settings_panel') === 'edit') {
                return ['birth_date' => null];
            }

            return [];
        }

        if (! checkdate((int) $month, (int) $day, (int) $year)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'birth_date' => 'Geçerli bir doğum tarihi seçiniz.',
            ]);
        }

        $date = Carbon::createFromDate((int) $year, (int) $month, (int) $day)->startOfDay();

        if ($date->greaterThan(now()->subYears(18)->startOfDay())) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'birth_date' => '18 yaşından büyük olmalısınız.',
            ]);
        }

        return ['birth_date' => $date->toDateString()];
    }
}
