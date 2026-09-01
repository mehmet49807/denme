@extends('layouts.admin')

@section('title', 'Mesaj Geçmişi — Gönül Köprüsü')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('admin.messages.index') }}" class="btn btn-secondary btn-sm">
        ← Mesaj Listesine Dön
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Kullanıcı Mesaj Geçmişi</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Gönderen</th>
                    <th>Alıcı</th>
                    <th>Mesaj</th>
                    <th>Tarih</th>
                    <th>Okunma Durumu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.show', $msg->sender_id ?? 0) }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                {{ $msg->sender->username ?? 'Kullanıcı #' . ($msg->sender_id ?? '') }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $msg->receiver_id ?? 0) }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                {{ $msg->receiver->username ?? 'Kullanıcı #' . ($msg->receiver_id ?? '') }}
                            </a>
                        </td>
                        <td style="color: var(--text-heading); font-size: 0.9rem;">
                            {{ $msg->body ?? $msg->message ?? '-' }}
                        </td>
                        <td>{{ isset($msg->created_at) ? \Carbon\Carbon::parse($msg->created_at)->format('d.m.Y H:i') : '-' }}</td>
                        <td>
                            @if($msg->is_read || $msg->read_at)
                                <span class="badge badge-success">Okundu</span>
                            @else
                                <span class="badge badge-warning">Okunmadı</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-body); padding: 24px;">
                            Geçmiş mesaj bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($messages, 'links'))
        <div class="pagination-wrapper">
            {{ $messages->links() }}
        </div>
    @endif
</div>
@endsection
