# MENU-FEATURE-INVENTORY — Sikad Pro (source-verified)

Tanggal: 2026-10-10. HEAD: `1d76d28`. Machine-readable: `feature-inventory.json`, `route-inventory.json`
(1630 routes). Baseline mentah: `docs/audit/baseline/`.

## Angka

- Menu sidebar: 142 item (12 grup + 5 top link), 141 nama route unik — 100% resolve (0 dead route).
- Route admin web: 996 bernama (382 GET). API v1: 411. Total registry: 1630.
- Role seeder: 21. Gate grup admin: 8 role.
- Halaman GET tanpa menu: 238 (sengaja: sub-halaman show/workflow/laporan).
- Fitur bisnis teridentifikasi: 58 (lihat JSON). CRUD penuh vs workflow-by-design dicatat per fitur.

## ID audit

- `NAV-*`: navigasi/route. `FEATURE-*`: fitur bisnis. `FLOW-*`: workflow multi-langkah.
  `SEC-*`: temuan keamanan. `UI-*`: temuan Blade/JS.

## Temuan terverifikasi manual (bukan dari nama file)

| ID | Lokasi | Status |
|---|---|---|
| NAV-001 | Menu Tugas → `admin.classroom.assignments.index` | FIXED sesi lalu, terverifikasi |
| NAV-002 | Shadowing procurement/emergency | FIXED sesi lalu, terverifikasi |
| FEATURE-OSIS-001 | `admin.osis.programs` → view hilang (500) | BROKEN → fix sesi ini |
| FEATURE-OSIS-002 | `student.osis.programs` → view hilang (500) | BROKEN → fix sesi ini |
| FEATURE-COMM-001 | `portal.committee.meeting` → view hilang (500) | BROKEN → fix sesi ini |
| FLOW-GEN-001 | `TimetableGenerator::saveConfig` tanpa route | BROKEN → fix sesi ini (tambah route) |
| SEC-JRN-001 | Journal lines COA tanpa scope sekolah | BROKEN → fix sesi ini |
| SEC-FEE-001 | Truncation `(int)($x*100)` di struktur SPP | BROKEN → fix sesi ini |
| SEC-PAY-001 | `updateTaxProfile` staff lintas sekolah | BROKEN → fix sesi ini |
| FLOW-PAY-001 | Web `paySlip` bypass `PayrollService::markPaid` (tanpa jurnal/tx/guard) | BROKEN → fix sesi ini |
| SEC-AST-001/002 | Asset category/asset + loan lintas sekolah, tanpa tx | BROKEN → fix sesi ini |
| FEATURE-STAFF-001 | Staff 360 trainings pakai `user_id` (salah, harus `id`) | BROKEN → fix sesi ini |
| UI-* | Tidak ditemukan `href="#"`/form tanpa aksi pada sampel blade akademik & finansial | OK |

## Yang disengaja (bukan bug)

- 238 halaman tanpa menu (sub-halaman, aksi workflow, laporan).
- Sidebar role kosmetik + gate grup lebar (arsitektur existing; otorisasi per-aksi via `authorizeOwn`).
- Exam/timetable/quiz immutable (delete+recreate by-design).
- Tracer publik, signage publik (by-design + throttle).
- `discount_amount` respons subscription minor-unit mentah (inkonsistensi kecil warisan kontrak rupiah).
