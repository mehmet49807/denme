<dialog id="postCaptionEditDialog" class="post-caption-edit-dialog">
    <form method="dialog" class="post-caption-edit-inner" id="postCaptionEditForm">
        <header class="post-caption-edit-header">
            <h2 id="postCaptionEditTitle">{{ __('app.feed.edit_caption') }}</h2>
            <button type="button" class="post-detail-close" data-close-caption-edit aria-label="{{ __('app.common.close') }}">×</button>
        </header>
        <label class="post-caption-edit-label" for="postCaptionEditInput">{{ __('app.feed.caption_label') }}</label>
        <textarea
            id="postCaptionEditInput"
            class="post-caption-edit-input"
            name="caption"
            maxlength="500"
            rows="4"
            placeholder="{{ __('app.feed.caption_placeholder') }}"
        ></textarea>
        <div class="post-caption-edit-meta">
            <span class="post-caption-edit-count" id="postCaptionEditCount">0 / 500</span>
            <p class="post-caption-edit-error" id="postCaptionEditError" hidden></p>
        </div>
        <div class="post-caption-edit-actions">
            <button type="button" class="btn btn-outline btn-sm" data-close-caption-edit>{{ __('app.common.cancel') }}</button>
            <button type="submit" class="btn btn-primary btn-sm" id="postCaptionEditSave">{{ __('app.common.save') }}</button>
        </div>
    </form>
</dialog>
