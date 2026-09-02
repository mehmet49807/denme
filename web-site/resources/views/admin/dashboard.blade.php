@extends('layouts.admin')

@section('title', 'Panel — Gönül Köprüsü')

@section('content')
<div class="dashboard-wrapper">
    <!-- Stat Cards Grid -->
    <div class="grid-stats">
        <div class="stat-card border-purple">
            <div>
                <div class="stat-label">Toplam Kullanıcı</div>
                <div class="stat-number">{{ number_format($totalUsers ?? 0) }}</div>
            </div>
            <div class="stat-icon">👥</div>
        </div>

        <div class="stat-card border-blue">
            <div>
                <div class="stat-label">Erkek Kullanıcı</div>
                <div class="stat-number">{{ number_format($maleCount ?? 0) }}</div>
            </div>
            <div class="stat-icon">👨</div>
        </div>

        <div class="stat-card border-pink">
            <div>
                <div class="stat-label">Kadın Kullanıcı</div>
                <div class="stat-number">{{ number_format($femaleCount ?? 0) }}</div>
            </div>
            <div class="stat-icon">👩</div>
        </div>

        <div class="stat-card border-amber">
            <div>
                <div class="stat-label">Premium Üye</div>
                <div class="stat-number">{{ number_format($premiumActive ?? 0) }}</div>
            </div>
            <div class="stat-icon">💎</div>
        </div>

        <div class="stat-card border-red">
            <div>
                <div class="stat-label">Bekleyen Şikayetler</div>
                <div class="stat-number">{{ number_format($pendingReports ?? 0) }}</div>
            </div>
            <div class="stat-icon">⚠️</div>
        </div>

        <div class="stat-card border-indigo">
            <div>
                <div class="stat-label">Açık Destek Talepleri</div>
                <div class="stat-number">{{ number_format($openTickets ?? 0) }}</div>
            </div>
            <div class="stat-icon">🎧</div>
        </div>

        <div class="stat-card border-teal">
            <div>
                <div class="stat-label">Mavi Tik Bekleyenler</div>
                <div class="stat-number">{{ number_format($pendingVerify ?? 0) }}</div>
            </div>
            <div class="stat-icon">🔍</div>
        </div>

        <div class="stat-card border-green">
            <div>
                <div class="stat-label">Bugün Yeni Üye</div>
                <div class="stat-number">{{ number_format($newToday ?? 0) }}</div>
            </div>
            <div class="stat-icon">✨</div>
        </div>
    </div>

    <!-- Two Columns -->
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
                            <th>Kullanıcı</th>
                            <th>E-posta</th>
                            <th>Cinsiyet</th>
                            <th>Onay</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers as $userItem)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $userItem->id) }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                        {{ $userItem->username ?? 'Kullanıcı' }}
                                    </a>
                                </td>
                                <td>{{ $userItem->email ?? '-' }}</td>
                                <td>
                                    @if($userItem->gender === 'male')
                                        <span class="badge badge-info">Erkek</span>
                                    @elseif($userItem->gender === 'female')
                                        <span class="badge badge-warning">Kadın</span>
                                    @else
                                        <span class="badge badge-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($userItem->is_verified)
                                        <span style="color: #1D9BF0; font-weight: bold;" title="Mavi Tik">✔️</span>
                                    @else
                                        <span style="color: #D1D5DB;" title="Onaysız">✖</span>
                                    @endif
                                </td>
                                <td>{{ $userItem->created_at ? $userItem->created_at->format('d.m.Y H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px;">
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
                            <th>Sebep</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentReports as $reportItem)
                            <tr>
                                <td>{{ $reportItem->reporter->username ?? 'Silinmiş' }}</td>
                                <td>
                                    @if($reportItem->reported)
                                        <a href="{{ route('admin.users.show', $reportItem->reported_id) }}" style="color: #EF4444; text-decoration: none; font-weight: 500;">
                                            {{ $reportItem->reported->username }}
                                        </a>
                                    @else
                                        Silinmiş
                                    @endif
                                </td>
                                <td>{{ Str::limit($reportItem->reason ?? '-', 25) }}</td>
                                <td>
                                    @if($reportItem->status === 'pending')
                                        <span class="badge badge-warning">Beklemede</span>
                                    @elseif($reportItem->status === 'resolved')
                                        <span class="badge badge-success">Çözüldü</span>
                                    @elseif($reportItem->status === 'dismissed')
                                        <span class="badge badge-secondary">Reddedildi</span>
                                    @else
                                        <span class="badge badge-info">{{ $reportItem->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px;">
                                    Şikayet bulunmuyor.
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
