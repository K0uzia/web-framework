(function () {
    function initTestimonials(root) {
        var slides = Array.prototype.slice.call(root.querySelectorAll('[data-demo-testimonial]'));
        if (slides.length <= 1) {
            var nav = root.querySelector('.section-demo__testimonials-nav--bookademo2');
            if (nav) {
                nav.hidden = true;
            }
            return;
        }

        var index = slides.findIndex(function (slide) {
            return slide.classList.contains('is-active');
        });
        if (index < 0) {
            index = 0;
        }

        function show(nextIndex) {
            index = (nextIndex + slides.length) % slides.length;
            slides.forEach(function (slide, i) {
                slide.classList.toggle('is-active', i === index);
                slide.hidden = i !== index;
            });
        }

        slides.forEach(function (slide, i) {
            slide.hidden = i !== index;
        });

        var prev = root.querySelector('[data-demo-testimonial-prev]');
        var next = root.querySelector('[data-demo-testimonial-next]');
        if (prev) {
            prev.addEventListener('click', function () {
                show(index - 1);
            });
        }
        if (next) {
            next.addEventListener('click', function () {
                show(index + 1);
            });
        }
    }

    document.querySelectorAll('[data-demo-testimonials]').forEach(initTestimonials);
})();
