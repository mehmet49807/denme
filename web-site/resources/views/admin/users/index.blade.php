@extends('layouts.admin')

@section('title', 'Kullanıcılar — Gönül Köprüsü')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Kullanıcı Filtreleme & Arama</h2>
    </div>
    <form action="{{ route('admin.users.index') }}" method="GET">
        <div class="filter-grid">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Arama</label>
                <input type="text" name="search" class="form-control" placeholder="Ad, kullanıcı adı veya e-posta..." value="{{ request('search') }}">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Cinsiyet</label>
                <select name="gender" class="form-select">
                    <option value="">Hepsi</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Erkek</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Kadın</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Rol</label>
                <select name="role" class="form-select">
                    <option value="">Hepsi</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Kullanıcı</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="moderator" {{ request('role') === 'moderator' ? 'selected' : '' }}>Moderatör</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Yasak Durumu</label>
                <select name="banned" class="form-select">
                    <option value="">Hepsi</option>
                    <option value="1" {{ request('banned') === '1' ? 'selected' : '' }}>Yasaklı</option>
                    <option value="0" {{ request('banned') === '0' ? 'selected' : '' }}>Aktif (Yasaksız)</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Onay Durumu</label>
                <select name="verified" class="form-select">
                    <option value="">Hepsi</option>
                    <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Onaylı</option>
                    <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Onaysız</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Filtrele</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Temizle</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Kullanıcı Listesi</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı Adı</th>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Cinsiyet</th>
                    <th>Rol</th>
                    <th>Onay</th>
                    <th>Durum</th>
                    <th>Premium</th>
                    <th>Şehir</th>
                    <th>Kayıt Tarihi</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>#{{ $user->id }}</td>
                        <td style="font-weight: 600; color: var(--text-heading);">
                            {{ $user->username ?? '-' }}
                        </td>
                        <td>{{ $user->name ?? '-' }}</td>
                        <td>{{ $user->email ?? '-' }}</td>
                        <td>
                            @if(($user->gender ?? '') === 'male' || ($user->gender ?? '') === 'erkek')
                                <span class="badge badge-info">Erkek</span>
                            @elseif(($user->gender ?? '') === 'female' || ($user->gender ?? '') === 'kadin')
                                <span class="badge badge-warning">Kadın</span>
                            @else
                                <span class="badge badge-secondary">{{ $user->gender ?? '-' }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ ucfirst($user->role ?? 'user') }}</span>
                        </td>
                        <td style="text-align: center;">
                            @if($user->is_verified || $user->verified_at)
                                <span style="color: #10B981; font-weight: bold;">✔</span>
                            @else
                                <span style="color: #EF4444; font-weight: bold;">✖</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_banned || $user->banned_at)
                                <span class="badge badge-danger">Yasaklı</span>
                            @else
                                <span class="badge badge-success">Aktif</span>
                            @endif
                        </td>
                        <td>
                            @if($user->isPremium())
                                <span class="badge badge-warning">💎 {{ ucfirst($user->premium_package ?? 'Premium') }}</span>
                            @else
                                <span class="badge badge-secondary">Standart</span>
                            @endif
                        </td>
                        <td>{{ $user->city ?? '-' }}</td>
                        <td>{{ isset($user->created_at) ? \Carbon\Carbon::parse($user->created_at)->format('d.m.Y') : '-' }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-primary btn-sm">
                                👁️ Görüntüle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" style="text-align: center; color: var(--text-body); padding: 24px;">
                            Aradığınız kriterlere uygun kullanıcı bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($users, 'links'))
        <div class="pagination-wrapper">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
