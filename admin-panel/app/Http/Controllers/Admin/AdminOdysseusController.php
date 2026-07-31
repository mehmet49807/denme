<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAuditService;
use App\Services\OdysseusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminOdysseusController extends Controller
{
    public function index(OdysseusService $odysseus): View
    {
        $status = $odysseus->status();
        $models = $status['ok']
            ? $odysseus->modelEndpointsSummary()
            : ['ok' => false, 'endpoints' => [], 'error' => $status['message'] ?? null];

        return view('admin.odysseus', [
            'status' => $status,
            'models' => $models,
            'baseUrl' => $odysseus->baseUrl(),
            'publicUrl' => $odysseus->publicUrl(),
            'workspace' => $odysseus->workspace(),
            'history' => session('odysseus_history', []),
        ]);
    }

    public function run(Request $request, OdysseusService $odysseus, AdminAuditService $audit): RedirectResponse
    {
        $validated = $request->validate([
            'command' => 'required|string|min:3|max:8000',
        ], [
            'command.required' => 'Komut yazın.',
            'command.min' => 'Komut çok kısa.',
        ]);

        $command = trim($validated['command']);
        $result = $odysseus->runCommand($command);

        $history = session('odysseus_history', []);
        array_unshift($history, [
            'at' => now()->format('d.m.Y H:i:s'),
            'command' => $command,
            'ok' => (bool) ($result['ok'] ?? false),
            'reply' => (string) ($result['reply'] ?? ''),
            'error' => (string) ($result['error'] ?? ''),
        ]);
        $history = array_slice($history, 0, 12);
        session(['odysseus_history' => $history]);

        $audit->log(
            $result['ok'] ? 'odysseus.command' : 'odysseus.command_failed',
            mb_substr($command, 0, 240),
            'odysseus'
        );

        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->route('admin.odysseus')
                ->withInput()
                ->with('error', $result['error'] ?: 'Odysseus komutu başarısız.');
        }

        return redirect()
            ->route('admin.odysseus')
            ->with('success', 'Komut Odysseus agent’a iletildi; kod değişiklikleri workspace üzerinde uygulandı.');
    }

    public function refreshStatus(OdysseusService $odysseus): RedirectResponse
    {
        $status = $odysseus->status();

        return redirect()
            ->route('admin.odysseus')
            ->with($status['ok'] ? 'success' : 'error', $status['message']);
    }
}
