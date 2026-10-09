# FULL-SYSTEM FUNCTIONAL AUDIT — Sikad Pro

Tanggal: 2026-10-10. HEAD: `a31bb59`. Branch `main` == `origin/main`, working tree bersih sebelum audit.
Metode: 3 auditor paralel (navigasi/route, akademik web, finansial web) + verifikasi manual tiap temuan P0
(`route:list`, baca source, HTTP lokal, test). Tidak ada `migrate:fresh`/`db:wipe` ke data dev; hanya DB test
`sikadpro_test` yang di-reset.

## Baseline

- 252 controller, 565 Blade view, `routes/web.php` 1826 baris, `config/navigation.php` 378 baris,
  227 migration, 11 policies, ~996 route `admin.*`, 316 URI API.
- Test: Pest. Playwright/CI tidak tersedia → smoke via HTTP + feature test.
- Selama audit ditemukan file test tak terlacak dari pihak lain
  (`tests/Feature/Api/TenantIsolationTest.php`, `MobileMoneyAndDirectoryTest.php`) — tidak disentuh.

## Temuan P0 (semua diverifikasi + diperbaiki)

| # | Temuan | Bukti | Akar |
|---|---|---|---|
| 1 | Menu "Tugas" mati (route tidak ada) | `route:list --name=admin.assignments.index` → ERROR; `NavigationService` menyembunyikan diam-diam | B: nama route salah di `navigation.php:100,312` + F |
| 2 | Halaman approvals & suppliers pengadaan mati (404/500) | `/{procurement}` terdaftar sebelum path statis (web.php:1071 vs 1076/1080) | C: urutan route (shadowing). Pola sama di emergency contacts |
| 3 | Jurnal bisa hilang tanpa pembayaran / sebaliknya | `postFeePayment` dipanggil SETELAH commit; `(int)($x*100)` truncates | F: split-brain ledger |
| 4 | Simpanan/pinjaman/angsuran koperasi non-atomik + member lintas sekolah lolos | create+increment tanpa tx; `exists` tanpa school; `find` tanpa school | F + G(tenant) |
| 5 | Budget transaksi non-atomik; hapus kategori gagal tapi pesan "success" | increment di luar tx; `with('success', ...)` di jalur blocked | F + E |
| 6 | Statement anggota koperasi render view yang salah | `memberStatement` return view `savings` + tak ada route/link | C + E |
| 7 | Cross-school ID diterima di QR/timetable/exam/budget/fee-refund/koperasi-member | `exists:table,id` tanpa `where school_id` di 9 titik | G(tenant) |
| 8 | 4 test merah warisan kontrak rupiah (donasi, payroll, kupon, plan) | HEAD `a31bb59/8693d15` ubah kontrak minor-unit; test masih skala lama | D: test basi (bukan bug kode), + 1 gap nyata (`SuperPlan::update` tak konversi) |

## Bukan bug ( diverifikasi )

- `migrate` "table already exists" saat audit = artefak output ter-truncate yang membunuh proses,
  bukan migrasi ganda. Migrasi penuh 227 file → 361 tabel bersih.
- `bulkMark` absensi aman (validasi siswa ∈ rombel+sekolah, otorisasi guru per kelas).
- Deadlock `drop table` massal saat `migrate:fresh` = flakiness MySQL 8.4; mitigasi: DROP+CREATE DB test
  sebelum run file.
- 76 route tanpa entri menu (jurnal, sirkulasi lib, dsb.) = by-design, dapat dibuka via URL.
- Gating role sidebar kosmetik + gate grup lebar = arsitektur existing; otorisasi per-aksi via
  `authorizeOwn`/`abort_unless` (spot-check ada). Tidak di-redesign dalam audit ini.
- Exam/timetable/quiz tanpa edit = immutable-by-design (delete+recreate).

## Status keamanan

Sanctum, RBAC, 2FA, tenant isolation, security middleware: tidak dimatikan. Tidak ada POST ke produksi.
Kredensial produksi tidak diubah.
