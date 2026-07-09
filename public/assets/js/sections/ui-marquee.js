(function () {
  function initMarquee(track) {
    if (track.dataset.uiMarqueeReady === '1') return;
    track.dataset.uiMarqueeReady = '1';
    var clone = track.cloneNode(true);
    clone.setAttribute('aria-hidden', 'true');
    track.parentElement.appendChild(clone);
  }
  document.querySelectorAll('[data-ui-marquee] .section-ui-marquee__track').forEach(initMarquee);
})();
