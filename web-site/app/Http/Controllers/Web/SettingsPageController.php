<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsPageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $initialPanel = (string) $request->query(
            'panel',
            old('settings_panel', session('settings_panel', 'menu'))
        );

        if ($request->session()->has('settings_panel')) {
            $request->session()->forget('settings_panel');
        }

        return view('web.settings', compact('user', 'initialPanel'));
    }
}
