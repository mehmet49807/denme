@extends('layouts.admin')

@section('title', 'E-posta Gönderimi')
@section('lead', 'Hazır şablonlarla kullanıcılara toplu veya tekil e-posta gönderin.')

@section('content')
@if(!$tableReady)
<div class="admin-flash admin-flash--warn">
    E-posta log tablosu henüz kurulmadı. Gönderim çalışır; geçmiş kayıtları için migration çalıştırın.
</div>
@endif

<div class="admin-email-page">
<div class="admin-email-hero" aria-hidden="true">
    <span class="admin-email-hero-icon">✉</span>
    <div class="admin-email-hero-copy">
        <strong>Toplu veya tekil gönderim</strong>
        <span>{{ $userCount }} aktif kullanıcıya hazır şablonlarla ulaşın</span>
    </div>
</div>

<div class="admin-email-grid">
    <div class="admin-panel admin-panel--glass admin-email-panel admin-email-panel--form">
        <h3 class="admin-panel-title admin-panel-title--accent">E-posta Gönder</h3>
        <form method="POST" action="{{ route('admin.emails.send') }}" id="adminEmailForm" class="admin-email-form">
            @csrf

            <div class="form-group">
                <label for="template_key">Şablon</label>
                <select name="template_key" id="template_key" required>
                    @foreach($templates as $template)
                        <option value="{{ $template['key'] }}" @selected(old('template_key') === $template['key'])>
                            {{ $template['label'] }}
                        </option>
                    @endforeach
                </select>
                <p class="admin-field-hint" id="templateDescription"></p>
            </div>

            <div class="form-group">
                <label for="target">Alıcılar</label>
                <select name="target" id="target" required>
                    <option value="all" @selected(old('target', 'all') === 'all')>Tüm aktif kullanıcılar ({{ $userCount }})</option>
                    <option value="male" @selected(old('target') === 'male')>Yalnızca erkekler</option>
                    <option value="female" @selected(old('target') === 'female')>Yalnızca kadınlar</option>
                    <option value="single" @selected(old('target') === 'single')>Tek e-posta adresi</option>
                </select>
            </div>

            <div class="form-group" id="singleEmailWrap" hidden>
                <label for="single_email">E-posta adresi</label>
                <input type="email" name="single_email" id="single_email" value="{{ old('single_email') }}" placeholder="kullanici@email.com">
                @error('single_email') <small class="form-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="subject">Konu</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required maxlength="255">
                @error('subject') <small class="form-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="body">İçerik (HTML destekli)</label>
                <textarea name="body" id="body" rows="12" required>{{ old('body') }}</textarea>
                @error('body') <small class="form-error">{{ $message }}</small> @enderror
                <p class="admin-field-hint">Değişkenler: {first_name}, {last_name}, {username}, {email}, {city}, {feed_url}, {premium_url}</p>
            </div>

            <div class="admin-email-actions">
                <button type="button" class="btn btn-outline" id="previewTemplateBtn">Önizleme</button>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Seçili alıcılara e-posta gönderilsin mi?');">Gönder</button>
            </div>
        </form>
    </div>

    <div class="admin-panel admin-panel--glass admin-email-panel admin-email-panel--templates">
        <h3 class="admin-panel-title admin-panel-title--accent">Hazır Şablonlar</h3>
        <div class="admin-template-list">
            @foreach($templates as $template)
                @if($template['key'] !== 'custom')
                <button type="button" class="admin-template-card" data-template-key="{{ $template['key'] }}">
                    <strong>{{ $template['label'] }}</strong>
                    <span>{{ $template['description'] }}</span>
                </button>
                @endif
            @endforeach
        </div>
    </div>
</div>

<div class="admin-panel admin-panel--glass admin-email-panel admin-email-panel--logs">
    <div class="admin-panel-title-row">
        <h3 class="admin-panel-title admin-panel-title--accent">
            Son Gönderimler
            @if($tableReady && $logsCount > 0)
                <small class="admin-panel-title-count">({{ $logsCount }})</small>
            @endif
        </h3>
        @if($tableReady && $logsCount > 0)
        <form method="POST" action="{{ route('admin.emails.clear') }}" onsubmit="return confirm('Tüm e-posta gönderim geçmişi silinsin mi? Bu işlem geri alınamaz.');">
            @csrf
            <button type="submit" class="btn btn-outline btn-outline--danger">Geçmişi Temizle</button>
        </form>
        @endif
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Tarih</th>
                    <th>Alıcı</th>
                    <th>Konu</th>
                    <th>Şablon</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at?->format('d.m.Y H:i') ?? '—' }}</td>
                    <td>{{ $log->recipient_email }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($log->subject, 48) }}</td>
                    <td>{{ $log->template_key ?? '—' }}</td>
                    <td>
                        @if($log->status === 'sent')
                            <span class="badge badge-resolved">Gönderildi</span>
                        @else
                            <span class="badge badge-banned" title="{{ $log->error_message }}">Başarısız</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="admin-table-empty">Henüz e-posta kaydı yok.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

<script>
window.adminEmailTemplates = @json($templates);

(function () {
    const templates = window.adminEmailTemplates || [];
    const select = document.getElementById('template_key');
    const subject = document.getElementById('subject');
    const body = document.getElementById('body');
    const desc = document.getElementById('templateDescription');
    const target = document.getElementById('target');
    const singleWrap = document.getElementById('singleEmailWrap');

    function findTemplate(key) {
        return templates.find(function (t) { return t.key === key; });
    }

    function applyTemplate(key) {
        const tpl = findTemplate(key);
        if (!tpl) return;
        desc.textContent = tpl.description || '';
        if (key !== 'custom' || !subject.value) subject.value = tpl.subject || '';
        if (key !== 'custom' || !body.value) body.value = tpl.body || '';
    }

    function toggleSingleEmail() {
        singleWrap.hidden = target.value !== 'single';
    }

    select.addEventListener('change', function () { applyTemplate(select.value); });
    target.addEventListener('change', toggleSingleEmail);

    document.querySelectorAll('.admin-template-card').forEach(function (btn) {
        btn.addEventListener('click', function () {
            select.value = btn.dataset.templateKey;
            applyTemplate(select.value);
        });
    });

    document.getElementById('previewTemplateBtn')?.addEventListener('click', function () {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = @json(route('admin.emails.preview'));
        form.target = '_blank';
        form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
            '<input type="hidden" name="template_key" value="' + select.value + '">';
        document.body.appendChild(form);
        form.submit();
        form.remove();
    });

    applyTemplate(select.value);
    toggleSingleEmail();
})();
</script>
@endsection
