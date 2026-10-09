# FULL-SYSTEM IMPLEMENTATION — hasil perbaikan audit

Semua perubahan di bawah telah diuji (test baru 9 + full suite 397 hijau). Tidak ada fitur dihapus,
tidak ada migrasi destruktif, tidak ada mock/placeholder.

## 1. Navigasi (P0-1)

- `config/navigation.php`: `admin.assignments.index` → `admin.classroom.assignments.index`
  (menu "Tugas" + quick-create "Buat Tugas"). Bukti: `route:list` resolve + test render 200.

## 2. Route shadowing (P0-2)

- `routes/web.php`: path statis (`procurement/approvals`, `procurement/suppliers`,
  `emergency/contacts`) dipindah ke atas `/{param}`. Scan shadowing satu file:
  sisanya false-positive (beda grup/prefix, diverifikasi via `route:list` URI penuh).

## 3. Atomicitas uang (P0-3/4/5)

- `FeeWebController::recordPayment`: `round()` (ganti truncating cast) + posting jurnal
  `postFeePayment` masuk ke dalam transaksi yang sama. `postFeePayment` sinkron + idempoten
  via `reference_no`, aman di dalam tx.
- `CooperativeController::storeSaving/storeLoan/approveLoan/payInstallment/deleteSaving`:
  semua pasangan tulis dibungkus `DB::transaction`; lookup member di-scope sekolah.
- `BudgetController::storeTransaction/deleteTransaction`: increment/decrement di dalam tx;
  `deleteTransaction` + `abort_unless` school; `storeTransaction` pakai scoped `findOrFail`.
- `AccountingController::deleteJournal`: hapus lines+entry dalam tx (tetap tolak jika posted).
- `BudgetController::deleteCategory`: jalur blocked sekarang `withErrors` (sebelumnya `with('success')`).
- `FeeRefundService::refund`: referensi `fee_payment_id` wajib milik invoice+sekolah yang sama (422).

## 4. Statement koperasi (P0-6)

- View baru `school-admin/finance/cooperative/statement.blade.php` (ringkasan pokok/wajib/sukarela/total
  + tabel mutasi + empty state) — mengikuti layout `layouts.school-admin`.
- `memberStatement` return view yang benar; route baru
  `admin.cooperative.members.statement`; link "Statement" di tabel anggota.

## 5. Scoped validation (P1 IDOR)

`Rule::exists(...)->where('school_id', ...)` di: QR generate (rombel+mapel), QR manual override
(siswa ∈ rombel sesi), timetable store (rombel+mapel+guru), exam store (rombel+mapel),
exam generate-from-bank (kategori bank soal), budget items/categories/years/parent, cooperative
storeMember (pemilik morph ∈ sekolah).

## 6. Kontrak rupiah (warisan HEAD)

- 4 test basi diupdate ke kontrak whole-rupiah boundary (donasi, payroll, kupon, plan).
- Gap nyata diperbaiki: `SuperPlanController::update` kini konversi `price` + respons rupiah
  (konsisten dengan `store`/`index`).

## File diubah

`config/navigation.php`, `routes/web.php`,
`Web/Admin/{Academic/QrAttendanceController,TimetableWebController,ExamWebController}`,
`Web/Admin/Finance/{FeeWebController,CooperativeController,BudgetController,AccountingController}`,
`Services/Finance/FeeRefundService.php`, `Api/SuperAdmin/SuperPlanController.php`,
view `cooperative/{statement,members}`, test (4 file update + `tests/Feature/Web/FunctionalAuditFixTest.php` baru, 9 test).
