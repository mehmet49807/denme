@extends('layouts.admin')

@section('title', 'Odysseus')
@section('lead', 'Admin komutu ver; Odysseus agent workspace üzerinde kod ekler ve düzenler.')

@section('content')
@php
    $isUp = (bool) ($status['ok'] ?? false);
@endphp

<div class="admin-panel admin-panel--glass" style="margin-bottom:1rem;">
    <div class="admin-panel-head">
        <h3 class="admin-panel-title">Servis durumu</h3>
        <form method="POST" action="{{ route('admin.odysseus.refresh') }}" class="admin-inline-form">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm">Yenile</button>
        </form>
    </div>
    <p style="margin:0 0 .5rem;">
        <span class="admin-badge admin-badge--{{ $isUp ? 'ok' : 'danger' }}">{{ $isUp ? 'Çalışıyor' : 'Kapalı' }}</span>
        <code style="margin-left:.5rem;">{{ $baseUrl }}</code>
    </p>
    <p class="admin-ops-meta" style="margin:0;">
        Workspace: <code>{{ $workspace }}</code>
    </p>
    @unless($isUp)
        <p class="admin-ops-meta" style="margin:.75rem 0 0;">
            Sunucuda kurulum:
            <code>bash scripts/odysseus/install.sh && bash scripts/odysseus/start.sh</code>
        </p>
    @endunless
</div>

<div class="admin-panel admin-panel--glass" style="margin-bottom:1rem;">
    <div class="admin-panel-head">
        <h3 class="admin-panel-title">Komut</h3>
    </div>
    <form method="POST" action="{{ route('admin.odysseus.run') }}">
        @csrf
        <label class="admin-field" style="display:block;margin-bottom:.75rem;">
            <span style="display:block;margin-bottom:.35rem;font-size:.85rem;opacity:.8;">Ne yapılsın? (kod ekle / düzelt / düzenle)</span>
            <textarea
                name="command"
                rows="6"
                required
                maxlength="8000"
                placeholder="Örn: App giriş sayfasındaki logo plakasını biraz büyüt ve mobil boşluğu azalt."
                style="width:100%;min-height:140px;border-radius:14px;padding:.9rem 1rem;font:inherit;"
            >{{ old('command') }}</textarea>
        </label>
        @error('command')
            <p class="admin-ops-meta" style="color:#c44;">{{ $message }}</p>
        @enderror
        <button type="submit" class="btn btn-primary" @disabled(! $isUp)>
            Odysseus’a gönder
        </button>
    </form>
</div>

@if(! empty($history))
<div class="admin-panel admin-panel--glass">
    <div class="admin-panel-head">
        <h3 class="admin-panel-title">Son komutlar</h3>
    </div>
    @foreach($history as $item)
        <div class="admin-ops-row" style="align-items:flex-start;flex-direction:column;gap:.35rem;margin-bottom:.85rem;">
            <div style="width:100%;display:flex;justify-content:space-between;gap:.75rem;flex-wrap:wrap;">
                <strong>{{ $item['at'] ?? '' }}</strong>
                <span class="admin-badge admin-badge--{{ ! empty($item['ok']) ? 'ok' : 'danger' }}">
                    {{ ! empty($item['ok']) ? 'OK' : 'Hata' }}
                </span>
            </div>
            <div style="width:100%;white-space:pre-wrap;font-size:.92rem;">{{ $item['command'] ?? '' }}</div>
            @if(! empty($item['error']))
                <div class="admin-ops-meta" style="color:#c44;white-space:pre-wrap;">{{ $item['error'] }}</div>
            @endif
            @if(! empty($item['reply']))
                <div class="admin-ops-meta" style="white-space:pre-wrap;">{{ \Illuminate\Support\Str::limit($item['reply'], 1200) }}</div>
            @endif
        </div>
    @endforeach
</div>
@endif
@endsection
