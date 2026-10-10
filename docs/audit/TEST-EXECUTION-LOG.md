# TEST-EXECUTION-LOG

Tanggal: 2026-10-10. DB test: `sikadpro_test` (Pest, DROP+CREATE sebelum run file).
DB E2E: `sikadpro_e2e_test` (361 tabel, seed E2EFixtureSeeder). Serve: `:8765` testing+file session.

| Waktu | Perintah | Hasil |
|---|---|---|
| 06:1x | `php artisan route:list --json` → baseline (1630) | OK (via cmd, hindari UTF-16 PS) |
| 06:2x | `php artisan test tests/Feature/Web/MasterAuditRegressionTest.php` | 13/13 PASS (3 iterasi perbaikan test-setup) |
| 06:3x | `php artisan test tests/Feature/Security/RolePermissionMatrixTest.php` | 3/3 PASS |
| 06:4x | `npx playwright test` (setup+21) | 21/21 PASS (setelah fix session array, throttle-state, dashboard regex) |
| 06:5x | curl/Invoke prod 4 URL (read-only) | 200 semua, deep ok |
| 06:5x | `php artisan test` (full) | 413 passed / 0 failed (1923 assertions, ~15 mnt) |
| 06:5x | `npm run build` | OK 927ms |
| 06:5x | `composer validate --no-check-publish` | valid + warning skema umum |
| 06:5x | `git diff --check` | bersih |

Catatan: run Pest pertama gagal akibat artefak truncate-output (proses terbunuh di tengah
`migrate:fresh`, DB setengah jadi) — lingkungan, bukan kode. Mitigasi DROP+CREATE selalu sebelum run.
Run Playwright pertama gagal 16x karena SESSION array + throttle login — didokumentasikan di
FULL-BROWSER-AUDIT-REPORT dan diperbaiki via pola setup+state.
