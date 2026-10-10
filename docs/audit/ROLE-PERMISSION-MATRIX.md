# ROLE-PERMISSION-MATRIX — implementasi aktual (bukan kosmetik nav)

Tanggal: 2026-10-10. HEAD awal: `1d76d28`. Test: `tests/Feature/Security/RolePermissionMatrixTest.php` (3/3 PASS).
Browser: teacher/parent login + isolasi terverifikasi via Playwright (21/21).

## Gate grup web admin (`routes/web.php:188`)

`auth` + `school.access` + `role:admin|accountant|principal|hr|transport_admin|hostel_admin|procurement_admin|homeroom_teacher` + `subscription.active` + `2fa.enforce`.

## Matriks (hasil uji, bukan asumsi)

| Role | Dashboard admin | Aksi POST admin | Keterangan |
|---|---|---|---|
| admin | ALLOWED | ALLOWED | penuh |
| accountant | ALLOWED | ALLOWED (domainnya) | gate grup; per-aksi via permission/controller |
| principal/hr/dll (6 lainnya) | ALLOWED (gate) | per-controller | mengandalkan `authorizeOwn`/permission |
| teacher | DENIED (403) | DENIED (403) | dibuktikan via test + browser |
| parent | DENIED (403) | DENIED (403) | dibuktikan via test + browser; portal sendiri di `/portal` |
| student | DENIED (403) | DENIED (403) | portal sendiri di `/siswa` |
| guest | redirect `/admin/login` | redirect | tidak ada kebocoran |

## Catatan jujur

- Sidebar `roles` di `navigation.php` = presentasi; enforcement = gate grup + `authorizeOwn`/permission
  per controller (pola arsitektur existing, tidak di-redesign).
- `NavigationService` hanya menampilkan menu untuk role pertama user; user multi-role mengandalkan
  gate grup saat akses URL langsung — terverifikasi tidak 500.
- `super_admin` web memakai grup sendiri (`/super`, `role:super_admin`).
- Perbaikan sesi ini: `authorizeOwn` ditambah ke 4 method Budget; ownership parent di
  ParentPayment (`choose`/`initiate`/`show`/`cancel`); `authorizeInvoice` membolehkan
  admin/accountant/principal bertindak atas nama.
