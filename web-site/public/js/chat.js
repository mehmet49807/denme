(function () {
    const i18n = window.__gk_i18n || {};
    const chatConfig = window.__gk_chat || {};
    const t = function (key, fallback) {
        return i18n[key] != null ? i18n[key] : fallback;
    };

    const messagesEl = document.getElementById('chat-messages');
    const form = document.getElementById('chatComposeForm') || document.querySelector('.chat-compose');
    const input = document.getElementById('message_text');
    const emojiToggle = document.getElementById('chatEmojiToggle');
    const emojiPanel = document.getElementById('chatEmojiPanel');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function scrollToBottom() {
        if (!messagesEl) return;
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function isEmojiOnlyText(text) {
        const trimmed = String(text).trim();
        if (!trimmed) return false;
        return !/[^\p{Extended_Pictographic}\p{Emoji_Presentation}\s]/u.test(trimmed);
    }

    function removeEmptyState() {
        if (!messagesEl) return;
        const empty = messagesEl.querySelector('.chat-empty');
        if (empty) empty.remove();
    }

    function buildBubbleBody(text, emojiOnly) {
        return '<p class="chat-bubble-text' + (emojiOnly ? ' chat-bubble-text--emoji' : '') + '">' + escapeHtml(text) + '</p>';
    }

    function buildDeleteButtonHtml(messageId) {
        if (!messageId || !chatConfig.deleteMessageUrl) return '';
        return '<button type="button" class="chat-msg-delete" data-delete-message="' + escapeHtml(String(messageId)) + '" aria-label="' + escapeHtml(t('delete', 'Sil')) + '" title="' + escapeHtml(t('delete', 'Sil')) + '">' +
            '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 7h12M9 7V5h6v2M10 11v6M14 11v6M8 7l1 12h6l1-12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
            '</button>';
    }

    async function deleteMessage(messageId, row) {
        if (!chatConfig.deleteMessageUrl || !csrf || !messageId) return false;

        try {
            const res = await fetch(chatConfig.deleteMessageUrl + '/' + encodeURIComponent(messageId), {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) return false;
            const data = await res.json();
            if (!data || data.ok !== true) return false;
            if (row) row.remove();
            if (messagesEl && !messagesEl.querySelector('.chat-msg')) {
                const empty = document.createElement('p');
                empty.className = 'chat-empty';
                empty.textContent = t('empty', 'Henüz mesaj yok.');
                messagesEl.appendChild(empty);
            }
            return true;
        } catch (e) {
            return false;
        }
    }

    function buildAvatarHtml(options) {
        const opts = options || {};
        const size = opts.size || 28;
        const style = 'width:' + size + 'px;height:' + size + 'px;';
        const inner = opts.photoUrl
            ? '<img src="' + escapeHtml(opts.photoUrl) + '" alt="" width="' + size + '" height="' + size + '" loading="lazy" decoding="async">'
            : '<span class="chat-user-avatar-fallback">' + escapeHtml(opts.initial || '?') + '</span>';

        if (opts.href) {
            return '<a href="' + escapeHtml(opts.href) + '" class="chat-user-avatar" style="' + style + '">' + inner + '</a>';
        }

        return '<span class="chat-user-avatar" style="' + style + '" aria-hidden="true">' + inner + '</span>';
    }

    function appendSentBubble(text, options) {
        if (!messagesEl) return null;

        const opts = options || {};
        const emojiOnly = opts.emojiOnly != null ? opts.emojiOnly : isEmojiOnlyText(text);
        const wrap = document.createElement('div');
        wrap.className = 'chat-msg chat-msg--sent';
        if (opts.pending) wrap.dataset.pending = 'true';
        if (opts.messageId) wrap.dataset.messageId = String(opts.messageId);

        wrap.innerHTML =
            '<div class="chat-msg-body">' +
            '<div class="chat-bubble chat-bubble--sent' + (emojiOnly ? ' chat-bubble--emoji' : '') + '">' +
            buildBubbleBody(text, emojiOnly) +
            '<time class="chat-bubble-time" datetime="' + escapeHtml(opts.datetime || '') + '">' +
            escapeHtml(opts.timeLabel || t('now', 'Now')) +
            '</time></div>' +
            buildDeleteButtonHtml(opts.messageId) +
            '</div>' +
            buildAvatarHtml({
                photoUrl: chatConfig.viewerPhotoUrl || '',
                initial: chatConfig.viewerInitial || 'S',
                href: chatConfig.viewerProfileUrl || '',
                size: 28,
            });

        removeEmptyState();
        messagesEl.appendChild(wrap);
        scrollToBottom();
        return wrap;
    }

    function appendReceivedBubble(msg) {
        if (!messagesEl || !msg || !msg.message_text) return null;

        const emojiOnly = msg.is_emoji_only != null ? msg.is_emoji_only : isEmojiOnlyText(msg.message_text);
        const wrap = document.createElement('div');
        wrap.className = 'chat-msg chat-msg--received';
        wrap.dataset.messageId = String(msg.id);

        wrap.innerHTML =
            buildAvatarHtml({
                photoUrl: chatConfig.partnerPhotoUrl || '',
                initial: chatConfig.partnerInitial || (chatConfig.partnerName || '').slice(0, 1).toUpperCase(),
                href: chatConfig.partnerProfileUrl || '',
                size: 28,
            }) +
            '<div class="chat-msg-body">' +
            '<div class="chat-bubble chat-bubble--received' + (emojiOnly ? ' chat-bubble--emoji' : '') + '">' +
            buildBubbleBody(msg.message_text, emojiOnly) +
            '<time class="chat-bubble-time" datetime="' + escapeHtml(msg.created_at || '') + '">' +
            escapeHtml(msg.created_at_display || t('now', 'Now')) +
            '</time></div>' +
            buildDeleteButtonHtml(msg.id) +
            '</div>';

        removeEmptyState();
        messagesEl.appendChild(wrap);
        scrollToBottom();
        return wrap;
    }

    function hasMessageId(id) {
        if (!messagesEl || !id) return false;
        return !!messagesEl.querySelector('[data-message-id="' + id + '"]');
    }

    function getLastMessageId() {
        let max = parseInt(String(chatConfig.lastMessageId || 0), 10) || 0;
        if (!messagesEl) return max;

        messagesEl.querySelectorAll('[data-message-id]').forEach(function (el) {
            const id = parseInt(el.dataset.messageId, 10);
            if (id > max) max = id;
        });

        return max;
    }

    function applyMessageMeta(wrap, message) {
        if (!wrap || !message) return;
        if (message.id) wrap.dataset.messageId = String(message.id);
        delete wrap.dataset.pending;

        const timeEl = wrap.querySelector('.chat-bubble-time');
        if (timeEl) {
            timeEl.textContent = message.created_at_display || t('now', 'Now');
            timeEl.setAttribute('datetime', message.created_at || '');
        }

        if (message.id && chatConfig.deleteMessageUrl && !wrap.querySelector('[data-delete-message]')) {
            const body = wrap.querySelector('.chat-msg-body');
            if (body) {
                body.insertAdjacentHTML('beforeend', buildDeleteButtonHtml(message.id));
            }
        }
    }

    function markBubbleFailed(wrap) {
        if (!wrap) return;
        wrap.classList.add('chat-msg--failed');
        const bubble = wrap.querySelector('.chat-bubble');
        if (bubble) bubble.classList.add('chat-bubble--failed');
    }

    function popEmojiBtn(btn) {
        if (!btn) return;
        btn.classList.add('chat-emoji-btn--pop');
        window.setTimeout(function () {
            btn.classList.remove('chat-emoji-btn--pop');
        }, 140);
    }

    function readJsonErrorMessage(data, fallback) {
        if (!data) return fallback;
        if (data.errors && data.errors.message_text && data.errors.message_text[0]) {
            return data.errors.message_text[0];
        }
        if (typeof data.message === 'string' && data.message) {
            return data.message;
        }
        return fallback;
    }

    async function sendMessage(text, options) {
        if (!form || !csrf || !text) {
            return { ok: false, message: t('failed', 'Could not send message.') };
        }

        const formData = new FormData(form);
        formData.set('message_text', text);

        let res;
        try {
            res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                redirect: 'manual',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        } catch (e) {
            return { ok: false, message: t('connectionError', 'Connection error. Please try again.') };
        }

        const contentType = res.headers.get('content-type') || '';
        const isJson = contentType.includes('application/json');

        if (res.type === 'opaqueredirect' || (res.status >= 300 && res.status < 400)) {
            return { ok: false, message: t('failed', 'Could not send message.') };
        }

        if (!res.ok) {
            let message = t('failed', 'Could not send message.');
            if (isJson) {
                try {
                    const data = await res.json();
                    message = readJsonErrorMessage(data, message);
                } catch (e) {
                    /* ignore parse errors */
                }
            }
            return { ok: false, message: message };
        }

        if (!isJson) {
            return { ok: false, message: t('failed', 'Could not send message.') };
        }

        try {
            const data = await res.json();
            if (!data || data.ok !== true) {
                return {
                    ok: false,
                    message: readJsonErrorMessage(data, t('failed', 'Could not send message.')),
                };
            }
            return { ok: true, message: data.message || null };
        } catch (e) {
            return { ok: false, message: t('failed', 'Could not send message.') };
        }
    }

    scrollToBottom();

    const typingEl = document.getElementById('chatTyping');
    const typingLabelEl = document.getElementById('chatTypingLabel');
    let typingPollTimer = null;
    let typingPingTimer = null;
    let lastTypingPingAt = 0;

    function setPartnerTyping(visible) {
        if (!typingEl) return;
        if (visible) {
            if (typingLabelEl) {
                typingLabelEl.textContent = t('typing', 'Typing…');
            }
            typingEl.hidden = false;
            scrollToBottom();
            return;
        }
        typingEl.hidden = true;
    }

    function setSelfTyping(active) {
        if (form) form.classList.toggle('chat-compose--active', active);
        if (input) input.classList.toggle('chat-input--typing', active);
    }

    async function pingTyping() {
        if (!chatConfig.typingPingUrl || !csrf) return;
        const now = Date.now();
        if (now - lastTypingPingAt < 1800) return;
        lastTypingPingAt = now;

        try {
            await fetch(chatConfig.typingPingUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        } catch (e) {
            /* ignore */
        }
    }

    async function pollTypingStatus() {
        if (!chatConfig.typingStatusUrl) return;

        try {
            const res = await fetch(chatConfig.typingStatusUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;
            const data = await res.json();
            setPartnerTyping(!!data.typing);
        } catch (e) {
            /* ignore */
        }
    }

    async function pollMessages() {
        if (!chatConfig.messagesPollUrl || document.hidden) return;

        const after = getLastMessageId();

        try {
            const res = await fetch(chatConfig.messagesPollUrl + '?after=' + encodeURIComponent(after), {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;

            const data = await res.json();
            if (!data.ok || !Array.isArray(data.messages) || !data.messages.length) return;

            let addedIncoming = false;

            data.messages.forEach(function (msg) {
                if (!msg.id || hasMessageId(msg.id)) return;

                if (msg.sender_id === chatConfig.viewerId) {
                    appendSentBubble(msg.message_text, {
                        messageId: msg.id,
                        datetime: msg.created_at || '',
                        timeLabel: msg.created_at_display || t('now', 'Now'),
                        emojiOnly: msg.is_emoji_only,
                    });
                    return;
                }

                appendReceivedBubble(msg);
                addedIncoming = true;
            });

            if (addedIncoming) {
                setPartnerTyping(false);
                if (typeof window.__gk_refreshBadges === 'function') {
                    window.__gk_refreshBadges();
                }
            }
        } catch (e) {
            /* ignore */
        }
    }

    let messagesPollTimer = null;

    function startMessagePolling() {
        if (!chatConfig.messagesPollUrl) return;
        pollMessages();
        messagesPollTimer = window.setInterval(pollMessages, 5000);
    }

    function scheduleTypingPing() {
        const hasText = input && input.value.trim().length > 0;
        setSelfTyping(hasText);
        if (!hasText) return;

        pingTyping();
        if (typingPingTimer) window.clearTimeout(typingPingTimer);
        typingPingTimer = window.setTimeout(function () {
            if (input && input.value.trim().length > 0) {
                pingTyping();
            }
        }, 2200);
    }

    if (chatConfig.typingStatusUrl) {
        pollTypingStatus();
        typingPollTimer = window.setInterval(pollTypingStatus, 2500);
    }

    startMessagePolling();

    if (messagesEl) {
        messagesEl.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-delete-message]');
            if (!btn) return;
            const messageId = btn.getAttribute('data-delete-message');
            const row = btn.closest('.chat-msg');
            if (!messageId || !row) return;
            const confirmMsg = t('deleteConfirm', 'Bu mesajı silmek istediğinize emin misiniz?');
            if (!window.confirm(confirmMsg)) return;
            btn.disabled = true;
            deleteMessage(messageId, row).then(function (ok) {
                if (!ok) {
                    btn.disabled = false;
                    window.alert(t('deleteFailed', 'Mesaj silinemedi.'));
                }
            });
        });
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            setSelfTyping(false);
            return;
        }
        if (chatConfig.typingStatusUrl) pollTypingStatus();
        pollMessages();
    });

    window.addEventListener('beforeunload', function () {
        if (typingPollTimer) window.clearInterval(typingPollTimer);
        if (messagesPollTimer) window.clearInterval(messagesPollTimer);
    });

    if (input && chatConfig.typingPingUrl) {
        input.addEventListener('input', scheduleTypingPing);
        input.addEventListener('blur', function () {
            setSelfTyping(false);
        });
        input.addEventListener('focus', function () {
            if (input.value.trim().length > 0) scheduleTypingPing();
        });
    }

    if (emojiToggle && emojiPanel) {
        emojiToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const open = emojiPanel.hidden;
            emojiPanel.hidden = !open;
            emojiToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (emojiPanel.hidden) return;
            if (emojiPanel.contains(e.target) || emojiToggle.contains(e.target)) return;
            emojiPanel.hidden = true;
            emojiToggle.setAttribute('aria-expanded', 'false');
        });
    }

    if (emojiPanel && form && csrf) {
        emojiPanel.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-emoji-send]');
            if (!btn || btn.disabled) return;

            const emoji = btn.dataset.emojiSend;
            if (!emoji) return;

            e.preventDefault();
            popEmojiBtn(btn);

            const wrap = appendSentBubble(emoji, { pending: true, emojiOnly: true });
            if (input) input.value = '';

            btn.disabled = true;

            sendMessage(emoji, { emojiOnly: true })
                .then(function (result) {
                    if (result.ok) {
                        if (wrap) applyMessageMeta(wrap, result.message);
                        return;
                    }

                    markBubbleFailed(wrap);
                    window.alert(result.message || t('emojiFailed', 'Could not send emoji.'));
                })
                .catch(function () {
                    markBubbleFailed(wrap);
                    window.alert(t('connectionError', 'Connection error. Please try again.'));
                })
                .finally(function () {
                    btn.disabled = false;
                });
        });
    }

    document.querySelectorAll('[data-emoji-insert]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const emoji = btn.dataset.emojiInsert;
            if (!emoji || !input) return;

            const start = input.selectionStart ?? input.value.length;
            const end = input.selectionEnd ?? input.value.length;
            input.value = input.value.slice(0, start) + emoji + input.value.slice(end);
            input.focus();
            input.selectionStart = input.selectionEnd = start + emoji.length;
            popEmojiBtn(btn);
        });
    });

    if (form && csrf && input) {
        form.addEventListener('submit', function (e) {
            const text = input.value.trim();
            if (!text) return;

            e.preventDefault();

            const submitBtn = form.querySelector('.chat-send');
            if (submitBtn) submitBtn.disabled = true;

            const wrap = appendSentBubble(text, { pending: true });
            const savedText = text;
            input.value = '';
            setSelfTyping(false);

            sendMessage(savedText)
                .then(function (result) {
                    if (result.ok) {
                        if (wrap) applyMessageMeta(wrap, result.message);
                        return;
                    }

                    markBubbleFailed(wrap);
                    input.value = savedText;
                    window.alert(result.message || t('failed', 'Could not send message.'));
                })
                .catch(function () {
                    markBubbleFailed(wrap);
                    input.value = savedText;
                    window.alert(t('connectionError', 'Connection error. Please try again.'));
                })
                .finally(function () {
                    if (submitBtn) submitBtn.disabled = false;
                    input.focus();
                });
        });
    }
})();
