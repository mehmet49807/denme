@extends('layouts.admin')

@section('title', 'Fotoğraf Doğrulama')
@section('lead', 'Selfie başvurularını inceleyin; onaylanan üyelere doğrulama işareti verilir.')

@section('content')
<div class="admin-stat-grid admin-stat-grid--compact">
    <div class="admin-stat-card admin-stat-card--gold">
        <div class="admin-stat-value">{{ $stats['pending'] }}</div>
        <div class="admin-stat-label">Bekleyen</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value">{{ $stats['approved'] }}</div>
        <div class="admin-stat-label">Onaylı</div>
    </div>
    <div class="admin-stat-card admin-stat-card--coral">
        <div class="admin-stat-value">{{ $stats['rejected'] }}</div>
        <div class="admin-stat-label">Reddedilen</div>
    </div>
</div>

<div class="admin-panel admin-panel--glass">
    <form method="GET" action="{{ route('admin.photo-verifications') }}" class="admin-users-filter" role="search">
        <div class="admin-users-filter-field">
            <label for="pv-status">Durum</label>
            <select id="pv-status" name="status">
                <option value="pending" @selected($status === 'pending')>Bekleyen</option>
                <option value="approved" @selected($status === 'approved')>Onaylı</option>
                <option value="rejected" @selected($status === 'rejected')>Reddedilen</option>
            </select>
        </div>
        <div class="admin-users-filter-field admin-users-filter-field--grow">
            <label for="pv-search">Ara</label>
            <input type="search" id="pv-search" name="search" value="{{ $search }}" placeholder="Kullanıcı adı veya e-posta">
        </div>
        <div class="admin-users-filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">Filtrele</button>
        </div>
    </form>
</div>

<div class="admin-gallery-grid">
    @forelse($users as $user)
        <article class="admin-gallery-card">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.35rem;">
                <div>
                    <small class="admin-ops-meta">Profil</small>
                    @if($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->username }}" loading="lazy">
                    @else
                        <div class="admin-ops-empty">Yok</div>
                    @endif
                </div>
                <div>
                    <small class="admin-ops-meta">Selfie</small>
                    @if($user->photo_verify_selfie_url)
                        <img src="{{ $user->photo_verify_selfie_url }}" alt="selfie" loading="lazy">
                    @else
                        <div class="admin-ops-empty">Yok</div>
                    @endif
                </div>
            </div>
            <div class="admin-gallery-card__meta">
                <strong>{{ $user->username }}</strong>
                <span>{{ $user->photo_verify_submitted_at?->format('d.m.Y H:i') }}</span>
            </div>
            @if($status === 'pending')
                <form method="POST" action="{{ route('admin.photo-verifications.approve', $user) }}" style="margin-bottom:.4rem;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">Onayla</button>
                </form>
                <form method="POST" action="{{ route('admin.photo-verifications.reject', $user) }}">
                    @csrf
                    <input type="text" name="note" placeholder="Red nedeni" maxlength="250" style="width:100%;margin-bottom:.35rem;">
                    <button type="submit" class="btn btn-danger btn-sm">Reddet</button>
                </form>
            @else
                <p class="admin-ops-meta">{{ $user->photo_verify_note }}</p>
            @endif
        </article>
    @empty
        <div class="admin-panel admin-panel--glass">
            <p class="admin-ops-empty">Bu filtrede başvuru yok.</p>
        </div>
    @endforelse
</div>

{{ $users->links() }}
@endsection
