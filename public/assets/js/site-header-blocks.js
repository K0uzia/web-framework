(function () {
    'use strict';

    document.querySelectorAll('.site-header__blocks-toggle').forEach(function (btn) {
        var panelId = btn.getAttribute('aria-controls');
        if (!panelId) {
            return;
        }
        var panel = document.getElementById(panelId);
        if (!panel) {
            return;
        }
        var header = btn.closest('.site-header');

        btn.addEventListener('click', function () {
            var open = panel.hasAttribute('hidden');
            if (open) {
                panel.removeAttribute('hidden');
            } else {
                panel.setAttribute('hidden', '');
            }
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (header) {
                header.classList.toggle('is-menu-open', open);
            }
        });

        document.addEventListener('click', function (event) {
            if (panel.hasAttribute('hidden')) {
                return;
            }
            if (panel.contains(event.target) || btn.contains(event.target)) {
                return;
            }
            panel.setAttribute('hidden', '');
            btn.setAttribute('aria-expanded', 'false');
            if (header) {
                header.classList.remove('is-menu-open');
            }
        });
    });

    if (!window.matchMedia('(min-width: 1024px)').matches) {
        return;
    }

    document.querySelectorAll('.site-header__blocks-mega-details, .site-header__blocks-dropdown').forEach(function (details) {
        var parent = details.parentElement;
        if (!parent) {
            return;
        }

        var closeTimer;
        parent.addEventListener('mouseenter', function () {
            window.clearTimeout(closeTimer);
            details.setAttribute('open', '');
        });
        parent.addEventListener('mouseleave', function () {
            closeTimer = window.setTimeout(function () {
                details.removeAttribute('open');
            }, 120);
        });
    });
})();
