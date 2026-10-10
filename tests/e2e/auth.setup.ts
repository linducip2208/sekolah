import { test as setup, expect } from '@playwright/test';

const STATES = {
  'e2e-admin@sekolah.test': 'storage/app/audit-artifacts/state-admin.json',
  'e2e-teacher@sekolah.test': 'storage/app/audit-artifacts/state-teacher.json',
  'e2e-parent@sekolah.test': 'storage/app/audit-artifacts/state-parent.json',
};

for (const [email, state] of Object.entries(STATES)) {
  setup(`authenticate ${email}`, async ({ page }) => {
    await page.goto('/admin/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill('Password123!');
    await page.getByRole('button', { name: /masuk/i }).click();
    await expect(page).not.toHaveURL(/admin\/login/, { timeout: 15_000 });
    await page.context().storageState({ path: state });
  });
}
