<?php

namespace App\Providers;

use App\Support\SidebarBadgeCounts;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['partials.app-sidebar', 'layouts.app'], function ($view) {
            $user = auth()->user();
            if (! $user) {
                $view->with([
                    'unreadNotifications' => 0,
                    'unreadMessages' => 0,
                ]);

                return;
            }

            $counts = SidebarBadgeCounts::forUser($user);

            $view->with([
                'unreadNotifications' => $counts['notifications'],
                'unreadMessages' => $counts['messages'],
            ]);
        });
    }
}

