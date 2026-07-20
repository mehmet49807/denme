<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAuditService;
use App\Services\DeployGithubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminGithubController extends Controller
{
    public function index(DeployGithubService $deploy): View
    {
        return view('admin.github', $this->pageData($deploy));
    }

    public function check(Request $request, DeployGithubService $deploy): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            $data = $this->pageData($deploy);

            return response()->json([
                'health' => $data['health'],
                'workflow' => $data['workflow'],
                'runs' => $data['runs'],
                'sync' => $data['sync'],
                'alert' => $data['alert'],
            ]);
        }

        return redirect()->route('admin.github')->with('success', 'Durum yenilendi.');
    }

    public function clearCache(Request $request, DeployGithubService $deploy): RedirectResponse
    {
        $target = (string) $request->input('target', 'all');
        if (! in_array($target, ['web', 'admin', 'all'], true)) {
            $target = 'all';
        }

        $result = $deploy->clearRemoteCache($target);
        app(AdminAuditService::class)->log('github.cache', $result['message'], 'deploy');

        return redirect()->route('admin.github')->with(
            $result['ok'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function trigger(Request $request, DeployGithubService $deploy): RedirectResponse
    {
        $validated = $request->validate([
            'target' => 'required|in:all,web,admin',
            'sync_mode' => 'required|in:delta,full',
        ]);

        $result = $deploy->triggerDeploy($validated['target'], $validated['sync_mode']);
        app(AdminAuditService::class)->log(
            'github.trigger',
            $result['message'],
            'deploy',
            null,
            $validated,
        );

        return redirect()->route('admin.github')->with(
            $result['ok'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function smokeTest(DeployGithubService $deploy): RedirectResponse
    {
        $result = $deploy->runSmokeTests();
        app(AdminAuditService::class)->log(
            'github.smoke',
            'Smoke test: '.$result['overall'],
            'deploy',
            null,
            ['failed' => collect($result['checks'])->where('ok', false)->count()],
        );

        return redirect()->route('admin.github')->with(
            $result['ok'] ? 'success' : 'error',
            $result['ok']
                ? 'Smoke test başarılı ('.$result['ran_at'].').'
                : 'Smoke test başarısız — aşağıdaki sonuçları kontrol edin.'
        );
    }

    public function dismissAlert(Request $request, DeployGithubService $deploy): RedirectResponse
    {
        $sha = (string) $request->input('sha', '');
        $deploy->dismissFailureAlert($sha !== '' ? $sha : null);
        app(AdminAuditService::class)->log('github.alert.dismiss', 'Deploy fail uyarısı kapatıldı', 'deploy');

        return redirect()->route('admin.github')->with('success', 'Uyarı kapatıldı.');
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
        $lastAt = cache('deploy.last_success_at');
        $runs = $deploy->listWorkflowRuns(10);
        $smoke = cache('deploy.last_smoke');

        return [
            'config' => [
                'repo' => (string) config('deploy.repository'),
                'branch' => (string) config('deploy.branch'),
                'repo_url' => (string) config('deploy.repository'),
                'actions_url' => (string) config('deploy.actions_url'),
                'compare_url' => (string) config('deploy.compare_url'),
                'secrets_url' => (string) config('deploy.secrets_url'),
                'pulls_url' => (string) config('deploy.pulls_url'),
                'workflow_file' => (string) config('deploy.workflow_file'),
                'token_ready' => $deploy->hasGithubToken(),
            ],
            'health' => $deploy->formattedHealthChecks(),
            'workflow' => $deploy->latestWorkflowRun(),
            'runs' => $runs,
            'sync' => $deploy->commitSyncStatus(),
            'secrets' => $deploy->secretsStatus(),
            'pulls' => $deploy->openPullRequests(),
            'alert' => $deploy->failureAlert(),
            'rollback' => $deploy->rollbackInfo(),
            'smoke' => is_array($smoke) ? $smoke : null,
            'lastDeploy' => $lastAt ? [
                'sha_short' => cache('deploy.last_commit'),
                'deployed_at' => $lastAt,
                'message' => 'Hedef: '.(cache('deploy.last_target') ?? 'all'),
            ] : null,
            'requiredSecrets' => config('deploy.required_secrets', []),
            'paths' => $deploy->flattenDeployPaths(),
        ];
    }
}
