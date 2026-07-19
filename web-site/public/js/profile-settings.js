(function () {
    var sheet = document.getElementById('profileSettingsSheet');
    var openBtn = document.getElementById('profileSettingsOpen');
    if (!sheet || !openBtn) {
        return;
    }

    var titleEl = document.getElementById('profileSettingsTitle');
    var backBtn = sheet.querySelector('[data-settings-back]');
    var stages = sheet.querySelectorAll('[data-settings-panel]');
    var panelTitles = {
        menu: 'Ayarlar',
        edit: 'Profil Düzenle',
        bio: 'Bio',
        relationship: 'İlişki Durumu',
        hobbies: 'Hobiler',
        language: 'Dil Seç',
        appearance: 'Görünüm / Tema',
        privacy: 'Gizlilik',
        password: 'Şifre Değiştir',
    };
    var currentPanel = 'menu';

    function showPanel(name) {
        currentPanel = name;
        stages.forEach(function (stage) {
            stage.hidden = stage.getAttribute('data-settings-panel') !== name;
        });
        if (titleEl) {
            titleEl.textContent = panelTitles[name] || 'Ayarlar';
        }
        if (backBtn) {
            backBtn.hidden = name === 'menu';
        }
        var body = sheet.querySelector('.profile-settings-sheet-body');
        if (body) {
            body.scrollTop = 0;
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

    sheet.querySelectorAll('[data-theme-choice]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var pref = btn.getAttribute('data-theme-choice');
            if (typeof window.__gk_applyTheme === 'function') {
                window.__gk_applyTheme(pref);
            }
            sheet.querySelectorAll('[data-theme-choice]').forEach(function (other) {
                var active = other === btn;
                other.classList.toggle('profile-settings-language-item--active', active);
                if (active) {
                    other.setAttribute('aria-current', 'true');
                } else {
                    other.removeAttribute('aria-current');
                }
                var check = other.querySelector('.profile-settings-language-check');
                if (active && !check) {
                    var mark = document.createElement('span');
                    mark.className = 'profile-settings-language-check';
                    mark.setAttribute('aria-hidden', 'true');
                    mark.textContent = '✓';
                    other.appendChild(mark);
                } else if (!active && check) {
                    check.remove();
                }
            });
        });
    });

    var quietToggle = sheet.querySelector('[data-quiet-hours-toggle]');
    var quietFields = sheet.querySelector('[data-quiet-hours-fields]');
    if (quietToggle && quietFields) {
        quietToggle.addEventListener('change', function () {
            quietFields.hidden = !quietToggle.checked;
        });
    }

    var initialPanel = sheet.getAttribute('data-initial-panel');
    if (initialPanel && initialPanel !== 'menu') {
        openSheet(initialPanel);
    } else if (sheet.querySelector('.form-error')) {
        openSheet(initialPanel || 'edit');
    }
})();
