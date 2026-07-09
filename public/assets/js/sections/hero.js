(function () {
    function initSlider(root) {
        var slides = root.querySelectorAll('[data-hero-slide]');
        var features = root.closest('.section-hero--hero45');
        if (!features) return;
        var targets = features.querySelectorAll('[data-hero-slide-target]');
        if (!slides.length) return;

        function activate(index) {
            slides.forEach(function (slide) {
                slide.classList.toggle('is-active', slide.getAttribute('data-hero-slide') === String(index));
            });
            targets.forEach(function (feature) {
                feature.classList.toggle('is-active', feature.getAttribute('data-hero-slide-target') === String(index));
            });
        }

        targets.forEach(function (feature) {
            feature.addEventListener('mouseenter', function () {
                activate(feature.getAttribute('data-hero-slide-target'));
            });
            feature.addEventListener('focusin', function () {
                activate(feature.getAttribute('data-hero-slide-target'));
            });
        });

        var featuresWrap = features.querySelector('[data-hero-slider-features]');
        if (featuresWrap) {
            featuresWrap.addEventListener('mouseleave', function () {
                activate(0);
            });
        }

        activate(0);
    }

    function initTabs(root) {
        var tabs = root.querySelectorAll('[data-hero-tab]');
        var panels = root.querySelectorAll('[data-hero-tab-panel]');
        if (!tabs.length) return;

        function activate(index) {
            tabs.forEach(function (tab) {
                var on = tab.getAttribute('data-hero-tab') === String(index);
                tab.classList.toggle('is-active', on);
                tab.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            panels.forEach(function (panel) {
                var on = panel.getAttribute('data-hero-tab-panel') === String(index);
                panel.classList.toggle('is-active', on);
                panel.hidden = !on;
            });
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                activate(tab.getAttribute('data-hero-tab'));
            });
        });

        activate(tabs[0].getAttribute('data-hero-tab'));
    }

    function initFlip(root) {
        var words = root.querySelectorAll('.section-hero__flip-word');
        if (words.length < 2) {
            if (words[0]) {
                words[0].classList.add('is-active');
            }
            return;
        }

        var index = 0;
        words[0].classList.add('is-active');

        window.setInterval(function () {
            words[index].classList.remove('is-active');
            index = (index + 1) % words.length;
            words[index].classList.add('is-active');
        }, 2800);
    }

    document.querySelectorAll('[data-hero-slider]').forEach(initSlider);
    document.querySelectorAll('[data-hero-tabs]').forEach(initTabs);
    document.querySelectorAll('[data-hero-flip]').forEach(initFlip);
})();
