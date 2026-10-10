# SECURITY-AND-TENANT-ISOLATION

Tanggal: 2026-10-10. Semua uji memakai data testing buatan sendiri. Tidak ada POST ke produksi.

## Hasil uji (PASS)

1. **Scope global**: 268 model via `SchoolModel`/`SchoolScope`; binding ikut scope (47 model platform dikecualikan by-design).
2. **Scoped validation** (sesi ini): journal-COA, struktur SPP classroom, QR rombel/mapel/siswa, timetable, exam, bank soal, budget category/item/year/parent, koperasi member, procurement supplier/category, asset category/asset, loan asset/borrower — semua menolak ID asing (422/404).
3. **Refund**: `fee_payment_id` wajib milik invoice+sekolah yang sama (422).
4. **Parent payment**: `choose`/`initiate`/`show`/`cancel` wajib anak-tertaut kecuali peran back-office.
5. **Budget**: `authorizeOwn` + guard hapus (item bertransaksi, kategori berisi).
6. **Koperasi**: guard hapus anggota (ada simpanan/pinjaman aktif) & pinjaman aktif (422); installment via loan.
7. **Payroll**: tax-profile staff di-scope; `paySlip` via service (tx + jurnal + guard 409).
8. **Aset**: loan/return tx + scope; enum diperbaiki (`active`/`borrowed`).
9. **Role gate**: teacher/parent/student 403 di grup admin (test + browser).
10. **API**: 401 JSON tanpa Accept header; throttle login/2FA/reset; 2FA enforced.

## Risiko residual diterima

- `returnFromGateway` publik menampilkan nota via `reference_no` (entropi ref sebagai kapasitas; tidak diubah agar callback gateway tetap jalan).
- Sidebar role kosmetik (arsitektur existing).
- Kredensial provider riil (payment/SMS/WA/AI) hanya via admin UI produksi → BLOCKED, tidak diuji live.
