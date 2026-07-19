@extends('layouts.app-with-sidebar')

@php $activeNav = 'feed'; @endphp

@push('head')
@include('partials.asset', ['path' => 'css/feed-page.min.css'])
@endpush

@section('title', __('app.feed.title') . ' — ' . __('app.brand'))

@section('app-content')
@php
    $allStoryGroups = collect();
    if ($ownStoryGroup) {
        $allStoryGroups->push($ownStoryGroup);
    }
    $allStoryGroups = $allStoryGroups->merge($storyGroups);
@endphp

<div class="feed-container feed-page">
    @if(session('success')) <p class="feed-flash profile-success">{{ session('success') }}</p> @endif
    @error('image') <small class="form-error feed-flash">{{ $message }}</small> @enderror

    @include('partials.growth-onboarding', ['viewer' => $viewer, 'onboarding' => $onboarding ?? null])
    @include('partials.growth-invite-banner', ['viewer' => $viewer, 'showInviteBanner' => $showInviteBanner ?? false])

    <div class="feed-top">
        @if($allStoryGroups->isNotEmpty() || $viewer->canPostStories())
        <section class="stories-section" aria-label="{{ __('app.feed.stories') }}">
            <div class="stories-strip">
                @if($ownStoryGroup)
                <div class="story-item-host story-item-host--own">
                    <div class="story-item-ring-wrap">
                        <button type="button" class="story-item story-item--own" data-story-index="0" data-user-id="{{ $viewer->id }}" aria-label="{{ __('app.profile.view_story') }}">
                            <span class="story-ring story-ring--unseen story-ring--own">
                                <span class="story-avatar">
                                    @if($viewer->profile_photo_url)
                                        <img src="{{ $viewer->profile_photo_url }}" alt="" width="62" height="62" loading="lazy" decoding="async">
                                    @else
                                        {{ strtoupper(substr($viewer->username, 0, 1)) }}
                                    @endif
                                    @include('partials.online-status-badge', ['user' => $viewer, 'size' => 'sm'])
                                </span>
                            </span>
                        </button>
                        @if($viewer->canPostStories())
                        <button type="button" class="story-add-badge story-add-badge--compose" data-open-compose="story" aria-label="{{ __('app.feed.add_story') }}">+</button>
                        @endif
                    </div>
                    <span class="story-username">{{ __('app.feed.your_story') }}</span>
                </div>
                @elseif($viewer->canPostStories())
                <button type="button" class="story-item story-item--add" data-open-compose="story" aria-label="{{ __('app.feed.add_story') }}">
                    <span class="story-ring story-ring--add">
                        <span class="story-avatar story-avatar--add">
                            @if($viewer->profile_photo_url)
                                <img src="{{ $viewer->profile_photo_url }}" alt="" width="62" height="62" loading="lazy" decoding="async">
                            @else
                                {{ strtoupper(substr($viewer->username, 0, 1)) }}
                            @endif
                        </span>
                        <span class="story-add-badge" aria-hidden="true">+</span>
                    </span>
                    <span class="story-username">{{ __('app.feed.add_story') }}</span>
                </button>
                @endif

                @foreach($storyGroups as $index => $group)
                @php $storyIndex = ($ownStoryGroup ? 1 : 0) + $index; @endphp
                <button type="button" class="story-item {{ !empty($group['is_featured']) ? 'story-item--featured' : '' }}" data-story-index="{{ $storyIndex }}" data-user-id="{{ $group['user_id'] }}" aria-label="{{ __('app.feed.story_of', ['name' => $group['username']]) }}">
                    <span class="story-ring story-ring--unseen {{ !empty($group['is_featured']) ? 'story-ring--featured' : '' }}">
                        <span class="story-avatar">
                            @if($group['profile_photo_url'])
                                <img src="{{ $group['profile_photo_url'] }}" alt="" width="62" height="62" loading="lazy" decoding="async">
                            @else
                                {{ strtoupper(substr($group['username'], 0, 1)) }}
                            @endif
                            @include('partials.online-status-badge', ['online' => !empty($group['is_online']), 'size' => 'sm'])
                        </span>
                    </span>
                    <span class="story-username">{{ $group['username'] }}</span>
                </button>
                @endforeach
            </div>
            @error('story') <small class="form-error story-error">{{ $message }}</small> @enderror
        </section>
        @endif

        @include('partials.feed-toolbar', ['viewer' => $viewer])
    </div>

    @include('partials.feed-recommended-users', ['recommendedUsers' => $recommendedUsers ?? collect()])

    <div class="feed-posts">
    @forelse($posts as $post)
    <article
        class="post-card {{ in_array($post->id, $likedPostIds) ? 'post-card--liked' : '' }}"
        data-post-id="{{ $post->id }}"
        style="--post-enter-delay: {{ min($loop->index * 0.07, 0.42) }}s"
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
                data-image-url="{{ $post->image_url }}"
                data-caption="{{ e($post->caption ?? '') }}"
                data-username="{{ $post->user->username }}"
                data-likes-count="{{ $post->likes_count }}"
                data-is-liked="{{ in_array($post->id, $likedPostIds) ? '1' : '0' }}"
                data-like-url="{{ route('posts.like', $post) }}"
                @if($post->user_id === $viewer->id)
                data-destroy-url="{{ route('posts.destroy', $post) }}"
                @endif
                aria-label="{{ __('app.feed.zoom_post') }}"
            >
                <div class="post-image-shimmer" aria-hidden="true"></div>
                <div class="post-image-frame">
                    <img
                        src="{{ $post->image_url }}"
                        alt="{{ $post->caption ? Str::limit($post->caption, 80) : __('app.feed.post_image') }}"
                        class="post-image-media"
                        loading="lazy"
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

        <footer class="post-footer{{ $post->caption ? '' : ' post-footer--compact' }}">
            <div class="post-footer-row">
                @if($post->caption)
                <div class="post-caption" data-post-caption>
                    <p class="post-caption-text">
                        <a href="{{ route('users.show', $post->user->username) }}" class="post-caption-user">{{ $post->user->username }}</a>
                        <span class="post-caption-body">{{ $post->caption }}</span>
                    </p>
                    <button type="button" class="post-caption-more" hidden data-caption-toggle>{{ __('app.common.more') }}</button>
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
    @empty
    <p class="feed-empty">{{ __('app.feed.empty') }}</p>
    @endforelse

    {{ $posts->links() }}
    </div>
