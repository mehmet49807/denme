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
    @endphp
    <a
        href="{{ route('users.show', $user->username) }}"
        class="users-browse-card users-browse-card--{{ $pkgClass }}"
    >
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
            @if($pkgLabel)
                <span class="users-browse-pkg users-browse-pkg--{{ $pkgClass }}">{{ $pkgLabel }}</span>
            @endif
            @include('partials.online-status-badge', ['user' => $user, 'size' => 'sm'])
        </div>
        <div class="users-browse-meta">
            <strong class="users-browse-name">
                {{ $user->username }}
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
        <span class="users-browse-cta">{{ __('app.users.view_profile') }}</span>
    </a>
@endforeach
