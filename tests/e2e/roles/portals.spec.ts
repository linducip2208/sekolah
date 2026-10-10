import { test, expect } from '@playwright/test';
import { collectPageProblems } from '../helpers/auth';

/** Teacher + student portal dashboards render for their roles. */
test.describe('teacher portal', () => {
  test.use({ storageState: 'storage/app/audit-artifacts/state-teacher.json' });

  test('teacher dashboard renders', async ({ page }) => {
    const { consoleErrors, failedRequests } = await collectPageProblems(page);
    const res = await page.goto('/guru');
    expect(res?.status()).toBeLessThan(400);
    await expect(page.locator('h1').first()).toContainText(/selamat datang/i);
    expect(consoleErrors).toEqual([]);
    expect(failedRequests).toEqual([]);
  });
});

test.describe('student portal', () => {
  test.use({ storageState: 'storage/app/audit-artifacts/state-student.json' });

  test('student dashboard renders', async ({ page }) => {
    const res = await page.goto('/siswa');
    expect(res?.status()).toBeLessThan(400);
    await expect(page.locator('h1').first()).toContainText(/halo/i);
  });
});
