(function () {
    const syncUrl = document.querySelector('meta[name="live-sync-url"]')?.content;
    if (!syncUrl) return;

    const POLL_MS = 10000;
    const POLL_MS_PUSHER = 60000;
    let pollIntervalMs = POLL_MS;
    let feedSince = document.querySelector('meta[name="live-sync-feed-since"]')?.content || null;
    let profileSince = document.querySelector('meta[name="live-sync-profile-since"]')?.content || null;
    const profileUsername = document.querySelector('meta[name="live-sync-profile-username"]')?.content || null;
    const syncMode = document.querySelector('meta[name="live-sync-mode"]')?.content || 'auto';

    let busy = false;
    let timer = null;

    function isUsersSearchActive() {
        const page = document.querySelector('.users-browse-page');
        if (page && page.getAttribute('data-users-search')) {
            return true;
        }
        const params = new URLSearchParams(window.location.search || '');
        if ((params.get('q') || '').trim() !== '') {
            return true;
        }
        const input = document.querySelector('#users-search-q, .users-browse-search__input');
        return !!(input && String(input.value || '').trim() !== '');
    }

    function collectPostIds() {
        const ids = new Set();
        document.querySelectorAll('[data-post-id]').forEach(function (el) {
            const id = parseInt(el.dataset.postId, 10);
            if (id > 0) ids.add(id);
        });
        return Array.from(ids);
    }

    function buildQuery() {
        const params = new URLSearchParams();
        const postIds = collectPostIds();
        if (postIds.length) {
            params.set('post_ids', postIds.join(','));
        }

        const feedRoot = document.querySelector('.feed-posts:not(.profile-feed-posts)');
        if ((syncMode === 'feed' || syncMode === 'auto') && feedRoot) {
            if (feedSince) params.set('feed_since', feedSince);
            params.set('stories', '1');
        }

        const profileRoot = document.querySelector('.profile-feed-posts--grid');
        if ((syncMode === 'profile' || syncMode === 'auto') && profileRoot && profileUsername) {
            if (profileSince) params.set('profile_since', profileSince);
            params.set('profile_username', profileUsername);
        }

        const usersGrid = document.querySelector('.users-browse-grid');
        if ((syncMode === 'users' || syncMode === 'auto') && usersGrid && !isUsersSearchActive()) {
            params.set('users', '1');
        }

        if ((syncMode === 'premium' || syncMode === 'auto') && document.querySelector('.premium-page')) {
            params.set('premium', '1');
        }

        if (document.body.classList.contains('app-shell-body') && !params.toString() && collectPostIds().length === 0) {
            params.set('heartbeat', '1');
        }

        return params.toString();
    }

    function applyPostUpdate(update) {
        if (!update || !update.id) return;

        document.querySelectorAll('[data-post-id="' + update.id + '"]').forEach(function (card) {
            if (typeof update.is_liked === 'boolean') {
                card.classList.toggle('post-card--liked', update.is_liked);
            }

            const btn = card.querySelector('.like-btn[data-like-url]');
            if (btn) {
                if (typeof update.is_liked === 'boolean') {
                    btn.classList.toggle('like-btn--active', update.is_liked);
                    btn.setAttribute('aria-pressed', update.is_liked ? 'true' : 'false');
                    btn.setAttribute('aria-label', update.is_liked ? 'Beğeniyi kaldır' : 'Beğen');
                }
                const countEl = btn.querySelector('.like-count');
                if (countEl && update.likes_count != null) {
                    countEl.textContent = String(update.likes_count);
                }
            }

            const trigger = card.querySelector('[data-open-feed-post]');
            if (trigger) {
                if (typeof update.is_liked === 'boolean') {
                    trigger.dataset.isLiked = update.is_liked ? '1' : '0';
                }
                if (update.likes_count != null) {
                    trigger.dataset.likesCount = String(update.likes_count);
                }
            }
        });

        if (typeof window.__gk_syncLike === 'function' && update.like_url && typeof update.is_liked === 'boolean') {
            window.__gk_syncLike(update.like_url, update.is_liked, update.likes_count);
        }
    }

    function prependFeedPosts(html, newPosts) {
        const root = document.querySelector('.feed-posts:not(.profile-feed-posts)');
        if (!root || !html) return;

        const empty = root.querySelector('.feed-empty');
        if (empty) empty.remove();

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        const nodes = Array.from(wrapper.children);
        nodes.reverse().forEach(function (node) {
            root.insertBefore(node, root.firstChild);
        });

        if (newPosts && newPosts.length) {
            const newest = newPosts.reduce(function (latest, post) {
                if (!latest || (post.created_at && post.created_at > latest)) return post.created_at;
                return latest;
            }, feedSince);
            if (newest) feedSince = newest;
        }

        document.dispatchEvent(new CustomEvent('gk:posts-inserted'));
    }

    function prependProfilePosts(html, newPosts) {
        const root = document.querySelector('.profile-feed-posts--grid');
        if (!root || !html) return;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        const nodes = Array.from(wrapper.children);
        nodes.reverse().forEach(function (node) {
            root.insertBefore(node, root.firstChild);
        });

        if (newPosts && newPosts.length) {
            const newest = newPosts.reduce(function (latest, post) {
                if (!latest || (post.created_at && post.created_at > latest)) return post.created_at;
                return latest;
            }, profileSince);
            if (newest) profileSince = newest;
        }

        document.dispatchEvent(new CustomEvent('gk:posts-inserted'));
    }

    function replaceUsersGrid(html) {
        const root = document.querySelector('.users-browse-grid');
        if (!root || !html || isUsersSearchActive()) return;
        root.innerHTML = html;
    }

    function applyPremiumState(premium) {
        if (!premium) return;

        const snapshot = JSON.stringify(premium);
        if (window.__gk_premiumState && window.__gk_premiumState !== snapshot) {
            window.location.reload();
            return;
        }
        window.__gk_premiumState = snapshot;
    }

    async function poll() {
        if (document.hidden || busy) return;

        const query = buildQuery();
        if (!query && collectPostIds().length === 0) return;

        busy = true;

        try {
            const res = await fetch(syncUrl + (query ? '?' + query : ''), {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!res.ok) return;

            const payload = await res.json();
            if (!payload?.success || !payload.data) return;

            const data = payload.data;

            (data.post_updates || []).forEach(applyPostUpdate);

            if (data.feed_new_html) {
                prependFeedPosts(data.feed_new_html, data.feed_new_posts || []);
            }

            if (data.profile_new_html) {
                prependProfilePosts(data.profile_new_html, data.profile_new_posts || []);
            }

            if (data.users_html) {
                replaceUsersGrid(data.users_html);
            }

            if (data.stories && typeof window.__gk_refreshStories === 'function') {
                window.__gk_refreshStories(data.stories);
            }

            if (data.premium) {
                applyPremiumState(data.premium);
            }

            if (data.server_time && !feedSince && document.querySelector('.feed-posts:not(.profile-feed-posts)')) {
                feedSince = data.server_time;
            }
        } catch (err) {
            // Sessizce devam et.
        } finally {
            busy = false;
        }
    }

    function startPolling() {
        if (timer) clearInterval(timer);
        poll();
        timer = setInterval(poll, pollIntervalMs);
    }

    document.addEventListener('gk:pusher-ready', function () {
        pollIntervalMs = POLL_MS_PUSHER;
        startPolling();
    });

    document.addEventListener('gk:post-updated', function (event) {
        const detail = event.detail || {};
        if (detail.post_id) {
            applyPostUpdate({
                id: detail.post_id,
                likes_count: detail.likes_count,
                is_liked: undefined,
            });
        }
    });

    document.addEventListener('gk:feed-changed', function () {
        poll();
    });

    document.addEventListener('gk:live-update', function (event) {
        const payload = event.detail || {};
        const type = payload.type;
        const data = payload.data || {};

        if (type === 'badges' && data) {
            document.dispatchEvent(new CustomEvent('gk:badges-updated', { detail: data }));
        }

        if (type === 'notification' || type === 'message' || type === 'inbox') {
            poll();
        }

        if (typeof window.__gk_refreshBadges === 'function') {
            window.__gk_refreshBadges();
        }
    });

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) poll();
    });

    window.addEventListener('focus', poll);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startPolling);
    } else {
        startPolling();
    }

    window.__gk_liveSyncPoll = poll;
})();
