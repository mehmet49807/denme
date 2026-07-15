(function () {
    const viewer = document.getElementById('igStoryViewer');
    if (!viewer) return;

    const groups = JSON.parse(viewer.dataset.groups || '[]');
    const IMAGE_MS = 5000;
    const STORY_VIDEO_MAX_MS = 15000;
    const STORAGE_KEY = 'gk_story_viewed';

    const progressEl = document.getElementById('igStoryProgress');
    const mediaEl = document.getElementById('igStoryMedia');
    const userLinkEl = document.getElementById('igStoryUserLink');
    const userNameEl = document.getElementById('igStoryUserName');
    const userAvatarEl = document.getElementById('igStoryUserAvatar');
    const timeEl = document.getElementById('igStoryTime');

    let groupIndex = 0;
    let itemIndex = 0;
    let timer = null;
    let startedAt = 0;
    let pausedAt = 0;
    let paused = false;
    let activeVideo = null;

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
        if (activeVideo) {
            activeVideo.pause();
            activeVideo.onended = null;
            activeVideo = null;
        }
    }

    function buildProgressBars(count) {
        progressEl.innerHTML = '';
        for (let i = 0; i < count; i++) {
            const bar = document.createElement('div');
            bar.className = 'ig-story-progress-bar';
            bar.innerHTML = '<span class="ig-story-progress-fill"></span>';
            progressEl.appendChild(bar);
        }
    }

    function updateProgressBars(elapsed, duration) {
        const bars = progressEl.querySelectorAll('.ig-story-progress-bar');
        bars.forEach(function (bar, i) {
            const fill = bar.querySelector('.ig-story-progress-fill');
            if (i < itemIndex) {
                fill.style.width = '100%';
            } else if (i > itemIndex) {
                fill.style.width = '0%';
            } else {
                fill.style.width = Math.min(100, (elapsed / duration) * 100) + '%';
            }
        });
    }

    function startImageTimer() {
        startedAt = performance.now();
        function tick(now) {
            if (paused) {
                timer = requestAnimationFrame(tick);
                return;
            }
            const elapsed = now - startedAt;
            updateProgressBars(elapsed, IMAGE_MS);
            if (elapsed >= IMAGE_MS) {
                goNext();
                return;
            }
            timer = requestAnimationFrame(tick);
        }
        timer = requestAnimationFrame(tick);
    }

    function startVideoTimer(video) {
        activeVideo = video;
        function tick() {
            if (paused || !activeVideo) return;
            const duration = Math.min((activeVideo.duration || 10) * 1000, STORY_VIDEO_MAX_MS);
            const elapsed = Math.min(activeVideo.currentTime * 1000, duration);
            updateProgressBars(elapsed, duration);
            if (!activeVideo.paused && !activeVideo.ended && activeVideo.currentTime * 1000 < STORY_VIDEO_MAX_MS) {
                timer = requestAnimationFrame(tick);
            }
        }
        video.onended = function () {
            goNext();
        };
        video.onplay = function () {
            timer = requestAnimationFrame(tick);
        };
        video.addEventListener('timeupdate', function onCap() {
            if (video.currentTime * 1000 >= STORY_VIDEO_MAX_MS) {
                video.removeEventListener('timeupdate', onCap);
                goNext();
            }
        });
    }

    function renderStory() {
        clearTimer();
        const group = groups[groupIndex];
        const item = group.items[itemIndex];

        buildProgressBars(group.items.length);
        updateProgressBars(0, IMAGE_MS);

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
            startImageTimer();
        }

        markViewed(group.user_id);
        updateDeleteButton();
    }

    function openStory(index, startItem) {
        if (!groups.length) return;
        groupIndex = index;
        itemIndex = typeof startItem === 'number' && startItem >= 0 ? startItem : 0;
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
        if (activeVideo) activeVideo.pause();
    }

    function resume() {
        if (!paused) return;
        paused = false;
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
    stage.addEventListener('mousedown', pause);
    stage.addEventListener('mouseup', resume);
    stage.addEventListener('mouseleave', resume);
    stage.addEventListener('touchstart', function (e) {
        if (e.target.closest('.ig-story-header')) return;
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
