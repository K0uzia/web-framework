(function () {
    'use strict';

    var editor = document.querySelector('[data-admin-editor]');
    if (!editor) {
        return;
    }

    var form = document.getElementById('admin-page-form');
    var savedEls = editor.querySelectorAll('[data-save-state="saved"]');
    var dirtyEls = editor.querySelectorAll('[data-save-state="dirty"]');
    var sidebar = document.getElementById('admin-editor-sidebar');
    var backdrop = document.getElementById('admin-editor-sidebar-backdrop');
    var openBtn = document.getElementById('admin-editor-sidebar-open');
    var closeBtn = document.getElementById('admin-editor-sidebar-close');

    function setDirty(isDirty) {
        savedEls.forEach(function (el) {
            el.classList.toggle('is-visible', !isDirty);
        });
        dirtyEls.forEach(function (el) {
            el.classList.toggle('is-visible', isDirty);
        });
    }

    function openSidebar() {
        if (!sidebar) {
            return;
        }
        editor.classList.add('is-aside-open');
        sidebar.classList.add('is-open');
        if (backdrop) {
            backdrop.hidden = false;
        }
        if (openBtn) {
            openBtn.setAttribute('aria-expanded', 'true');
        }
    }

    function closeSidebar() {
        if (!sidebar) {
            return;
        }
        editor.classList.remove('is-aside-open');
        sidebar.classList.remove('is-open');
        if (backdrop) {
            backdrop.hidden = true;
        }
        if (openBtn) {
            openBtn.setAttribute('aria-expanded', 'false');
        }
    }

    function syncMediaPicker(picker, url) {
        var input = picker.querySelector('[data-admin-media-value]');
        if (input) {
            input.value = url;
        }
        picker.querySelectorAll('[data-admin-media-pick]').forEach(function (btn) {
            var selected = (btn.getAttribute('data-url') || '') === url;
            btn.classList.toggle('is-selected', selected);
            btn.setAttribute('aria-selected', selected ? 'true' : 'false');
        });
        var clearBtn = picker.querySelector('[data-admin-media-clear]');
        if (clearBtn) {
            clearBtn.classList.toggle('is-selected', url === '');
            clearBtn.setAttribute('aria-pressed', url === '' ? 'true' : 'false');
        }
        setDirty(true);
    }

    editor.addEventListener('click', function (event) {
        var pick = event.target.closest('[data-admin-media-pick]');
        if (pick) {
            event.preventDefault();
            var picker = pick.closest('[data-admin-media-picker]');
            if (picker) {
                syncMediaPicker(picker, pick.getAttribute('data-url') || '');
            }
            return;
        }
        var clear = event.target.closest('[data-admin-media-clear]');
        if (clear) {
            event.preventDefault();
            var clearPicker = clear.closest('[data-admin-media-picker]');
            if (clearPicker) {
                syncMediaPicker(clearPicker, '');
            }
        }
    });

    editor.addEventListener('change', function (event) {
        var select = event.target.closest('[data-link-picker-select]');
        if (!select) {
            return;
        }
        var picker = select.closest('[data-link-picker]');
        var input = picker ? picker.querySelector('[data-link-picker-input]') : null;
        if (input && select.value !== '') {
            input.value = select.value;
            setDirty(true);
        }
    });

    var accordion = editor.querySelector('[data-admin-accordion]');
    if (accordion) {
        accordion.addEventListener('toggle', function (event) {
            var details = event.target;
            if (!details || details.tagName !== 'DETAILS' || !details.open) {
                return;
            }
            accordion.querySelectorAll('details[data-admin-block]').forEach(function (other) {
                if (other !== details) {
                    other.open = false;
                }
            });
        }, true);
    }

    if (form) {
        form.addEventListener('input', function () { setDirty(true); });
        form.addEventListener('change', function () { setDirty(true); });
        form.addEventListener('submit', function () { setDirty(false); });
    }

    if (openBtn) {
        openBtn.addEventListener('click', openSidebar);
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }
    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    setDirty(false);
})();
