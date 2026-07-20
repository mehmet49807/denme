@extends('layouts.admin')

@section('title', 'AI Denetim')
@section('lead', 'OpenRouter ile içerik denetimi, otomatik eskalasyon ve hızlı moderasyon.')

@section('header-actions')
    <form action="{{ route('admin.ai.daily-report') }}" method="POST" class="admin-inline-form">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm">Günlük Rapor</button>
    </form>
    <form action="{{ route('admin.ai.scan') }}" method="POST" class="admin-inline-form">
        @csrf
        <input type="hidden" name="hours" value="48">
        <button type="submit" class="btn btn-outline btn-sm">Tarama Başlat</button>
    </form>
    <form action="{{ route('admin.ai.test') }}" method="POST" class="admin-inline-form">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm">Bağlantı Testi</button>
    </form>
@endsection

@section('content')
@if(session('success'))
    <div class="admin-ai-flash admin-ai-flash--ok">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="admin-ai-flash admin-ai-flash--bad">{{ session('error') }}</div>
@endif

<div class="admin-stat-grid admin-stat-grid--compact">
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value">{{ $stats['pending'] }}</div>
        <div class="admin-stat-label">Bekleyen</div>
    </div>
    <div class="admin-stat-card admin-stat-card--coral">
        <div class="admin-stat-value">{{ $stats['today'] }}</div>
        <div class="admin-stat-label">Bugün</div>
    </div>
    <div class="admin-stat-card admin-stat-card--gold">
        <div class="admin-stat-value">{{ $stats['high'] }}</div>
        <div class="admin-stat-label">Yüksek Öncelik</div>
    </div>
    <div class="admin-stat-card admin-stat-card--indigo">
        <div class="admin-stat-value">{{ $stats['ai_source'] }}</div>
        <div class="admin-stat-label">AI (24s)</div>
    </div>
    <div class="admin-stat-card admin-stat-card--violet">
        <div class="admin-stat-value">{{ $stats['actioned_today'] ?? 0 }}</div>
        <div class="admin-stat-label">Bugün İşlenen</div>
    </div>
</div>

<div class="admin-ai-topgrid">
    <section class="admin-panel admin-panel--glass admin-ai-status">
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
            <li>Canlı mesaj / gönderi / hikaye / profil denetimi</li>
            <li>Regex + AI çift katman; düşük güven skoru elenir</li>
            <li>Tekrarlayan ihlalde otomatik uyarı veya ban</li>
            <li>Toplu işlem ve hızlı aksiyon menüsü</li>
        </ul>
        <div class="admin-quick-actions" style="margin-top:0.85rem">
            <form action="{{ route('admin.ai.publish-blog-faq') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm">Blog / SSS Yayınla</button>
            </form>
            <a href="{{ route('admin.auto-rules') }}" class="btn btn-outline btn-sm">Regex Kuralları</a>
            <a href="{{ route('admin.moderation') }}" class="btn btn-outline btn-sm">Denetim Kuyruğu</a>
        </div>
    </section>

    <section class="admin-panel admin-panel--glass">
        <h3 class="admin-panel-title">Otomatik Eskalasyon</h3>
        <form method="POST" action="{{ route('admin.ai.settings') }}" class="admin-ai-settings">
            @csrf
            <label class="admin-rule-toggle">
                <input type="checkbox" name="ai_auto_escalate" value="1" @checked($aiSettings['auto_escalate'])>
                <span>Otomatik eskalasyon açık</span>
            </label>
            <div class="admin-ai-settings-grid">
                <div class="form-group">
                    <label>Eşik (ihlal sayısı)</label>
                    <input type="number" name="ai_escalate_threshold" min="2" max="20" value="{{ $aiSettings['escalate_threshold'] }}">
                </div>
                <div class="form-group">
                    <label>Pencere (saat)</label>
                    <input type="number" name="ai_escalate_hours" min="6" max="168" value="{{ $aiSettings['escalate_hours'] }}">
                </div>
                <div class="form-group">
                    <label>Aksiyon</label>
                    <select name="ai_escalate_action">
                        <option value="ban" @selected($aiSettings['escalate_action'] === 'ban')>Otomatik ban</option>
                        <option value="warn" @selected($aiSettings['escalate_action'] === 'warn')>Uyarı gönder</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Min. AI güven</label>
                    <input type="number" step="0.01" min="0.1" max="0.99" name="ai_min_confidence" value="{{ $aiSettings['ai_min_confidence'] }}">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Ayarları Kaydet</button>
        </form>
    </section>
