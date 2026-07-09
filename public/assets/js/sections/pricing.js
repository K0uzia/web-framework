(function () {
    function setYearly(root, yearly) {
        root.classList.toggle('is-yearly', yearly);
        var switchInput = root.querySelector('[data-pricing-billing-switch]');
        if (switchInput) {
            switchInput.checked = yearly;
        }
        root.querySelectorAll('[data-pricing-billing-tab]').forEach(function (tab) {
            var isYearlyTab = tab.getAttribute('data-pricing-billing-tab') === 'yearly';
            var active = yearly ? isYearlyTab : !isYearlyTab;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
        });
    }

    function initBilling(root) {
        var switchInput = root.querySelector('[data-pricing-billing-switch]');
        if (switchInput) {
            switchInput.addEventListener('change', function () {
                setYearly(root, switchInput.checked);
            });
        }

        root.querySelectorAll('[data-pricing-billing-tab]').forEach(function (tab) {
            tab.addEventListener('click', function () {
                setYearly(root, tab.getAttribute('data-pricing-billing-tab') === 'yearly');
            });
        });

        setYearly(root, false);
    }

    document.querySelectorAll('[data-pricing-billing]').forEach(initBilling);
})();
