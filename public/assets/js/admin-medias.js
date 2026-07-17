(function () {
    'use strict';

    document.querySelectorAll('[data-admin-upload]').forEach(function (input) {
        input.addEventListener('change', function () {
            if (input.files && input.files.length > 0 && input.form) {
                input.form.submit();
            }
        });
    });

    document.querySelectorAll('form[data-admin-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-admin-confirm') || 'Confirmer ?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('.admin-upload__zone').forEach(function (zone) {
        var form = zone.closest('form');
        var input = zone.querySelector('[data-admin-upload]');
        if (!form || !input) {
            return;
        }

        ['dragenter', 'dragover'].forEach(function (type) {
            zone.addEventListener(type, function (event) {
                event.preventDefault();
                event.stopPropagation();
                zone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (type) {
            zone.addEventListener(type, function (event) {
                event.preventDefault();
                event.stopPropagation();
                zone.classList.remove('is-dragover');
            });
        });

        zone.addEventListener('drop', function (event) {
            var files = event.dataTransfer && event.dataTransfer.files;
            if (!files || files.length === 0) {
                return;
            }
            if (typeof DataTransfer === 'undefined') {
                return;
            }
            var transfer = new DataTransfer();
            transfer.items.add(files[0]);
            input.files = transfer.files;
            form.submit();
        });
    });
})();
