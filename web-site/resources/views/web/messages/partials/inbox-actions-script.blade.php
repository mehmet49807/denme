<script>
(function () {
    if (window.__gkInboxActionsBound) return;
    window.__gkInboxActionsBound = true;

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var ACTION_W = 88;
    var OPEN_THRESHOLD = 44;
    var activeRow = null;
    var drag = null;

    function hintEl() {
        return document.getElementById('inboxSwipeHint');
    }

    function showSwipeHint() {
        var el = hintEl();
        if (!el) return;
        try {
            if (window.localStorage && localStorage.getItem('gk_inbox_swipe_hint_v2') === '1') {
                el.hidden = true;
                return;
            }
        } catch (err) {}
        el.hidden = false;
    }

    function dismissSwipeHint() {
        var el = hintEl();
        if (el) el.hidden = true;
        try {
            if (window.localStorage) localStorage.setItem('gk_inbox_swipe_hint_v2', '1');
        } catch (err) {}
    }

    function frontOf(row) {
        return row ? row.querySelector('.conversation-swipe-front') : null;
    }

    function setOffset(row, x, animate) {
        var front = frontOf(row);
        if (!front) return;
        var clamped = Math.max(-ACTION_W, Math.min(ACTION_W, x || 0));
        front.style.transition = animate ? 'transform 0.22s ease' : 'none';
        front.style.transform = 'translate3d(' + clamped + 'px,0,0)';
        // Sağa kaydır (+X) → Engelle (sol aksiyon)
        // Sola kaydır (−X) → Sil (sağ aksiyon)
        row.classList.toggle('is-swiped-block', clamped > 8);
        row.classList.toggle('is-swiped-delete', clamped < -8);
        row.dataset.swipeX = String(clamped);
    }

    function closeRow(row, animate) {
        if (!row) return;
        setOffset(row, 0, animate !== false);
        if (activeRow === row) activeRow = null;
    }

    function closeOthers(except) {
        document.querySelectorAll('[data-swipe-row].is-swiped-delete, [data-swipe-row].is-swiped-block').forEach(function (row) {
            if (row !== except) closeRow(row, true);
        });
    }

    function snapRow(row) {
        var x = parseFloat(row.dataset.swipeX || '0') || 0;
        if (x >= OPEN_THRESHOLD) {
            setOffset(row, ACTION_W, true);
            activeRow = row;
            dismissSwipeHint();
        } else if (x <= -OPEN_THRESHOLD) {
            setOffset(row, -ACTION_W, true);
            activeRow = row;
            dismissSwipeHint();
        } else {
            closeRow(row, true);
        }
    }

    function playDemoPeek() {
        try {
            if (window.localStorage && localStorage.getItem('gk_inbox_swipe_demo_v2') === '1') return;
        } catch (err) {}

        var first = document.querySelector('#inboxPollRoot [data-swipe-row]');
        if (!first || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        setTimeout(function () {
            // Sağa → Engelle, sonra sola → Sil
            setOffset(first, ACTION_W * 0.55, true);
            setTimeout(function () {
                setOffset(first, -ACTION_W * 0.55, true);
                setTimeout(function () {
                    closeRow(first, true);
                    try {
                        if (window.localStorage) localStorage.setItem('gk_inbox_swipe_demo_v2', '1');
                    } catch (err2) {}
                }, 420);
            }, 480);
        }, 500);
    }

    showSwipeHint();
    playDemoPeek();

    document.addEventListener('click', function (e) {
        var dismiss = e.target.closest('[data-inbox-swipe-hint-dismiss]');
        if (dismiss) {
            e.preventDefault();
            dismissSwipeHint();
            return;
        }

        var btn = e.target.closest('[data-inbox-clear]');
        if (btn) {
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
                if (activeRow === row) activeRow = null;

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
            return;
        }

        var openRow = e.target.closest('[data-swipe-row]');
        if (!openRow && activeRow) {
            closeRow(activeRow, true);
        }
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
        dismissSwipeHint();
    });

    document.addEventListener('pointerdown', function (e) {
        if (e.pointerType === 'mouse' && e.button !== 0) return;
        var row = e.target.closest('[data-swipe-row]');
        if (!row || e.target.closest('.conversation-swipe-rail')) return;

        closeOthers(row);
        var startX = e.clientX;
        var startY = e.clientY;
        var base = parseFloat(row.dataset.swipeX || '0') || 0;
        drag = {
            row: row,
            pointerId: e.pointerId,
            startX: startX,
            startY: startY,
            base: base,
            moved: false,
            locked: false,
            axis: null,
        };
    }, { passive: true });

    document.addEventListener('pointermove', function (e) {
        if (!drag || e.pointerId !== drag.pointerId) return;

        var dx = e.clientX - drag.startX;
        var dy = e.clientY - drag.startY;

        if (!drag.axis) {
            if (Math.abs(dx) < 6 && Math.abs(dy) < 6) return;
            drag.axis = Math.abs(dx) > Math.abs(dy) ? 'x' : 'y';
            if (drag.axis === 'y') {
                drag = null;
                return;
            }
            drag.locked = true;
            try { drag.row.setPointerCapture(e.pointerId); } catch (err) {}
        }

        if (drag.axis !== 'x') return;

        e.preventDefault();
        drag.moved = Math.abs(dx) > 4;
        setOffset(drag.row, drag.base + dx, false);
    }, { passive: false });

    function endDrag(e) {
        if (!drag || (e && e.pointerId !== drag.pointerId)) return;
        var row = drag.row;
        var moved = drag.moved;
        drag = null;
        snapRow(row);
        if (moved) {
            row.dataset.suppressClick = '1';
            setTimeout(function () { delete row.dataset.suppressClick; }, 280);
        }
    }

    document.addEventListener('pointerup', endDrag);
    document.addEventListener('pointercancel', endDrag);

    document.addEventListener('click', function (e) {
        var link = e.target.closest('.conversation-item');
        if (!link) return;
        var row = link.closest('[data-swipe-row]');
        if (!row) return;
        var x = parseFloat(row.dataset.swipeX || '0') || 0;
        if (row.dataset.suppressClick === '1' || Math.abs(x) > 8) {
            e.preventDefault();
            e.stopPropagation();
            if (Math.abs(x) > 8) closeRow(row, true);
        }
    }, true);
})();
</script>
