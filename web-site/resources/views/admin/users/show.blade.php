@extends('layouts.admin')

@section('title', 'Kullanıcı Detayı: ' . ($user->username ?? 'Kullanıcı'))

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">← Kullanıcı Listesine Dön</a>
</div>

<div class="card">
    <div style="display: flex; flex-wrap: wrap; gap: 24px; align-items: flex-start;">
        <div style="text-align: center; min-width: 160px;">
            <img src="{{ $user->profile_photo_url ?? asset('images/default-avatar.png') }}" 
                 alt="{{ $user->username }}" 
                 style="width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 4px solid var(--primary); box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="margin-top: 12px; display: flex; flex-direction: column; gap: 6px; align-items: center;">
                @if($user->is_banned)
                    <span class="badge badge-danger">YASAKLI</span>
                @else
                    <span class="badge badge-success">AKTİF ÜYE</span>
                @endif
                @if($user->is_verified)
                    <span class="badge badge-info">✓ ONAYLI</span>
                @endif
                @if($user->isPremium())
                    <span class="badge badge-warning">💎 PREMIUM</span>
                @endif
            </div>
        </div>

        <div style="flex: 1; min-width: 280px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <div>
                    <h2 style="font-size: 1.5rem; color: var(--text-heading); font-weight: 700;">{{ $user->first_name ?? '' }} {{ $user->last_name ?? '' }}</h2>
                    <p style="color: var(--text-body); font-size: 0.95rem;">@{{ $user->username }}</p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    @if(!$user->is_verified)
                    <form action="{{ route('admin.users.verify-photo', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">✅ Fotoğraf Onayla</button>
                    </form>
                    @endif
                    @if($user->is_banned)
                    <form action="{{ route('admin.users.unban', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">🔓 Banı Kaldır</button>
                    </form>
                    @else
                    <form action="{{ route('admin.users.ban', $user) }}" method="POST">
                        @csrf
                        <input type="text" name="reason" placeholder="Ban sebebi (opsiyonel)" style="padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; max-width: 200px;">
                        <button type="submit" class="btn btn-danger btn-sm">🚫 Banla</button>
                    </form>
                    @endif
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; background-color: #f9fafb; padding: 16px; border-radius: 8px;">
                <div>
                    <strong style="display: block; font-size: 0.8rem; color: #9ca3af; text-transform: uppercase;">E-posta</strong>
                    <span>{{ $user->email ?? '-' }}</span>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.8rem; color: #9ca3af; text-transform: uppercase;">Telefon</strong>
                    <span>{{ $user->phone ?? '-' }}</span>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.8rem; color: #9ca3af; text-transform: uppercase;">Cinsiyet</strong>
                    <span>{{ $user->gender === 'male' ? 'Erkek' : ($user->gender === 'female' ? 'Kadın' : '-') }}</span>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.8rem; color: #9ca3af; text-transform: uppercase;">Şehir</strong>
                    <span>{{ $user->city ?? '-' }} / {{ $user->district ?? '' }}</span>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.8rem; color: #9ca3af; text-transform: uppercase;">Doğum Tarihi</strong>
                    <span>{{ $user->birth_date ? $user->birth_date->format('d.m.Y') : '-' }}</span>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.8rem; color: #9ca3af; text-transform: uppercase;">Rol</strong>
                    <span>{{ ucfirst($user->role ?? 'user') }}</span>
                </div>
            </div>

            @if($user->bio)
            <div style="margin-top: 16px;">
                <strong style="display: block; font-size: 0.85rem; color: #6b7280; margin-bottom: 4px;">Biyografi</strong>
                <p style="background: #f9fafb; padding: 12px; border-radius: 8px; font-size: 0.9rem;">{{ $user->bio }}</p>
            </div>
            @endif

            @if($user->photo_verify_status === 'pending')
            <div style="margin-top: 12px; padding: 12px; background: #fef3c7; border-radius: 8px; border: 1px solid #fcd34d;">
                <strong>⚠️ Fotoğraf doğrulama bekliyor</strong>
                @if($user->photo_verify_selfie_url)
                <div style="margin-top: 8px;">
                    <img src="{{ $user->photo_verify_selfie_url }}" style="max-width: 200px; border-radius: 8px; border: 1px solid #e5e7eb;">
                </div>
                @endif
            </div>
            @endif

            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <label class="form-label" style="margin: 0;">Rol Değiştir:</label>
                        <select name="role" class="form-select" style="max-width: 180px;">
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Kullanıcı</option>
                            <option value="moderator" {{ $user->role === 'moderator' ? 'selected' : '' }}>Moderatör</option>
                            <option value="support" {{ $user->role === 'support' ? 'selected' : '' }}>Destek</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($reportsAgainst->count() > 0)
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Bu Kullanıcı Hakkındaki Şikayetler ({{ $reportsAgainst->count() }})</h3>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Şikayet Eden</th>
                    <th>Sebep</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportsAgainst as $report)
                <tr>
                    <td>#{{ $report->id }}</td>
                    <td>{{ $report->reporter->username ?? 'Silinmiş' }}</td>
                    <td>{{ $report->reason ?? '-' }}</td>
                    <td>
                        @if($report->status === 'pending')
                            <span class="badge badge-warning">Beklemede</span>
                        @elseif($report->status === 'resolved')
                            <span class="badge badge-success">Çözüldü</span>
                        @elseif($report->status === 'dismissed')
                            <span class="badge badge-secondary">Reddedildi</span>
                        @else
                            <span class="badge badge-info">{{ $report->status }}</span>
                        @endif
                    </td>
                    <td>{{ $report->created_at ? $report->created_at->format('d.m.Y H:i') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($user->premiumSubscriptions->count() > 0)
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Premium Abonelikler</h3>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Paket</th>
                    <th>Fiyat</th>
                    <th>Başlangıç</th>
                    <th>Bitiş</th>
                    <th>Aktif</th>
                </tr>
            </thead>
            <tbody>
                @foreach($user->premiumSubscriptions as $sub)
                <tr>
                    <td>{{ ucfirst($sub->package_type) }}</td>
                    <td>₺{{ number_format($sub->price_tl, 2) }}</td>
                    <td>{{ $sub->starts_at ? $sub->starts_at->format('d.m.Y') : '-' }}</td>
                    <td>{{ $sub->expires_at ? $sub->expires_at->format('d.m.Y') : '-' }}</td>
                    <td>
                        @if($sub->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-secondary">Pasif</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($user->posts->count() > 0)
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Gönderiler</h3>
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 12px; padding: 16px;">
        @foreach($user->posts as $post)
        <div style="width: 120px; text-align: center;">
            <img src="{{ $post->image_url }}" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">❤️ {{ $post->likes_count ?? 0 }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
