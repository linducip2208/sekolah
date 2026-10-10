import { test, expect } from '@playwright/test';

/** Student portal: previously-missing programs view renders; propose persists. */
test.describe('student osis programs', () => {
  test.use({ storageState: 'storage/app/audit-artifacts/state-student.json' });

  test('programs page renders and propose works', async ({ page }) => {
    await page.goto('/siswa/osis/program');
    await expect(page.locator('h1')).toContainText(/program kerja osis/i);

    const title = `Usulan E2E ${Date.now()}`;
    await page.locator('input[name="title"]').fill(title);
    await page.getByRole('button', { name: /kirim usulan/i }).click();
    await expect(page.locator('body')).toContainText(title);
    await page.reload();
    await expect(page.locator('body')).toContainText(title);
  });
});
