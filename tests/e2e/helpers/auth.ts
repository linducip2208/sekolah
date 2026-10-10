import { Page, expect } from '@playwright/test';

export const E2E_PASSWORD = 'Password123!';

/** Login via the school-admin web form. Fails loudly if the app is unreachable. */
export async function loginAs(page: Page, email: string, password = E2E_PASSWORD) {
  await page.goto('/admin/login');
  await page.getByLabel(/email/i).fill(email);
  await page.getByLabel(/kata sandi|password/i).fill(password);
  await page.getByRole('button', { name: /masuk|login/i }).click();
  await expect(page).not.toHaveURL(/admin\/login/, { timeout: 15_000 });
}

/** Collect console errors + failed requests during a callback scope. */
export async function collectPageProblems(page: Page) {
  const consoleErrors: string[] = [];
  const failedRequests: string[] = [];
  page.on('console', (msg) => {
    if (msg.type() === 'error') consoleErrors.push(msg.text().slice(0, 300));
  });
  page.on('requestfailed', (req) => failedRequests.push(`${req.method()} ${req.url().slice(0, 160)}`));
  page.on('response', (res) => {
    if (res.status() >= 500) failedRequests.push(`${res.status()} ${res.url().slice(0, 160)}`);
  });
  return { consoleErrors, failedRequests };
}
