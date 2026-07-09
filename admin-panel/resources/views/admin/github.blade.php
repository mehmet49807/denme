@extends('layouts.admin')

@section('title', 'GitHub')
@section('lead', 'Otomatik deploy durumu ve hızlı bağlantılar.')

@section('header-actions')
    <form action="{{ route('admin.github.check') }}" method="POST" class="admin-inline-form" id="github-check-form">
        @csrf
        <button type="submit" class="btn btn-outline">Durumu Yenile</button>
    </form>
    <a href="{{ ($config['actions_url'] ?? '#') }}" target="_blank" rel="noopener" class="btn btn-primary">GitHub Actions</a>
@endsection

@section('content')
@php
    $c = $config ?? [];
    $h = $health ?? [];
    $wf = $workflow ?? [];
    $last = $lastDeploy ?? null;
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
    <div class="admin-flash admin-flash--success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="admin-flash admin-flash--error">{{ session('error') }}</div>
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
        <div class="admin-stat-value">{{ $last['sha_short'] ?? '—' }}</div>
        <div class="admin-stat-label">Son Deploy</div>
    </div>
</div>

<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Bağlantılar</h3>
    <ul class="admin-ai-tasks">
        <li><a href="{{ $c['repo_url'] ?? '#' }}" target="_blank" rel="noopener">Repository · {{ $c['repo'] ?? '—' }}</a></li>
        <li><a href="{{ $c['actions_url'] ?? '#' }}" target="_blank" rel="noopener">Actions · deploy geçmişi</a></li>
        <li><a href="{{ $c['secrets_url'] ?? '#' }}" target="_blank" rel="noopener">Secrets · FTP ayarları</a></li>
        <li><a href="https://gonulkoprusu.com" target="_blank" rel="noopener">Web sitesi (canlı)</a></li>
    </ul>
    <p class="admin-stat-label">Dal: <code>{{ $c['branch'] ?? 'master' }}</code>
        @if($last)
            · Son deploy: {{ $last['deployed_at'] ?? '' }}
        @endif
        @if(($wf['found'] ?? false) && !empty($wf['html_url']))
            · <a href="{{ $wf['html_url'] }}" target="_blank" rel="noopener">workflow detayı</a>
        @endif
    </p>
</div>

<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Site Kontrolleri</h3>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kontrol</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                @forelse($health['checks'] ?? [] as $check)
                    @php
                        $ok = ($check['status'] ?? '') === 'ok';
                        $warn = ($check['status'] ?? '') === 'warning';
                    @endphp
                    <tr>
                        <td>{{ $check['label'] ?? $check['id'] }}</td>
                        <td>
                            <span class="badge {{ $ok ? 'badge-premium' : ($warn ? 'badge-pending' : 'badge-banned') }}">
                                {{ strtoupper($check['status'] ?? '') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2">Kontrol verisi yok</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(!empty($health['message']))
        <p class="admin-stat-label">{{ $health['message'] }}</p>
    @endif
</div>

<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Bakım</h3>
    <p class="admin-stat-label">Deploy sonrası önbellek sorunu yaşarsanız temizleyin.</p>
    <form method="POST" action="{{ route('admin.github.clear-cache') }}" class="admin-inline-form">
        @csrf
        <button type="submit" class="btn btn-outline" onclick="return confirm('Önbellek temizlensin mi?')">Önbelleği Temizle</button>
    </form>
</div>
@endsection
