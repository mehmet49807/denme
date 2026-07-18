<script>
(function () {
    if (window.__gkInboxActionsBound) return;
    window.__gkInboxActionsBound = true;

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-inbox-clear]');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        var confirmMsg = btn.getAttribute('data-confirm') || '';
        if (confirmMsg && !window.confirm(confirmMsg)) return;

        var url = btn.getAttribute('data-clear-url');
        var csrf = csrfMeta && csrfMeta.content;
        if (!url || !csrf) {
            window.alert(btn.getAttribute('data-failed') || 'Sohbet temizlenemedi.');
            return;
        }

        btn.disabled = true;

        fetch(url, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).then(function (res) {
            if (!res.ok) throw new Error('clear failed');
            return res.json();
        }).then(function (data) {
            if (!data || data.ok !== true) throw new Error('clear failed');

            var row = btn.closest('.conversation-row');
            var username = row ? row.getAttribute('data-username') : '';
            if (row) row.remove();

            var list = document.querySelector('#inboxPollRoot .conversation-list');
            if (!list || !list.querySelector('.conversation-row')) {
                window.location.reload();
                return;
            }

            if (username) {
                var path = window.location.pathname || '';
                if (path.indexOf('/messages/' + username) !== -1) {
                    window.location.href = @json(route('messages.index'));
                }
            }
        }).catch(function () {
            btn.disabled = false;
            window.alert(btn.getAttribute('data-failed') || 'Sohbet temizlenemedi.');
        });
    });

    document.addEventListener('submit', function (e) {
        var form = e.target.closest('form[data-inbox-block]');
        if (!form) return;

        var confirmMsg = form.getAttribute('data-confirm') || '';
        if (confirmMsg && !window.confirm(confirmMsg)) {
            e.preventDefault();
            return;
        }

        var btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;
    });
})();
</script>
