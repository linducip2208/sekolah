@php
    $isActive = fn($pattern) => request()->routeIs($pattern) ? 'active' : '';
    $icon = fn($d) => "<svg class='w-4 h-4 flex-shrink-0' fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='1.8'><path stroke-linecap='round' stroke-linejoin='round' d='{$d}'/></svg>";
    $chevron = "<svg class='w-3 h-3 transition-transform duration-200' :class=\"open ? 'rotate-90' : ''\" fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='2.5'><path stroke-linecap='round' stroke-linejoin='round' d='M9 5l7 7-7 7'/></svg>";
@endphp

<div class="px-3 pt-3 pb-1 flex items-center justify-between gap-2">
    <div class="relative flex-1" x-data="{ q: '' }">
        <svg class="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" x-model="q" placeholder="Cari menu..."
            class="w-full bg-white/6 border border-white/8 rounded-md pl-7 pr-2 py-1.5 text-[.6rem] text-white/70 placeholder-white/25 outline-none focus:border-white/20 transition"
            style="font-family:'Inter',sans-serif;"
            @input="document.querySelectorAll('.sidebar-section').forEach(s => { const t = s.querySelector('.sidebar-section-header'); const txt = s.textContent.toLowerCase(); s.style.display = q.length < 2 || txt.includes(q.toLowerCase()) ? '' : 'none' })">
    </div>
    <button onclick="document.querySelectorAll('.sidebar-section').forEach(s => { s.__x.$data.open = true })" class="text-white/30 hover:text-white/70 p-1 text-[.55rem] uppercase tracking-wider" style="font-family:'Inter',sans-serif;" title="Buka semua">&#x25BC;</button>
    <button onclick="document.querySelectorAll('.sidebar-section').forEach(s => { s.__x.$data.open = false })" class="text-white/30 hover:text-white/70 p-1 text-[.55rem] uppercase tracking-wider" style="font-family:'Inter',sans-serif;" title="Tutup semua">&#x25B2;</button>
</div>

<a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ $isActive('admin.dashboard') }}">
    {!! $icon('M3 12l9-9 9 9M5 10v10h14V10') !!}
    <span>Dashboard</span>
</a>

