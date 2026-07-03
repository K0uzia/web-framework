const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  page.on('pageerror', err => console.log('PAGEERROR:', err.message));
  page.on('console', msg => { if (msg.type() === 'error') console.log('CONSOLE ERROR:', msg.text()); });

  await page.goto('http://localhost:8080/dev/site');
  await page.click('[data-tab="navigation"]');
  await page.waitForTimeout(300);

  // Try changing the type of the first row (Accueil) from Page to Lien externe.
  const rows = await page.$$('.dev-nav-row');
  console.log('rows found:', rows.length);
  const firstTypeSelect = await page.$('.dev-nav-row select[data-nav-row-type]');
  await firstTypeSelect.selectOption('link');
  await page.waitForTimeout(600);

  const hrefVisible = await page.$eval('.dev-nav-row [data-nav-row-href]', el => !el.hidden).catch(e => 'ERR:' + e.message);
  console.log('href visible after switch:', hrefVisible);

  await page.screenshot({ path: '/tmp/screenshots/nav-after-type-switch.png', fullPage: true });

  // Try typing a value in the now-visible href field
  const hrefInput = await page.$('.dev-nav-row [data-nav-row-href]');
  await hrefInput.fill('https://example.com/test');
  await hrefInput.dispatchEvent('change');
  await page.waitForTimeout(600);

  await page.screenshot({ path: '/tmp/screenshots/nav-after-href-fill.png', fullPage: true });

  const html = await page.$eval('#dev-nav-list', el => el.innerHTML).catch(e => 'ERR: ' + e.message);
  console.log('NAV LIST HTML SNIPPET:', html.slice(0, 1500));

  await browser.close();
})();
