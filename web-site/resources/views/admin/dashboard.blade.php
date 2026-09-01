@extends('layouts.admin')

@section('title', 'Panel — Gönül Köprüsü')

@section('content')
<div class="dashboard-wrapper">
    <!-- Stat Cards Grid -->
    <div class="grid-stats">
        <div class="stat-card border-purple">
            <div>
                <div class="stat-label">Toplam Kullanıcı</div>
                <div class="stat-number">{{ number_format($totalUsers ?? $stats['total_users'] ?? 0) }}</div>
            </div>
            <div class="stat-icon">👥</div>
        </div>

        <div class="stat-card border-blue">
            <div>
                <div class="stat-label">Erkek Kullanıcı</div>
                <div class="stat-number">{{ number_format($maleCount ?? $stats['male_users'] ?? 0) }}</div>
            </div>
            <div class="stat-icon">👨</div>
        </div>

        <div class="stat-card border-pink">
            <div>
                <div class="stat-label">Kadın Kullanıcı</div>
                <div class="stat-number">{{ number_format($femaleCount ?? $stats['female_users'] ?? 0) }}</div>
            </div>
            <div class="stat-icon">👩</div>
        </div>

        <div class="stat-card border-amber">
            <div>
                <div class="stat-label">Premium Üye</div>
                <div class="stat-number">{{ number_format($premiumCount ?? $stats['premium_users'] ?? 0) }}</div>
            </div>
            <div class="stat-icon">💎</div>
        </div>

        <div class="stat-card border-red">
            <div>
                <div class="stat-label">Bekleyen Şikayetler</div>
                <div class="stat-number">{{ number_format($pendingReportsCount ?? $stats['pending_reports'] ?? 0) }}</div>
            </div>
            <div class="stat-icon">⚠️</div>
        </div>

        <div class="stat-card border-indigo">
            <div>
                <div class="stat-label">Açık Destek Talepleri</div>
                <div class="stat-number">{{ number_format($openTicketsCount ?? $stats['open_tickets'] ?? 0) }}</div>
            </div>
            <div class="stat-icon">🎧</div>
        </div>

        <div class="stat-card border-teal">
            <div>
                <div class="stat-label">Bekleyen Onaylar</div>
                <div class="stat-number">{{ number_format($pendingVerificationsCount ?? $stats['pending_verifications'] ?? 0) }}</div>
            </div>
            <div class="stat-icon">🔍</div>
        </div>

        <div class="stat-card border-green">
            <div>
                <div class="stat-label">Aktif Hikayeler</div>
                <div class="stat-number">{{ number_format($activeStoriesCount ?? $stats['active_stories'] ?? 0) }}</div>
            </div>
            <div class="stat-icon">📸</div>
        </div>
    </div>

    <!-- Two Columns: Recent Registrations & Recent Reports -->
    <div class="grid-2col">
        <!-- Recent Registrations -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Son Kayıt Olanlar</h2>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">Tümünü Gör</a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kullanıcı Adı</th>
                            <th>E-posta</th>
                            <th>Cinsiyet</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers ?? $users ?? [] as $userItem)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $userItem->id) }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                        {{ $userItem->username ?? $userItem->name ?? 'Kullanıcı' }}
                                    </a>
                                </td>
                                <td>{{ $userItem->email ?? '-' }}</td>
                                <td>
                                    @if(($userItem->gender ?? '') === 'male' || ($userItem->gender ?? '') === 'erkek')
                                        <span class="badge badge-info">Erkek</span>
                                    @elseif(($userItem->gender ?? '') === 'female' || ($userItem->gender ?? '') === 'kadin')
                                        <span class="badge badge-warning">Kadın</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $userItem->gender ?? '-' }}</span>
                                    @endif
                                </td>
                                <td>{{ isset($userItem->created_at) ? \Carbon\Carbon::parse($userItem->created_at)->format('d.m.Y H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-body); padding: 20px;">
                                    Henüz yeni kayıt bulunmuyor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Son Şikayetler</h2>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary btn-sm">Tümünü Gör</a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Şikayet Eden</th>
                            <th>Şikayet Edilen</th>
                            <th>Neden</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentReports ?? $reports ?? [] as $reportItem)
                            <tr>
                                <td>{{ $reportItem->reporter->username ?? $reportItem->reporter_name ?? 'Kullanıcı #' . ($reportItem->reporter_id ?? '') }}</td>
                                <td>{{ $reportItem->reportedUser->username ?? $reportItem->reported_name ?? 'Kullanıcı #' . ($reportItem->reported_id ?? '') }}</td>
                                <td>{{ Str::limit($reportItem->reason ?? $reportItem->subject ?? '-', 25) }}</td>
                                <td>
                                    @if(($reportItem->status ?? 'pending') === 'pending')
                                        <span class="badge badge-warning">Bekliyor</span>
                                    @elseif(($reportItem->status ?? '') === 'resolved' || ($reportItem->status ?? '') === 'approved')
                                        <span class="badge badge-success">Çözüldü</span>
                                    @else
                                        <span class="badge badge-danger">Reddedildi</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-body); padding: 20px;">
                                    Henüz yeni şikayet bulunmuyor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
