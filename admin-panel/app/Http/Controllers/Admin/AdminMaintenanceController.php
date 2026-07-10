<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeployGithubService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMaintenanceController extends Controller
{
    public function index(DeployGithubService $deploy): View
    {
        $health = $deploy->formattedHealthChecks();
        $cacheChecks = array_values(array_filter(
            $health['checks'] ?? [],
            fn (array $check): bool => in_array($check['id'] ?? '', ['web_cache', 'admin_cache'], true)
        ));

        return view('admin.maintenance', [
            'cacheChecks' => $cacheChecks,
            'webUrl' => rtrim((string) config('deploy.web_url'), '/'),
            'adminUrl' => rtrim((string) config('deploy.admin_url'), '/'),
        ]);
    }

    public function clearCache(Request $request, DeployGithubService $deploy): RedirectResponse
    {
        $target = (string) $request->input('target', 'all');
        if (! in_array($target, ['web', 'admin', 'all'], true)) {
            $target = 'all';
        }

        $result = $deploy->clearRemoteCache($target);
        $returnTo = (string) $request->input('return_to', 'maintenance');

        if ($returnTo === 'back') {
            return redirect()->back()->with(
                $result['ok'] ? 'success' : 'error',
                $result['message']
            );
        }

        $route = $returnTo === 'github' ? 'admin.github' : 'admin.maintenance';

        return redirect()->route($route)->with(
            $result['ok'] ? 'success' : 'error',
            $result['message']
        );
    }
}
