# SIKAD Pro — Role Navigation Matrix

Sumber kebenaran: `config/navigation.php` + `App\Services\Navigation\NavigationService`.
Server-side authorization tetap di middleware (`role:...`) dan Policy — config ini hanya
menentukan apa yang *tampil*. Item dengan route yang tidak terdaftar otomatis disembunyikan.

## Legenda
- ✓ = group tampil · ✗ = disembunyikan · ◐ = tampil sebagian (item-level filtering)
- ★ = dashboard khusus role ini

| Group | super_admin | admin | principal | accountant | teacher/homeroom | counselor | hr | receptionist | librarian | nurse | transport_admin | hostel_admin | procurement_admin | foundation_admin |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| Dashboard/My Work/Kalender/Notifikasi | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Akademik | ✓ | ✓ | ✓ | ✗ | ◐ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Pembelajaran | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Kesiswaan | ✓ | ✓ | ✓ | ✗ | ✗ | ✓ | ✗ | ✗ | ✗ | ◐ | ✗ | ✗ | ✗ | ✗ |
| PPDB | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Keuangan | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| SDM | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Operasional | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ◐ | ◐ | ✗ | ◐ | ◐ | ◐ | ✗ |
| Komunikasi | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Administrasi | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Analitik | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Yayasan (foundation_only) | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✓ |
| Pengaturan | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |

## Item-level detail (◐)

- **Akademik @ teacher/homeroom**: Siswa, Jadwal, Absensi, Penilaian, Ujian (input harian) — tanpa Kurikulum/Mapping/Transkrip.
- **Kesiswaan @ nurse**: UKS / Kesehatan saja.
- **Operasional @ receptionist**: Kunjungan/Visitor.
- **Operasional @ librarian**: Perpustakaan + e-Library.
- **Operasional @ transport_admin**: Transportasi, Gerbang & Perangkat.
- **Operasional @ hostel_admin**: Asrama.
- **Operasional @ procurement_admin**: Pengadaan, Approval Pengadaan, Inventaris, Peminjaman, Maintenance.

## Quick Create (+ Buat) per role

| Role | Item |
|---|---|
| super_admin | Siswa, Guru/Staf, Pengumuman, Event, Struktur SPP, Tagihan, Anggaran, Tugas, Absensi, Jurnal, Ujian |
| admin | Semua di atas + Data Pendaftar, Periode PPDB |
| principal | Siswa, Guru/Staf, Pengumuman, Event, Tugas, Absensi, Jurnal, Ujian |
| accountant | Struktur SPP, Tagihan, Anggaran |
| teacher / homeroom | Tugas, Absensi, Jurnal, Ujian |
| hr | Guru/Staf, Slip Gaji |
| receptionist | Data Pendaftar, Periode PPDB |

## Portal (non-admin)

| Role | Permukaan | Menu |
|---|---|---|
| Siswa | `/siswa/*` (layout parent) | Beranda, Jadwal, Nilai, Absensi, Materi, Tugas, Ujian, Kuis, Peringkat, Survei, Portofolio |
| Orang Tua | `/portal/*` | Beranda, Tagihan (badge tunggakan), Konferensi Guru, Komunitas, Komite + children switcher |

## Dashboard KPI per role (`RoleDashboardService`)

| Role key | KPI |
|---|---|
| principal | Siswa Aktif, Kehadiran Hari Ini, Rata-rata Nilai, At-Risk, Pendaftar PPDB, Menunggu Approval |
| teacher | Kelas Hari Ini, Tugas, Ujian, Jurnal, At-Risk |
| finance | Pendapatan Bulan Ini, Outstanding, Collection Rate, Beban, Slip Gaji |
| counselor | At-Risk, Sesi Konseling, Kasus Disiplin, Laporan Bullying |
| hr | Karyawan, Cuti Pending, Lembur Pending, Kontrak Aktif |
| librarian ★ | Koleksi, Dipinjam, Terlambat, e-Library |
| ppdb_officer ★ | Menunggu Verifikasi, Total Pendaftar, Diterima, Conversion, Dashboard |
| nurse ★ | Kunjungan Hari Ini, Perlu Tindak Lanjut, Rekam Medis, Imunisasi |
| transport ★ | Rute, Armada, Penumpang, Absensi Hari Ini, Jadwal Pengemudi |
| hostel ★ | Asrama, Okupansi, Absensi, Gate Pass |
| procurement ★ | Menunggu Approval, Total Permintaan, Supplier, Aset Perlu Maintenance |
| foundation ★ | Dashboard, Benchmark, Master Data, User Management |

★ = ditambahkan iterasi ini (sebelumnya fallback ke principal — tidak relevan).
