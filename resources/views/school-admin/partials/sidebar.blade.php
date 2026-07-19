@php
    $isActive = fn($pattern) => request()->routeIs($pattern) ? 'active' : '';

    // Auto-open the section containing the active route
    $openSection = function($patterns) {
        foreach ((array)$patterns as $p) {
            if (request()->routeIs($p)) return true;
        }
        return false;
    };

    $icon = fn($d) => "<svg class='w-4.5 h-4.5' fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='1.8'><path stroke-linecap='round' stroke-linejoin='round' d='{$d}'/></svg>";

    $chevron = "<svg class='w-3.5 h-3.5 transition-transform duration-200' :class=\"open ? 'rotate-90' : ''\" fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M9 5l7 7-7 7'/></svg>";
@endphp

{{-- ===== SEARCH ===== --}}
<div class="px-3 pt-3 pb-2" x-data="{ q: '' }">
    <div class="relative">
        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" x-model="q" placeholder="Cari menu..."
            class="w-full bg-white/8 border border-white/10 rounded-lg pl-9 pr-3 py-2 text-xs text-white/80 placeholder-white/30 outline-none focus:border-white/25 focus:bg-white/12 transition"
            style="font-family:'Inter',sans-serif;">
    </div>
</div>

{{-- ===== DASHBOARD ===== --}}
<a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ $isActive('admin.dashboard') }}">
    {!! $icon('M3 12l9-9 9 9M5 10v10h14V10') !!}
    <span>Dashboard</span>
</a>

{{-- ===== AKADEMIK ===== --}}
<div class="sidebar-section" x-data="{ open: {{ $openSection(['admin.academic.*','admin.students.*','admin.staff.*','admin.attendance.*','admin.qr-attendance.*','admin.exams.*','admin.leaderboard.*','admin.assignments.*','admin.qbank.*','admin.curriculum.*','admin.raport-interaktif.*','admin.portfolios.*','admin.timetable.*','admin.accreditation.*','admin.adiwiyata.*','admin.calendar.*']) ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2.5">
            <span class="text-sm">📚</span>
            <span>Akademik</span>
        </span>
        {!! $chevron !!}
    </button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.academic.years.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.years.*') }}">Tahun Ajaran</a>
        <a href="{{ route('admin.academic.subjects.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.subjects.*') }}">Mata Pelajaran</a>
        <a href="{{ route('admin.academic.classes.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.classes.*') }}">Kelas</a>
        <a href="{{ route('admin.academic.class-sections.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.class-sections.*') }}">Rombel</a>
        <a href="{{ route('admin.students.index') }}" class="sidebar-sub-link {{ $isActive('admin.students.*') }}">Siswa</a>
        <a href="{{ route('admin.staff.index') }}" class="sidebar-sub-link {{ $isActive('admin.staff.*') }}">Staff & Guru</a>
        <a href="{{ route('admin.attendance.index') }}" class="sidebar-sub-link {{ $isActive('admin.attendance.*') }}">Absensi</a>
        <a href="{{ route('admin.qr-attendance.show') }}" class="sidebar-sub-link {{ $isActive('admin.qr-attendance.*') }}">Absensi QR</a>
        <a href="{{ route('admin.timetable.index') }}" class="sidebar-sub-link {{ $isActive('admin.timetable.*') }}">Jadwal Pelajaran</a>
        <a href="{{ route('admin.timetable.generator.wizard') }}" class="sidebar-sub-link {{ $isActive('admin.timetable.generator.*') }}">→ Generate Otomatis</a>
        <a href="{{ route('admin.calendar.index') }}" class="sidebar-sub-link {{ $isActive('admin.calendar.*') }}">Kalender Akademik</a>
        <a href="{{ route('admin.exams.index') }}" class="sidebar-sub-link {{ $isActive('admin.exams.*') }}">Ujian & Nilai</a>
        <a href="{{ route('admin.leaderboard.index') }}" class="sidebar-sub-link {{ $isActive('admin.leaderboard.*') }}">Leaderboard</a>
        <a href="{{ route('admin.raport-interaktif.index') }}" class="sidebar-sub-link {{ $isActive('admin.raport-interaktif.*') }}">Raport Interaktif</a>
        <a href="{{ route('admin.assignments.index') }}" class="sidebar-sub-link {{ $isActive('admin.assignments.*') }}">Online Classroom</a>
        <a href="{{ route('admin.qbank.items.index') }}" class="sidebar-sub-link {{ $isActive('admin.qbank.*') }}">Bank Soal</a>
        <a href="{{ route('admin.curriculum.frameworks.index') }}" class="sidebar-sub-link {{ $isActive('admin.curriculum.*') }}">Kurikulum (CP/ATP)</a>
        <a href="{{ route('admin.portfolios.index') }}" class="sidebar-sub-link {{ $isActive('admin.portfolios.*') }}">e-Portfolio</a>
        <a href="{{ route('admin.students.timeline') }}" class="sidebar-sub-link {{ $isActive('admin.students.timeline') }}">Timeline Aktivitas</a>
        <a href="{{ route('admin.accreditation.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.accreditation.*') }}">Akreditasi BAN-S/M</a>
        <a href="{{ route('admin.adiwiyata.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.adiwiyata.*') }}">Adiwiyata</a>
    </div>
