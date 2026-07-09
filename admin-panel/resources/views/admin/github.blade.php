@extends('layouts.admin')

@section('title', 'GitHub & Deploy')

@section('content')
@php
    $c = $config ?? [];
    $h = $health ?? [];
    $wf = $workflow ?? [];
    $last = $lastDeploy ?? null;
    $secrets = $secrets ?? [];
    $paths = $paths ?? [];
    $checks = $h['checks'] ?? [];
    $overall = $h['overall'] ?? 'unknown';
    $badge = match ($overall) {
        'ok' => 'success',
        'warning' => 'warning',
        'error' => 'danger',
        default => 'secondary',
    };
    $wfBadge = match ($wf['conclusion'] ?? $wf['status'] ?? '') {
        'success' => 'success',
        'failure' => 'danger',
        'in_progress' => 'info',
        'queued' => 'secondary',
        default => 'secondary',
    };
@endphp

<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">GitHub & Otomatik Deploy</h1>
            <p class="text-muted mb-0">Repo, workflow, secret ve canlı site kontrolleri</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" id="btn-run-checks">
                <i class="bi bi-arrow-repeat me-1"></i> Kontrolleri yenile
            </button>
            <a href="{{ $c['actions_url'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-dark btn-sm">
                <i class="bi bi-github me-1"></i> GitHub Actions
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Özet kartlar --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Sistem durumu</div>
                    <span class="badge bg-{{ $badge }} fs-6" id="overall-badge">{{ strtoupper($overall) }}</span>
                    <div class="small text-muted mt-2" id="overall-message">{{ $h['message'] ?? '' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Son workflow</div>
                    @if($wf['found'] ?? false)
                        <span class="badge bg-{{ $wfBadge }}">{{ $wf['conclusion'] ?? $wf['status'] ?? '—' }}</span>
                        <div class="small mt-2">
                            <a href="{{ $wf['html_url'] ?? '#' }}" target="_blank" rel="noopener" class="text-decoration-none">
                                {{ \Illuminate\Support\Str::limit($wf['head_sha'] ?? '', 7, '') }}
                            </a>
                            @if(!empty($wf['created_at']))
                                <span class="text-muted"> · {{ \Carbon\Carbon::parse($wf['created_at'])->diffForHumans() }}</span>
                            @endif
                        </div>
                    @else
                        <span class="text-muted small">{{ $wf['message'] ?? 'Bilinmiyor' }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Son deploy (kayıt)</div>
                    @if($last)
                        <div class="fw-semibold">{{ $last['sha_short'] ?? '—' }}</div>
                        <div class="small text-muted">{{ $last['deployed_at'] ?? '' }}</div>
                        <div class="small">{{ $last['message'] ?? '' }}</div>
                    @else
                        <span class="text-muted small">Henüz kayıt yok</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Repo</div>
                    <a href="{{ $c['repo_url'] ?? '#' }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">
                        {{ $c['repo'] ?? '—' }}
                    </a>
                    <div class="small text-muted mt-1">Dal: <code>{{ $c['branch'] ?? 'master' }}</code></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Sağlık kontrolleri --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-heart-pulse me-2"></i>Canlı kontroller</strong>
                    <span class="text-muted small" id="checks-updated">{{ now()->format('H:i:s') }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Kontrol</th>
                                    <th>Durum</th>
                                    <th>Detay</th>
                                </tr>
                            </thead>
                            <tbody id="checks-tbody">
                                @forelse($checks as $check)
                                    @php
                                        $cb = match ($check['status'] ?? '') {
                                            'ok' => 'success',
                                            'warning' => 'warning',
                                            'error' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ $check['label'] ?? $check['id'] }}</td>
                                        <td><span class="badge bg-{{ $cb }}">{{ strtoupper($check['status'] ?? '') }}</span></td>
                                        <td class="small text-muted">{{ $check['message'] ?? '' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-muted text-center py-4">Kontrol yok</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Deploy yolları --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom"><strong><i class="bi bi-folder2-open me-2"></i>Deploy yolları</strong></div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        @foreach($paths as $key => $path)
                            <dt class="col-sm-3 text-muted">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="col-sm-9"><code>{{ $path }}</code></dd>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>

        {{-- Ayarlar & işlemler --}}
        <div class="col-lg-5">
            {{-- GitHub Secrets --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-key me-2"></i>GitHub Secrets</strong>
                    <a href="{{ $c['secrets_url'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Ayarlar</a>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($secrets as $secret)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <code class="small">{{ $secret }}</code>
                            <span class="badge bg-secondary">GitHub'da tanımlı olmalı</span>
                        </li>
                    @endforeach
                </ul>
                <div class="card-body border-top small text-muted">
                    <a href="{{ $c['compare_url'] ?? '#' }}" target="_blank" rel="noopener">master...main karşılaştır</a>
                    · Workflow: <code>{{ $c['workflow_file'] ?? '' }}</code>
                </div>
            </div>

            {{-- Hızlı linkler --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom"><strong><i class="bi bi-link-45deg me-2"></i>Hızlı linkler</strong></div>
                <div class="list-group list-group-flush">
                    <a href="{{ $c['repo_url'] ?? '#' }}" target="_blank" rel="noopener" class="list-group-item list-group-item-action">Repository</a>
                    <a href="{{ $c['actions_url'] ?? '#' }}" target="_blank" rel="noopener" class="list-group-item list-group-item-action">Actions (deploy geçmişi)</a>
                    <a href="{{ $c['secrets_url'] ?? '#' }}" target="_blank" rel="noopener" class="list-group-item list-group-item-action">Secrets ayarları</a>
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="list-group-item list-group-item-action">Web sitesi (canlı)</a>
                    <a href="https://gonulkoprusu.com" target="_blank" rel="noopener" class="list-group-item list-group-item-action">gonulkoprusu.com</a>
                </div>
            </div>

            {{-- Bakım --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><strong><i class="bi bi-tools me-2"></i>Bakım</strong></div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Deploy sonrası eski CSS/JS önbelleğini temizlemek için Laravel cache temizleyin.</p>
                    <form method="POST" action="{{ route('admin.github.clear-cache') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm w-100" onclick="return confirm('Config, view ve route önbelleği temizlensin mi?')">
                            <i class="bi bi-trash3 me-1"></i> Laravel önbelleğini temizle
                        </button>
                    </form>
                </div>
            </div>

            {{-- Nasıl deploy edilir --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom"><strong><i class="bi bi-info-circle me-2"></i>Deploy nasıl çalışır?</strong></div>
                <div class="card-body small text-muted">
                    <ol class="mb-0 ps-3">
                        <li class="mb-2">Kod değişikliğini <code>master</code> dalına push edin veya PR birleştirin.</li>
                        <li class="mb-2">GitHub Actions <strong>Deploy to cPanel</strong> workflow'u otomatik başlar.</li>
                        <li class="mb-2">FTP ile web + admin dosyaları sunucuya kopyalanır.</li>
                        <li>Bu sayfadaki kontroller canlı dosyaları ve workflow durumunu gösterir.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const checkUrl = @json(route('admin.github.check'));
    const csrf = @json(csrf_token());
    const btn = document.getElementById('btn-run-checks');
    const tbody = document.getElementById('checks-tbody');
    const badge = document.getElementById('overall-badge');
    const msg = document.getElementById('overall-message');
    const updated = document.getElementById('checks-updated');

    function statusBadge(s) {
        const map = { ok: 'success', warning: 'warning', error: 'danger' };
        return map[s] || 'secondary';
    }

    function renderChecks(checks) {
        if (!checks || !checks.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-muted text-center py-4">Kontrol yok</td></tr>';
            return;
        }
        tbody.innerHTML = checks.map(c => `
            <tr>
                <td>${c.label || c.id}</td>
                <td><span class="badge bg-${statusBadge(c.status)}">${(c.status || '').toUpperCase()}</span></td>
                <td class="small text-muted">${c.message || ''}</td>
            </tr>
        `).join('');
    }

    async function runChecks() {
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Kontrol ediliyor...'; }
        try {
            const res = await fetch(checkUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: '{}',
            });
            const data = await res.json();
            if (data.health) {
                const h = data.health;
                if (badge) {
                    badge.className = 'badge bg-' + statusBadge(h.overall) + ' fs-6';
                    badge.textContent = (h.overall || 'unknown').toUpperCase();
                }
                if (msg) msg.textContent = h.message || '';
                renderChecks(h.checks || []);
            }
            if (updated) updated.textContent = new Date().toLocaleTimeString('tr-TR');
        } catch (e) {
            console.error(e);
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Kontrolleri yenile'; }
        }
    }

    if (btn) btn.addEventListener('click', runChecks);
})();
</script>
@endsection
