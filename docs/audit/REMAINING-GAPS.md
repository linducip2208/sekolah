# REMAINING-GAPS (sesi master-command 2026-10-10)

## P1 berikutnya (disarankan, bukan kritis) — update 2026-10-10 sesi 2

0. SELESAI sesi ini: klaim "missing ownership" pada 6 controller kecil terbukti KELIRU —
   semuanya sudah `abort_unless`/`abort(403)`. Satu-satunya celah nyata (broadcast kirim ulang)
   diperbaiki (422) + test. Confirm hapus: tersisa 1 (logo branding) diperbaiki; sisanya OK
   (JS confirm / modal data-confirm).
1. `onsubmit=confirm` belum di semua form hapus (koperasi/OSIS sudah; finance/inventory sebagian).
2. `onsubmit=confirm` belum di semua form hapus (koperasi/OSIS sudah; finance/inventory sebagian).
3. `discount_amount` respons subscription minor-unit mentah (warisan kontrak rupiah).

## P2

4. 238 halaman tanpa menu (by-design) — butuh keputusan UX.
5. `migrate:fresh` 361 tabel kadang deadlock (MySQL 8.4) — mitigasi DROP+CREATE (terbukti).
6. Pint merah pre-existing sejak HEAD lama — bukan regresi.

## BLOCKED (kredensial/layanan eksternal)

7. Payment/SMS/WA/AI provider live, Reverb E2E + FCM delivery, beban volume produksi.

## NOT_TESTED (jujur)

8. ~500 halaman tidak diklik satu per satu via browser (cakupan browser: 9 smoke + 5 workflow;
   sisanya Pest + baca source).
9. Upload file via browser, import massal via UI,Concurrency race (ditangani lockForUpdate di kode).

## Angka

- Menu: 142. Fitur: 58 (18 baris matriks CRUD). Role diuji: 5. Workflow browser: 5.
- Pest: 414 passed / 0 failed (1923+ assertions). Playwright: 30/30. Build: OK.
- Bug: P0 12 (3 view + enum + 8 scope/money) — semua diperbaiki + test.
  P1 10 — diperbaiki. P2/P3 — diterima/didokumentasikan.
- File diubah sesi ini: ~30 (lihat `git status`). Tanpa migrasi baru. Tanpa push.
