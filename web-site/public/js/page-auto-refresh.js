(function () {
    const reloadSec = parseInt(document.querySelector('meta[name="page-auto-reload"]')?.content || '0', 10);
    if (!reloadSec || reloadSec < 15) return;

    let timer = null;

    function reloadIfVisible() {
        if (document.hidden) return;
        window.location.reload();
    }

    function start() {
        if (timer) clearInterval(timer);
        timer = setInterval(reloadIfVisible, reloadSec * 1000);
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) reloadIfVisible();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
