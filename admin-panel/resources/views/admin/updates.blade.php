@extends('layouts.admin')

@section('title', 'Güncelleme')
@section('lead', 'Laravel sürümü, PHP uyumluluğu ve tek tıkla güncelleme.')

@section('header-actions')
    <form action="{{ route('admin.updates.refresh') }}" method="POST" class="admin-inline-form">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm">Yenile</button>
    </form>
    <a href="{{ route('admin.github') }}" class="btn btn-outline btn-sm">GitHub Deploy</a>
    <a href="{{ route('admin.system-health') }}" class="btn btn-outline btn-sm">Sistem Sağlığı</a>
@endsection

@section('content')
@php
    $local = $local ?? [];
    $web = $web ?? [];
    $adminRemote = $adminRemote ?? [];
    $packagist = $packagist ?? [];
    $history = $history ?? [];
    $current = $local['current'] ?? app()->version();
    $recommended = $packagist['recommended'] ?? null;
    $updateAvailable = !empty($local['update_available']);
@endphp

@if(session('success'))
    <div class="admin-ai-flash admin-ai-flash--ok">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="admin-ai-flash admin-ai-flash--bad">{{ session('error') }}</div>
@endif

<div class="admin-stat-grid admin-stat-grid--compact">
    <div class="admin-stat-card {{ $updateAvailable ? 'admin-stat-card--gold' : 'admin-stat-card--emerald' }}">
        <div class="admin-stat-value" style="font-size:1.15rem">{{ $current }}</div>
        <div class="admin-stat-label">Bu panel (Laravel)</div>
    </div>
    <div class="admin-stat-card admin-stat-card--indigo">
        <div class="admin-stat-value" style="font-size:1.15rem">{{ $local['php'] ?? PHP_VERSION }}</div>
        <div class="admin-stat-label">PHP</div>
    </div>
    <div class="admin-stat-card admin-stat-card--violet">
        <div class="admin-stat-value" style="font-size:1.15rem">{{ $recommended ?? '—' }}</div>
        <div class="admin-stat-label">Önerilen</div>
    </div>
    <div class="admin-stat-card admin-stat-card--{{ !empty($local['composer_available']) ? 'emerald' : 'coral' }}">
        <div class="admin-stat-value" style="font-size:1.15rem">{{ !empty($local['composer_available']) ? 'Hazır' : 'Yok' }}</div>
        <div class="admin-stat-label">Composer</div>
    </div>
</div>

<div class="admin-panel admin-panel--glass" style="margin-top:1.25rem">
    <h3 class="admin-panel-title">Uygulama sürümleri</h3>
    <p class="admin-ops-meta">Web ve admin ayrı Laravel kurulumlarıdır; ikisini de güncelleyin.</p>
    <div class="admin-health-grid" style="margin-top:1rem">
        <div class="admin-health-card {{ ($web['ok'] ?? false) ? 'is-ok' : 'is-bad' }}">
            <strong>Web</strong>
            <span>
                @if($web['ok'] ?? false)
                    Laravel {{ $web['current'] ?? '—' }}
                    · PHP {{ $web['php'] ?? '—' }}
                    · {{ $web['constraint'] ?? '—' }}
                @else
                    {{ $web['error'] ?? 'Durum alınamadı' }}
                @endif
            </span>
            <em>{{ ($web['ok'] ?? false) ? 'Bağlı' : 'Kontrol' }}</em>
        </div>
        <div class="admin-health-card {{ ($adminRemote['ok'] ?? false) ? 'is-ok' : 'is-bad' }}">
            <strong>Admin</strong>
            <span>
                @if($adminRemote['ok'] ?? false)
                    Laravel {{ $adminRemote['current'] ?? $current }}
                    · PHP {{ $adminRemote['php'] ?? ($local['php'] ?? '—') }}
                    · {{ $adminRemote['constraint'] ?? ($local['constraint'] ?? '—') }}
                @else
                    Yerel: Laravel {{ $current }}
                @endif
            </span>
            <em>Canlı</em>
        </div>
        <div class="admin-health-card is-ok">
            <strong>Packagist</strong>
            <span>
                11.x {{ $packagist['latest11'] ?? '—' }}
                · 12.x {{ $packagist['latest12'] ?? '—' }}
                · 13.x {{ $packagist['latest13'] ?? '—' }}
                @if(!empty($packagist['error']))
                    · {{ $packagist['error'] }}
                @endif
            </span>
            <em>Katalog</em>
        </div>
    </div>
</div>

<div class="admin-panel admin-panel--glass" style="margin-top:1.25rem">
    <h3 class="admin-panel-title">Laravel güncelle</h3>
    <p class="admin-ops-meta">
        Hedef: <strong>{{ $local['target_constraint'] ?? '^12.0' }}</strong>
        (PHP 8.2 ile Laravel 12 desteklenir; Laravel 13 için PHP 8.3+ gerekir).
        İşlem 1–5 dakika sürebilir — sayfayı kapatmayın.
    </p>

    <form method="POST" action="{{ route('admin.updates.run') }}" class="admin-inline-form" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:end;margin-top:1rem" onsubmit="return confirm('Laravel güncellemesi başlatılsın mı?');">
        @csrf
        <label style="display:flex;flex-direction:column;gap:.35rem;font-size:.85rem">
            Hedef
            <select name="target" class="admin-input" style="min-width:10rem">
                <option value="both" selected>Web + Admin</option>
                <option value="admin">Yalnız Admin</option>
                <option value="web">Yalnız Web</option>
            </select>
        </label>
        <label style="display:flex;flex-direction:column;gap:.35rem;font-size:.85rem">
            Mod
            <select name="mode" class="admin-input" style="min-width:12rem">
                <option value="target" selected>Laravel 12’ye yükselt</option>
                <option value="patch">Aynı majörde yama (11.x)</option>
            </select>
        </label>
        <button type="submit" class="btn btn-primary">Güncellemeyi başlat</button>
    </form>
</div>

@if(!empty($history))
<div class="admin-panel admin-panel--glass" style="margin-top:1.25rem">
    <h3 class="admin-panel-title">Geçmiş</h3>
    <div class="admin-table-wrap" style="margin-top:.75rem">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Zaman</th>
                    <th>Sonuç</th>
                    <th>Önce → Sonra</th>
                    <th>Hedef</th>
                </tr>
            </thead>
            <tbody>
                @foreach($history as $row)
                    <tr>
                        <td>{{ $row['at'] ?? '—' }}</td>
                        <td>
                            <span class="{{ !empty($row['ok']) ? 'admin-badge admin-badge--success' : 'admin-badge admin-badge--danger' }}">
                                {{ !empty($row['ok']) ? 'OK' : 'Hata' }}
                            </span>
                            <div class="admin-ops-meta">{{ $row['message'] ?? '' }}</div>
                        </td>
                        <td>{{ ($row['before'] ?? '—').' → '.($row['after'] ?? '—') }}</td>
                        <td>{{ $row['target'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
