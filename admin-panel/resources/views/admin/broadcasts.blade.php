@extends('layouts.admin')

@section('title', 'Duyuru Sistemi')
@section('lead', 'Tüm kullanıcılara veya hedef gruplara duyuru gönderin / zamanlayın.')

@section('content')
@if(!empty($fcmConfigured))
    <p class="admin-ops-meta" style="margin-bottom:1rem">FCM hazır · {{ $registeredDevices ?? 0 }} kayıtlı cihaz</p>
@else
    <p class="admin-ops-meta" style="margin-bottom:1rem">FCM yapılandırması eksik — duyurular uygulama içi olarak kaydedilir.</p>
@endif

<div class="admin-panel admin-panel--glass form-card">
    <h3 class="admin-panel-title">Yeni Duyuru</h3>
    <form method="POST" action="{{ route('admin.broadcasts.send') }}">
        @csrf
        <div class="form-group">
            <label>Başlık</label>
            <input type="text" name="title" required value="{{ old('title') }}">
        </div>
        <div class="form-group">
            <label>Mesaj</label>
            <textarea name="message_text" rows="4" required>{{ old('message_text') }}</textarea>
        </div>
        <div class="form-group">
            <label>Hedef</label>
            <select name="target_gender">
                <option value="all">Tüm Kullanıcılar</option>
                <option value="male">Yalnızca Erkekler</option>
                <option value="female">Yalnızca Kadınlar</option>
            </select>
        </div>
        <div class="form-group">
            <label>Zamanla (opsiyonel)</label>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}">
            <small class="admin-ops-meta">Boş bırakırsanız hemen gönderilir. Zamanlanan duyurular bu sayfa açılınca tetiklenir.</small>
        </div>
        <button type="submit" class="btn btn-primary">Duyuru Gönder / Zamanla</button>
    </form>
</div>

<div class="admin-panel admin-panel--glass">
<div class="admin-table-wrap"><table class="admin-table">
    <thead>
        <tr>
            <th>Başlık</th>
            <th>Hedef</th>
            <th>Durum</th>
            <th>Gönderilen</th>
            <th>Zaman</th>
            <th>Tarih</th>
        </tr>
    </thead>
    <tbody>
        @foreach($broadcasts as $b)
        <tr>
            <td>{{ $b->title }}</td>
            <td>{{ $b->target_gender }}</td>
            <td>{{ $b->status ?? 'sent' }}</td>
            <td>{{ $b->sent_count }} kullanıcı</td>
            <td>{{ $b->scheduled_at ? $b->scheduled_at->format('d.m.Y H:i') : '—' }}</td>
            <td>{{ $b->created_at }}</td>
        </tr>
        @endforeach
    </tbody>
</table></div>

{{ $broadcasts->links() }}
</div>
@endsection
