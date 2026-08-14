@php
    $isActive = fn($pattern) => request()->routeIs($pattern) ? 'active' : '';
    $icon = fn($d) => "<svg class='w-4 h-4 flex-shrink-0' fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='1.8'><path stroke-linecap='round' stroke-linejoin='round' d='{$d}'/></svg>";
    $chevron = "<svg class='w-3 h-3 transition-transform duration-200' :class=\"open ? 'rotate-90' : ''\" fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='2.5'><path stroke-linecap='round' stroke-linejoin='round' d='M9 5l7 7-7 7'/></svg>";

    // Role-based navigation: admin sees the full menu; accountant sees
    // finance, reports and analytics only. (Backend authorization is enforced
    // separately via policies/middleware — this is presentation only.)
    $role    = auth()->check() ? (auth()->user()->getRoleNames()->first() ?? 'admin') : 'admin';
    $isAdmin = in_array($role, ['admin', 'super_admin'], true);

    $navCounts = [
        'ppdb'     => rescue(fn () => \App\Models\PPDB\PpdbApplication::where('school_id', auth()->user()->school_id)->count(), 0, false),
        'invoices' => rescue(fn () => \App\Models\Finance\FeeInvoice::where('school_id', auth()->user()->school_id)->whereIn('status', ['unpaid', 'partial', 'overdue'])->count(), 0, false),
    ];
@endphp

<div class="px-3 pt-3 pb-1.5 flex items-center gap-1.5 sidebar-search">
    <div class="relative flex-1" x-data="{ q: '' }">
        <svg class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" x-model="q" placeholder="Cari menu..." aria-label="Cari menu"
               class="w-full bg-white/10 border border-white/10 rounded-md pl-8 pr-2 py-1.5 text-[13px] text-white/80 placeholder-white/40 outline-none focus:border-white/30 focus:bg-white/15 transition"
               @input="document.querySelectorAll('.sidebar-section').forEach(s => { s.style.display = q.length<2 || s.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none' })">
    </div>
    <button onclick="document.querySelectorAll('.sidebar-section').forEach(s=>s.__x.$data.open=true)" class="text-white/40 hover:text-white/80 p-1.5 text-xs" title="Buka semua" aria-label="Buka semua bagian">&#x25BC;</button>
    <button onclick="document.querySelectorAll('.sidebar-section').forEach(s=>s.__x.$data.open=false)" class="text-white/40 hover:text-white/80 p-1.5 text-xs" title="Tutup semua" aria-label="Tutup semua bagian">&#x25B2;</button>
</div>

<a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ $isActive('admin.dashboard') }}">{!! $icon('M3 12l9-9 9 9M5 10v10h14V10') !!}<span>Dashboard</span></a>