</div>

@include('partials.feed-compose', ['viewer' => $viewer])
@include('partials.post-detail-dialog')

@if($allStoryGroups->isNotEmpty())
<div class="ig-story-viewer" id="igStoryViewer" hidden data-groups="{{ $allStoryGroups->toJson() }}">
    <div class="ig-story-frame">
        <div class="ig-story-progress" id="igStoryProgress"></div>

        <header class="ig-story-header">
            <a href="#" id="igStoryUserLink" class="ig-story-user">
                <span class="ig-story-user-avatar" id="igStoryUserAvatar"></span>
                <span class="ig-story-user-meta">
                    <strong id="igStoryUserName"></strong>
                    <small id="igStoryTime">{{ __('app.common.now') }}</small>
                </span>
            </a>
            <div class="ig-story-header-actions">
                <button type="button" class="ig-story-delete" id="igStoryDelete" hidden aria-label="{{ __('app.feed.delete_story') }}">🗑</button>
                <button type="button" class="ig-story-close" data-close-story aria-label="{{ __('app.common.close') }}">×</button>
            </div>
        </header>

        <div class="ig-story-stage" id="igStoryStage">
            <button type="button" class="ig-story-tap ig-story-tap--prev" id="igStoryTapPrev" aria-label="{{ __('app.feed.prev') }}"></button>
            <div class="ig-story-media" id="igStoryMedia"></div>
            <button type="button" class="ig-story-tap ig-story-tap--next" id="igStoryTapNext" aria-label="{{ __('app.feed.next') }}"></button>
        </div>
    </div>
</div>
@endif

@include('partials.asset', ['path' => 'js/feed-page.min.js'])
@endsection