</div>

@if($reports->isNotEmpty())
<div class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Son AI Raporları</h3>
    @foreach($reports as $report)
        <div class="admin-ops-row">
            <div>
                <strong>{{ $report->title }}</strong>
                <span class="admin-ops-meta">{{ $report->summary }}</span>
            </div>
            <span class="admin-ops-meta">{{ optional($report->created_at)->format('d.m H:i') }}</span>
        </div>
    @endforeach
</div>
@endif

<div class="admin-panel admin-panel--glass">
    <form method="GET" action="{{ route('admin.ai') }}" class="admin-users-filter" role="search">
        <div class="admin-users-filter-field admin-users-filter-field--grow">
            <label for="ai-search">Ara</label>
            <input type="search" id="ai-search" name="search" value="{{ $filters['search'] }}" placeholder="kullanıcı, içerik, AI nedeni…">
        </div>
        <div class="admin-users-filter-field">
            <label>Durum</label>
            <select name="status" class="admin-users-filter-select">
                <option value="">Tümü</option>
                @foreach(['pending','reviewed','actioned','dismissed'] as $st)
                    <option value="{{ $st }}" @selected($filters['status'] === $st)>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-users-filter-field">
            <label>Öncelik</label>
            <select name="severity" class="admin-users-filter-select">
                <option value="">Tümü</option>
                @foreach(['high','medium','low'] as $sev)
                    <option value="{{ $sev }}" @selected($filters['severity'] === $sev)>{{ $sev }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-users-filter-field">
            <label>Kaynak</label>
            <select name="source" class="admin-users-filter-select">
                <option value="">Tümü</option>
                <option value="ai" @selected($filters['source'] === 'ai')>AI</option>
                <option value="regex" @selected($filters['source'] === 'regex')>Regex</option>
            </select>
        </div>
        <div class="admin-users-filter-field">
            <label>Kategori</label>
            <select name="category" class="admin-users-filter-select">
                <option value="">Tümü</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" @selected($filters['category'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-users-filter-field">
            <label>Tür</label>
            <select name="type" class="admin-users-filter-select">
                <option value="">Tümü</option>
                @foreach(['message','post','story','profile','report'] as $type)
                    <option value="{{ $type }}" @selected($filters['type'] === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-users-filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">Filtrele</button>
            <a href="{{ route('admin.ai') }}" class="btn btn-outline btn-sm">Temizle</a>
        </div>
    </form>
</div>

<div class="admin-panel admin-panel--glass">
    <div class="admin-panel-head">
        <h3 class="admin-panel-title">Tespit Edilen İhlaller</h3>
    </div>

    <div class="admin-users-bulk" id="adminAiBulk" hidden>
        <span class="admin-users-bulk-count"><strong id="adminAiSelectedCount">0</strong> kayıt seçildi</span>
        <button type="button" class="btn btn-outline btn-sm" data-ai-bulk="review">İncelendi</button>
        <button type="button" class="btn btn-outline btn-sm" data-ai-bulk="dismiss">Yok say</button>
        <button type="button" class="btn btn-outline btn-sm" data-ai-bulk="warn">Uyarı</button>
        <button type="button" class="btn btn-danger btn-sm" data-ai-bulk="ban">Banla</button>
    </div>

    <form method="POST" action="{{ route('admin.ai.flags.bulk') }}" id="adminAiBulkForm" hidden>
        @csrf
        <input type="hidden" name="bulk_action" id="adminAiBulkAction" value="">
        @foreach($filters as $key => $value)
            @if($value !== '' && $value !== null)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table admin-ai-table">
            <thead>
                <tr>
                    <th class="admin-table-check-col">
                        <input type="checkbox" id="adminAiSelectAll" aria-label="Tümünü seç">
                    </th>
                    <th>Kullanıcı</th>
                    <th>Tür</th>
                    <th>Kategori</th>
                    <th>Kaynak</th>
                    <th>Öncelik</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>Hızlı</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($flags as $flag)
                    <tr>
                        <td class="admin-table-check-col">
                            <input type="checkbox" class="admin-ai-select" value="{{ $flag->id }}">
                        </td>
                        <td>
                            @if($flag->user)
                                <strong>{{ $flag->user->username }}</strong>
                                <form method="POST" action="{{ route('admin.ai.users.scan', $flag->user) }}" class="admin-ai-inline-scan">
                                    @csrf
                                    <button type="submit" class="btn btn-outline btn-sm">Profil tara</button>
                                </form>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $flag->contentTypeLabel() }}</td>
                        <td>{{ $flag->categoryLabel() }}</td>
                        <td><span class="admin-badge {{ $flag->source === 'ai' ? 'admin-badge--warn' : 'admin-badge--danger' }}">{{ strtoupper($flag->source) }}</span></td>
                        <td>
                            <span class="admin-badge {{ $flag->severity === 'high' ? 'admin-badge--danger' : 'admin-badge--warn' }}">
                                {{ strtoupper($flag->severity) }}
                            </span>
                            @if($flag->ai_confidence)
                                <div class="admin-ops-meta">{{ number_format($flag->ai_confidence * 100, 0) }}%</div>
                            @endif
                        </td>
                        <td>{{ $flag->status }}</td>
                        <td>{{ $flag->created_at?->format('d.m.Y H:i') }}</td>
                        <td class="admin-ai-quick">
                            <form method="POST" action="{{ route('admin.ai.flags.quick', $flag) }}">@csrf<input type="hidden" name="action" value="warn"><button class="btn btn-outline btn-sm" type="submit">Uyarı</button></form>
                            <form method="POST" action="{{ route('admin.ai.flags.quick', $flag) }}">@csrf<input type="hidden" name="action" value="hide"><button class="btn btn-outline btn-sm" type="submit">Gizle</button></form>
                            <form method="POST" action="{{ route('admin.ai.flags.quick', $flag) }}" onsubmit="return confirm('Banlansın mı?')">@csrf<input type="hidden" name="action" value="ban"><button class="btn btn-danger btn-sm" type="submit">Ban</button></form>
                            <form method="POST" action="{{ route('admin.ai.flags.quick', $flag) }}">@csrf<input type="hidden" name="action" value="dismiss"><button class="btn btn-outline btn-sm" type="submit">Yok say</button></form>
                        </td>
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
                                        <label class="admin-checkbox"><input type="checkbox" name="warn_user" value="1"> Uyarı gönder</label>
                                        <label class="admin-checkbox"><input type="checkbox" name="hide_content" value="1"> İçeriği gizle</label>
                                        <label class="admin-checkbox"><input type="checkbox" name="ban_user" value="1"> Kullanıcıyı banla</label>
                                        <button type="submit" class="btn btn-primary btn-sm">Kaydet</button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10">Filtreye uyan ihlal yok. Tarama başlatabilirsiniz.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $flags->links() }}
