@php
    $profileUser = $profileUser ?? $user;
    $likedPostIds = $likedPostIds ?? [];
    $isOwnProfile = $isOwnProfile ?? ($profileUser->id === ($viewer->id ?? null));
    $postsTitle = $postsTitle ?? ($isOwnProfile ? __('app.feed.my_posts') : __('app.common.posts_title'));
    $emptyMessage = $emptyMessage ?? ($isOwnProfile ? __('app.feed.empty_own') : __('app.feed.empty_other'));
@endphp

<section class="profile-posts-section">
    <header class="profile-section-head profile-section-head--posts">
        <div class="profile-section-head__main">
            <span class="profile-section-head__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'post'])</span>
            <div class="profile-section-head__copy">
                <h2 class="profile-section-head__title">{{ $postsTitle }}</h2>
                <p class="profile-section-head__sub">{{ $isOwnProfile ? __('app.profile.posts_sub_own') : __('app.profile.posts_sub_other') }}</p>
            </div>
        </div>
        <span class="profile-section-head__meta">{{ $posts->count() }} {{ __('app.users.posts_label') }}</span>
    </header>
    @if($posts->isNotEmpty())
    <div class="user-profile-grid" id="profilePostsGrid">
        @foreach($posts as $post)
        <button
            type="button"
            class="user-profile-grid-item"
            data-open-post-detail
            data-post-id="{{ $post->id }}"
            data-image-url="{{ $post->image_url }}"
            data-caption="{{ e($post->caption ?? '') }}"
            data-likes-count="{{ $post->likes_count }}"
            data-is-liked="{{ in_array($post->id, $likedPostIds) ? '1' : '0' }}"
            data-like-url="{{ route('posts.like', $post) }}"
            @if($isOwnProfile)
            data-destroy-url="{{ route('posts.destroy', $post) }}"
            @if(\Illuminate\Support\Facades\Route::has('posts.update'))
            data-update-url="{{ route('posts.update', $post) }}"
            @endif
            @endif
            aria-label="{{ __('app.feed.zoom_post') }}"
        >
            @if($post->image_url)
                <img src="{{ $post->image_url }}" alt="{{ __('app.feed.post_image') }}" loading="lazy" decoding="async">
            @endif
        </button>
        @endforeach
    </div>
    @else
    <p class="feed-empty">{{ $emptyMessage }}</p>
    @endif
</section>

<dialog id="postDetailDialog" class="post-detail-dialog">
    <div class="post-detail-inner">
        <header class="post-detail-header">
            <strong id="postDetailUsername" class="post-detail-username">{{ $profileUser->username }}</strong>
            <button type="button" class="post-detail-close" data-close-post-detail aria-label="{{ __('app.common.close') }}">×</button>
        </header>
        <div class="post-detail-image-wrap">
            <div class="post-detail-image-frame">
                <img id="postDetailImage" src="" alt="{{ __('app.feed.post_image') }}" decoding="async">
            </div>
        </div>
        <div class="post-detail-footer" id="postDetailFooter">
            <p class="post-detail-caption" id="postDetailCaption" hidden></p>
            <div class="post-detail-actions">
                <button type="button" class="like-btn post-detail-like" id="postDetailLikeBtn" data-like-url="" aria-label="{{ __('app.feed.like') }}">
                    <span class="like-icon" aria-hidden="true">♥</span>
                    <span class="like-count" id="postDetailLikeCount">0</span>
                </button>
                @if($isOwnProfile)
                <div class="post-detail-actions-end">
                    <button type="button" class="post-detail-edit" id="postDetailEditCaption" hidden>{{ __('app.feed.edit_caption') }}</button>
                    <form method="POST" id="postDetailDeleteForm" class="post-detail-delete-form" action="" onsubmit="return confirm(@js(__('app.feed.delete_confirm')))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="post-detail-delete">{{ __('app.common.delete') }}</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</dialog>
