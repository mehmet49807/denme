@extends('layouts.app')
@section('title', 'Kayıt Ol · Gönül Köprüsü')

@section('content')
<div class="gk-container" style="max-width:560px;">
    <div class="gk-card">
        <h2 style="margin-top:0;">Aramıza Katılın</h2>
        @if ($errors->any())
            <div style="color:var(--gk-rose-deep); margin-bottom:12px;">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="gk-field">
                <label>Kullanıcı Adı (sonradan değiştirilemez)</label>
                <input class="gk-input" name="username" value="{{ old('username') }}" required>
            </div>
            <div class="gk-row">
                <div class="gk-field"><label>Ad</label><input class="gk-input" name="first_name" value="{{ old('first_name') }}" required></div>
                <div class="gk-field"><label>Soyad</label><input class="gk-input" name="last_name" value="{{ old('last_name') }}" required></div>
            </div>
            <div class="gk-field"><label>E-posta</label><input class="gk-input" type="email" name="email" value="{{ old('email') }}" required></div>
            <div class="gk-field"><label>Telefon</label><input class="gk-input" name="phone" value="{{ old('phone') }}" required></div>
            <div class="gk-row">
                <div class="gk-field">
                    <label>Cinsiyet</label>
                    <select class="gk-select" name="gender" required>
                        <option value="female">Kadın</option>
                        <option value="male">Erkek</option>
                    </select>
                </div>
                {{-- City - District side-by-side box selection --}}
                <div class="gk-field"><label>Şehir</label><input class="gk-input" name="city" value="{{ old('city') }}" required></div>
                <div class="gk-field"><label>İlçe</label><input class="gk-input" name="district" value="{{ old('district') }}" required></div>
            </div>
            <div class="gk-row">
                <div class="gk-field"><label>Şifre</label><input class="gk-input" type="password" name="password" required></div>
                <div class="gk-field"><label>Şifre Tekrar</label><input class="gk-input" type="password" name="password_confirmation" required></div>
            </div>
            <button class="gk-btn" style="width:100%;">Kayıt Ol</button>
        </form>
    </div>
</div>
@endsection
