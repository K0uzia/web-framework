(function () {
    'use strict';

    document.querySelectorAll('.site-header__toggle').forEach(function (btn) {
        var navId = btn.getAttribute('aria-controls');
        if (!navId) {
            return;
        }
        var nav = document.getElementById(navId);
        if (!nav) {
            return;
        }

        btn.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
            if (!nav.classList.contains('is-open')) {
                return;
            }
            if (nav.contains(event.target) || btn.contains(event.target)) {
                return;
            }
            nav.classList.remove('is-open');
            btn.setAttribute('aria-expanded', 'false');
        });
    });

    var groups = document.querySelectorAll('.site-nav__item--group');
    if (groups.length === 0) {
        return;
    }

    var desktop = window.matchMedia('(min-width: 1024px)');

    groups.forEach(function (item) {
        var details = item.querySelector('.site-nav__details');
        if (!details) {
            return;
        }

        details.addEventListener('toggle', function () {
            if (!details.open || !desktop.matches) {
                return;
            }
            groups.forEach(function (other) {
                if (other === item) {
                    return;
                }
                var otherDetails = other.querySelector('.site-nav__details');
                if (otherDetails && otherDetails.open) {
                    otherDetails.open = false;
                }
            });
        });

        if (!desktop.matches) {
            return;
        }

        var closeTimer;
        item.addEventListener('mouseenter', function () {
            window.clearTimeout(closeTimer);
            details.open = true;
        });
        item.addEventListener('mouseleave', function () {
            closeTimer = window.setTimeout(function () {
                details.open = false;
            }, 120);
        });
        item.addEventListener('focusin', function () {
            details.open = true;
        });
        item.addEventListener('focusout', function (event) {
            if (item.contains(event.relatedTarget)) {
                return;
            }
            details.open = false;
        });
    });

    document.addEventListener('click', function (event) {
        if (!desktop.matches) {
            return;
        }
        groups.forEach(function (item) {
            if (item.contains(event.target)) {
                return;
            }
            var details = item.querySelector('.site-nav__details');
            if (details) {
                details.open = false;
            }
        });
    });
})();
