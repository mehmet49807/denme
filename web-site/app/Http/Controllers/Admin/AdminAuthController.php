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

            // 2FA for admin/staff users
            if ($user->needsTwoFactor()) {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $user->forceFill([
                    'two_factor_code' => $code,
                    'two_factor_expires_at' => now()->addMinutes(10),
                ])->save();

                session(['2fa:user_id' => $user->id]);

                try {
                    app(\App\Services\UserMailService::class)->sendTwoFactorCode($user, $code);
                } catch (\Throwable) {
                    //
                }

                return redirect()->route('2fa.verify');
            }

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
