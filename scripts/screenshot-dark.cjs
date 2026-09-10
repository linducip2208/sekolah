/**
 * Dark mode visual audit — captures key pages with data-theme="dark"
 * forced via localStorage before load.
 */
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = process.env.ESCHOOL_URL || 'http://localhost:8000';
const EMAIL = 'admin@sman1demo.sch.id';
const PASSWORD = 'Admin123!';
const OUT_DIR = path.resolve(__dirname, '..', 'public', 'marketing', 'screens-dark');

const PAGES = [
    { path: '/admin/login', file: '01-login-dark.png' },
    { path: '/admin/dashboard', file: '02-dashboard-dark.png' },
    { path: '/admin/students', file: '03-students-dark.png' },
    { path: '/admin/fee/invoices', file: '04-invoices-dark.png' },
    { path: '/admin/my-work', file: '05-my-work-dark.png' },
];

(async () => {
    if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    // Seed theme BEFORE first navigation
    await page.addInitScript(() => {
        try { localStorage.setItem('sikadpro:theme', JSON.stringify('dark')); } catch (e) {}
    });

    await page.goto(BASE_URL + '/admin/login', { waitUntil: 'networkidle' });
    await page.locator('input[type="email"]').first().fill('');
    await page.type('input[type="email"]', EMAIL, { delay: 30 });
    await page.locator('input[type="password"]').first().fill('');
    await page.type('input[type="password"]', PASSWORD, { delay: 30 });
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {}),
        page.locator('button[type="submit"]').first().click(),
    ]);

    let ok = 0;
    for (const p of PAGES) {
        try {
            const resp = await page.goto(BASE_URL + p.path, { waitUntil: 'networkidle', timeout: 30000 });
            await page.waitForTimeout(700);
            await page.screenshot({ path: path.join(OUT_DIR, p.file), fullPage: false });
            console.log(`saved: ${p.file} (HTTP ${resp ? resp.status() : '?'})`);
            ok++;
        } catch (e) {
            console.log(`FAILED: ${p.path} — ${e.message.split('\n')[0]}`);
        }
    }
    console.log(`\n[dark] ${ok}/${PAGES.length} succeeded`);
    await browser.close();
})();
