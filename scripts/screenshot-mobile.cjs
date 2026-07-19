/**
 * eSchool SaaS — Mobile Screenshot Script
 *
 * Captures 5 critical pages in iPhone 11 Pro Max viewport
 * (414x896 @ 2x DPR) to public/marketing/screens-mobile/.
 *
 * Usage:
 *   1. Make sure dev server is running:
 *      php artisan serve --host=127.0.0.1 --port=8000
 *   2. Run: node scripts/screenshot-mobile.cjs
 */

const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = process.env.ESCHOOL_URL || 'http://localhost:8000';
const EMAIL = 'admin@sman1demo.sch.id';
const PASSWORD = 'Admin123!';

const OUT_DIR = path.resolve(__dirname, '..', 'public', 'marketing', 'screens-mobile');

const PAGES = [
    { path: '/',                    file: '01-homepage-mobile.png',     title: 'Homepage (mobile)' },
    { path: '/admin/dashboard',     file: '02-dashboard-mobile.png',    title: 'Admin Dashboard' },
    { path: '/admin/students',      file: '03-students-mobile.png',     title: 'Students list' },
    { path: '/admin/fee/invoices',  file: '04-fee-invoices-mobile.png', title: 'Fee Invoices' },
    { path: '/admin/attendance',    file: '05-attendance-mobile.png',   title: 'Attendance' },
];

(async () => {
    if (!fs.existsSync(OUT_DIR)) {
        fs.mkdirSync(OUT_DIR, { recursive: true });
    }

    console.log(`[mobile] base url: ${BASE_URL}`);
    console.log(`[mobile] output dir: ${OUT_DIR}`);

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 414, height: 896 },
        deviceScaleFactor: 2,
        isMobile: true,
        hasTouch: true,
        userAgent:
            'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1',
        locale: 'id-ID',
    });
    const page = await context.newPage();

    // ================================================================
    // Phase 1: Capture homepage (public, no login needed first)
    // ================================================================
    let publicSuccess = 0;
    let publicFailed = [];

    console.log('\n[mobile] === PUBLIC PAGE ===');
    const homePage = PAGES[0];
    const homeUrl = `${BASE_URL}${homePage.path}`;
    const homeFile = path.join(OUT_DIR, homePage.file);
    try {
        console.log(`[mobile] -> ${homeUrl}  (${homePage.title})`);
        const response = await page.goto(homeUrl, { waitUntil: 'networkidle', timeout: 30000 });
        const status = response ? response.status() : 0;
        console.log(`    HTTP ${status}`);
        await page.waitForTimeout(1200);
        await page.screenshot({ path: homeFile, fullPage: false });
        console.log(`    saved: ${homePage.file}`);
        publicSuccess++;
    } catch (err) {
        console.error(`    FAILED: ${err.message}`);
        publicFailed.push({ url: homeUrl, file: homePage.file, title: homePage.title, error: err.message });
    }

    // ================================================================
    // Phase 2: Login
    // ================================================================
    console.log('\n[mobile] === LOGIN ===');
    try {
        console.log(`[mobile] navigating to ${BASE_URL}/admin/login`);
        await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await page.waitForLoadState('networkidle', { timeout: 30000 }).catch(() => {});

        const emailSel = 'input[type="email"]';
        const passSel  = 'input[type="password"]';

        await page.waitForSelector(emailSel, { timeout: 15000 });

        // Use fill() for standard Blade form (NOT Livewire)
        await page.locator(emailSel).first().fill(EMAIL);
        await page.locator(passSel).first().fill(PASSWORD);
        await page.waitForTimeout(800);

        await page.locator('button[type="submit"]').first().click();
        await page.waitForLoadState('networkidle', { timeout: 30000 }).catch(() => {});

        // Wait for redirect to dashboard
        await page.waitForURL('**/admin/dashboard', { timeout: 15000 }).catch(() => {});

        for (let i = 0; i < 10 && /\/login/.test(page.url()); i++) {
            await page.waitForTimeout(500);
        }

        console.log(`[mobile] logged in, now at: ${page.url()}`);

        if (/\/login/.test(page.url())) {
            throw new Error('Still on login page after submit');
        }
    } catch (err) {
        console.error('[mobile] LOGIN FAILED:', err.message);
        await browser.close();
        process.exit(1);
    }

    // ================================================================
    // Phase 3: Capture admin pages (after login)
    // ================================================================
    let adminSuccess = 0;
    let adminFailed = [];

    console.log('\n[mobile] === ADMIN PAGES ===');
    for (const p of PAGES.slice(1)) {
        const url = `${BASE_URL}${p.path}`;
        const outFile = path.join(OUT_DIR, p.file);
        try {
            console.log(`[mobile] -> ${url}  (${p.title})`);
            const response = await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
            const status = response ? response.status() : 0;
            console.log(`    HTTP ${status}`);
            await page.waitForTimeout(1200);
            await page.screenshot({ path: outFile, fullPage: false });
            console.log(`    saved: ${p.file}`);
            adminSuccess++;
        } catch (err) {
            console.error(`    FAILED: ${err.message}`);
            adminFailed.push({ url, file: p.file, title: p.title, error: err.message });
        }
    }

    await browser.close();

    // ================================================================
    // Summary
    // ================================================================
    const totalSuccess = publicSuccess + adminSuccess;
    const totalFailed = [...publicFailed, ...adminFailed];

    console.log('\n[mobile] ===== SUMMARY =====');
    console.log(`  Public:  ${publicSuccess}/${PAGES.filter((_, i) => i === 0).length} succeeded`);
    console.log(`  Admin:   ${adminSuccess}/${PAGES.slice(1).length} succeeded`);
    console.log(`  TOTAL:   ${totalSuccess}/${PAGES.length} succeeded`);

    if (totalFailed.length > 0) {
        console.log('\n  Failures:');
        for (const f of totalFailed) console.log(`    - ${f.file} (${f.title}): ${f.error}`);
    }

    process.exit(totalFailed.length > 0 ? 1 : 0);
})();
