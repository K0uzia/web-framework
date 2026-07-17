(function () {
    'use strict';

    var root = document.querySelector('[data-admin-site]');
    if (!root) {
        return;
    }

    function syncMediaPicker(picker, url) {
        var input = picker.querySelector('[data-admin-media-value]');
        if (input) {
            input.value = url;
        }
        picker.querySelectorAll('[data-admin-media-pick]').forEach(function (btn) {
            var selected = (btn.getAttribute('data-url') || '') === url;
            btn.classList.toggle('is-selected', selected);
            btn.setAttribute('aria-selected', selected ? 'true' : 'false');
        });
        var clearBtn = picker.querySelector('[data-admin-media-clear]');
        if (clearBtn) {
            clearBtn.classList.toggle('is-selected', url === '');
            clearBtn.setAttribute('aria-pressed', url === '' ? 'true' : 'false');
        }
    }

    root.addEventListener('click', function (event) {
        var pick = event.target.closest('[data-admin-media-pick]');
        if (pick) {
            event.preventDefault();
            var picker = pick.closest('[data-admin-media-picker]');
            if (picker) {
                syncMediaPicker(picker, pick.getAttribute('data-url') || '');
            }
            return;
        }
        var clear = event.target.closest('[data-admin-media-clear]');
        if (clear) {
            event.preventDefault();
            var clearPicker = clear.closest('[data-admin-media-picker]');
            if (clearPicker) {
                syncMediaPicker(clearPicker, '');
            }
        }
    });
})();
