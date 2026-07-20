@extends('layouts.admin')

@section('title', 'Dashboard')
@section('lead', 'Platform özeti ve canlı istatistikler.')

@section('header-actions')
    <span class="admin-live-indicator">Canlı veri · <span id="adminDashboardUpdatedAt">—</span></span>
@endsection

@section('content')
<div class="admin-stat-grid">
    <div class="admin-stat-card admin-stat-card--violet">
        <div class="admin-stat-value" id="statTotalUsers">{{ $stats['total_users'] }}</div>
        <div class="admin-stat-label">Toplam Kullanıcı</div>
    </div>
    <div class="admin-stat-card admin-stat-card--blue">
        <div class="admin-stat-value" id="statTotalReferrals">{{ $stats['total_referrals'] ?? 0 }}</div>
        <div class="admin-stat-label">Toplam Davet</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value" id="statReferredUsers">{{ $stats['referred_users'] ?? 0 }}</div>
        <div class="admin-stat-label">Davetle Gelen</div>
    </div>
    <div class="admin-stat-card admin-stat-card--indigo">
        <div class="admin-stat-value" id="statActiveMale">{{ $stats['active_male'] }}</div>
        <div class="admin-stat-label">Aktif Erkek</div>
    </div>
    <div class="admin-stat-card admin-stat-card--coral">
        <div class="admin-stat-value" id="statActiveFemale">{{ $stats['active_female'] }}</div>
        <div class="admin-stat-label">Aktif Kadın</div>
    </div>
    <div class="admin-stat-card admin-stat-card--gold">
        <div class="admin-stat-value" id="statPendingReports">{{ $stats['pending_reports'] }}</div>
        <div class="admin-stat-label">Bekleyen Şikayet</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value" id="statActivePremium">{{ $stats['active_premium'] }}</div>
        <div class="admin-stat-label">Aktif Premium</div>
    </div>
    <div class="admin-stat-card admin-stat-card--gold">
        <div class="admin-stat-value" id="statRevenue">{{ number_format($stats['revenue_tl'], 0) }} <small>TL</small></div>
        <div class="admin-stat-label">Toplam Gelir</div>
    </div>
    <div class="admin-stat-card admin-stat-card--indigo">
        <div class="admin-stat-value" id="statSignupsToday">{{ $stats['signups_today'] ?? 0 }}</div>
        <div class="admin-stat-label">Bugün Kayıt</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value" id="statPendingProfiles">{{ $stats['pending_profiles'] ?? 0 }}</div>
        <div class="admin-stat-label">Bekleyen Profil</div>
    </div>
    <div class="admin-stat-card admin-stat-card--violet">
        <div class="admin-stat-value" id="statAiFlags">{{ $stats['ai_flags'] ?? 0 }}</div>
        <div class="admin-stat-label">AI Bayrak</div>
    </div>
    <div class="admin-stat-card admin-stat-card--coral">
        <div class="admin-stat-value" id="statOpenSupport">{{ $stats['open_support'] ?? 0 }}</div>
        <div class="admin-stat-label">Açık Destek</div>
    </div>
</div>

