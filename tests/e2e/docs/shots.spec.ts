import { test, expect } from '@playwright/test';

test('docs screenshots render', async ({ page }) => {
  await page.goto('/docs');
  await expect(page.locator('.browser-frame img').first()).toBeVisible();
  expect(await page.locator('.docs-chapter-card .img-responsive img').count()).toBeGreaterThan(3);
  await page.screenshot({ path: 'storage/app/audit-artifacts/docs-index.png' });

  await page.goto('/docs/admin');
  await expect(page.locator('.docs-gallery img').first()).toBeVisible();
  await page.screenshot({ path: 'storage/app/audit-artifacts/docs-show.png' });
});
