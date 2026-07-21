<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ApplyUserLocale;
use App\Http\Middleware\CaptureGrowthAttribution;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetupAccessMiddleware;
use App\Http\Middleware\TrackLastActive;
use App\Support\AdminApp;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'locale' => ApplyUserLocale::class,
            'setup' => SetupAccessMiddleware::class,
        ]);

        $middleware->encryptCookies(except: [
            'gk_locale',
        ]);

        $middleware->appendToGroup('web', [CaptureGrowthAttribution::class]);

        if (class_exists(SetLocale::class)) {
            $middleware->appendToGroup('web', [SetLocale::class]);
        }

        if (class_exists(TrackLastActive::class)) {
            $middleware->appendToGroup('web', [TrackLastActive::class]);
            $middleware->appendToGroup('api', [TrackLastActive::class]);
        }

        $middleware->redirectGuestsTo(function (Request $request) {
            if (AdminApp::isSubdomainRequest()) {
                return AdminApp::loginPath();
            }

            if (str_starts_with(trim($request->path(), '/'), 'adminlogin')) {
                return 'https://admin.gonulkoprusu.com/login';
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

