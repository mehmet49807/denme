@extends('layouts.admin')

@section('title', '7/24 Destek')
@section('lead', 'Destek talepleri, iletişim kanalları ve yanıtlar.')

@section('content')
@if(session('success'))
<div class="admin-flash admin-flash--success">{{ session('success') }}</div>
@endif

@if(!$tableReady)
<div class="admin-flash admin-flash--warn">
    Destek tablosu kurulamadı. <code>/setup/support-tickets?key=gk-cpanel-setup-2026</code> çalıştırın veya DB izinlerini kontrol edin.
</div>
@endif

<div class="admin-email-grid">
    <div class="admin-panel admin-panel--glass admin-email-panel">
        <h3 class="admin-panel-title admin-panel-title--accent">İletişim ayarları</h3>
        <form method="POST" action="{{ route('admin.support.settings') }}" class="admin-email-form">
            @csrf
            <div class="form-group">
                <label for="support_email">Destek e-postası</label>
                <input type="email" name="support_email" id="support_email" value="{{ old('support_email', $supportEmail) }}" required>
            </div>
            <div class="form-group">
                <label for="support_phone">Telefon (isteğe bağlı)</label>
                <input type="text" name="support_phone" id="support_phone" value="{{ old('support_phone', $supportPhone) }}" maxlength="40">
            </div>
            <div class="form-group">
                <label for="support_whatsapp">WhatsApp (isteğe bağlı)</label>
                <input type="text" name="support_whatsapp" id="support_whatsapp" value="{{ old('support_whatsapp', $supportWhatsapp) }}" maxlength="40" placeholder="905xxxxxxxxx">
            </div>
            <div class="form-group">
                <label for="support_hours">Çalışma saati metni</label>
                <input type="text" name="support_hours" id="support_hours" value="{{ old('support_hours', $supportHours) }}" maxlength="80">
            </div>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </form>
        <p class="admin-field-hint" style="margin-top:1rem;">
            Site destek sayfası: <a href="{{ config('app.frontend_url', 'https://www.gonulkoprusu.com') }}/destek" target="_blank" rel="noopener">/destek</a>
        </p>
    </div>

    <div class="admin-panel admin-panel--glass">
        <h3 class="admin-panel-title admin-panel-title--accent">Bekleyen talepler ({{ $pendingCount }})</h3>
        @if(!$tableReady || ($tickets instanceof \Illuminate\Support\Collection && $tickets->isEmpty()))
            <p class="admin-empty">Destek talebi yok.</p>
        @else
            @foreach($tickets as $ticket)
                <article class="admin-support-ticket">
                    <header>
                        <strong>{{ $ticket->subject }}</strong>
                        <span class="admin-badge admin-badge--{{ $ticket->status }}">{{ $ticket->status }}</span>
                    </header>
                    <p><small>{{ $ticket->name }} · {{ $ticket->email }} · {{ $ticket->created_at?->format('d.m.Y H:i') }}</small></p>
                    <p>{{ $ticket->message }}</p>
                    @if($ticket->admin_reply)
                        <blockquote class="admin-support-reply"><strong>Yanıt:</strong> {{ $ticket->admin_reply }}</blockquote>
                    @endif
                    <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="admin-support-reply-form">
                        @csrf
                        <textarea name="admin_reply" rows="3" placeholder="Yanıt yaz..." required>{{ old('admin_reply', $ticket->admin_reply) }}</textarea>
                        <select name="status">
                            <option value="pending" @selected($ticket->status === 'pending')>Bekliyor</option>
                            <option value="answered" @selected($ticket->status === 'answered')>Yanıtlandı</option>
                            <option value="closed" @selected($ticket->status === 'closed')>Kapatıldı</option>
                        </select>
                        <button type="submit" class="btn btn-outline btn-sm">Kaydet</button>
                    </form>
                </article>
            @endforeach
            @if(method_exists($tickets, 'links'))
                {{ $tickets->links() }}
            @endif
        @endif
    </div>
</div>
@endsection
