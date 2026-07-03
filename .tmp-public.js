const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 1400 } });
  page.on('pageerror', err => console.log('PAGEERROR:', err.message));
  page.on('console', msg => { if (msg.type() === 'error') console.log('CONSOLE ERROR:', msg.text()); });

  await page.goto('http://localhost:8080/');
  await page.waitForTimeout(300);
  await page.screenshot({ path: '/tmp/screenshots/public-home-full.png', fullPage: true });

  const mobile = await browser.newPage({ viewport: { width: 390, height: 844 } });
  await mobile.goto('http://localhost:8080/');
  await mobile.waitForTimeout(300);
  await mobile.screenshot({ path: '/tmp/screenshots/public-home-mobile.png', fullPage: true });

  await browser.close();
})();
