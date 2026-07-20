<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use RuntimeException;

class AdminProfileController extends Controller
{
    public function __construct(
        private MediaUploadService $mediaUpload,
    ) {}

    public function show(Request $request): View
    {
        return view('admin.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username,'.$user->id.'|regex:/^[a-zA-Z0-9_]+$/',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:191|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
        ], [
            'username.required' => 'Kullanıcı adı zorunludur.',
            'username.min' => 'Kullanıcı adı en az 3 karakter olmalıdır.',
            'username.max' => 'Kullanıcı adı en fazla 50 karakter olabilir.',
            'username.unique' => 'Bu kullanıcı adı başka bir hesapta kayıtlı.',
            'username.regex' => 'Kullanıcı adı yalnızca harf, rakam ve alt çizgi içerebilir.',
            'first_name.required' => 'Ad alanı zorunludur.',
            'last_name.required' => 'Soyad alanı zorunludur.',
            'email.required' => 'E-posta alanı zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.unique' => 'Bu e-posta adresi başka bir hesapta kayıtlı.',
        ]);

        $user->update($validated);

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Profil bilgileriniz güncellendi.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ], [
            'photo.required' => 'Lütfen bir görsel seçin.',
            'photo.image' => 'Yalnızca görsel dosyası yükleyebilirsiniz.',
            'photo.max' => 'Görsel en fazla 5 MB olabilir.',
        ]);

        $user = $request->user();

        try {
            $this->mediaUpload->deleteByUrl($user->profile_photo_url);
            $url = $this->mediaUpload->uploadProfilePhoto($request->file('photo'));
            $user->update(['profile_photo_url' => $url]);
        } catch (RuntimeException) {
            return back()->withErrors(['photo' => 'Profil fotoğrafı yüklenemedi. Lütfen tekrar deneyin.']);
        }

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Profil fotoğrafınız güncellendi.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|confirmed|min:8',
        ], [
            'current_password.required' => 'Mevcut şifrenizi girin.',
            'password.required' => 'Yeni şifre zorunludur.',
            'password.confirmed' => 'Yeni şifre tekrarı eşleşmiyor.',
            'password.min' => 'Yeni şifre en az 8 karakter olmalıdır.',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mevcut şifre hatalı.']);
        }

        $user->update([
            'password' => $request->password,
        ]);

        return redirect()
            ->route('admin.profile')
            ->with('success', 'Şifreniz güncellendi.');
    }
}
