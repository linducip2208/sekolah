@php
    $isActive = fn($pattern) => request()->routeIs($pattern) ? 'active' : '';
    $iconClass = 'w-5 h-5';
@endphp

<a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ $isActive('admin.dashboard') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h14V10"/></svg>
    Dashboard
</a>

{{-- ==================== AKADEMIK (Core) ==================== --}}
<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Akademik</div>

<a href="{{ route('admin.academic.years.index') }}" class="sidebar-link {{ $isActive('admin.academic.years.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    Tahun Ajaran
</a>
<a href="{{ route('admin.academic.subjects.index') }}" class="sidebar-link {{ $isActive('admin.academic.subjects.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
    Mata Pelajaran
</a>
<a href="{{ route('admin.academic.classes.index') }}" class="sidebar-link {{ $isActive('admin.academic.classes.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    Kelas
</a>
<a href="{{ route('admin.academic.sections.index') }}" class="sidebar-link {{ $isActive('admin.academic.sections.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
    Section
</a>
<a href="{{ route('admin.academic.class-sections.index') }}" class="sidebar-link {{ $isActive('admin.academic.class-sections.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    Rombel
</a>
<a href="{{ route('admin.academic.mediums.index') }}" class="sidebar-link {{ $isActive('admin.academic.mediums.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
    Medium
</a>
<a href="{{ route('admin.students.index') }}" class="sidebar-link {{ $isActive('admin.students.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    Siswa
</a>
<a href="{{ route('admin.staff.index') }}" class="sidebar-link {{ $isActive('admin.staff.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    Staff & Guru
</a>
<a href="{{ route('admin.attendance.index') }}" class="sidebar-link {{ $isActive('admin.attendance.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
    Absensi
</a>
<a href="{{ route('admin.qr-attendance.show') }}" class="sidebar-link {{ $isActive('admin.qr-attendance.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2m4 0v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    Absensi QR
</a>
    <a href="{{ route('admin.exams.index') }}" class="sidebar-link {{ $isActive('admin.exams.*') }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        Ujian & Nilai
    </a>
    <a href="{{ route('admin.leaderboard.index') }}" class="sidebar-link {{ $isActive('admin.leaderboard.*') }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        Leaderboard
    </a>
<a href="{{ route('admin.assignments.index') }}" class="sidebar-link {{ $isActive('admin.assignments.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
    Online Classroom
</a>
<a href="{{ route('admin.qbank.items.index') }}" class="sidebar-link {{ $isActive('admin.qbank.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Bank Soal
</a>
<a href="{{ route('admin.curriculum.frameworks.index') }}" class="sidebar-link {{ $isActive('admin.curriculum.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h6m-6 4h6m-6 4h6"/></svg>
    Kurikulum (CP/ATP)
</a>
<a href="{{ route('admin.raport-interaktif.index') }}" class="sidebar-link {{ $isActive('admin.raport-interaktif.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
    Raport Interaktif
</a>
<a href="{{ route('admin.portfolios.index') }}" class="sidebar-link {{ $isActive('admin.portfolios.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    e-Portfolio
</a>
<a href="{{ route('admin.timetable.index') }}" class="sidebar-link {{ $isActive('admin.timetable.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Jadwal Pelajaran
</a>
<a href="{{ route('admin.timetable.generator.wizard') }}" class="sidebar-link {{ $isActive('admin.timetable.generator.*') }}" style="font-size:.63rem; padding-left:2.2rem;">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    Generate Otomatis
</a>
<a href="{{ route('admin.accreditation.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.accreditation.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    Akreditasi (BAN-S/M)
</a>
<a href="{{ route('admin.adiwiyata.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.adiwiyata.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
    Adiwiyata (Sekolah Hijau)
</a>
<a href="{{ route('admin.calendar.index') }}" class="sidebar-link {{ $isActive('admin.calendar.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    Kalender Akademik
</a>
<a href="{{ route('admin.notices.index') }}" class="sidebar-link {{ $isActive('admin.notices.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
    Pengumuman
