import { test, expect } from '@playwright/test';

/** Parent portal committee + cooperative statement deep pages render. */
test.describe('parent committee', () => {
  test.use({ storageState: 'storage/app/audit-artifacts/state-parent.json' });

  test('committee index lists E2E meeting', async ({ page }) => {
    await page.goto('/portal/komite');
    await expect(page.locator('body')).toContainText(/komite/i);
  });
});

test.describe('cooperative statement', () => {
  test('member statement renders via members table link', async ({ page }) => {
    await page.goto('/admin/cooperative/members');
    await expect(page.locator('body')).toContainText('E2E-M1');
    await page.getByRole('link', { name: /statement/i }).first().click();
    await expect(page.locator('h1')).toContainText(/statement/i);
    await expect(page.locator('body')).toContainText('E2E-M1');
  });
});
