@php $current = request()->route()->getName(); @endphp
@php
    // Ikon SVG konsisten (satu family dengan admin) — bukan emoji.
    $navItems = [
        ['student.dashboard', 'Beranda', 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['student.schedule', 'Jadwal', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['student.marks', 'Nilai', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ['student.attendance', 'Absensi', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
        ['student.lessons', 'Materi', 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
        ['student.assignments', 'Tugas', 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
        ['student.exams.index', 'Ujian', 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z'],
        ['student.quizzes.index', 'Kuis', 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['student.leaderboard', 'Peringkat', 'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['student.surveys', 'Survei', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
        ['student.portfolios', 'Portofolio', 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
    ];
@endphp
<nav class="border-b mb-6 sticky top-0 z-10" style="background: var(--color-surface); border-color: var(--color-border);" aria-label="Navigasi portal siswa">
    <div class="flex gap-0.5 overflow-x-auto px-1" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
        @foreach($navItems as [$rt, $label, $path])
            <a href="{{ route($rt) }}" aria-current="{{ $current === $rt ? 'page' : false }}"
               class="relative inline-flex items-center gap-1.5 whitespace-nowrap px-3.5 py-3 text-sm font-medium transition-colors"
               style="{{ $current === $rt ? 'color: var(--color-primary);' : 'color: var(--color-text-secondary);' }}">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $path }}"/></svg>
                {{ $label }}
                @if($current === $rt)
                    <span class="absolute inset-x-2 bottom-0 h-[2.5px] rounded-t-full" style="background: var(--color-primary);" aria-hidden="true"></span>
                @endif
            </a>
        @endforeach
    </div>
</nav>
