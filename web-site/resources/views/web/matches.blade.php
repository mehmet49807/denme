@extends('layouts.app-with-sidebar')

@php $activeNav = 'matches'; @endphp

@section('title', ($tab ?? 'matches') === 'incoming' ? 'Kim Beğendi — Gönül Köprüsü' : 'Eşleşmeler — Gönül Köprüsü')

@section('app-content')
@php
    $tab = $tab ?? 'matches';
    $incomingCount = (int) ($incomingCount ?? 0);
    $matchesCount = (int) ($matchesCount ?? ($matchedUsers->total() ?? 0));
    $canRevealIncoming = (bool) ($canRevealIncoming ?? false);
@endphp
<div class="matches-page users-browse-page">
    <header class="users-browse-hero">
        <div class="users-browse-hero-inner">
            <span class="users-browse-badge">{{ $tab === 'incoming' ? 'Bekleyen beğeni' : 'Karşılıklı beğeni' }}</span>
            <h1>{{ $tab === 'incoming' ? 'Kim Beğendi' : 'Eşleşmelerim' }}</h1>
            <p class="users-browse-hero-lead">
                @if($tab === 'incoming')
                    Sizi beğenen üyeler burada. Karşılık verirseniz eşleşirsiniz.
                @else
                    Karşılıklı beğendiğiniz üyeler burada. Hemen mesajlaşmaya başlayın.
                @endif
            </p>

            <nav class="matches-tabs" aria-label="Eşleşme sekmeleri">
                <a
                    href="{{ route('matches.index') }}"
                    class="matches-tab {{ $tab === 'matches' ? 'is-active' : '' }}"
                >
                    Eşleşmeler
                    <span class="matches-tab__count">{{ $matchesCount }}</span>
                </a>
                <a
                    href="{{ route('matches.index', ['tab' => 'incoming']) }}"
                    class="matches-tab {{ $tab === 'incoming' ? 'is-active' : '' }}"
                >
                    Kim Beğendi
                    @if($incomingCount > 0)
                        <span class="matches-tab__count matches-tab__count--hot">{{ $incomingCount > 99 ? '99+' : $incomingCount }}</span>
                    @endif
                </a>
            </nav>

            <div class="matches-hero-actions">
                <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm">Keşfet</a>
                <a href="{{ route('feed') }}" class="btn btn-outline btn-sm">Önerilenler</a>
            </div>
        </div>
    </header>

    @if($tab === 'incoming')
        @if(!$canRevealIncoming && $incomingCount > 0)
            <div class="matches-lock-banner" data-gk-event="incoming_likes_lock_view">
                <div>
                    <strong>{{ $incomingCount }} kişi sizi beğendi</strong>
                    <p>Kim olduklarını görmek ve eşleşmek için deneme veya premium gerekli.</p>
                </div>
                <a href="{{ route('premium') }}#premium-packages" class="btn btn-primary btn-sm" data-gk-event="trial_cta_click" data-gk-event-label="incoming_likes_lock">Paketleri incele</a>
            </div>
        @endif

        <div class="users-browse-grid matches-grid">
            @forelse(($incomingUsers ?? collect()) as $user)
                <article class="users-browse-card matches-card {{ $canRevealIncoming ? '' : 'matches-card--locked' }}">
                    @if($canRevealIncoming)
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
                            <form method="POST" action="{{ route('users.like', $user->username) }}" data-profile-like>
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm" data-like-btn>Beğen · Eşleş</button>
                            </form>
                            <a href="{{ route('users.show', $user->username) }}" class="btn btn-outline btn-sm">Profil</a>
                        </div>
                    @else
                        <div class="users-browse-card__link matches-card__locked-link" aria-hidden="true">
                            <span class="users-browse-card__photo matches-card__photo--blur">
                                @if($user->profile_photo_url)
                                    <img src="{{ $user->profile_photo_url }}" alt="" loading="lazy" width="160" height="160">
                                @else
                                    <span class="users-browse-card__initial">?</span>
                                @endif
                            </span>
                            <span class="users-browse-card__body">
                                <span class="users-browse-card__name">Gizli üye</span>
                                <span class="users-browse-card__meta">Premium ile gör</span>
                            </span>
                        </div>
                        <div class="matches-card__actions">
                            <a href="{{ route('premium') }}#premium-packages" class="btn btn-primary btn-sm" data-gk-event="trial_cta_click" data-gk-event-label="incoming_card">Kim olduğunu gör</a>
                        </div>
                    @endif
                </article>
            @empty
                <div class="admin-panel" style="grid-column:1/-1;padding:1.5rem;">
                    <p>Henüz bekleyen beğeni yok. Keşfet’ten profilleri beğenerek görünürlüğünü artır.</p>
                    <a href="{{ route('users.index') }}" class="btn btn-primary">Üyeleri keşfet</a>
                </div>
            @endforelse
        </div>

        @if($incomingUsers)
            {{ $incomingUsers->links() }}
        @endif
    @else
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
                    <p>Henüz eşleşmeniz yok. Kim Beğendi’den karşılık verin veya Keşfet’ten beğenin.</p>
                    <div class="matches-hero-actions">
                        <a href="{{ route('matches.index', ['tab' => 'incoming']) }}" class="btn btn-primary">Kim Beğendi</a>
                        <a href="{{ route('users.index') }}" class="btn btn-outline">Üyeleri keşfet</a>
                    </div>
                </div>
            @endforelse
        </div>

        {{ $matchedUsers->links() }}
    @endif
</div>
@endsection
