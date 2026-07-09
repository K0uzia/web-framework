(function () {
  function animateValue(el, target, duration) {
    var start = 0;
    var startTime = null;
    var suffix = el.getAttribute('data-suffix') || '';
    var prefix = el.getAttribute('data-prefix') || '';
    function frame(time) {
      if (!startTime) startTime = time;
      var progress = Math.min((time - startTime) / duration, 1);
      var value = Math.floor(progress * target);
      el.textContent = prefix + value.toLocaleString('fr-FR') + suffix;
      if (progress < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }
  function initCounter(section) {
    section.querySelectorAll('[data-ui-counter-value]').forEach(function (el) {
      var raw = el.getAttribute('data-target') || el.textContent || '0';
      var target = parseInt(raw.replace(/\D/g, ''), 10) || 0;
      if (!('IntersectionObserver' in window)) return;
      var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          animateValue(el, target, 1200);
          obs.disconnect();
        });
      }, { threshold: 0.3 });
      obs.observe(el);
    });
  }
  document.querySelectorAll('.section-ui-counter').forEach(initCounter);
})();
