# SIKAD Pro — Dashboard Architecture

## Filosofi

> Dashboard = **command center**, bukan galeri stat cards.
> Struktur: *konteks → KPI (≤6) → hal yang butuh AKSI → detail pendukung.*
> Setiap alert wajib punya CTA eksplisit ("31 tagihan overdue → Lihat & Kirim Reminder"),
> bukan angka mati.

## Data Flow

```
AuthController@dashboard
  └─ DashboardDataService::for($user)          ← SATU entry point
       ├─ context()   greeting, sekolah, TA & semester aktif
       ├─ kpis()      delegasi → RoleDashboardService::forRole()
       ├─ alerts()    overdue, approval, at-risk, absensi, PPDB (semua + CTA)
       ├─ charts()    kehadiran doughnut + keuangan 6-bulan (theme-aware)
       ├─ lists()     at-risk top5, my-work preview, agenda, aktivitas
       └─ setup()     SchoolSetupService::progress() — 7 langkah onboarding
       [Cache 120s: dash:v2:{school}:{role}:{user}]
```

Aturan:
- **Tidak ada query kompleks di Blade** — semua di service, tenant-scoped (`where school_id`), rescue-guarded (tidak pernah 500 ke UI).
- **Jangan cache objek Eloquent** — konversi ke plain array sebelum masuk cache.
- Cache di-flush via observer saat data berubah (`DashboardDataService::flush`).

## Layout (school-admin/dashboard.blade.php)

```
1. Header        greeting + nama + role + sekolah + TA/SMT + tombol My Work
2. Setup         Penyiapan Sekolah (admin only, <100%) — chip per langkah + CTA
3. KPI           maks 6 kartu, tone dot, href ke modul
4. Perhatian     alert list (icon + judul + deskripsi + CTA) │ Aksi Cepat (grid)
5. Charts        Kehadiran Hari Ini (doughnut) │ Penerimaan vs Pengeluaran (bar)
6. Risiko        Siswa At-Risk top 5 │ Tugas Saya (My Work preview)
7. Aktivitas     Agenda Mendatang │ Aktivitas Terbaru (audit log)
```

Empty state kontekstual di **setiap** panel (mis. "Belum ada data absensi hari ini.").

## Chart Theme-Aware

- Warna dibaca saat render dari `getComputedStyle(--color-*)` → otomatis ikut dark mode + white-label.
- Listener `sikadpro:theme-changed` → `chart.destroy()` + rebuild (tanpa memory leak).

## Role Dashboard (`/dashboard/role`)

Halaman sekunder per role (13 role) — dipakai sebagai "Pusat Kendali {Role}" dengan KPI
role-spesifik + konteks tambahan (pembayaran terbaru untuk bendahara, ujian mendatang
untuk siswa, children untuk orang tua). Lihat `docs/ROLE_NAVIGATION_MATRIX.md` untuk
daftar KPI per role.

## My Work (`/my-work`)

Agregasi lintas modul yang butuh aksi user saat ini (`MyWorkService`):
workflow approval, verifikasi PPDB, tagihan overdue, approval pengadaan, approval
dokumen, cuti HR, jurnal guru belum diisi, notifikasi belum dibaca.
Dikelompokkan **Kritis / Penting / Normal**, masing-masing dengan count + CTA.
Badge total tampil di sidebar & dashboard.

## Setup Progress (onboarding)

`SchoolSetupService::progress(schoolId)` — 7 langkah nyata dari DB:
Profil Sekolah ✓ · Tahun Ajaran · Mata Pelajaran · Kelas & Rombel · Guru & Staf ·
Siswa · Struktur SPP. Setiap langkah belum-selesai = chip + link ke halaman input.
Cache 300s, `SchoolSetupService::flush()` saat konfigurasi berubah.
Widget hanya tampil untuk super_admin/admin dan hilang otomatis di 100%.
