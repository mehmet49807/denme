(function () {
    'use strict';

    var PROMPT_ID = 'gkFcmWebPrompt';
    var SESSION_KEY = 'gk_fcm_prompt_session';
    var started = false;
    var acknowledged = false;
    var cachedCfg = null;

    function forcePrompt() {
        try {
            return /(?:^|[?&])fcm=1(?:&|$)/.test(location.search);
        } catch (e) {
            return false;
        }
    }

    function seenThisBrowserSession() {
        try {
            return sessionStorage.getItem(SESSION_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function markBrowserSessionSeen() {
        try {
            sessionStorage.setItem(SESSION_KEY, '1');
        } catch (e) {}
    }

    function clearBrowserSessionSeen() {
        try {
            sessionStorage.removeItem(SESSION_KEY);
        } catch (e) {}
    }

    function isNewLoginPrompt() {
        return window.__GK_FCM_LOGIN_PROMPT__ === true;
    }

    function shouldAutoPrompt() {
        if (forcePrompt()) return true;
        // Her yeni girişte bir kez
        if (isNewLoginPrompt()) return true;
        // Aynı girişte yeni tarayıcı oturumu (sekme/yeniden açma)
        return !seenThisBrowserSession();
    }

    function ackPrompt() {
        if (acknowledged) return;
        acknowledged = true;
        window.__GK_FCM_LOGIN_PROMPT__ = false;
        markBrowserSessionSeen();
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

    function isIos() {
        var ua = navigator.userAgent || '';
        if (/iPad|iPhone|iPod/.test(ua)) return true;
        // iPadOS 13+ desktop UA
        return navigator.platform === 'MacIntel' && (navigator.maxTouchPoints || 0) > 1;
    }

    function isStandalone() {
        try {
            if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
                return true;
            }
        } catch (e) {}
        return window.navigator.standalone === true;
    }

    // iPhone/iPad: Web Push yalnızca Ana Ekrana eklenen (standalone) uygulamada çalışır.
    // Chrome/Edge iOS’ta da WebKit kullandığı için tarayıcı sekmesinde çalışmaz.
    function needsIosHomeScreen() {
        return isIos() && !isStandalone();
    }

    function isSupported() {
        if (!window.isSecureContext || !hasServiceWorker()) return false;
        if (needsIosHomeScreen()) return false;
        return hasNotificationApi();
    }

    function permission() {
        if (needsIosHomeScreen()) return 'ios-homescreen';
        if (!hasNotificationApi()) return 'unsupported';
        return Notification.permission || 'default';
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
        return window.GkPush.register(token, 'web').then(function (res) {
            if (res && res.ok) {
                try { document.dispatchEvent(new CustomEvent('gk:push-ready', { detail: { token: token } })); } catch (e) {}
            }
            return res;
        });
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
        injectStyles();
        var el = document.createElement('div');
        el.id = PROMPT_ID;
        el.className = 'gk-fcm-prompt';
        el.setAttribute('role', 'dialog');
        el.setAttribute('aria-label', 'Bildirim izni');
        var primary = opts.primaryLabel || 'İzin ver';
        var secondary = opts.secondaryLabel || 'Şimdi değil';
        el.innerHTML =
            '<div class="gk-fcm-prompt__card">' +
            '<div class="gk-fcm-prompt__text">' +
            '<strong>' + (opts.title || 'Tarayıcı bildirimleri') + '</strong>' +
            '<p>' + (opts.body || 'Yeni mesaj ve duyuruları tarayıcıdan anında alın.') + '</p>' +
            '</div>' +
            '<div class="gk-fcm-prompt__actions">' +
            '<button type="button" class="gk-fcm-prompt__btn gk-fcm-prompt__btn--ghost" data-fcm-later>' + secondary + '</button>' +
            (opts.hidePrimary ? '' : '<button type="button" class="gk-fcm-prompt__btn gk-fcm-prompt__btn--primary" data-fcm-allow>' + primary + '</button>') +
            '</div></div>';
        document.body.appendChild(el);

        // Gösterildi → bu oturumda / girişte tekrarlama
        ackPrompt();

        el.querySelector('[data-fcm-later]').addEventListener('click', function () {
            hidePrompt();
        });
        var allow = el.querySelector('[data-fcm-allow]');
        if (allow) {
            allow.addEventListener('click', function () {
                hidePrompt();
                if (opts.onAllow) opts.onAllow();
            });
        }
    }

    function enableWeb(cfg) {
        cfg = cfg || cachedCfg;
        if (!cfg) {
            return loadConfig().then(function (c) {
                cachedCfg = c;
                return enableWeb(c);
            });
        }
        if (needsIosHomeScreen()) {
            updateSettingsUi();
            return Promise.resolve({ ok: false, reason: 'ios-homescreen', permission: 'ios-homescreen' });
        }
        if (!hasNotificationApi()) {
            return Promise.resolve({ ok: false, reason: 'unsupported', permission: 'unsupported' });
        }
        return Notification.requestPermission().then(function (perm) {
            updateSettingsUi();
            if (perm !== 'granted') {
                return { ok: false, permission: perm };
            }
            return getToken(cfg).then(function (token) {
                if (!token) return { ok: false, permission: perm, reason: 'no_token' };
                return saveToken(token).then(function (res) {
                    updateSettingsUi();
                    return { ok: !!(res && res.ok), token: token, permission: perm };
                });
            });
        });
    }

    function statusLabel(perm) {
        if (needsIosHomeScreen()) {
            return {
                text: 'Ana Ekran gerekli',
                tone: 'warn',
                hint: 'iPhone’da web bildirimleri Safari sekmesinde çalışmaz. Ana Ekrana ekleyip uygulamadan açmanız gerekir. (Chrome/Edge iPhone’da da aynı kısıtı taşır.)',
                showIosSteps: true
            };
        }
        if (!isSupported()) {
            return {
                text: 'Desteklenmiyor',
                tone: 'muted',
                hint: 'Bu tarayıcıda web bildirimleri kullanılamıyor. Güncel Safari (iOS 16.4+) veya masaüstünde Chrome/Edge deneyin.',
                showIosSteps: false
            };
        }
        if (perm === 'granted') {
            return { text: 'Açık', tone: 'ok', hint: 'Tarayıcı bildirimleri aktif. Yeni mesaj ve duyurularda bildirim alırsınız.', showIosSteps: false };
        }
        if (perm === 'denied') {
            return {
                text: 'Engellendi',
                tone: 'bad',
                hint: 'Ayarlar → Bildirimler bölümünden Gönül Köprüsü için izni açın, sonra buradan tekrar deneyin.',
                showIosSteps: false
            };
        }
        return { text: 'Kapalı', tone: 'warn', hint: 'Mesaj ve duyuruları anında almak için tarayıcı izni verin.', showIosSteps: false };
    }

    function updateSettingsUi() {
        var root = document.querySelector('[data-fcm-settings]');
        if (!root) return;
        var perm = permission();
        var info = statusLabel(perm);
        var badge = root.querySelector('[data-fcm-status-badge]');
        var hint = root.querySelector('[data-fcm-status-hint]');
        var steps = root.querySelector('[data-fcm-ios-steps]');
        var btn = root.querySelector('[data-fcm-enable]');
        if (badge) {
            badge.textContent = info.text;
            badge.dataset.tone = info.tone;
        }
        if (hint) hint.textContent = info.hint;
        if (steps) steps.hidden = !info.showIosSteps;
        if (btn) {
            if (needsIosHomeScreen()) {
                btn.hidden = true;
            } else if (!isSupported()) {
                btn.hidden = true;
            } else if (perm === 'granted') {
                btn.hidden = false;
                btn.disabled = true;
                btn.textContent = 'Bildirimler açık';
            } else if (perm === 'denied') {
                btn.hidden = false;
                btn.disabled = false;
                btn.textContent = 'Tekrar dene';
            } else {
                btn.hidden = false;
                btn.disabled = false;
                btn.textContent = 'İzin ver';
            }
        }
    }

    function bindSettingsUi() {
        var root = document.querySelector('[data-fcm-settings]');
        if (!root || root.dataset.bound === '1') {
            updateSettingsUi();
            return;
        }
        root.dataset.bound = '1';
        var btn = root.querySelector('[data-fcm-enable]');
        if (btn) {
            btn.addEventListener('click', function () {
                btn.disabled = true;
                btn.textContent = 'İsteniyor…';
                var run = window.GkPush && window.GkPush.enableWeb
                    ? window.GkPush.enableWeb()
                    : enableWeb(cachedCfg);
                Promise.resolve(run).then(function (res) {
                    updateSettingsUi();
                    if (res && res.ok) return;
                    if (res && res.permission === 'denied') {
                        btn.disabled = false;
                        btn.textContent = 'Tekrar dene';
                    } else {
                        btn.disabled = false;
                        btn.textContent = 'İzin ver';
                    }
                }).catch(function () {
                    updateSettingsUi();
                    btn.disabled = false;
                    btn.textContent = 'İzin ver';
                });
            });
        }
        updateSettingsUi();

        // Ayarlar paneli açılınca durumu yenile
        document.querySelectorAll('[data-open-settings-panel="push"]').forEach(function (el) {
            el.addEventListener('click', function () {
                setTimeout(updateSettingsUi, 50);
            });
        });
    }

    function start() {
        if (started) return;
        started = true;

        waitForGkPush(3000).then(function (ok) {
            if (!ok) return;

            // Yeni girişte sessionStorage’ı sıfırla ki her oturumda tekrar sorulsun
            if (isNewLoginPrompt()) {
                clearBrowserSessionSeen();
                acknowledged = false;
            }

            bindSettingsUi();

            // iPhone Safari sekmesi: izin istenemez — Ana Ekran yönergesi göster
            if (needsIosHomeScreen()) {
                updateSettingsUi();
                if (shouldAutoPrompt()) {
                    setTimeout(function () {
                        showPrompt({
                            title: 'iPhone’da bildirimler',
                            body: 'Safari’de Paylaş → Ana Ekrana Ekle, sonra uygulamayı açıp Ayarlar’dan izin verin.',
                            primaryLabel: 'Anladım',
                            secondaryLabel: 'Kapat',
                            onAllow: function () {}
                        });
                    }, 700);
                }
                return;
            }

            if (!isSupported()) {
                if (shouldAutoPrompt()) ackPrompt();
                updateSettingsUi();
                return;
            }

            loadConfig().then(function (cfg) {
                cachedCfg = cfg;
                if (!cfg || cfg.enabled === false || !cfg.configured) {
                    if (shouldAutoPrompt()) ackPrompt();
                    return;
                }

                window.GkPush.enableWeb = function () {
                    return enableWeb(cfg).catch(function (err) {
                        console.warn('[GkFcm] enableWeb hata', err);
                        return { ok: false, error: String(err && err.message || err) };
                    });
                };
                window.GkPush.refreshPushSettings = updateSettingsUi;

                var perm = permission();
                if (perm === 'granted') {
                    if (shouldAutoPrompt()) ackPrompt();
                    getToken(cfg).then(saveToken).catch(function () {});
                    updateSettingsUi();
                    return;
                }

                if (perm === 'denied') {
                    if (shouldAutoPrompt()) ackPrompt();
                    updateSettingsUi();
                    return;
                }

                // default — yeni giriş veya yeni tarayıcı oturumunda bir kez
                if (!shouldAutoPrompt()) {
                    updateSettingsUi();
                    return;
                }

                setTimeout(function () {
                    if (permission() !== 'default') {
                        ackPrompt();
                        updateSettingsUi();
                        return;
                    }
                    showPrompt({
                        onAllow: function () {
                            window.GkPush.enableWeb().then(updateSettingsUi);
                        }
                    });
                    updateSettingsUi();
                }, 700);
            }).catch(function () {
                if (shouldAutoPrompt()) ackPrompt();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
