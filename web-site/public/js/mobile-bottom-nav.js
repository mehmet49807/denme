(function () {
    'use strict';

    var MOBILE_MAX = 767;

    function isMobileNav() {
        return window.innerWidth <= MOBILE_MAX;
    }

    function updateSmartNavFab() {
        var nav = document.querySelector('.app-sidebar-nav--smart');
        var sidebar = document.querySelector('.app-sidebar');
        if (!nav || !sidebar || !isMobileNav()) {
            return;
        }

        var active = nav.querySelector('a.active');
        var notch = nav.querySelector('.app-sidebar-nav-notch');
        if (!active) {
            return;
        }

        var navRect = nav.getBoundingClientRect();
        var activeRect = active.getBoundingClientRect();
        var centerX = activeRect.left + activeRect.width / 2 - navRect.left;
        var pct = Math.max(8, Math.min(92, (centerX / navRect.width) * 100));

        sidebar.style.setProperty('--smart-nav-notch-left', pct + '%');
        if (notch) {
            notch.style.left = pct + '%';
        }
    }

    function bindSmartNav() {
        var nav = document.querySelector('.app-sidebar-nav--smart');
        if (!nav) {
            return;
        }

        nav.querySelectorAll('a[href]').forEach(function (link) {
            link.addEventListener('click', function () {
                if (!isMobileNav()) {
                    return;
                }
                nav.querySelectorAll('a.active').forEach(function (el) {
                    el.classList.remove('active');
                });
                link.classList.add('active');
                requestAnimationFrame(updateSmartNavFab);
            });
        });

        updateSmartNavFab();
        window.addEventListener('resize', updateSmartNavFab);
        window.addEventListener('orientationchange', function () {
            setTimeout(updateSmartNavFab, 120);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindSmartNav);
    } else {
        bindSmartNav();
    }
})();