@if($isAdmin)
{{-- 📚 AKADEMIK INTI --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">📚</span>Akademik Inti</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.academic.years.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.years.*') }}">Tahun Ajaran</a>
        <a href="{{ route('admin.academic.subjects.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.subjects.*') }}">Mata Pelajaran</a>
        <a href="{{ route('admin.academic.classes.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.classes.*') }}">Kelas</a>
        <a href="{{ route('admin.academic.sections.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.sections.*') }}">Section</a>
        <a href="{{ route('admin.academic.class-sections.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.class-sections.*') }}">Rombel</a>
        <a href="{{ route('admin.academic.mediums.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.mediums.*') }}">Medium Bahasa</a>
        <a href="{{ route('admin.curriculum.frameworks.index') }}" class="sidebar-sub-link {{ $isActive('admin.curriculum.*') }}">Kurikulum (CP/ATP)</a>
        <a href="{{ route('admin.calendar.index') }}" class="sidebar-sub-link {{ $isActive('admin.calendar.*') }}">Kalender Akademik</a>
    </div>
</div>

{{-- 👥 SISWA & STAFF --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">👥</span>Siswa & Staff</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.students.index') }}" class="sidebar-sub-link {{ $isActive('admin.students.*') }}">Data Siswa</a>
        <a href="{{ route('admin.staff.index') }}" class="sidebar-sub-link {{ $isActive('admin.staff.*') }}">Staff & Guru</a>
        <a href="{{ route('admin.import.index') }}" class="sidebar-sub-link {{ $isActive('admin.import.*') }}">Import CSV</a>
        <a href="{{ route('admin.portfolios.index') }}" class="sidebar-sub-link {{ $isActive('admin.portfolios.*') }}">e-Portfolio</a>
        <a href="{{ route('admin.misc.career') }}" class="sidebar-sub-link {{ $isActive('admin.misc.career') }}">Career Guidance</a>
        <a href="{{ route('admin.misc.internships.index') }}" class="sidebar-sub-link {{ $isActive('admin.misc.internships.*') }}">Magang / Internship</a>
    </div>
</div>

{{-- 📋 ABSENSI & JADWAL --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">📋</span>Absensi & Jadwal</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.attendance.index') }}" class="sidebar-sub-link {{ $isActive('admin.attendance.*') }}">Absensi Harian</a>
        <a href="{{ route('admin.attendance.recap') }}" class="sidebar-sub-link {{ $isActive('admin.attendance.recap') }}">Rekap Absensi</a>
        <a href="{{ route('admin.qr-attendance.show') }}" class="sidebar-sub-link {{ $isActive('admin.qr-attendance.*') }}">Absensi QR Code</a>
        <a href="{{ route('admin.qr-attendance.history') }}" class="sidebar-sub-link {{ $isActive('admin.qr-attendance.history') }}">Riwayat QR</a>
        <a href="{{ route('admin.timetable.index') }}" class="sidebar-sub-link {{ $isActive('admin.timetable.*') }}">Jadwal Pelajaran</a>
        <a href="{{ route('admin.timetable.generator.wizard') }}" class="sidebar-sub-link {{ $isActive('admin.timetable.generator.*') }}">Generate Otomatis</a>
    </div>
</div>

{{-- 📝 UJIAN & PENILAIAN --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">📝</span>Ujian & Penilaian</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.exams.index') }}" class="sidebar-sub-link {{ $isActive('admin.exams.*') }}">Ujian</a>
        <a href="{{ route('admin.qbank.categories.index') }}" class="sidebar-sub-link {{ $isActive('admin.qbank.categories.*') }}">Kategori Bank Soal</a>
        <a href="{{ route('admin.qbank.items.index') }}" class="sidebar-sub-link {{ $isActive('admin.qbank.items.*') }}">Bank Soal</a>
        <a href="{{ route('admin.academic.essay-grading.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.essay-grading.*') }}">AI Penilaian Essay</a>
        <a href="{{ route('admin.raport-interaktif.index') }}" class="sidebar-sub-link {{ $isActive('admin.raport-interaktif.*') }}">Raport Interaktif</a>
    </div>
</div>

{{-- 🏆 PRESTASI --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🏆</span>Prestasi & Skor</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.leaderboard.index') }}" class="sidebar-sub-link {{ $isActive('admin.leaderboard.*') }}">Leaderboard</a>
        <a href="{{ route('admin.leaderboard.history') }}" class="sidebar-sub-link {{ $isActive('admin.leaderboard.history') }}">Riwayat Poin</a>
        <a href="{{ route('admin.achievements.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.achievements.dashboard') }}">Dashboard Prestasi</a>
        <a href="{{ route('admin.achievements.categories.index') }}" class="sidebar-sub-link {{ $isActive('admin.achievements.categories.*') }}">Kategori Prestasi</a>
        <a href="{{ route('admin.achievements.records.index') }}" class="sidebar-sub-link {{ $isActive('admin.achievements.records.*') }}">Prestasi Siswa</a>
    </div>
</div>

{{-- 🎓 PENGAJARAN --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🎓</span>Pengajaran</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.classroom.lessons.index') }}" class="sidebar-sub-link {{ $isActive('admin.classroom.lessons.*') }}">Materi Pelajaran</a>
        <a href="{{ route('admin.assignments.index') }}" class="sidebar-sub-link {{ $isActive('admin.assignments.*') }}">Tugas / PR</a>
        <a href="{{ route('admin.lesson-plan.index') }}" class="sidebar-sub-link {{ $isActive('admin.lesson-plan.*') }}">Lesson Plan / RPP</a>
        <a href="{{ route('admin.lesson-plan.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.lesson-plan.dashboard') }}">Dashboard RPP</a>
        <a href="{{ route('admin.live-class.index') }}" class="sidebar-sub-link {{ $isActive('admin.live-class.*') }}">Live Class</a>
        <a href="{{ route('admin.misc.live-class-attendances') }}" class="sidebar-sub-link {{ $isActive('admin.misc.live-class-attendances') }}">Absensi Live Class</a>
        <a href="{{ route('admin.canteen.categories.index') }}" class="sidebar-sub-link {{ $isActive('admin.canteen.categories.*') }}">Kategori Kantin</a>
        <a href="{{ route('admin.canteen.menu.index') }}" class="sidebar-sub-link {{ $isActive('admin.canteen.menu.*') }}">Menu Kantin</a>
        <a href="{{ route('admin.misc.canteen.wallets') }}" class="sidebar-sub-link {{ $isActive('admin.misc.canteen.wallets') }}">Wallet Kantin</a>
        <a href="{{ route('admin.religious.targets.index') }}" class="sidebar-sub-link {{ $isActive('admin.religious.*') }}">Pesantren / Madrasah</a>
    </div>
</div>

{{-- 🧠 GURU --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🧠</span>Pengembangan Guru</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.pkg.index') }}" class="sidebar-sub-link {{ $isActive('admin.pkg.*') }}">PKG (Kinerja Guru)</a>
        <a href="{{ route('admin.training.index') }}" class="sidebar-sub-link {{ $isActive('admin.training.*') }}">Diklat & Pelatihan</a>
        <a href="{{ route('admin.training.certifications') }}" class="sidebar-sub-link {{ $isActive('admin.training.certifications') }}">Sertifikasi Guru</a>
        <a href="{{ route('admin.lesson-study.index') }}" class="sidebar-sub-link {{ $isActive('admin.lesson-study.*') }}">Lesson Study</a>
    </div>
</div>

{{-- 🧒 PPDB & SISWA --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🧒</span>PPDB & Kesiswaan</span>@if($navCounts['ppdb'] > 0)<span class="sidebar-badge">{{ $navCounts['ppdb'] }}</span>@endif{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.ppdb.periods.index') }}" class="sidebar-sub-link {{ $isActive('admin.ppdb.periods.*') }}">Periode PPDB</a>
        <a href="{{ route('admin.ppdb.applications.index') }}" class="sidebar-sub-link {{ $isActive('admin.ppdb.applications.*') }}">Pendaftar PPDB</a>
        <a href="{{ route('admin.ppdb.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.ppdb.dashboard') }}">Dashboard PPDB</a>
        <a href="{{ route('admin.clinic.visits.index') }}" class="sidebar-sub-link {{ $isActive('admin.clinic.*')||$isActive('admin.medical.*') }}">UKS / Klinik</a>
        <a href="{{ route('admin.clinic.vaccinations.index') }}" class="sidebar-sub-link {{ $isActive('admin.clinic.vaccinations.*') }}">Vaksinasi</a>
        <a href="{{ route('admin.counseling.sessions.index') }}" class="sidebar-sub-link {{ $isActive('admin.counseling.*') }}">BP/BK Konseling</a>
        <a href="{{ route('admin.counseling.bullying.index') }}" class="sidebar-sub-link {{ $isActive('admin.counseling.bullying.*') }}">Laporan Bullying</a>
        <a href="{{ route('admin.counseling.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.counseling.dashboard') }}">Dashboard BK</a>
        <a href="{{ route('admin.discipline.categories.index') }}" class="sidebar-sub-link {{ $isActive('admin.discipline.categories.*') }}">Kategori Disiplin</a>
        <a href="{{ route('admin.discipline.records.index') }}" class="sidebar-sub-link {{ $isActive('admin.discipline.records.*') }}">Catatan Disiplin</a>
        <a href="{{ route('admin.discipline.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.discipline.dashboard') }}">Dashboard Disiplin</a>
        <a href="{{ route('admin.extracurricular.index') }}" class="sidebar-sub-link {{ $isActive('admin.extracurricular.*') }}">Ekstrakurikuler</a>
        <a href="{{ route('admin.misc.daily-reports') }}" class="sidebar-sub-link {{ $isActive('admin.misc.daily-reports') }}">Laporan Harian</a>
    </div>
</div>

{{-- 🤝 ENGAGEMENT --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🤝</span>Engagement</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.events.index') }}" class="sidebar-sub-link {{ $isActive('admin.events.*') }}">Event Sekolah</a>
        <a href="{{ route('admin.donations.campaigns.index') }}" class="sidebar-sub-link {{ $isActive('admin.donations.*') }}">Donasi & Fundraising</a>
        <a href="{{ route('admin.scholarship.programs.index') }}" class="sidebar-sub-link {{ $isActive('admin.scholarship.programs.*') }}">Program Beasiswa</a>
        <a href="{{ route('admin.scholarship.applications.index') }}" class="sidebar-sub-link {{ $isActive('admin.scholarship.applications.*') }}">Pendaftar Beasiswa</a>
        <a href="{{ route('admin.scholarship.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.scholarship.dashboard') }}">Dashboard Beasiswa</a>
        <a href="{{ route('admin.conferences.index') }}" class="sidebar-sub-link {{ $isActive('admin.conferences.*') }}">Konferensi Ortu</a>
        <a href="{{ route('admin.forum.categories') }}" class="sidebar-sub-link {{ $isActive('admin.forum.*') }}">Forum Komunitas</a>
    </div>
</div>

{{-- 🏛 ORGANISASI --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🏛</span>Organisasi Sekolah</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.committee.members') }}" class="sidebar-sub-link {{ $isActive('admin.committee.*') }}">Komite Sekolah</a>
        <a href="{{ route('admin.committee.meetings') }}" class="sidebar-sub-link {{ $isActive('admin.committee.meetings') }}">Rapat Komite</a>
        <a href="{{ route('admin.committee.decisions') }}" class="sidebar-sub-link {{ $isActive('admin.committee.decisions') }}">Keputusan Komite</a>
        <a href="{{ route('admin.committee.proposals') }}" class="sidebar-sub-link {{ $isActive('admin.committee.proposals') }}">Proposal Komite</a>
        <a href="{{ route('admin.osis.index') }}" class="sidebar-sub-link {{ $isActive('admin.osis.*') }}">OSIS / MPK</a>
    </div>
</div>

{{-- 🎓 ALUMNI --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🎓</span>Alumni & Karir</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.alumni.index') }}" class="sidebar-sub-link {{ $isActive('admin.alumni.*') }}">Data Alumni</a>
        <a href="{{ route('admin.tracer.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.tracer.*') }}">Tracer Study</a>
        <a href="{{ route('admin.jobs.index') }}" class="sidebar-sub-link {{ $isActive('admin.jobs.*') }}">Job Board</a>
        <a href="{{ route('admin.bkk.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.bkk.*') }}">BKK (Bursa Kerja)</a>
    </div>
</div>

@endif

{{-- 💰 KEUANGAN --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">💰</span>Keuangan</span>@if($navCounts['invoices'] > 0)<span class="sidebar-badge">{{ $navCounts['invoices'] }}</span>@endif{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.fee.structures.index') }}" class="sidebar-sub-link {{ $isActive('admin.fee.structures.*') }}">Struktur Biaya</a>
        <a href="{{ route('admin.fee.invoices.index') }}" class="sidebar-sub-link {{ $isActive('admin.fee.invoices.*') }}">Invoice / Tagihan</a>
        <a href="{{ route('admin.payroll.structures.index') }}" class="sidebar-sub-link {{ $isActive('admin.payroll.structures.*') }}">Komponen Gaji</a>
        <a href="{{ route('admin.payroll.slips.index') }}" class="sidebar-sub-link {{ $isActive('admin.payroll.slips.*') }}">Slip Gaji</a>
        <a href="{{ route('admin.payment.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.payment.providers.*') }}">Provider Bayar</a>
        <a href="{{ route('admin.payment.methods.index') }}" class="sidebar-sub-link {{ $isActive('admin.payment.methods.*') }}">Metode Bayar</a>
        <a href="{{ route('admin.budget.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.budget.*') }}">Anggaran (RKAS)</a>
        <a href="{{ route('admin.budget.categories.index') }}" class="sidebar-sub-link {{ $isActive('admin.budget.categories.*') }}">Kategori Anggaran</a>
        <a href="{{ route('admin.budget.transactions.index') }}" class="sidebar-sub-link {{ $isActive('admin.budget.transactions.*') }}">Transaksi Anggaran</a>
        <a href="{{ route('admin.procurement.index') }}" class="sidebar-sub-link {{ $isActive('admin.procurement.*') }}">Pengadaan</a>
        <a href="{{ route('admin.procurement.approvals') }}" class="sidebar-sub-link {{ $isActive('admin.procurement.approvals') }}">Approval Pengadaan</a>
        <a href="{{ route('admin.procurement.suppliers') }}" class="sidebar-sub-link {{ $isActive('admin.procurement.suppliers') }}">Supplier</a>
        <a href="{{ route('admin.cooperative.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.cooperative.*') }}">Koperasi</a>
        <a href="{{ route('admin.cooperative.members') }}" class="sidebar-sub-link {{ $isActive('admin.cooperative.members') }}">Anggota Koperasi</a>
        <a href="{{ route('admin.finance.reports.summary') }}" class="sidebar-sub-link {{ $isActive('admin.finance.reports.summary') }}">Ringkasan Keuangan</a>
        <a href="{{ route('admin.finance.reports.outstanding') }}" class="sidebar-sub-link {{ $isActive('admin.finance.reports.outstanding') }}">Piutang SPP</a>
        <a href="{{ route('admin.currency.show') }}" class="sidebar-sub-link {{ $isActive('admin.currency.*') }}">Mata Uang</a>
    </div>
</div>

@if($isAdmin)
{{-- 🏫 FASILITAS --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🏫</span>Fasilitas</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.hostel.list.index') }}" class="sidebar-sub-link {{ $isActive('admin.hostel.*') }}">Asrama / Hostel</a>
        <a href="{{ route('admin.library.books.index') }}" class="sidebar-sub-link {{ $isActive('admin.library.*') }}">Perpustakaan</a>
        <a href="{{ route('admin.library.categories.index') }}" class="sidebar-sub-link {{ $isActive('admin.library.categories.*') }}">Kategori Buku</a>
        <a href="{{ route('admin.library.digital.upload') }}" class="sidebar-sub-link {{ $isActive('admin.library.digital.*') }}">e-Library Digital</a>
        <a href="{{ route('admin.transport.vehicles.index') }}" class="sidebar-sub-link {{ $isActive('admin.transport.*') }}">Transportasi</a>
        <a href="{{ route('admin.transport.routes.index') }}" class="sidebar-sub-link {{ $isActive('admin.transport.routes.*') }}">Rute Transport</a>
        <a href="{{ route('admin.transport.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.transport.dashboard') }}">Dashboard Transport</a>
        <a href="{{ route('admin.facilities.rooms.index') }}" class="sidebar-sub-link {{ $isActive('admin.facilities.rooms.*') }}">Booking Ruangan</a>
        <a href="{{ route('admin.facilities.rooms.calendar') }}" class="sidebar-sub-link {{ $isActive('admin.facilities.rooms.calendar') }}">Kalender Ruangan</a>
    </div>
</div>

{{-- 🔧 OPERASIONAL --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🔧</span>Operasional</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.visitor.logs.index') }}" class="sidebar-sub-link {{ $isActive('admin.visitor.logs.*') }}">Log Tamu</a>
        <a href="{{ route('admin.visitor.pre-registration.index') }}" class="sidebar-sub-link {{ $isActive('admin.visitor.pre-registration.*') }}">Pre-Registration Tamu</a>
        <a href="{{ route('admin.visitor.blacklist.index') }}" class="sidebar-sub-link {{ $isActive('admin.visitor.blacklist.*') }}">Blacklist Tamu</a>
        <a href="{{ route('admin.inventory.categories.index') }}" class="sidebar-sub-link {{ $isActive('admin.inventory.categories.*') }}">Kategori Aset</a>
        <a href="{{ route('admin.inventory.assets.index') }}" class="sidebar-sub-link {{ $isActive('admin.inventory.assets.*') }}">Daftar Aset</a>
        <a href="{{ route('admin.inventory.enhanced.index') }}" class="sidebar-sub-link {{ $isActive('admin.inventory.enhanced.*') }}">Aset Lanjutan</a>
        <a href="{{ route('admin.inventory.loans.index') }}" class="sidebar-sub-link {{ $isActive('admin.inventory.loans.*') }}">Peminjaman Aset</a>
        <a href="{{ route('admin.misc.maintenance.index') }}" class="sidebar-sub-link {{ $isActive('admin.misc.maintenance.*') }}">Maintenance Request</a>
        <a href="{{ route('admin.operations.gate-devices.index') }}" class="sidebar-sub-link {{ $isActive('admin.operations.gate-devices.*') }}">Gate Devices</a>
        <a href="{{ route('admin.operations.gate-events.index') }}" class="sidebar-sub-link {{ $isActive('admin.operations.gate-events.*') }}">Log Gate</a>
        <a href="{{ route('admin.dapodik.config.index') }}" class="sidebar-sub-link {{ $isActive('admin.dapodik.*') }}">Dapodik</a>
    </div>
</div>

{{-- 📢 KOMUNIKASI --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">📢</span>Komunikasi</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.notices.index') }}" class="sidebar-sub-link {{ $isActive('admin.notices.*') }}">Pengumuman</a>
        <a href="{{ route('admin.chat.inbox') }}" class="sidebar-sub-link {{ $isActive('admin.chat.*') }}">Chat</a>
        <a href="{{ route('admin.notifications.index') }}" class="sidebar-sub-link {{ $isActive('admin.notifications.*') }}">Notifikasi</a>
        <a href="{{ route('admin.reminders.index') }}" class="sidebar-sub-link {{ $isActive('admin.reminders.*') }}">Reminder Scheduler</a>
        <a href="{{ route('admin.wa-bot.commands.index') }}" class="sidebar-sub-link {{ $isActive('admin.wa-bot.*') }}">WA Bot (Chatbot)</a>
        <a href="{{ route('admin.wa-bot.conversations.index') }}" class="sidebar-sub-link {{ $isActive('admin.wa-bot.conversations.*') }}">Log Percakapan WA</a>
        <a href="{{ route('admin.emergency.index') }}" class="sidebar-sub-link {{ $isActive('admin.emergency.*') }}">Peringatan Darurat</a>
        <a href="{{ route('admin.emergency.contacts') }}" class="sidebar-sub-link {{ $isActive('admin.emergency.contacts') }}">Kontak Darurat</a>
        <a href="{{ route('admin.emergency.history') }}" class="sidebar-sub-link {{ $isActive('admin.emergency.history') }}">Riwayat Alert</a>
        <a href="{{ route('admin.webhooks.index') }}" class="sidebar-sub-link {{ $isActive('admin.webhooks.*') }}">Webhooks</a>
        <a href="{{ route('admin.notif.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.notif.*') }}">Provider Notifikasi</a>
    </div>
</div>

@endif

{{-- 📊 LAPORAN --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">📊</span>Laporan</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.reports.spp-aging') }}" class="sidebar-sub-link {{ $isActive('admin.reports.spp-aging') }}">Aging SPP</a>
        <a href="{{ route('admin.reports.attendance-pct') }}" class="sidebar-sub-link {{ $isActive('admin.reports.attendance-pct') }}">% Kehadiran</a>
        <a href="{{ route('admin.reports.grade-distribution') }}" class="sidebar-sub-link {{ $isActive('admin.reports.grade-distribution') }}">Distribusi Nilai</a>
        <a href="{{ route('admin.reports.discipline-leaderboard') }}" class="sidebar-sub-link {{ $isActive('admin.reports.discipline-leaderboard') }}">Leaderboard Disiplin</a>
        <a href="{{ route('admin.reports.cash-flow') }}" class="sidebar-sub-link {{ $isActive('admin.reports.cash-flow') }}">Cash Flow</a>
        <a href="{{ route('admin.reports.builder.index') }}" class="sidebar-sub-link {{ $isActive('admin.reports.builder.*') }}">Report Builder</a>
        <a href="{{ route('admin.foundation.benchmark.index') }}" class="sidebar-sub-link {{ $isActive('admin.foundation.benchmark.*') }}">Benchmark Yayasan</a>
    </div>
</div>

{{-- 🤖 AI --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🤖</span>AI & Analitik</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.ai.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.ai.*') }}">AI Providers</a>
        <a href="{{ route('admin.ai.usage') }}" class="sidebar-sub-link {{ $isActive('admin.ai.usage') }}">AI Usage Dashboard</a>
        <a href="{{ route('admin.analytics.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.analytics.dashboard') }}">School Intelligence</a>
        <a href="{{ route('admin.analytics.risks.index') }}" class="sidebar-sub-link {{ $isActive('admin.analytics.risks.*') }}">Learning Analytics</a>
        <a href="{{ route('admin.analytics.dropout-risk.index') }}" class="sidebar-sub-link {{ $isActive('admin.analytics.dropout-risk.*') }}">Deteksi Dropout</a>
    </div>
</div>

@if($isAdmin)
{{-- 🔄 WORKFLOW --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🔄</span>Workflow & Persetujuan</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.workflow.index') }}" class="sidebar-sub-link {{ $isActive('admin.workflow.*') }}">Antrian Persetujuan</a>
        <a href="{{ route('admin.workflow.create') }}" class="sidebar-sub-link {{ $isActive('admin.workflow.create') }}">Ajukan Permintaan</a>
    </div>
</div>

{{-- 📋 ADMINISTRASI --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">📋</span>Administrasi</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.blog.index') }}" class="sidebar-sub-link {{ $isActive('admin.blog.*') }}">Blog</a>
        <a href="{{ route('admin.documents.index') }}" class="sidebar-sub-link {{ $isActive('admin.documents.*') }}">Dokumen</a>
        <a href="{{ route('admin.documents.approvals') }}" class="sidebar-sub-link {{ $isActive('admin.documents.approvals') }}">Approval Dokumen</a>
        <a href="{{ route('admin.letters.templates') }}" class="sidebar-sub-link {{ $isActive('admin.letters.*') }}">Surat-Menyurat</a>
        <a href="{{ route('admin.surveys.templates.index') }}" class="sidebar-sub-link {{ $isActive('admin.surveys.*') }}">Survei Kepuasan</a>
        <a href="{{ route('admin.exports.index') }}" class="sidebar-sub-link {{ $isActive('admin.exports.*') }}">Ekspor Data</a>
        <a href="{{ route('admin.audit.index') }}" class="sidebar-sub-link {{ $isActive('admin.audit.*') }}">Audit Log</a>
        <a href="{{ route('admin.branding.website.pages') }}" class="sidebar-sub-link {{ $isActive('admin.branding.website.*') }}">Website Builder</a>
    </div>
</div>

{{-- 🎨 TAMPILAN --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">🎨</span>Tampilan</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.branding.show') }}" class="sidebar-sub-link {{ $isActive('admin.branding.*') }}">Branding & Logo</a>
        <a href="{{ route('admin.signage.config') }}" class="sidebar-sub-link {{ $isActive('admin.signage.*') }}">Digital Signage</a>
        <a href="{{ route('admin.dashboard-tv.config') }}" class="sidebar-sub-link {{ $isActive('admin.dashboard-tv.*') }}">Dashboard TV</a>
    </div>
</div>

{{-- ✅ AKREDITASI --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open=!open" class="sidebar-section-header"><span class="flex items-center gap-2"><span class="text-xs">✅</span>Akreditasi</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.accreditation.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.accreditation.*') }}">Dashboard Akreditasi</a>
        <a href="{{ route('admin.accreditation.instruments') }}" class="sidebar-sub-link {{ $isActive('admin.accreditation.instruments') }}">Instrumen Penilaian</a>
        <a href="{{ route('admin.accreditation.documents') }}" class="sidebar-sub-link {{ $isActive('admin.accreditation.documents') }}">Dokumen Bukti</a>
        <a href="{{ route('admin.adiwiyata.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.adiwiyata.*') }}">Adiwiyata</a>
        <a href="{{ route('admin.adiwiyata.indicators') }}" class="sidebar-sub-link {{ $isActive('admin.adiwiyata.indicators') }}">Indikator Adiwiyata</a>
    </div>
</div>
@endif
