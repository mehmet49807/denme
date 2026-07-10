(function () {
    var sheet = document.getElementById('profileSettingsSheet');
    var openBtn = document.getElementById('profileSettingsOpen');
    if (!sheet || !openBtn) {
        return;
    }

    function openSheet() {
        sheet.hidden = false;
        document.body.classList.add('profile-settings-open');
        openBtn.setAttribute('aria-expanded', 'true');
    }

    function closeSheet() {
        sheet.hidden = true;
        document.body.classList.remove('profile-settings-open');
        openBtn.setAttribute('aria-expanded', 'false');
        openBtn.focus();
    }

    openBtn.addEventListener('click', openSheet);

    sheet.querySelectorAll('[data-close-settings]').forEach(function (el) {
        el.addEventListener('click', closeSheet);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !sheet.hidden) {
            closeSheet();
        }
    });
})();
