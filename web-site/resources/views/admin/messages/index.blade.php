@extends('layouts.admin')

@section('title', 'Mesajlaşma İstatistikleri — Gönül Köprüsü')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Kullanıcı Mesaj İstatistikleri</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kullanıcı</th>
                    <th>Gönderilen Mesaj</th>
                    <th>Alınan Mesaj</th>
                    <th>Son Mesaj Tarihi</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $userItem)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.show', $userItem->id) }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                {{ $userItem->username ?? $userItem->name ?? 'Kullanıcı #' . $userItem->id }}
                            </a>
                        </td>
                        <td><span class="badge badge-info">{{ number_format($userItem->sent_messages_count ?? $userItem->sent_count ?? 0) }}</span></td>
                        <td><span class="badge badge-secondary">{{ number_format($userItem->received_messages_count ?? $userItem->received_count ?? 0) }}</span></td>
                        <td>{{ isset($userItem->last_message_at) ? \Carbon\Carbon::parse($userItem->last_message_at)->format('d.m.Y H:i') : '-' }}</td>
                        <td>
                            <a href="{{ route('admin.messages.show', $userItem->id) }}" class="btn btn-primary btn-sm">
                                💬 Geçmişi Gör
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-body); padding: 24px;">
                            Mesaj kaydı bulunmuyor.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($users, 'links'))
        <div class="pagination-wrapper">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
