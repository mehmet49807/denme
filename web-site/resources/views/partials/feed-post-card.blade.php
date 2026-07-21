@php
    $index = $index ?? 0;
    $eager = $eager ?? false;
@endphp
<article
    class="post-card {{ in_array($post->id, $likedPostIds) ? 'post-card--liked' : '' }}"
    data-post-id="{{ $post->id }}"
    style="--post-enter-delay: {{ min($index * 0.07, 0.42) }}s"
>
    <div class="post-card-accent" aria-hidden="true"></div>

    <header class="post-header">
        <a href="{{ route('users.show', $post->user->username) }}" class="post-header-avatar" aria-hidden="true" tabindex="-1">
            @if($post->user->profile_photo_url)
                <img src="{{ $post->user->profile_photo_url }}" alt="" width="40" height="40" loading="lazy" decoding="async">
            @else
                <span>{{ strtoupper(substr($post->user->username, 0, 1)) }}</span>
            @endif
            @include('partials.online-status-badge', ['user' => $post->user, 'size' => 'sm'])
        </a>

        <div class="post-header-meta">
            <div class="post-header-top">
                <a href="{{ route('users.show', $post->user->username) }}" class="post-username">
                    {{ $post->user->username }}
                    @if($post->user->age())
                        <span class="post-user-age" title="Yaş">{{ $post->user->age() }}</span>
                    @endif
                    @include('partials.profile-verified-tick', ['user' => $post->user, 'size' => 'sm'])
                </a>
                @include('partials.profile-online-label', ['user' => $post->user, 'compact' => true])
                @include('partials.profile-member-badges', ['user' => $post->user, 'compact' => true])
                <time class="post-time" datetime="{{ $post->created_at->toIso8601String() }}">{{ $post->created_at->locale(app()->getLocale())->diffForHumans(short: true) }}</time>
            </div>
            @include('partials.location-link', [
                'country' => $post->user->country ?? 'Türkiye',
                'city' => $post->user->city,
                'district' => $post->user->district,
                'class' => 'post-location',
            ])
        </div>

        @if($post->user_id === $viewer->id)
        <div class="post-menu">
            <button type="button" class="post-menu-btn" aria-label="{{ __('app.feed.post_menu') }}" aria-haspopup="true" data-post-menu>⋯</button>
            <div class="post-menu-dropdown" hidden>
                <button
                    type="button"
                    class="post-menu-edit"
                    data-edit-caption
                    data-post-id="{{ $post->id }}"
                    data-update-url="{{ route('posts.update', $post) }}"
                    data-caption="{{ e($post->caption ?? '') }}"
                >{{ __('app.feed.edit_caption') }}</button>
                <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm(@js(__('app.feed.delete_confirm')))">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="post-menu-delete">{{ __('app.common.delete') }}</button>
                </form>
            </div>
        </div>
        @endif
    </header>

    @if($post->image_url)
    <div class="post-image" data-post-image>
        <button
            type="button"
            class="post-image-trigger"
            data-open-feed-post
            data-post-id="{{ $post->id }}"
            data-image-url="{{ $post->image_url }}"
            data-caption="{{ e($post->caption ?? '') }}"
            data-username="{{ $post->user->username }}"
            data-likes-count="{{ $post->likes_count }}"
            data-is-liked="{{ in_array($post->id, $likedPostIds) ? '1' : '0' }}"
            data-like-url="{{ route('posts.like', $post) }}"
            @if($post->user_id === $viewer->id)
            data-destroy-url="{{ route('posts.destroy', $post) }}"
            data-update-url="{{ route('posts.update', $post) }}"
            @endif
            aria-label="{{ __('app.feed.zoom_post') }}"
        >
            <div class="post-image-shimmer" aria-hidden="true"></div>
            <div class="post-image-frame">
                <img
                    src="{{ $post->image_url }}"
                    alt="{{ $post->caption ? Str::limit($post->caption, 80) : __('app.feed.post_image') }}"
                    class="post-image-media"
                    loading="{{ $eager ? 'eager' : 'lazy' }}"
                    @if($eager) fetchpriority="high" @endif
                    decoding="async"
                    width="450"
                    height="450"
                >
            </div>
            <span class="post-image-zoom-hint" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M14 10l6.1-6.1M9 21H3v-6M10 14l-6.1 6.1"/></svg>
            </span>
        </button>
        <span class="post-like-burst" aria-hidden="true">♥</span>
    </div>
    @else
    <div class="post-image post-image--empty">
        <div class="post-image-placeholder" aria-hidden="true">
            <span class="post-image-placeholder-icon">🖼</span>
            <span>{{ __('app.feed.post_image_label') }}</span>
        </div>
    </div>
    @endif

    <footer class="post-footer{{ $post->caption || $post->user_id === $viewer->id ? '' : ' post-footer--compact' }}">
        <div class="post-footer-row">
            @if($post->caption)
            <div class="post-caption" data-post-caption data-post-id="{{ $post->id }}">
                <p class="post-caption-text">
                    <a href="{{ route('users.show', $post->user->username) }}" class="post-caption-user">{{ $post->user->username }}</a>
                    <span class="post-caption-body">{{ $post->caption }}</span>
                </p>
                <button type="button" class="post-caption-more" hidden data-caption-toggle>{{ __('app.common.more') }}</button>
                @if($post->user_id === $viewer->id)
                <button
                    type="button"
                    class="post-caption-edit-btn"
                    data-edit-caption
                    data-post-id="{{ $post->id }}"
                    data-update-url="{{ route('posts.update', $post) }}"
                    data-caption="{{ e($post->caption ?? '') }}"
                >{{ __('app.common.edit') }}</button>
                @endif
            </div>
            @elseif($post->user_id === $viewer->id)
            <div class="post-caption post-caption--empty" data-post-caption data-post-id="{{ $post->id }}">
                <button
                    type="button"
                    class="post-caption-add-btn"
                    data-edit-caption
                    data-post-id="{{ $post->id }}"
                    data-update-url="{{ route('posts.update', $post) }}"
                    data-caption=""
                >{{ __('app.feed.add_caption') }}</button>
            </div>
            @endif
            <button type="button"
                class="like-btn {{ in_array($post->id, $likedPostIds) ? 'like-btn--active' : '' }}"
                data-like-url="{{ route('posts.like', $post) }}"
                aria-label="{{ in_array($post->id, $likedPostIds) ? __('app.feed.unlike') : __('app.feed.like') }}"
                aria-pressed="{{ in_array($post->id, $likedPostIds) ? 'true' : 'false' }}">
                <span class="like-btn-shine" aria-hidden="true"></span>
                <span class="like-icon" aria-hidden="true">♥</span>
                <span class="like-count">{{ $post->likes_count }}</span>
                <span class="like-label">{{ __('app.feed.likes') }}</span>
            </button>
        </div>
    </footer>
</article>
