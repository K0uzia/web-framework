(function () {
    var YT_ORIGINS = ['https://www.youtube.com', 'https://www.youtube-nocookie.com'];
    var CHROMELESS_FALLBACK_MS = 15000;
    var STANDARD_FALLBACK_MS = 2500;

    function revealChromeless(shell, iframe) {
        shell.classList.add('is-ready');
        if (iframe) {
            iframe.classList.add('is-ready');
        }
    }

    function revealStandard(shell, iframe) {
        shell.classList.add('is-playing');
        if (iframe) {
            iframe.classList.add('is-ready');
        }
    }

    function withOrigin(url) {
        if (!url || url.indexOf('enablejsapi=1') === -1 || url.indexOf('origin=') !== -1) {
            return url;
        }

        var separator = url.indexOf('?') !== -1 ? '&' : '?';

        return url + separator + 'origin=' + encodeURIComponent(window.location.origin);
    }

    function loadDeferred(iframe) {
        var url = iframe.getAttribute('data-src');
        if (!url || iframe.getAttribute('src')) {
            return;
        }

        iframe.setAttribute('src', withOrigin(url));
    }

    function isYouTubeIframe(iframe) {
        var src = iframe.getAttribute('src') || iframe.getAttribute('data-src') || '';

        return src.indexOf('youtube.com') !== -1 || src.indexOf('youtube-nocookie.com') !== -1;
    }

    function postToYouTube(iframe, payload) {
        if (!iframe.contentWindow) {
            return;
        }

        iframe.contentWindow.postMessage(JSON.stringify(payload), '*');
    }

    function youtubeState(info) {
        var value = Number(info);

        return Number.isFinite(value) ? value : -1;
    }

    function waitForYouTubePlaying(iframe, onPlaying) {
        var done = false;
        var readyAt = 0;

        function finish() {
            if (done) {
                return;
            }
            done = true;
            window.removeEventListener('message', onMessage);
            onPlaying();
        }

        function onMessage(event) {
            if (YT_ORIGINS.indexOf(event.origin) === -1) {
                return;
            }
            if (event.source !== iframe.contentWindow) {
                return;
            }

            var data;
            try {
                data = typeof event.data === 'string' ? JSON.parse(event.data) : event.data;
            } catch (error) {
                return;
            }

            if (!data || typeof data.event !== 'string') {
                return;
            }

            if (data.event === 'onReady') {
                readyAt = Date.now();
                postToYouTube(iframe, { event: 'command', func: 'playVideo', args: '' });
                postToYouTube(iframe, { event: 'listening' });
                return;
            }

            if (data.event === 'onStateChange' && youtubeState(data.info) === 1) {
                var elapsed = readyAt > 0 ? Date.now() - readyAt : 0;
                var delay = Math.max(180, 500 - elapsed);
                window.setTimeout(finish, delay);
            }
        }

        window.addEventListener('message', onMessage);

        function bootstrap() {
            postToYouTube(iframe, { event: 'listening' });
            postToYouTube(iframe, { event: 'command', func: 'playVideo', args: '' });
        }

        iframe.addEventListener('load', function () {
            bootstrap();
            var attempts = 0;
            var interval = window.setInterval(function () {
                if (done) {
                    window.clearInterval(interval);
                    return;
                }
                attempts += 1;
                if (attempts > 60) {
                    window.clearInterval(interval);
                    return;
                }
                bootstrap();
            }, 200);
        }, { once: true });

        window.setTimeout(finish, CHROMELESS_FALLBACK_MS);
    }

    function initShell(shell) {
        if (shell.dataset.heroVideoInit === '1') {
            return;
        }
        shell.dataset.heroVideoInit = '1';

        var iframe = shell.querySelector('.section-hero__iframe, .section-hero__backdrop-iframe');
        if (!iframe) {
            return;
        }

        var chromeless = shell.getAttribute('data-hero-video-chromeless') === '1';

        if (chromeless) {
            var reveal = function () {
                revealChromeless(shell, iframe);
            };

            if (isYouTubeIframe(iframe)) {
                waitForYouTubePlaying(iframe, reveal);
            } else {
                iframe.addEventListener('load', reveal, { once: true });
                window.setTimeout(reveal, CHROMELESS_FALLBACK_MS);
            }

            window.requestAnimationFrame(function () {
                loadDeferred(iframe);
            });
            return;
        }

        var revealed = false;

        function show() {
            if (revealed) {
                return;
            }
            revealed = true;
            revealStandard(shell, iframe);
        }

        if (iframe.getAttribute('data-src')) {
            iframe.addEventListener('load', show, { once: true });
            window.requestAnimationFrame(function () {
                loadDeferred(iframe);
            });
        } else {
            iframe.addEventListener('load', show, { once: true });
        }

        window.setTimeout(show, STANDARD_FALLBACK_MS);
    }

    document.querySelectorAll('[data-hero-video]').forEach(initShell);
})();
