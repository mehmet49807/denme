@extends('layouts.admin')

@section('title', 'GitHub Deploy')
@section('lead', 'GitHub Actions üzerinden otomatik canlıya alma durumu ve bağlantılar.')

@section('header-actions')
    <a href="{{ $actionsUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">GitHub Actions</a>
    <a href="{{ $repo }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline">Depoyu Aç</a>
@endsection

@section('content')
<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Kaynak depo</h3>
    <p class="admin-muted">Tüm site kodları tek depoda tutulur. <code>master</code> dalına merge edildiğinde deploy otomatik başlar.</p>
    <ul class="admin-list-plain">
        <li><strong>Depo:</strong> <a href="{{ $repo }}" target="_blank" rel="noopener noreferrer">{{ $repo }}</a></li>
        <li><strong>Dal:</strong> <code>{{ $branch }}</code></li>
        <li><strong>Workflow:</strong> <a href="{{ $actionsUrl }}" target="_blank" rel="noopener noreferrer">deploy.yml</a></li>
    </ul>
</div>

<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Son deploy bilgisi</h3>
    @if($lastDeployAt)
        <ul class="admin-list-plain">
            <li><strong>Zaman:</strong> {{ $lastDeployAt }}</li>
            <li><strong>Hedef:</strong> {{ $lastDeployTarget ?: 'all' }}</li>
            <li><strong>Commit:</strong> <code>{{ $lastDeployCommit ?: '—' }}</code></li>
        </ul>
    @else
        <p class="admin-muted">Henüz kayıtlı deploy bilgisi yok. İlk GitHub Actions çalışmasından sonra burada görünebilir.</p>
    @endif
</div>

<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Nasıl çalışır?</h3>
    <ol class="admin-list-numbered">
        <li>Geliştirme <code>cursor/*</code> dalında yapılır.</li>
        <li>PR merge edilip <code>{{ $branch }}</code> güncellenir.</li>
        <li>GitHub Actions FTP ile <strong>gonulkoprusu.com</strong> ve <strong>admin.gonulkoprusu.com</strong> sunucularına dosyaları yükler.</li>
        <li>Deploy sonrası önbellek temizlenir.</li>
    </ol>
</div>

<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Manuel deploy</h3>
    <p class="admin-muted">Acil durumda GitHub → Actions → <strong>Deploy to cPanel</strong> → <em>Run workflow</em> ile web, admin veya ikisini birden tetikleyebilirsiniz.</p>
</div>
@endsection
