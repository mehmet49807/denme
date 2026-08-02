@if(($allStoryGroups ?? collect())->isNotEmpty())
<div
    class="ig-story-viewer"
    id="igStoryViewer"
    hidden
    data-groups="{{ $allStoryGroups->toJson() }}"
    data-messages-base="{{ url('/messages') }}"
>
    <div class="ig-story-frame">
        <div class="ig-story-progress" id="igStoryProgress"></div>

        <header class="ig-story-header">
            <a href="#" id="igStoryUserLink" class="ig-story-user">
                <span class="ig-story-user-avatar" id="igStoryUserAvatar"></span>
                <span class="ig-story-user-meta">
                    <strong id="igStoryUserName"></strong>
                    <span class="ig-story-user-line" id="igStoryUserLine" hidden></span>
                    <span class="shared-time shared-time--viewer">
                        <span class="shared-time__icon" aria-hidden="true">
                            @include('partials.theme-icon', ['icon' => 'clock'])
                        </span>
                        <time class="ig-story-time shared-time__value" id="igStoryTime" data-relative-time datetime=""></time>
                    </span>
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

        <footer class="ig-story-reply" id="igStoryReply" hidden>
            <form class="ig-story-reply-form" id="igStoryReplyForm" autocomplete="off">
                <input
                    type="text"
                    id="igStoryReplyInput"
                    class="ig-story-reply-input"
                    maxlength="280"
                    placeholder="Hikâyeye yanıt yaz…"
                    enterkeyhint="send"
                >
                <button type="submit" class="ig-story-reply-send" id="igStoryReplySend">Gönder</button>
            </form>
            <p class="ig-story-reply-status" id="igStoryReplyStatus" hidden></p>
        </footer>
    </div>
</div>
@endif
