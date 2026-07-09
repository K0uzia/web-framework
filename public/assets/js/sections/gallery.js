(function () {
    function getScroller(root) {
        if (root.matches('[data-gallery-track]')) {
            return root;
        }

        return root.querySelector('[data-gallery-track]');
    }

    function getSlides(root) {
        return Array.prototype.slice.call(root.querySelectorAll('.section-gallery__slide'));
    }

    function getSlideOffset(scroller, slide) {
        return slide.getBoundingClientRect().left - scroller.getBoundingClientRect().left + scroller.scrollLeft;
    }

    function getActiveIndex(scroller, slides) {
        if (slides.length === 0) {
            return 0;
        }

        var scrollLeft = scroller.scrollLeft;
        var closest = 0;
        var minDistance = Infinity;

        slides.forEach(function (slide, index) {
            var distance = Math.abs(getSlideOffset(scroller, slide) - scrollLeft);
            if (distance < minDistance) {
                minDistance = distance;
                closest = index;
            }
        });

        return closest;
    }

    function updateState(root) {
        if (!root) {
            return;
        }

        var scroller = getScroller(root);
        if (!scroller) {
            return;
        }

        var slides = getSlides(root);
        var section = root.closest('.section-gallery');
        if (!section) {
            return;
        }

        var id = root.getAttribute('data-gallery-id') || '';
        var nav = section.querySelector('[data-gallery-nav][data-gallery-id="' + id + '"]');
        var dotsWrap = section.querySelector('[data-gallery-dots]');
        var activeIndex = getActiveIndex(scroller, slides);
        var maxScroll = scroller.scrollWidth - scroller.clientWidth;
        var atStart = scroller.scrollLeft <= 1;
        var atEnd = scroller.scrollLeft >= maxScroll - 1;

        if (nav) {
            var prev = nav.querySelector('[data-gallery-prev]');
            var next = nav.querySelector('[data-gallery-next]');
            if (prev) {
                prev.disabled = atStart;
            }
            if (next) {
                next.disabled = atEnd;
            }
        }

        if (dotsWrap) {
            dotsWrap.querySelectorAll('[data-gallery-dot]').forEach(function (dot) {
                var index = Number(dot.getAttribute('data-gallery-dot'));
                var isActive = index === activeIndex;
                dot.classList.toggle('is-active', isActive);
                dot.setAttribute('aria-current', isActive ? 'true' : 'false');
            });
        }
    }

    function scrollToIndex(root, index) {
        var scroller = getScroller(root);
        if (!scroller) {
            return;
        }

        var slides = getSlides(root);
        if (slides.length === 0) {
            return;
        }

        var target = Math.max(0, Math.min(index, slides.length - 1));
        scroller.scrollTo({
            left: getSlideOffset(scroller, slides[target]),
            behavior: 'smooth',
        });
    }

    function scrollBySlide(root, direction) {
        var scroller = getScroller(root);
        if (!scroller) {
            return;
        }

        var slides = getSlides(root);
        if (slides.length === 0) {
            return;
        }

        var activeIndex = getActiveIndex(scroller, slides);
        scrollToIndex(root, activeIndex + direction);
    }

    function initCarousel(root) {
        var section = root.closest('.section-gallery');
        if (!section) {
            return;
        }

        var scroller = getScroller(root);
        var id = root.getAttribute('data-gallery-id') || '';
        var nav = section.querySelector('[data-gallery-nav][data-gallery-id="' + id + '"]');

        if (nav) {
            var prev = nav.querySelector('[data-gallery-prev]');
            var next = nav.querySelector('[data-gallery-next]');
            if (prev) {
                prev.addEventListener('click', function () {
                    scrollBySlide(root, -1);
                });
            }
            if (next) {
                next.addEventListener('click', function () {
                    scrollBySlide(root, 1);
                });
            }
        }

        section.querySelectorAll('[data-gallery-dot][data-gallery-id="' + id + '"]').forEach(function (dot) {
            dot.addEventListener('click', function () {
                var index = Number(dot.getAttribute('data-gallery-dot'));
                scrollToIndex(root, index);
            });
        });

        if (scroller) {
            scroller.addEventListener('scroll', function () {
                updateState(root);
            }, { passive: true });
        }

        window.addEventListener('resize', function () {
            updateState(root);
        });

        updateState(root);
    }

    document.querySelectorAll('[data-gallery-carousel]').forEach(initCarousel);
})();
