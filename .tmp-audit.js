const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 1600 } });
  page.on('pageerror', err => console.log('PAGEERROR:', err.message));
  page.on('console', msg => { if (msg.type() === 'error') console.log('CONSOLE ERROR:', msg.text()); });

  await page.goto('http://localhost:8080/dev/site');
  await page.waitForTimeout(300);
  await page.screenshot({ path: '/tmp/screenshots/audit-site-identity.png', fullPage: true });

  await page.click('[data-tab="footer"]');
  await page.waitForTimeout(200);
  await page.screenshot({ path: '/tmp/screenshots/audit-site-footer.png', fullPage: true });

  await page.click('[data-tab="navigation"]');
  await page.waitForTimeout(200);
  await page.screenshot({ path: '/tmp/screenshots/audit-site-nav.png', fullPage: true });

  await page.goto('http://localhost:8080/dev/theme');
  await page.waitForTimeout(300);
  await page.screenshot({ path: '/tmp/screenshots/audit-theme-colors.png', fullPage: true });

  await page.goto('http://localhost:8080/dev/pages');
  await page.waitForTimeout(300);
  await page.screenshot({ path: '/tmp/screenshots/audit-pages-index.png', fullPage: true });

  await page.goto('http://localhost:8080/dev/pages/_');
  await page.waitForTimeout(300);
  await page.screenshot({ path: '/tmp/screenshots/audit-page-edit.png', fullPage: true });

  await browser.close();
})();
