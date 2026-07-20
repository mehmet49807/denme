(function () {
    'use strict';

    var DISMISS_KEY = 'gk_fcm_web_dismissed';
    var PROMPT_ID = 'gkFcmWebPrompt';

    function ready() {
        return typeof window.GkPush !== 'undefined'
            && 'Notification' in window
            && 'serviceWorker' in navigator
            && window.isSecureContext;
    }

    function loadConfig() {
        return fetch('/firebase-config.json', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        }).then(function (r) { return r.json(); });
    }

    function ensureFirebase() {
        if (window.firebase && window.firebase.messaging) {
            return Promise.resolve();
        }
        return new Promise(function (resolve, reject) {
            var a = document.createElement('script');
            a.src = 'https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js';
            a.onload = function () {
                var b = document.createElement('script');
                b.src = 'https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js';
                b.onload = function () { resolve(); };
                b.onerror = reject;
                document.head.appendChild(b);
            };
            a.onerror = reject;
            document.head.appendChild(a);
        });
    }

    function registerSw() {
        return navigator.serviceWorker.register('/firebase-messaging-sw.js', { scope: '/' });
    }

    function saveToken(token) {
        if (!window.GkPush || !token) return Promise.resolve({ ok: false });
        return window.GkPush.register(token, 'web');
    }

    function getToken(cfg) {
        return ensureFirebase().then(function () {
            if (!firebase.apps.length) {
                firebase.initializeApp({
                    apiKey: cfg.apiKey,
                    authDomain: cfg.authDomain,
                    projectId: cfg.projectId,
                    storageBucket: cfg.storageBucket,
                    messagingSenderId: cfg.messagingSenderId,
                    appId: cfg.appId
                });
            }
            return registerSw().then(function (reg) {
                var messaging = firebase.messaging();
                return messaging.getToken({
                    vapidKey: cfg.vapidKey,
                    serviceWorkerRegistration: reg
                });
            });
        });
    }

    function hidePrompt() {
        var el = document.getElementById(PROMPT_ID);
        if (el) el.remove();
    }

    function showPrompt(onAllow, onLater) {
        if (document.getElementById(PROMPT_ID)) return;
        try {
            if (localStorage.getItem(DISMISS_KEY) === '1') return;
        } catch (e) {}

        var el = document.createElement('div');
        el.id = PROMPT_ID;
        el.className = 'gk-fcm-prompt';
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-label', 'Bildirim izni');
        el.innerHTML =
            '<div class="gk-fcm-prompt__card">' +
            '<div class="gk-fcm-prompt__text">' +
            '<strong>Tarayıcı bildirimleri</strong>' +
            '<p>Yeni mesaj ve duyuruları tarayıcıdan anında alın.</p>' +
            '</div>' +
            '<div class="gk-fcm-prompt__actions">' +
            '<button type="button" class="gk-fcm-prompt__btn gk-fcm-prompt__btn--ghost" data-fcm-later>Şimdi değil</button>' +
            '<button type="button" class="gk-fcm-prompt__btn gk-fcm-prompt__btn--primary" data-fcm-allow>İzin ver</button>' +
            '</div></div>';
        document.body.appendChild(el);
        el.querySelector('[data-fcm-allow]').addEventListener('click', onAllow);
        el.querySelector('[data-fcm-later]').addEventListener('click', function () {
            try { localStorage.setItem(DISMISS_KEY, '1'); } catch (e) {}
            hidePrompt();
            if (onLater) onLater();
        });
    }

    function injectStyles() {
        if (document.getElementById('gkFcmPromptStyle')) return;
        var s = document.createElement('style');
        s.id = 'gkFcmPromptStyle';
        s.textContent =
            '.gk-fcm-prompt{position:fixed;left:1rem;right:1rem;bottom:1rem;z-index:12000;display:flex;justify-content:center;pointer-events:none}' +
            '.gk-fcm-prompt__card{pointer-events:auto;max-width:28rem;width:100%;display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;justify-content:space-between;padding:.9rem 1rem;border-radius:16px;background:rgba(255,255,255,.96);border:1px solid rgba(124,58,237,.18);box-shadow:0 16px 40px rgba(26,21,35,.16);font-family:inherit}' +
            '.gk-fcm-prompt__text strong{display:block;font-size:.95rem;color:#1A1523}' +
            '.gk-fcm-prompt__text p{margin:.2rem 0 0;font-size:.82rem;color:#5C5470;line-height:1.4}' +
            '.gk-fcm-prompt__actions{display:flex;gap:.45rem;flex-shrink:0}' +
            '.gk-fcm-prompt__btn{border:0;border-radius:999px;padding:.55rem .9rem;font:inherit;font-size:.82rem;font-weight:700;cursor:pointer}' +
            '.gk-fcm-prompt__btn--ghost{background:rgba(124,58,237,.08);color:#5B21B6}' +
            '.gk-fcm-prompt__btn--primary{background:linear-gradient(135deg,#7C3AED,#EC4899);color:#fff}' +
            '@media (max-width:640px){.gk-fcm-prompt{bottom:5.5rem}}';
        document.head.appendChild(s);
    }

    function start() {
        if (!ready()) return;

        loadConfig().then(function (cfg) {
            if (!cfg || !cfg.configured || cfg.enabled === false) return;

            window.GkPush.enableWeb = function () {
                return Notification.requestPermission().then(function (perm) {
                    if (perm !== 'granted') return { ok: false, permission: perm };
                    return getToken(cfg).then(function (token) {
                        return saveToken(token).then(function (res) {
                            return { ok: !!(res && res.ok), token: token, permission: perm };
                        });
                    });
                });
            };

            if (Notification.permission === 'granted') {
                getToken(cfg).then(saveToken).catch(function () {});
                return;
            }

            if (Notification.permission === 'denied') return;

            injectStyles();
            showPrompt(function () {
                hidePrompt();
                window.GkPush.enableWeb().catch(function () {});
            });
        }).catch(function () {});
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
