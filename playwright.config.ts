import { defineConfig, devices } from '@playwright/test';

/**
 * Browser E2E for Sikad Pro. Read-only safety:
 * - Only runs against E2E_BASE_URL (local/staging testing env, never production).
 * - Fixture DB: sikadpro_e2e_test (see database/seeders/E2EFixtureSeeder.php).
 *
 * Usage:
 *   $env:DB_DATABASE='sikadpro_e2e_test'; php artisan migrate --env=testing --force
 *   $env:DB_DATABASE='sikadpro_e2e_test'; php artisan db:seed --class=E2EFixtureSeeder --env=testing --force
 *   $env:DB_DATABASE='sikadpro_e2e_test'; php artisan serve --env=testing --host=127.0.0.1 --port=8765
 *   $env:E2E_BASE_URL='http://127.0.0.1:8765'; npx playwright test
 */
export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: 0,
  workers: 1,
  timeout: 60_000,
  expect: { timeout: 10_000 },
  reporter: [['list'], ['html', { outputFolder: 'storage/app/audit-artifacts/playwright-report', open: 'never' }]],
  use: {
    baseURL: process.env.E2E_BASE_URL || 'http://127.0.0.1:8765',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'off',
  },
  projects: [
    { name: 'setup', testMatch: /.*\.setup\.ts/ },
    {
      name: 'chromium',
      dependencies: ['setup'],
      use: { ...devices['Desktop Chrome'], storageState: 'storage/app/audit-artifacts/state-admin.json' },
    },
  ],
  webServer: {
    command: 'php artisan serve --env=testing --host=127.0.0.1 --port=8765',
    url: 'http://127.0.0.1:8765/admin/login',
    reuseExistingServer: true,
    timeout: 60_000,
    env: {
      DB_DATABASE: 'sikadpro_e2e_test',
      SESSION_DRIVER: 'file',
      CACHE_STORE: 'file',
    },
  },
  outputDir: 'storage/app/audit-artifacts/test-results',
});
