<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminGithubController extends Controller
{
    public function index(): View
    {
        $repo = (string) config('deploy.repository');
        $branch = (string) config('deploy.branch');
        $actionsUrl = (string) config('deploy.actions_url');

        return view('admin.github', [
            'repo' => $repo,
            'branch' => $branch,
            'actionsUrl' => $actionsUrl,
            'lastDeployAt' => Cache::get('deploy.last_success_at'),
            'lastDeployTarget' => Cache::get('deploy.last_target'),
            'lastDeployCommit' => Cache::get('deploy.last_commit'),
        ]);
    }
}
