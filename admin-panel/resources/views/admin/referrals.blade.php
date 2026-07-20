@extends('layouts.admin')

@section('title', 'Davet / Referans')
@section('lead', 'Referans kodları, davet istatistikleri ve reklam UTM kaynakları.')

@section('content')
@if(!$tableReady)
<div class="admin-flash admin-flash--warn">
    Referans tabloları henüz kurulmadı. Canlıda <code>setup_growth.php</code> migration çalıştırın.
</div>
@endif

<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <span class="admin-stat-label">Toplam davet</span>
        <strong class="admin-stat-value">{{ number_format($stats['total_referrals']) }}</strong>
    </div>
    <div class="admin-stat-card">
        <span class="admin-stat-label">Referans kodlu üye</span>
        <strong class="admin-stat-value">{{ number_format($stats['users_with_code']) }}</strong>
    </div>
    <div class="admin-stat-card">
        <span class="admin-stat-label">Davetle gelen üye</span>
        <strong class="admin-stat-value">{{ number_format($stats['referred_users']) }}</strong>
    </div>
</div>

<div class="admin-email-grid">
    <div class="admin-panel admin-panel--glass">
        <h3 class="admin-panel-title admin-panel-title--accent">En çok davet edenler</h3>
        @if($topReferrers->isEmpty())
            <p class="admin-empty">Henüz davet kaydı yok.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr><th>Kullanıcı</th><th>Davet</th></tr>
                </thead>
                <tbody>
                    @foreach($topReferrers as $row)
                        <tr>
                            <td>{{ $row->referrer?->username ?? '—' }} <small>{{ $row->referrer?->email }}</small></td>
                            <td>{{ $row->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="admin-panel admin-panel--glass">
        <h3 class="admin-panel-title admin-panel-title--accent">UTM kaynakları</h3>
        @if($utmBreakdown->isEmpty())
            <p class="admin-empty">UTM verisi henüz yok.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr><th>Kaynak</th><th>Ortam</th><th>Kampanya</th><th>Üye</th></tr>
                </thead>
                <tbody>
                    @foreach($utmBreakdown as $row)
                        <tr>
                            <td>{{ $row->utm_source }}</td>
                            <td>{{ $row->utm_medium ?: '—' }}</td>
                            <td>{{ $row->utm_campaign ?: '—' }}</td>
                            <td>{{ $row->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="admin-panel admin-panel--glass" style="margin-top:1.5rem;">
    <h3 class="admin-panel-title admin-panel-title--accent">Kayıt kaynağı</h3>
    @if($registrationSources->isEmpty())
        <p class="admin-empty">Kayıt kaynağı verisi yok.</p>
    @else
        <ul class="admin-template-list">
            @foreach($registrationSources as $src)
                <li><strong>{{ $src->registration_source }}</strong> — {{ $src->total }} üye</li>
            @endforeach
        </ul>
    @endif
</div>

<div class="admin-panel admin-panel--glass" style="margin-top:1.5rem;">
    <h3 class="admin-panel-title admin-panel-title--accent">Son davetler</h3>
    @if($recentReferrals->isEmpty())
        <p class="admin-empty">Son davet yok.</p>
    @else
        <table class="admin-table">
            <thead>
                <tr><th>Davet eden</th><th>Yeni üye</th><th>Tarih</th></tr>
            </thead>
            <tbody>
                @foreach($recentReferrals as $ref)
                    <tr>
                        <td>{{ $ref->referrer?->username ?? '—' }}</td>
                        <td>{{ $ref->referred?->username ?? '—' }}</td>
                        <td>{{ $ref->created_at?->format('d.m.Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
