(function () {
    'use strict';

    var PER_PAGE = 12;

    var searchInput = document.getElementById('admin-pages-search');
    var list = document.getElementById('admin-pages-rows');
    var noMatch = document.getElementById('admin-pages-no-match');
    var prevBtn = document.getElementById('admin-pages-prev');
    var nextBtn = document.getElementById('admin-pages-next');
    var pageNumbers = document.getElementById('admin-pages-page-numbers');
    var pagination = document.getElementById('admin-pages-pagination');

    if (!list) {
        return;
    }

    var rows = Array.prototype.slice.call(list.querySelectorAll('[data-page-row]'));
    var currentPage = 1;

    function matchesFilters(row) {
        var term = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var haystack = (row.getAttribute('data-title') || '') + ' ' + (row.getAttribute('data-path') || '');

        return term === '' || haystack.indexOf(term) !== -1;
    }

    function filteredRows() {
        return rows.filter(matchesFilters);
    }

    function totalPages(count) {
        return Math.max(1, Math.ceil(count / PER_PAGE));
    }

    function renderPageNumbers(total) {
        if (!pageNumbers) {
            return;
        }
        pageNumbers.innerHTML = '';
        for (var i = 1; i <= total; i++) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'admin-pagination__btn' + (i === currentPage ? ' is-active' : '');
            btn.textContent = String(i);
            btn.setAttribute('aria-label', 'Page ' + i);
            if (i === currentPage) {
                btn.setAttribute('aria-current', 'page');
            }
            btn.addEventListener('click', (function (page) {
                return function () {
                    currentPage = page;
                    render();
                };
            })(i));
            pageNumbers.appendChild(btn);
        }
    }

    function render() {
        var visible = filteredRows();
        var pages = totalPages(visible.length);
        if (currentPage > pages) {
            currentPage = pages;
        }

        var start = (currentPage - 1) * PER_PAGE;
        var pageRows = visible.slice(start, start + PER_PAGE);

        rows.forEach(function (row) {
            row.hidden = pageRows.indexOf(row) === -1;
        });

        if (noMatch) {
            noMatch.classList.toggle('hidden', visible.length !== 0);
        }
        if (pagination) {
            pagination.classList.toggle('hidden', visible.length <= PER_PAGE);
        }
        if (prevBtn) {
            prevBtn.disabled = currentPage <= 1;
        }
        if (nextBtn) {
            nextBtn.disabled = currentPage >= pages;
        }
        renderPageNumbers(pages);
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentPage = 1;
            render();
        });
    }
    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                render();
            }
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            if (currentPage < totalPages(filteredRows().length)) {
                currentPage++;
                render();
            }
        });
    }

    render();
})();
