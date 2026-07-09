(function () {
    'use strict';
    try {
        var stored = window.localStorage.getItem('capsuleDevTheme');
        if (stored === 'light' || stored === 'dark') {
            document.documentElement.setAttribute('data-theme', stored);
        }
    } catch (e) {
        // localStorage indisponible (navigation privée) : le thème sombre par défaut s'applique.
    }
})();
