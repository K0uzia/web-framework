const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });

  const urls = [
    '/dev/site',
    '/dev/theme',
    '/dev/pages',
    '/dev/pages/_',
    '/dev/pages/new',
  ];

  for (const url of urls) {
    const res = await page.goto('http://localhost:8080' + url);
    const raw = await res.text();
    const rawOpenCount = (raw.match(/<form[\s>]/g) || []).length;
    const domCount = await page.evaluate(() => document.forms.length);
    console.log(url, '-> raw <form> tags:', rawOpenCount, '| DOM document.forms.length:', domCount, rawOpenCount !== domCount ? '  <<<< MISMATCH (nested form!)' : '');
  }

  await browser.close();
})();
