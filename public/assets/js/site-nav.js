(function () {
    'use strict';

    document.querySelectorAll('.site-header__toggle').forEach(function (btn) {
        var navId = btn.getAttribute('aria-controls');
        if (!navId) {
            return;
        }
        var nav = document.getElementById(navId);
        if (!nav) {
            return;
        }

        btn.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
            if (!nav.classList.contains('is-open')) {
                return;
            }
            if (nav.contains(event.target) || btn.contains(event.target)) {
                return;
            }
            nav.classList.remove('is-open');
            btn.setAttribute('aria-expanded', 'false');
        });
    });
})();