</a>
<a href="{{ route('admin.chat.inbox') }}" class="sidebar-link {{ $isActive('admin.chat.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    Chat
</a>
<a href="{{ route('admin.notifications.index') }}" class="sidebar-link {{ $isActive('admin.notifications.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
    Notifikasi
</a>
<a href="{{ route('admin.emergency.index') }}" class="sidebar-link {{ $isActive('admin.emergency.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    Peringatan Darurat
</a>
<a href="{{ route('admin.hostel.list.index') }}" class="sidebar-link {{ $isActive('admin.hostel.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    Asrama / Hostel
</a>
<a href="{{ route('admin.library.books.index') }}" class="sidebar-link {{ $isActive('admin.library.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
    Perpustakaan
</a>

{{-- ==================== STUDENT LIFECYCLE (Phase 8) ==================== --}}
<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Siswa & Pendaftaran</div>

<a href="{{ route('admin.ppdb.periods.index') }}" class="sidebar-link {{ request()->routeIs('admin.ppdb.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    PPDB
</a>
<a href="{{ route('admin.clinic.visits.index') }}" class="sidebar-link {{ request()->routeIs('admin.clinic.*') || request()->routeIs('admin.medical.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    UKS / Klinik
</a>
<a href="{{ route('admin.counseling.sessions.index') }}" class="sidebar-link {{ request()->routeIs('admin.counseling.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    BP/BK Konseling
</a>
<a href="{{ route('admin.discipline.records.index') }}" class="sidebar-link {{ request()->routeIs('admin.discipline.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    Disiplin
</a>
<a href="{{ route('admin.transport.vehicles.index') }}" class="sidebar-link {{ request()->routeIs('admin.transport.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
    Transportasi
</a>
<a href="{{ route('admin.extracurricular.index') }}" class="sidebar-link {{ $isActive('admin.extracurricular.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    Ekstrakurikuler
</a>
<a href="{{ route('admin.misc.daily-reports') }}" class="sidebar-link {{ $isActive('admin.misc.daily-reports') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Laporan Harian
</a>
<a href="{{ route('admin.misc.career') }}" class="sidebar-link {{ $isActive('admin.misc.career') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    Career Guidance
</a>

{{-- ==================== TEACHING TOOLS (Phase 9) ==================== --}}
<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Pengajaran</div>

    <a href="{{ route('admin.lesson-plan.index') }}" class="sidebar-link {{ $isActive('admin.lesson-plan.*') }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        Lesson Plan / RPP
    </a>
    <a href="{{ route('admin.pkg.index') }}" class="sidebar-link {{ $isActive('admin.pkg.*') }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        PKG Guru
    </a>
    <a href="{{ route('admin.academic.essay-grading.index') }}" class="sidebar-link {{ $isActive('admin.academic.essay-grading.*') }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        AI Penilaian Essay
    </a>
<a href="{{ route('admin.live-class.index') }}" class="sidebar-link {{ $isActive('admin.live-class.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
    Live Class
</a>
<a href="{{ route('admin.ai.providers.index') }}" class="sidebar-link {{ $isActive('admin.ai.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    AI Assistant
</a>
<a href="{{ route('admin.canteen.menu.index') }}" class="sidebar-link {{ $isActive('admin.canteen.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
    Kantin Cashless
</a>
<a href="{{ route('admin.religious.targets.index') }}" class="sidebar-link {{ $isActive('admin.religious.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
    Pesantren / Madrasah
</a>

{{-- ==================== ENGAGEMENT (Phase 10) ==================== --}}
<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Engagement</div>

<a href="{{ route('admin.events.index') }}" class="sidebar-link {{ $isActive('admin.events.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    Event
</a>
<a href="{{ route('admin.donations.campaigns.index') }}" class="sidebar-link {{ $isActive('admin.donations.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
    Donasi & Fundraising
