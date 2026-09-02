@extends('layouts.admin')

@section('title', 'Premium Yönetimi — Gönül Köprüsü')

@section('content')
@if(session('success'))
<div style="background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-weight: 600;">
    {{ session('success') }}
</div>
@endif

<!-- Stats Grid -->
<div class="grid-stats">
    <div class="stat-card border-amber">
        <div>
            <div class="stat-label">Aktif Premium Üye</div>
            <div class="stat-number">{{ number_format($activeCount ?? 0) }}</div>
        </div>
        <div class="stat-icon">💎</div>
    </div>

    <div class="stat-card border-green">
        <div>
            <div class="stat-label">Toplam Gelir</div>
            <div class="stat-number">₺{{ number_format($totalRevenue ?? 0, 2) }}</div>
        </div>
        <div class="stat-icon">💰</div>
    </div>
</div>

<!-- Assign Premium Form -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Kullanıcıya Manuel Premium Tanımla</h2>
    </div>
    <form action="{{ route('admin.premium.store') }}" method="POST">
        @csrf
        <div class="filter-grid" style="grid-template-columns: 2fr 1fr 1fr auto;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Kullanıcı ID</label>
                <input type="number" name="user_id" class="form-control" placeholder="Örn: 102" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Paket Tipi</label>
                <select name="package_type" class="form-select" required>
                    <option value="pro">Pro (7 gün / ₺250)</option>
                    <option value="gold">Gold (14 gün / ₺400)</option>
                    <option value="platinum">Platinum (30 gün / ₺500)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Süre (Gün)</label>
                <input type="number" name="duration_days" class="form-control" value="30" min="1" max="365">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">💎 Tanımla</button>
            </div>
        </div>
    </form>
</div>

<!-- Subscriptions Table -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Abonelik Listesi</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kullanıcı</th>
                    <th>Paket</th>
                    <th>Fiyat</th>
                    <th>Süre</th>
                    <th>Başlangıç</th>
                    <th>Bitiş</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.show', $sub->user_id) }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                {{ $sub->user->username ?? 'Kullanıcı #' . $sub->user_id }}
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-warning">💎 {{ ucfirst($sub->package_type) }}</span>
                        </td>
                        <td>₺{{ number_format((float) $sub->price_tl, 2) }}</td>
                        <td>{{ $sub->duration_days }} Gün</td>
                        <td>{{ $sub->starts_at ? $sub->starts_at->format('d.m.Y') : '-' }}</td>
                        <td>{{ $sub->expires_at ? $sub->expires_at->format('d.m.Y') : '-' }}</td>
                        <td>
                            @if($sub->is_active && $sub->expires_at && $sub->expires_at->isFuture())
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-secondary">Süresi Doldu</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px;">
                            Henüz abonelik kaydı bulunmuyor.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $subscriptions->links() }}
</div>
@endsection
