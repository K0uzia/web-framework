(function () {
    'use strict';

    function bindTemplatePicker(inputId, gridId, dataAttribute) {
        var templateInput = document.getElementById(inputId);
        var grid = document.getElementById(gridId);
        if (!templateInput || !grid) {
            return;
        }

        function selectTemplate(id) {
            templateInput.value = id;
            grid.querySelectorAll('[' + dataAttribute + ']').forEach(function (card) {
                var active = card.getAttribute(dataAttribute) === id;
                card.classList.toggle('is-selected', active);
                card.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        }

        grid.addEventListener('click', function (event) {
            var card = event.target.closest('[' + dataAttribute + ']');
            if (!card) {
                return;
            }
            selectTemplate(card.getAttribute(dataAttribute));
        });

        selectTemplate(templateInput.value || 'default');
    }

    bindTemplatePicker('dev-header-new-template', 'dev-header-template-grid', 'data-header-template');
    bindTemplatePicker('dev-footer-new-template', 'dev-footer-template-grid', 'data-footer-template');
})();