</a>
<a href="{{ route('admin.achievements.records.index') }}" class="sidebar-link {{ $isActive('admin.achievements.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
    Prestasi Siswa
</a>
<a href="{{ route('admin.scholarship.programs.index') }}" class="sidebar-link {{ $isActive('admin.scholarship.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
    Beasiswa
</a>
    <a href="{{ route('admin.alumni.index') }}" class="sidebar-link {{ $isActive('admin.alumni.*') }}">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm-7 6h14"/></svg>
        Alumni
    </a>
<a href="{{ route('admin.tracer.dashboard') }}" class="sidebar-link {{ $isActive('admin.tracer.*') }}" style="font-size:.63rem; padding-left:2.2rem;">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
    Tracer Study
</a>
<a href="{{ route('admin.jobs.index') }}" class="sidebar-link {{ $isActive('admin.jobs.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    Job Board
</a>
<a href="{{ route('admin.bkk.dashboard') }}" class="sidebar-link {{ $isActive('admin.bkk.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    BKK (Bursa Kerja)
</a>

<a href="{{ route('admin.conferences.index') }}" class="sidebar-link {{ $isActive('admin.conferences.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    Konferensi
</a>

<a href="{{ route('admin.forum.categories') }}" class="sidebar-link {{ request()->routeIs('admin.forum.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
    Forum Komunitas
</a>
<a href="{{ route('admin.committee.members') }}" class="sidebar-link {{ request()->routeIs('admin.committee.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    Komite Sekolah
</a>
<a href="{{ route('admin.osis.index') }}" class="sidebar-link {{ request()->routeIs('admin.osis.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    OSIS / MPK
</a>

{{-- ==================== OPERATIONS (Phase 11) ==================== --}}
<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Operasional</div>

<a href="{{ route('admin.visitor.logs.index') }}" class="sidebar-link {{ request()->routeIs('admin.visitor.*') || request()->routeIs('admin.visitors.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    Tamu / Visitor
</a>
<a href="{{ route('admin.inventory.assets.index') }}" class="sidebar-link {{ $isActive('admin.inventory.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
    Inventaris / Aset
</a>
<a href="{{ route('admin.inventory.enhanced.index') }}" class="sidebar-link {{ $isActive('admin.inventory.enhanced.*') || $isActive('admin.inventory.maintenance*') || $isActive('admin.inventory.writeoffs*') ? 'active' : '' }}" style="font-size:.63rem; padding-left:2.2rem;">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
    Aset Lanjutan
</a>
<a href="{{ route('admin.dapodik.config.index') }}" class="sidebar-link {{ $isActive('admin.dapodik.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
    Sinkronisasi Dapodik
</a>
<a href="{{ route('admin.analytics.risks.index') }}" class="sidebar-link {{ $isActive('admin.analytics.risks.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
    Learning Analytics
</a>
<a href="{{ route('admin.analytics.dropout-risk.index') }}" class="sidebar-link {{ $isActive('admin.analytics.dropout-risk.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    Deteksi Dropout
</a>

{{-- ==================== KEUANGAN ==================== --}}
<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Keuangan SPP</div>

<a href="{{ route('admin.fee.structures.index') }}" class="sidebar-link {{ $isActive('admin.fee.structures.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Struktur Biaya
</a>
<a href="{{ route('admin.fee.invoices.index') }}" class="sidebar-link {{ $isActive('admin.fee.invoices.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Invoice / Tagihan
</a>
<a href="{{ route('admin.finance.reports.summary') }}" class="sidebar-link {{ $isActive('admin.finance.reports.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Laporan Keuangan
</a>
<a href="{{ route('admin.payroll.structures.index') }}" class="sidebar-link {{ $isActive('admin.payroll.structures.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
    Komponen Gaji
