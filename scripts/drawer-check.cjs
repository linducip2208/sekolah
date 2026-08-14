const { chromium } = require('playwright');
(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 375, height: 667 } });
    const page = await context.newPage();
    await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' });
    await page.fill('input[type=email]', 'admin@sman1demo.sch.id');
    await page.fill('input[type=password]', 'Admin123!');
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle' }), page.click('button[type=submit]')]);
    await page.goto('http://127.0.0.1:8000/admin/dashboard', { waitUntil: 'networkidle' });

    // sidebar hidden initially
    let w = await page.evaluate(() => document.querySelector('aside').getBoundingClientRect().width);
    console.log('sidebar width before toggle:', w);

    // click hamburger
    await page.click('button[aria-label="Buka/tutup menu"]');
    await page.waitForTimeout(400);
    w = await page.evaluate(() => document.querySelector('aside').getBoundingClientRect().width);
    console.log('sidebar width after toggle:', w);

    // backdrop present?
    const backdrop = await page.evaluate(() => !!document.querySelector('.sidebar-backdrop'));
    console.log('backdrop present:', backdrop);

    // click a nav link closes drawer
    const link = await page.$('.sidebar-link');
    if (link) { await link.click(); await page.waitForTimeout(300); }
    w = await page.evaluate(() => document.querySelector('aside').getBoundingClientRect().width);
    console.log('sidebar width after nav click:', w);

    await browser.close();
})();
