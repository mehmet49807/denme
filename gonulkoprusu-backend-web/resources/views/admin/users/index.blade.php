@extends('layouts.admin')
@section('title', 'Kullanıcı Yönetimi')

@section('content')
<form method="GET" class="gk-row" style="margin-bottom:18px; max-width:560px;">
    <input class="gk-input" name="q" value="{{ request('q') }}" placeholder="Kullanıcı adı veya e-posta ara">
    <select class="gk-select" name="gender">
        <option value="">Tümü</option>
        <option value="male" @selected(request('gender')==='male')>Erkek</option>
        <option value="female" @selected(request('gender')==='female')>Kadın</option>
    </select>
    <button class="gk-btn">Ara</button>
</form>

<div class="gk-card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:var(--gk-cream-2); text-align:left;">
                <th style="padding:12px;">Kullanıcı Adı</th>
                <th>Cinsiyet</th>
                <th>Şehir - İlçe</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($users as $u)
            <tr style="border-top:1px solid var(--gk-beige);">
                <td style="padding:12px;font-weight:600;">{{ $u->username }}
                    @if($u->is_premium) <span class="gk-badge gk-badge--premium">Premium</span> @endif
                </td>
                <td>{{ $u->gender === 'male' ? 'Erkek' : 'Kadın' }}</td>
                <td><span class="gk-location-box">{{ $u->city }} <span class="gk-sep">·</span> {{ $u->district }}</span></td>
                <td><span class="gk-badge gk-badge--{{ $u->status }}">{{ $u->status === 'banned' ? 'Yasaklı' : 'Aktif' }}</span></td>
                <td>
                    <a class="gk-btn gk-btn--ghost" href="{{ route('admin.users.show', $u) }}">Detay</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div style="margin-top:16px;">{{ $users->links() }}</div>
@endsection
