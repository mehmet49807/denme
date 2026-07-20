@extends('layouts.admin')

@section('title', 'Denetim Kayıtları')
@section('lead', 'Admin işlemlerinin iz kaydı.')

@section('content')
<div class="admin-panel admin-panel--glass">
    <form method="GET" action="{{ route('admin.audit') }}" class="admin-users-filter" role="search">
        <div class="admin-users-filter-field">
            <label for="audit-action">Aksiyon</label>
            <input type="search" id="audit-action" name="action" value="{{ request('action') }}" placeholder="user.ban, premium…">
        </div>
        <div class="admin-users-filter-field admin-users-filter-field--grow">
            <label for="audit-search">Özet</label>
            <input type="search" id="audit-search" name="search" value="{{ request('search') }}" placeholder="Ara…">
        </div>
        <div class="admin-users-filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">Filtrele</button>
        </div>
    </form>
</div>

<div class="admin-panel admin-panel--glass">
    @if(! $ready)
        <p class="admin-ops-empty">Denetim tablosu henüz hazır değil. İlk işlemden sonra oluşacak.</p>
    @else
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tarih</th>
                        <th>Admin</th>
                        <th>Aksiyon</th>
                        <th>Özet</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ optional($log->created_at)->format('d.m.Y H:i') }}</td>
                            <td>{{ $log->admin->username ?? '—' }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td>{{ $log->summary }}</td>
                            <td>{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Kayıt yok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($logs, 'links'))
            {{ $logs->links() }}
        @endif
    @endif
</div>
@endsection
