<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function index(): RedirectResponse
    {
        if (AdminApp::userIsStaff(Auth::user())) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::check() || AdminApp::requestHasSharedRememberCookie(request())) {
            AdminApp::purgeNonStaffAuth(request());
        }

        return redirect()->route('admin.login');
    }

    public function loginForm(): View|RedirectResponse
    {
        if (AdminApp::userIsStaff(Auth::user())) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::check() || AdminApp::requestHasSharedRememberCookie(request())) {
            AdminApp::purgeNonStaffAuth(request());
        }

        return view('admin.login');
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

        if (! $user || ! Hash::check($request->password, $user->password) || $user->is_banned) {
            return back()->withErrors(['login' => 'Giriş bilgileri hatalı.']);
        }

        if (! AdminApp::userIsStaff($user)) {
            AdminApp::purgeNonStaffAuth($request);

            return back()->withErrors(['login' => 'Bu alan yalnızca yöneticiler içindir.']);
        }

        // Ana siteden kalan remember çerezini temizle; admin oturumu ayrı kalsın.
        AdminApp::expireSharedAuthCookies($request);

        Auth::login($user, false);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        AdminApp::expireSharedAuthCookies($request);

        return redirect()->route('admin.login');
    }
}
