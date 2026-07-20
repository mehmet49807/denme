@extends('layouts.admin')

@section('title', 'Personel Rolleri')
@section('lead', 'Kullanıcıyı bul, tek dokunuşla rol ver.')

@section('content')
@if($errors->any())
    <div class="admin-ai-flash admin-ai-flash--bad">
        {{ $errors->first() }}
    </div>
@endif

@if(session('success'))
    <div class="admin-ai-flash admin-ai-flash--ok">{{ session('success') }}</div>
@endif

@if(empty($canManage))
    <div class="admin-ai-flash admin-ai-flash--bad">
        Rol değiştirmek için hesabınızın <strong>Yönetici</strong> olması gerekir.
    </div>
@endif

<section class="admin-panel admin-panel--glass admin-staff-simple">
    <h3 class="admin-panel-title">1) Kullanıcı bul</h3>
    <form method="GET" action="{{ route('admin.staff') }}" class="admin-staff-search">
        <input
            type="search"
            name="q"
            value="{{ $search }}"
            placeholder="Kullanıcı adı veya e-posta yaz…"
            autocomplete="off"
            autofocus
        >
        <button type="submit" class="btn btn-primary">Ara</button>
    </form>

    @if($search !== '')
        <div class="admin-staff-results">
            @forelse($searchResults as $user)
                <article class="admin-staff-card">
                    <div class="admin-staff-card__who">
                        <strong>{{ $user->username }}</strong>
                        <span>{{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->email }}</span>
                        <em>Şu an: {{ $roleLabels[$user->role] ?? $user->role }}</em>
                    </div>
                    @if(!empty($canManage))
                        <div class="admin-staff-actions">
                            <form method="POST" action="{{ route('admin.staff.promote') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <input type="hidden" name="role" value="moderator">
                                <button type="submit" class="btn btn-primary admin-staff-btn {{ $user->role === 'moderator' ? 'is-current' : '' }}">Moderatör</button>
                            </form>
                            <form method="POST" action="{{ route('admin.staff.promote') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <input type="hidden" name="role" value="support">
                                <button type="submit" class="btn btn-outline admin-staff-btn {{ $user->role === 'support' ? 'is-current' : '' }}">Destek</button>
                            </form>
                            <form method="POST" action="{{ route('admin.staff.promote') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <input type="hidden" name="role" value="admin">
                                <button type="submit" class="btn btn-outline admin-staff-btn {{ $user->role === 'admin' ? 'is-current' : '' }}">Yönetici</button>
                            </form>
                            @if($user->role !== 'user')
                                <form method="POST" action="{{ route('admin.staff.promote') }}" onsubmit="return confirm('Personel yetkisi kaldırılsın mı?');">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                    <input type="hidden" name="role" value="user">
                                    <button type="submit" class="btn btn-danger admin-staff-btn">Kaldır</button>
                                </form>
                            @endif
                        </div>
                    @endif
                </article>
            @empty
                <p class="admin-ops-empty">“{{ $search }}” için kullanıcı bulunamadı.</p>
            @endforelse
        </div>
    @else
        <p class="admin-ops-meta" style="margin-top:.85rem">Örnek: abinin kullanıcı adını yazıp Ara’ya bas.</p>
    @endif
</section>

<section class="admin-panel admin-panel--glass admin-staff-simple">
    <h3 class="admin-panel-title">2) Mevcut personel</h3>
    @forelse($staff as $member)
        <article class="admin-staff-card">
            <div class="admin-staff-card__who">
                <strong>{{ $member->username }}</strong>
                <span>{{ $member->email }}</span>
                <em>{{ $roleLabels[$member->role] ?? $member->role }}</em>
            </div>
            @if(!empty($canManage))
                <div class="admin-staff-actions">
                    <form method="POST" action="{{ route('admin.staff.update', $member) }}">
                        @csrf
                        <input type="hidden" name="role" value="moderator">
                        <button type="submit" class="btn btn-primary admin-staff-btn {{ $member->role === 'moderator' ? 'is-current' : '' }}">Moderatör</button>
                    </form>
                    <form method="POST" action="{{ route('admin.staff.update', $member) }}">
                        @csrf
                        <input type="hidden" name="role" value="support">
                        <button type="submit" class="btn btn-outline admin-staff-btn {{ $member->role === 'support' ? 'is-current' : '' }}">Destek</button>
                    </form>
                    <form method="POST" action="{{ route('admin.staff.update', $member) }}">
                        @csrf
                        <input type="hidden" name="role" value="admin">
                        <button type="submit" class="btn btn-outline admin-staff-btn {{ $member->role === 'admin' ? 'is-current' : '' }}">Yönetici</button>
                    </form>
                    <form method="POST" action="{{ route('admin.staff.update', $member) }}" onsubmit="return confirm('{{ $member->username }} personelden çıkarılsın mı?');">
                        @csrf
                        <input type="hidden" name="role" value="user">
                        <button type="submit" class="btn btn-danger admin-staff-btn">Kaldır</button>
                    </form>
                </div>
            @endif
        </article>
    @empty
        <p class="admin-ops-empty">Henüz personel yok. Yukarıdan kullanıcı ara ve rol ver.</p>
    @endforelse
</section>
@endsection
