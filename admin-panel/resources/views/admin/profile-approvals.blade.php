@extends('layouts.admin')

@section('title', 'Profil Onay')
@section('eyebrow', 'Güven ve moderasyon')
@section('lead', 'Fotoğraflı ve gerçek görünen kullanıcı profillerini hızlıca onaylayın; onaylanan profillerde doğrulanmış profil rozeti görünür.')

@section('header-actions')
    <form method="POST" action="{{ route('admin.profile-approvals.bulk') }}" class="admin-inline-form">
        @csrf
        <input type="hidden" name="limit" value="25">
        <button type="submit" class="admin-btn admin-btn-primary admin-bulk-approve-btn">
            <span class="admin-bulk-approve-btn__full">Fotoğraflı 25 Profili Hızlı Onayla</span>
            <span class="admin-bulk-approve-btn__short">25 Fotoğraflı Onayla</span>
        </button>
    </form>
@endsection

@section('content')
<div class="admin-stat-grid">
    <div class="admin-stat-card admin-stat-card--amber">
        <div class="admin-stat-value">{{ $stats['pending'] ?? 0 }}</div>
        <div class="admin-stat-label">Bekleyen Profil</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value">{{ $stats['verified'] ?? 0 }}</div>
        <div class="admin-stat-label">Onaylı Profil</div>
    </div>
    <div class="admin-stat-card admin-stat-card--blue">
        <div class="admin-stat-value">{{ $stats['with_photo_pending'] ?? 0 }}</div>
        <div class="admin-stat-label">Fotoğraflı Bekleyen</div>
    </div>
    <div class="admin-stat-card admin-stat-card--rose">
        <div class="admin-stat-value">{{ $stats['no_photo'] ?? 0 }}</div>
        <div class="admin-stat-label">Fotoğrafsız</div>
    </div>
</div>

<section class="admin-card">
    <form method="GET" action="{{ route('admin.profile-approvals') }}" class="admin-filter-form">
        <label>
            Durum
            <select name="status">
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Bekleyen</option>
                <option value="with_photo" {{ $status === 'with_photo' ? 'selected' : '' }}>Fotoğraflı Bekleyen</option>
                <option value="no_photo" {{ $status === 'no_photo' ? 'selected' : '' }}>Fotoğrafsız</option>
                <option value="verified" {{ $status === 'verified' ? 'selected' : '' }}>Onaylı</option>
            </select>
        </label>
        <label>
            Arama
            <input type="search" name="search" value="{{ $search }}" placeholder="Kullanıcı, ad veya e-posta">
        </label>
        <button type="submit" class="admin-btn">Filtrele</button>
        <a href="{{ route('admin.profile-approvals') }}" class="admin-btn admin-btn-ghost">Sıfırla</a>
    </form>
</section>

<section class="admin-card">
    <div class="admin-table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Profil</th>
                    <th>Bilgi</th>
                    <th>Fotoğraf</th>
                    <th>Onay Durumu</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="admin-user-cell">
                                <span class="admin-user-avatar">
                                    @if($user->profile_photo_url)
                                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->username }}" width="44" height="44" loading="lazy">
                                    @else
                                        {{ strtoupper(substr($user->username, 0, 1)) }}
                                    @endif
                                </span>
                                <div>
                                    <strong>{{ $user->username }}</strong>
                                    <small>{{ $user->first_name }} {{ $user->last_name }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $user->email }}</div>
                            <small>{{ $user->gender === 'male' ? 'Erkek' : 'Kadın' }} · {{ $user->city ?: 'Şehir yok' }}</small>
                        </td>
                        <td>
                            @if($user->profile_photo_url)
                                <span class="admin-badge admin-badge--success">Fotoğraf var</span>
                            @else
                                <span class="admin-badge admin-badge--warning">Fotoğraf yok</span>
                            @endif
                        </td>
                        <td>
                            @if($user->profile_verified_at)
                                <span class="admin-badge admin-badge--success">Onaylı</span>
                                <small>{{ $user->profile_verified_at->format('d.m.Y H:i') }}</small>
                            @else
                                <span class="admin-badge admin-badge--warning">Bekliyor</span>
                            @endif
                        </td>
                        <td>
                            @if(!$user->profile_verified_at)
                                <form method="POST" action="{{ route('admin.profile-approvals.approve', $user) }}" class="admin-inline-form">
                                    @csrf
                                    <input type="hidden" name="note" value="Admin hızlı profil onayı">
                                    <button type="submit" class="admin-btn admin-btn-primary">Onayla</button>
                                </form>
                            @else
                                <span class="admin-muted">Rozet aktif</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="admin-table-empty">Bu filtreye uygun profil bulunamadı.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-pagination">
        {{ $users->links() }}
    </div>
</section>
@endsection