</div>

<script>
(function () {
    const selectAll = document.getElementById('adminAiSelectAll');
    const bulkBar = document.getElementById('adminAiBulk');
    const countEl = document.getElementById('adminAiSelectedCount');
    const bulkForm = document.getElementById('adminAiBulkForm');
    const actionInput = document.getElementById('adminAiBulkAction');

    function boxes() { return Array.from(document.querySelectorAll('.admin-ai-select')); }
    function selected() { return boxes().filter(function (b) { return b.checked; }); }

    function refresh() {
        const sel = selected();
        countEl.textContent = String(sel.length);
        bulkBar.hidden = sel.length === 0;
        if (selectAll) {
            const all = boxes();
            selectAll.indeterminate = sel.length > 0 && sel.length < all.length;
            selectAll.checked = all.length > 0 && sel.length === all.length;
        }
    }

    boxes().forEach(function (b) { b.addEventListener('change', refresh); });
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            boxes().forEach(function (b) { b.checked = selectAll.checked; });
            refresh();
        });
    }

    document.querySelectorAll('[data-ai-bulk]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const action = btn.getAttribute('data-ai-bulk');
            const sel = selected();
            if (!sel.length) return;
            if (action === 'ban' && !confirm(sel.length + ' kullanıcı banlansın mı?')) return;
            actionInput.value = action;
            bulkForm.querySelectorAll('input[name="flag_ids[]"]').forEach(function (el) { el.remove(); });
            sel.forEach(function (cb) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'flag_ids[]';
                input.value = cb.value;
                bulkForm.appendChild(input);
            });
            bulkForm.submit();
        });
    });
})();
</script>
@endsection
