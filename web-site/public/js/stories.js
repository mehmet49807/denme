(function () {
    const viewer = document.getElementById('igStoryViewer');
    if (!viewer) return;

    const groups = JSON.parse(viewer.dataset.groups || '[]');
    const IMAGE_MS = 5000;
    const STORY_VIDEO_MAX_MS = 15000;
    const STORAGE_KEY = 'gk_story_viewed';
    const messagesBase = (viewer.dataset.messagesBase || '/messages').replace(/\/$/, '');
    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const progressEl = document.getElementById('igStoryProgress');
    const mediaEl = document.getElementById('igStoryMedia');
    const userLinkEl = document.getElementById('igStoryUserLink');
    const userNameEl = document.getElementById('igStoryUserName');
    const userAvatarEl = document.getElementById('igStoryUserAvatar');
    const timeEl = document.getElementById('igStoryTime');
    const replyEl = document.getElementById('igStoryReply');
    const replyForm = document.getElementById('igStoryReplyForm');
    const replyInput = document.getElementById('igStoryReplyInput');
    const replyStatus = document.getElementById('igStoryReplyStatus');

    let groupIndex = 0;
    let itemIndex = 0;
    let timer = null;
    let timeoutId = null;
    let startedAt = 0;
    let pausedAt = 0;
    let paused = false;
    let activeVideo = null;
    let activeFill = null;
    let builtBarCount = -1;

    function getViewedIds() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        } catch {
            return [];
        }
    }

    function markViewed(userId) {
        const ids = getViewedIds();
        if (!ids.includes(userId)) {
            ids.push(userId);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
        }
        document.querySelectorAll('.story-item[data-user-id="' + userId + '"] .story-ring').forEach(function (ring) {
            ring.classList.remove('story-ring--unseen');
            ring.classList.add('story-ring--seen');
        });
    }

    function applyViewedRings() {
        const viewed = getViewedIds();
        document.querySelectorAll('.story-item[data-user-id]').forEach(function (item) {
            const ring = item.querySelector('.story-ring');
            if (!ring || ring.classList.contains('story-ring--own')) return;
            const userId = parseInt(item.dataset.userId, 10);
            if (viewed.includes(userId)) {
                ring.classList.add('story-ring--seen');
                ring.classList.remove('story-ring--unseen');
            }
        });
    }

    function clearTimer() {
        if (timer) {
            cancelAnimationFrame(timer);
            timer = null;
        }
        if (timeoutId) {
            clearTimeout(timeoutId);
            timeoutId = null;
        }
        if (activeFill) {
            activeFill.removeEventListener('animationend', onFillEnd);
            activeFill = null;
        }
        if (activeVideo) {
            activeVideo.pause();
            activeVideo.onended = null;
            activeVideo = null;
        }
    }

    function buildProgressBars(count) {
        if (builtBarCount === count && progressEl.childElementCount === count) {
            return;
        }
        progressEl.innerHTML = '';
        for (let i = 0; i < count; i++) {
            const bar = document.createElement('div');
            bar.className = 'ig-story-progress-bar';
            bar.innerHTML = '<span class="ig-story-progress-fill"></span>';
            progressEl.appendChild(bar);
        }
        builtBarCount = count;
    }

    function resetFills() {
        progressEl.querySelectorAll('.ig-story-progress-fill').forEach(function (fill, i) {
            fill.classList.remove('is-animating', 'is-done', 'is-paused');
            fill.style.animationDuration = '';
            if (i < itemIndex) {
                fill.classList.add('is-done');
            }
        });
    }

    function onFillEnd(e) {
        if (e.animationName !== 'igStoryFill') return;
        goNext();
    }

    function startCssProgress(durationMs) {
        resetFills();
        const fills = progressEl.querySelectorAll('.ig-story-progress-fill');
        const fill = fills[itemIndex];
        if (!fill) return;

        if (reduceMotion) {
            fill.classList.add('is-done');
            timeoutId = setTimeout(goNext, Math.min(durationMs, 1200));
            return;
        }

        activeFill = fill;
        fill.style.animationDuration = durationMs + 'ms';
        void fill.offsetWidth;
        fill.classList.add('is-animating');
        if (paused) fill.classList.add('is-paused');
        fill.addEventListener('animationend', onFillEnd);
    }

    function startImageTimer(img) {
        const begin = function () {
            startedAt = performance.now();
            startCssProgress(IMAGE_MS);
        };
        if (img.complete) {
            begin();
        } else {
            img.addEventListener('load', begin, { once: true });
            img.addEventListener('error', begin, { once: true });
        }
    }

    function startVideoTimer(video) {
        activeVideo = video;
        function beginProgress() {
            const duration = Math.min((video.duration || 10) * 1000, STORY_VIDEO_MAX_MS);
            startCssProgress(duration);
        }
        if (video.readyState >= 1) {
            beginProgress();
        } else {
            video.addEventListener('loadedmetadata', beginProgress, { once: true });
        }
        video.onended = function () {
            goNext();
        };
        video.addEventListener('timeupdate', function onCap() {
            if (video.currentTime * 1000 >= STORY_VIDEO_MAX_MS) {
                video.removeEventListener('timeupdate', onCap);
                goNext();
            }
        });
    }

    function updateReplyUi() {
        if (!replyEl) return;
        const group = groups[groupIndex];
        const own = !!group?.is_own;
        replyEl.hidden = own;
        if (replyStatus) {
            replyStatus.hidden = true;
            replyStatus.textContent = '';
        }
        if (replyInput && !own) {
            replyInput.value = '';
        }
    }

    function renderStory() {
        clearTimer();
        paused = false;
        const group = groups[groupIndex];
        const item = group.items[itemIndex];

        buildProgressBars(group.items.length);
        resetFills();

        userLinkEl.href = group.profile_url;
        userNameEl.textContent = group.username;
        timeEl.textContent = 'Şimdi';

        if (group.profile_photo_url) {
            userAvatarEl.innerHTML = '<img src="' + group.profile_photo_url + '" alt="">';
        } else {
            userAvatarEl.textContent = group.username.charAt(0).toUpperCase();
        }

        mediaEl.innerHTML = '';
        if (item.media_type === 'video') {
            const video = document.createElement('video');
            video.src = item.media_url;
            video.playsInline = true;
            video.autoplay = true;
            video.muted = false;
            mediaEl.appendChild(video);
            video.play().catch(function () {
                video.muted = true;
                video.play();
            });
            startVideoTimer(video);
        } else {
            const img = document.createElement('img');
            img.src = item.media_url;
            img.alt = 'Hikaye';
            mediaEl.appendChild(img);
            startImageTimer(img);
        }

        markViewed(group.user_id);
        updateDeleteButton();
        updateReplyUi();
    }

    function openStory(index, startItem) {
        if (!groups.length) return;
        groupIndex = index;
        itemIndex = typeof startItem === 'number' && startItem >= 0 ? startItem : 0;
        if (viewer.parentElement !== document.body) {
            document.body.appendChild(viewer);
        }
        viewer.hidden = false;
        document.body.classList.add('ig-story-open');
        renderStory();
    }

    window.gkOpenStory = openStory;

    function closeStory() {
        clearTimer();
        viewer.hidden = true;
        mediaEl.innerHTML = '';
        document.body.classList.remove('ig-story-open');
    }

    function goPrev() {
        if (itemIndex > 0) {
            itemIndex--;
            renderStory();
            return;
        }
        if (groupIndex > 0) {
            groupIndex--;
            itemIndex = groups[groupIndex].items.length - 1;
            renderStory();
        }
    }

    function goNext() {
        const group = groups[groupIndex];
        if (itemIndex < group.items.length - 1) {
            itemIndex++;
            renderStory();
            return;
        }
        if (groupIndex < groups.length - 1) {
            groupIndex++;
            itemIndex = 0;
            renderStory();
        } else {
            closeStory();
        }
    }

    function pause() {
        if (paused) return;
        paused = true;
        pausedAt = performance.now();
        if (activeFill) activeFill.classList.add('is-paused');
        if (activeVideo) activeVideo.pause();
    }

    function resume() {
        if (!paused) return;
        // Don't resume while typing a reply
        if (replyInput && document.activeElement === replyInput) return;
        paused = false;
        if (activeFill) activeFill.classList.remove('is-paused');
        if (activeVideo) {
            activeVideo.play();
        } else {
            const now = performance.now();
            startedAt += now - pausedAt;
        }
    }

    const deleteBtn = document.getElementById('igStoryDelete');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function updateDeleteButton() {
        if (!deleteBtn) return;
        const group = groups[groupIndex];
        const item = group?.items?.[itemIndex];
        if (group?.is_own && item?.id) {
            deleteBtn.hidden = false;
            deleteBtn.dataset.storyId = item.id;
        } else {
            deleteBtn.hidden = true;
            deleteBtn.dataset.storyId = '';
        }
    }

    if (deleteBtn && csrf) {
        deleteBtn.addEventListener('click', async function () {
            const storyId = deleteBtn.dataset.storyId;
            if (!storyId || !confirm('Bu hikayeyi silmek istediğinize emin misiniz?')) return;

            try {
                const res = await fetch('/stories/' + storyId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                });
                if (res.ok) {
                    window.location.reload();
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    if (replyForm && replyInput && csrf) {
        replyInput.addEventListener('focus', function () {
            pause();
        });
        replyInput.addEventListener('blur', function () {
            setTimeout(resume, 120);
        });

        replyForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const group = groups[groupIndex];
            if (!group || group.is_own) return;
            const text = replyInput.value.trim();
            if (!text) return;

            const btn = document.getElementById('igStoryReplySend');
            if (btn) btn.disabled = true;
            pause();

            try {
                const res = await fetch(messagesBase + '/' + encodeURIComponent(group.username), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        message_text: '💬 Hikayene yanıt: ' + text,
                    }),
                });
                const data = await res.json().catch(function () { return {}; });
                if (res.ok) {
                    replyInput.value = '';
                    if (replyStatus) {
                        replyStatus.hidden = false;
                        replyStatus.textContent = 'Yanıt gönderildi';
                        setTimeout(function () {
                            replyStatus.hidden = true;
                        }, 1800);
                    }
                } else if (replyStatus) {
                    replyStatus.hidden = false;
                    replyStatus.textContent = data.message || 'Gönderilemedi';
                }
            } catch (err) {
                if (replyStatus) {
                    replyStatus.hidden = false;
                    replyStatus.textContent = 'Bağlantı hatası';
                }
            } finally {
                if (btn) btn.disabled = false;
                replyInput.blur();
                resume();
            }
        });
    }

    document.querySelectorAll('.story-item[data-story-index]').forEach(function (item) {
        item.addEventListener('click', function (e) {
            if (e.target.closest('.story-add-badge')) return;
            const startItem = parseInt(item.dataset.storyItemIndex || '0', 10);
            openStory(parseInt(item.dataset.storyIndex, 10), startItem);
        });
    });

    document.querySelectorAll('.story-item--own .story-ring--own').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const item = btn.closest('.story-item--own');
            if (item && item.dataset.storyIndex !== undefined) {
                openStory(parseInt(item.dataset.storyIndex, 10));
            }
        });
    });

    document.querySelectorAll('[data-close-story]').forEach(function (el) {
        el.addEventListener('click', closeStory);
    });

    document.getElementById('igStoryTapPrev').addEventListener('click', goPrev);
    document.getElementById('igStoryTapNext').addEventListener('click', goNext);

    const stage = document.getElementById('igStoryStage');
    stage.addEventListener('mousedown', function (e) {
        if (e.target.closest('.ig-story-reply')) return;
        pause();
    });
    stage.addEventListener('mouseup', resume);
    stage.addEventListener('mouseleave', resume);
    stage.addEventListener('touchstart', function (e) {
        if (e.target.closest('.ig-story-header') || e.target.closest('.ig-story-reply')) return;
        pause();
    }, { passive: true });
    stage.addEventListener('touchend', resume);
    stage.addEventListener('touchcancel', resume);

    let touchStartY = 0;
    stage.addEventListener('touchstart', function (e) {
        touchStartY = e.touches[0].clientY;
    }, { passive: true });
    stage.addEventListener('touchend', function (e) {
        const diff = e.changedTouches[0].clientY - touchStartY;
        if (diff > 80) closeStory();
    });

    document.addEventListener('keydown', function (e) {
        if (viewer.hidden) return;
        if (document.activeElement === replyInput) return;
        if (e.key === 'Escape') closeStory();
        if (e.key === 'ArrowRight') goNext();
        if (e.key === 'ArrowLeft') goPrev();
        if (e.key === ' ') {
            e.preventDefault();
            paused ? resume() : pause();
        }
    });

    applyViewedRings();
})();
