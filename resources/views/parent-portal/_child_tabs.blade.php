@php $current = request()->route()->getName(); @endphp
<nav class="bg-white border-b border-rule mb-6 -mx-4 sm:-mx-6 px-4 sm:px-6 sticky top-0 z-10">
<div class="flex gap-1 overflow-x-auto py-1">
@foreach([
    ['portal.child', 'Overview', '📊'],
    ['portal.child.attendance', 'Absensi', '📅'],
    ['portal.child.marks', 'Nilai', '✓'],
    ['portal.child.health', 'UKS', '🏥'],
    ['portal.child.discipline', 'Disiplin', '⚖'],
    ['portal.child.achievements', 'Prestasi', '🏆'],
    ['portal.child.counseling', 'Konseling', '💬'],
] as [$rt, $label, $icon])
<a href="{{ route($rt, $student) }}"
   class="px-4 py-2 elite-kicker text-[.65rem] whitespace-nowrap {{ $current === $rt ? 'border-b-2 border-[var(--c-accent)] ink-primary' : 'text-gray-500 hover:ink-primary' }}">
    {{ $icon }} {{ $label }}
</a>
@endforeach
</div>
</nav>
