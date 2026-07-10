(function () {
    var page = document.getElementById('profileSettingsPage');
    if (!page) {
        return;
    }

    var titleEl = document.getElementById('profileSettingsTitle');
    var backBtn = page.querySelector('[data-settings-back]');
    var panels = page.querySelectorAll('[data-settings-panel]');
    var panelTitles = {
        menu: 'Ayarlar',
        edit: 'Profil Düzenle',
        hobbies: 'Hobiler',
        language: 'Dil Seç',
        password: 'Şifre Değiştir',
    };
    var currentPanel = 'menu';

    function showPanel(name) {
        currentPanel = name;
        panels.forEach(function (panel) {
            var isActive = panel.getAttribute('data-settings-panel') === name;
            panel.hidden = !isActive;
        });
        if (titleEl) {
            titleEl.textContent = panelTitles[name] || 'Ayarlar';
        }
        if (backBtn) {
            backBtn.hidden = name === 'menu';
        }
        if (name !== 'menu' && history.replaceState) {
            var url = new URL(window.location.href);
            url.searchParams.set('panel', name);
            history.replaceState(null, '', url.toString());
        } else if (name === 'menu' && history.replaceState) {
            var cleanUrl = new URL(window.location.href);
            cleanUrl.searchParams.delete('panel');
            history.replaceState(null, '', cleanUrl.toString());
        }
    }

    page.querySelectorAll('[data-open-settings-panel]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showPanel(btn.getAttribute('data-open-settings-panel'));
        });
    });

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            showPanel('menu');
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && currentPanel !== 'menu') {
            showPanel('menu');
        }
    });

    var initialPanel = page.getAttribute('data-initial-panel') || 'menu';
    if (initialPanel && initialPanel !== 'menu') {
        showPanel(initialPanel);
    } else if (page.querySelector('.form-error')) {
        showPanel(initialPanel || 'edit');
    }
})();
