(function () {
    var POLL_MS = 3000;

    function qs(sel, root) {
        return (root || document).querySelector(sel);
    }

    function qsa(sel, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(sel));
    }

    function initModeToggle() {
        var select = qs('[data-dev-video-import-mode]');
        if (!select) {
            return;
        }

        function sync() {
            var youtube = qs('[data-dev-video-import-youtube]');
            var upload = qs('[data-dev-video-import-upload]');
            var isUpload = select.value === 'upload';
            if (youtube) {
                youtube.hidden = isUpload;
            }
            if (upload) {
                upload.hidden = !isUpload;
            }
        }

        select.addEventListener('change', sync);
        sync();
    }

    function pollJob(card) {
        var id = card.getAttribute('data-video-id');
        var status = card.getAttribute('data-video-status');
        if (!id || status === 'ready' || status === 'failed') {
            return;
        }

        fetch('/dev/api/videos/' + encodeURIComponent(id) + '/status', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (!data || !data.status) {
                    return;
                }

                card.setAttribute('data-video-status', data.status);
                var statusEl = qs('[data-dev-video-status]', card);
                if (statusEl) {
                    statusEl.textContent = data.status + ' : ' + (data.message || '');
                }

                var bar = qs('.dev-video-import__progress > span', card);
                var progressWrap = qs('.dev-video-import__progress', card);
                if (bar) {
                    bar.style.width = String(data.progress || 0) + '%';
                }
                if (progressWrap) {
                    progressWrap.setAttribute('aria-valuenow', String(data.progress || 0));
                }

                if (data.status === 'ready' && data.public_video_url) {
                    var visual = qs('.dev-video-import__visual', card);
                    if (visual) {
                        visual.innerHTML = '<video class="dev-video-import__player" controls preload="metadata" playsinline src="/dev/api/videos/'
                            + encodeURIComponent(id) + '/stream"></video>';
                    }
                }
            })
            .catch(function () {
                /* silencieux */
            });
    }

    function initPolling() {
        setInterval(function () {
            qsa('[data-dev-video-import]').forEach(pollJob);
        }, POLL_MS);
    }

    function initApproveForms() {
        qsa('[data-dev-video-approve]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                }).then(function () {
                    window.location.reload();
                });
            });
        });
    }

    function initDeleteForms() {
        qsa('[data-dev-video-delete]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                if (!window.confirm('Annuler et supprimer cet import ?')) {
                    return;
                }
                fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                }).then(function (response) {
                    if (!response.ok) {
                        return response.json().then(function (data) {
                            window.alert(data.error || 'Suppression impossible.');
                        });
                    }
                    var card = form.closest('[data-dev-video-import]');
                    if (card) {
                        card.remove();
                    } else {
                        window.location.reload();
                    }
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initModeToggle();
        initPolling();
        initApproveForms();
        initDeleteForms();
    });
})();
