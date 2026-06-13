@extends('layouts.admin')
@section('title', 'Genel Bakış')

@section('content')
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:18px;">
    <div class="gk-card">
        <div style="color:var(--gk-text-soft);font-size:.85rem;">Toplam Kullanıcı</div>
        <div style="font-size:2rem;font-weight:700;">{{ $stats['users'] }}</div>
        <div class="gk-location-box" style="margin-top:8px;">
            {{ $stats['men'] }} Erkek <span class="gk-sep">·</span> {{ $stats['women'] }} Kadın
        </div>
    </div>
    <div class="gk-card">
        <div style="color:var(--gk-text-soft);font-size:.85rem;">Premium Üye</div>
        <div style="font-size:2rem;font-weight:700;">{{ $stats['premium'] }}</div>
        <span class="gk-badge gk-badge--premium">Yalnızca Erkek</span>
    </div>
    <div class="gk-card">
        <div style="color:var(--gk-text-soft);font-size:.85rem;">Bekleyen Şikayet</div>
        <div style="font-size:2rem;font-weight:700;">{{ $stats['pending_reports'] }}</div>
    </div>
    <div class="gk-card">
        <div style="color:var(--gk-text-soft);font-size:.85rem;">Toplam Mesaj</div>
        <div style="font-size:2rem;font-weight:700;">{{ $stats['messages'] }}</div>
    </div>
    <div class="gk-card">
        <div style="color:var(--gk-text-soft);font-size:.85rem;">Aktif Premium Geliri</div>
        <div style="font-size:2rem;font-weight:700;">{{ number_format($stats['revenue'], 2) }} TL</div>
    </div>
</div>
@endsection
