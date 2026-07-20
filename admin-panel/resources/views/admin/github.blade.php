@extends('layouts.admin')

@section('title', 'GitHub')
@section('lead', 'Deploy tetikleme, Actions geçmişi, canlı commit ve sağlık kontrolleri.')

@section('header-actions')
    <form action="{{ route('admin.github.check') }}" method="POST" class="admin-inline-form">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm">Durumu Yenile</button>
    </form>
    <form action="{{ route('admin.github.smoke') }}" method="POST" class="admin-inline-form">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm">Smoke Test</button>
    </form>
    <a href="{{ ($config['actions_url'] ?? '#') }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm">GitHub Actions</a>
@endsection

@section('content')
@php
    $c = $config ?? [];
    $h = $health ?? [];
    $wf = $workflow ?? [];
    $last = $lastDeploy ?? null;
    $sync = $sync ?? [];
    $runsPayload = $runs ?? ['ok' => false, 'runs' => []];
    $secrets = $secrets ?? ['items' => [], 'token_ready' => false];
    $pulls = $pulls ?? ['items' => [], 'count' => 0];
    $alert = $alert ?? ['active' => false];
    $rollback = $rollback ?? [];
    $smoke = $smoke ?? null;
    $overall = $h['overall'] ?? 'unknown';
    $statusClass = match ($overall) {
        'ok' => 'admin-stat-card--emerald',
        'warning' => 'admin-stat-card--gold',
        'error' => 'admin-stat-card--coral',
        default => 'admin-stat-card--indigo',
    };
    $wfLabel = ($wf['found'] ?? false)
        ? strtoupper((string) ($wf['conclusion'] ?? $wf['status'] ?? '—'))
        : '—';
@endphp

@if(session('success'))
    <div class="admin-ai-flash admin-ai-flash--ok">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="admin-ai-flash admin-ai-flash--bad">{{ session('error') }}</div>
@endif

@if(!empty($alert['active']) && !empty($alert['run']))
    <div class="admin-ai-flash admin-ai-flash--bad admin-github-alert">
        <div>
            <strong>Deploy başarısız</strong>
            <div class="admin-ops-meta">
                SHA {{ $alert['run']['sha_short'] ?? '—' }}
                · {{ $alert['run']['display_title'] ?? '' }}
                @if(!empty($alert['run']['html_url']))
                    · <a href="{{ $alert['run']['html_url'] }}" target="_blank" rel="noopener">workflow log</a>
                @endif
            </div>
        </div>
        <form method="POST" action="{{ route('admin.github.alert.dismiss') }}">
            @csrf
            <input type="hidden" name="sha" value="{{ $alert['run']['head_sha'] ?? '' }}">
            <button type="submit" class="btn btn-outline btn-sm">Uyarıyı kapat</button>
        </form>
    </div>
@endif

<div class="admin-stat-grid admin-stat-grid--compact">
    <div class="admin-stat-card {{ $statusClass }}">
        <div class="admin-stat-value">{{ strtoupper($overall) }}</div>
        <div class="admin-stat-label">Sistem</div>
    </div>
    <div class="admin-stat-card admin-stat-card--indigo">
        <div class="admin-stat-value">{{ $wfLabel }}</div>
        <div class="admin-stat-label">Son Workflow</div>
    </div>
    <div class="admin-stat-card admin-stat-card--gold">
        <div class="admin-stat-value">{{ $last['sha_short'] ?? ($sync['live_sha'] ?? '—') }}</div>
        <div class="admin-stat-label">Canlı Commit</div>
    </div>
    <div class="admin-stat-card {{ !empty($sync['in_sync']) ? 'admin-stat-card--emerald' : 'admin-stat-card--coral' }}">
        <div class="admin-stat-value">{{ !empty($sync['in_sync']) ? 'SYNC' : 'DIFF' }}</div>
        <div class="admin-stat-label">Master ↔ Canlı</div>
    </div>
    <div class="admin-stat-card admin-stat-card--violet">
        <div class="admin-stat-value">{{ $pulls['count'] ?? 0 }}</div>
        <div class="admin-stat-label">Açık PR</div>
    </div>
</div>

