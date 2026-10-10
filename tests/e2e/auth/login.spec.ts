import { test, expect } from '@playwright/test';
import { loginAs, collectPageProblems } from '../helpers/auth';

// Login tests must NEVER share the saved admin session: a real login
// regenerates the session server-side and would orphan state-admin.json
// for every test running after this file.
test.use({ storageState: { cookies: [], origins: [] } });

test.describe('auth', () => {
  test('login page renders', async ({ page }) => {
    await page.goto('/admin/login');
    await expect(page.getByRole('heading', { name: /masuk/i })).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
  });

  test('wrong password shows validation error, no login', async ({ page }) => {
    await page.goto('/admin/login');
    await page.locator('input[name="email"]').fill('e2e-admin@sekolah.test');
    await page.locator('input[name="password"]').fill('WrongPassword!');
    await page.getByRole('button', { name: /masuk/i }).click();
    await expect(page).toHaveURL(/admin\/login/);
    await expect(page.locator('body')).toContainText(/salah/i);
  });

  test('admin login lands on dashboard', async ({ page }) => {
    const { consoleErrors, failedRequests } = await collectPageProblems(page);
    await loginAs(page, 'e2e-admin@sekolah.test');
    await expect(page).toHaveURL(/admin\/dashboard/);
    expect(consoleErrors, `console errors: ${consoleErrors.join(' | ')}`).toEqual([]);
    expect(failedRequests, `failed requests: ${failedRequests.join(' | ')}`).toEqual([]);
  });

  test('guest hitting dashboard is redirected to login', async ({ page }) => {
    await page.goto('/admin/dashboard');
    await expect(page).toHaveURL(/admin\/login/);
  });
});
