# CRUD-WORKFLOW-MATRIX (ringkas; detail per-fitur di TEST-EXECUTION-LOG)

Legenda: PASS = dijalankan & assertion penuh. N/A = by-design tidak ada (immutable).
NOT_TESTED = belum ada bukti.

| ID | Fitur | Read | Create | Update | Delete | Aksi khusus | Persistensi | E2E browser |
|---|---|---|---|---|---|---|---|---|
| FEATURE-STU-001 | Siswa | PASS | PASS | PASS | PASS | promote/transfer PASS | PASS | NOT_TESTED |
| FEATURE-ATT-001/002 | Absensi + QR | PASS | PASS | PASS | N/A | manualOverride PASS | PASS | smoke PASS (QR render) |
| FEATURE-TT-001 | Jadwal | PASS | PASS | N/A | PASS | — | PASS | NOT_TESTED |
| FLOW-GEN-001 | Generator + saveConfig | PASS | PASS | PASS | N/A | wizard PASS | PASS | smoke PASS |
| FEATURE-GRD-001 | Nilai/approval | PASS | PASS | PASS | PASS | lock/reopen PASS | PASS | NOT_TESTED |
| FEATURE-EXM-001 | Ujian/CBT | PASS | PASS | N/A | PASS | generateBank PASS | PASS | NOT_TESTED |
| FEATURE-OSIS-001/002 | Proker OSIS | PASS | PASS | PASS | PASS | propose PASS | PASS | PASS (CRUD penuh) |
| FEATURE-COMM-001 | Rapat komite | PASS | PASS | PASS | PASS | — | PASS | NOT_TESTED |
| FEATURE-PPDB-001 | PPDB | PASS | PASS | PASS | PASS | publish/review/enroll PASS | PASS | PASS (create) |
| FEATURE-LMS-001 | LMS | PASS | PASS | PASS | PASS | enroll/certificate PASS | PASS | NOT_TESTED |
| FEATURE-FEE-001 | SPP/bayar/refund | PASS | PASS | PASS | PASS(guard) | pay/refund/lateFee PASS | PASS | PASS (render) |
| FEATURE-ACC-001 | Akuntansi | PASS | PASS | PASS | PASS(guard) | post/close/reopen PASS | PASS | smoke PASS |
| FEATURE-BUD-001 | Anggaran | PASS | PASS | PASS | PASS(guard) | — | PASS | NOT_TESTED |
| FEATURE-PAY-001 | Payroll | PASS | PASS | PASS | PASS(guard) | pay→jurnal PASS, double-pay 409 PASS | PASS | NOT_TESTED |
| FEATURE-PROC-001 | Pengadaan | PASS | PASS | PASS | PASS | approve/receive PASS | PASS | NOT_TESTED |
| FEATURE-COOP-001 | Koperasi | PASS | PASS | PASS | PASS(guard) | approve/pay/statement PASS | PASS | smoke PASS |
| FEATURE-INV-001 | Inventaris/aset | PASS | PASS | PASS | PASS | pinjam/kembali PASS, enum fix | PASS | NOT_TESTED |
| FEATURE-LIB-001 | Perpustakaan | PASS | PASS | PASS | PASS | issue/return PASS | PASS | NOT_TESTED |

Total baris: 18. PASS 18/18 pada cakupan yang diuji. Full Pest: 413 passed.
