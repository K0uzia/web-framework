(function () {
    'use strict';

    var debounceTimers = new WeakMap();
    var inflightForms = new WeakMap();
    var SAVED_HTML = '<span class="dev-saved" role="status">Enregistré</span>';
    var pendingConfirmForm = null;

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

    function applyPreviewEmbedClass(frame) {
        try {
            var doc = frame.contentDocument;
            if (!doc || !doc.documentElement) {
                return;
            }
            doc.documentElement.classList.add('is-preview-embed');
            if (doc.body) {
                doc.body.classList.add('is-preview-embed');
            }
        } catch (e) {
            /* iframe inaccessible */
        }
    }

    function initPreviewEmbed() {
        var frame = document.getElementById('preview-frame');
        if (!frame) {
            return;
        }
        if (!frame.dataset.devPreviewEmbedInit) {
            frame.dataset.devPreviewEmbedInit = '1';
            frame.addEventListener('load', function () {
                applyPreviewEmbedClass(frame);
            });
        }
        if (frame.contentDocument && frame.contentDocument.readyState === 'complete') {
            applyPreviewEmbedClass(frame);
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
                var redirect = '';
                try {
                    redirect = response.headers.get('Location') || '';
                } catch (e) {
                    redirect = '';
                }
                return { ok: true, html: '', redirect: redirect };
            }
            if (!response.ok) {
                return response.text().then(function (text) {
                    return { ok: false, html: text || '', redirect: '' };
                });
            }
            return response.text().then(function (text) {
                return { ok: true, html: text, redirect: '' };
            });
        }).catch(function () {
            return { ok: false, html: '', redirect: '' };
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

        if (inflightForms.get(form)) {
            return;
        }
        inflightForms.set(form, true);

        var targetSel = form.getAttribute('hx-target');
        var swap = form.getAttribute('hx-swap') || 'none';
        var toastMessage = form.getAttribute('data-dev-toast-form');

        postForm(url, new URLSearchParams(new FormData(form)).toString())
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
            })
            .finally(function () {
                inflightForms.set(form, false);
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
        openSectionEditor(cards[cards.length - 1]);
    }

    var activeSectionCard = null;

    function openSectionEditor(card) {
        var modal = document.getElementById('dev-section-editor');
        var slot = document.getElementById('dev-section-editor-slot');
        var title = document.getElementById('dev-section-editor-title');
        if (!modal || !slot || !card) {
            return;
        }

        if (activeSectionCard && activeSectionCard !== card) {
            restoreSectionBody(activeSectionCard);
        }

        var body = card.querySelector('.dev-section-card__body');
        if (!body) {
            return;
        }

        activeSectionCard = card;
        slot.appendChild(body);
        card.classList.add('is-editing');

        if (title) {
            var label = card.querySelector('.dev-section-card__title span');
            title.textContent = label ? label.textContent.trim() : 'Modifier le bloc';
        }

        modal.hidden = false;
    }

    function restoreSectionBody(card) {
        if (!card) {
            return;
        }
        var slot = document.getElementById('dev-section-editor-slot');
        var body = slot ? slot.querySelector('.dev-section-card__body') : null;
        if (body) {
            card.appendChild(body);
        }
        card.classList.remove('is-editing');
        if (activeSectionCard === card) {
            activeSectionCard = null;
        }
    }

    function closeSectionEditor() {
        var modal = document.getElementById('dev-section-editor');
        if (activeSectionCard) {
            restoreSectionBody(activeSectionCard);
        }
        if (modal) {
            modal.hidden = true;
        }
    }

    function updateSectionCount() {
        var list = document.getElementById('dev-sections-list');
        var count = document.querySelector('.dev-outline__count');
        if (!count || !list) {
            return;
        }
        var n = list.querySelectorAll('.dev-section-card').length;
        count.textContent = n + ' bloc' + (n === 1 ? '' : 's');
    }

    function showSectionsEmptyState(list) {
        if (!list || list.querySelector('.dev-section-card')) {
            return;
        }
        list.innerHTML = '<p class="dev-empty" id="dev-sections-empty"><i class="fa-solid fa-layer-group" aria-hidden="true"></i>Aucun bloc. Ajoutez-en un ci-dessous pour construire la page.</p>';
    }

    function swapSectionsList(html) {
        var list = document.getElementById('dev-sections-list');
        html = safeSwapHtml(html, '');
        if (list && html.trim() !== '' && !isFullLayoutHtml(html)) {
            list.innerHTML = html;
            initAccordions(list);
            initSortable(list);
        }
        updateSectionCount();
    }

    function parseSectionDeleteContext(form) {
        var card = form.closest('.dev-section-card');
        if (!card) {
            return null;
        }
        var list = document.getElementById('dev-sections-list');
        var cards = list ? list.querySelectorAll('.dev-section-card') : [];
        var index = Array.prototype.indexOf.call(cards, card);
        var sectionData = null;
        try {
            sectionData = JSON.parse(card.getAttribute('data-section') || '');
        } catch (e) {
            sectionData = null;
        }
        if (!sectionData || !sectionData.id) {
            return null;
        }
        var labelEl = card.querySelector('.dev-section-card__title span');
        var label = labelEl ? labelEl.textContent.trim() : 'Bloc';
        var match = (form.getAttribute('action') || '').match(/^\/dev\/pages\/([^/]+)\/sections\//);
        var slug = match ? decodeURIComponent(match[1]) : '';
        return {
            card: card,
            index: index < 0 ? 0 : index,
            sectionData: sectionData,
            label: label,
            slug: slug,
        };
    }

    function restoreDeletedSection(slug, sectionData, index) {
        var body = new URLSearchParams();
        body.set('section', JSON.stringify(sectionData));
        body.set('index', String(index));
        return postForm('/dev/pages/' + encodeURIComponent(slug) + '/sections/restore', body.toString(), false)
            .then(function (result) {
                if (!result.ok) {
                    showToast('Échec de la restauration.', true);
                    return;
                }
                swapSectionsList(result.html);
                refreshPreview();
                showToast('Bloc restauré');
            });
    }

    function handleSectionDelete(form) {
        if (inflightForms.get(form)) {
            return;
        }
        var context = parseSectionDeleteContext(form);
        if (!context) {
            return;
        }

        inflightForms.set(form, true);
        var card = context.card;

        if (activeSectionCard === card) {
            closeSectionEditor();
        }

        card.remove();
        updateSectionCount();
        showSectionsEmptyState(document.getElementById('dev-sections-list'));

        var body = new URLSearchParams(new FormData(form)).toString();
        postForm(form.action, body, false)
            .then(function (result) {
                if (!result.ok) {
                    var list = document.getElementById('dev-sections-list');
                    if (list) {
                        var cards = list.querySelectorAll('.dev-section-card');
                        var ref = cards[context.index] || null;
                        if (ref) {
                            list.insertBefore(card, ref);
                        } else {
                            list.appendChild(card);
                        }
                        updateSectionCount();
                    }
                    showToast('Échec de la suppression.', true);
                    return;
                }

                refreshPreview();
                showUndoToast(context.label + ' supprimé', function () {
                    restoreDeletedSection(context.slug, context.sectionData, context.index);
                });
            })
            .finally(function () {
                inflightForms.set(form, false);
            });
    }

    function submitAjaxForm(form) {
        if (inflightForms.get(form)) {
            return;
        }
        inflightForms.set(form, true);

        var isMultipart = (form.getAttribute('enctype') || '').indexOf('multipart') !== -1;
        var body = isMultipart ? new FormData(form) : new URLSearchParams(new FormData(form)).toString();
        var toastMessage = form.getAttribute('data-dev-toast-form');

        postForm(form.action, body, isMultipart)
            .then(function (result) {
                var html = result.html;
                var mode = form.getAttribute('data-dev-ajax') || '';
                if (mode === 'sections') {
                    var list = document.getElementById('dev-sections-list');
                    var wasAdd = form.id === 'dev-add-section-form';
                    html = safeSwapHtml(html, '');
                    if (list && html.trim() !== '' && !isFullLayoutHtml(html)) {
                        list.innerHTML = html;
                        initAccordions(list);
                        initSortable(list);
                        if (wasAdd) {
                            openLastSectionCard();
                        }
                    }
                    updateSectionCount();
                    if (toastMessage) {
                        showToast(result.ok ? toastMessage : 'Échec de l\u2019opération.', !result.ok);
                    }
                }
                if (mode === 'nav') {
                    var navList = document.getElementById('dev-nav-list');
                    html = safeSwapHtml(html, '');
                    if (navList && html.trim() !== '' && !isFullLayoutHtml(html)) {
                        navList.innerHTML = html;
                        initSortable(navList);
                        initNavRows(navList);
                    }
                    if (toastMessage) {
                        showToast(result.ok ? toastMessage : 'Échec de l\u2019opération.', !result.ok);
                    }
                }
                if (mode.indexOf('media-') === 0) {
                    var field = mode.slice('media-'.length);
                    var uploader = document.getElementById('uploader-' + field);
                    html = safeSwapHtml(html, '');
                    if (uploader && html.trim() !== '' && !isFullLayoutHtml(html)) {
                        uploader.outerHTML = html;
                    }
                    if (result.ok) {
                        showToast(toastMessage || 'Fichier mis à jour');
                    } else if (html.indexOf('dev-uploader__error') !== -1) {
                        showToast('Échec de l\u2019import du fichier.', true);
                    }
                    refreshPreview();
                }
                if (mode === 'theme-reset') {
                    window.location.reload();
                    return;
                }
                if (mode === 'nav-reload') {
                    window.location.reload();
                    return;
                }
                if (mode === 'font-upload') {
                    if (result.ok && toastMessage) {
                        showToast(toastMessage);
                    }
                    window.location.reload();
                    return;
                }
                if (mode === 'post-redirect') {
                    if (result.ok) {
                        if (toastMessage) {
                            showToast(toastMessage);
                        }
                        closeModal('dev-page-new');
                        var redirectUrl = result.redirect || form.getAttribute('data-dev-redirect') || '/dev/pages';
                        window.setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 350);
                    } else {
                        var errMsg = (result.html && result.html.trim()) ? result.html.trim() : 'Échec de l\u2019opération.';
                        showToast(errMsg, true);
                    }
                    return;
                }
                if (toastMessage && mode !== 'sections' && mode !== 'nav' && mode.indexOf('media-') !== 0) {
                    showToast(result.ok ? toastMessage : 'Échec de l\u2019opération.', !result.ok);
                }
                refreshPreview();
            })
            .finally(function () {
                inflightForms.set(form, false);
            });
    }

    // Les événements input et change partagent le même timer par formulaire :
    // une case à cocher ou un <select> émet les deux quasi simultanément, ce qui
    // provoquait une double sauvegarde (et une notification en double).
    function scheduleHxSubmit(form, delay) {
        clearTimeout(debounceTimers.get(form));
        debounceTimers.set(form, setTimeout(function () {
            submitHxForm(form);
        }, delay));
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
        scheduleHxSubmit(form, parseDelay(trigger));
    });

    document.addEventListener('change', function (event) {
        var form = findHxForm(event.target);
        if (!form) {
            return;
        }
        var trigger = form.getAttribute('hx-trigger') || '';
        if (triggerMatches(trigger, 'change')) {
            scheduleHxSubmit(form, 150);
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (form.hasAttribute('data-dev-section-delete')) {
            event.preventDefault();
            handleSectionDelete(form);
            return;
        }

        var confirmMessage = form.getAttribute('data-dev-confirm') || form.getAttribute('data-confirm');
        if (confirmMessage) {
            event.preventDefault();
            openConfirmModal(confirmMessage, form);
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
        if (!(input instanceof HTMLInputElement) || !input.hasAttribute('data-dev-autosubmit')) {
            return;
        }
        if (input.type !== 'file' && input.type !== 'checkbox') {
            return;
        }
        event.stopPropagation();
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

    function showUndoToast(message, onUndo) {
        var container = document.getElementById('dev-toast-container');
        if (!container || !message) {
            return;
        }
        var toast = document.createElement('div');
        toast.className = 'dev-toast dev-toast--undo';
        toast.setAttribute('role', 'status');
        toast.innerHTML = '<i class="fa-solid fa-trash" aria-hidden="true"></i>'
            + '<span class="dev-toast__text"></span>'
            + '<button type="button" class="dev-toast__undo">Restaurer</button>';
        toast.querySelector('.dev-toast__text').textContent = message;
        container.appendChild(toast);
        window.requestAnimationFrame(function () {
            toast.classList.add('is-visible');
        });

        var dismissed = false;
        var timer = window.setTimeout(dismiss, 10000);

        function dismiss() {
            if (dismissed) {
                return;
            }
            dismissed = true;
            window.clearTimeout(timer);
            toast.classList.remove('is-visible');
            window.setTimeout(function () {
                toast.remove();
            }, 220);
        }

        toast.querySelector('.dev-toast__undo').addEventListener('click', function () {
            if (onUndo) {
                onUndo();
            }
            dismiss();
        });

        return { dismiss: dismiss };
    }

    window.DevToast = { show: showToast };

    function openConfirmModal(message, form) {
        var modal = document.getElementById('dev-confirm-modal');
        var messageEl = document.getElementById('dev-confirm-message');
        if (!modal || !messageEl) {
            if (form && window.confirm(message)) {
                form.submit();
            }
            return;
        }
        pendingConfirmForm = form || null;
        messageEl.textContent = message;
        modal.hidden = false;
        var okBtn = modal.querySelector('[data-dev-confirm-ok]');
        if (okBtn) {
            okBtn.focus();
        }
    }

    function closeConfirmModal() {
        var modal = document.getElementById('dev-confirm-modal');
        if (modal) {
            modal.hidden = true;
        }
        pendingConfirmForm = null;
    }

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-dev-confirm-cancel]')) {
            closeConfirmModal();
            return;
        }
        if (event.target.closest('[data-dev-confirm-ok]')) {
            var form = pendingConfirmForm;
            closeConfirmModal();
            if (!form) {
                return;
            }
            if (form.hasAttribute('data-dev-ajax')) {
                submitAjaxForm(form);
                return;
            }
            form.submit();
        }
    });

    function showFlashToast() {
        var flash = document.querySelector('.dev-flash');
        if (!flash) {
            return;
        }
        var message = flash.textContent.trim();
        if (message !== '') {
            showToast(message);
            flash.textContent = '';
            flash.classList.add('visually-hidden');
        }
        document.cookie = 'capsule_flash=; path=/dev; max-age=0';
    }

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
        if (id === 'dev-page-new') {
            var form = modal.querySelector('#dev-page-new-form');
            if (form) {
                form.reset();
            }
        }
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
            var modalId = modalCloser.getAttribute('data-dev-modal-close');
            if (modalId === 'dev-section-editor') {
                closeSectionEditor();
                return;
            }
            closeModal(modalId);
        }
    });

    document.addEventListener('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            openModal('dev-cmdk');
        }
        if (event.key === 'Escape') {
            closeConfirmModal();
            closeSectionEditor();
            cancelColorPick();
            document.querySelectorAll('.dev-modal').forEach(function (modal) {
                if (!modal.hidden && modal.id !== 'dev-confirm-modal' && modal.id !== 'dev-section-editor') {
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
        var filter = event.target.closest('[data-block-filter]');
        if (filter) {
            var group = filter.getAttribute('data-block-filter');
            var toolbar = filter.closest('.dev-block-picker__filters');
            if (toolbar) {
                toolbar.querySelectorAll('[data-block-filter]').forEach(function (btn) {
                    btn.classList.toggle('is-active', btn === filter);
                });
            }
            var grid = document.getElementById('dev-block-picker-grid');
            if (grid) {
                grid.querySelectorAll('[data-block-group]').forEach(function (card) {
                    var match = group === 'all' || card.getAttribute('data-block-group') === group;
                    card.classList.toggle('is-hidden', !match);
                });
            }
            return;
        }

        var card = event.target.closest('[data-block-type]');
        if (!card) {
            return;
        }
        var typeInput = document.getElementById('dev-add-section-type');
        var variantInput = document.getElementById('dev-add-section-variant');
        var form = document.getElementById('dev-add-section-form');
        if (!typeInput || !form) {
            return;
        }
        typeInput.value = card.getAttribute('data-block-type');
        if (variantInput) {
            variantInput.value = card.getAttribute('data-block-variant') || '';
        }
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
                openSectionEditor(card);
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
     * Preview show / hide toggle (chrome editor)
     * ------------------------------------------------------------------- */

    document.querySelectorAll('[data-dev-preview-toggle]').forEach(function (btn) {
        var builder = btn.closest('.dev-builder');
        if (!builder) {
            return;
        }
        var label = btn.querySelector('[data-preview-toggle-label]');
        var icon = btn.querySelector('i');

        function apply(hidden) {
            builder.classList.toggle('is-preview-hidden', hidden);
            btn.setAttribute('aria-expanded', hidden ? 'false' : 'true');
            if (label) {
                label.textContent = hidden ? 'Afficher l\u2019aperçu' : 'Masquer';
            }
            if (icon) {
                icon.className = hidden ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
            }
        }

        var stored = null;
        try {
            stored = window.localStorage.getItem('dev-preview-hidden');
        } catch (e) {
            stored = null;
        }
        apply(stored === '1');

        btn.addEventListener('click', function () {
            var hidden = !builder.classList.contains('is-preview-hidden');
            apply(hidden);
            try {
                window.localStorage.setItem('dev-preview-hidden', hidden ? '1' : '0');
            } catch (e) {
                /* stockage indisponible */
            }
        });
    });

    /* ---------------------------------------------------------------------
     * Color swatch <-> hex text sync + eyedropper
     * ------------------------------------------------------------------- */

    var activeColorPick = null;

    function normalizeHexColor(value) {
        var hex = String(value || '').trim();
        if (!/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(hex)) {
            return null;
        }
        if (hex.length === 4) {
            return '#' + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
        }
        return hex.toLowerCase();
    }

    function rgbStringToHex(value) {
        if (!value || value === 'transparent') {
            return null;
        }
        var match = value.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/);
        if (!match) {
            return null;
        }
        var alphaMatch = value.match(/^rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*([\d.]+)/);
        if (alphaMatch && parseFloat(alphaMatch[1]) === 0) {
            return null;
        }
        return '#' + [match[1], match[2], match[3]].map(function (channel) {
            return parseInt(channel, 10).toString(16).padStart(2, '0');
        }).join('');
    }

    function cssColorToHex(value) {
        if (!value || value === 'transparent') {
            return null;
        }
        var rgbHex = rgbStringToHex(value);
        if (rgbHex) {
            return rgbHex;
        }
        try {
            var canvas = document.createElement('canvas');
            canvas.width = 1;
            canvas.height = 1;
            var ctx = canvas.getContext('2d', { willReadFrequently: true });
            if (!ctx) {
                return null;
            }
            ctx.clearRect(0, 0, 1, 1);
            ctx.fillStyle = '#000000';
            ctx.fillStyle = value;
            ctx.fillRect(0, 0, 1, 1);
            var pixels = ctx.getImageData(0, 0, 1, 1).data;
            if (pixels[3] === 0) {
                return null;
            }
            return '#' + [pixels[0], pixels[1], pixels[2]].map(function (channel) {
                return channel.toString(16).padStart(2, '0');
            }).join('');
        } catch (e) {
            return null;
        }
    }

    function applyColorValue(colorInput, textInput, value) {
        var hex = normalizeHexColor(value) || cssColorToHex(String(value || ''));
        if (!hex) {
            return false;
        }
        colorInput.value = hex;
        textInput.value = hex;
        colorInput.dispatchEvent(new Event('input', { bubbles: true }));
        colorInput.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    }

    function rgbBytesToHex(r, g, b) {
        return '#' + [r, g, b].map(function (channel) {
            return channel.toString(16).padStart(2, '0');
        }).join('');
    }

    function readCanvasPixel(ctx, x, y) {
        var pixels = ctx.getImageData(x, y, 1, 1).data;
        if (pixels[3] === 0) {
            return null;
        }
        return rgbBytesToHex(pixels[0], pixels[1], pixels[2]);
    }

    function mapPointerInBox(clientX, clientY, rect) {
        var localX = clientX - rect.left;
        var localY = clientY - rect.top;
        if (localX < 0 || localY < 0 || localX > rect.width || localY > rect.height) {
            return null;
        }
        return { x: localX, y: localY };
    }

    function mapPointerToMediaRect(rect, mediaWidth, mediaHeight, objectFit) {
        var fit = objectFit || 'fill';
        if (!mediaWidth || !mediaHeight) {
            return null;
        }
        if (fit === 'fill' || fit === 'stretch') {
            return {
                offsetX: 0,
                offsetY: 0,
                width: rect.width,
                height: rect.height,
                mediaWidth: mediaWidth,
                mediaHeight: mediaHeight,
            };
        }

        var mediaRatio = mediaWidth / mediaHeight;
        var boxRatio = rect.width / rect.height;
        var renderWidth;
        var renderHeight;
        var offsetX;
        var offsetY;

        if (fit === 'contain') {
            if (mediaRatio > boxRatio) {
                renderWidth = rect.width;
                renderHeight = rect.width / mediaRatio;
                offsetX = 0;
                offsetY = (rect.height - renderHeight) / 2;
            } else {
                renderHeight = rect.height;
                renderWidth = rect.height * mediaRatio;
                offsetX = (rect.width - renderWidth) / 2;
                offsetY = 0;
            }
        } else if (fit === 'cover') {
            if (mediaRatio > boxRatio) {
                renderHeight = rect.height;
                renderWidth = rect.height * mediaRatio;
                offsetX = (rect.width - renderWidth) / 2;
                offsetY = 0;
            } else {
                renderWidth = rect.width;
                renderHeight = rect.width / mediaRatio;
                offsetX = 0;
                offsetY = (rect.height - renderHeight) / 2;
            }
        } else {
            return {
                offsetX: 0,
                offsetY: 0,
                width: rect.width,
                height: rect.height,
                mediaWidth: mediaWidth,
                mediaHeight: mediaHeight,
            };
        }

        return {
            offsetX: offsetX,
            offsetY: offsetY,
            width: renderWidth,
            height: renderHeight,
            mediaWidth: mediaWidth,
            mediaHeight: mediaHeight,
        };
    }

    function sampleImagePixel(img, clientX, clientY) {
        if (!img.complete || !img.naturalWidth) {
            return null;
        }
        var rect = img.getBoundingClientRect();
        var point = mapPointerInBox(clientX, clientY, rect);
        if (!point) {
            return null;
        }
        var mapped = mapPointerToMediaRect(rect, img.naturalWidth, img.naturalHeight, getComputedStyle(img).objectFit);
        if (!mapped) {
            return null;
        }
        var relX = (point.x - mapped.offsetX) / mapped.width;
        var relY = (point.y - mapped.offsetY) / mapped.height;
        if (relX < 0 || relX > 1 || relY < 0 || relY > 1) {
            return null;
        }
        var sourceX = Math.min(mapped.mediaWidth - 1, Math.max(0, Math.floor(relX * mapped.mediaWidth)));
        var sourceY = Math.min(mapped.mediaHeight - 1, Math.max(0, Math.floor(relY * mapped.mediaHeight)));
        var canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        var ctx = canvas.getContext('2d', { willReadFrequently: true });
        if (!ctx) {
            return null;
        }
        try {
            ctx.drawImage(img, sourceX, sourceY, 1, 1, 0, 0, 1, 1);
            return readCanvasPixel(ctx, 0, 0);
        } catch (e) {
            return null;
        }
    }

    function sampleCanvasPixel(canvasEl, clientX, clientY) {
        var rect = canvasEl.getBoundingClientRect();
        var point = mapPointerInBox(clientX, clientY, rect);
        if (!point || !canvasEl.width || !canvasEl.height) {
            return null;
        }
        var ctx = canvasEl.getContext('2d', { willReadFrequently: true });
        if (!ctx) {
            return null;
        }
        var x = Math.min(canvasEl.width - 1, Math.max(0, Math.floor((point.x / rect.width) * canvasEl.width)));
        var y = Math.min(canvasEl.height - 1, Math.max(0, Math.floor((point.y / rect.height) * canvasEl.height)));
        return readCanvasPixel(ctx, x, y);
    }

    function sampleHtmlVideoPixel(videoEl, clientX, clientY) {
        if (!videoEl.videoWidth) {
            return null;
        }
        var rect = videoEl.getBoundingClientRect();
        var point = mapPointerInBox(clientX, clientY, rect);
        if (!point) {
            return null;
        }
        var mapped = mapPointerToMediaRect(rect, videoEl.videoWidth, videoEl.videoHeight, getComputedStyle(videoEl).objectFit);
        if (!mapped) {
            return null;
        }
        var relX = (point.x - mapped.offsetX) / mapped.width;
        var relY = (point.y - mapped.offsetY) / mapped.height;
        if (relX < 0 || relX > 1 || relY < 0 || relY > 1) {
            return null;
        }
        var sourceX = Math.min(mapped.mediaWidth - 1, Math.max(0, Math.floor(relX * mapped.mediaWidth)));
        var sourceY = Math.min(mapped.mediaHeight - 1, Math.max(0, Math.floor(relY * mapped.mediaHeight)));
        var canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        var ctx = canvas.getContext('2d', { willReadFrequently: true });
        if (!ctx) {
            return null;
        }
        try {
            ctx.drawImage(videoEl, sourceX, sourceY, 1, 1, 0, 0, 1, 1);
            return readCanvasPixel(ctx, 0, 0);
        } catch (e) {
            return null;
        }
    }

    function sampleIframePixel(frame, clientX, clientY) {
        var rect = frame.getBoundingClientRect();
        var point = mapPointerInBox(clientX, clientY, rect);
        if (!point) {
            return null;
        }
        var doc = frame.contentDocument;
        var win = frame.contentWindow;
        if (!doc || !win) {
            return null;
        }
        var docW = doc.documentElement.clientWidth || rect.width;
        var docH = doc.documentElement.clientHeight || rect.height;
        if (!docW || !docH) {
            return null;
        }
        var sourceX = Math.min(docW - 1, Math.max(0, Math.floor((point.x / rect.width) * docW)));
        var sourceY = Math.min(docH - 1, Math.max(0, Math.floor((point.y / rect.height) * docH)));
        var canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        var ctx = canvas.getContext('2d', { willReadFrequently: true });
        if (!ctx) {
            return null;
        }
        try {
            ctx.drawImage(frame, sourceX, sourceY, 1, 1, 0, 0, 1, 1);
            return readCanvasPixel(ctx, 0, 0);
        } catch (e) {
            return null;
        }
    }

    function sampleRenderedElement(element, win, clientX, clientY) {
        if (!element || element.nodeType !== 1) {
            return null;
        }
        var tag = element.tagName;
        if (tag === 'IMG') {
            return sampleImagePixel(element, clientX, clientY);
        }
        if (tag === 'CANVAS') {
            return sampleCanvasPixel(element, clientX, clientY);
        }
        if (tag === 'VIDEO') {
            return sampleHtmlVideoPixel(element, clientX, clientY);
        }
        if (tag === 'IFRAME' && element.id === 'preview-frame') {
            return sampleIframePixel(element, clientX, clientY);
        }
        if (tag === 'svg' || tag === 'SVG') {
            var style = win.getComputedStyle(element);
            return cssColorToHex(style.fill) || cssColorToHex(style.stroke) || cssColorToHex(style.color);
        }
        return null;
    }

    function colorPickStack(win, clientX, clientY) {
        var stack = win.document.elementsFromPoint(clientX, clientY);
        var filtered = [];
        for (var i = 0; i < stack.length; i++) {
            var el = stack[i];
            if (el.nodeType !== 1 || el.closest('.dev-color-pick-ui')) {
                continue;
            }
            filtered.push(el);
        }
        return filtered;
    }

    function resolveVisibleBackgroundFromStack(stack, win) {
        for (var i = 0; i < stack.length; i++) {
            var bg = cssColorToHex(win.getComputedStyle(stack[i]).backgroundColor);
            if (bg) {
                return bg;
            }
        }
        return null;
    }

    function resolveForegroundFromStack(stack, win) {
        for (var i = 0; i < stack.length; i++) {
            var style = win.getComputedStyle(stack[i]);
            var borderWidths = [
                style.borderTopWidth,
                style.borderRightWidth,
                style.borderBottomWidth,
                style.borderLeftWidth,
            ];
            var borderColors = [
                style.borderTopColor,
                style.borderRightColor,
                style.borderBottomColor,
                style.borderLeftColor,
            ];
            for (var b = 0; b < borderColors.length; b++) {
                if (borderWidths[b] && borderWidths[b] !== '0px') {
                    var borderHex = cssColorToHex(borderColors[b]);
                    if (borderHex) {
                        return borderHex;
                    }
                }
            }
        }
        for (var k = 0; k < stack.length; k++) {
            var el = stack[k];
            var tag = el.tagName;
            if (tag === 'svg' || tag === 'SVG') {
                var svgStyle = win.getComputedStyle(el);
                var fillHex = cssColorToHex(svgStyle.fill);
                if (fillHex) {
                    return fillHex;
                }
                var strokeHex = cssColorToHex(svgStyle.stroke);
                if (strokeHex) {
                    return strokeHex;
                }
            }
            var textHex = cssColorToHex(win.getComputedStyle(el).color);
            if (textHex) {
                return textHex;
            }
        }
        return null;
    }

    function pickColorFromStack(stack, win, clientX, clientY) {
        if (!stack.length) {
            return null;
        }

        for (var i = 0; i < stack.length; i++) {
            var el = stack[i];
            if (el.id === 'preview-frame') {
                var fromFrame = sampleIframePixel(el, clientX, clientY);
                if (fromFrame) {
                    return fromFrame;
                }
            }
            var rendered = sampleRenderedElement(el, win, clientX, clientY);
            if (rendered) {
                return rendered;
            }
        }

        var background = resolveVisibleBackgroundFromStack(stack, win);
        if (background) {
            return background;
        }

        var foreground = resolveForegroundFromStack(stack, win);
        if (foreground) {
            return foreground;
        }

        return cssColorToHex(win.getComputedStyle(win.document.documentElement).backgroundColor)
            || cssColorToHex(win.getComputedStyle(win.document.body).backgroundColor);
    }

    function pickColorAtPoint(clientX, clientY) {
        var frame = document.getElementById('preview-frame');
        if (frame) {
            var frameRect = frame.getBoundingClientRect();
            if (clientX >= frameRect.left && clientX <= frameRect.right
                && clientY >= frameRect.top && clientY <= frameRect.bottom) {
                var iframePixel = sampleIframePixel(frame, clientX, clientY);
                if (iframePixel) {
                    return iframePixel;
                }
                try {
                    var doc = frame.contentDocument;
                    if (doc) {
                        var iframeStack = colorPickStack(
                            frame.contentWindow,
                            clientX - frameRect.left,
                            clientY - frameRect.top
                        );
                        var frameHex = pickColorFromStack(iframeStack, frame.contentWindow, clientX, clientY);
                        if (frameHex) {
                            return frameHex;
                        }
                    }
                } catch (e) {
                    /* iframe inaccessible */
                }
            }
        }

        return pickColorFromStack(colorPickStack(window, clientX, clientY), window, clientX, clientY);
    }

    function cancelColorPick() {
        if (!activeColorPick) {
            return;
        }
        var state = activeColorPick;
        activeColorPick = null;
        if (state.cleanup) {
            state.cleanup();
        }
    }

    function mapPointerToVideo(video, clientX, clientY) {
        if (!video.videoWidth || !video.videoHeight) {
            return null;
        }
        var rect = video.getBoundingClientRect();
        var videoRatio = video.videoWidth / video.videoHeight;
        var elementRatio = rect.width / rect.height;
        var renderWidth;
        var renderHeight;
        var offsetX;
        var offsetY;

        if (videoRatio > elementRatio) {
            renderWidth = rect.width;
            renderHeight = rect.width / videoRatio;
            offsetX = 0;
            offsetY = (rect.height - renderHeight) / 2;
        } else {
            renderHeight = rect.height;
            renderWidth = rect.height * videoRatio;
            offsetX = (rect.width - renderWidth) / 2;
            offsetY = 0;
        }

        var localX = clientX - rect.left - offsetX;
        var localY = clientY - rect.top - offsetY;
        if (localX < 0 || localY < 0 || localX > renderWidth || localY > renderHeight) {
            return null;
        }

        return {
            x: Math.min(video.videoWidth - 1, Math.max(0, Math.floor((localX / renderWidth) * video.videoWidth))),
            y: Math.min(video.videoHeight - 1, Math.max(0, Math.floor((localY / renderHeight) * video.videoHeight))),
        };
    }

    function sampleVideoAt(video, clientX, clientY) {
        var point = mapPointerToVideo(video, clientX, clientY);
        if (!point) {
            return null;
        }
        var canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        var ctx = canvas.getContext('2d', { willReadFrequently: true });
        if (!ctx) {
            return null;
        }
        ctx.drawImage(video, point.x, point.y, 1, 1, 0, 0, 1, 1);
        var pixels = ctx.getImageData(0, 0, 1, 1).data;
        if (pixels[3] === 0) {
            return null;
        }
        return '#' + [pixels[0], pixels[1], pixels[2]].map(function (channel) {
            return channel.toString(16).padStart(2, '0');
        }).join('');
    }

    function waitForVideoFrame(video) {
        return new Promise(function (resolve) {
            if (video.videoWidth > 0 && video.videoHeight > 0) {
                resolve();
                return;
            }
            var onMeta = function () {
                video.removeEventListener('loadeddata', onMeta);
                resolve();
            };
            video.addEventListener('loadeddata', onMeta);
            video.play().catch(function () {
                resolve();
            });
        });
    }

    function startDocumentColorPick(colorInput, textInput) {
        cancelColorPick();

        var bar = document.createElement('div');
        bar.className = 'dev-color-pick-ui dev-color-pick-ui__bar';
        bar.setAttribute('role', 'status');
        bar.innerHTML = '<span>Cliquez sur la couleur visible à prélever (fond, image, texte).</span>'
            + '<button type="button" class="dev-button dev-button--ghost dev-button--sm" data-color-screen-fallback>Autre fenêtre ou écran</button>'
            + '<button type="button" class="dev-button dev-button--ghost dev-button--sm" data-color-pick-cancel>Annuler</button>';

        var preview = document.createElement('div');
        preview.className = 'dev-color-pick-ui dev-color-pick-ui__preview';
        preview.setAttribute('aria-hidden', 'true');
        preview.hidden = true;

        document.body.appendChild(bar);
        document.body.appendChild(preview);
        document.body.classList.add('dev-color-pick-open');

        function finish() {
            cancelColorPick();
        }

        function onMove(event) {
            var hex = pickColorAtPoint(event.clientX, event.clientY);
            if (!hex) {
                preview.hidden = true;
                return;
            }
            preview.hidden = false;
            preview.style.backgroundColor = hex;
            preview.style.left = Math.min(event.clientX + 16, window.innerWidth - 60) + 'px';
            preview.style.top = Math.min(event.clientY + 16, window.innerHeight - 60) + 'px';
            preview.textContent = hex;
        }

        function onClick(event) {
            if (event.target.closest('.dev-color-pick-ui__bar')) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            var hex = pickColorAtPoint(event.clientX, event.clientY);
            if (!applyColorValue(colorInput, textInput, hex || '')) {
                showToast('Couleur non détectée sur cet élément.', true);
            }
            finish();
        }

        function onKey(event) {
            if (event.key === 'Escape') {
                finish();
            }
        }

        bar.querySelector('[data-color-pick-cancel]').addEventListener('click', finish);
        bar.querySelector('[data-color-screen-fallback]').addEventListener('click', function () {
            finish();
            startDisplayMediaColorPick(colorInput, textInput);
        });

        document.addEventListener('mousemove', onMove, true);
        document.addEventListener('click', onClick, true);
        document.addEventListener('keydown', onKey, true);

        activeColorPick = {
            cleanup: function () {
                document.removeEventListener('mousemove', onMove, true);
                document.removeEventListener('click', onClick, true);
                document.removeEventListener('keydown', onKey, true);
                document.body.classList.remove('dev-color-pick-open');
                if (bar.parentNode) {
                    bar.parentNode.removeChild(bar);
                }
                if (preview.parentNode) {
                    preview.parentNode.removeChild(preview);
                }
            },
        };
    }

    function startDisplayMediaColorPick(colorInput, textInput) {
        if (!navigator.mediaDevices || typeof navigator.mediaDevices.getDisplayMedia !== 'function') {
            showToast('Prélèvement à l\'écran non disponible dans ce navigateur.', true);
            return;
        }

        cancelColorPick();

        navigator.mediaDevices.getDisplayMedia({ video: { cursor: 'never' }, audio: false })
            .then(function (stream) {
                var overlay = document.createElement('div');
                overlay.className = 'dev-color-screen-pick dev-color-pick-ui';
                overlay.setAttribute('role', 'dialog');
                overlay.setAttribute('aria-modal', 'true');
                overlay.setAttribute('aria-label', 'Prélèvement de couleur');

                var video = document.createElement('video');
                video.className = 'dev-color-screen-pick__video';
                video.setAttribute('playsinline', '');
                video.muted = true;
                video.srcObject = stream;

                var hint = document.createElement('p');
                hint.className = 'dev-color-screen-pick__hint';
                hint.textContent = 'Cliquez pour prélever la couleur. Échap pour annuler.';

                var preview = document.createElement('div');
                preview.className = 'dev-color-pick-ui__preview dev-color-screen-pick__preview';
                preview.setAttribute('aria-hidden', 'true');
                preview.hidden = true;

                var catcher = document.createElement('button');
                catcher.type = 'button';
                catcher.className = 'dev-color-screen-pick__catcher';
                catcher.setAttribute('aria-label', 'Prélever la couleur sous le curseur');
                catcher.disabled = true;

                overlay.appendChild(video);
                overlay.appendChild(catcher);
                overlay.appendChild(preview);
                overlay.appendChild(hint);
                document.body.appendChild(overlay);
                document.body.classList.add('dev-color-screen-pick-open');

                function stopStream() {
                    stream.getTracks().forEach(function (track) {
                        track.stop();
                    });
                }

                function finish() {
                    cancelColorPick();
                }

                function onEscape(event) {
                    if (event.key === 'Escape') {
                        finish();
                    }
                }

                function onMove(event) {
                    var hex = sampleVideoAt(video, event.clientX, event.clientY);
                    if (!hex) {
                        preview.hidden = true;
                        return;
                    }
                    preview.hidden = false;
                    preview.style.backgroundColor = hex;
                    preview.style.left = Math.min(event.clientX + 16, window.innerWidth - 60) + 'px';
                    preview.style.top = Math.min(event.clientY + 16, window.innerHeight - 60) + 'px';
                    preview.textContent = hex;
                }

                function onPick(event) {
                    var hex = sampleVideoAt(video, event.clientX, event.clientY);
                    if (!applyColorValue(colorInput, textInput, hex || '')) {
                        showToast('Couleur non détectée.', true);
                    }
                    finish();
                }

                waitForVideoFrame(video).then(function () {
                    catcher.disabled = false;
                });

                catcher.addEventListener('mousemove', onMove);
                catcher.addEventListener('click', onPick);
                document.addEventListener('keydown', onEscape);

                activeColorPick = {
                    cleanup: function () {
                        document.removeEventListener('keydown', onEscape);
                        document.body.classList.remove('dev-color-screen-pick-open');
                        stopStream();
                        if (overlay.parentNode) {
                            overlay.parentNode.removeChild(overlay);
                        }
                    },
                };
            })
            .catch(function (error) {
                if (error && error.name === 'NotAllowedError') {
                    showToast('Partage d\'écran refusé.', true);
                    return;
                }
                showToast('Impossible de démarrer le prélèvement à l\'écran.', true);
            });
    }

    function openColorEyedropper(colorInput, textInput) {
        startDocumentColorPick(colorInput, textInput);
    }

    function initColorFields(scope) {
        (scope || document).querySelectorAll('[data-color-sync]').forEach(function (wrap) {
            if (wrap.dataset.colorSyncInit === '1') {
                return;
            }
            wrap.dataset.colorSyncInit = '1';

            var colorInput = wrap.querySelector('input[type="color"]');
            var textInput = wrap.querySelector('[data-color-text]');
            if (!colorInput || !textInput) {
                return;
            }

            var initialHex = normalizeHexColor(textInput.value)
                || normalizeHexColor(colorInput.value)
                || cssColorToHex(textInput.value);
            if (initialHex) {
                colorInput.value = initialHex;
                textInput.value = initialHex;
            }

            colorInput.addEventListener('input', function () {
                var hex = normalizeHexColor(colorInput.value);
                if (hex) {
                    textInput.value = hex;
                }
            });

            colorInput.addEventListener('change', function () {
                applyColorValue(colorInput, textInput, colorInput.value);
            });

            textInput.addEventListener('change', function () {
                var hex = normalizeHexColor(textInput.value) || cssColorToHex(textInput.value);
                if (hex) {
                    applyColorValue(colorInput, textInput, hex);
                }
            });

            var pickBtn = wrap.querySelector('[data-color-eyedropper]');
            if (pickBtn) {
                pickBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    openColorEyedropper(colorInput, textInput);
                });
            }
        });
    }

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

    function syncNavRowTarget(row) {
        if (!row) {
            return;
        }
        var typeSelect = row.querySelector('[data-nav-row-type]');
        var target = row.querySelector('[data-nav-row-target]');
        var input = target ? target.querySelector('[data-link-picker-input]') : null;
        if (!typeSelect || !target) {
            return;
        }
        var type = typeSelect.value;
        target.setAttribute('data-nav-type', type);
        if (!input) {
            return;
        }
        if (type === 'link') {
            input.placeholder = 'https://exemple.fr';
            input.setAttribute('aria-label', 'URL externe');
        } else if (type === 'button') {
            input.placeholder = '/contact ou https://exemple.fr';
            input.setAttribute('aria-label', 'Destination du bouton');
        } else {
            input.placeholder = 'https://exemple.fr ou /page';
            input.setAttribute('aria-label', 'Chemin de la page');
        }
    }

    function syncNavAddTarget() {
        var typeSelect = document.getElementById('nav_type');
        var picker = document.querySelector('.dev-nav-add [data-link-picker]');
        if (!typeSelect || !picker) {
            return;
        }
        var input = picker.querySelector('[data-link-picker-input]');
        var type = typeSelect.value;
        picker.setAttribute('data-nav-type', type);
        if (input) {
            if (type === 'link') {
                input.placeholder = 'https://exemple.fr';
            } else if (type === 'button') {
                input.placeholder = '/contact ou https://exemple.fr';
            } else {
                input.placeholder = 'https://exemple.fr ou /page';
            }
        }
    }

    function initNavRows(root) {
        var scope = root || document;
        scope.querySelectorAll('[data-nav-row]').forEach(syncNavRowTarget);
        if (!root || root.id === 'dev-nav-list' || root === document) {
            syncNavAddTarget();
        }
    }

    document.addEventListener('change', function (event) {
        var typeSelect = event.target.closest('[data-nav-row-type], #nav_type');
        if (!typeSelect) {
            return;
        }
        if (typeSelect.id === 'nav_type') {
            syncNavAddTarget();
            return;
        }
        syncNavRowTarget(typeSelect.closest('[data-nav-row]'));
    });

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
        initColorFields(document);
        initNavRows(document);
        initPreviewEmbed();
        showFlashToast();
        if (window.location.hash === '#new') {
            openModal('dev-page-new');
            history.replaceState(null, '', window.location.pathname + window.location.search);
        }
    });

    initColorFields(document);
    initPreviewEmbed();
})();
