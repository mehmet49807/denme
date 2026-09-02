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
                    <option value="0" {{ request('banned') === '0' ? 'selected' : '' }}>Aktif</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Onay Durumu</label>
                <select name="verified" class="form-select">
                    <option value="">Hepsi</option>
                    <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Onaylı (Mavi Tik)</option>
                    <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Onaysız</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">E-posta Doğrulama</label>
                <select name="email_verified" class="form-select">
                    <option value="">Hepsi</option>
                    <option value="1" {{ request('email_verified') === '1' ? 'selected' : '' }}>Doğrulanmış</option>
                    <option value="0" {{ request('email_verified') === '0' ? 'selected' : '' }}>Doğrulanmamış</option>
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
                    <th>E-posta Doğrulama</th>
                    <th>Cinsiyet</th>
                    <th>Rol</th>
                    <th>Onay (Mavi Tik)</th>
                    <th>Durum</th>
                    <th>Premium</th>
                    <th>Şehir</th>
                    <th>Kayıt</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>#{{ $user->id }}</td>
                        <td style="font-weight: 600;">
                            {{ $user->username ?? '-' }}
                        </td>
                        <td>{{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: '-' }}</td>
                        <td>{{ $user->email ?? '-' }}</td>
                        <td>
                            @if($user->email_verified_at)
                                <span class="badge badge-success">Doğrulandı</span>
                            @else
                                <span class="badge badge-secondary">Bekliyor</span>
                            @endif
                        </td>
                        <td>
                            @if($user->gender === 'male')
                                <span class="badge badge-info">Erkek</span>
                            @elseif($user->gender === 'female')
                                <span class="badge badge-warning">Kadın</span>
                            @else
                                <span class="badge badge-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ ucfirst($user->role ?? 'user') }}</span>
                        </td>
                        <td style="text-align: center;">
                            @if($user->is_verified)
                                <span style="color: #1D9BF0; font-size: 16px; font-weight: bold;" title="Mavi Onay Tik">✔️</span>
                            @else
                                <span style="color: #D1D5DB; font-weight: bold;" title="Onaysız">✖</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_banned)
                                <span class="badge badge-danger">Yasaklı</span>
                            @else
                                <span class="badge badge-success">Aktif</span>
                            @endif
                        </td>
                        <td>
                            @if($user->isPremium())
                                <span class="badge badge-warning">💎 {{ ucfirst($user->packageBadge() ?? 'Premium') }}</span>
                            @else
                                <span class="badge badge-secondary">Standart</span>
                            @endif
                        </td>
                        <td>{{ $user->city ?? '-' }}</td>
                        <td>{{ $user->created_at ? $user->created_at->format('d.m.Y') : '-' }}</td>
                        <td>
                            <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-primary btn-sm" style="font-size: 12px;">
                                    👁️ Detay
                                </a>
                                @if(!$user->is_verified)
                                <form action="{{ route('admin.users.approve', $user->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" style="font-size: 12px;" title="Mavi onay tik ver">
                                        ✅ Onayla
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" style="text-align: center; padding: 24px;">
                            Aradığınız kriterlere uygun kullanıcı bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
