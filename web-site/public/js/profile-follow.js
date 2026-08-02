/* profile follow toggle — browse + profil */
(function () {
    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    var ICON_PLUS = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 19c0-2.8 2.5-5 5.5-5s5.5 2.2 5.5 5"/><path d="M17 9v6"/><path d="M14 12h6"/></svg>';
    var ICON_CHECK = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M3.5 19c0-2.8 2.5-5 5.5-5s5.5 2.2 5.5 5"/><path d="M15 12.5l2 2 4-4.5"/></svg>';

    function applyState(form, following) {
        form.setAttribute('data-following', following ? '1' : '0');
        var btn = form.querySelector('[data-follow-btn]');
        var label = form.querySelector('[data-follow-label]');
        var icon = form.querySelector('.profile-action-icon--follow');
        var text = following ? 'Takip ediliyor' : 'Takip et';
        if (btn) {
            btn.classList.toggle('is-following', !!following);
            btn.setAttribute('aria-pressed', following ? 'true' : 'false');
            btn.setAttribute('title', text);
            btn.setAttribute('aria-label', text);
        }
        if (label) {
            label.textContent = text;
        }
        if (icon) {
            icon.innerHTML = following ? ICON_CHECK : ICON_PLUS;
        }
    }

    function bindForm(form) {
        if (form.getAttribute('data-follow-bound') === '1') {
            return;
        }
        form.setAttribute('data-follow-bound', '1');
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var btn = form.querySelector('[data-follow-btn]');
            if (btn) {
                btn.disabled = true;
            }
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
                credentials: 'same-origin',
            })
                .then(function (res) { return res.json().catch(function () { return null; }); })
                .then(function (data) {
                    if (!data || !data.ok) {
                        form.submit();
                        return;
                    }
                    applyState(form, !!data.following);
                    if (data.following && data.follow_back && typeof window.gkToast === 'function') {
                        window.gkToast('Karşılıklı takip!', 'success');
                    }
                })
                .catch(function () {
                    form.submit();
                })
                .finally(function () {
                    if (btn) {
                        btn.disabled = false;
                    }
                });
        });
    }

    function bindAll(root) {
        (root || document).querySelectorAll('[data-profile-follow]').forEach(bindForm);
    }

    bindAll();
    window.__gk_bindProfileFollow = bindAll;
})();
