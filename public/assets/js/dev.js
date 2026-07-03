(function () {
    'use strict';

    var debounceTimers = new WeakMap();
    var lastSubmitted = new WeakMap();
    var SAVED_HTML = '<span class="dev-saved" role="status">Enregistré</span>';

    /* ---------------------------------------------------------------------
     * Auto-save engine (hx-post/hx-trigger emulation) + ajax forms
     * ------------------------------------------------------------------- */

    function findHxForm(el) {
        if (el.form && el.form.hasAttribute('hx-post')) {
            return el.form;
        }
        var formId = el.getAttribute('form');
        if (formId) {
            var linked = document.getElementById(formId);
            if (linked && linked.hasAttribute('hx-post')) {
                return linked;
            }
        }
        return el.closest('form[hx-post]');
    }

    function parseDelay(trigger) {
        var match = (trigger || '').match(/delay:(\d+)ms/);
        return match ? parseInt(match[1], 10) : 300;
    }

    function triggerMatches(trigger, eventType) {
        var parts = (trigger || 'change').split(',');
        for (var i = 0; i < parts.length; i++) {
            var part = parts[i].trim().split(/\s+/)[0];
            if (part === eventType) {
                return true;
            }
        }
        return false;
    }

    function refreshPreview() {
        var frame = document.getElementById('preview-frame');
        if (!frame) {
            return;
        }
        try {
            frame.contentWindow.location.reload();
        } catch (e) {
            frame.src = frame.src;
        }
    }

    function isFullLayoutHtml(html) {
        return html.indexOf('dev-app') !== -1 && html.indexOf('dev-topbar') !== -1;
    }

    function safeSwapHtml(html, targetSel) {
        if (!html || isFullLayoutHtml(html)) {
            if (targetSel && String(targetSel).indexOf('section-saved') !== -1) {
                return SAVED_HTML;
            }
            return '';
        }
        return html;
    }

    function postForm(url, body, isMultipart) {
        var headers = { 'HX-Request': 'true' };
        if (!isMultipart) {
            headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }
        return fetch(url, {
            method: 'POST',
            headers: headers,
            body: body,
            redirect: 'manual'
        }).then(function (response) {
            if (response.type === 'opaqueredirect' || (response.status >= 300 && response.status < 400)) {
                return { ok: true, html: '' };
            }
            if (!response.ok) {
                return response.text().then(function (text) {
                    return { ok: false, html: text || '' };
                });
            }
            return response.text().then(function (text) {
                return { ok: true, html: text };
            });
        }).catch(function () {
            return { ok: false, html: '' };
        });
    }

    function submitHxForm(form) {
        var url = form.getAttribute('hx-post');
        if (!url) {
            return;
        }

        if (form.hasAttribute('data-dev-ajax')) {
            submitAjaxForm(form);
            return;
        }

        var targetSel = form.getAttribute('hx-target');
        var swap = form.getAttribute('hx-swap') || 'none';
        var toastMessage = form.getAttribute('data-dev-toast-form');

        var serialized = new URLSearchParams(new FormData(form)).toString();
        if (lastSubmitted.get(form) === serialized) {
            // Valeurs identiques à la dernière sauvegarde : évite une double
            // requête quand le déclencheur "input" (debounce) et "change"
            // (au blur) se chevauchent pour un même champ.
            return;
        }
        lastSubmitted.set(form, serialized);

        postForm(url, serialized)
            .then(function (result) {
                var html = safeSwapHtml(result.html, targetSel);
                if (targetSel && swap !== 'none' && html) {
                    var target = document.querySelector(targetSel);
                    if (target) {
                        if (swap === 'outerHTML') {
                            target.outerHTML = html;
                        } else {
                            target.innerHTML = html;
                        }
                    }
                }
                if (toastMessage) {
                    showToast(result.ok ? toastMessage : 'Échec de l\u2019enregistrement.', !result.ok);
                }
                refreshPreview();
            });
    }

    function openLastSectionCard() {
        var list = document.getElementById('dev-sections-list');
        if (!list) {
            return;
        }
        var cards = list.querySelectorAll('.dev-section-card');
        if (cards.length === 0) {
            return;
        }
        var last = cards[cards.length - 1];
        setCardOpen(last, true);
        last.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function applyAjaxResult(result, mode, toastMessage) {
        var html = result.html;
        if (mode === 'sections' || mode === 'sections-add') {
            var list = document.getElementById('dev-sections-list');
            html = safeSwapHtml(html, '');
            if (list && html.trim() !== '' && !isFullLayoutHtml(html)) {
                list.innerHTML = html;
                initAccordions(list);
                initSortable(list);
                if (mode === 'sections-add') {
                    openLastSectionCard();
                }
            }
            var count = document.querySelector('.dev-outline__count');
            if (count && list) {
                var n = list.querySelectorAll('.dev-section-card').length;
                count.textContent = n + ' bloc' + (n === 1 ? '' : 's');
            }
        }
        if (mode === 'nav') {
            var navList = document.getElementById('dev-nav-list');
            html = safeSwapHtml(html, '');
            if (navList && html.trim() !== '' && !isFullLayoutHtml(html)) {
                navList.innerHTML = html;
                initSortable(navList);
            }
        }
        if (mode.indexOf('media-') === 0) {
            var field = mode.slice('media-'.length);
            var uploader = document.getElementById('uploader-' + field);
            html = safeSwapHtml(html, '');
            if (uploader && html.trim() !== '' && !isFullLayoutHtml(html)) {
                uploader.outerHTML = html;
            }
            refreshPreview();
        }
        if (mode === 'theme-reset' || mode === 'nav-reload') {
            window.location.reload();
            return;
        }
        if (toastMessage) {
            showToast(result.ok ? toastMessage : 'Échec de l\u2019opération.', !result.ok);
        }
        refreshPreview();
    }

    function submitAjaxForm(form) {
        var isMultipart = (form.getAttribute('enctype') || '').indexOf('multipart') !== -1;
        var body = isMultipart ? new FormData(form) : new URLSearchParams(new FormData(form)).toString();
        var toastMessage = form.getAttribute('data-dev-toast-form');
        var mode = form.getAttribute('data-dev-ajax') || '';
        if (mode === 'sections' && form.id === 'dev-add-section-form') {
            mode = 'sections-add';
        }

        postForm(form.action, body, isMultipart).then(function (result) {
            applyAjaxResult(result, mode, toastMessage);
        });
    }

    /* Actions ponctuelles (bouton seul, sans formulaire) : nécessaire pour les
     * actions imbriquées dans une liste déjà couverte par un <form> parent
     * (ex. supprimer une ligne de navigation) puisque les formulaires HTML ne
     * peuvent pas être imbriqués les uns dans les autres. */
    document.addEventListener('click', function (event) {
        var btn = event.target.closest('[data-dev-ajax-action]');
        if (!btn) {
            return;
        }
        var confirmMessage = btn.getAttribute('data-confirm');
        if (confirmMessage) {
            event.preventDefault();
            confirmModal(confirmMessage).then(function (confirmed) {
                if (confirmed) {
                    runAjaxAction(btn);
                }
            });
            return;
        }
        event.preventDefault();
        runAjaxAction(btn);
    });

    function runAjaxAction(btn) {
        var url = btn.getAttribute('data-dev-ajax-action');
        var mode = btn.getAttribute('data-dev-ajax-mode') || '';
        var toastMessage = btn.getAttribute('data-dev-toast-action') || '';
        postForm(url, '').then(function (result) {
            applyAjaxResult(result, mode, toastMessage);
        });
    }

    document.addEventListener('input', function (event) {
        var form = findHxForm(event.target);
        if (!form) {
            return;
        }
        var trigger = form.getAttribute('hx-trigger') || '';
        if (!triggerMatches(trigger, 'input')) {
            return;
        }
        clearTimeout(debounceTimers.get(form));
        debounceTimers.set(form, setTimeout(function () {
            submitHxForm(form);
        }, parseDelay(trigger)));
    });

    document.addEventListener('change', function (event) {
        var form = findHxForm(event.target);
        if (!form) {
            return;
        }
        var trigger = form.getAttribute('hx-trigger') || '';
        if (triggerMatches(trigger, 'change')) {
            // Un blur juste après une saisie peut coïncider avec le debounce
            // "input" encore en attente : on l'annule pour ne sauvegarder
            // qu'une seule fois (les valeurs sont de toute façon identiques).
            clearTimeout(debounceTimers.get(form));
            submitHxForm(form);
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        var confirmMessage = form.getAttribute('data-confirm');
        if (confirmMessage && form.dataset.devConfirmed !== '1') {
            event.preventDefault();
            confirmModal(confirmMessage).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }
                form.dataset.devConfirmed = '1';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
                delete form.dataset.devConfirmed;
            });
            return;
        }
        if (!form.hasAttribute('data-dev-ajax')) {
            return;
        }
        event.preventDefault();
        submitAjaxForm(form);
    });

    document.addEventListener('change', function (event) {
        var input = event.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.hasAttribute('data-dev-autosubmit')) {
            return;
        }
        if (input.form) {
            input.form.requestSubmit();
        }
    });

    window.DevPreview = { refresh: refreshPreview };

    /* ---------------------------------------------------------------------
     * Toast notifications (succès / échec des actions de sauvegarde)
     * ------------------------------------------------------------------- */

    function showToast(message, isError) {
        var container = document.getElementById('dev-toast-container');
        if (!container || !message) {
            return;
        }
        var toast = document.createElement('div');
        toast.className = 'dev-toast' + (isError ? ' dev-toast--error' : ' dev-toast--success');
        toast.setAttribute('role', 'status');
        toast.innerHTML = '<i class="fa-solid ' + (isError ? 'fa-circle-exclamation' : 'fa-circle-check') + '" aria-hidden="true"></i><span></span>';
        toast.querySelector('span').textContent = message;
        container.appendChild(toast);
        window.requestAnimationFrame(function () {
            toast.classList.add('is-visible');
        });
        setTimeout(function () {
            toast.classList.remove('is-visible');
            setTimeout(function () {
                toast.remove();
            }, 220);
        }, 4200);
    }

    window.DevToast = { show: showToast };

    /* ---------------------------------------------------------------------
     * Sidebar (mobile off-canvas)
     * ------------------------------------------------------------------- */

    (function initSidebar() {
        var app = document.querySelector('[data-dev-app]');
        if (!app) {
            return;
        }
        var toggles = document.querySelectorAll('[data-dev-sidebar-toggle]');
        var closers = document.querySelectorAll('[data-dev-sidebar-close]');

        function setOpen(open) {
            app.classList.toggle('is-sidebar-open', open);
            toggles.forEach(function (btn) {
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }

        toggles.forEach(function (btn) {
            btn.addEventListener('click', function () {
                setOpen(!app.classList.contains('is-sidebar-open'));
            });
        });
        closers.forEach(function (el) {
            el.addEventListener('click', function () {
                setOpen(false);
            });
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        });
    })();

    /* ---------------------------------------------------------------------
     * Theme toggle (dark/light, dashboard only)
     * ------------------------------------------------------------------- */

    (function initThemeToggle() {
        var STORAGE_KEY = 'capsuleDevTheme';
        var buttons = document.querySelectorAll('[data-dev-theme-toggle]');
        if (buttons.length === 0) {
            return;
        }

        function current() {
            return document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
        }

        function apply(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            try {
                window.localStorage.setItem(STORAGE_KEY, theme);
            } catch (e) {
                // navigation privée : le choix ne sera pas mémorisé.
            }
            buttons.forEach(function (btn) {
                var icon = btn.querySelector('[data-theme-icon]');
                var label = btn.querySelector('[data-theme-label]');
                btn.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
                if (icon) {
                    icon.className = theme === 'light' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
                }
                if (label) {
                    label.textContent = theme === 'light' ? 'Thème clair' : 'Thème sombre';
                }
            });
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                apply(current() === 'light' ? 'dark' : 'light');
            });
        });

        apply(current());
    })();

    /* ---------------------------------------------------------------------
     * Generic modal (command palette, block picker)
     * ------------------------------------------------------------------- */

    function openModal(id) {
        var modal = document.getElementById(id);
        if (!modal) {
            return;
        }
        modal.hidden = false;
        var input = modal.querySelector('input[type="text"]');
        if (input) {
            window.requestAnimationFrame(function () {
                input.focus();
                input.select();
            });
        } else {
            var focusable = modal.querySelector('button, a');
            if (focusable) {
                focusable.focus();
            }
        }
    }

    function closeModal(id) {
        var modal = document.getElementById(id);
        if (!modal) {
            return;
        }
        modal.hidden = true;
    }

    /* ---------------------------------------------------------------------
     * Modale de confirmation (remplace window.confirm pour rester dans le
     * design system et fonctionner correctement en environnement sandboxé).
     * ------------------------------------------------------------------- */

    var confirmResolver = null;

    function confirmModal(message) {
        var modal = document.getElementById('dev-confirm-modal');
        var text = document.getElementById('dev-confirm-message');
        if (!modal || !text) {
            return Promise.resolve(window.confirm(message));
        }
        text.textContent = message;
        modal.hidden = false;
        var okBtn = modal.querySelector('[data-dev-confirm-ok]');
        if (okBtn) {
            window.requestAnimationFrame(function () { okBtn.focus(); });
        }
        return new Promise(function (resolve) {
            confirmResolver = resolve;
        });
    }

    function settleConfirm(value) {
        var modal = document.getElementById('dev-confirm-modal');
        if (modal) {
            modal.hidden = true;
        }
        if (confirmResolver) {
            var resolve = confirmResolver;
            confirmResolver = null;
            resolve(value);
        }
    }

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-dev-confirm-ok]')) {
            settleConfirm(true);
            return;
        }
        if (event.target.closest('[data-dev-confirm-cancel]')) {
            settleConfirm(false);
        }
    });

    window.DevConfirm = { ask: confirmModal };

    document.addEventListener('change', function (event) {
        var picker = event.target.closest('[data-font-picker]');
        if (!picker) {
            return;
        }
        var input = document.querySelector('[data-font-picker-custom="' + picker.getAttribute('data-font-picker') + '"]');
        if (!input) {
            return;
        }
        if (picker.value === '__custom__') {
            input.hidden = false;
            input.focus();
            return;
        }
        input.hidden = true;
        input.value = picker.value;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    document.addEventListener('click', function (event) {
        var opener = event.target.closest('[data-dev-cmdk-open]');
        if (opener) {
            openModal('dev-cmdk');
            return;
        }
        var closer = event.target.closest('[data-dev-cmdk-close]');
        if (closer) {
            closeModal('dev-cmdk');
            return;
        }
        var pickerOpener = event.target.closest('[data-dev-modal-open]');
        if (pickerOpener) {
            openModal(pickerOpener.getAttribute('data-dev-modal-open'));
            return;
        }
        var modalCloser = event.target.closest('[data-dev-modal-close]');
        if (modalCloser) {
            closeModal(modalCloser.getAttribute('data-dev-modal-close'));
        }
    });

    document.addEventListener('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            openModal('dev-cmdk');
        }
        if (event.key === 'Escape') {
            settleConfirm(false);
            document.querySelectorAll('.dev-modal').forEach(function (modal) {
                if (!modal.hidden) {
                    modal.hidden = true;
                }
            });
        }
    });

    /* ---------------------------------------------------------------------
     * Command palette filtering
     * ------------------------------------------------------------------- */

    (function initCmdk() {
        var input = document.getElementById('dev-cmdk-input');
        var list = document.getElementById('dev-cmdk-list');
        if (!input || !list) {
            return;
        }
        var items = list.querySelectorAll('li');

        input.addEventListener('input', function () {
            var term = input.value.trim().toLowerCase();
            items.forEach(function (li) {
                var link = li.querySelector('a');
                var haystack = ((link ? link.textContent : '') + ' ' + (link ? link.getAttribute('data-cmdk-label') : '')).toLowerCase();
                li.classList.toggle('is-hidden', term !== '' && haystack.indexOf(term) === -1);
            });
        });
    })();

    /* ---------------------------------------------------------------------
     * Block picker: clicking a card fills + submits the hidden add form
     * ------------------------------------------------------------------- */

    document.addEventListener('click', function (event) {
        var card = event.target.closest('[data-block-type]');
        if (!card) {
            return;
        }
        var typeInput = document.getElementById('dev-add-section-type');
        var form = document.getElementById('dev-add-section-form');
        if (!typeInput || !form) {
            return;
        }
        typeInput.value = card.getAttribute('data-block-type');
        closeModal('dev-block-picker');
        submitAjaxForm(form);
    });

    /* ---------------------------------------------------------------------
     * Tabs
     * ------------------------------------------------------------------- */

    function initTabs(scope) {
        (scope || document).querySelectorAll('[data-dev-tabs]').forEach(function (root) {
            if (root.dataset.tabsInit === '1') {
                return;
            }
            root.dataset.tabsInit = '1';

            var tabs = root.querySelectorAll('[data-tab]');
            var panels = root.querySelectorAll('[data-tab-panel]');

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var target = tab.getAttribute('data-tab');
                    tabs.forEach(function (t) {
                        var active = t === tab;
                        t.classList.toggle('is-active', active);
                        t.setAttribute('aria-selected', active ? 'true' : 'false');
                    });
                    panels.forEach(function (panel) {
                        panel.classList.toggle('is-active', panel.getAttribute('data-tab-panel') === target);
                    });
                });
            });
        });
    }

    /* ---------------------------------------------------------------------
     * Section accordion (page builder outline)
     * ------------------------------------------------------------------- */

    function setCardOpen(card, open) {
        card.classList.toggle('is-open', open);
        var toggle = card.querySelector('[data-dev-accordion-toggle]');
        if (toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
    }

    function initAccordions(scope) {
        (scope || document).querySelectorAll('[data-dev-accordion-toggle]').forEach(function (btn) {
            if (btn.dataset.accordionInit === '1') {
                return;
            }
            btn.dataset.accordionInit = '1';
            btn.addEventListener('click', function () {
                var card = btn.closest('.dev-section-card');
                if (!card) {
                    return;
                }
                setCardOpen(card, !card.classList.contains('is-open'));
            });
        });
    }

    /* ---------------------------------------------------------------------
     * Drag & drop sortable lists (section outline, nav table rows)
     * ------------------------------------------------------------------- */

    function initSortable(scope) {
        (scope || document).querySelectorAll('[data-dev-sortable]').forEach(function (container) {
            if (container.dataset.sortableInit === '1') {
                return;
            }
            container.dataset.sortableInit = '1';

            var dragging = null;

            container.addEventListener('dragstart', function (event) {
                var item = event.target.closest('[data-dev-sortable-item]');
                if (!item) {
                    return;
                }
                dragging = item;
                item.classList.add('dev-drag-ghost');
                event.dataTransfer.effectAllowed = 'move';
                try {
                    event.dataTransfer.setData('text/plain', item.getAttribute('data-id') || '');
                } catch (e) {
                    // certains navigateurs exigent setData même si non utilisé ensuite.
                }
            });

            container.addEventListener('dragend', function () {
                if (dragging) {
                    dragging.classList.remove('dev-drag-ghost');
                }
                dragging = null;
                container.classList.remove('dev-drag-over');
                persistOrder(container);
            });

            container.addEventListener('dragover', function (event) {
                if (!dragging) {
                    return;
                }
                event.preventDefault();
                var afterEl = itemAfter(container, event.clientY);
                if (afterEl == null) {
                    container.appendChild(dragging);
                } else if (afterEl !== dragging) {
                    container.insertBefore(dragging, afterEl);
                }
            });
        });
    }

    function itemAfter(container, y) {
        var items = Array.prototype.slice.call(
            container.querySelectorAll('[data-dev-sortable-item]:not(.dev-drag-ghost)'),
        );
        var closest = null;
        var closestOffset = Number.NEGATIVE_INFINITY;
        items.forEach(function (child) {
            var box = child.getBoundingClientRect();
            var offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closestOffset) {
                closestOffset = offset;
                closest = child;
            }
        });
        return closest;
    }

    function persistOrder(container) {
        var url = container.getAttribute('data-dev-sortable-url');
        if (!url) {
            return;
        }
        var ids = Array.prototype.map.call(
            container.querySelectorAll('[data-dev-sortable-item]'),
            function (el) { return el.getAttribute('data-id'); },
        );
        postForm(url, 'order=' + encodeURIComponent(ids.join(','))).then(function () {
            refreshPreview();
        });
    }

    /* ---------------------------------------------------------------------
     * Device preview toolbar
     * ------------------------------------------------------------------- */

    document.querySelectorAll('[data-dev-device-toolbar]').forEach(function (toolbar) {
        var targetId = toolbar.getAttribute('data-target');
        var target = targetId ? document.getElementById(targetId) : null;
        var buttons = toolbar.querySelectorAll('[data-device]');

        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                buttons.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
                if (target) {
                    target.setAttribute('data-device', btn.getAttribute('data-device'));
                }
            });
        });
    });

    /* ---------------------------------------------------------------------
     * Color swatch <-> hex text sync
     * ------------------------------------------------------------------- */

    document.querySelectorAll('[data-color-sync]').forEach(function (wrap) {
        var colorInput = wrap.querySelector('input[type="color"]');
        var textInput = wrap.querySelector('[data-color-text]');
        if (!colorInput || !textInput) {
            return;
        }
        var hexPattern = /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/;

        colorInput.addEventListener('input', function () {
            textInput.value = colorInput.value;
        });

        textInput.addEventListener('input', function () {
            var value = textInput.value.trim();
            if (hexPattern.test(value)) {
                colorInput.value = value.length === 4
                    ? '#' + value[1] + value[1] + value[2] + value[2] + value[3] + value[3]
                    : value;
                colorInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    });

    /* ---------------------------------------------------------------------
     * Répéteur de boutons (blocs hero / appel à l'action) : ajout/suppression
     * ------------------------------------------------------------------- */

    function reindexButtonsRepeater(fieldset) {
        var rows = fieldset.querySelectorAll('[data-buttons-repeater-row]');
        rows.forEach(function (row, index) {
            row.querySelectorAll('[name]').forEach(function (input) {
                input.name = input.name.replace(/content_buttons_[^_]+_/, 'content_buttons_' + index + '_');
            });
        });
        var countField = fieldset.querySelector('[data-buttons-repeater-count]');
        if (countField) {
            countField.value = rows.length;
        }
    }

    document.addEventListener('click', function (event) {
        var addBtn = event.target.closest('[data-buttons-repeater-add]');
        if (addBtn) {
            var fieldset = addBtn.closest('[data-buttons-repeater]');
            var template = fieldset ? fieldset.querySelector('[data-buttons-repeater-template]') : null;
            var list = fieldset ? fieldset.querySelector('[data-buttons-repeater-list]') : null;
            if (!fieldset || !template || !list) {
                return;
            }
            var index = list.querySelectorAll('[data-buttons-repeater-row]').length;
            var wrapper = document.createElement('div');
            wrapper.innerHTML = template.innerHTML.split('__INDEX__').join(String(index)).trim();
            var row = wrapper.firstElementChild;
            if (!row) {
                return;
            }
            list.appendChild(row);
            var countField = fieldset.querySelector('[data-buttons-repeater-count]');
            if (countField) {
                countField.value = list.querySelectorAll('[data-buttons-repeater-row]').length;
                countField.dispatchEvent(new Event('input', { bubbles: true }));
            }
            var firstInput = row.querySelector('input[type="text"]');
            if (firstInput) {
                firstInput.focus();
            }
            return;
        }

        var removeBtn = event.target.closest('[data-buttons-repeater-remove]');
        if (removeBtn) {
            var row2 = removeBtn.closest('[data-buttons-repeater-row]');
            var fieldset2 = removeBtn.closest('[data-buttons-repeater]');
            if (!row2 || !fieldset2) {
                return;
            }
            row2.remove();
            reindexButtonsRepeater(fieldset2);
            var countField2 = fieldset2.querySelector('[data-buttons-repeater-count]');
            if (countField2) {
                countField2.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    });

    /* ---------------------------------------------------------------------
     * Link picker (page publiée du site OU URL libre)
     * ------------------------------------------------------------------- */

    document.addEventListener('change', function (event) {
        var select = event.target.closest('[data-link-picker-select]');
        if (!select) {
            return;
        }
        var picker = select.closest('[data-link-picker]');
        var input = picker ? picker.querySelector('[data-link-picker-input]') : null;
        if (!input || select.value === '') {
            if (input) {
                input.focus();
            }
            return;
        }
        input.value = select.value;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    /* ---------------------------------------------------------------------
     * Kebab menus (native <details>): close others + outside click + escape
     * ------------------------------------------------------------------- */

    document.addEventListener('toggle', function (event) {
        var el = event.target;
        if (!(el instanceof HTMLElement) || !el.matches('details.dev-menu')) {
            return;
        }
        if (el.open) {
            document.querySelectorAll('details.dev-menu[open]').forEach(function (other) {
                if (other !== el) {
                    other.open = false;
                }
            });
        }
    }, true);

    document.addEventListener('click', function (event) {
        document.querySelectorAll('details.dev-menu[open]').forEach(function (el) {
            if (!el.contains(event.target)) {
                el.open = false;
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('details.dev-menu[open]').forEach(function (el) {
                el.open = false;
            });
        }
    });

    /* ---------------------------------------------------------------------
     * Init on load
     * ------------------------------------------------------------------- */

    document.addEventListener('DOMContentLoaded', function () {
        initTabs(document);
        initAccordions(document);
        initSortable(document);
    });
})();
