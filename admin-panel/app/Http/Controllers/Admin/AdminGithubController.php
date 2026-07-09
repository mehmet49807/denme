<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeployGithubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AdminGithubController extends Controller
{
    public function index(DeployGithubService $deploy): View
    {
        return view('admin.github', $this->pageData($deploy));
    }

    public function check(DeployGithubService $deploy): JsonResponse
    {
        $data = $this->pageData($deploy);

        return response()->json([
            'health' => $data['health'],
            'workflow' => $data['workflow'],
        ]);
    }

    public function clearCache(Request $request, DeployGithubService $deploy): RedirectResponse
    {
        $target = (string) $request->input('target', 'all');
        if (! in_array($target, ['web', 'admin', 'all'], true)) {
            $target = 'all';
        }

        $result = $deploy->clearRemoteCache($target);

        return redirect()->route('admin.github')->with(
            $result['ok'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function deployNotify(Request $request, DeployGithubService $deploy): Response
    {
        if ($request->query('key') !== config('deploy.setup_key')) {
            abort(403);
        }

        $target = (string) $request->query('target', 'all');
        $commit = (string) $request->query('commit', '');

        $deploy->recordDeploy($target, $commit !== '' ? $commit : null);

        return response("deploy recorded\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function pageData(DeployGithubService $deploy): array
    {
        $lastAt = Cache::get('deploy.last_success_at');

        return [
            'config' => [
                'repo' => (string) config('deploy.repository'),
                'branch' => (string) config('deploy.branch'),
                'repo_url' => (string) config('deploy.repository'),
                'actions_url' => (string) config('deploy.actions_url'),
                'compare_url' => (string) config('deploy.compare_url'),
                'secrets_url' => (string) config('deploy.secrets_url'),
                'workflow_file' => (string) config('deploy.workflow_file'),
            ],
            'health' => $deploy->formattedHealthChecks(),
            'workflow' => $deploy->latestWorkflowRun(),
            'lastDeploy' => $lastAt ? [
                'sha_short' => Cache::get('deploy.last_commit'),
                'deployed_at' => $lastAt,
                'message' => 'Hedef: '.(Cache::get('deploy.last_target') ?? 'all'),
            ] : null,
            'secrets' => config('deploy.required_secrets', []),
            'paths' => $deploy->flattenDeployPaths(),
        ];
    }
}