{{-- 📚 Akademik Inti --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">📚</span>Akademik Inti</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.academic.years.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.years.*') }}">Tahun Ajaran</a>
        <a href="{{ route('admin.academic.subjects.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.subjects.*') }}">Mata Pelajaran</a>
        <a href="{{ route('admin.academic.classes.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.classes.*') }}">Kelas</a>
        <a href="{{ route('admin.academic.class-sections.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.class-sections.*') }}">Rombel</a>
        <a href="{{ route('admin.curriculum.frameworks.index') }}" class="sidebar-sub-link {{ $isActive('admin.curriculum.*') }}">Kurikulum (CP/ATP)</a>
        <a href="{{ route('admin.calendar.index') }}" class="sidebar-sub-link {{ $isActive('admin.calendar.*') }}">Kalender Akademik</a>
    </div>
</div>

{{-- 👥 Siswa & Staff --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">👥</span>Siswa & Staff</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.students.index') }}" class="sidebar-sub-link {{ $isActive('admin.students.*') }}">Data Siswa</a>
        <a href="{{ route('admin.staff.index') }}" class="sidebar-sub-link {{ $isActive('admin.staff.*') }}">Staff & Guru</a>
        <a href="{{ route('admin.portfolios.index') }}" class="sidebar-sub-link {{ $isActive('admin.portfolios.*') }}">e-Portfolio</a>
        <a href="{{ route('admin.misc.career') }}" class="sidebar-sub-link {{ $isActive('admin.misc.career') }}">Career Guidance</a>
    </div>
</div>

{{-- 📋 Absensi & Jadwal --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">📋</span>Absensi & Jadwal</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.attendance.index') }}" class="sidebar-sub-link {{ $isActive('admin.attendance.*') }}">Absensi Harian</a>
        <a href="{{ route('admin.qr-attendance.show') }}" class="sidebar-sub-link {{ $isActive('admin.qr-attendance.*') }}">Absensi QR Code</a>
        <a href="{{ route('admin.timetable.index') }}" class="sidebar-sub-link {{ $isActive('admin.timetable.*') }}">Jadwal Pelajaran</a>
        <a href="{{ route('admin.timetable.generator.wizard') }}" class="sidebar-sub-link {{ $isActive('admin.timetable.generator.*') }}">&#x2192; Generate Otomatis</a>
    </div>
</div>

{{-- 📝 Ujian & Penilaian --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">📝</span>Ujian & Penilaian</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.exams.index') }}" class="sidebar-sub-link {{ $isActive('admin.exams.*') }}">Ujian</a>
        <a href="{{ route('admin.qbank.items.index') }}" class="sidebar-sub-link {{ $isActive('admin.qbank.*') }}">Bank Soal</a>
        <a href="{{ route('admin.academic.essay-grading.index') }}" class="sidebar-sub-link {{ $isActive('admin.academic.essay-grading.*') }}">AI Penilaian Essay</a>
        <a href="{{ route('admin.raport-interaktif.index') }}" class="sidebar-sub-link {{ $isActive('admin.raport-interaktif.*') }}">Raport Interaktif</a>
    </div>
</div>

{{-- 🏆 Prestasi & Gamifikasi --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">🏆</span>Prestasi & Gamifikasi</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.leaderboard.index') }}" class="sidebar-sub-link {{ $isActive('admin.leaderboard.*') }}">Leaderboard</a>
        <a href="{{ route('admin.achievements.records.index') }}" class="sidebar-sub-link {{ $isActive('admin.achievements.*') }}">Prestasi Siswa</a>
        <a href="{{ route('admin.misc.daily-reports') }}" class="sidebar-sub-link {{ $isActive('admin.misc.daily-reports') }}">Laporan Harian</a>
    </div>
</div>

{{-- 🎓 Pengajaran --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">🎓</span>Pengajaran</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.assignments.index') }}" class="sidebar-sub-link {{ $isActive('admin.assignments.*') }}">Online Classroom</a>
        <a href="{{ route('admin.lesson-plan.index') }}" class="sidebar-sub-link {{ $isActive('admin.lesson-plan.*') }}">Lesson Plan / RPP</a>
        <a href="{{ route('admin.live-class.index') }}" class="sidebar-sub-link {{ $isActive('admin.live-class.*') }}">Live Class</a>
        <a href="{{ route('admin.canteen.menu.index') }}" class="sidebar-sub-link {{ $isActive('admin.canteen.*') }}">Kantin Cashless</a>
        <a href="{{ route('admin.religious.targets.index') }}" class="sidebar-sub-link {{ $isActive('admin.religious.*') }}">Pesantren / Madrasah</a>
    </div>
</div>

{{-- 🧠 Pengembangan Guru --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">🧠</span>Pengembangan Guru</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.pkg.index') }}" class="sidebar-sub-link {{ $isActive('admin.pkg.*') }}">PKG (Penilaian Kinerja)</a>
        <a href="{{ route('admin.training.index') }}" class="sidebar-sub-link {{ $isActive('admin.training.*') }}">Diklat & Sertifikasi</a>
        <a href="{{ route('admin.lesson-study.index') }}" class="sidebar-sub-link {{ $isActive('admin.lesson-study.*') }}">Lesson Study</a>
    </div>
</div>

{{-- 🧒 PPDB & Kesiswaan --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">🧒</span>PPDB & Kesiswaan</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.ppdb.periods.index') }}" class="sidebar-sub-link {{ $isActive('admin.ppdb.*') }}">PPDB Online</a>
        <a href="{{ route('admin.clinic.visits.index') }}" class="sidebar-sub-link {{ $isActive('admin.clinic.*') || $isActive('admin.medical.*') }}">UKS / Klinik</a>
        <a href="{{ route('admin.counseling.sessions.index') }}" class="sidebar-sub-link {{ $isActive('admin.counseling.*') }}">BP/BK Konseling</a>
        <a href="{{ route('admin.discipline.records.index') }}" class="sidebar-sub-link {{ $isActive('admin.discipline.*') }}">Disiplin</a>
        <a href="{{ route('admin.extracurricular.index') }}" class="sidebar-sub-link {{ $isActive('admin.extracurricular.*') }}">Ekstrakurikuler</a>
    </div>
</div>

{{-- 🤝 Engagement --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">🤝</span>Engagement</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.events.index') }}" class="sidebar-sub-link {{ $isActive('admin.events.*') }}">Event Sekolah</a>
        <a href="{{ route('admin.donations.campaigns.index') }}" class="sidebar-sub-link {{ $isActive('admin.donations.*') }}">Donasi & Fundraising</a>
        <a href="{{ route('admin.scholarship.programs.index') }}" class="sidebar-sub-link {{ $isActive('admin.scholarship.*') }}">Beasiswa</a>
        <a href="{{ route('admin.conferences.index') }}" class="sidebar-sub-link {{ $isActive('admin.conferences.*') }}">Konferensi Ortu</a>
        <a href="{{ route('admin.forum.categories') }}" class="sidebar-sub-link {{ $isActive('admin.forum.*') }}">Forum Komunitas</a>
    </div>
</div>

{{-- 🏛 Organisasi Sekolah --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">🏛</span>Organisasi Sekolah</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.committee.members') }}" class="sidebar-sub-link {{ $isActive('admin.committee.*') }}">Komite Sekolah</a>
        <a href="{{ route('admin.osis.index') }}" class="sidebar-sub-link {{ $isActive('admin.osis.*') }}">OSIS / MPK</a>
    </div>
</div>

{{-- 🎓 Alumni & Karir --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">🎓</span>Alumni & Karir</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.alumni.index') }}" class="sidebar-sub-link {{ $isActive('admin.alumni.*') }}">Data Alumni</a>
        <a href="{{ route('admin.tracer.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.tracer.*') }}">Tracer Study</a>
        <a href="{{ route('admin.jobs.index') }}" class="sidebar-sub-link {{ $isActive('admin.jobs.*') }}">Job Board</a>
        <a href="{{ route('admin.bkk.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.bkk.*') }}">BKK (Bursa Kerja)</a>
    </div>
</div>

{{-- 💰 Keuangan --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">💰</span>Keuangan</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.fee.structures.index') }}" class="sidebar-sub-link {{ $isActive('admin.fee.structures.*') }}">Struktur Biaya</a>
        <a href="{{ route('admin.fee.invoices.index') }}" class="sidebar-sub-link {{ $isActive('admin.fee.invoices.*') }}">Invoice / Tagihan</a>
        <a href="{{ route('admin.payroll.structures.index') }}" class="sidebar-sub-link {{ $isActive('admin.payroll.structures.*') }}">Komponen Gaji</a>
        <a href="{{ route('admin.payroll.slips.index') }}" class="sidebar-sub-link {{ $isActive('admin.payroll.slips.*') }}">Slip Gaji</a>
        <a href="{{ route('admin.payment.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.payment.providers.*') }}">Provider Bayar</a>
        <a href="{{ route('admin.payment.methods.index') }}" class="sidebar-sub-link {{ $isActive('admin.payment.methods.*') }}">Metode Bayar</a>
        <a href="{{ route('admin.budget.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.budget.*') }}">Anggaran (RKAS)</a>
        <a href="{{ route('admin.procurement.index') }}" class="sidebar-sub-link {{ $isActive('admin.procurement.*') }}">Pengadaan</a>
        <a href="{{ route('admin.cooperative.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.cooperative.*') }}">Koperasi</a>
        <a href="{{ route('admin.finance.reports.summary') }}" class="sidebar-sub-link {{ $isActive('admin.finance.reports.*') }}">Laporan Keuangan</a>
    </div>
</div>

{{-- 🏫 Fasilitas --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">🏫</span>Fasilitas</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.hostel.list.index') }}" class="sidebar-sub-link {{ $isActive('admin.hostel.*') }}">Asrama / Hostel</a>
        <a href="{{ route('admin.library.books.index') }}" class="sidebar-sub-link {{ $isActive('admin.library.*') }}">Perpustakaan</a>
        <a href="{{ route('admin.digital-library.index') }}" class="sidebar-sub-link {{ $isActive('admin.digital-library.*') }}">e-Library Digital</a>
        <a href="{{ route('admin.transport.vehicles.index') }}" class="sidebar-sub-link {{ $isActive('admin.transport.*') }}">Transportasi</a>
        <a href="{{ route('admin.rooms.index') }}" class="sidebar-sub-link {{ $isActive('admin.rooms.*') }}">Booking Ruangan</a>
    </div>
</div>

{{-- 🔧 Operasional --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">🔧</span>Operasional</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.visitor.logs.index') }}" class="sidebar-sub-link {{ $isActive('admin.visitor.*') || $isActive('admin.visitors.*') }}">Tamu / Visitor</a>
        <a href="{{ route('admin.visitor.pre-registration') }}" class="sidebar-sub-link {{ $isActive('admin.visitor.pre-registration') }}">Pre-Registration Tamu</a>
        <a href="{{ route('admin.inventory.assets.index') }}" class="sidebar-sub-link {{ $isActive('admin.inventory.assets.*') }}">Inventaris / Aset</a>
        <a href="{{ route('admin.inventory.enhanced.index') }}" class="sidebar-sub-link {{ $isActive('admin.inventory.enhanced.*') || $isActive('admin.inventory.maintenance*') || $isActive('admin.inventory.writeoffs*') }}">Aset Lanjutan</a>
        <a href="{{ route('admin.dapodik.config.index') }}" class="sidebar-sub-link {{ $isActive('admin.dapodik.*') }}">Dapodik</a>
    </div>
</div>

{{-- 📢 Komunikasi --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">📢</span>Komunikasi</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.notices.index') }}" class="sidebar-sub-link {{ $isActive('admin.notices.*') }}">Pengumuman</a>
        <a href="{{ route('admin.chat.inbox') }}" class="sidebar-sub-link {{ $isActive('admin.chat.*') }}">Chat</a>
        <a href="{{ route('admin.notifications.index') }}" class="sidebar-sub-link {{ $isActive('admin.notifications.*') }}">Notifikasi</a>
        <a href="{{ route('admin.reminders.index') }}" class="sidebar-sub-link {{ $isActive('admin.reminders.*') }}">Reminder Scheduler</a>
        <a href="{{ route('admin.wa-bot.commands.index') }}" class="sidebar-sub-link {{ $isActive('admin.wa-bot.*') }}">WA Bot (Chatbot)</a>
        <a href="{{ route('admin.emergency.index') }}" class="sidebar-sub-link {{ $isActive('admin.emergency.*') }}">Peringatan Darurat</a>
        <a href="{{ route('admin.notif.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.notif.*') }}">Provider Notifikasi</a>
    </div>
</div>

{{-- 📊 Laporan --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">📊</span>Laporan</span>{!! $chevron !!}</button>
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

{{-- 🤖 AI & Analitik --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">🤖</span>AI & Analitik</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.ai.providers.index') }}" class="sidebar-sub-link {{ $isActive('admin.ai.*') }}">AI Providers</a>
        <a href="{{ route('admin.analytics.risks.index') }}" class="sidebar-sub-link {{ $isActive('admin.analytics.risks.*') }}">Learning Analytics</a>
        <a href="{{ route('admin.analytics.dropout-risk.index') }}" class="sidebar-sub-link {{ $isActive('admin.analytics.dropout-risk.*') }}">Deteksi Dropout</a>
    </div>
</div>

{{-- 📋 Administrasi --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">📋</span>Administrasi</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.blog.index') }}" class="sidebar-sub-link {{ $isActive('admin.blog.*') }}">Blog</a>
        <a href="{{ route('admin.documents.index') }}" class="sidebar-sub-link {{ $isActive('admin.documents.*') }}">Dokumen</a>
        <a href="{{ route('admin.letters.templates') }}" class="sidebar-sub-link {{ $isActive('admin.letters.*') }}">Surat-Menyurat</a>
        <a href="{{ route('admin.surveys.templates.index') }}" class="sidebar-sub-link {{ $isActive('admin.surveys.*') }}">Survei Kepuasan</a>
        <a href="{{ route('admin.exports.index') }}" class="sidebar-sub-link {{ $isActive('admin.exports.*') }}">Ekspor Data</a>
        <a href="{{ route('admin.audit.index') }}" class="sidebar-sub-link {{ $isActive('admin.audit.*') }}">Audit Log</a>
        <a href="{{ route('admin.website.pages.index') }}" class="sidebar-sub-link {{ $isActive('admin.website.*') }}">Website Builder</a>
    </div>
</div>

{{-- 🎨 Tampilan --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">🎨</span>Tampilan</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.branding.show') }}" class="sidebar-sub-link {{ $isActive('admin.branding.*') }}">Branding & Logo</a>
        <a href="{{ route('admin.signage.config') }}" class="sidebar-sub-link {{ $isActive('admin.signage.*') }}">Digital Signage</a>
        <a href="{{ route('admin.dashboard-tv.config') }}" class="sidebar-sub-link {{ $isActive('admin.dashboard-tv.*') }}">Dashboard TV</a>
    </div>
</div>

{{-- ✅ Akreditasi --}}
<div class="sidebar-section" x-data="{ open: true }">
    <button @click="open = !open" class="sidebar-section-header">
        <span class="flex items-center gap-2"><span class="text-xs">✅</span>Akreditasi</span>{!! $chevron !!}</button>
    <div x-show="open" x-collapse class="sidebar-section-body">
        <a href="{{ route('admin.accreditation.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.accreditation.*') }}">Akreditasi BAN-S/M</a>
        <a href="{{ route('admin.adiwiyata.dashboard') }}" class="sidebar-sub-link {{ $isActive('admin.adiwiyata.*') }}">Adiwiyata</a>
    </div>
</div>
