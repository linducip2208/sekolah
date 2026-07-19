/**
 * eSchool SaaS — Desktop Screenshot Script
 *
 * Launches Playwright, logs in to the admin panel (standard Blade form),
 * and captures 1440x900 PNG screenshots of key pages into public/marketing/screens/.
 *
 * Usage:
 *   1. Make sure dev server is running:
 *      php artisan serve --host=127.0.0.1 --port=8000
 *   2. Run: node scripts/screenshot.cjs
 */

const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = process.env.ESCHOOL_URL || 'http://localhost:8000';
const EMAIL = 'admin@sman1demo.sch.id';
const PASSWORD = 'Admin123!';

const OUT_DIR = path.resolve(__dirname, '..', 'public', 'marketing', 'screens');

const PUBLIC_PAGES = [
    { path: '/',                    file: '01-homepage.png',             title: 'Homepage' },
    { path: '/pricing',             file: '02-pricing.png',              title: 'Pricing' },
    { path: '/blog',                file: '03-blog.png',                 title: 'Blog' },
    { path: '/docs',                file: '04-docs.png',                 title: 'Documentation' },
    { path: '/api-docs',            file: '05-api-docs.png',             title: 'API Docs' },
    { path: '/daftar',              file: '06-school-registration.png',  title: 'School Registration' },
];

const ADMIN_PAGES = [
    { path: '/admin/dashboard',              file: '07-dashboard.png',             title: 'Dashboard' },
    { path: '/admin/academic/years',         file: '08-academic-years.png',        title: 'Academic Years' },
    { path: '/admin/students',               file: '09-students.png',              title: 'Students' },
    { path: '/admin/staff',                  file: '10-staff.png',                 title: 'Staff' },
    { path: '/admin/attendance',             file: '11-attendance.png',            title: 'Attendance' },
    { path: '/admin/timetable',              file: '12-timetable.png',             title: 'Timetable' },
    { path: '/admin/exams',                  file: '13-exams.png',                 title: 'Exams' },
    { path: '/admin/fee/structures',         file: '14-fee-structures.png',        title: 'Fee Structures' },
    { path: '/admin/fee/invoices',           file: '15-fee-invoices.png',          title: 'Fee Invoices' },
    { path: '/admin/payroll/slips',          file: '16-payroll-slips.png',         title: 'Payroll Slips' },
    { path: '/admin/library/books',          file: '17-library-books.png',         title: 'Library Books' },
    { path: '/admin/notices',                file: '18-notices.png',               title: 'Notices' },
    { path: '/admin/chat',                   file: '19-chat.png',                  title: 'Chat' },
    { path: '/admin/ppdb/applications',      file: '20-ppdb-applications.png',     title: 'PPDB Applications' },
    { path: '/admin/achievements/records',   file: '21-achievement-records.png',   title: 'Achievement Records' },
    { path: '/admin/discipline/records',     file: '22-discipline-records.png',    title: 'Discipline Records' },
    { path: '/admin/inventory/assets',       file: '23-inventory-assets.png',      title: 'Inventory Assets' },
    { path: '/admin/reports/spp-aging',      file: '24-reports-spp-aging.png',     title: 'Reports SPP Aging' },
    { path: '/admin/reports/attendance-pct', file: '25-reports-attendance.png',    title: 'Reports Attendance' },
];