<div class="admin-ai-topgrid">
    <section class="admin-panel admin-panel--glass">
        <h3 class="admin-panel-title">Deploy Tetikle</h3>
        @if(empty($c['token_ready']))
            <p class="admin-ops-meta">Panelden tetiklemek için sunucuda <code>DEPLOY_GITHUB_TOKEN</code> tanımlayın (repo + <code>actions:write</code>). Yoksa Actions sayfasından manuel çalıştırın.</p>
        @endif
        <form method="POST" action="{{ route('admin.github.trigger') }}" class="admin-github-trigger" onsubmit="return confirm('Deploy başlatılsın mı?');">
            @csrf
            <div class="admin-ai-settings-grid">
                <div class="form-group">
                    <label>Hedef</label>
                    <select name="target">
                        <option value="all">Web + Admin</option>
                        <option value="web">Yalnızca Web</option>
                        <option value="admin">Yalnızca Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Sync modu</label>
                    <select name="sync_mode">
                        <option value="delta">Delta (hızlı)</option>
                        <option value="full">Full sync</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" {{ empty($c['token_ready']) ? 'disabled' : '' }}>Deploy Başlat</button>
            <a href="{{ $c['actions_url'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-outline">Actions’ta çalıştır</a>
        </form>
    </section>

    <section class="admin-panel admin-panel--glass">
        <h3 class="admin-panel-title">Canlı Commit</h3>
        <div class="admin-ops-row">
            <div>
                <strong>Master HEAD</strong>
                <span class="admin-ops-meta"><code>{{ $sync['head_sha'] ?? '—' }}</code> · {{ $sync['head_message'] ?? '' }}</span>
            </div>
        </div>
        <div class="admin-ops-row">
            <div>
                <strong>Canlı</strong>
                <span class="admin-ops-meta"><code>{{ $sync['live_sha'] ?? '—' }}</code>
                    @if(!empty($sync['live_at'])) · {{ $sync['live_at'] }}@endif
                    @if(!empty($sync['live_target'])) · {{ $sync['live_target'] }}@endif
                </span>
            </div>
        </div>
        <div class="admin-quick-actions" style="margin-top:.75rem">
            <a class="btn btn-outline btn-sm" href="{{ $sync['compare_url'] ?? $c['compare_url'] }}" target="_blank" rel="noopener">Diff / Compare</a>
            <a class="btn btn-outline btn-sm" href="{{ $c['repo_url'] ?? '#' }}" target="_blank" rel="noopener">Repository</a>
            <a class="btn btn-outline btn-sm" href="{{ $c['pulls_url'] ?? '#' }}" target="_blank" rel="noopener">Pull Requests</a>
        </div>
    </section>
</div>

