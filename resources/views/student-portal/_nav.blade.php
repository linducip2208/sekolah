@php $current = request()->route()->getName(); @endphp
<nav class="bg-white border-b border-rule mb-6 -mx-4 sm:-mx-6 px-4 sm:px-6 sticky top-0 z-10">
<div class="flex gap-1 overflow-x-auto py-1">
@foreach([
    ['student.dashboard', 'Beranda', '🏠'],
    ['student.schedule', 'Jadwal', '📅'],
    ['student.marks', 'Nilai', '✓'],
    ['student.attendance', 'Absensi', '📋'],
    ['student.lessons', 'Materi', '📖'],
    ['student.assignments', 'Tugas', '✍'],
    ['student.exams.index', 'Ujian', '📝'],
    ['student.quizzes.index', 'Kuis', '🎯'],
    ['student.leaderboard', 'Leaderboard', '🏆'],
    ['student.surveys', 'Survei', '📝'],
    ['student.portfolios', 'Portofolio', '🖼️'],
] as [$rt, $label, $icon])
<a href="{{ route($rt) }}" class="px-4 py-2.5 text-sm font-medium whitespace-nowrap {{ $current === $rt ? 'border-b-2 border-[var(--c-accent)] text-[var(--color-text)]' : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text)]' }}">
{{ $icon }} {{ $label }}
</a>
@endforeach
</div>
</nav>
