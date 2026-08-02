@php
    $likedUserIds = collect($likedUserIds ?? [])->map(fn ($id) => (int) $id)->all();
    $followingUserIds = collect($followingUserIds ?? [])->map(fn ($id) => (int) $id)->all();
@endphp
@foreach($users as $user)
    @php
        $pkg = method_exists($user, 'activePackageType') ? $user->activePackageType() : null;
        $pkgClass = in_array($pkg, ['pro', 'gold', 'platinum'], true) ? $pkg : 'free';
        $pkgLabel = match ($pkgClass) {
            'platinum' => 'Platinum',
            'gold' => 'Gold',
            'pro' => 'Pro',
            default => null,
        };
        $age = method_exists($user, 'age') ? $user->age() : null;
        $place = collect([$user->city, $user->district])->filter()->implode(', ');
        $isLiked = in_array((int) $user->id, $likedUserIds, true);
        $isFollowing = in_array((int) $user->id, $followingUserIds, true);
        $isBoosted = method_exists($user, 'isBoosted') && $user->isBoosted();
    @endphp
    <article class="users-browse-card users-browse-card--{{ $pkgClass }} {{ $isBoosted ? 'users-browse-card--boost' : '' }}">
        <a href="{{ route('users.show', $user->username) }}" class="users-browse-card__main">
            <div class="users-browse-card-top">
                <div class="users-browse-avatar-ring users-browse-avatar-ring--{{ $pkgClass }}" aria-hidden="true">
                    <div class="users-browse-avatar">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->username }}" width="72" height="72" loading="lazy" decoding="async">
                        @else
                            <span class="users-browse-initial">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                        @endif
                    </div>
                </div>
                @if($isBoosted)
                    <span class="users-browse-pkg users-browse-pkg--boost">Öne çıkan</span>
                @elseif($pkgLabel)
                    <span class="users-browse-pkg users-browse-pkg--{{ $pkgClass }}">{{ $pkgLabel }}</span>
                @endif
                @include('partials.online-status-badge', ['user' => $user, 'size' => 'sm'])
            </div>
            <div class="users-browse-meta">
                <strong class="users-browse-name">
                    {{ $user->username }}
                    @include('partials.profile-verified-tick', ['user' => $user, 'size' => 'sm'])
                    @if($age)
                        <span class="users-browse-age">{{ $age }}</span>
                    @endif
                </strong>
                @if($place !== '')
                <span class="users-browse-location">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    {{ $place }}
                </span>
                @endif
                <span class="users-browse-posts">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    {{ $user->posts_count }} {{ __('app.users.posts_label') }}
                </span>
            </div>
        </a>
        <div class="users-browse-card__actions">
            <form
                method="POST"
                action="{{ route('users.follow', $user->username) }}"
                class="users-browse-follow-form"
                data-profile-follow
                data-following="{{ $isFollowing ? '1' : '0' }}"
            >
                @csrf
                <button
                    type="submit"
                    class="users-browse-follow-btn {{ $isFollowing ? 'is-following' : '' }}"
                    data-follow-btn
                    aria-pressed="{{ $isFollowing ? 'true' : 'false' }}"
                    title="{{ $isFollowing ? __('app.profile.following') : __('app.profile.follow') }}"
                    aria-label="{{ $isFollowing ? __('app.profile.following') : __('app.profile.follow') }}"
                >
                    <span class="profile-action-icon profile-action-icon--follow" aria-hidden="true">
                        @if($isFollowing)
                            @include('partials.theme-icon', ['icon' => 'user-check'])
                        @else
                            @include('partials.theme-icon', ['icon' => 'user-plus'])
                        @endif
                    </span>
                    <span data-follow-label>{{ $isFollowing ? __('app.profile.following') : __('app.profile.follow') }}</span>
                </button>
            </form>
            <form method="POST" action="{{ route('users.like', $user->username) }}" class="users-browse-like-form" data-profile-like data-liked="{{ $isLiked ? '1' : '0' }}">
                @csrf
                <button type="submit" class="users-browse-like-btn {{ $isLiked ? 'is-liked' : '' }}" data-like-btn aria-pressed="{{ $isLiked ? 'true' : 'false' }}">
                    <span data-like-label>{{ $isLiked ? __('app.profile.liked') : __('app.profile.like') }}</span>
                </button>
            </form>
            <a href="{{ route('users.show', $user->username) }}" class="users-browse-cta">{{ __('app.users.view_profile') }}</a>
        </div>
    </article>
@endforeach
