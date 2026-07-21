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
        // Admin alt alanıyla paylaşılan .gonulkoprusu.com session/remember çerezlerini kes.
        // Host-only çerez: gonulkoprusu.com ↔ admin.gonulkoprusu.com sızıntısı olmaz.
        $sessionDomain = (string) config('session.domain');
        if ($sessionDomain !== '' && str_contains(ltrim($sessionDomain, '.'), 'gonulkoprusu.com')) {
            config(['session.domain' => null]);
        }

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

