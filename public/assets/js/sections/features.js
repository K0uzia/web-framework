(function () {
    function initTabs(root) {
        var tabs = root.querySelectorAll('[data-feature-tab]');
        var panels = root.querySelectorAll('[data-feature-tab-panel]');
        if (!tabs.length) return;

        function activate(index) {
            tabs.forEach(function (tab) {
                var on = tab.getAttribute('data-feature-tab') === String(index);
                tab.classList.toggle('is-active', on);
                tab.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach(function (panel) {
                var on = panel.getAttribute('data-feature-tab-panel') === String(index);
                panel.classList.toggle('is-active', on);
                if (on) {
                    panel.removeAttribute('hidden');
                } else {
                    panel.setAttribute('hidden', '');
                }
            });
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                activate(tab.getAttribute('data-feature-tab'));
            });
        });

        activate(tabs[0].getAttribute('data-feature-tab'));
    }

    function initAccordion(root) {
        var triggers = root.querySelectorAll('[data-feature-accordion-trigger]');
        var images = root.querySelectorAll('[data-feature-accordion-image]');
        if (!triggers.length) return;

        function activate(index) {
            root.querySelectorAll('[data-feature-accordion-item]').forEach(function (item) {
                var on = item.getAttribute('data-feature-accordion-item') === String(index);
                item.classList.toggle('is-active', on);
            });
            triggers.forEach(function (trigger) {
                var on = trigger.getAttribute('data-feature-accordion-trigger') === String(index);
                trigger.setAttribute('aria-expanded', on ? 'true' : 'false');
            });
            root.querySelectorAll('[data-feature-accordion-panel]').forEach(function (panel) {
                var on = panel.getAttribute('data-feature-accordion-panel') === String(index);
                panel.hidden = !on;
            });
            images.forEach(function (image) {
                var on = image.getAttribute('data-feature-accordion-image') === String(index);
                image.classList.toggle('is-active', on);
            });
        }

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                activate(trigger.getAttribute('data-feature-accordion-trigger'));
            });
        });

        activate(triggers[0].getAttribute('data-feature-accordion-trigger'));
    }

    document.querySelectorAll('[data-feature-tabs]').forEach(initTabs);
    document.querySelectorAll('[data-feature-accordion]').forEach(initAccordion);
})();
