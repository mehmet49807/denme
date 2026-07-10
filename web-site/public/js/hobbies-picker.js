(function () {
    function initPicker(root) {
        var max = parseInt(root.getAttribute('data-max') || '4', 10);
        var countEl = root.querySelector('[data-hobby-count]');
        var inputs = root.querySelectorAll('[data-hobby-input]');

        function sync() {
            var checked = root.querySelectorAll('[data-hobby-input]:checked');
            if (countEl) {
                countEl.textContent = String(checked.length);
            }
            inputs.forEach(function (input) {
                var chip = input.closest('[data-hobby-chip]');
                if (chip) {
                    chip.classList.toggle('hobby-chip--on', input.checked);
                }
                if (!input.checked) {
                    input.disabled = checked.length >= max;
                } else {
                    input.disabled = false;
                }
            });
        }

        inputs.forEach(function (input) {
            input.addEventListener('change', function () {
                var checked = root.querySelectorAll('[data-hobby-input]:checked');
                if (input.checked && checked.length > max) {
                    input.checked = false;
                }
                sync();
            });
        });

        sync();
    }

    document.querySelectorAll('[data-hobbies-picker]').forEach(initPicker);
})();
