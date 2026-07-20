<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAuditService;
use App\Services\LaravelUpdateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUpdateController extends Controller
{
    public function index(LaravelUpdateService $updater): View
    {
        $local = $updater->localStatus();
        $web = $updater->probeRemote('web');
        $adminRemote = $updater->probeRemote('admin');

        return view('admin.updates', [
            'local' => $local,
            'web' => $web,
            'adminRemote' => $adminRemote,
            'history' => $updater->history(),
            'packagist' => $local['packagist'] ?? [],
        ]);
    }

    public function run(Request $request, LaravelUpdateService $updater): RedirectResponse
    {
        $validated = $request->validate([
            'target' => 'required|in:admin,web,both',
            'mode' => 'required|in:target,patch',
        ]);

        $results = [];
        $allOk = true;

        if (in_array($validated['target'], ['admin', 'both'], true)) {
            // Ayrı PHP süreci: opcache + uzun süre için setup endpoint
            $remote = $updater->runRemoteUpdate('admin', $validated['mode']);
            if (! ($remote['ok'] ?? false)) {
                $local = $updater->runUpdate($validated['mode']);
                $results[] = 'Admin: '.$local['message'];
                $allOk = $allOk && ($local['ok'] ?? false);
            } else {
                $results[] = 'Admin: '.$remote['message'];
                $allOk = $allOk && true;
            }
        }

        if (in_array($validated['target'], ['web', 'both'], true)) {
            $web = $updater->runRemoteUpdate('web', $validated['mode']);
            $results[] = 'Web: '.$web['message'];
            $allOk = $allOk && ($web['ok'] ?? false);
        }

        app(AdminAuditService::class)->log(
            'laravel.update',
            implode(' | ', $results),
            'system',
            null,
            $validated + ['ok' => $allOk],
        );

        return redirect()->route('admin.updates')->with(
            $allOk ? 'success' : 'error',
            implode(' · ', $results)
        );
    }

    public function refresh(LaravelUpdateService $updater): RedirectResponse
    {
        cache()->forget('laravel_packagist_latest_v1');

        return redirect()->route('admin.updates')->with('success', 'Sürüm bilgileri yenilendi.');
    }
}
