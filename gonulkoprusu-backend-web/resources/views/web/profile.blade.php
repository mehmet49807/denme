@extends('layouts.app')
@section('title', 'Profilim · Gönül Köprüsü')

@section('content')
<div class="gk-container" style="max-width:620px;">
    <div class="gk-card">
        <div style="display:flex; gap:18px; align-items:center;">
            <div class="gk-mark" style="width:84px;height:84px;border-radius:50%;"></div>
            <div>
                <h2 style="margin:0;">{{ $user->username }}
                    @if($user->is_premium)<span class="gk-badge gk-badge--premium">Premium</span>@endif
                </h2>
                {{-- City - District side-by-side bounded box --}}
                <span class="gk-location-box">{{ $user->city }} <span class="gk-sep">·</span> {{ $user->district }}</span>
            </div>
        </div>

        <hr style="border:none; border-top:1px solid var(--gk-beige); margin:22px 0;">

        <p style="color:var(--gk-text-muted); font-size:.85rem;">
            Kullanıcı adınız hariç tüm bilgileri güncelleyebilirsiniz. Aşağıdaki bilgiler
            <strong>yalnızca size ve yöneticilere</strong> görünür; diğer kullanıcılar sadece kullanıcı adınızı görür.
        </p>

        <div class="gk-field">
            <label>Kullanıcı Adı (değiştirilemez)</label>
            <input class="gk-input" value="{{ $user->username }}" readonly>
        </div>
        <div class="gk-row">
            <div class="gk-field"><label>Ad (gizli)</label><input class="gk-input" value="{{ $user->first_name }}"></div>
            <div class="gk-field"><label>Soyad (gizli)</label><input class="gk-input" value="{{ $user->last_name }}"></div>
        </div>
        <div class="gk-field"><label>E-posta (gizli)</label><input class="gk-input" value="{{ $user->email }}"></div>
        <div class="gk-field"><label>Telefon (gizli)</label><input class="gk-input" value="{{ $user->phone }}"></div>
        <div class="gk-row">
            <div class="gk-field"><label>Şehir</label><input class="gk-input" value="{{ $user->city }}"></div>
            <div class="gk-field"><label>İlçe</label><input class="gk-input" value="{{ $user->district }}"></div>
        </div>
        <p style="color:var(--gk-text-muted); font-size:.8rem;">Kaydetmek için <code>PUT /api/v1/profile</code> uç noktasını kullanın.</p>
    </div>
</div>
@endsection
