import { test, expect } from '@playwright/test';
import { collectPageProblems } from '../helpers/auth';

/** Ops pages: budget, payroll, asset loans render with headings, no errors. */
const PAGES: Array<[string, RegExp]> = [
  ['/admin/budget/dashboard', /anggaran|rkas/i],
  ['/admin/payroll/slips', /slip gaji/i],
  ['/admin/inventory/loans', /peminjaman aset/i],
];

test.describe('ops pages smoke', () => {
  for (const [url, heading] of PAGES) {
    test(`GET ${url} renders`, async ({ page }) => {
      const { consoleErrors, failedRequests } = await collectPageProblems(page);
      const res = await page.goto(url);
      expect(res?.status(), `${url} status`).toBeLessThan(400);
      await expect(page.locator('h1').first()).toContainText(heading);
      expect(consoleErrors, `console errors @${url}`).toEqual([]);
      expect(failedRequests.filter((u) => !u.includes('/build/')), `failed @${url}`).toEqual([]);
    });
  }
});
