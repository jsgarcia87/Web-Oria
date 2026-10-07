import { chromium, devices } from 'playwright';

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext(devices['iPhone 13']);
  const page = await context.newPage();
  
  // Go to the local server
  await page.goto('http://127.0.0.1:8080/trabajo.html');
  
  // Wait for animations and GSAP to settle
  await page.waitForTimeout(1500);
  
  // Accept cookies if present
  try {
      await page.click('#accept-cookies', { timeout: 1000 });
  } catch(e) {}
  
  // Scroll down by 500px to see the top section
  await page.evaluate(() => window.scrollBy(0, 500));
  await page.waitForTimeout(500);
  await page.screenshot({ path: '/tmp/screenshot1.png' });

  // Scroll down further to the areas-wrapper
  const wrapper = await page.$('.areas-wrapper');
  if (wrapper) {
      await wrapper.scrollIntoViewIfNeeded();
      await page.waitForTimeout(500);
      await page.screenshot({ path: '/tmp/screenshot2.png' });
      
      // Scroll more inside the wrapper to see the masking effect
      await page.evaluate(() => window.scrollBy(0, 400));
      await page.waitForTimeout(500);
      await page.screenshot({ path: '/tmp/screenshot3.png' });
  }

  await browser.close();
})();
