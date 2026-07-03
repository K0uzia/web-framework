const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  page.on('request', req => {
    if (req.method() === 'POST') console.log('POST:', req.url());
  });

  await page.goto('http://localhost:8080/dev/site');
  await page.click('[data-tab="navigation"]');
  await page.waitForTimeout(300);

  const countBefore = await page.$$eval('.dev-nav-row', rows => rows.length);
  console.log('rows before:', countBefore);

  const deleteBtn = await page.$('.dev-nav-row__danger button');
  await deleteBtn.click();
  await page.waitForTimeout(200);
  await page.screenshot({ path: '/tmp/screenshots/nav-delete-confirm-modal.png', fullPage: true });
  await page.click('[data-dev-confirm-ok]');
  await page.waitForTimeout(600);

  const countAfter = await page.$$eval('.dev-nav-row', rows => rows.length);
  console.log('rows after clicking delete on first row:', countAfter);

  await browser.close();
})();
