@extends('layouts.admin')

@section('title', 'Şikayetler — Gönül Köprüsü Admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Şikayet Filtreleme</h2>
    </div>
    <form action="{{ route('admin.reports.index') }}" method="GET">
        <div class="filter-grid" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tümü</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Beklemede</option>
                    <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>İnceleniyor</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Çözüldü</option>
                    <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Reddedildi</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrele</button>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">Temizle</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Şikayet Listesi</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Şikayet Eden</th>
                    <th>Şikayet Edilen</th>
                    <th>Sebep</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td>#{{ $report->id }}</td>
                        <td>
                            @if($report->reporter)
                                <a href="{{ route('admin.users.show', $report->reporter_id) }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">
                                    {{ $report->reporter->username }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if($report->reported)
                                <a href="{{ route('admin.users.show', $report->reported_id) }}" style="color: #EF4444; text-decoration: none; font-weight: 500;">
                                    {{ $report->reported->username }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ Str::limit($report->reason ?? '-', 40) }}</td>
                        <td>
                            @if($report->status === 'pending')
                                <span class="badge badge-warning">Beklemede</span>
                            @elseif($report->status === 'investigating')
                                <span class="badge badge-info">İnceleniyor</span>
                            @elseif($report->status === 'resolved')
                                <span class="badge badge-success">Çözüldü</span>
                            @elseif($report->status === 'dismissed')
                                <span class="badge badge-secondary">Reddedildi</span>
                            @else
                                <span class="badge badge-secondary">{{ $report->status ?? '-' }}</span>
                            @endif
                        </td>
                        <td>{{ $report->created_at ? $report->created_at->format('d.m.Y H:i') : '-' }}</td>
                        <td>
                            <details style="position: relative;">
                                <summary class="btn btn-primary btn-sm" style="cursor: pointer; list-style: none;">
                                    ⚙️ Yönet
                                </summary>
                                <div style="position: absolute; right: 0; top: 100%; z-index: 100; background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 320px; margin-top: 4px;">
                                    <form action="{{ route('admin.reports.update', $report) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <label class="form-label" style="font-size: 13px;">Durum</label>
                                            <select name="status" class="form-select">
                                                <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Beklemede</option>
                                                <option value="investigating" {{ $report->status === 'investigating' ? 'selected' : '' }}>İnceleniyor</option>
                                                <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Çözüldü</option>
                                                <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Reddedildi</option>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <label class="form-label" style="font-size: 13px;">Notlar</label>
                                            <textarea name="admin_notes" rows="3" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px;">{{ $report->admin_notes ?? '' }}</textarea>
                                        </div>
                                        <div class="form-group" style="margin-bottom: 12px;">
                                            <label style="display: flex; align-items: center; gap: 6px; font-size: 13px;">
                                                <input type="checkbox" name="ban_user" value="1">
                                                Şu an şikayet edilen kullanıcıyı banla
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-primary" style="width: 100%;">Kaydet</button>
                                    </form>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-body);">
                            Şikayet bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $reports->links() }}
</div>
@endsection
