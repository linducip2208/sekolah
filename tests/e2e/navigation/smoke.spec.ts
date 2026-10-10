import { test, expect } from '@playwright/test';
import { collectPageProblems } from '../helpers/auth';

/** Smoke: every key admin menu page returns 200, renders a heading, no 500/console errors. */
const PAGES: Array<[string, RegExp]> = [
  ['/admin/dashboard', /selamat|dashboard/i],
  ['/admin/students', /siswa/i],
  ['/admin/fee/invoices', /tagihan|invoice/i],
  ['/admin/ppdb/applications', /ppdb|pendaftar/i],
  ['/admin/osis-programs', /program kerja osis/i],
  ['/admin/timetable/generator', /jadwal|generator/i],
  ['/admin/accounting/journal', /jurnal/i],
  ['/admin/cooperative/members', /koperasi|anggota/i],
  ['/admin/attendance/qr', /qr|absensi/i],
];

test.describe('admin navigation smoke', () => {
  // Authenticated via setup project storageState (admin).

  for (const [url, heading] of PAGES) {
    test(`GET ${url} renders`, async ({ page }) => {
      const { consoleErrors, failedRequests } = await collectPageProblems(page);
      const res = await page.goto(url);
      expect(res?.status(), `${url} status`).toBeLessThan(400);
      await expect(page.locator('h1').first()).toContainText(heading);
      expect(consoleErrors, `console errors @${url}: ${consoleErrors.join(' | ')}`).toEqual([]);
      expect(
        failedRequests.filter((u) => !u.includes('/build/')),
        `failed requests @${url}: ${failedRequests.join(' | ')}`,
      ).toEqual([]);
    });
  }
});
