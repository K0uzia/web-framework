(function () {
    'use strict';

    var modal = document.getElementById('site-login-modal');
    if (!modal) {
        return;
    }

    var panel = modal.querySelector('.site-login-modal__panel');
    var lastFocus = null;

    function openModal() {
        lastFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
        modal.hidden = false;
        document.body.classList.add('site-login-modal-open');
        if (window.SiteAuthSwitch && typeof window.SiteAuthSwitch.reset === 'function') {
            window.SiteAuthSwitch.reset(modal);
        }
        var closeBtn = modal.querySelector('[data-login-modal-close]');
        if (closeBtn instanceof HTMLElement) {
            closeBtn.focus();
        }
    }

    function closeModal() {
        modal.hidden = true;
        document.body.classList.remove('site-login-modal-open');
        if (lastFocus instanceof HTMLElement) {
            lastFocus.focus();
        }
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof Element)) {
            return;
        }
        if (target.closest('[data-login-modal-open]')) {
            event.preventDefault();
            openModal();
            return;
        }
        if (target.closest('[data-login-modal-close]')) {
            event.preventDefault();
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            event.preventDefault();
            closeModal();
            return;
        }
        if (event.key !== 'Tab' || modal.hidden || !panel) {
            return;
        }
        var focusable = panel.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        if (focusable.length === 0) {
            return;
        }
        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
})();
