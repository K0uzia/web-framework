(function () {
    'use strict';

    if (!window.__CAPSULE_DEV_STATIC) {
        return;
    }

    var NOTICE = 'Mode démo statique : les modifications ne sont pas enregistrées en ligne.';

    function showNotice() {
        if (typeof window.__devToast === 'function') {
            window.__devToast(NOTICE, 'warning');
            return;
        }
        window.alert(NOTICE);
    }

    var originalFetch = window.fetch;
    if (typeof originalFetch === 'function') {
        window.fetch = function (input, init) {
            var method = (init && init.method) || 'GET';
            if (String(method).toUpperCase() !== 'GET') {
                var url = typeof input === 'string' ? input : (input && input.url) || '';
                if (url.indexOf('/dev') !== -1) {
                    showNotice();
                    return Promise.resolve(new Response('', { status: 200, headers: { 'Content-Type': 'text/html' } }));
                }
            }
            return originalFetch.apply(this, arguments);
        };
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        var action = form.getAttribute('action') || '';
        if (action.indexOf('/dev') === -1 && !form.hasAttribute('hx-post')) {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        showNotice();
    }, true);
})();
