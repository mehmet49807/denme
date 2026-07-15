@php
    $age = $user->age();
    $status = $user->resolvedRelationshipStatus();
    $showPosts = isset($postsCount);
@endphp

<div class="profile-identity">
    <div class="profile-identity-row">
        <h1 class="profile-username profile-identity-name">
            <span class="profile-username-text">{{ $user->username }}</span>
            @if($age)
                <span class="profile-identity-age" title="Yaş">{{ $age }}</span>
            @endif
            @include('partials.profile-verified-tick', ['user' => $user, 'size' => $tickSize ?? 'md'])
            @include('partials.trust-badge', ['user' => $user, 'size' => $tickSize ?? 'md'])
            @include('partials.profile-online-label', ['user' => $user])
        </h1>
    </div>

    <p class="profile-location-line profile-identity-location">
        @isset($locationAsLinks)
            @include('partials.location-link', [
                'country' => $user->country ?? 'Türkiye',
                'city' => $user->city,
                'district' => $user->district,
            ])
        @else
            {{ $user->country ?? 'Türkiye' }} — {{ $user->city }}
            @if($user->district) — {{ $user->district }}@endif
        @endisset
    </p>

    @if($status)
        <p class="profile-relationship-status">
            <span class="profile-relationship-status-label">İlişki durumu:</span>
            <span class="profile-relationship-status-icon" aria-hidden="true">{{ $status['icon'] }}</span>
            <span class="profile-relationship-status-value">{{ $status['label'] }}</span>
        </p>
    @endif

    @if(!empty($user->bio))
        <p class="profile-bio">{{ $user->bio }}</p>
    @endif

    @if($showPosts)
        <p class="profile-post-count">
            {{ $postsCount }} gönderi
            @if($age)
                <span class="profile-post-count-age">· {{ $age }} yaş</span>
            @endif
        </p>
    @endif
</div>
