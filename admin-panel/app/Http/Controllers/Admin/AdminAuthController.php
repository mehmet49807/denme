<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function index(): RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    }

    public function loginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
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

        if (!$user || !Hash::check($request->password, $user->password) || $user->is_banned) {
            return back()->withErrors(['login' => 'Giriş bilgileri hatalı.']);
        }

        if (!$user->isAdmin()) {
            return back()->withErrors(['login' => 'Bu alan yalnızca yöneticiler içindir.']);
        }

        Auth::login($user);

        return redirect()->route('admin.dashboard');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        return redirect()->route('admin.login');
    }
}
