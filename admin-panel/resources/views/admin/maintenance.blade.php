@extends('layouts.admin')

@section('title', 'Önbellek Temizliği')
@section('lead', 'Web sitesi ve admin panel Laravel önbelleğini tek tıkla temizleyin.')

@section('content')
@if(session('success'))
    <div class="admin-flash admin-flash--success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="admin-flash admin-flash--error">{{ session('error') }}</div>
@endif

<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Önbellek Temizliği</h3>
    <p class="admin-stat-label">
        Deploy sonrası eski sayfa, rota veya yapılandırma görüyorsanız aşağıdaki butonlarla önbelleği temizleyin.
        İşlem sunucu tarafında çalışır; route, view, config ve application cache temizlenir.
    </p>

    @include('partials.admin-cache-clear-buttons', ['returnTo' => 'maintenance'])

    <ul class="admin-cache-clear-notes">
        <li><strong>Web:</strong> <code>{{ $webUrl }}/setup/clear-cache</code></li>
        <li><strong>Admin:</strong> <code>{{ $adminUrl }}/setup/clear-cache</code></li>
    </ul>
</div>

@if(!empty($cacheChecks))
<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Endpoint Durumu</h3>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kontrol</th>
                    <th>Durum</th>
                    <th>Mesaj</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cacheChecks as $check)
                    @php
                        $ok = ($check['status'] ?? '') === 'ok';
                        $warn = ($check['status'] ?? '') === 'warning';
                    @endphp
                    <tr>
                        <td>{{ $check['label'] ?? $check['id'] }}</td>
                        <td>
                            <span class="badge {{ $ok ? 'badge-premium' : ($warn ? 'badge-pending' : 'badge-banned') }}">
                                {{ strtoupper($check['status'] ?? '') }}
                            </span>
                        </td>
                        <td>{{ $check['message'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
