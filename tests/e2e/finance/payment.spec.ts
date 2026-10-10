import { test, expect } from '@playwright/test';

/** Finance: OSIS program CRUD via the previously-missing view (regression). */
test.describe('finance smoke', () => {
  // Authenticated via setup project storageState (admin).

  test('osis program create → listed → reload persists → delete', async ({ page }) => {
    const title = `Proker E2E ${Date.now()}`;
    await page.goto('/admin/osis-programs');
    await expect(page.locator('h1')).toContainText(/program kerja osis/i);
    await page.locator('input[name="title"]').fill(title);
    await page.getByRole('button', { name: /simpan program/i }).click();
    await expect(page.locator('body')).toContainText(title);
    await page.reload();
    await expect(page.locator('body')).toContainText(title);

    page.on('dialog', (d) => d.accept());
    const row = page.locator('tr', { hasText: title });
    await row.getByRole('button', { name: /hapus/i }).click();
    await expect(page.locator('body')).not.toContainText(title);
  });

  test('fee invoices page renders with structure filter', async ({ page }) => {
    await page.goto('/admin/fee/invoices');
    await expect(page.locator('h1').first()).toBeVisible();
  });
});
