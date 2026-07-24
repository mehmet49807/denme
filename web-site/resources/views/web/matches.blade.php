@extends('layouts.app-with-sidebar')

@php $activeNav = 'matches'; @endphp

@section('title', 'Eşleşmeler — Gönül Köprüsü')

@section('app-content')
<div class="matches-page users-browse-page">
    <header class="users-browse-hero">
        <div class="users-browse-hero-inner">
            <span class="users-browse-badge">Karşılıklı beğeni</span>
            <h1>Eşleşmelerim</h1>
            <p class="users-browse-hero-lead">
                Karşılıklı beğendiğiniz üyeler burada. Hemen mesajlaşmaya başlayın.
                @if(($incomingCount ?? 0) > 0)
                    <strong>{{ $incomingCount }}</strong> bekleyen beğeni var — profillerini beğenerek eşleşin.
                @endif
            </p>
            <div class="matches-hero-actions">
                <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm">Keşfet</a>
                <a href="{{ route('feed') }}" class="btn btn-outline btn-sm">Önerilenler</a>
            </div>
        </div>
    </header>

    <div class="users-browse-grid matches-grid">
        @forelse($matchedUsers as $user)
            <article class="users-browse-card matches-card">
                <a href="{{ route('users.show', $user->username) }}" class="users-browse-card__link">
                    <span class="users-browse-card__photo">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="" loading="lazy" width="160" height="160">
                        @else
                            <span class="users-browse-card__initial">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                        @endif
                        @include('partials.online-status-badge', ['user' => $user, 'size' => 'sm'])
                    </span>
                    <span class="users-browse-card__body">
                        <span class="users-browse-card__name">
                            {{ $user->username }}
                            @include('partials.profile-verified-tick', ['user' => $user, 'size' => 'sm'])
                        </span>
                        <span class="users-browse-card__meta">{{ $user->city ?: 'Türkiye' }}</span>
                    </span>
                </a>
                <div class="matches-card__actions">
                    @if($viewer->canSendMessages())
                        <a href="{{ route('messages.show', $user->username) }}" class="btn btn-primary btn-sm">Mesaj</a>
                    @else
                        <a href="{{ route('premium') }}#premium-packages" class="btn btn-outline btn-sm">Premium ile yaz</a>
                    @endif
                    <a href="{{ route('users.show', $user->username) }}" class="btn btn-outline btn-sm">Profil</a>
                </div>
            </article>
        @empty
            <div class="admin-panel" style="grid-column:1/-1;padding:1.5rem;">
                <p>Henüz eşleşmeniz yok. Keşfet’ten profilleri beğenerek başlayın.</p>
                <a href="{{ route('users.index') }}" class="btn btn-primary">Üyeleri keşfet</a>
            </div>
        @endforelse
    </div>

    {{ $matchedUsers->links() }}
</div>
@endsection
