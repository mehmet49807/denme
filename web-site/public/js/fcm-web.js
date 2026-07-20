(function () {
    'use strict';

    // Bir kez kapatıldıysa / izin verildiyse bir daha otomatik gösterme.
    var DONE_KEY = 'gk_fcm_web_prompt_done';
    var PROMPT_ID = 'gkFcmWebPrompt';
    var started = false;

    function forcePrompt() {
        try {
            return /(?:^|[?&])fcm=1(?:&|$)/.test(location.search);
        } catch (e) {
            return false;
        }
    }

    function isDone() {
        if (forcePrompt()) return false;
        try {
            return localStorage.getItem(DONE_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function markDone() {
        try {
            localStorage.setItem(DONE_KEY, '1');
            // Eski anahtarları temizle
            localStorage.removeItem('gk_fcm_web_dismissed_at');
            localStorage.removeItem('gk_fcm_web_dismissed');
        } catch (e) {}
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

    function showPrompt(opts) {
        opts = opts || {};
        if (document.getElementById(PROMPT_ID)) return;
        if (!opts.force && isDone()) return;

        injectStyles();
        var el = document.createElement('div');
        el.id = PROMPT_ID;
        el.className = 'gk-fcm-prompt';
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-label', 'Bildirim izni');
        el.innerHTML =
            '<div class="gk-fcm-prompt__card">' +
            '<div class="gk-fcm-prompt__text">' +
            '<strong>' + (opts.title || 'Tarayıcı bildirimleri') + '</strong>' +
            '<p>' + (opts.body || 'Yeni mesaj ve duyuruları tarayıcıdan anında alın.') + '</p>' +
            '</div>' +
            '<div class="gk-fcm-prompt__actions">' +
            '<button type="button" class="gk-fcm-prompt__btn gk-fcm-prompt__btn--ghost" data-fcm-later>Şimdi değil</button>' +
            '<button type="button" class="gk-fcm-prompt__btn gk-fcm-prompt__btn--primary" data-fcm-allow>İzin ver</button>' +
            '</div></div>';
        document.body.appendChild(el);

        el.querySelector('[data-fcm-later]').addEventListener('click', function () {
            markDone();
            hidePrompt();
        });
        el.querySelector('[data-fcm-allow]').addEventListener('click', function () {
            hidePrompt();
            if (opts.onAllow) opts.onAllow();
        });
    }

    function enableWeb(cfg) {
        if (!hasNotificationApi()) {
            markDone();
            return Promise.resolve({ ok: false, reason: 'unsupported' });
        }
        return Notification.requestPermission().then(function (perm) {
            markDone();
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
                markDone();
                return;
            }

            loadConfig().then(function (cfg) {
                if (!cfg || cfg.enabled === false || !cfg.configured) return;

                window.GkPush.enableWeb = function () {
                    return enableWeb(cfg).catch(function (err) {
                        console.warn('[GkFcm] enableWeb hata', err);
                        markDone();
                        return { ok: false, error: String(err && err.message || err) };
                    });
                };

                // İzin zaten verilmiş → arka planda token kaydet, banner yok
                if (Notification.permission === 'granted') {
                    markDone();
                    getToken(cfg).then(saveToken).catch(function () {});
                    return;
                }

                // Tarayıcıda engellenmiş → bir daha sorma
                if (Notification.permission === 'denied') {
                    markDone();
                    return;
                }

                // default — yalnızca daha önce kapatılmadıysa bir kez sor
                if (isDone()) return;

                setTimeout(function () {
                    if (isDone() || Notification.permission !== 'default') return;
                    showPrompt({
                        force: forcePrompt(),
                        onAllow: function () {
                            window.GkPush.enableWeb();
                        }
                    });
                }, 800);
            }).catch(function () {});
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
