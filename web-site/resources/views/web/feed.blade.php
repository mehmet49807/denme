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
        @if($viewer->canViewStories() && ($allStoryGroups->isNotEmpty() || $viewer->canPostStories()))
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

        @include('partials.feed-toolbar', ['viewer' => $viewer, 'showFeedPromoBanner' => $showFeedPromoBanner ?? true])
    </div>

    <div
        class="feed-posts"
        data-feed-infinite
        data-next-page-url="{{ $feedNextPageUrl ?? '' }}"
    >
    @forelse($posts as $post)
        @include('partials.feed-post-card', [
            'post' => $post,
            'viewer' => $viewer,
            'likedPostIds' => $likedPostIds,
            'index' => $loop->index,
            'eager' => $loop->index < 2,
        ])

        {{-- Erkek akışı: gönderilerin altında "Senin için Önerilen üyeler" (kadın kartları) --}}
        @if(strtolower((string) ($viewer->gender ?? '')) === 'male' && ($loop->iteration === 2 || ($loop->last && $loop->count < 2)))
            @include('partials.feed-recommended-users', [
                'recommendedUsers' => $recommendedUsers ?? collect(),
                'variant' => 'feed',
            ])
        @endif
    @empty
        @include('partials.empty-state', [
            'class' => 'feed-empty-state',
            'icon' => 'post',
            'title' => __('app.feed.empty'),
            'ctaUrl' => route('profile'),
            'ctaLabel' => 'İlk gönderini paylaş',
        ])
        @if(strtolower((string) ($viewer->gender ?? '')) === 'male')
            @include('partials.feed-recommended-users', [
                'recommendedUsers' => $recommendedUsers ?? collect(),
                'variant' => 'feed',
            ])
        @endif
    @endforelse

    <div class="feed-infinite-sentinel" data-feed-sentinel aria-hidden="true"></div>
    <noscript>{{ $posts->links() }}</noscript>
    </div>
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

@include('partials.asset', ['path' => 'js/feed-page.min.js', 'defer' => true])
@endsection
