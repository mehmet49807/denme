<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function loginForm(Request $request)
    {
        if (Auth::check() && $request->user()?->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->login;

        $user = User::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('username', $login);
        })->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->is_banned) {
                return back()->withErrors(['login' => 'Hesabınız askıya alınmıştır.'])->withInput();
            }

            if (! $user->isStaff()) {
                return back()->withErrors(['login' => 'Yönetici yetkisi gereklidir.'])->withInput();
            }

            // Admin girişinde 2FA adımı devre dışı bırakıldı; doğrudan oturum açılır.
            $user->clearTwoFactor();
            Auth::login($user, $request->boolean('remember'));

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['login' => 'Giriş bilgileri hatalı.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
