(function () {
    function syncFlaggedSelect(select) {
        if (!select) {
            return;
        }

        const wrap = select.closest('.flagged-select-wrap');
        if (!wrap) {
            return;
        }

        const img = wrap.querySelector('[data-flagged-select-flag] img');
        const option = select.options[select.selectedIndex];
        const iso = option && option.dataset.iso ? option.dataset.iso : '';

        if (!img) {
            return;
        }

        if (!iso || !option.value) {
            img.style.visibility = 'hidden';
            return;
        }

        img.style.visibility = 'visible';
        img.src = 'https://flagcdn.com/w20/' + iso + '.png';
        img.srcset = 'https://flagcdn.com/w40/' + iso + '.png 2x';
    }

    function bindFlaggedSelect(select) {
        syncFlaggedSelect(select);
        select.addEventListener('change', function () {
            syncFlaggedSelect(select);
        });
    }

    document.querySelectorAll('.flagged-select').forEach(bindFlaggedSelect);

    window.gkSyncFlaggedSelect = syncFlaggedSelect;
})();
