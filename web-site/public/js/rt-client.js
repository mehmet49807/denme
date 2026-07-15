(function () {
    const key = document.querySelector('meta[name="pusher-key"]')?.content;
    const userId = document.querySelector('meta[name="auth-user-id"]')?.content;
    if (!key || !userId || typeof Pusher === 'undefined' || typeof Echo === 'undefined') {
        return;
    }

    const cluster = document.querySelector('meta[name="pusher-cluster"]')?.content || 'eu';
    const authEndpoint = document.querySelector('meta[name="pusher-auth-url"]')?.content || '/broadcasting/auth';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: key,
        cluster: cluster,
        forceTLS: true,
        authEndpoint: authEndpoint,
        auth: {
            headers: {
                'X-CSRF-TOKEN': csrf || '',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        },
    });

    Echo.private('user.' + userId)
        .listen('.live.update', function (payload) {
            document.dispatchEvent(new CustomEvent('gk:live-update', { detail: payload }));
        });

    Echo.channel('feed')
        .listen('.post.updated', function (payload) {
            document.dispatchEvent(new CustomEvent('gk:post-updated', { detail: payload }));
        })
        .listen('.feed.changed', function (payload) {
            document.dispatchEvent(new CustomEvent('gk:feed-changed', { detail: payload }));
        });

    window.__gk_pusherConnected = true;
    document.dispatchEvent(new CustomEvent('gk:pusher-ready'));
})();