</div>

{{-- ===== PENGAJARAN ===== --}}
<div class="sidebar-section" x-data="{ open: {{ $openSection(['admin.lesson-plan.*','admin.pkg.*','admin.training.*','admin.lesson-study.*','admin.academic.essay-grading.*','admin.live-class.*','admin.ai.*','admin.canteen.*','admin.religious.*']) ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2.5">
            <span class="text-sm">🎓</span>
            <span>Pengajaran</span>
        </span>
        {!! $chevron !!}
    </button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.lesson-plan.index') }}" class="sidebar-sub-link {{ $isActive('admin.lesson-plan.*') }}">Lesson Plan / RPP</a>
        <a href="{{ route('admin.pkg.index') }}" class="sidebar-sub-link {{ $isActive('admin.pkg.*') }}">PKG Guru</a>
        <a href="{{ route('admin.training.index') }}" class="sidebar-sub-link {{ $isActive('admin.training.*') }}">Diklat & Sertifikasi</a>
        <a href="{{ route('admin.lesson-study.index') }}" class="sidebar-sub-link {{ $isActive('admin.lesson-study.*') }}">Lesson Study</a>
        <a href="{{ route('admin.academic.essay-grading.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.essay-grading.*') }}">AI Penilaian Essay</a>
        <a href="{{ route('admin.live-class.index') }}" class="sidebar-sub-link {{ $isActive('admin.live-class.*') }}">Live Class</a>
        <a href="{{ route('admin.ai.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.ai.*') }}">AI Assistant</a>
        <a href="{{ route('admin.canteen.menu.index') }}" class="sidebar-sub-link {{ $isActive('admin.canteen.*') }}">Kantin Cashless</a>
        <a href="{{ route('admin.religious.targets.index') }}" class="sidebar-sub-link {{ $isActive('admin.religious.*') }}">Pesantren / Madrasah</a>
    </div>
</div>

