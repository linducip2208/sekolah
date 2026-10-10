import { test, expect } from '@playwright/test';

/** Public docs render on the Tabler layout (no auth needed). */
test.describe('docs guide', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('docs index renders chapter cards', async ({ page }) => {
    await page.goto('/docs');
    await expect(page.locator('h1.page-title')).toContainText(/buku panduan/i);
    expect(await page.locator('.docs-chapter-card').count()).toBeGreaterThan(3);
    await page.screenshot({ path: 'storage/app/audit-artifacts/docs-index.png' });
  });

  test('docs chapter renders accordion + credential table', async ({ page }) => {
    await page.goto('/docs/admin');
    await expect(page.locator('.accordion')).toBeVisible();
    await expect(page.locator('table.table')).toBeVisible();
    await expect(page.locator('.breadcrumb').first()).toBeVisible();
  });
});
