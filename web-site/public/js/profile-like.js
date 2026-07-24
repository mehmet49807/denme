(function () {
    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function labelsFor(liked, matched) {
        if (matched) {
            return { text: 'Eşleştiniz', liked: true, matched: true };
        }
        if (liked) {
            return { text: 'Beğenildi', liked: true, matched: false };
        }
        return { text: 'Beğen', liked: false, matched: false };
    }

    function applyState(form, liked, matched) {
        form.setAttribute('data-liked', liked ? '1' : '0');
        form.setAttribute('data-matched', matched ? '1' : '0');
        var btn = form.querySelector('[data-like-btn]');
        var label = form.querySelector('[data-like-label]');
        var state = labelsFor(liked, matched);
        if (btn) {
            btn.classList.toggle('is-liked', state.liked);
            btn.classList.toggle('is-matched', state.matched);
            btn.setAttribute('aria-pressed', state.liked ? 'true' : 'false');
            if (!label) {
                btn.textContent = state.text;
            }
        }
        if (label) {
            label.textContent = state.text;
        }
        if (matched && window.gkTrack) {
            window.gkTrack('match_created', { event_category: 'engagement' });
        }
    }

    function bindForm(form) {
        if (form.getAttribute('data-like-bound') === '1') {
            return;
        }
        form.setAttribute('data-like-bound', '1');
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var btn = form.querySelector('[data-like-btn]');
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
                    applyState(form, !!data.liked, !!data.matched);
                    if (data.matched && typeof window.gkToast === 'function') {
                        window.gkToast('Karşılıklı beğeni! Eşleşmelerde görünecek.', 'success');
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

    document.querySelectorAll('[data-profile-like]').forEach(bindForm);
})();
