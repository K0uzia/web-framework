(function () {
    'use strict';

    var base = typeof window.CapsuleBasePath === 'string' ? window.CapsuleBasePath : '';

    window.capsuleUrl = function (url) {
        if (!url || url.charAt(0) !== '/' || base === '') {
            return url;
        }

        return base + url;
    };

    if (base === '') {
        return;
    }

    var nativeFetch = window.fetch;
    window.fetch = function (input, init) {
        if (typeof input === 'string') {
            input = window.capsuleUrl(input);
        }

        return nativeFetch.call(this, input, init);
    };
})();
