(function () {
  function initTabs(root) {
    var items = root.querySelectorAll('[data-ui-tab-item]');
    if (!items.length) return;
    function activate(index) {
      items.forEach(function (item) {
        var tab = item.querySelector('.section-ui-tabs__tab');
        var panel = item.querySelector('.section-ui-tabs__panel');
        var on = item.getAttribute('data-index') === String(index);
        if (tab) {
          tab.setAttribute('aria-selected', on ? 'true' : 'false');
          tab.classList.toggle('is-active', on);
        }
        if (panel) {
          panel.hidden = !on;
        }
      });
    }
    items.forEach(function (item) {
      var tab = item.querySelector('.section-ui-tabs__tab');
      if (!tab) return;
      tab.addEventListener('click', function () {
        activate(item.getAttribute('data-index'));
      });
    });
    activate(items[0].getAttribute('data-index'));
  }
  document.querySelectorAll('[data-ui-tabs]').forEach(initTabs);
})();