{{-- ===== PPDB & SISWA ===== --}}
<div class="sidebar-section" x-data="{ open: {{ $openSection(['admin.ppdb.*','admin.clinic.*','admin.medical.*','admin.counseling.*','admin.discipline.*','admin.transport.*','admin.extracurricular.*','admin.misc.daily-reports','admin.misc.career']) ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2.5">
            <span class="text-sm">👨‍🎓</span>
            <span>PPDB & Kesiswaan</span>
        </span>
        {!! $chevron !!}
    </button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.ppdb.periods.index') }}" class="sidebar-sub-link {{ $isActive('admin.ppdb.*') }}">PPDB Online</a>
        <a href="{{ route('admin.clinic.visits.index') }}" class="sidebar-sub-link {{ $isActive('admin.clinic.*') || $isActive('admin.medical.*') }}">UKS / Klinik</a>
        <a href="{{ route('admin.counseling.sessions.index') }}" class="sidebar-sub-link {{ $isActive('admin.counseling.*') }}">BP/BK Konseling</a>
        <a href="{{ route('admin.discipline.records.index') }}" class="sidebar-sub-link {{ $isActive('admin.discipline.*') }}">Disiplin</a>
        <a href="{{ route('admin.transport.vehicles.index') }}" class="sidebar-sub-link {{ $isActive('admin.transport.*') }}">Transportasi</a>
        <a href="{{ route('admin.extracurricular.index') }}" class="sidebar-sub-link {{ $isActive('admin.extracurricular.*') }}">Ekstrakurikuler</a>
        <a href="{{ route('admin.misc.daily-reports') }}" class="sidebar-sub-link {{ $isActive('admin.misc.daily-reports') }}">Laporan Harian</a>
        <a href="{{ route('admin.misc.career') }}" class="sidebar-sub-link {{ $isActive('admin.misc.career') }}">Career Guidance</a>
    </div>
</div>

{{-- ===== ENGAGEMENT ===== --}}
<div class="sidebar-section" x-data="{ open: {{ $openSection(['admin.events.*','admin.donations.*','admin.achievements.*','admin.scholarship.*','admin.alumni.*','admin.tracer.*','admin.jobs.*','admin.bkk.*','admin.conferences.*','admin.forum.*','admin.committee.*','admin.osis.*']) ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2.5">
            <span class="text-sm">🤝</span>
            <span>Engagement</span>
        </span>
        {!! $chevron !!}
    </button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.events.index') }}" class="sidebar-sub-link {{ $isActive('admin.events.*') }}">Event</a>
        <a href="{{ route('admin.donations.campaigns.index') }}" class="sidebar-sub-link {{ $isActive('admin.donations.*') }}">Donasi</a>
        <a href="{{ route('admin.achievements.records.index') }}" class="sidebar-sub-link {{ $isActive('admin.achievements.*') }}">Prestasi</a>
        <a href="{{ route('admin.scholarship.programs.index') }}" class="sidebar-sub-link {{ $isActive('admin.scholarship.*') }}">Beasiswa</a>
        <a href="{{ route('admin.alumni.index') }}" class="sidebar-sub-link {{ $isActive('admin.alumni.*') }}">Alumni</a>
        <a href="{{ route('admin.tracer.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.tracer.*') }}">→ Tracer Study</a>
        <a href="{{ route('admin.jobs.index') }}" class="sidebar-sub-link {{ $isActive('admin.jobs.*') }}">→ Job Board</a>
        <a href="{{ route('admin.bkk.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.bkk.*') }}">→ BKK SMK</a>
        <a href="{{ route('admin.conferences.index') }}" class="sidebar-sub-link {{ $isActive('admin.conferences.*') }}">Konferensi Ortu</a>
        <a href="{{ route('admin.forum.categories') }}" class="sidebar-sub-link {{ $isActive('admin.forum.*') }}">Forum Komunitas</a>
        <a href="{{ route('admin.committee.members') }}" class="sidebar-sub-link {{ $isActive('admin.committee.*') }}">Komite Sekolah</a>
        <a href="{{ route('admin.osis.index') }}" class="sidebar-sub-link {{ $isActive('admin.osis.*') }}">OSIS / MPK</a>
    </div>
</div>

