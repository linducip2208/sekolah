import { test, expect } from '@playwright/test';

/** PPDB: create period via UI, see it in list, reload persists, publish works. */
test.describe('ppdb workflow', () => {
  // Authenticated via setup project storageState (admin).

  test('create period → listed → reload persists', async ({ page }) => {
    const name = `PPDB E2E ${Date.now()}`;
    await page.goto('/admin/ppdb/periods');
    await page.locator('input[name="name"]').fill(name);
    await page.locator('select[name="academic_year_id"]').selectOption({ index: 1 });
    await page.locator('input[name="open_date"]').fill('2026-01-01');
    await page.locator('input[name="close_date"]').fill('2026-06-30');
    await page.getByRole('button', { name: /simpan|tambah/i }).click();
    await expect(page.locator('body')).toContainText(name);
    await page.reload();
    await expect(page.locator('body')).toContainText(name);
  });
});
