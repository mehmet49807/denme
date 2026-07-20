@extends('layouts.admin')

@section('title', 'Premium Takip')
@section('lead', 'Aktif abonelikler ve gelir özeti.')

@section('content')
<style>
    /* Premium Page — Royal Gold Theme */
    .premium-hero {
        position: relative;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 20px;
        background: linear-gradient(135deg, #1a0533 0%, #2d1b69 40%, #4c1d95 70%, #7c3aed 100%);
        overflow: hidden;
        border: 1px solid rgba(251, 191, 36, 0.2);
    }

    .premium-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.25) 0%, transparent 70%);
        pointer-events: none;
    }

    .premium-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(236, 72, 153, 0.2) 0%, transparent 70%);
        pointer-events: none;
    }

    .premium-hero-content {
        position: relative;
        z-index: 1;
    }

    .premium-hero-title {
        font-family: "Playfair Display", Georgia, serif;
        font-size: 1.75rem;
        font-weight: 700;
        color: #fff;
        margin: 0 0 0.25rem;
        background: linear-gradient(135deg, #fff 0%, #fbbf24 50%, #f59e0b 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .premium-hero-subtitle {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.88rem;
        margin: 0;
    }

    /* Stats Cards */
    .premium-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .premium-stat-card {
        position: relative;
        padding: 1.5rem;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .premium-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
    }

    .premium-stat-card--revenue {
        background: linear-gradient(145deg, #1a0533 0%, #2d1b69 100%);
        border-color: rgba(251, 191, 36, 0.3);
    }

    .premium-stat-card--active {
        background: linear-gradient(145deg, #0c1f3f 0%, #1e3a5f 100%);
        border-color: rgba(6, 182, 212, 0.3);
    }

    .premium-stat-card--pro {
        background: linear-gradient(145deg, #1a0533 0%, #4c1d95 100%);
        border-color: rgba(139, 92, 246, 0.3);
    }

    .premium-stat-card--gold {
        background: linear-gradient(145deg, #2d1b00 0%, #6b4000 100%);
        border-color: rgba(251, 191, 36, 0.4);
    }

    .premium-stat-card--platinum {
        background: linear-gradient(145deg, #0f172a 0%, #334155 100%);
        border-color: rgba(148, 163, 184, 0.4);
    }

    .premium-stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .premium-stat-icon--revenue {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
        box-shadow: 0 6px 16px rgba(251, 191, 36, 0.3);
    }

    .premium-stat-icon--active {
        background: linear-gradient(135deg, #06b6d4, #0891b2);
        box-shadow: 0 6px 16px rgba(6, 182, 212, 0.3);
    }

    .premium-stat-icon--pro {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        box-shadow: 0 6px 16px rgba(139, 92, 246, 0.3);
    }

    .premium-stat-icon--gold {
        background: linear-gradient(135deg, #fbbf24, #d97706);
        box-shadow: 0 6px 16px rgba(251, 191, 36, 0.3);
    }

    .premium-stat-icon--platinum {
        background: linear-gradient(135deg, #94a3b8, #64748b);
        box-shadow: 0 6px 16px rgba(148, 163, 184, 0.3);
    }

    .premium-stat-icon svg {
        width: 22px;
        height: 22px;
        color: #fff;
    }

    .premium-stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: #fff;
        line-height: 1.1;
        margin-bottom: 0.35rem;
    }

    .premium-stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.55);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Tier badges */
    .premium-tier-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.3rem 0.75rem;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .premium-tier-badge--pro {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(124, 58, 237, 0.15));
        color: #a78bfa;
        border: 1px solid rgba(139, 92, 246, 0.3);
    }

    .premium-tier-badge--gold {
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.2), rgba(217, 119, 6, 0.15));
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.3);
    }

    .premium-tier-badge--platinum {
        background: linear-gradient(135deg, rgba(148, 163, 184, 0.2), rgba(100, 116, 139, 0.15));
        color: #cbd5e1;
        border: 1px solid rgba(148, 163, 184, 0.3);
    }

    .premium-tier-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .premium-tier-dot--pro { background: #8b5cf6; }
    .premium-tier-dot--gold { background: #fbbf24; }
    .premium-tier-dot--platinum { background: #94a3b8; }

    /* Table Section */
    .premium-table-section {
        background: linear-gradient(145deg, rgba(30, 15, 60, 0.5), rgba(15, 25, 50, 0.4));
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 20px;
        overflow: hidden;
    }

    .premium-table-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .premium-table-title {
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .premium-table-title svg {
        width: 18px;
        height: 18px;
        color: #fbbf24;
    }

    .premium-table-count {
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.06);
        padding: 0.3rem 0.7rem;
        border-radius: 20px;
    }

    .premium-table-wrap {
        overflow-x: auto;
    }

    .premium-table {
        width: 100%;
        border-collapse: collapse;
    }

    .premium-table thead th {
        padding: 0.85rem 1.5rem;
        text-align: left;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: rgba(255, 255, 255, 0.45);
        background: rgba(0, 0, 0, 0.15);
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    }

    .premium-table tbody tr {
        transition: background 0.15s;
    }

    .premium-table tbody tr:hover {
        background: rgba(255, 255, 255, 0.03);
    }

    .premium-table tbody td {
        padding: 1rem 1.5rem;
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.8);
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    }

    .premium-table tbody tr:last-child td {
        border-bottom: none;
    }

    .premium-user-cell {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .premium-user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.7rem;
        background: linear-gradient(135deg, #7c3aed, #ec4899);
        color: #fff;
        flex-shrink: 0;
    }

    .premium-price {
        font-weight: 700;
        color: #fbbf24;
    }

    .premium-date {
        color: rgba(255, 255, 255, 0.5);
        font-size: 0.8rem;
    }

    .premium-empty {
        text-align: center;
        padding: 3rem 1rem;
        color: rgba(255, 255, 255, 0.4);
    }

    .premium-empty svg {
        width: 48px;
        height: 48px;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, 0.15);
    }

    /* Pagination override */
    .premium-pagination {
        padding: 1rem 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    /* Responsive */
    @media (max-width: 640px) {
        .premium-stats {
            grid-template-columns: 1fr 1fr;
        }
        .premium-hero {
            padding: 1.5rem;
        }
        .premium-hero-title {
            font-size: 1.3rem;
        }
        .premium-stat-value {
            font-size: 1.4rem;
        }
    }
</style>

<!-- Premium Hero Banner -->
<div class="premium-hero">
    <div class="premium-hero-content">
        <h2 class="premium-hero-title">Premium Abonelikler</h2>
        <p class="premium-hero-subtitle">Aktif abonelikleri takip edin, gelir analizini g&ouml;r&uuml;nt&uuml;leyin</p>
    </div>
</div>

<!-- Stats Grid -->
<div class="premium-stats">
    <div class="premium-stat-card premium-stat-card--revenue">
        <div class="premium-stat-icon premium-stat-icon--revenue">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div class="premium-stat-value">{{ number_format($totalRevenue, 0) }} <small style="font-size:0.55em;opacity:0.7;">TL</small></div>
        <div class="premium-stat-label">Toplam Gelir</div>
    </div>

    <div class="premium-stat-card premium-stat-card--active">
        <div class="premium-stat-icon premium-stat-icon--active">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="premium-stat-value">{{ $activeCount }}</div>
        <div class="premium-stat-label">Aktif Abonelik</div>
    </div>

    @foreach($tierDistribution as $tier => $count)
    @php
        $tierClass = strtolower($tier);
        if (!in_array($tierClass, ['pro', 'gold', 'platinum'])) $tierClass = 'pro';
    @endphp
    <div class="premium-stat-card premium-stat-card--{{ $tierClass }}">
        <div class="premium-stat-icon premium-stat-icon--{{ $tierClass }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
        </div>
        <div class="premium-stat-value">{{ $count }}</div>
        <div class="premium-stat-label">{{ ucfirst($tier) }}</div>
    </div>
    @endforeach
</div>

<!-- Subscriptions Table -->
<div class="premium-table-section">
    <div class="premium-table-header">
        <h3 class="premium-table-title">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            Abonelik Listesi
        </h3>
        <span class="premium-table-count">{{ $activeCount }} aktif</span>
    </div>
    <div class="premium-table-wrap">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Kullanici</th>
                    <th>Paket</th>
                    <th>Fiyat</th>
                    <th>Bitis Tarihi</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                @php
                    $subTier = strtolower($sub->package_type);
                    if (!in_array($subTier, ['pro', 'gold', 'platinum'])) $subTier = 'pro';
                    $isActive = $sub->is_active && $sub->expires_at && $sub->expires_at->isFuture();
                @endphp
                <tr>
                    <td>
                        <div class="premium-user-cell">
                            <span class="premium-user-avatar">{{ strtoupper(substr($sub->user->username ?? '-', 0, 1)) }}</span>
                            {{ $sub->user->username ?? '-' }}
                        </div>
                    </td>
                    <td>
                        <span class="premium-tier-badge premium-tier-badge--{{ $subTier }}">
                            <span class="premium-tier-dot premium-tier-dot--{{ $subTier }}"></span>
                            {{ ucfirst($sub->package_type) }}
                        </span>
                    </td>
                    <td><span class="premium-price">{{ number_format($sub->price_tl, 0) }} TL</span></td>
                    <td><span class="premium-date">{{ $sub->expires_at?->format('d.m.Y H:i') ?? '—' }}</span></td>
                    <td>
                        @if($isActive)
                            <form method="POST" action="{{ route('admin.premium.cancel', $sub) }}" onsubmit="return confirm('Bu premium iptal edilsin mi?');">
                                @csrf
                                <button type="submit" class="btn btn-outline btn-sm">İptal</button>
                            </form>
                        @else
                            <span class="admin-ops-meta">Pasif</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="premium-empty">
                            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            <p>Hen&uuml;z premium abonelik kaydi yok.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="premium-pagination">
        {{ $subscriptions->links() }}
    </div>
</div>
@endsection