{{-- ===== KEUANGAN ===== --}}
<div class="sidebar-section" x-data="{ open: {{ $openSection(['admin.fee.*','admin.finance.*','admin.payroll.*','admin.payment.*','admin.budget.*','admin.procurement.*','admin.cooperative.*']) ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2.5">
            <span class="text-sm">💰</span>
            <span>Keuangan</span>
        </span>
        {!! $chevron !!}
    </button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.fee.structures.index') }}" class="sidebar-sub-link {{ $isActive('admin.fee.structures.*') }}">Struktur Biaya</a>
        <a href="{{ route('admin.fee.invoices.index') }}" class="sidebar-sub-link {{ $isActive('admin.fee.invoices.*') }}">Invoice / Tagihan</a>
        <a href="{{ route('admin.finance.reports.summary') }}" class="sidebar-sub-link {{ $isActive('admin.finance.reports.*') }}">Laporan Keuangan</a>
        <a href="{{ route('admin.payroll.structures.index') }}" class="sidebar-sub-link {{ $isActive('admin.payroll.structures.*') }}">Komponen Gaji</a>
        <a href="{{ route('admin.payroll.slips.index') }}" class="sidebar-sub-link {{ $isActive('admin.payroll.slips.*') }}">Slip Gaji</a>
        <a href="{{ route('admin.payment.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.payment.providers.*') }}">Provider Bayar</a>
        <a href="{{ route('admin.payment.methods.index') }}" class="sidebar-sub-link {{ $isActive('admin.payment.methods.*') }}">Metode Bayar</a>
        <a href="{{ route('admin.budget.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.budget.*') }}">Anggaran (RKAS)</a>
        <a href="{{ route('admin.procurement.index') }}" class="sidebar-sub-link {{ $isActive('admin.procurement.*') }}">Pengadaan</a>
        <a href="{{ route('admin.cooperative.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.cooperative.*') }}">Koperasi</a>
    </div>
</div>

{{-- ===== FASILITAS ===== --}}
<div class="sidebar-section" x-data="{ open: {{ $openSection(['admin.hostel.*','admin.library.*','admin.visitor.*','admin.visitors.*','admin.inventory.*','admin.dapodik.*']) ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2.5">
            <span class="text-sm">🏫</span>
            <span>Fasilitas</span>
        </span>
        {!! $chevron !!}
    </button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.hostel.list.index') }}" class="sidebar-sub-link {{ $isActive('admin.hostel.*') }}">Asrama / Hostel</a>
        <a href="{{ route('admin.library.books.index') }}" class="sidebar-sub-link {{ $isActive('admin.library.*') }}">Perpustakaan</a>
        <a href="{{ route('admin.visitor.logs.index') }}" class="sidebar-sub-link {{ $isActive('admin.visitor.logs.*') }}">Tamu / Visitor</a>
        <a href="{{ route('admin.visitor.pre-registration.index') }}" class="sidebar-sub-link {{ $isActive('admin.visitor.pre-registration.*') }}">Pre-Registrasi Tamu</a>
        <a href="{{ route('admin.inventory.assets.index') }}" class="sidebar-sub-link {{ $isActive('admin.inventory.*') }}">Inventaris / Aset</a>
        <a href="{{ route('admin.dapodik.config.index') }}" class="sidebar-sub-link {{ $isActive('admin.dapodik.*') }}">Dapodik</a>
    </div>
</div>
</div>
</div>

{{-- ===== KOMUNIKASI ===== --}}
<div class="sidebar-section" x-data="{ open: {{ $openSection(['admin.notices.*','admin.chat.*','admin.notifications.*','admin.emergency.*','admin.wa-bot.*','admin.reminders.*']) ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2.5">
            <span class="text-sm">📢</span>
            <span>Komunikasi</span>
        </span>
        {!! $chevron !!}
    </button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.notices.index') }}" class="sidebar-sub-link {{ $isActive('admin.notices.*') }}">Pengumuman</a>
        <a href="{{ route('admin.chat.inbox') }}" class="sidebar-sub-link {{ $isActive('admin.chat.*') }}">Chat</a>
        <a href="{{ route('admin.notifications.index') }}" class="sidebar-sub-link {{ $isActive('admin.notifications.*') }}">Notifikasi</a>
        <a href="{{ route('admin.emergency.index') }}" class="sidebar-sub-link {{ $isActive('admin.emergency.*') }}">Peringatan Darurat</a>
        <a href="{{ route('admin.wa-bot.commands.index') }}" class="sidebar-sub-link {{ $isActive('admin.wa-bot.*') }}">WhatsApp Bot</a>
        <a href="{{ route('admin.reminders.index') }}" class="sidebar-sub-link {{ $isActive('admin.reminders.*') }}">Pengingat Otomatis</a>
    </div>
</div>

