(function () {
    'use strict';

    var typeSelect = document.getElementById('nav_type');
    var hrefField = document.getElementById('nav-href-field');
    var slugField = document.getElementById('nav-slug-field');

    if (!typeSelect || !hrefField || !slugField) {
        return;
    }

    function syncFields() {
        var isPage = typeSelect.value === 'page';
        hrefField.hidden = isPage;
        slugField.hidden = !isPage;
    }

    typeSelect.addEventListener('change', syncFields);
    syncFields();
})();
