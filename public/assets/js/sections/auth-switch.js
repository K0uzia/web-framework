(function () {
    'use strict';

    var VALID_MODES = {
        login: true,
        signup: true,
        forgot: true,
    };

    function setAuthMode(root, mode) {
        if (!(root instanceof HTMLElement)) {
            return;
        }
        var nextMode = VALID_MODES[mode] ? mode : 'login';
        root.setAttribute('data-auth-mode', nextMode);
        root.querySelectorAll('[data-auth-panel]').forEach(function (panel) {
            if (!(panel instanceof HTMLElement)) {
                return;
            }
            var panelMode = panel.getAttribute('data-auth-panel');
            var isActive = panelMode === nextMode;
            panel.hidden = !isActive;
        });
    }

    function resetAuthRoots(scope) {
        var roots = (scope || document).querySelectorAll('[data-auth-root]');
        roots.forEach(function (root) {
            setAuthMode(root, 'login');
        });
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof Element)) {
            return;
        }
        var switcher = target.closest('[data-auth-switch]');
        if (!(switcher instanceof HTMLElement)) {
            return;
        }
        var mode = switcher.getAttribute('data-auth-switch');
        var root = switcher.closest('[data-auth-root]');
        if (!(root instanceof HTMLElement) || !mode) {
            return;
        }
        event.preventDefault();
        setAuthMode(root, mode);
    });

    window.SiteAuthSwitch = {
        reset: resetAuthRoots,
        setMode: setAuthMode,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            resetAuthRoots(document);
        });
    } else {
        resetAuthRoots(document);
    }
})();
