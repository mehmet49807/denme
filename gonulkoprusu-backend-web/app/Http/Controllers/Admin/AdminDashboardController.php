<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        return view('admin.dashboard', ['section' => 'User Management']);
    }

    public function messages()
    {
        return view('admin.dashboard', ['section' => 'Message Auditor']);
    }

    public function reports()
    {
        return view('admin.dashboard', ['section' => 'Complaints Dashboard']);
    }

    public function premium()
    {
        return view('admin.dashboard', ['section' => 'Premium Tracker']);
    }

    public function broadcasts()
    {
        return view('admin.dashboard', ['section' => 'Admin Broadcast System']);
    }
}
