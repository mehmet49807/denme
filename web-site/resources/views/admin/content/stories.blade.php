@extends('layouts.admin')

@section('title', 'İçerik Yönetimi — Hikayeler — Gönül Köprüsü')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Aktif Hikayeler Listesi</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Medya</th>
                    <th>Kullanıcı</th>
                    <th>Tip</th>
                    <th>Hedef Kitle</th>
                    <th>Bitiş Tarihi</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stories as $story)
                    <tr>
                        <td>#{{ $story->id }}</td>
                        <td>
                            <img src="{{ $story->media_url ?? asset('images/placeholder.jpg') }}" alt="Hikaye Medyası" style="width: 48px; height: 64px; border-radius: 6px; object-fit: cover; border: 1px solid var(--border-color);">
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $story->user_id ?? 0) }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                {{ $story->user->username ?? 'Kullanıcı #' . ($story->user_id ?? '') }}
                            </a>
                        </td>
                        <td><span class="badge badge-info">{{ ucfirst($story->media_type ?? 'Görsel') }}</span></td>
                        <td><span class="badge badge-secondary">{{ ucfirst($story->audience ?? 'Herkes') }}</span></td>
                        <td>{{ isset($story->expires_at) ? \Carbon\Carbon::parse($story->expires_at)->format('d.m.Y H:i') : '-' }}</td>
                        <td>
                            <form action="{{ route('admin.content.stories.destroy', $story->id) }}" method="POST" onsubmit="return confirm('Bu hikayeyi silmek istediğinizden emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">🗑️ Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-body); padding: 24px;">
                            Aktif hikaye bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($stories, 'links'))
        <div class="pagination-wrapper">
            {{ $stories->links() }}
        </div>
    @endif
</div>
@endsection
