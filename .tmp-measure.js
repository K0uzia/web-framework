const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 1400 } });
  await page.goto('http://localhost:8080/');
  await page.waitForTimeout(300);

  const info = await page.evaluate(() => {
    function box(sel) {
      var el = document.querySelector(sel);
      if (!el) return null;
      var r = el.getBoundingClientRect();
      var cs = getComputedStyle(el);
      return { sel: sel, top: r.top, bottom: r.bottom, height: r.height, minHeight: cs.minHeight, display: cs.display, flex: cs.flex };
    }
    var sections = Array.from(document.querySelectorAll('.site-main > *')).map(function (el, i) {
      var r = el.getBoundingClientRect();
      return { i: i, tag: el.tagName, cls: el.className, top: r.top, bottom: r.bottom, height: r.height };
    });
    return {
      body: box('.site-body'),
      main: box('.site-main'),
      footer: box('.site-footer'),
      sections: sections,
      bodyHeight: document.body.scrollHeight,
      viewportHeight: window.innerHeight,
    };
  });
  console.log(JSON.stringify(info, null, 2));

  await browser.close();
})();
