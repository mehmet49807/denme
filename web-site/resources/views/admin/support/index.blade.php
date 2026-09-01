@extends('layouts.admin')

@section('title', 'Destek Talepleri — Gönül Köprüsü Admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Filtrele</h2>
    </div>
    <form action="{{ route('admin.support.index') }}" method="GET">
        <div style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tümü</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Açık</option>
                    <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Cevaplandı</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Kapalı</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrele</button>
            <a href="{{ route('admin.support.index') }}" class="btn btn-secondary">Temizle</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Destek Talepleri</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kullanıcı</th>
                    <th>İsim</th>
                    <th>Email</th>
                    <th>Konu</th>
                    <th>Mesaj</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>Cevapla</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td>#{{ $ticket->id }}</td>
                        <td>
                            @if($ticket->user)
                                <a href="{{ route('admin.users.show', $ticket->user_id) }}" style="color: var(--primary); font-weight: 500; text-decoration: none;">
                                    {{ $ticket->user->username }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $ticket->name ?? '-' }}</td>
                        <td>{{ $ticket->email ?? '-' }}</td>
                        <td>{{ $ticket->subject ?? '-' }}</td>
                        <td>{{ Str::limit($ticket->message ?? '-', 50) }}</td>
                        <td>
                            @if($ticket->status === 'open')
                                <span class="badge badge-warning">Açık</span>
                            @elseif($ticket->status === 'replied')
                                <span class="badge badge-info">Cevaplandı</span>
                            @elseif($ticket->status === 'closed')
                                <span class="badge badge-secondary">Kapalı</span>
                            @else
                                <span class="badge badge-secondary">{{ $ticket->status ?? '-' }}</span>
                            @endif
                        </td>
                        <td>{{ $ticket->created_at ? $ticket->created_at->format('d.m.Y H:i') : '-' }}</td>
                        <td>
                            <details style="position: relative;">
                                <summary class="btn btn-primary btn-sm" style="cursor: pointer; list-style: none;">
                                    ✏️ Cevapla
                                </summary>
                                <div style="position: absolute; right: 0; top: 100%; z-index: 100; background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 350px; margin-top: 4px;">
                                    <form action="{{ route('admin.support.update', $ticket) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <label class="form-label" style="font-size: 13px;">Durum</label>
                                            <select name="status" class="form-select">
                                                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Açık</option>
                                                <option value="replied" {{ $ticket->status === 'replied' ? 'selected' : '' }}>Cevaplandı</option>
                                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Kapalı</option>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <label class="form-label" style="font-size: 13px;">Cevap</label>
                                            <textarea name="admin_reply" rows="4" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;" placeholder="Kullanıcıya cevabınız...">{{ $ticket->admin_reply ?? '' }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary" style="width: 100%;">Kaydet</button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 24px; color: var(--text-body);">
                            Destek talebi bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $tickets->links() }}
</div>
@endsection
