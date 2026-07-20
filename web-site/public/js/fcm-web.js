(function () {
    'use strict';

    var DISMISS_KEY = 'gk_fcm_web_dismissed_at';
    var DISMISS_MS = 12 * 60 * 60 * 1000; // 12 saat
    var PROMPT_ID = 'gkFcmWebPrompt';
    var started = false;

    function forcePrompt() {
        try {
            return /(?:^|[?&])fcm=1(?:&|$)/.test(location.search);
        } catch (e) {
            return false;
        }
    }

    function isDismissed() {
        if (forcePrompt()) return false;
        try {
            var raw = localStorage.getItem(DISMISS_KEY);
            if (!raw) return false;
            var at = parseInt(raw, 10);
            if (!at || isNaN(at)) return false;
            return (Date.now() - at) < DISMISS_MS;
        } catch (e) {
            return false;
        }
    }

    function setDismissed() {
        try { localStorage.setItem(DISMISS_KEY, String(Date.now())); } catch (e) {}
    }

    function clearDismissed() {
        try { localStorage.removeItem(DISMISS_KEY); } catch (e) {}
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
            '.gk-fcm-prompt__btn--primary{background:linear-gradient(135deg,#7C3AED,#EC4899);color:#fff}' +
            '.gk-fcm-prompt--warn .gk-fcm-prompt__card{border-color:rgba(220,38,38,.25)}';
        document.head.appendChild(s);
    }

    function showPrompt(opts) {
        opts = opts || {};
        if (document.getElementById(PROMPT_ID)) return;
        if (!opts.force && isDismissed()) return;

        injectStyles();
        var el = document.createElement('div');
        el.id = PROMPT_ID;
        el.className = 'gk-fcm-prompt' + (opts.warn ? ' gk-fcm-prompt--warn' : '');
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-label', 'Bildirim izni');
        el.innerHTML =
            '<div class="gk-fcm-prompt__card">' +
            '<div class="gk-fcm-prompt__text">' +
            '<strong>' + (opts.title || 'Tarayıcı bildirimleri') + '</strong>' +
            '<p>' + (opts.body || 'Yeni mesaj ve duyuruları tarayıcıdan anında alın.') + '</p>' +
            '</div>' +
            '<div class="gk-fcm-prompt__actions">' +
            (opts.hideLater ? '' : '<button type="button" class="gk-fcm-prompt__btn gk-fcm-prompt__btn--ghost" data-fcm-later>Şimdi değil</button>') +
            (opts.primaryLabel
                ? '<button type="button" class="gk-fcm-prompt__btn gk-fcm-prompt__btn--primary" data-fcm-allow>' + opts.primaryLabel + '</button>'
                : '') +
            '</div></div>';
        document.body.appendChild(el);

        var later = el.querySelector('[data-fcm-later]');
        if (later) {
            later.addEventListener('click', function () {
                setDismissed();
                hidePrompt();
            });
        }
        var allow = el.querySelector('[data-fcm-allow]');
        if (allow && opts.onAllow) {
            allow.addEventListener('click', function () {
                hidePrompt();
                opts.onAllow();
            });
        }
    }

    function enableWeb(cfg) {
        if (!hasNotificationApi()) {
            return Promise.resolve({ ok: false, reason: 'unsupported' });
        }
        return Notification.requestPermission().then(function (perm) {
            if (perm !== 'granted') {
                return { ok: false, permission: perm };
            }
            clearDismissed();
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
            if (!ok) {
                console.warn('[GkFcm] GkPush yok — giriş gerekli olabilir');
                return;
            }

            if (!window.isSecureContext) {
                showPrompt({
                    force: true,
                    warn: true,
                    title: 'Güvenli bağlantı gerekli',
                    body: 'Bildirimler yalnızca HTTPS üzerinde çalışır.',
                    hideLater: false,
                    primaryLabel: ''
                });
                return;
            }

            if (!hasNotificationApi() || !hasServiceWorker()) {
                showPrompt({
                    force: true,
                    warn: true,
                    title: 'Tarayıcı desteklemiyor',
                    body: 'Bu tarayıcıda web bildirimleri kapalı. Chrome/Edge deneyin; iPhone’da “Ana Ekrana Ekle” gerekebilir.',
                    hideLater: false,
                    primaryLabel: 'Tamam',
                    onAllow: function () { hidePrompt(); }
                });
                return;
            }

            loadConfig().then(function (cfg) {
                if (!cfg || cfg.enabled === false || !cfg.configured) {
                    console.warn('[GkFcm] config hazır değil', cfg);
                    return;
                }

                window.GkPush.enableWeb = function () {
                    return enableWeb(cfg).then(function (res) {
                        if (res && res.ok) {
                            showPrompt({
                                force: true,
                                title: 'Bildirimler açık',
                                body: 'Yeni mesaj geldiğinde tarayıcı bildirimi alacaksınız.',
                                hideLater: true,
                                primaryLabel: 'Tamam',
                                onAllow: function () { hidePrompt(); }
                            });
                        } else if (res && res.permission === 'denied') {
                            showPrompt({
                                force: true,
                                warn: true,
                                title: 'Bildirim izni kapalı',
                                body: 'Adres çubuğundaki kilit simgesinden Bildirimler → İzin ver yapın, sonra sayfayı yenileyin.',
                                hideLater: false,
                                primaryLabel: 'Anladım',
                                onAllow: function () { hidePrompt(); }
                            });
                        }
                        return res;
                    }).catch(function (err) {
                        console.warn('[GkFcm] enableWeb hata', err);
                        showPrompt({
                            force: true,
                            warn: true,
                            title: 'Bildirim açılamadı',
                            body: 'Tekrar deneyin. Sorun sürerse tarayıcı bildirim iznini kontrol edin.',
                            primaryLabel: 'Tekrar dene',
                            onAllow: function () { window.GkPush.enableWeb(); }
                        });
                        return { ok: false, error: String(err && err.message || err) };
                    });
                };

                if (Notification.permission === 'granted') {
                    getToken(cfg).then(saveToken).catch(function (err) {
                        console.warn('[GkFcm] token yenileme', err);
                    });
                    return;
                }

                if (Notification.permission === 'denied') {
                    if (forcePrompt() || !isDismissed()) {
                        showPrompt({
                            force: true,
                            warn: true,
                            title: 'Bildirim izni kapalı',
                            body: 'Adres çubuğundaki kilit → Bildirimler → İzin ver. Sonra sayfayı yenileyin.',
                            primaryLabel: 'Anladım',
                            onAllow: function () { setDismissed(); hidePrompt(); }
                        });
                    }
                    return;
                }

                // default — soft prompt
                setTimeout(function () {
                    showPrompt({
                        force: forcePrompt(),
                        title: 'Tarayıcı bildirimleri',
                        body: 'Yeni mesaj ve duyuruları tarayıcıdan anında alın.',
                        primaryLabel: 'İzin ver',
                        onAllow: function () {
                            window.GkPush.enableWeb();
                        }
                    });
                }, 600);
            }).catch(function (err) {
                console.warn('[GkFcm] config yüklenemedi', err);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
