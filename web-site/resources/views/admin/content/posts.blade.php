@extends('layouts.admin')

@section('title', 'İçerik Yönetimi — Gönderiler — Gönül Köprüsü')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Gönderi Filtrele</h2>
    </div>
    <form action="{{ route('admin.content.posts') }}" method="GET">
        <div class="filter-grid" style="grid-template-columns: 1fr auto;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tümü</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Pasif / Gizli</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary">Filtrele</button>
                <a href="{{ route('admin.content.posts') }}" class="btn btn-secondary">Temizle</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Gönderi Listesi</h2>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Görsel</th>
                    <th>Kullanıcı</th>
                    <th>Açıklama</th>
                    <th>Beğeni</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>#{{ $post->id }}</td>
                        <td>
                            <img src="{{ $post->image_url ?? asset('images/placeholder.jpg') }}" alt="Görsel" style="width: 50px; height: 50px; border-radius: 6px; object-fit: cover; border: 1px solid var(--border-color);">
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $post->user_id ?? 0) }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                {{ $post->user->username ?? 'Kullanıcı #' . ($post->user_id ?? '') }}
                            </a>
                        </td>
                        <td>{{ Str::limit($post->caption ?? '-', 45) }}</td>
                        <td><span class="badge badge-info">❤️ {{ $post->likes_count ?? 0 }}</span></td>
                        <td>
                            @if($post->is_active ?? true)
                                <span class="badge badge-success">Aktif</span>
                            @else
                                <span class="badge badge-danger">Pasif</span>
                            @endif
                        </td>
                        <td>{{ isset($post->created_at) ? \Carbon\Carbon::parse($post->created_at)->format('d.m.Y H:i') : '-' }}</td>
                        <td>
                            <form action="{{ route('admin.content.posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Bu gönderiyi silmek istediğinizden emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">🗑️ Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-body); padding: 24px;">
                            Gönderi bulunamadı.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($posts, 'links'))
        <div class="pagination-wrapper">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
