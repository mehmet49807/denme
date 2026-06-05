@extends('layouts.admin')
@section('title', 'Kullanıcı: ' . $user->username)

@section('content')
<div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
    <div class="gk-card">
        <h3 style="margin-top:0;">Profil Bilgileri</h3>
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')
            <div class="gk-field">
                <label>Kullanıcı Adı (değiştirilemez)</label>
                <input class="gk-input" value="{{ $user->username }}" readonly>
            </div>
            <div class="gk-row">
                <div class="gk-field"><label>Ad</label><input class="gk-input" name="first_name" value="{{ $user->first_name }}"></div>
                <div class="gk-field"><label>Soyad</label><input class="gk-input" name="last_name" value="{{ $user->last_name }}"></div>
            </div>
            <div class="gk-field"><label>E-posta</label><input class="gk-input" name="email" value="{{ $user->email }}"></div>
            <div class="gk-field"><label>Telefon</label><input class="gk-input" name="phone" value="{{ $user->phone }}"></div>
            <div class="gk-row">
                <div class="gk-field"><label>Şehir</label><input class="gk-input" name="city" value="{{ $user->city }}"></div>
                <div class="gk-field"><label>İlçe</label><input class="gk-input" name="district" value="{{ $user->district }}"></div>
            </div>
            <button class="gk-btn">Kaydet</button>
        </form>
    </div>

    <div class="gk-card">
        <h3 style="margin-top:0;">Durum & İşlemler</h3>
        <p>Cinsiyet: <strong>{{ $user->gender === 'male' ? 'Erkek' : 'Kadın' }}</strong></p>
        <p>Premium: <strong>{{ $user->is_premium ? 'Evet' : 'Hayır' }}</strong></p>
        <p>Durum: <span class="gk-badge gk-badge--{{ $user->status }}">{{ $user->status }}</span></p>

        <form method="POST" action="{{ route('admin.users.ban', $user) }}" style="margin-top:18px;">
            @csrf
            <button class="gk-btn">{{ $user->status === 'banned' ? 'Yasağı Kaldır' : 'Yasakla' }}</button>
        </form>
        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="margin-top:12px;"
              onsubmit="return confirm('Bu kullanıcı kalıcı olarak silinsin mi?');">
            @csrf @method('DELETE')
            <button class="gk-btn gk-btn--danger">Kullanıcıyı Sil</button>
        </form>
    </div>
</div>
@endsection
