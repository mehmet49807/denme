@extends('layouts.admin')

@section('title', 'Duyuru Gönderimi — Gönül Köprüsü')

@section('content')
<!-- Form to create a new broadcast -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Yeni Toplu Duyuru Gönder</h2>
    </div>
    <form action="{{ route('admin.broadcasts.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Duyuru Başlığı</label>
            <input type="text" name="title" class="form-control" placeholder="Örn: Yeni Güncelleme ve Kampanya!" required>
        </div>

        <div class="form-group">
            <label class="form-label">Hedef Cinsiyet / Kitle</label>
            <select name="target_gender" class="form-select">
                <option value="all">Tüm Kullanıcılar (Erkek & Kadın)</option>
                <option value="male">Sadece Erkek Üyeler</option>
                <option value="female">Sadece Kadın Üyeler</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Duyuru Mesajı</label>
            <textarea name="message" class="form-control" rows="4" placeholder="Kullanıcılara iletilecek bildirim/mesaj metnini yazın..." required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">📢 Duyuruyu Gönder</button>
    </form>
</div>

<!-- Table of past broadcasts -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Geçmiş Duyurular</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Mesaj</th>
                    <th>Hedef Kitle</th>
                    <th>Gönderilen Kişi</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                @forelse($broadcasts as $broadcast)
                    <tr>
                        <td style="font-weight: 600; color: var(--text-heading);">{{ $broadcast->title }}</td>
                        <td>{{ Str::limit($broadcast->message, 50) }}</td>
                        <td>
                            @if(($broadcast->target_gender ?? 'all') === 'all')
                                <span class="badge badge-info">Tümü</span>
                            @elseif(($broadcast->target_gender ?? '') === 'male')
                                <span class="badge badge-secondary">Erkekler</span>
                            @else
                                <span class="badge badge-warning">Kadınlar</span>
                            @endif
                        </td>
                        <td>{{ number_format($broadcast->sent_count ?? 0) }} kişi</td>
                        <td>{{ isset($broadcast->created_at) ? \Carbon\Carbon::parse($broadcast->created_at)->format('d.m.Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-body); padding: 24px;">
                            Geçmişte gönderilmiş duyuru bulunmuyor.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($broadcasts, 'links'))
        <div class="pagination-wrapper">
            {{ $broadcasts->links() }}
        </div>
    @endif
</div>
@endsection
