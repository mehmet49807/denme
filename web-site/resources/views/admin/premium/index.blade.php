@extends('layouts.admin')

@section('title', 'Premium Yönetimi — Gönül Köprüsü')

@section('content')
<!-- Stats Grid -->
<div class="grid-stats">
    <div class="stat-card border-amber">
        <div>
            <div class="stat-label">Aktif Premium Üye Sayısı</div>
            <div class="stat-number">{{ number_format($activeCount ?? $stats['active_count'] ?? 0) }}</div>
        </div>
        <div class="stat-icon">💎</div>
    </div>

    <div class="stat-card border-green">
        <div>
            <div class="stat-label">Toplam Gelir</div>
            <div class="stat-number">₺{{ number_format($totalRevenue ?? $stats['total_revenue'] ?? 0, 2) }}</div>
        </div>
        <div class="stat-icon">💰</div>
    </div>
</div>

<!-- Assign Premium Form Card -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Kullanıcıya Manuel Premium Tanımla</h2>
    </div>
    <form action="{{ route('admin.premium.store') }}" method="POST">
        @csrf
        <div class="filter-grid" style="grid-template-columns: 2fr 1fr 1fr auto;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Kullanıcı ID veya E-posta</label>
                <input type="text" name="user_id" class="form-control" placeholder="Örn: 102 veya ahmet@example.com" required>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Paket Tipi</label>
                <select name="package_type" class="form-select" required>
                    <option value="pro">Pro Paket</option>
                    <option value="gold">Gold Paket</option>
                    <option value="platinum">Platinum Paket</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Süre (Gün)</label>
                <input type="number" name="duration_days" class="form-control" value="30" min="1" max="365" required>
            </div>

            <div>
                <button type="submit" class="btn btn-primary">💎 Premium Tanımla</button>
            </div>
        </div>
    </form>
</div>

<!-- Subscriptions Table -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Abonelik Geçmişi ve Listesi</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kullanıcı</th>
                    <th>Paket</th>
                    <th>Fiyat</th>
                    <th>Süre (Gün)</th>
                    <th>Başlangıç Tarihi</th>
                    <th>Bitiş Tarihi</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions ?? $premiumUsers ?? [] as $sub)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.show', $sub->user_id ?? 0) }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                {{ $sub->user->username ?? $sub->user_name ?? 'Kullanıcı #' . ($sub->user_id ?? '') }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-warning">💎 {{ ucfirst($sub->package_type ?? 'Pro') }}</span>
                        </td>
                        <td>₺{{ number_format($sub->price ?? 0, 2) }}</td>
                        <td>{{ $sub->duration_days ?? '-' }} Gün</td>
                        <td>{{ isset($sub->starts_at) ? \Carbon\Carbon::parse($sub->starts_at)->format('d.m.Y') : '-' }}</td>
                        <td>{{ isset($sub->expires_at) ? \Carbon\Carbon::parse($sub->expires_at)->format('d.m.Y') : '-' }}</td>
                        <td>
                            @if($sub->is_active ?? (isset($sub->expires_at) && \Carbon\Carbon::parse($sub->expires_at)->isFuture()))
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Süresi Doldu</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-body); padding: 24px;">
                            Henüz abonelik kaydı bulunmuyor.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($subscriptions ?? null, 'links'))
        <div class="pagination-wrapper">
            {{ $subscriptions->links() }}
        </div>
    @endif
</div>
@endsection
