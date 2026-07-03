const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
  page.on('pageerror', err => console.log('PAGEERROR:', err.message));
  page.on('console', msg => console.log('CONSOLE:', msg.type(), msg.text()));
  page.on('requestfailed', req => console.log('REQFAIL:', req.url(), req.failure()));
  page.on('request', req => console.log('REQUEST:', req.method(), req.url()));
  page.on('response', res => {
    console.log('RESPONSE:', res.status(), res.url());
  });

  await page.goto('http://localhost:8080/dev/site');
  await page.waitForTimeout(300);

  const fileInput = await page.$('#uploader-logo input[type="file"]');
  console.log('file input found:', !!fileInput);
  await fileInput.setInputFiles('/tmp/test-logo.png');
  await page.waitForTimeout(300);
  var formCheck = await page.evaluate(() => {
    var input = document.querySelector('#uploader-logo input[type=file]');
    return { hasForm: !!input.form, action: input.form ? input.form.action : null, files: input.files.length, hasAttr: input.hasAttribute('data-dev-autosubmit') };
  });
  console.log('formCheck:', JSON.stringify(formCheck));
  await page.waitForTimeout(1000);
  await page.screenshot({ path: '/tmp/screenshots/logo-after-upload.png', fullPage: true });

  const html = await page.$eval('#uploader-logo', el => el.outerHTML).catch(e => 'ERR: ' + e.message);
  console.log('UPLOADER HTML:', html);

  await browser.close();
})();
