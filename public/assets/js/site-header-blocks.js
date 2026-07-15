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

        function setOpen(open) {
            if (open) {
                panel.removeAttribute('hidden');
            } else {
                panel.setAttribute('hidden', '');
            }
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (header) {
                header.classList.toggle('is-menu-open', open);
            }
            document.documentElement.classList.toggle('site-nav-open', open);
        }

        btn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            setOpen(panel.hasAttribute('hidden'));
        });

        document.addEventListener('click', function (event) {
            if (panel.hasAttribute('hidden')) {
                return;
            }
            if (panel.contains(event.target) || btn.contains(event.target)) {
                return;
            }
            setOpen(false);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !panel.hasAttribute('hidden')) {
                setOpen(false);
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
