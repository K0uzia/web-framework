(function () {
    function copyText(text) {
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            return navigator.clipboard.writeText(text);
        }

        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'absolute';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        var ok = false;
        try {
            ok = document.execCommand('copy');
        } catch (error) {
            ok = false;
        }
        document.body.removeChild(textarea);

        return ok ? Promise.resolve() : Promise.reject(new Error('copy_failed'));
    }

    function initCodeExample(root) {
        root.querySelectorAll('[data-code-copy]').forEach(function (button) {
            if (button.dataset.codeCopyInit === '1') {
                return;
            }
            button.dataset.codeCopyInit = '1';
            button.addEventListener('click', function () {
                var panel = button.closest('[data-code-panel]');
                var code = panel ? panel.querySelector('code') : null;
                if (!code) {
                    return;
                }
                copyText(code.textContent || '').then(function () {
                    button.setAttribute('aria-label', 'Code copié');
                    window.setTimeout(function () {
                        button.setAttribute('aria-label', 'Copier le code');
                    }, 2000);
                }).catch(function () {
                    button.setAttribute('aria-label', 'Échec de la copie');
                    window.setTimeout(function () {
                        button.setAttribute('aria-label', 'Copier le code');
                    }, 2000);
                });
            });
        });
    }

    document.querySelectorAll('[data-code-example]').forEach(initCodeExample);
})();
