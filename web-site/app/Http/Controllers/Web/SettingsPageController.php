<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsPageController extends Controller
{
    public function profile(Request $request): View
    {
        return view('web.settings.profile', [
            'user' => $request->user(),
        ]);
    }

    public function hobbies(Request $request): View
    {
        return view('web.settings.hobbies', [
            'user' => $request->user(),
        ]);
    }

    public function language(Request $request): View
    {
        return view('web.settings.language', [
            'user' => $request->user(),
        ]);
    }

    public function password(Request $request): View
    {
        return view('web.settings.password', [
            'user' => $request->user(),
        ]);
    }
}