(async () => {
    if (!fs.existsSync(OUT_DIR)) {
        fs.mkdirSync(OUT_DIR, { recursive: true });
    }

    console.log(`[screenshot] base url: ${BASE_URL}`);
    console.log(`[screenshot] output dir: ${OUT_DIR}`);

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        deviceScaleFactor: 1,
        locale: 'id-ID',
    });
    const page = await context.newPage();

    // ================================================================
    // Phase 1: Capture public pages (no auth required)
    // ================================================================
    let publicSuccess = 0;
    let publicFailed = [];

    console.log('\n[screenshot] === PUBLIC PAGES ===');
    for (const p of PUBLIC_PAGES) {
        const url = `${BASE_URL}${p.path}`;
        const outFile = path.join(OUT_DIR, p.file);
        try {
            console.log(`[screenshot] -> ${url}  (${p.title})`);
            const response = await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
            const status = response ? response.status() : 0;
            console.log(`    HTTP ${status}`);

            await page.waitForTimeout(1200);
            await page.screenshot({ path: outFile, fullPage: false });
            console.log(`    saved: ${p.file}`);
            publicSuccess++;
        } catch (err) {
            console.error(`    FAILED: ${err.message}`);
            publicFailed.push({ url, file: p.file, title: p.title, error: err.message });
        }
    }

    // ================================================================
    // Phase 2: Login
    // ================================================================
    console.log('\n[screenshot] === LOGIN ===');
    try {
        console.log(`[screenshot] navigating to ${BASE_URL}/admin/login`);
        await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await page.waitForLoadState('networkidle', { timeout: 30000 }).catch(() => {});

        const emailSel = 'input[type="email"]';
        const passSel  = 'input[type="password"]';

        await page.waitForSelector(emailSel, { timeout: 15000 });

        // Use fill() for standard Blade form (NOT Livewire)
        await page.locator(emailSel).first().fill(EMAIL);
        await page.locator(passSel).first().fill(PASSWORD);
        await page.waitForTimeout(500);

        await page.locator('button[type="submit"]').first().click();
        await page.waitForLoadState('networkidle', { timeout: 30000 }).catch(() => {});

        // Wait for redirect to dashboard
        await page.waitForURL('**/admin/dashboard', { timeout: 15000 }).catch(() => {});

        for (let i = 0; i < 10 && /\/login/.test(page.url()); i++) {
            await page.waitForTimeout(500);
        }

        console.log(`[screenshot] logged in, now at: ${page.url()}`);

        if (/\/login/.test(page.url())) {
            await page.screenshot({ path: path.join(OUT_DIR, '00-login-still-here.png') }).catch(() => {});
            throw new Error('Still on login page after submit');
        }
    } catch (err) {
        console.error('[screenshot] LOGIN FAILED:', err.message);
        await page.screenshot({ path: path.join(OUT_DIR, '00-login-fail.png'), fullPage: false }).catch(() => {});
        await browser.close();
        process.exit(1);
    }

    // ================================================================
    // Phase 3: Capture admin pages (after login)
    // ================================================================
    let adminSuccess = 0;
    let adminFailed = [];

    console.log('\n[screenshot] === ADMIN PAGES ===');
    for (const p of ADMIN_PAGES) {
        const url = `${BASE_URL}${p.path}`;
        const outFile = path.join(OUT_DIR, p.file);
        try {
            console.log(`[screenshot] -> ${url}  (${p.title})`);
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
    const totalPages = PUBLIC_PAGES.length + ADMIN_PAGES.length;
    const totalSuccess = publicSuccess + adminSuccess;
    const totalFailed = [...publicFailed, ...adminFailed];

    console.log('\n[screenshot] ===== SUMMARY =====');
    console.log(`  Public:  ${publicSuccess}/${PUBLIC_PAGES.length} succeeded`);
    console.log(`  Admin:   ${adminSuccess}/${ADMIN_PAGES.length} succeeded`);
    console.log(`  TOTAL:   ${totalSuccess}/${totalPages} succeeded`);

    if (publicFailed.length > 0) {
        console.log('\n  Public failures:');
        for (const f of publicFailed) console.log(`    - ${f.file} (${f.title}): ${f.error}`);
    }
    if (adminFailed.length > 0) {
        console.log('\n  Admin failures:');
        for (const f of adminFailed) console.log(`    - ${f.file} (${f.title}): ${f.error}`);
    }

    process.exit(totalFailed.length > 0 ? 1 : 0);
})();