{{-- ===== LAPORAN ===== --}}
<div class="sidebar-section" x-data="{ open: {{ $openSection(['admin.reports.*','admin.foundation.benchmark.*','admin.analytics.*']) ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2.5">
            <span class="text-sm">📊</span>
            <span>Laporan</span>
        </span>
        {!! $chevron !!}
    </button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.reports.spp-aging') }}" class="sidebar-sub-link {{ $isActive('admin.reports.spp-aging') }}">Aging SPP</a>
        <a href="{{ route('admin.reports.attendance-pct') }}" class="sidebar-sub-link {{ $isActive('admin.reports.attendance-pct') }}">% Kehadiran</a>
        <a href="{{ route('admin.reports.grade-distribution') }}" class="sidebar-sub-link {{ $isActive('admin.reports.grade-distribution') }}">Distribusi Nilai</a>
        <a href="{{ route('admin.reports.cash-flow') }}" class="sidebar-sub-link {{ $isActive('admin.reports.cash-flow') }}">Cash Flow</a>
        <a href="{{ route('admin.reports.builder.index') }}" class="sidebar-sub-link {{ $isActive('admin.reports.builder.*') }}">Report Builder</a>
        <a href="{{ route('admin.foundation.benchmark.index') }}" class="sidebar-sub-link {{ $isActive('admin.foundation.benchmark.*') }}">Benchmark Yayasan</a>
        <a href="{{ route('admin.analytics.risks.index') }}" class="sidebar-sub-link {{ $isActive('admin.analytics.risks.*') }}">Learning Analytics</a>
        <a href="{{ route('admin.analytics.dropout-risk.index') }}" class="sidebar-sub-link {{ $isActive('admin.analytics.dropout-risk.*') }}">Deteksi Dropout</a>
    </div>
</div>

{{-- ===== ADMINISTRASI ===== --}}
<div class="sidebar-section" x-data="{ open: {{ $openSection(['admin.blog.*','admin.documents.*','admin.letters.*','admin.surveys.*','admin.notif.*','admin.exports.*','admin.audit.*','admin.signage.*']) ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2.5">
            <span class="text-sm">⚙️</span>
            <span>Administrasi</span>
        </span>
        {!! $chevron !!}
    </button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.blog.index') }}" class="sidebar-sub-link {{ $isActive('admin.blog.*') }}">Blog</a>
        <a href="{{ route('admin.documents.index') }}" class="sidebar-sub-link {{ $isActive('admin.documents.*') }}">Dokumen</a>
        <a href="{{ route('admin.letters.templates') }}" class="sidebar-sub-link {{ $isActive('admin.letters.*') }}">Surat-Menyurat</a>
        <a href="{{ route('admin.surveys.templates.index') }}" class="sidebar-sub-link {{ $isActive('admin.surveys.*') }}">Survei Kepuasan</a>
        <a href="{{ route('admin.notif.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.notif.*') }}">Provider Notifikasi</a>
        <a href="{{ route('admin.exports.index') }}" class="sidebar-sub-link {{ $isActive('admin.exports.*') }}">Ekspor Data</a>
        <a href="{{ route('admin.audit.index') }}" class="sidebar-sub-link {{ $isActive('admin.audit.*') }}">Audit Log</a>
        <a href="{{ route('admin.signage.config') }}" class="sidebar-sub-link {{ $isActive('admin.signage.*') }}">Digital Signage</a>
    </div>
</div>

{{-- ===== TAMPILAN ===== --}}
<div class="sidebar-section" x-data="{ open: {{ $openSection(['admin.branding.*']) ? 'true' : 'false' }} }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2.5">
            <span class="text-sm">🎨</span>
            <span>Tampilan</span>
        </span>
        {!! $chevron !!}
    </button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.branding.show') }}" class="sidebar-sub-link {{ $isActive('admin.branding.show') }}">Branding & Logo</a>
        <a href="{{ route('admin.branding.website.pages') }}" class="sidebar-sub-link {{ $isActive('admin.branding.website.*') }}">Website Builder</a>
    </div>
</div>
</div>
