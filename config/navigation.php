<?php

/* =====================================================================
   Sikad Pro — Information Architecture (domain navigation)
   ---------------------------------------------------------------------
   Single source of truth untuk sidebar admin, command palette,
   breadcrumbs, dan quick-create. Semua menu dirender dari config ini
   melalui App\Services\Navigation\NavigationService.

   Aturan:
   - Maksimal 2 level (group → item). Tidak ada menu dump.
   - `roles` = daftar role yang BOLEH melihat. Backend authorization
     TETAP ditangani middleware/policy — config ini hanya presentation.
   - Item dengan route yang tidak terdaftar otomatis disembunyikan
     (tidak ada menu mati).
   - Label Bahasa Indonesia konsisten.
   ===================================================================== */

return [

    /*
    |------------------------------------------------------------------
    | Icon paths — stroke SVG path data (heroicons outline style).
    | Dipakai bersama oleh sidebar & command palette.
    |------------------------------------------------------------------
    */
    'icons' => [
        'home'        => 'M3 12l9-9 9 9M5 10v10h14V10',
        'work'        => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-8.995-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'calendar'    => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'bell'        => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        'academic'    => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
        'book-open'   => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'students'    => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'admissions'  => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
        'finance'     => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'people'      => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'facilities'  => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'comm'        => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'office'      => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
        'chart'       => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'system'      => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        'shield'      => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    ],

    /*
    |------------------------------------------------------------------
    | Top links (selalu tampil, di atas group domain)
    |------------------------------------------------------------------
    */
    'top' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'home'],
        ['label' => 'My Work', 'route' => 'admin.my-work', 'active' => 'admin.my-work', 'icon' => 'work', 'badge' => 'mywork'],
        ['label' => 'Kalender', 'route' => 'admin.calendar.index', 'active' => 'admin.calendar.*', 'icon' => 'calendar'],
        ['label' => 'Notifikasi', 'route' => 'admin.notifications.index', 'active' => 'admin.notifications.*', 'icon' => 'bell', 'roles' => ['*']],
    ],

    /*
    |------------------------------------------------------------------
    | Domain groups
    |------------------------------------------------------------------
    | Setiap item: label, route, active pattern (routeIs), optional:
    | roles (override group), badge (key navCounts), title (tooltip).
    |------------------------------------------------------------------
    */
    'groups' => [

        [
            'key' => 'akademik', 'label' => 'Akademik', 'icon' => 'academic',
            'roles' => ['super_admin', 'admin', 'principal', 'homeroom_teacher', 'teacher'],
            'items' => [
                ['label' => 'Siswa', 'route' => 'admin.students.index', 'active' => 'admin.students.*'],
                ['label' => 'Import Siswa', 'route' => 'admin.import.index', 'active' => 'admin.import.*'],
                ['label' => 'Tahun Ajaran', 'route' => 'admin.academic.years.index', 'active' => 'admin.academic.years.*'],
                ['label' => 'Kurikulum', 'route' => 'admin.curriculum.frameworks.index', 'active' => 'admin.curriculum.frameworks.*|admin.curriculum.versions.*'],
                ['label' => 'Kompetensi (CP/TP/ATP)', 'route' => 'admin.curriculum.competencies.index', 'active' => 'admin.curriculum.competencies.*|admin.learning-outcomes.*'],
                ['label' => 'Mapping TP → CP', 'route' => 'admin.curriculum.mapping.index', 'active' => 'admin.curriculum.mapping.*'],
                ['label' => 'Mata Pelajaran', 'route' => 'admin.academic.subjects.index', 'active' => 'admin.academic.subjects.*'],
                ['label' => 'Kelas', 'route' => 'admin.academic.classes.index', 'active' => 'admin.academic.classes.*|admin.academic.sections.*'],
                ['label' => 'Rombel', 'route' => 'admin.academic.class-sections.index', 'active' => 'admin.academic.class-sections.*'],
                ['label' => 'Wali Kelas', 'route' => 'admin.academic.homeroom-teachers.index', 'active' => 'admin.academic.homeroom-teachers.*'],
                ['label' => 'Jadwal Pelajaran', 'route' => 'admin.timetable.index', 'active' => 'admin.timetable.*|admin.ptm-schedules.*'],
                ['label' => 'Absensi Siswa', 'route' => 'admin.attendance.index', 'active' => 'admin.attendance.*|admin.qr-attendance.*'],
                ['label' => 'Penilaian & Rapor', 'route' => 'admin.raport-interaktif.index', 'active' => 'admin.raport-interaktif.*|admin.grades.*'],
                ['label' => 'Skala Nilai', 'route' => 'admin.grades.index', 'active' => 'admin.grades.index'],
                ['label' => 'Transkrip Nilai', 'route' => 'admin.grades.transcript', 'active' => 'admin.grades.transcript'],
                ['label' => 'Rubrik Penilaian', 'route' => 'admin.rubrics.index', 'active' => 'admin.rubrics.*'],
                ['label' => 'Observasi Siswa', 'route' => 'admin.student-observations.index', 'active' => 'admin.student-observations.*'],
                ['label' => 'Ujian / CBT', 'route' => 'admin.exams.index', 'active' => 'admin.exams.*'],
            ],
        ],

        [
            'key' => 'pembelajaran', 'label' => 'Pembelajaran', 'icon' => 'book-open',
            'roles' => ['super_admin', 'admin', 'principal', 'homeroom_teacher', 'teacher'],
            'items' => [
                ['label' => 'Kursus (LMS)', 'route' => 'admin.courses.index', 'active' => 'admin.courses.*'],
                ['label' => 'Materi Pelajaran', 'route' => 'admin.classroom.lessons.index', 'active' => 'admin.classroom.lessons.*'],
                ['label' => 'Tugas', 'route' => 'admin.assignments.index', 'active' => 'admin.assignments.*'],
                ['label' => 'Kuis', 'route' => 'admin.quizzes.index', 'active' => 'admin.quizzes.*'],
                ['label' => 'Bank Soal', 'route' => 'admin.qbank.items.index', 'active' => 'admin.qbank.*'],
                ['label' => 'Live Class', 'route' => 'admin.live-class.index', 'active' => 'admin.live-class.*'],
                ['label' => 'RPP / Modul Ajar', 'route' => 'admin.lesson-plan.index', 'active' => 'admin.lesson-plan.*'],
                ['label' => 'PROTA (Program Tahunan)', 'route' => 'admin.prota.index', 'active' => 'admin.prota.*'],
                ['label' => 'PROMES (Program Semester)', 'route' => 'admin.promes.index', 'active' => 'admin.promes.*'],
                ['label' => 'Jurnal Mengajar', 'route' => 'admin.teaching-journal.index', 'active' => 'admin.teaching-journal.*'],
                ['label' => 'AI Penilaian Esai', 'route' => 'admin.academic.essay-grading.index', 'active' => 'admin.academic.essay-grading.*'],
            ],
        ],

        [
            'key' => 'kesiswaan', 'label' => 'Kesiswaan', 'icon' => 'students',
            'roles' => ['super_admin', 'admin', 'principal', 'homeroom_teacher', 'counselor', 'nurse'],
            'items' => [
                ['label' => 'Student 360', 'route' => 'admin.students.index', 'active' => 'admin.students.show*'],
                ['label' => 'Kenaikan Kelas', 'route' => 'admin.students.lifecycle.batch-promote-form', 'active' => 'admin.students.lifecycle.batch-promote*'],
                ['label' => 'Mutasi Siswa', 'route' => 'admin.students.lifecycle.transfer-form', 'active' => 'admin.students.lifecycle.transfer*'],
                ['label' => 'Tag Siswa', 'route' => 'admin.students.lifecycle.tags', 'active' => 'admin.students.lifecycle.tags'],
                ['label' => 'Disiplin', 'route' => 'admin.discipline.records.index', 'active' => 'admin.discipline.*'],
                ['label' => 'BK / Konseling', 'route' => 'admin.counseling.sessions.index', 'active' => 'admin.counseling.*'],
                ['label' => 'UKS / Kesehatan', 'route' => 'admin.clinic.visits.index', 'active' => 'admin.clinic.*|admin.medical.*'],
                ['label' => 'Prestasi', 'route' => 'admin.achievements.records.index', 'active' => 'admin.achievements.*'],
                ['label' => 'Ekstrakurikuler', 'route' => 'admin.extracurricular.index', 'active' => 'admin.extracurricular.*'],
                ['label' => 'OSIS', 'route' => 'admin.osis.index', 'active' => 'admin.osis.*'],
                ['label' => 'e-Portfolio', 'route' => 'admin.portfolios.index', 'active' => 'admin.portfolios.*'],
                ['label' => 'Beasiswa', 'route' => 'admin.scholarship.programs.index', 'active' => 'admin.scholarship.*'],
                ['label' => 'Bimbingan Karier', 'route' => 'admin.misc.career', 'active' => 'admin.misc.career'],
                ['label' => 'PKL / Magang', 'route' => 'admin.misc.internships.index', 'active' => 'admin.misc.internships.*'],
                ['label' => 'Pesantren / Tahfidz', 'route' => 'admin.religious.targets.index', 'active' => 'admin.religious.*'],
                ['label' => 'Leaderboard', 'route' => 'admin.leaderboard.index', 'active' => 'admin.leaderboard.*'],
            ],
        ],

        [
            'key' => 'ppdb', 'label' => 'PPDB', 'icon' => 'admissions',
            'roles' => ['super_admin', 'admin', 'principal', 'receptionist'],
            'items' => [
                ['label' => 'Dashboard PPDB', 'route' => 'admin.ppdb.dashboard', 'active' => 'admin.ppdb.dashboard'],
                ['label' => 'Pendaftar', 'route' => 'admin.ppdb.applications.index', 'active' => 'admin.ppdb.applications.*', 'badge' => 'ppdb'],
                ['label' => 'Periode PPDB', 'route' => 'admin.ppdb.periods.index', 'active' => 'admin.ppdb.periods.*'],
                ['label' => 'Form Builder', 'route' => 'admin.ppdb.form-builder.index', 'active' => 'admin.ppdb.form-builder.*'],
            ],
        ],

        [
            'key' => 'keuangan', 'label' => 'Keuangan', 'icon' => 'finance',
            'roles' => ['super_admin', 'admin', 'accountant', 'principal'],
            'items' => [
                ['label' => 'Ringkasan Keuangan', 'route' => 'admin.finance.reports.summary', 'active' => 'admin.finance.reports.summary'],
                ['label' => 'Struktur SPP', 'route' => 'admin.fee.structures.index', 'active' => 'admin.fee.structures.*'],
                ['label' => 'Tagihan', 'route' => 'admin.fee.invoices.index', 'active' => 'admin.fee.invoices.*', 'badge' => 'invoices'],
                ['label' => 'Tunggakan SPP', 'route' => 'admin.finance.reports.outstanding', 'active' => 'admin.finance.reports.outstanding'],
                ['label' => 'RKAS / Anggaran', 'route' => 'admin.budget.dashboard', 'active' => 'admin.budget.*'],
                ['label' => 'Akuntansi (COA)', 'route' => 'admin.accounting.coa', 'active' => 'admin.accounting.coa'],
                ['label' => 'Rekonsiliasi Bank', 'route' => 'admin.accounting.bank-reconciliation', 'active' => 'admin.accounting.bank-reconciliation'],
                ['label' => 'Koperasi Sekolah', 'route' => 'admin.cooperative.dashboard', 'active' => 'admin.cooperative.*'],
                ['label' => 'Donasi & Fundraising', 'route' => 'admin.donations.campaigns.index', 'active' => 'admin.donations.*'],
                ['label' => 'Laporan SPP Aging', 'route' => 'admin.reports.spp-aging', 'active' => 'admin.reports.spp-aging'],
                ['label' => 'Laporan Cash Flow', 'route' => 'admin.reports.cash-flow', 'active' => 'admin.reports.cash-flow'],
            ],
        ],

        [
            'key' => 'sdm', 'label' => 'SDM', 'icon' => 'people',
            'roles' => ['super_admin', 'admin', 'hr', 'principal'],
            'items' => [
                ['label' => 'Guru & Staf', 'route' => 'admin.staff.index', 'active' => 'admin.staff.*'],
                ['label' => 'Human Capital', 'route' => 'admin.hr.index', 'active' => 'admin.hr.index'],
                ['label' => 'KPI Appraisal', 'route' => 'admin.hr.kpi.index', 'active' => 'admin.hr.kpi.*'],
                ['label' => 'PKG (Penilaian Kinerja)', 'route' => 'admin.pkg.index', 'active' => 'admin.pkg.*'],
                ['label' => 'Pelatihan', 'route' => 'admin.training.index', 'active' => 'admin.training.*'],
                ['label' => 'Lesson Study', 'route' => 'admin.lesson-study.index', 'active' => 'admin.lesson-study.*'],
                ['label' => 'Slip Gaji', 'route' => 'admin.payroll.slips.index', 'active' => 'admin.payroll.slips.*'],
                ['label' => 'Komponen Gaji', 'route' => 'admin.payroll.structures.index', 'active' => 'admin.payroll.structures.*'],
                ['label' => 'Konfigurasi BPJS', 'route' => 'admin.payroll.bpjs.index', 'active' => 'admin.payroll.bpjs.*'],
                ['label' => 'PPh21', 'route' => 'admin.payroll.pph21.index', 'active' => 'admin.payroll.pph21.*|admin.payroll.tax-profiles.*'],
            ],
        ],

        [
            'key' => 'operasional', 'label' => 'Operasional', 'icon' => 'facilities',
            'roles' => ['super_admin', 'admin', 'procurement_admin', 'transport_admin', 'hostel_admin', 'librarian', 'principal'],
            'items' => [
                ['label' => 'Pengadaan', 'route' => 'admin.procurement.index', 'active' => 'admin.procurement.index|admin.procurement.suppliers', 'roles' => ['super_admin', 'admin', 'procurement_admin']],
                ['label' => 'Approval Pengadaan', 'route' => 'admin.procurement.approvals', 'active' => 'admin.procurement.approvals', 'roles' => ['super_admin', 'admin', 'procurement_admin']],
                ['label' => 'Inventaris & Aset', 'route' => 'admin.inventory.assets.index', 'active' => 'admin.inventory.assets.*|admin.inventory.categories.*|admin.inventory.stock.*', 'roles' => ['super_admin', 'admin', 'procurement_admin']],
                ['label' => 'Peminjaman Aset', 'route' => 'admin.inventory.loans.index', 'active' => 'admin.inventory.loans.*', 'roles' => ['super_admin', 'admin', 'procurement_admin']],
                ['label' => 'Maintenance Aset', 'route' => 'admin.misc.maintenance.index', 'active' => 'admin.misc.maintenance.*', 'roles' => ['super_admin', 'admin', 'procurement_admin']],
                ['label' => 'Perpustakaan', 'route' => 'admin.library.books.index', 'active' => 'admin.library.books.*|admin.library.categories.*', 'roles' => ['super_admin', 'admin', 'librarian']],
                ['label' => 'e-Library', 'route' => 'admin.library.digital.upload', 'active' => 'admin.library.digital.*', 'roles' => ['super_admin', 'admin', 'librarian']],
                ['label' => 'Transportasi', 'route' => 'admin.transport.dashboard', 'active' => 'admin.transport.*', 'roles' => ['super_admin', 'admin', 'transport_admin']],
                ['label' => 'Asrama', 'route' => 'admin.hostel.list.index', 'active' => 'admin.hostel.*', 'roles' => ['super_admin', 'admin', 'hostel_admin']],
                ['label' => 'Kantin', 'route' => 'admin.canteen.menu.index', 'active' => 'admin.canteen.*'],
                ['label' => 'Booking Ruangan', 'route' => 'admin.facilities.rooms.index', 'active' => 'admin.facilities.rooms.*'],
                ['label' => 'Kunjungan / Visitor', 'route' => 'admin.visitor.logs.index', 'active' => 'admin.visitor.*|admin.visitors.*', 'roles' => ['super_admin', 'admin', 'receptionist']],
                ['label' => 'Gerbang & Perangkat', 'route' => 'admin.operations.gate-devices.index', 'active' => 'admin.operations.*', 'roles' => ['super_admin', 'admin', 'transport_admin']],
                ['label' => 'Dapodik Sync', 'route' => 'admin.dapodik.config.index', 'active' => 'admin.dapodik.*'],
            ],
        ],

        [
            'key' => 'komunikasi', 'label' => 'Komunikasi', 'icon' => 'comm',
            'roles' => ['super_admin', 'admin', 'principal', 'receptionist', 'homeroom_teacher', 'teacher'],
            'items' => [
                ['label' => 'Pengumuman', 'route' => 'admin.notices.index', 'active' => 'admin.notices.*'],
                ['label' => 'Pesan', 'route' => 'admin.chat.inbox', 'active' => 'admin.chat.*'],
                ['label' => 'Broadcast', 'route' => 'admin.broadcast.index', 'active' => 'admin.broadcast.*', 'roles' => ['super_admin', 'admin', 'principal']],
                ['label' => 'WhatsApp Bot', 'route' => 'admin.wa-bot.commands.index', 'active' => 'admin.wa-bot.*', 'roles' => ['super_admin', 'admin']],
                ['label' => 'Reminder', 'route' => 'admin.reminders.index', 'active' => 'admin.reminders.*'],
                ['label' => 'Event Sekolah', 'route' => 'admin.events.index', 'active' => 'admin.events.*'],
                ['label' => 'Pertemuan Orang Tua', 'route' => 'admin.conferences.index', 'active' => 'admin.conferences.*'],
                ['label' => 'Forum', 'route' => 'admin.forum.categories', 'active' => 'admin.forum.*'],
                ['label' => 'Komite Sekolah', 'route' => 'admin.committee.members', 'active' => 'admin.committee.*'],
                ['label' => 'Laporan Harian', 'route' => 'admin.misc.daily-reports', 'active' => 'admin.misc.daily-reports'],
                ['label' => 'Info Darurat', 'route' => 'admin.emergency.index', 'active' => 'admin.emergency.*', 'roles' => ['super_admin', 'admin', 'principal']],
            ],
        ],

        [
            'key' => 'administrasi', 'label' => 'Administrasi', 'icon' => 'office',
            'roles' => ['super_admin', 'admin', 'receptionist', 'hr'],
            'items' => [
                ['label' => 'Surat Masuk', 'route' => 'admin.office.incoming.index', 'active' => 'admin.office.incoming.*'],
                ['label' => 'Surat Keluar', 'route' => 'admin.office.outgoing.index', 'active' => 'admin.office.outgoing.*'],
                ['label' => 'Template Surat', 'route' => 'admin.letters.templates', 'active' => 'admin.letters.*'],
                ['label' => 'Dokumen', 'route' => 'admin.documents.index', 'active' => 'admin.documents.*'],
                ['label' => 'Tanda Tangan Digital', 'route' => 'admin.digital-signatures.index', 'active' => 'admin.digital-signatures.*'],
                ['label' => 'Agenda Rapat', 'route' => 'admin.office.meetings.index', 'active' => 'admin.office.meetings.*'],
                ['label' => 'Tugas Staff', 'route' => 'admin.office.tasks.index', 'active' => 'admin.office.tasks.*'],
                ['label' => 'Survei', 'route' => 'admin.surveys.templates.index', 'active' => 'admin.surveys.*'],
                ['label' => 'Ajukan Persetujuan', 'route' => 'admin.workflow.create', 'active' => 'admin.workflow.create'],
            ],
        ],

        [
            'key' => 'analitik', 'label' => 'Analitik', 'icon' => 'chart',
            'roles' => ['super_admin', 'admin', 'principal', 'counselor', 'accountant'],
            'items' => [
                ['label' => 'School Intelligence', 'route' => 'admin.analytics.dashboard', 'active' => 'admin.analytics.dashboard'],
                ['label' => 'Executive Dashboard', 'route' => 'admin.analytics.executive', 'active' => 'admin.analytics.executive'],
                ['label' => 'Student Risk', 'route' => 'admin.analytics.risks.index', 'active' => 'admin.analytics.risks.*|admin.analytics.predictive.*|admin.analytics.dropout-risk.*|admin.analytics.anomalies.*'],
                ['label' => 'PPDB Analytics', 'route' => 'admin.analytics.ppdb', 'active' => 'admin.analytics.ppdb'],
                ['label' => 'HR Analytics', 'route' => 'admin.analytics.hr', 'active' => 'admin.analytics.hr'],
                ['label' => 'Library Analytics', 'route' => 'admin.analytics.library', 'active' => 'admin.analytics.library'],
                ['label' => 'Laporan Absensi', 'route' => 'admin.reports.attendance-pct', 'active' => 'admin.reports.attendance-pct'],
                ['label' => 'Distribusi Nilai', 'route' => 'admin.reports.grade-distribution', 'active' => 'admin.reports.grade-distribution'],
                ['label' => 'Laporan Disiplin', 'route' => 'admin.reports.discipline-leaderboard', 'active' => 'admin.reports.discipline-leaderboard'],
                ['label' => 'Report Builder', 'route' => 'admin.reports.builder.index', 'active' => 'admin.reports.builder.*'],
                ['label' => 'AI Guru (Tanya Data)', 'route' => 'admin.ai.chat-data.index', 'active' => 'admin.ai.chat-data.*|admin.ai.ocr.*|admin.ai.recommendations.*'],
            ],
        ],

        [
            'key' => 'yayasan', 'label' => 'Yayasan', 'icon' => 'people',
            'roles' => ['super_admin', 'admin', 'foundation_admin', 'principal'],
            'foundation_only' => true,
            'items' => [
                ['label' => 'Dashboard Yayasan', 'route' => 'admin.foundation.dashboard', 'active' => 'admin.foundation.dashboard'],
                ['label' => 'Master Data', 'route' => 'admin.foundation.master-data.index', 'active' => 'admin.foundation.master-data.*'],
                ['label' => 'User Management', 'route' => 'admin.foundation.user-management.index', 'active' => 'admin.foundation.user-management.*'],
                ['label' => 'Benchmark Antar Sekolah', 'route' => 'admin.foundation.benchmark.index', 'active' => 'admin.foundation.benchmark.*'],
            ],
        ],

        [
            'key' => 'pengaturan', 'label' => 'Pengaturan', 'icon' => 'system',
            'roles' => ['super_admin', 'admin'],
            'items' => [
                ['label' => 'Branding Sekolah', 'route' => 'admin.branding.show', 'active' => 'admin.branding.show'],
                ['label' => 'Website Builder', 'route' => 'admin.branding.website.pages', 'active' => 'admin.branding.website.*'],
                ['label' => 'Blog Sekolah', 'route' => 'admin.blog.index', 'active' => 'admin.blog.*'],
                ['label' => 'Payment Gateway', 'route' => 'admin.payment.providers.index', 'active' => 'admin.payment.*'],
                ['label' => 'Notification Provider', 'route' => 'admin.notif.providers.index', 'active' => 'admin.notif.providers.*|admin.notif-prefs.*'],
                ['label' => 'AI Provider', 'route' => 'admin.ai.providers.index', 'active' => 'admin.ai.providers.*|admin.ai.usage'],
                ['label' => 'Kurs Mata Uang', 'route' => 'admin.currency.show', 'active' => 'admin.currency.*'],
                ['label' => 'Webhooks', 'route' => 'admin.webhooks.index', 'active' => 'admin.webhooks.*'],
                ['label' => 'Automation Rules', 'route' => 'admin.automation.rules.index', 'active' => 'admin.automation.*'],
                ['label' => 'Export Data', 'route' => 'admin.exports.index', 'active' => 'admin.exports.*'],
                ['label' => 'Audit Log', 'route' => 'admin.audit.index', 'active' => 'admin.audit.*|admin.internal-audit.*'],
                ['label' => 'Digital Signage', 'route' => 'admin.signage.config', 'active' => 'admin.signage.*|admin.dashboard-tv.*'],
                ['label' => 'Akreditasi', 'route' => 'admin.accreditation.dashboard', 'active' => 'admin.accreditation.*'],
                ['label' => 'Compliance', 'route' => 'admin.compliance.dashboard', 'active' => 'admin.compliance.*'],
                ['label' => 'Adiwiyata', 'route' => 'admin.adiwiyata.dashboard', 'active' => 'admin.adiwiyata.*'],
            ],
        ],
    ],

    /*
    |------------------------------------------------------------------
    | Quick Create (+ Buat di topbar) — per role family
    |------------------------------------------------------------------
    */
    'quick_create' => [
        'management' => [
            ['label' => 'Siswa Baru', 'route' => 'admin.students.create', 'icon' => 'students'],
            ['label' => 'Guru / Staf', 'route' => 'admin.staff.create', 'icon' => 'people'],
            ['label' => 'Pengumuman', 'route' => 'admin.notices.create', 'icon' => 'bell'],
            ['label' => 'Event', 'route' => 'admin.events.index', 'icon' => 'calendar'],
        ],
        'finance' => [
            ['label' => 'Struktur SPP', 'route' => 'admin.fee.structures.index', 'icon' => 'finance'],
            ['label' => 'Tagihan', 'route' => 'admin.fee.invoices.index', 'icon' => 'money'],
            ['label' => 'Anggaran', 'route' => 'admin.budget.dashboard', 'icon' => 'chart'],
        ],
        'teaching' => [
            ['label' => 'Buat Tugas', 'route' => 'admin.assignments.index', 'icon' => 'book-open'],
            ['label' => 'Input Absensi', 'route' => 'admin.attendance.index', 'icon' => 'check'],
            ['label' => 'Jurnal Mengajar', 'route' => 'admin.teaching-journal.index', 'icon' => 'edit'],
            ['label' => 'Buat Ujian', 'route' => 'admin.exams.index', 'icon' => 'academic'],
        ],
        'admissions' => [
            ['label' => 'Data Pendaftar', 'route' => 'admin.ppdb.applications.index', 'icon' => 'admissions'],
            ['label' => 'Periode PPDB', 'route' => 'admin.ppdb.periods.index', 'icon' => 'calendar'],
        ],
        'hr' => [
            ['label' => 'Guru / Staf', 'route' => 'admin.staff.create', 'icon' => 'people'],
            ['label' => 'Slip Gaji', 'route' => 'admin.payroll.slips.index', 'icon' => 'finance'],
        ],
    ],

    /*
    | Role → quick_create set mapping (urutan penting: first match wins)
    */
    'quick_create_roles' => [
        'super_admin'       => ['management', 'finance', 'teaching'],
        'admin'             => ['management', 'finance', 'teaching', 'admissions'],
        'principal'         => ['management', 'teaching'],
        'accountant'        => ['finance'],
        'hr'                => ['hr'],
        'teacher'           => ['teaching'],
        'homeroom_teacher'  => ['teaching'],
        'receptionist'      => ['admissions'],
    ],

    /*
    | Route prefixes → breadcrumb group fallback (untuk route yang tidak
    | didefinisikan dalam groups di atas, mis. halaman mandiri).
    */
    'breadcrumb_prefixes' => [
        'admin.students' => 'Akademik', 'admin.import' => 'Akademik', 'admin.attendance' => 'Akademik',
        'admin.academic' => 'Akademik', 'admin.curriculum' => 'Akademik', 'admin.timetable' => 'Akademik',
        'admin.classroom' => 'Pembelajaran', 'admin.assignments' => 'Pembelajaran', 'admin.exams' => 'Akademik',
        'admin.qbank' => 'Pembelajaran', 'admin.quizzes' => 'Pembelajaran', 'admin.courses' => 'Pembelajaran',
        'admin.lesson-plan' => 'Pembelajaran', 'admin.teaching-journal' => 'Pembelajaran',
        'admin.discipline' => 'Kesiswaan', 'admin.counseling' => 'Kesiswaan', 'admin.clinic' => 'Kesiswaan',
        'admin.medical' => 'Kesiswaan', 'admin.achievements' => 'Kesiswaan', 'admin.portfolios' => 'Kesiswaan',
        'admin.extracurricular' => 'Kesiswaan', 'admin.osis' => 'Kesiswaan', 'admin.scholarship' => 'Kesiswaan',
        'admin.religious' => 'Kesiswaan', 'admin.leaderboard' => 'Kesiswaan', 'admin.misc' => 'Kesiswaan',
        'admin.ppdb' => 'PPDB',
        'admin.fee' => 'Keuangan', 'admin.finance' => 'Keuangan', 'admin.budget' => 'Keuangan',
        'admin.cooperative' => 'Keuangan', 'admin.accounting' => 'Keuangan', 'admin.donations' => 'Keuangan',
        'admin.currency' => 'Pengaturan', 'admin.payment' => 'Pengaturan',
        'admin.staff' => 'SDM', 'admin.pkg' => 'SDM', 'admin.training' => 'SDM', 'admin.lesson-study' => 'SDM',
        'admin.payroll' => 'SDM', 'admin.hr' => 'SDM',
        'admin.procurement' => 'Operasional', 'admin.inventory' => 'Operasional', 'admin.hostel' => 'Operasional',
        'admin.transport' => 'Operasional', 'admin.facilities' => 'Operasional', 'admin.visitor' => 'Operasional',
        'admin.operations' => 'Operasional', 'admin.dapodik' => 'Operasional', 'admin.library' => 'Operasional',
        'admin.canteen' => 'Operasional',
        'admin.notices' => 'Komunikasi', 'admin.chat' => 'Komunikasi', 'admin.broadcast' => 'Komunikasi',
        'admin.wa-bot' => 'Komunikasi', 'admin.reminders' => 'Komunikasi', 'admin.emergency' => 'Komunikasi',
        'admin.forum' => 'Komunikasi', 'admin.conferences' => 'Komunikasi', 'admin.committee' => 'Komunikasi',
        'admin.events' => 'Komunikasi',
        'admin.office' => 'Administrasi', 'admin.documents' => 'Administrasi', 'admin.letters' => 'Administrasi',
        'admin.surveys' => 'Administrasi', 'admin.digital-signatures' => 'Administrasi', 'admin.workflow' => 'Administrasi',
        'admin.analytics' => 'Analitik', 'admin.ai' => 'Analitik', 'admin.reports' => 'Analitik',
        'admin.foundation' => 'Yayasan',
        'admin.branding' => 'Pengaturan', 'admin.blog' => 'Pengaturan', 'admin.notif' => 'Pengaturan',
        'admin.notif-prefs' => 'Pengaturan', 'admin.webhooks' => 'Pengaturan', 'admin.automation' => 'Pengaturan',
        'admin.exports' => 'Pengaturan', 'admin.audit' => 'Pengaturan', 'admin.internal-audit' => 'Pengaturan',
        'admin.signage' => 'Pengaturan', 'admin.dashboard-tv' => 'Pengaturan', 'admin.accreditation' => 'Pengaturan',
        'admin.compliance' => 'Pengaturan', 'admin.adiwiyata' => 'Pengaturan',
    ],
];
