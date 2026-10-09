# TEST MATRIX — audit fungsional penuh

Legenda: VERIFIED = skenario wajib dijalankan dan lolos. PARTIAL = sebagian. BLOCKED = butuh akses
eksternal. NOT_TESTED = belum ada bukti.

## Baru (audit ini) — `tests/Feature/Web/FunctionalAuditFixTest.php`, 9 test, VERIFIED

| Skenario | Bukti |
|---|---|
| Tugas nav resolve + render | GET 200 |
| Procurement approvals/suppliers (shadowing) | GET 200 |
| Emergency contacts (shadowing) | GET 200 |
| recordPayment → payment + status partial + jurnal | assert DB 3 sisi |
| storeSaving tolak member asing (422) + catat + increment | assert invalid + count |
| Statement anggota render + lihat nomor | GET 200 + assertSee |
| storeTransaction tolak item asing; deleteCategory error (bukan success) | assertInvalid + assertSessionHasErrors + row tetap ada |
| QR generate tolak rombel asing (422 JSON) | assertStatus |
| Refund tolak payment asing (422), paid_amount utuh | assertStatus + fresh |

## Existing — full suite `php artisan test`: 397 passed, 0 failed (1872 assertions), VERIFIED

Termasuk: Security/IDOR, Auth+2FA, ApiContract (7), TenantIsolation (pihak lain),
MobileMoney (pihak lain), Finance, Donation, SuperAdmin, PPDB, LMS, Exam/CBT, Attendance,
Communication, Compliance, Setup, SEO.

## Browser

Playwright tidak tersedia di repo → smoke via HTTP lokal: `/api/v1` 200, `/api/v1/health` 200,
halaman web terproteksi 200 via test (actingAs admin). Screenshot: tidak dibuat (no runner).
Status: PARTIAL (tidak ada error 500/blank pada halaman yang diuji; cakupan bukan 100% halaman).

## Per-domain (web)

| Domain | Status | Catatan |
|---|---|---|
| Siswa/360, lifecycle, observasi | VERIFIED | test existing + audit baca |
| Staff/profil | PARTIAL | no self-edit (keputusan produk); training query OK |
| Absensi + QR | VERIFIED | bulkMark tervalidasi; QR di-scope + test |
| Nilai/approval/transkrip | VERIFIED | upsert-natural; lock/reopen ada |
| Exam/CBT/bank soal | PARTIAL | metadata immutable (by-design); jawaban tidak bocor (ExamSecurityTest) |
| Timetable | PARTIAL | tanpa edit (by-design); store di-scope |
| Raport/QR verify publik | VERIFIED | test existing |
| PPDB web+API | VERIFIED | test existing |
| LMS/tugas/sertifikat | VERIFIED | test existing |
| SPP/invoice/bayar/refund/denda | VERIFIED | test baru + existing |
| Akuntansi/jurnal/periode | VERIFIED | balanced-guard + lock; close/reopen ada |
| Budget/RKAS | VERIFIED | test baru |
| Payroll/BPJS/PPh21 | VERIFIED | test diperbaiki ke kontrak rupiah |
| Pengadaan | VERIFIED | shadowing diperbaiki + test |
| Koperasi | VERIFIED | test baru |
| Inventaris/aset | PARTIAL | approve path service-dependent, tidak diuji ujung-ujung |
| Perpustakaan/sirkulasi/denda | VERIFIED | tx + test existing |
| Transport/hostel/kantin/visitor/gate | PARTIAL | destroy tanpa school-check di beberapa controller kecil (D8) — binding scope menutupi model SchoolModel; back-office risk rendah |
| Event/kalender/notifikasi/broadcast | PARTIAL | broadcast send tanpa authorizeOwn (admin-only group) |
| Surat/dokumen/workflow/TTD | VERIFIED | tx + test existing |
| Analitik/laporan/AI | PARTIAL | heavy query ter-cache; output spot-check |
| Yayasan/oauth/payment-provider/webhook | PARTIAL | HMAC + throttle ada; BLOCKED: kredensial provider riil |
| Backup/health | VERIFIED | deep health ok di produksi |

## Perintah verifikasi yang dijalankan

`php -l` (12 file), `php artisan test` (397 hijau), `php artisan route:list` (shadowing+statement),
`composer validate` (valid + warning skema umum), `git diff --check` (bersih), `npm run build` (ok 1.16s),
`vendor/bin/pint --test` (file lama sudah merah sejak HEAD — pre-existing, bukan regresi).
