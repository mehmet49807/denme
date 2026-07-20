(function () {
    'use strict';

    var PROMPT_ID = 'gkFcmWebPrompt';
    var started = false;
    var acknowledged = false;

    function forcePrompt() {
        try {
            return /(?:^|[?&])fcm=1(?:&|$)/.test(location.search);
        } catch (e) {
            return false;
        }
    }

    function shouldShowAfterLogin() {
        return window.__GK_FCM_LOGIN_PROMPT__ === true || forcePrompt();
    }

    function ackPrompt() {
        if (acknowledged) return;
        acknowledged = true;
        window.__GK_FCM_LOGIN_PROMPT__ = false;
        if (window.GkPush && typeof window.GkPush.ackPrompt === 'function') {
            window.GkPush.ackPrompt();
        }
    }

    function hasNotificationApi() {
        return typeof window !== 'undefined' && 'Notification' in window;
    }

    function hasServiceWorker() {
        return typeof navigator !== 'undefined' && 'serviceWorker' in navigator;
    }

    function waitForGkPush(timeoutMs) {
        return new Promise(function (resolve) {
            if (window.GkPush) {
                resolve(true);
                return;
            }
            var start = Date.now();
            var t = setInterval(function () {
                if (window.GkPush) {
                    clearInterval(t);
                    resolve(true);
                    return;
                }
                if (Date.now() - start > timeoutMs) {
                    clearInterval(t);
                    resolve(false);
                }
            }, 50);
        });
    }

    function loadConfig() {
        return fetch('/firebase-config.json', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        }).then(function (r) {
            if (!r.ok) throw new Error('config HTTP ' + r.status);
            return r.json();
        });
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
                b.onerror = function () { reject(new Error('firebase-messaging yüklenemedi')); };
                document.head.appendChild(b);
            };
            a.onerror = function () { reject(new Error('firebase-app yüklenemedi')); };
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
                    appId: cfg.appId,
                    measurementId: cfg.measurementId || undefined
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

    function injectStyles() {
        if (document.getElementById('gkFcmPromptStyle')) return;
        var s = document.createElement('style');
        s.id = 'gkFcmPromptStyle';
        s.textContent =
            '.gk-fcm-prompt{position:fixed;left:0;right:0;top:0;z-index:2147483000;display:flex;justify-content:center;padding:max(0.75rem,env(safe-area-inset-top)) 0.85rem 0;pointer-events:none}' +
            '.gk-fcm-prompt__card{pointer-events:auto;max-width:32rem;width:100%;display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;justify-content:space-between;padding:1rem 1.1rem;border-radius:18px;background:#fff;border:1px solid rgba(124,58,237,.22);box-shadow:0 18px 50px rgba(26,21,35,.22);font-family:inherit}' +
            '.gk-fcm-prompt__text strong{display:block;font-size:1rem;color:#1A1523}' +
            '.gk-fcm-prompt__text p{margin:.25rem 0 0;font-size:.86rem;color:#5C5470;line-height:1.45}' +
            '.gk-fcm-prompt__actions{display:flex;gap:.45rem;flex-shrink:0;flex-wrap:wrap}' +
            '.gk-fcm-prompt__btn{border:0;border-radius:999px;padding:.6rem 1rem;font:inherit;font-size:.84rem;font-weight:700;cursor:pointer}' +
            '.gk-fcm-prompt__btn--ghost{background:rgba(124,58,237,.08);color:#5B21B6}' +
            '.gk-fcm-prompt__btn--primary{background:linear-gradient(135deg,#7C3AED,#EC4899);color:#fff}';
        document.head.appendChild(s);
    }

    function showPrompt(onAllow) {
        if (document.getElementById(PROMPT_ID)) return;
        injectStyles();
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

        // Gösterildiği anda session bayrağını temizle → sonraki sayfalarda tekrar çıkmaz
        ackPrompt();

        el.querySelector('[data-fcm-later]').addEventListener('click', function () {
            hidePrompt();
        });
        el.querySelector('[data-fcm-allow]').addEventListener('click', function () {
            hidePrompt();
            if (onAllow) onAllow();
        });
    }

    function enableWeb(cfg) {
        if (!hasNotificationApi()) {
            return Promise.resolve({ ok: false, reason: 'unsupported' });
        }
        return Notification.requestPermission().then(function (perm) {
            if (perm !== 'granted') {
                return { ok: false, permission: perm };
            }
            return getToken(cfg).then(function (token) {
                if (!token) return { ok: false, permission: perm, reason: 'no_token' };
                return saveToken(token).then(function (res) {
                    return { ok: !!(res && res.ok), token: token, permission: perm };
                });
            });
        });
    }

    function start() {
        if (started) return;
        started = true;

        waitForGkPush(3000).then(function (ok) {
            if (!ok) return;

            if (!window.isSecureContext || !hasNotificationApi() || !hasServiceWorker()) {
                if (shouldShowAfterLogin()) ackPrompt();
                return;
            }

            loadConfig().then(function (cfg) {
                if (!cfg || cfg.enabled === false || !cfg.configured) {
                    if (shouldShowAfterLogin()) ackPrompt();
                    return;
                }

                window.GkPush.enableWeb = function () {
                    return enableWeb(cfg).catch(function (err) {
                        console.warn('[GkFcm] enableWeb hata', err);
                        return { ok: false, error: String(err && err.message || err) };
                    });
                };

                // İzin verilmiş → token yenile, banner yok
                if (Notification.permission === 'granted') {
                    if (shouldShowAfterLogin()) ackPrompt();
                    getToken(cfg).then(saveToken).catch(function () {});
                    return;
                }

                // Engellenmiş → banner gösterme
                if (Notification.permission === 'denied') {
                    if (shouldShowAfterLogin()) ackPrompt();
                    return;
                }

                // Yalnızca yeni girişte (veya ?fcm=1) bir kez sor
                if (!shouldShowAfterLogin()) return;

                setTimeout(function () {
                    if (Notification.permission !== 'default') {
                        ackPrompt();
                        return;
                    }
                    showPrompt(function () {
                        window.GkPush.enableWeb();
                    });
                }, 700);
            }).catch(function () {
                if (shouldShowAfterLogin()) ackPrompt();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
