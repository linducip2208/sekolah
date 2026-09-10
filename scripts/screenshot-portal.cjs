/**
 * Portal visual verification — parent + student portal screenshots
 * (light + dark) at desktop and mobile widths.
 */
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = process.env.ESCHOOL_URL || 'http://localhost:8000';
const OUT_DIR = path.resolve(__dirname, '..', 'public', 'marketing', 'screens-portal');

const TARGETS = [
    // [url, file, viewport]
    ['/siswa', '01-student-portal-desktop.png', { width: 1440, height: 900 }],
    ['/siswa', '02-student-portal-mobile.png', { width: 390, height: 844 }],
    ['/portal', '03-parent-portal-desktop.png', { width: 1440, height: 900 }],
    ['/portal', '04-parent-portal-mobile.png', { width: 390, height: 844 }],
];

(async () => {
    if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });
    const browser = await chromium.launch({ headless: true });

    for (const [pathUrl, file, viewport] of TARGETS) {
        const context = await browser.newContext({ viewport });
        const page = await context.newPage();
        await page.addInitScript(() => {
            try { localStorage.setItem('sikadpro:theme', JSON.stringify('light')); } catch (e) {}
        });

        // Login as student (student portal) — student accounts share password seed
        await page.goto(BASE_URL + '/admin/login', { waitUntil: 'networkidle' });
        const isStudent = pathUrl.startsWith('/siswa');
        const email = isStudent ? 'siswa0_0@sman1demo.sch.id' : 'ortu.demo@sman1demo.sch.id';
        await page.locator('input[type="email"]').first().fill('');
        await page.type('input[type="email"]', email, { delay: 20 });
        await page.locator('input[type="password"]').first().fill('');
        await page.type('input[type="password"]', isStudent ? 'Siswa123!' : 'Ortu123!', { delay: 20 });
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle' }).catch(() => {}),
            page.locator('button[type="submit"]').first().click(),
        ]);

        const resp = await page.goto(BASE_URL + pathUrl, { waitUntil: 'networkidle', timeout: 30000 }).catch(e => null);
        await page.waitForTimeout(800);
        await page.screenshot({ path: path.join(OUT_DIR, file) });
        console.log(`saved: ${file} (HTTP ${resp ? resp.status() : 'n/a'}, url=${page.url()})`);
        await context.close();
    }
    await browser.close();
})();
