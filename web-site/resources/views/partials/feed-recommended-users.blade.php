@php
    $recommendedUsers = $recommendedUsers ?? collect();
    $variant = $variant ?? 'feed'; // feed | members
    $sectionClass = $variant === 'members' ? 'rec-strip rec-strip--members' : 'rec-strip rec-strip--feed';
@endphp

@if($recommendedUsers->isNotEmpty())
<section class="{{ $sectionClass }}" aria-label="{{ __('app.feed.recommended_title') }}">
    <header class="rec-strip__head">
        <div class="rec-strip__titles">
            <h2 class="rec-strip__title">{{ __('app.feed.recommended_title') }}</h2>
        </div>
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
                $frame = $isBoosted ? 'boost' : $pkg;
            @endphp
            <a
                href="{{ route('users.show', $user->username) }}"
                class="rec-card rec-card--{{ $frame }}"
                role="listitem"
            >
                <span class="rec-card__frame" aria-hidden="true"></span>
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
                        @if(method_exists($user, 'age') && $user->age())
                            <span class="rec-card__age">{{ $user->age() }}</span>
                        @endif
                    </span>
                    <span class="rec-card__place">{{ $place }}</span>
                    @include('partials.profile-member-badges', ['user' => $user, 'compact' => true])
                </span>
            </a>
        @endforeach
    </div>
</section>
@endif
