@extends('layouts.admin')

@section('title', 'Şikayetler')
@section('lead', 'Kullanıcı şikayetlerini inceleyin ve işlem yapın.')

@section('content')
<div class="admin-messages-toolbar">
    <div class="admin-messages-stat admin-messages-stat--coral">
        <div>
            <strong>{{ $pendingReports }}</strong>
            <span>bekleyen şikayet</span>
        </div>
    </div>
    <div class="admin-messages-stat admin-messages-stat--blue">
        <div>
            <strong>{{ $totalReports }}</strong>
            <span>toplam şikayet</span>
        </div>
    </div>
</div>

<div class="admin-panel admin-panel--glass admin-messages-panel">
    <div class="admin-thread-list">
        @forelse($reports as $report)
            @php
                $reporter = $report->reporter;
                $reported = $report->reported;
                $statusLabels = [
                    'pending' => 'Beklemede',
                    'reviewed' => 'İncelendi',
                    'resolved' => 'Çözüldü',
                    'dismissed' => 'Reddedildi',
                ];
                $statusBadges = [
                    'pending' => 'badge-pending',
                    'reviewed' => 'badge-premium',
                    'resolved' => 'badge-resolved',
                    'dismissed' => 'badge-banned',
                ];
                $status = $report->status;
            @endphp
            <details class="admin-thread">
                <summary class="admin-thread-summary">
                    <span class="admin-thread-chevron" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>

                    <span class="admin-thread-avatars" aria-hidden="true">
                        <span class="admin-thread-avatar admin-thread-avatar--a">
                            @if($reporter?->profile_photo_url)
                                <img src="{{ $reporter->profile_photo_url }}" alt="" width="34" height="34" loading="lazy" decoding="async">
                            @else
                                {{ strtoupper(substr($reporter->username ?? '?', 0, 1)) }}
                            @endif
                        </span>
                        <span class="admin-thread-avatar admin-thread-avatar--b">
                            @if($reported?->profile_photo_url)
                                <img src="{{ $reported->profile_photo_url }}" alt="" width="34" height="34" loading="lazy" decoding="async">
                            @else
                                {{ strtoupper(substr($reported->username ?? '?', 0, 1)) }}
                            @endif
                        </span>
                    </span>

                    <span class="admin-thread-main">
                        <span class="admin-thread-users">
                            <strong>{{ $reporter->username ?? '—' }}</strong>
                            <span class="admin-thread-link-icon" aria-hidden="true">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2.5 7h9M7 2.5L11.5 7 7 11.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <strong>{{ $reported->username ?? '—' }}</strong>
                        </span>
                        <span class="admin-thread-preview">{{ Str::limit($report->reason, 72) ?: '—' }}</span>
                    </span>

                    <span class="admin-thread-meta">
                        <span class="badge {{ $statusBadges[$status] ?? 'badge-gender' }}">
                            {{ $statusLabels[$status] ?? $status }}
                        </span>
                        <time>{{ $report->created_at?->format('d.m.Y H:i') ?? '—' }}</time>
                        <span class="admin-report-id">#{{ $report->id }}</span>
                    </span>
                </summary>

                <div class="admin-thread-body">
                    <div class="admin-report-detail">
                        <article class="admin-report-reason-card">
                            <header class="admin-report-reason-head">
                                <strong>Şikayet sebebi</strong>
                                <time>{{ $report->created_at?->format('d.m.Y H:i') }}</time>
                            </header>
                            <p class="admin-report-reason-text">{{ $report->reason }}</p>
                            @if($report->admin_notes)
                                <footer class="admin-report-notes-block">
                                    <strong>Yönetici notu</strong>
                                    <p>{{ $report->admin_notes }}</p>
                                </footer>
                            @endif
                        </article>

                        <form method="POST" action="{{ route('admin.reports.update', $report) }}" class="admin-report-form">
                            @csrf
                            @method('PUT')
                            <div class="admin-report-form-grid">
                                <label class="admin-report-field">
                                    <span>Durum</span>
                                    <select name="status" class="admin-select admin-select--sm">
                                        <option value="pending" @selected($report->status === 'pending')>Beklemede</option>
                                        <option value="reviewed" @selected($report->status === 'reviewed')>İncelendi</option>
                                        <option value="resolved" @selected($report->status === 'resolved')>Çözüldü</option>
                                        <option value="dismissed" @selected($report->status === 'dismissed')>Reddedildi</option>
                                    </select>
                                </label>
                                <label class="admin-report-field admin-report-field--grow">
                                    <span>Yönetici notu</span>
                                    <input type="text" name="admin_notes" class="admin-input admin-input--sm" placeholder="İnceleme notu ekleyin…" value="{{ $report->admin_notes }}">
                                </label>
                            </div>
                            <div class="admin-report-form-actions">
                                @if($reported && $reported->role === 'user' && !$reported->is_banned)
                                    <label class="admin-checkbox-label admin-report-ban-label">
                                        <input type="checkbox" name="ban_reported" value="1">
                                        Şikayet edilen kullanıcıyı banla
                                    </label>
                                @elseif($reported?->is_banned)
                                    <span class="admin-report-ban-hint">Bu kullanıcı zaten banlı.</span>
                                @endif
                                <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
                            </div>
                        </form>
                    </div>
                </div>
            </details>
        @empty
            <div class="admin-messages-empty">
                <span class="admin-messages-empty-icon" aria-hidden="true">🚩</span>
                <p>Henüz şikayet yok.</p>
            </div>
        @endforelse
    </div>

    @if($reports->hasPages())
        {{ $reports->links() }}
    @endif
</div>
@endsection
