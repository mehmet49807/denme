/**
 * Gönderi / hikâye paylaşım saatini gerçek zamanlı günceller.
 * Format: "3 dk · 14:32" — göreli süre + paylaşılan saat.
 */
(function () {
    const SELECTOR = 'time[data-relative-time], time.post-time[datetime], [data-relative-time][datetime]';
    const MONTHS_TR = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
    const TICK_MS = 15000;

    let timer = null;
    let observer = null;

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function parseDate(value) {
        if (!value) return null;
        if (value instanceof Date) {
            return Number.isNaN(value.getTime()) ? null : value;
        }
        const date = new Date(String(value));
        return Number.isNaN(date.getTime()) ? null : date;
    }

    function clockLabel(date) {
        return pad(date.getHours()) + ':' + pad(date.getMinutes());
    }

    function absoluteLabel(date) {
        return pad(date.getDate()) + '.' + pad(date.getMonth() + 1) + '.' + date.getFullYear()
            + ' ' + clockLabel(date);
    }

    function formatRelative(input, nowInput) {
        const date = parseDate(input);
        if (!date) return '';

        const now = parseDate(nowInput) || new Date();
        const diffSec = Math.max(0, Math.floor((now.getTime() - date.getTime()) / 1000));
        const clock = clockLabel(date);

        let relative;
        if (diffSec < 45) {
            relative = 'Az önce';
        } else if (diffSec < 3600) {
            relative = Math.floor(diffSec / 60) + ' dk';
        } else if (diffSec < 86400) {
            relative = Math.floor(diffSec / 3600) + ' sa';
        } else if (diffSec < 172800) {
            relative = 'Dün';
        } else if (date.getFullYear() === now.getFullYear()) {
            relative = date.getDate() + ' ' + MONTHS_TR[date.getMonth()];
        } else {
            relative = pad(date.getDate()) + '.' + pad(date.getMonth() + 1) + '.' + date.getFullYear();
        }

        return relative + ' · ' + clock;
    }

    function applyElement(el, now) {
        if (!el) return;
        const raw = el.getAttribute('datetime') || el.getAttribute('data-relative-time') || el.dataset.createdAt;
        const date = parseDate(raw);
        if (!date) return;

        const label = formatRelative(date, now);
        if (!label) return;

        if (el.textContent !== label) {
            el.textContent = label;
        }
        el.setAttribute('title', absoluteLabel(date));
        if (!el.getAttribute('datetime')) {
            el.setAttribute('datetime', date.toISOString());
        }
        el.setAttribute('data-relative-time', date.toISOString());
    }

    function refreshAll() {
        const now = new Date();
        document.querySelectorAll(SELECTOR).forEach(function (el) {
            applyElement(el, now);
        });
    }

    function bind(el, iso) {
        if (!el) return;
        if (iso) {
            el.setAttribute('datetime', iso);
            el.setAttribute('data-relative-time', iso);
        }
        applyElement(el, new Date());
    }

    function start() {
        refreshAll();
        if (timer) clearInterval(timer);
        timer = setInterval(refreshAll, TICK_MS);

        if (!observer && typeof MutationObserver !== 'undefined' && document.body) {
            observer = new MutationObserver(function (mutations) {
                let needs = false;
                for (let i = 0; i < mutations.length; i++) {
                    const nodes = mutations[i].addedNodes;
                    for (let j = 0; j < nodes.length; j++) {
                        const node = nodes[j];
                        if (node.nodeType !== 1) continue;
                        if (node.matches && node.matches(SELECTOR)) {
                            needs = true;
                            break;
                        }
                        if (node.querySelector && node.querySelector(SELECTOR)) {
                            needs = true;
                            break;
                        }
                    }
                    if (needs) break;
                }
                if (needs) refreshAll();
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    window.gkRelativeTime = {
        format: formatRelative,
        refresh: refreshAll,
        bind: bind,
        absolute: function (input) {
            const date = parseDate(input);
            return date ? absoluteLabel(date) : '';
        },
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
