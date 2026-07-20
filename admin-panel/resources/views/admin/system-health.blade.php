@extends('layouts.admin')

@section('title', 'Sistem Sağlığı')
@section('lead', 'Altyapı durumu, kuyruk yükü ve dışa aktarma.')

@section('header-actions')
    <a href="{{ route('admin.backup.users') }}" class="btn btn-outline btn-sm">CSV Dışa Aktar</a>
    <a href="{{ route('admin.backup.settings') }}" class="btn btn-outline btn-sm">Ayarlar JSON</a>
@endsection

@section('content')
<div class="admin-stat-grid admin-stat-grid--compact">
    <div class="admin-stat-card admin-stat-card--indigo">
        <div class="admin-stat-value" style="font-size:1.1rem">PHP {{ $phpVersion }}</div>
        <div class="admin-stat-label">PHP</div>
    </div>
    <div class="admin-stat-card admin-stat-card--violet">
        <div class="admin-stat-value" style="font-size:1.1rem">{{ $laravelVersion }}</div>
        <div class="admin-stat-label">Laravel</div>
    </div>
    <div class="admin-stat-card admin-stat-card--gold">
        <div class="admin-stat-value" style="font-size:1.1rem">{{ $appEnv }}</div>
        <div class="admin-stat-label">Ortam</div>
    </div>
</div>

<div class="admin-health-grid">
    @foreach($checks as $check)
        <div class="admin-health-card {{ $check['ok'] ? 'is-ok' : 'is-bad' }}">
            <strong>{{ $check['label'] }}</strong>
            <span>{{ $check['detail'] }}</span>
            <em>{{ $check['ok'] ? 'Sağlıklı' : 'Dikkat' }}</em>
        </div>
    @endforeach
</div>

<div class="admin-panel admin-panel--glass" style="margin-top:1.25rem">
    <h3 class="admin-panel-title">FCM Push yapılandırması</h3>
    @php $fcm = $fcmStatus ?? []; @endphp
    <p class="admin-ops-meta">
        @if(!empty($fcm['configured']))
            Hazır · proje {{ $fcm['project_id'] ?? '—' }} · {{ $fcm['device_count'] ?? 0 }} cihaz
            · kaynak {{ $fcm['credentials_source'] ?? '—' }}
        @else
            Service account JSON eksik. Firebase Console → Project settings → Service accounts → Generate new private key.
        @endif
    </p>
    <form method="post" action="{{ route('admin.system-health.fcm') }}" enctype="multipart/form-data" class="admin-inline-form" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;margin-top:.75rem">
        @csrf
        <input type="file" name="credentials" accept=".json,application/json" required>
        <button type="submit" class="btn btn-primary">JSON yükle</button>
    </form>
</div>

<div class="admin-panel admin-panel--glass" style="margin-top:1.25rem">
    <h3 class="admin-panel-title">Yedek / dışa aktarma</h3>
    <p class="admin-ops-meta">Tam veritabanı geri yükleme yerine güvenli CSV ve ayar dışa aktarımı.</p>
    <div class="admin-quick-actions">
        <a href="{{ route('admin.backup.users') }}" class="btn btn-primary">Kullanıcı CSV</a>
        <a href="{{ route('admin.backup.settings') }}" class="btn btn-outline">Site Ayarları JSON</a>
        <a href="{{ route('admin.audit') }}" class="btn btn-outline">Denetim kayıtları</a>
    </div>
</div>
@endsection
