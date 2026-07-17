<div class="chat-emoji-panel" id="chatEmojiPanel" hidden>
    <p class="chat-emoji-panel-title">Hızlı emoji gönder</p>
    <div class="chat-emoji-grid" role="listbox" aria-label="Emoji seç">
        @foreach(\App\Support\ChatMessageHelper::reactionEmojis() as $emoji)
        <button
            type="button"
            class="chat-emoji-btn"
            data-emoji-send="{{ $emoji }}"
            role="option"
            aria-label="{{ $emoji }} gönder"
        >{{ $emoji }}</button>
        @endforeach
    </div>
</div>
