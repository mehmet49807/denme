@extends('layouts.app-with-sidebar')

@php $activeNav = 'matches'; @endphp

@section('title', ($tab ?? 'matches') === 'incoming' ? 'Kim Beğendi — Gönül Köprüsü' : 'Eşleşmeler — Gönül Köprüsü')

@section('app-content')
@php
    $tab = $tab ?? 'matches';
    $incomingCount = (int) ($incomingCount ?? 0);
    $matchesCount = (int) ($matchesCount ?? ($matchedUsers->total() ?? 0));
    $canRevealIncoming = (bool) ($canRevealIncoming ?? false);
    $incomingLocked = $tab === 'incoming' && ! $canRevealIncoming;
@endphp
<div class="matches-page users-browse-page {{ $incomingLocked ? 'matches-page--locked' : '' }}">
    <header class="users-browse-hero">
        <div class="users-browse-hero-inner">
            <span class="users-browse-badge">{{ $tab === 'incoming' ? 'Bekleyen beğeni' : 'Karşılıklı beğeni' }}</span>
            <h1>{{ $tab === 'incoming' ? 'Kim Beğendi' : 'Eşleşmelerim' }}</h1>
            <p class="users-browse-hero-lead">
                @if($incomingLocked)
                    Bu alan yalnızca Premium üyelere açıktır.
                @elseif($tab === 'incoming')
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
                @if($canRevealIncoming)
                    <a
                        href="{{ route('matches.index', ['tab' => 'incoming']) }}"
                        class="matches-tab {{ $tab === 'incoming' ? 'is-active' : '' }}"
                    >
                        Kim Beğendi
                        @if($incomingCount > 0)
                            <span class="matches-tab__count matches-tab__count--hot">{{ $incomingCount > 99 ? '99+' : $incomingCount }}</span>
                        @endif
                    </a>
                @else
                    <a
                        href="{{ route('matches.index', ['tab' => 'incoming']) }}"
                        class="matches-tab matches-tab--locked {{ $tab === 'incoming' ? 'is-active' : '' }}"
                        aria-label="Kim Beğendi — Premium gerekli"
                        data-gk-event="incoming_likes_tab_lock"
                    >
                        <span class="matches-tab__blur" aria-hidden="true">
                            <span class="matches-tab__label">Kim Beğendi</span>
                            @if($incomingCount > 0)
                                <span class="matches-tab__count matches-tab__count--hot">{{ $incomingCount > 99 ? '99+' : $incomingCount }}</span>
                            @endif
                        </span>
                        <span class="matches-tab__lock" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="11" width="14" height="10" rx="2"/>
                                <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                            </svg>
                            Premium
                        </span>
                    </a>
                @endif
            </nav>

            @unless($incomingLocked)
            <div class="matches-hero-actions">
                <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm">Keşfet</a>
                <a href="{{ route('feed') }}" class="btn btn-outline btn-sm">Önerilenler</a>
            </div>
            @endunless
        </div>
    </header>

    @if($incomingLocked)
        <section class="matches-full-lock" data-gk-event="incoming_likes_full_lock_view" aria-label="Kim Beğendi kilitli">
            <div class="matches-full-lock__glow" aria-hidden="true"></div>
            <div class="matches-full-lock__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="5" y="11" width="14" height="10" rx="2"/>
                    <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                </svg>
            </div>
            <h2>Kim Beğendi kilitli</h2>
            <p>
                @if($incomingCount > 0)
                    <strong>{{ $incomingCount }}</strong> kişi sizi beğendi.
                    Kim olduklarını görmek ve eşleşmek için Premium gerekli.
                @else
                    Kimlerin sizi beğendiğini görmek yalnızca Premium üyelere açıktır.
                @endif
            </p>
            <div class="matches-full-lock__actions">
                <a href="{{ route('premium') }}#premium-packages" class="btn btn-primary" data-gk-event="trial_cta_click" data-gk-event-label="incoming_full_lock">Premium’a geç</a>
                <a href="{{ route('matches.index') }}" class="btn btn-outline">Eşleşmelerime dön</a>
            </div>
            <div class="matches-full-lock__ghost" aria-hidden="true">
                @for($i = 0; $i < 6; $i++)
                    <span class="matches-full-lock__ghost-card"></span>
                @endfor
            </div>
        </section>
    @elseif($tab === 'incoming')
        <div class="users-browse-grid matches-grid">
            @forelse(($incomingUsers ?? collect()) as $user)
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
                        <form method="POST" action="{{ route('users.like', $user->username) }}" data-profile-like>
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm" data-like-btn>Beğen · Eşleş</button>
                        </form>
                        <a href="{{ route('users.show', $user->username) }}" class="btn btn-outline btn-sm">Profil</a>
                    </div>
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
