(function () {
    'use strict';

    var input = document.getElementById('dev-pages-search');
    var rows = document.querySelectorAll('#dev-pages-rows tr[data-page-row]');
    var noMatch = document.getElementById('dev-pages-no-match');

    if (!input || rows.length === 0) {
        return;
    }

    input.addEventListener('input', function () {
        var term = input.value.trim().toLowerCase();
        var visible = 0;

        rows.forEach(function (row) {
            var haystack = (row.getAttribute('data-title') || '') + ' ' + (row.getAttribute('data-path') || '');
            var match = term === '' || haystack.indexOf(term) !== -1;
            row.hidden = !match;
            if (match) {
                visible++;
            }
        });

        if (noMatch) {
            noMatch.classList.toggle('visually-hidden', visible !== 0);
        }
    });
})();
