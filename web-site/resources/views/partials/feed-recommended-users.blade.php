@php
    $recommendedUsers = $recommendedUsers ?? collect();
    $variant = $variant ?? 'feed'; // feed | members
    $sectionClass = $variant === 'members' ? 'rec-strip rec-strip--members' : 'rec-strip rec-strip--feed';
    $viewer = $viewer ?? auth()->user();
@endphp

@if($recommendedUsers->isNotEmpty())
<section class="{{ $sectionClass }}" aria-label="{{ __('app.feed.recommended_title') }}">
    <header class="rec-strip__head">
        <div class="rec-strip__titles">
            <h2 class="rec-strip__title">{{ __('app.feed.recommended_title') }}</h2>
            @if(__('app.feed.recommended_sub') !== '' && __('app.feed.recommended_sub') !== 'app.feed.recommended_sub')
                <p class="rec-strip__sub">{{ __('app.feed.recommended_sub') }}</p>
            @endif
        </div>
        <a href="{{ route('matches.index') }}" class="rec-strip__link">{{ __('app.nav.matches') }}</a>
    </header>

    <div class="rec-strip__track" role="list">
        @foreach($recommendedUsers as $user)
            @php
                $pkg = method_exists($user, 'activePackageType') ? $user->activePackageType() : null;
                $pkg = $pkg ?: 'free';
                $isBoosted = method_exists($user, 'isBoosted') && $user->isBoosted();
                $place = collect([$user->city, $user->district])->filter()->implode(' · ');
                if ($place === '') {
                    $place = $user->country ?: 'Türkiye';
                }
                // Kadın önerilerinde paket çerçevesi yok; boost varsa vurgula.
                $frame = $isBoosted ? 'boost' : (($user->gender ?? null) === 'female' ? 'free' : $pkg);
            @endphp
            <article class="rec-card rec-card--{{ $frame }}" role="listitem">
                <span class="rec-card__frame" aria-hidden="true"></span>
                <a href="{{ route('users.show', $user->username) }}" class="rec-card__main">
                    <span class="rec-card__photo">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="" width="120" height="120" loading="lazy" decoding="async">
                        @else
                            <span class="rec-card__initial">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                        @endif
                        @include('partials.online-status-badge', ['user' => $user, 'size' => 'sm'])
                    </span>
                    <span class="rec-card__body">
                        <span class="rec-card__name">
                            {{ $user->username }}
                            @include('partials.profile-verified-tick', ['user' => $user, 'size' => 'sm'])
                            @if(method_exists($user, 'age') && $user->age())
                                <span class="rec-card__age">{{ $user->age() }}</span>
                            @endif
                        </span>
                        <span class="rec-card__place">{{ $place }}</span>
                        @include('partials.profile-member-badges', ['user' => $user, 'compact' => true])
                    </span>
                </a>
                <div class="rec-card__actions">
                    <form method="POST" action="{{ route('users.like', $user->username) }}" class="rec-card__like-form" data-profile-like>
                        @csrf
                        <button type="submit" class="rec-card__btn rec-card__btn--like" data-like-btn>
                            {{ __('app.feed.recommended_like') }}
                        </button>
                    </form>
                    <a href="{{ route('users.show', $user->username) }}" class="rec-card__btn rec-card__btn--view">
                        {{ __('app.feed.recommended_view') }}
                    </a>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endif