<div class="admin-panel admin-panel--glass admin-online-card">
    <div class="admin-online-card__top">
        <div class="admin-online-card__intro">
            <h3 class="admin-panel-title admin-online-card__title">
                <span class="admin-online-pulse" aria-hidden="true"></span>
                Çevrimiçi Kullanıcılar
            </h3>
            <p class="admin-online-card__hint">Son {{ $online['threshold_minutes'] }} dk içinde aktif olanlar çevrimiçi sayılır</p>
        </div>
        <div class="admin-online-hero">
            <div class="admin-online-hero__value" id="onlineNowCount">{{ $online['now'] }}</div>
            <div class="admin-online-hero__label">Şu an çevrimiçi</div>
            <div class="admin-online-hero__split">
                <span><span class="admin-online-dot admin-online-dot--male"></span> Erkek: <strong id="onlineNowMale">{{ $online['now_male'] }}</strong></span>
                <span><span class="admin-online-dot admin-online-dot--female"></span> Kadın: <strong id="onlineNowFemale">{{ $online['now_female'] }}</strong></span>
            </div>
        </div>
    </div>

    <div class="admin-online-periods">
        <div class="admin-online-period">
            <span class="admin-online-period__value" id="onlineToday">{{ $online['periods']['today'] }}</span>
            <span class="admin-online-period__label">Bugün</span>
        </div>
        <div class="admin-online-period">
            <span class="admin-online-period__value" id="onlineYesterday">{{ $online['periods']['yesterday'] }}</span>
            <span class="admin-online-period__label">Dün</span>
        </div>
        <div class="admin-online-period">
            <span class="admin-online-period__value" id="onlineThisWeek">{{ $online['periods']['this_week'] }}</span>
            <span class="admin-online-period__label">Bu Hafta</span>
        </div>
        <div class="admin-online-period">
            <span class="admin-online-period__value" id="onlineLastWeek">{{ $online['periods']['last_week'] }}</span>
            <span class="admin-online-period__label">Geçen Hafta</span>
        </div>
        <div class="admin-online-period">
            <span class="admin-online-period__value" id="onlineThisMonth">{{ $online['periods']['this_month'] }}</span>
            <span class="admin-online-period__label">Bu Ay</span>
        </div>
        <div class="admin-online-period">
            <span class="admin-online-period__value" id="onlineLastMonth">{{ $online['periods']['last_month'] }}</span>
            <span class="admin-online-period__label">Geçen Ay</span>
        </div>
    </div>

    <div class="admin-online-chart-wrap">
        <h4 class="admin-online-chart-title">Günlük Aktif Kullanıcı (Son 14 Gün)</h4>
        <canvas id="chartOnlineDaily"></canvas>
    </div>
</div>

<div class="admin-charts-grid">
    <div class="admin-panel admin-panel--glass admin-chart-card admin-chart-card--wide">
        <h3 class="admin-panel-title">Yeni Üye Kayıtları</h3>
        <canvas id="chartUserSignups"></canvas>
    </div>
    <div class="admin-panel admin-panel--glass admin-chart-card admin-chart-card--narrow">
        <h3 class="admin-panel-title">Cinsiyet Dağılımı</h3>
        <canvas id="chartGender"></canvas>
        <div class="admin-chart-legend">
            <span>Erkek: <strong id="legendMale">{{ $chartData['gender']['male'] }}</strong></span>
            <span>Kadın: <strong id="legendFemale">{{ $chartData['gender']['female'] }}</strong></span>
        </div>
    </div>
    <div class="admin-panel admin-panel--glass admin-chart-card admin-chart-card--wide">
        <h3 class="admin-panel-title">Günlük Mesajlar</h3>
        <canvas id="chartMessages"></canvas>
    </div>
    <div class="admin-panel admin-panel--glass admin-chart-card admin-chart-card--narrow">
        <h3 class="admin-panel-title">Aktif Kullanıcılar</h3>
        <canvas id="chartGenderBar"></canvas>
    </div>
    <div class="admin-panel admin-panel--glass admin-chart-card admin-chart-card--wide">
        <h3 class="admin-panel-title">Premium Satışları</h3>
        <canvas id="chartPremium"></canvas>
    </div>
</div>

<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Hızlı İşlemler</h3>
    <div class="admin-quick-actions">
        <a href="{{ route('admin.moderation') }}" class="btn btn-primary">Denetim Kuyruğu</a>
        <a href="{{ route('admin.messages') }}" class="btn btn-outline">Mesaj Denetimi</a>
        <a href="{{ route('admin.reports') }}" class="btn btn-outline">Şikayetler</a>
        <a href="{{ route('admin.broadcasts') }}" class="btn btn-outline">Duyuru Gönder</a>
        <a href="{{ route('admin.system-health') }}" class="btn btn-outline">Sistem Sağlığı</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
window.adminDashboardConfig = {
    statsUrl: @json(route('admin.dashboard.stats')),
    initial: @json(['stats' => $stats, 'charts' => $chartData, 'online' => $online]),
};
</script>
<script src="{{ rtrim(config('app.asset_url') ?: config('app.url'), '/') }}/js/admin-dashboard.js"></script>
@endsection
