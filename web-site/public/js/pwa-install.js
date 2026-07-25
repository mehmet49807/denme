(function () {
    var hint = document.getElementById('pwaInstallHint');
    if (!hint) return;

    var STORAGE_KEY = 'gk_pwa_install_dismissed';
    var installBtn = document.getElementById('pwaInstallBtn');
    var dismissBtn = document.getElementById('pwaInstallDismiss');
    var deferred = null;

    function isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;
    }

    function wasDismissed() {
        try {
            return localStorage.getItem(STORAGE_KEY) === '1';
        } catch (e) {
            return false;
        }
    }

    function show() {
        if (isStandalone() || wasDismissed()) return;
        hint.hidden = false;
    }

    function hide(persist) {
        hint.hidden = true;
        if (persist) {
            try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
        }
    }

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferred = e;
        show();
    });

    // iOS Safari: no beforeinstallprompt — soft tip after a short delay
    var isIos = /iphone|ipad|ipod/i.test(navigator.userAgent || '');
    if (isIos && !isStandalone() && !wasDismissed()) {
        setTimeout(function () {
            if (deferred) return;
            var copy = hint.querySelector('.pwa-install-hint__copy span');
            if (copy) {
                copy.textContent = 'Paylaş → Ana Ekrana Ekle ile uygulama gibi aç.';
            }
            if (installBtn) {
                installBtn.textContent = 'Nasıl?';
                installBtn.onclick = function () {
                    window.location.href = '/profile?settings=push';
                };
            }
            show();
        }, 4500);
    }

    if (installBtn) {
        installBtn.addEventListener('click', function () {
            if (!deferred) return;
            deferred.prompt();
            deferred.userChoice.finally(function () {
                deferred = null;
                hide(true);
            });
        });
    }

    if (dismissBtn) {
        dismissBtn.addEventListener('click', function () {
            hide(true);
        });
    }
})();
