(function () {
    'use strict';

    var input = document.getElementById('dev-pages-search');
    var rows = document.querySelectorAll('#dev-pages-rows tr[data-page-row]');
    var noMatch = document.getElementById('dev-pages-no-match');

    if (input && rows.length > 0) {
        input.addEventListener('input', function () {
            var term = input.value.trim().toLowerCase();
            var visible = 0;

            rows.forEach(function (row) {
                var haystack = (row.getAttribute('data-title') || '') + ' ' + (row.getAttribute('data-path') || '');
                var match = term === '' || haystack.indexOf(term) !== -1;
                row.hidden = !match;
                if (match) {
                    visible++;
                }
            });

            if (noMatch) {
                noMatch.classList.toggle('visually-hidden', visible !== 0);
            }
        });
    }

    var templateInput = document.getElementById('dev-new-page-template');
    var titleInput = document.getElementById('dev-new-page-title');
    var slugInput = document.getElementById('dev-new-page-slug');
    var descriptionInput = document.getElementById('dev-new-page-description');
    var hintEl = document.getElementById('dev-new-page-template-hint');
    var presetsEl = document.getElementById('dev-page-template-presets');
    var form = document.getElementById('dev-page-new-form');
    var grid = document.getElementById('dev-page-template-grid');

    if (!templateInput || !presetsEl || !grid) {
        return;
    }

    var presets = {};
    try {
        presets = JSON.parse(presetsEl.textContent || '{}');
    } catch (e) {
        presets = {};
    }

    var touched = { title: false, slug: false, description: false };

    if (titleInput) {
        titleInput.addEventListener('input', function () { touched.title = true; });
    }
    if (slugInput) {
        slugInput.addEventListener('input', function () { touched.slug = true; });
    }
    if (descriptionInput) {
        descriptionInput.addEventListener('input', function () { touched.description = true; });
    }

    function selectTemplateCard(id) {
        templateInput.value = id;
        grid.querySelectorAll('[data-page-template]').forEach(function (card) {
            var active = card.getAttribute('data-page-template') === id;
            card.classList.toggle('is-selected', active);
            card.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        applyTemplatePreset(id);
    }

    function applyTemplatePreset(id) {
        var preset = presets[id];
        if (!preset) {
            return;
        }

        if (hintEl && preset.hint) {
            hintEl.textContent = preset.hint;
        }

        if (titleInput && !touched.title) {
            titleInput.value = preset.title || '';
            titleInput.required = id !== 'blank';
        }
        if (slugInput && !touched.slug) {
            slugInput.value = preset.slug || '';
        }
        if (descriptionInput && !touched.description) {
            descriptionInput.value = preset.description || '';
        }

        if (form) {
            form.setAttribute(
                'data-dev-toast-form',
                preset.publish === false ? 'Page créée en brouillon' : 'Page créée et publiée',
            );
        }
    }

    function resetFormState() {
        touched = { title: false, slug: false, description: false };
        if (form) {
            form.reset();
        }
        selectTemplateCard(templateInput.value || 'blank');
    }

    grid.addEventListener('click', function (event) {
        var card = event.target.closest('[data-page-template]');
        if (!card) {
            return;
        }
        selectTemplateCard(card.getAttribute('data-page-template'));
    });

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-page-template-filter]')) {
            var filter = event.target.closest('[data-page-template-filter]');
            var group = filter.getAttribute('data-page-template-filter');
            var toolbar = filter.closest('.dev-page-template-filters');
            if (toolbar) {
                toolbar.querySelectorAll('[data-page-template-filter]').forEach(function (btn) {
                    btn.classList.toggle('is-active', btn === filter);
                });
            }
            grid.querySelectorAll('[data-page-template]').forEach(function (card) {
                var match = group === 'all' || card.getAttribute('data-page-template-category') === group;
                card.classList.toggle('is-hidden', !match);
            });
            return;
        }
    });

    document.addEventListener('dev-page-new-open', resetFormState);

    selectTemplateCard(templateInput.value || 'blank');
})();
