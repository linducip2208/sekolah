const { chromium } = require('playwright');

const BASE = 'http://127.0.0.1:8000';
const VIEWPORTS = [
    { name: '320px (SE)',  width: 320,  height: 667 },
    { name: '375px',       width: 375,  height: 667 },
    { name: '414px (11PM)',width: 414,  height: 896 },
    { name: '768px (iPad)',width: 768,  height: 1024 },
    { name: '1024px',      width: 1024, height: 768 },
    { name: '1280px',      width: 1280, height: 800 },
    { name: '1440px',      width: 1440, height: 900 },
];
const PAGES = [
    { path: '/admin/dashboard', name: 'dashboard' },
    { path: '/admin/students', name: 'students' },
    { path: '/admin/fee/invoices', name: 'invoices' },
    { path: '/admin/branding', name: 'branding' },
];

(async () => {
    const browser = await chromium.launch({ headless: true });
    const results = [];

    for (const vp of VIEWPORTS) {
        const context = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
        const page = await context.newPage();
        await page.goto(BASE + '/admin/login', { waitUntil: 'networkidle' });
        await page.fill('input[type=email]', 'admin@sman1demo.sch.id');
        await page.fill('input[type=password]', 'Admin123!');
        await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle' }), page.click('button[type=submit]')]);

        for (const p of PAGES) {
            try {
                await page.goto(BASE + p.path, { waitUntil: 'networkidle' });
                await page.waitForTimeout(500);
                const metrics = await page.evaluate(() => {
                    const doc = document.documentElement;
                    const overflowX = doc.scrollWidth > window.innerWidth + 1;
                    const sidebar = document.querySelector('aside');
                    const sidebarVisible = sidebar ? (sidebar.getBoundingClientRect().width > 10) : false;
                    // check table scroll container
                    const tableScroll = document.querySelector('.table-scroll');
                    const tableOverflow = tableScroll ? (tableScroll.scrollWidth > tableScroll.clientWidth + 1) : false;
                    return {
                        overflowX,
                        scrollWidth: doc.scrollWidth,
                        innerWidth: window.innerWidth,
                        sidebarVisible,
                        hasTable: !!tableScroll,
                        tableOverflow,
                    };
                });
                results.push({ vp: vp.name, page: p.name, ...metrics });
            } catch (e) {
                results.push({ vp: vp.name, page: p.name, error: e.message.split('\n')[0] });
            }
        }
        await context.close();
    }

    console.log('\n=== RESPONSIVE AUDIT ===');
    for (const r of results) {
        const flags = [];
        if (r.error) flags.push('ERROR: ' + r.error);
        else {
            if (r.overflowX) flags.push('H-OVERFLOW');
            if (!r.sidebarVisible && r.vp.includes('1024') === false && r.vp.includes('1280') === false && r.vp.includes('1440') === false) flags.push('sidebar-hidden(mobile, expected)');
            if (r.tableOverflow) flags.push('table-scroll(ok)');
        }
        console.log(`${r.vp.padEnd(14)} ${r.page.padEnd(10)} ${flags.length ? flags.join(', ') : 'OK'}`);
    }
    await browser.close();
})();
