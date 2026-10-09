# REMAINING GAPS — jujur, tanpa mengarang

## P1 (disarankan berikutnya)

1. `authorizeOwn`/policy eksplisit di destroy kecil: `DriverSchedule`, `Incoming/OutgoingMail`
   (archive/destroy), `Meeting`, `StaffTask`, `BroadcastMessage::send/destroy`. Saat ini mengandalkan
   grup `role:admin|...` + (sebagian) scope binding. Risk rendah (back-office), tapi inkonsisten.
2. Tombol hapus tanpa `onsubmit=confirm` di sebagian Blade (sampel: finance/invoices, budget/items,
   library/books). Sebagian view sudah pakai confirm (koperasi members).
3. Konfirmasi sengaja: exam/timetable/quiz tanpa edit (immutable-by-design). Jika produk butuh edit,
   itu fitur baru, bukan bug.

## P2

4. 76 route fungsional tanpa entri sidebar (jurnal, sirkulasi, dsb.) — dapat dibuka via URL;
   butuh keputusan UX (tambah menu vs sengaja).
5. Sidebar role kosmetik + gate grup lebar — arsitektur existing; perketat hanya jika ada insiden.
6. `discount_amount` respons subscription tidak dikonversi rupiah (mentah minor-unit) sementara
   `amount` dikonversi — inkonsistensi kontrak kecil warisan HEAD. Flutter harus baca dokumentasi.
7. `migrate:fresh` pada 361 tabel kadang deadlock multi-drop massal (MySQL 8.4) — hanya DB test;
   mitigasi: DROP+CREATE sebelum run file.

## BLOCKED (butuh akses eksternal)

8. Payment gateway / SMS / WhatsApp / AI provider riil — kredensial hanya via admin UI produksi.
9. Reverb realtime end-to-end + FCM delivery — butuh device + domain produksi.
10. Playwright browser test + screenshot — runner belum ada di repo.

## NOT_TESTED

11. Seluruh halaman (±565 Blade) tidak diklik satu per satu — cakupan via test rute+kunci + baca source.
12. Benchmark lintas-sekolah dengan data volume produksi.

## Ringkasan angka

- Menu ditemukan: 142 (141 nama route unik). Halaman diuji via test: seluruh alur P0 + suite 397 test.
- VERIFIED: 24 domain-baris. PARTIAL: 11. BROKEN: 0 tersisa. BLOCKED: 3. NOT_IMPLEMENTED: 0.
  NOT_TESTED: 2 (cakupan halaman penuh, volume produksi).
- Test: 397 passed / 0 failed / 0 skip (1872 assertions).
- Bug: P0 8 (diperbaiki), P1 7 (diperbaiki), P2 3, P3 3 (diterima/by-design).
- File diubah: 16 tracked + 2 baru (view statement, test audit) + 4 doc ini.
- Tidak di-commit/push — menunggu instruksi (ada file tak terlacak pihak lain di tree).
