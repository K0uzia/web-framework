const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  var postCount = 0;
  page.on('request', req => {
    if (req.method() === 'POST' && req.url().includes('/dev/site/nav')) {
      postCount++;
      console.log('POST #' + postCount, req.url(), req.postData());
    }
  });

  await page.goto('http://localhost:8080/dev/site');
  await page.click('[data-tab="navigation"]');
  await page.waitForTimeout(300);

  console.log('--- selecting type only ---');
  const firstTypeSelect = await page.$('.dev-nav-row select[data-nav-row-type]');
  await firstTypeSelect.selectOption('link');
  await page.waitForTimeout(800);
  console.log('total posts after select:', postCount);

  console.log('--- filling href only (fill + blur) ---');
  const hrefInput = await page.$('.dev-nav-row [data-nav-row-href]');
  await hrefInput.fill('https://example.com/test');
  await page.keyboard.press('Tab');
  await page.waitForTimeout(800);
  console.log('total posts after href fill:', postCount);

  await browser.close();
})();
