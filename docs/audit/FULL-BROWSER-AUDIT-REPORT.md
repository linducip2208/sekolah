# FULL-BROWSER-AUDIT-REPORT

Tanggal: 2026-10-10. Commit awal: `1d76d28` (+commit pemilik `8b10a18` di tengah sesi, diverifikasi kompatibel).
Environment: Windows + Laragon (Apache httpd, MySQL 8.4.9), PHP 8.3.30, Laravel 13.35,
Playwright 1.61.1 (chromium), `php artisan serve --env=testing :8765` dengan
`DB_DATABASE=sikadpro_e2e_test`, `SESSION_DRIVER=file`, `CACHE_STORE=file`.
Fixtures: `database/seeders/E2EFixtureSeeder.php` (allowlist guard, sekolah E2E + admin/guru/orang tua/siswa).

## Hasil: 21/21 PASS (1 worker, ~1 menit)

| File | Test | Bukti |
|---|---|---|
| auth.setup.ts | login admin/guru/orang tua + simpan state | 3/3 |
| auth/login.spec.ts | render, salah password → error, login → dashboard, guest → redirect | 4/4 |
| academic/ppdb.spec.ts | buat periode → tampil → reload persist | 1/1 |
| finance/payment.spec.ts | proker OSIS CRUD → reload → hapus; invoice render | 2/2 |
| navigation/smoke.spec.ts | 9 halaman admin 200 + h1 + tanpa console error/5xx | 9/9 |
| security/access.spec.ts | teacher session valid; parent 403 ke admin invoice | 2/2 |

## Temuan infrastruktur (diperbaiki dalam sesi)

1. `SESSION_DRIVER=array` membuat login HTTP tidak persist → serve E2E memakai `file`.
   (Hanya flag serve; `.env.testing` tidak diubah.)
2. Throttle login 8/menit (`AppServiceProvider:107`) membuat 16 login berurutan 429 →
   pola `auth.setup.ts` + `storageState` per role.
3. Test login:UI me-regenerasi session → cookie state file yatim → spec login diisolasi
   (`storageState: kosong`), didokumentasikan di spec.

## Cakupan jujur

- Browser mencakup: auth, 9 halaman inti, PPDB create, OSIS CRUD, invoice render, isolasi parent.
- TIDAK mencakup via browser: 500+ halaman lain, aliran refund/jurnal via UI, upload file,
  realtime Reverb, FCM. Itu dicakup Pest (413 test) atau bertanda NOT_TESTED di TEST-EXECUTION-LOG.
- Tidak ada screenshot kegagalan tersisa (semua hijau); screenshot pola login tercatat di laporan ini.
