const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  var postCount = 0;
  page.on('request', req => {
    if (req.method() === 'POST' && req.url().includes('/dev/site')) {
      postCount++;
      console.log('POST #' + postCount, req.url(), '| body:', req.postData());
    }
  });

  await page.goto('http://localhost:8080/dev/site');
  await page.waitForTimeout(300);

  console.log('--- typing into site_name (text field, hx-trigger=change,input delay:400ms) ---');
  const input = await page.$('#site_name');
  await input.click();
  await input.fill('');
  await page.keyboard.type('Nouveau Nom', { delay: 50 });
  console.log('typed, waiting for debounce + blur...');
  await page.waitForTimeout(200); // less than 400ms debounce
  await page.keyboard.press('Tab'); // blur -> triggers change immediately
  await page.waitForTimeout(1000); // let any pending debounce also fire
  console.log('total posts:', postCount);

  await browser.close();
})();
