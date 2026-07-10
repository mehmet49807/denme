(function () {
    var sheet = document.getElementById('profileSettingsSheet');
    var openBtn = document.getElementById('profileSettingsOpen');
    if (!sheet || !openBtn) {
        return;
    }

    var titleEl = document.getElementById('profileSettingsTitle');
    var backBtn = sheet.querySelector('[data-settings-back]');
    var panels = sheet.querySelectorAll('[data-settings-panel]');
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
    }

    function openSheet(panel) {
        sheet.hidden = false;
        document.body.classList.add('profile-settings-open');
        showPanel(panel || 'menu');
        openBtn.setAttribute('aria-expanded', 'true');
    }

    function closeSheet() {
        sheet.hidden = true;
        document.body.classList.remove('profile-settings-open');
        showPanel('menu');
        openBtn.setAttribute('aria-expanded', 'false');
        openBtn.focus();
    }

    openBtn.addEventListener('click', function () {
        openSheet('menu');
    });

    sheet.querySelectorAll('[data-close-settings]').forEach(function (el) {
        el.addEventListener('click', closeSheet);
    });

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            showPanel('menu');
        });
    }

    sheet.querySelectorAll('[data-open-settings-panel]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showPanel(btn.getAttribute('data-open-settings-panel'));
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !sheet.hidden) {
            if (currentPanel !== 'menu') {
                showPanel('menu');
            } else {
                closeSheet();
            }
        }
    });

    var initialPanel = sheet.getAttribute('data-initial-panel');
    if (initialPanel && initialPanel !== 'menu') {
        openSheet(initialPanel);
    } else if (sheet.querySelector('.form-error')) {
        openSheet(initialPanel || 'edit');
    }
})();
