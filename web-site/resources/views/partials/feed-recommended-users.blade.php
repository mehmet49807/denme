@php
    $recommendedUsers = $recommendedUsers ?? collect();
@endphp

@if($recommendedUsers->isNotEmpty())
<section class="feed-recommended" aria-label="{{ __('app.feed.recommended_title') }}">
    <header class="feed-recommended__head">
        <h2 class="feed-recommended__title">{{ __('app.feed.recommended_title') }}</h2>
        <p class="feed-recommended__sub">{{ __('app.feed.recommended_sub') }}</p>
    </header>
    <div class="feed-recommended__strip">
        @foreach($recommendedUsers as $user)
            <a href="{{ route('users.show', $user->username) }}" class="feed-recommended__card">
                <span class="feed-recommended__avatar" aria-hidden="true">
                    @if($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="" width="56" height="56" loading="lazy" decoding="async">
                    @else
                        <span>{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                    @endif
                    @include('partials.online-status-badge', ['user' => $user, 'size' => 'sm'])
                </span>
                <span class="feed-recommended__name">{{ $user->username }}</span>
                @include('partials.profile-member-badges', ['user' => $user, 'compact' => true])
            </a>
        @endforeach
    </div>
</section>
@endif
