(function () {
    const badgesUrl = document.querySelector('meta[name="badges-url"]')?.content;
    const inboxPollUrl = document.querySelector('meta[name="inbox-poll-url"]')?.content;
    const notificationsPollUrl = document.querySelector('meta[name="notifications-poll-url"]')?.content;
    let notificationsPollSince = document.querySelector('meta[name="notifications-poll-since"]')?.content || null;

    const POLL_MS = 10000;
    const NOTIFICATION_RETENTION_MS = 24 * 60 * 60 * 1000;
    let badgesTimer = null;
    let inboxTimer = null;
    let notificationsTimer = null;
    let inboxBusy = false;
    let notificationsBusy = false;

    function setBadge(link, count, badgeClass) {
        let badge = link.querySelector('.' + badgeClass);
        const display = count > 99 ? '99+' : String(count);

        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = badgeClass;
                link.appendChild(badge);
            }
            badge.textContent = display;
            badge.hidden = false;
        } else if (badge) {
            badge.remove();
        }
    }

    function updateBadges(data) {
        if (!data) return;

        document.querySelectorAll('[data-nav-badge="notifications"]').forEach(function (link) {
            const badgeClass = link.closest('.site-nav') ? 'site-nav-badge' : 'sidebar-nav-badge';
            setBadge(link, data.unread_notifications || 0, badgeClass);
        });

        document.querySelectorAll('[data-nav-badge="messages"]').forEach(function (link) {
            const badgeClass = link.closest('.site-nav') ? 'site-nav-badge' : 'sidebar-nav-badge';
            setBadge(link, data.unread_messages || 0, badgeClass);
        });

        document.dispatchEvent(new CustomEvent('gk:badges-updated', { detail: data }));
    }

    async function pollBadges() {
        if (!badgesUrl || document.hidden || inboxPollUrl || notificationsPollUrl) return;

        try {
            const res = await fetch(badgesUrl, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!res.ok) return;

            const payload = await res.json();
            if (payload?.success) {
                updateBadges(payload.data);
            }
        } catch (err) {
            // Sessizce devam et.
        }
    }

    async function pollInbox() {
        if (!inboxPollUrl || document.hidden || inboxBusy) return;

        const root = document.getElementById('inboxPollRoot');
        if (!root) return;

        inboxBusy = true;

        try {
            const res = await fetch(inboxPollUrl, {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!res.ok) return;

            const payload = await res.json();
            if (!payload?.success) return;

            if (typeof payload.data?.html === 'string') {
                root.innerHTML = payload.data.html;
            }

            if (payload.data) {
                updateBadges(payload.data);
            }
        } catch (err) {
            // Sessizce devam et.
        } finally {
            inboxBusy = false;
        }
    }

    function pruneExpiredNotifications() {
        const list = document.getElementById('notificationList');
        if (!list) return;

        const cutoff = Date.now() - NOTIFICATION_RETENTION_MS;
        let removed = false;

        list.querySelectorAll('.notification-item[data-notification-ts]').forEach(function (item) {
            const ts = parseInt(item.getAttribute('data-notification-ts'), 10);
            if (ts > 0 && ts * 1000 < cutoff) {
                item.remove();
                removed = true;
            }
        });

        if (!removed || list.children.length > 0) return;

        list.remove();

        const page = document.querySelector('.notifications-page');
        if (!page || page.querySelector('.notifications-empty')) return;

        const empty = document.createElement('div');
        empty.className = 'notifications-empty';
        empty.innerHTML = '<p>Henüz bildiriminiz yok.</p>';
        page.appendChild(empty);
    }

    async function pollNotifications() {
        if (!notificationsPollUrl || document.hidden || notificationsBusy) return;

        notificationsBusy = true;

        try {
            const url = new URL(notificationsPollUrl, window.location.origin);
            if (notificationsPollSince) {
                url.searchParams.set('since', notificationsPollSince);
            }

            const res = await fetch(url.toString(), {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!res.ok) return;

            const payload = await res.json();
            if (!payload?.success) return;

            if (payload.data?.latest_at) {
                notificationsPollSince = payload.data.latest_at;
            }

            if (payload.data?.html) {
                const empty = document.querySelector('.notifications-empty');
                if (empty) {
                    empty.remove();
                }

                let list = document.getElementById('notificationList');
                if (!list) {
                    const page = document.querySelector('.notifications-page');
                    if (page) {
                        list = document.createElement('ul');
                        list.className = 'notification-list';
                        list.id = 'notificationList';
                        page.appendChild(list);
                    }
                }

                if (list) {
                    list.insertAdjacentHTML('afterbegin', payload.data.html);
                }
            }

            if (payload.data) {
                updateBadges(payload.data);
            }

            pruneExpiredNotifications();
        } catch (err) {
            // Sessizce devam et.
        } finally {
            notificationsBusy = false;
        }
    }

    function refreshAll() {
        pollBadges();
        pollInbox();
        pollNotifications();
    }

    function startPolling() {
        refreshAll();

        if (badgesUrl && !inboxPollUrl && !notificationsPollUrl && !badgesTimer) {
            badgesTimer = window.setInterval(pollBadges, POLL_MS);
        }

        if (inboxPollUrl && !inboxTimer) {
            inboxTimer = window.setInterval(pollInbox, POLL_MS);
        }

        if (notificationsPollUrl && !notificationsTimer) {
            notificationsTimer = window.setInterval(pollNotifications, POLL_MS);
            pruneExpiredNotifications();
        }
    }

    function onVisible() {
        if (document.hidden) return;
        refreshAll();
    }

    window.__gk_refreshBadges = refreshAll;

    if (badgesUrl || inboxPollUrl || notificationsPollUrl) {
        startPolling();
        document.addEventListener('visibilitychange', onVisible);
        window.addEventListener('focus', onVisible);
    }
})();
