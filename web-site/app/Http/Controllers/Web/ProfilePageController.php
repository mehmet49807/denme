<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ValidatesHobbies;
use App\Http\Concerns\ValidatesLocation;
use App\Models\Like;
use App\Models\Post;
use App\Services\LocationDataService;
use App\Services\MediaUploadService;
use App\Services\StoryGroupService;
use App\Support\LocaleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('web.profile', compact('user', 'posts', 'ownStoryGroup', 'likedPostIds'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,'.$request->user()->id,
            'phone' => 'sometimes|string|max:20',
        ]);

        if ($request->hasAny(['country', 'city', 'district'])) {
            $validated = array_merge($validated, $this->validateLocationInput($request, $this->locations, false));
        }

        $validated = array_merge($validated, $this->validateHobbiesInput($request));

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
            return redirect()
                ->route('settings', ['panel' => 'password'])
                ->withErrors(['current_password' => 'Mevcut şifre hatalı.']);
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

    private function persistLocale(Request $request, string $locale): RedirectResponse
    {
        LocaleManager::remember($request, $locale);
        LocaleManager::apply($locale);

        return redirect()
            ->route('settings', ['panel' => 'language'])
            ->with('success', 'Dil tercihiniz kaydedildi.')
            ->withCookie(LocaleManager::makeCookie($locale, $request->isSecure()));
    }

    private function settingsRedirect(Request $request, string $message): RedirectResponse
    {
        $panel = $request->input('settings_panel', 'menu');

        return redirect()
            ->route('settings', ['panel' => $panel])
            ->with('success', $message);
    }
}
