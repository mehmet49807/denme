@extends('layouts.admin')

@section('title', 'AI Denetim')
@section('lead', 'OpenRouter yapay zeka ile otomatik içerik denetimi ve ihlal takibi.')

@section('header-actions')
    <form action="{{ route('admin.ai.publish-blog-faq') }}" method="POST" class="admin-inline-form">
        @csrf
        <button type="submit" class="btn btn-primary">Yeni Blog / SSS Yayınla</button>
    </form>
    <form action="{{ route('admin.ai.scan') }}" method="POST" class="admin-inline-form">
        @csrf
        <button type="submit" class="btn btn-outline">Tarama Başlat</button>
    </form>
    <form action="{{ route('admin.ai.test') }}" method="POST" class="admin-inline-form">
        @csrf
        <button type="submit" class="btn btn-primary">Bağlantı Testi</button>
    </form>
@endsection

@section('content')
<div class="admin-stat-grid admin-stat-grid--compact">
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value">{{ $stats['pending'] }}</div>
        <div class="admin-stat-label">Bekleyen İhlal</div>
    </div>
    <div class="admin-stat-card admin-stat-card--coral">
        <div class="admin-stat-value">{{ $stats['today'] }}</div>
        <div class="admin-stat-label">Bugün Tespit</div>
    </div>
    <div class="admin-stat-card admin-stat-card--gold">
        <div class="admin-stat-value">{{ $stats['high'] }}</div>
        <div class="admin-stat-label">Yüksek Öncelik</div>
    </div>
    <div class="admin-stat-card admin-stat-card--indigo">
        <div class="admin-stat-value">{{ $stats['ai_source'] }}</div>
        <div class="admin-stat-label">AI (24s)</div>
    </div>
</div>

<div class="admin-panel admin-panel--glass admin-ai-status">
    <div class="admin-ai-status__row">
        <span class="admin-online-pulse {{ $connection['ok'] ? '' : 'admin-online-pulse--off' }}"></span>
        <div>
            <strong>OpenRouter · {{ $model }}</strong>
            <p>{{ $connection['message'] ?? ($configured ? 'Yapılandırıldı' : 'API anahtarı eksik') }}
                @if(!empty($connection['latency_ms']))
                    · {{ $connection['latency_ms'] }} ms
                @endif
            </p>
        </div>
    </div>
    <ul class="admin-ai-tasks">
        <li>Mesaj denetimi (IBAN, para talebi, telefon)</li>
        <li>Gönderi / hikaye denetimi (sosyal medya, iletişim bilgisi)</li>
        <li>Sahte profil tespiti</li>
        <li>Şikayet analizi ve rapor oluşturma</li>
        <li>Yeni Türkçe Blog / SSS üretip web sitesinde yayına alma</li>
        <li>İhlal bildirimi kullanıcıya otomatik gider</li>
    </ul>
</div>

<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Tespit Edilen İhlaller</h3>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kullanıcı</th>
                    <th>Tür</th>
                    <th>Kategori</th>
                    <th>Kaynak</th>
                    <th>Öncelik</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($flags as $flag)
                    <tr>
                        <td>
                            @if($flag->user)
                                <strong>{{ $flag->user->username }}</strong>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $flag->contentTypeLabel() }}</td>
                        <td>{{ $flag->categoryLabel() }}</td>
                        <td><span class="badge badge-premium">{{ strtoupper($flag->source) }}</span></td>
                        <td>
                            <span class="badge {{ $flag->severity === 'high' ? 'badge-banned' : 'badge-pending' }}">
                                {{ strtoupper($flag->severity) }}
                            </span>
                        </td>
                        <td>{{ $flag->status }}</td>
                        <td>{{ $flag->created_at?->format('d.m.Y H:i') }}</td>
                        <td>
                            <details class="admin-flag-details">
                                <summary>İncele</summary>
                                <div class="admin-flag-panel">
                                    <p><strong>İçerik:</strong> {{ $flag->content_excerpt ?: '—' }}</p>
                                    @if($flag->ai_reason)
                                        <p><strong>AI / Kural:</strong> {{ $flag->ai_reason }}</p>
                                    @endif
                                    <form action="{{ route('admin.ai.flags.update', $flag) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <label>Durum
                                            <select name="status" class="admin-input">
                                                @foreach(['pending','reviewed','actioned','dismissed'] as $st)
                                                    <option value="{{ $st }}" @selected($flag->status === $st)>{{ $st }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label>Not
                                            <textarea name="admin_notes" class="admin-input" rows="2">{{ $flag->admin_notes }}</textarea>
                                        </label>
                                        <label class="admin-checkbox">
                                            <input type="checkbox" name="ban_user" value="1"> Kullanıcıyı banla
                                        </label>
                                        <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">Henüz ihlal kaydı yok. Tarama başlatabilirsiniz.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $flags->links() }}
</div>
@endsection
