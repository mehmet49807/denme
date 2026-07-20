@extends('layouts.admin')

@section('title', 'Denetim Kuyruğu')
@section('lead', 'Bekleyen şikayetler, AI bayrakları, profil onayları ve galeri örnekleri.')

@section('content')
<div class="admin-stat-grid admin-stat-grid--compact">
    <div class="admin-stat-card admin-stat-card--gold">
        <div class="admin-stat-value">{{ $counts['reports'] }}</div>
        <div class="admin-stat-label">Bekleyen Şikayet</div>
    </div>
    <div class="admin-stat-card admin-stat-card--violet">
        <div class="admin-stat-value">{{ $counts['ai_flags'] }}</div>
        <div class="admin-stat-label">AI Bayrak</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value">{{ $counts['profiles'] }}</div>
        <div class="admin-stat-label">Profil Onay</div>
    </div>
    <div class="admin-stat-card admin-stat-card--coral">
        <div class="admin-stat-value">{{ $counts['support'] }}</div>
        <div class="admin-stat-label">Açık Destek</div>
    </div>
</div>

<div class="admin-ops-grid">
    <section class="admin-panel admin-panel--glass">
        <div class="admin-panel-head">
            <h3 class="admin-panel-title">Şikayetler</h3>
            <a href="{{ route('admin.reports') }}" class="btn btn-outline btn-sm">Tümü</a>
        </div>
        @forelse($pendingReports as $report)
            <div class="admin-ops-row">
                <div>
                    <strong>{{ $report->reported->username ?? '—' }}</strong>
                    <span class="admin-ops-meta">{{ \Illuminate\Support\Str::limit($report->reason, 80) }}</span>
                </div>
                <span class="admin-ops-meta">{{ optional($report->created_at)->format('d.m H:i') }}</span>
            </div>
        @empty
            <p class="admin-ops-empty">Bekleyen şikayet yok.</p>
        @endforelse
    </section>

    <section class="admin-panel admin-panel--glass">
        <div class="admin-panel-head">
            <h3 class="admin-panel-title">AI Bayrakları</h3>
            <a href="{{ route('admin.ai') }}" class="btn btn-outline btn-sm">AI Denetim</a>
        </div>
        @forelse($aiFlags as $flag)
            <div class="admin-ops-row">
                <div>
                    <strong>{{ $flag->user->username ?? '—' }}</strong>
                    <span class="admin-ops-meta">{{ $flag->categoryLabel() }} · {{ $flag->contentTypeLabel() }}</span>
                </div>
                <span class="admin-badge admin-badge--{{ $flag->severity === 'high' ? 'danger' : 'warn' }}">{{ $flag->severity }}</span>
            </div>
        @empty
            <p class="admin-ops-empty">Bekleyen AI bayrağı yok.</p>
        @endforelse
    </section>

    <section class="admin-panel admin-panel--glass">
        <div class="admin-panel-head">
            <h3 class="admin-panel-title">Profil Onay</h3>
            <a href="{{ route('admin.profile-approvals') }}" class="btn btn-outline btn-sm">Onay ekranı</a>
        </div>
        @forelse($pendingProfiles as $user)
            <div class="admin-ops-row">
                <div class="admin-ops-user">
                    @if($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="" width="36" height="36" loading="lazy">
                    @endif
                    <div>
                        <strong>{{ $user->username }}</strong>
                        <span class="admin-ops-meta">{{ $user->city }}</span>
                    </div>
                </div>
            </div>
        @empty
            <p class="admin-ops-empty">Bekleyen profil yok.</p>
        @endforelse
    </section>

    <section class="admin-panel admin-panel--glass">
        <div class="admin-panel-head">
            <h3 class="admin-panel-title">Galeri örnekleri</h3>
            <a href="{{ route('admin.gallery') }}" class="btn btn-outline btn-sm">Galeri moderasyon</a>
        </div>
        <div class="admin-ops-gallery">
            @forelse($gallerySamples as $item)
                <a href="{{ route('admin.gallery', ['search' => $item['user']->username]) }}" class="admin-ops-gallery-item" title="{{ $item['user']->username }}">
                    <img src="{{ $item['url'] }}" alt="{{ $item['user']->username }}" loading="lazy">
                </a>
            @empty
                <p class="admin-ops-empty">Galeri örneği yok.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
