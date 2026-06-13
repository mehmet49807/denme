@extends('layouts.admin')
@section('title', 'Duyuru Sistemi')

@section('content')
<div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
    <div class="gk-card">
        <h3 style="margin-top:0;">Resmi Sistem Duyurusu Gönder</h3>
        <form method="POST" action="{{ route('admin.broadcast.send') }}">
            @csrf
            <div class="gk-field">
                <label>Hedef Kitle</label>
                <select class="gk-select" name="audience">
                    <option value="all">Tüm Kullanıcılar</option>
                    <option value="male">Erkekler</option>
                    <option value="female">Kadınlar</option>
                    <option value="premium">Premium Üyeler</option>
                </select>
            </div>
            <div class="gk-field">
                <label>Mesaj</label>
                <textarea class="gk-input" name="message_text" rows="5" placeholder="Resmi duyuru metni..."></textarea>
            </div>
            <button class="gk-btn">Duyuruyu Gönder</button>
        </form>
    </div>

    <div class="gk-card">
        <h3 style="margin-top:0;">Son Duyurular</h3>
        @forelse ($recent as $b)
            <div style="padding:12px 0; border-bottom:1px solid var(--gk-beige);">
                <div>{{ \Illuminate\Support\Str::limit($b->message_text, 120) }}</div>
                <small style="color:var(--gk-text-muted);">{{ $b->created_at?->format('d.m.Y H:i') }}</small>
            </div>
        @empty
            <p style="color:var(--gk-text-muted);">Henüz duyuru gönderilmedi.</p>
        @endforelse
    </div>
</div>
@endsection
