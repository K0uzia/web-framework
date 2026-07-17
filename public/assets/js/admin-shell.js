(function () {
    'use strict';

    var app = document.querySelector('[data-admin-app]');
    if (!app) {
        return;
    }

    var backdrop = app.querySelector('[data-admin-sidebar-close]');
    var openBtn = app.querySelector('[data-admin-sidebar-open]');

    function openSidebar() {
        app.classList.add('is-nav-open');
        if (backdrop) {
            backdrop.hidden = false;
        }
        if (openBtn) {
            openBtn.setAttribute('aria-expanded', 'true');
        }
    }

    function closeSidebar() {
        app.classList.remove('is-nav-open');
        if (backdrop) {
            backdrop.hidden = true;
        }
        if (openBtn) {
            openBtn.setAttribute('aria-expanded', 'false');
        }
    }

    if (openBtn) {
        openBtn.addEventListener('click', openSidebar);
    }
    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && app.classList.contains('is-nav-open')) {
            closeSidebar();
        }
    });
})();
