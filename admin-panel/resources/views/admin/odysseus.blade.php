@extends('layouts.admin')

@section('title', 'Odysseus')
@section('lead', 'Bağımsız arayüz + admin komut köprüsü. Model key’leri Odysseus Settings’te tanımlanır.')

@section('content')
@php
    $isUp = (bool) ($status['ok'] ?? false);
    $endpoints = $models['endpoints'] ?? [];
    $hasModel = collect($endpoints)->contains(fn ($ep) => ! empty($ep['is_enabled']));
@endphp

<div class="admin-panel admin-panel--glass" style="margin-bottom:1rem;">
    <div class="admin-panel-head">
        <h3 class="admin-panel-title">İki kullanım</h3>
    </div>
    <ol style="margin:0;padding-left:1.2rem;line-height:1.55;">
        <li style="margin-bottom:.55rem;">
            <strong>Bağımsız kullan</strong> — Odysseus kendi arayüzünden açılır; Settings’ten model eklenir, sohbet/agent orada çalışır.
            <div style="margin-top:.45rem;">
                <a class="btn btn-primary btn-sm" href="{{ $publicUrl }}" target="_blank" rel="noopener">
                    Odysseus’u aç
                </a>
                <code style="margin-left:.5rem;">{{ $publicUrl }}</code>
            </div>
        </li>
        <li>
            <strong>Admin’den tetikle</strong> — aşağıdaki komut köprüsü kalır; model/API key yalnızca Odysseus Settings’ten okunur (admin .env’ye key yazılmaz).
        </li>
    </ol>
</div>

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
    <p class="admin-ops-meta" style="margin:0 0 .5rem;">
        Workspace: <code>{{ $workspace }}</code>
    </p>
    <p style="margin:0 0 .35rem;">
        Settings modelleri:
        <span class="admin-badge admin-badge--{{ $hasModel ? 'ok' : 'danger' }}">
            {{ $hasModel ? count($endpoints).' endpoint' : 'Yok' }}
        </span>
    </p>
    @if(! empty($models['error']))
        <p class="admin-ops-meta" style="color:#c44;margin:.35rem 0 0;">{{ $models['error'] }}</p>
    @elseif($endpoints === [])
        <p class="admin-ops-meta" style="margin:.35rem 0 0;">
            Henüz model yok. <a href="{{ $publicUrl }}" target="_blank" rel="noopener">Odysseus Settings</a>
            içinden OpenAI / Groq / Gemini ekleyin; sonra admin komutları çalışır.
        </p>
    @else
        <ul class="admin-ops-meta" style="margin:.35rem 0 0;padding-left:1.1rem;">
            @foreach($endpoints as $ep)
                <li>
                    {{ $ep['name'] ?? 'Model' }}
                    @if(! empty($ep['status']))
                        <span style="opacity:.7;">({{ $ep['status'] }})</span>
                    @endif
                    @if(! empty($ep['models']))
                        — <code>{{ implode(', ', array_slice($ep['models'], 0, 3)) }}</code>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
    @unless($isUp)
        <p class="admin-ops-meta" style="margin:.75rem 0 0;">
            Sunucuda kurulum:
            <code>bash scripts/odysseus/install.sh && bash scripts/odysseus/start.sh</code>
        </p>
    @endunless
</div>

<div class="admin-panel admin-panel--glass" style="margin-bottom:1rem;">
    <div class="admin-panel-head">
        <h3 class="admin-panel-title">Admin komut köprüsü</h3>
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
        <button type="submit" class="btn btn-primary" @disabled(! $isUp || ! $hasModel)>
            Odysseus’a gönder
        </button>
        @unless($hasModel)
            <p class="admin-ops-meta" style="margin:.65rem 0 0;">
                Komut göndermek için önce Odysseus Settings’te bir model tanımlayın.
            </p>
        @endunless
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
