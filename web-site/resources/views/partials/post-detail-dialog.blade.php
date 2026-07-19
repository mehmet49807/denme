<dialog id="feedPostDetailDialog" class="post-detail-dialog">
    <div class="post-detail-inner">
        <header class="post-detail-header">
            <div class="post-detail-user">
                <span class="post-detail-user-dot" aria-hidden="true"></span>
                <strong id="feedPostDetailUsername"></strong>
            </div>
            <button type="button" class="post-detail-close" data-close-feed-post-detail aria-label="{{ __('app.common.close') }}">×</button>
        </header>
        <div class="post-image post-image--loaded" data-post-detail-image>
            <div class="post-image-trigger post-image-trigger--static" tabindex="-1" aria-hidden="true">
                <div class="post-image-frame">
                    <img id="feedPostDetailImage" src="" alt="{{ __('app.feed.post_image') }}" class="post-image-media post-image-media--loaded" decoding="async">
                </div>
            </div>
        </div>
        <div class="post-detail-footer post-footer">
            <div class="post-footer-row">
                <p class="post-detail-caption post-caption" id="feedPostDetailCaption" hidden></p>
                <div class="post-detail-actions post-detail-actions--end">
                    <button type="button" class="like-btn" id="feedPostDetailLikeBtn" data-like-url="" aria-label="{{ __('app.feed.like') }}">
                        <span class="like-btn-shine" aria-hidden="true"></span>
                        <span class="like-icon" aria-hidden="true">♥</span>
                        <span class="like-count" id="feedPostDetailLikeCount">0</span>
                        <span class="like-label">{{ __('app.feed.likes') }}</span>
                    </button>
                    <form method="POST" id="feedPostDetailDeleteForm" class="post-detail-delete-form" action="" hidden onsubmit="return confirm(@js(__('app.feed.delete_confirm')))">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="post-detail-delete">{{ __('app.common.delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</dialog>

@include('partials.post-caption-edit-dialog')