<div class="admin-panel admin-panel--glass">
    <div class="admin-panel-head">
        <h3 class="admin-panel-title">Actions Geçmişi (son 10)</h3>
        @if(!($runsPayload['ok'] ?? false))
            <span class="admin-ops-meta">{{ $runsPayload['message'] ?? '' }}</span>
        @endif
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Başlık</th>
                    <th>SHA</th>
                    <th>Durum</th>
                    <th>Olay</th>
                    <th>Zaman</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse(($runsPayload['runs'] ?? []) as $run)
                    <tr>
                        <td>{{ $run['run_number'] ?? '—' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($run['display_title'] ?? 'Deploy', 60) }}</td>
                        <td><code>{{ $run['sha_short'] ?? '—' }}</code></td>
                        <td>
                            @php $st = strtolower((string) ($run['status'] ?? '')); @endphp
                            <span class="admin-badge {{ $st === 'success' ? 'admin-badge--warn' : ($st === 'failure' ? 'admin-badge--danger' : 'admin-badge--warn') }}" style="{{ $st === 'success' ? 'background:#d1fae5;color:#065f46' : '' }}">
                                {{ strtoupper($run['status'] ?? '—') }}
                            </span>
                        </td>
                        <td>{{ $run['event'] ?? '—' }}</td>
                        <td>{{ isset($run['created_at']) ? \Illuminate\Support\Carbon::parse($run['created_at'])->timezone('Europe/Istanbul')->format('d.m H:i') : '—' }}</td>
                        <td>
                            @if(!empty($run['html_url']))
                                <a href="{{ $run['html_url'] }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Log</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">Workflow kaydı yok veya API erişilemedi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="admin-ai-topgrid">
    <section class="admin-panel admin-panel--glass">
        <h3 class="admin-panel-title">Secrets Kontrolü</h3>
        <p class="admin-ops-meta">{{ $secrets['hint'] ?? 'Değerler gösterilmez — yalnızca tanımlı mı kontrol edilir.' }}</p>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Secret</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($secrets['items'] ?? [] as $secret)
                        <tr>
                            <td><code>{{ $secret['name'] }}</code></td>
                            <td>
                                @if($secret['present'] === true)
                                    <span class="admin-badge" style="background:#d1fae5;color:#065f46">{{ $secret['label'] ?? 'VAR' }}</span>
                                @elseif($secret['present'] === false)
                                    <span class="admin-badge admin-badge--danger">{{ $secret['label'] ?? 'EKSİK' }}</span>
                                @else
                                    <span class="admin-badge admin-badge--warn">{{ $secret['label'] ?? 'Kontrol edilemedi' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="admin-quick-actions" style="margin-top:.75rem">
            <a href="{{ $c['secrets_url'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">GitHub Secrets</a>
            @if(empty($secrets['token_ready']))
                <span class="admin-ops-meta">Kesin liste için admin `.env` → <code>DEPLOY_GITHUB_TOKEN</code></span>
            @endif
        </div>
    </section>

    <section class="admin-panel admin-panel--glass">
        <h3 class="admin-panel-title">Açık Pull Request’ler</h3>
        @forelse($pulls['items'] ?? [] as $pr)
            <div class="admin-ops-row">
                <div>
                    <strong>#{{ $pr['number'] }} {{ $pr['title'] }}</strong>
                    <span class="admin-ops-meta">{{ $pr['head'] }} → {{ $pr['base'] }} · {{ $pr['user'] }}{{ !empty($pr['draft']) ? ' · draft' : '' }}</span>
                </div>
                @if(!empty($pr['html_url']))
                    <a href="{{ $pr['html_url'] }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Aç</a>
                @endif
            </div>
        @empty
            <p class="admin-ops-empty">Açık PR yok.</p>
        @endforelse
    </section>
</div>

<div class="admin-ai-topgrid">
    <section class="admin-panel admin-panel--glass">
        <h3 class="admin-panel-title">Geri Dönüş (Rollback)</h3>
        <p class="admin-ops-meta">Önceki başarılı deploy bilgisi ve önerilen adımlar.</p>
        <div class="admin-ops-row">
            <div>
                <strong>Önceki başarılı SHA</strong>
                <span class="admin-ops-meta"><code>{{ $rollback['previous_sha'] ?? '—' }}</code></span>
            </div>
        </div>
        <ol class="admin-github-steps">
            @foreach($rollback['steps'] ?? [] as $step)
                <li>{{ $step }}</li>
            @endforeach
        </ol>
        <div class="admin-quick-actions">
            @if(!empty($rollback['rerun_url']))
                <a href="{{ $rollback['rerun_url'] }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Önceki run</a>
            @endif
            @if(!empty($rollback['compare_url']))
                <a href="{{ $rollback['compare_url'] }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Compare</a>
            @endif
        </div>
    </section>

    <section class="admin-panel admin-panel--glass">
        <h3 class="admin-panel-title">Site Kontrolleri</h3>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Kontrol</th>
                        <th>Durum</th>
                        <th>Detay</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($h['checks'] ?? [] as $check)
                        <tr>
                            <td>{{ $check['label'] ?? $check['id'] }}</td>
                            <td>
                                <span class="admin-badge {{ ($check['status'] ?? '') === 'ok' ? '' : (($check['status'] ?? '') === 'warning' ? 'admin-badge--warn' : 'admin-badge--danger') }}"
                                      style="{{ ($check['status'] ?? '') === 'ok' ? 'background:#d1fae5;color:#065f46' : '' }}">
                                    {{ strtoupper($check['status'] ?? '') }}
                                </span>
                            </td>
                            <td class="admin-ops-meta">{{ $check['message'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <form method="POST" action="{{ route('admin.github.clear-cache') }}" class="admin-inline-form" style="margin-top:.85rem" onsubmit="return confirm('Önbellek temizlensin mi?');">
            @csrf
            <input type="hidden" name="target" value="all">
            <button type="submit" class="btn btn-outline btn-sm">Önbelleği Temizle</button>
        </form>
    </section>
</div>

@if(is_array($smoke))
<div class="admin-panel admin-panel--glass">
    <div class="admin-panel-head">
        <h3 class="admin-panel-title">Son Smoke Test</h3>
        <span class="admin-ops-meta">{{ $smoke['ran_at'] ?? '' }} · {{ strtoupper($smoke['overall'] ?? '') }}</span>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kontrol</th>
                    <th>Sonuç</th>
                    <th>ms</th>
                </tr>
            </thead>
            <tbody>
                @foreach($smoke['checks'] ?? [] as $check)
                    <tr>
                        <td>{{ $check['label'] }}</td>
                        <td>
                            <span class="admin-badge {{ !empty($check['ok']) ? '' : 'admin-badge--danger' }}"
                                  style="{{ !empty($check['ok']) ? 'background:#d1fae5;color:#065f46' : '' }}">
                                {{ $check['message'] ?? '' }}
                            </span>
                        </td>
                        <td>{{ $check['ms'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Deploy Yolları</h3>
    <ul class="admin-ai-tasks">
        @foreach($paths ?? [] as $group => $pathText)
            <li><strong>{{ strtoupper($group) }}:</strong> {{ $pathText }}</li>
        @endforeach
    </ul>
</div>
@endsection
