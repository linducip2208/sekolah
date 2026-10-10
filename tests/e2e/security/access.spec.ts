import { test, expect } from '@playwright/test';

/** Access control via saved role states (no repeated logins → no throttle). */
test.describe('security access', () => {
  test.use({ storageState: 'storage/app/audit-artifacts/state-teacher.json' });

  test('teacher session is authenticated (not bounced to login)', async ({ page }) => {
    await page.goto('/admin/dashboard');
    await expect(page).not.toHaveURL(/admin\/login/);
  });
});

test.describe('parent isolation', () => {
  test.use({ storageState: 'storage/app/audit-artifacts/state-parent.json' });

  test('parent cannot open admin invoices page', async ({ page }) => {
    const res = await page.goto('/admin/fee/invoices');
    expect([401, 403, 302]).toContain(res?.status());
  });
});