</a>
<a href="{{ route('admin.payroll.slips.index') }}" class="sidebar-link {{ $isActive('admin.payroll.slips.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    Slip Gaji
</a>
<a href="{{ route('admin.payment.providers.index') }}" class="sidebar-link {{ $isActive('admin.payment.providers.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
    Provider Pembayaran
</a>
<a href="{{ route('admin.payment.methods.index') }}" class="sidebar-link {{ $isActive('admin.payment.methods.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    Metode Pembayaran
</a>
<a href="{{ route('admin.budget.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.budget.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
    Anggaran (RKAS)
</a>

<a href="{{ route('admin.procurement.index') }}" class="sidebar-link {{ request()->routeIs('admin.procurement.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
    Pengadaan
</a>
<a href="{{ route('admin.cooperative.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.cooperative.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    Koperasi
</a>

{{-- ==================== LAPORAN ==================== --}}
<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Laporan</div>

<a href="{{ route('admin.reports.spp-aging') }}" class="sidebar-link {{ request()->routeIs('admin.reports.spp-aging') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Aging SPP
</a>
<a href="{{ route('admin.reports.attendance-pct') }}" class="sidebar-link {{ request()->routeIs('admin.reports.attendance-pct') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
    % Kehadiran
</a>
<a href="{{ route('admin.reports.grade-distribution') }}" class="sidebar-link {{ request()->routeIs('admin.reports.grade-distribution') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
    Distribusi Nilai
</a>
<a href="{{ route('admin.reports.cash-flow') }}" class="sidebar-link {{ request()->routeIs('admin.reports.cash-flow') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
    Cash Flow
</a>
<a href="{{ route('admin.reports.builder.index') }}" class="sidebar-link {{ request()->routeIs('admin.reports.builder.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    Report Builder
</a>
<a href="{{ route('admin.foundation.benchmark.index') }}" class="sidebar-link {{ request()->routeIs('admin.foundation.benchmark.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    Benchmark Yayasan
</a>

{{-- ==================== KONTEN ==================== --}}
<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Konten & Blog</div>

<a href="{{ route('admin.blog.index') }}" class="sidebar-link {{ $isActive('admin.blog.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
    Blog
</a>

{{-- ==================== ADMINISTRASI ==================== --}}
<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Administrasi</div>

<a href="{{ route('admin.documents.index') }}" class="sidebar-link {{ request()->routeIs('admin.documents.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9l-5-5H7a2 2 0 00-2 2v12a2 2 0 002 2zm0-4h10M7 7h5v5"/></svg>
    Dokumen
</a>

<a href="{{ route('admin.letters.templates') }}" class="sidebar-link {{ $isActive('admin.letters.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Surat-Menyurat
</a>

<a href="{{ route('admin.notif.providers.index') }}" class="sidebar-link {{ $isActive('admin.notif.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
    Provider Notifikasi
</a>

<a href="{{ route('admin.exports.index') }}" class="sidebar-link {{ $isActive('admin.exports.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Ekspor Data
</a>

<a href="{{ route('admin.audit.index') }}" class="sidebar-link {{ $isActive('admin.audit.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Audit Log
</a>

{{-- ==================== TAMPILAN ==================== --}}
<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Tampilan</div>

<a href="{{ route('admin.branding.show') }}" class="sidebar-link {{ $isActive('admin.branding.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
    Branding & Logo
</a>

{{-- ==================== EVALUASI ==================== --}}
<div class="px-4 mt-4 mb-1 text-xs uppercase text-white/50 tracking-wider">Evaluasi</div>

<a href="{{ route('admin.surveys.templates.index') }}" class="sidebar-link {{ $isActive('admin.surveys.*') }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    Survei Kepuasan
</a>

<a href="{{ route('admin.signage.config') }}" class="sidebar-link {{ request()->routeIs('admin.signage.*') ? 'active' : '' }}">
    <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
    Digital Signage
</a>
