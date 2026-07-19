@extends('layouts.parent')
@section('title', 'Leaderboard — ' . $periodLabel)
@section('content')
@include('student-portal._nav')

<div class="mb-7">
    <div class="elite-kicker mb-2">Honoris Discipulorum</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Leaderboard {{ $periodLabel }}</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">{{ $student->classSection?->classRoom?->name }} {{ $student->classSection?->section?->name }} · {{ count($rankings) }} siswa dalam peringkat</p>
</div>

{{-- Period Switcher --}}
<div class="flex gap-2 mb-6">
    @foreach(['weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'semester' => 'Semester'] as $key => $label)
    <a href="?period={{ $key }}" class="px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $configType === $key ? 'bg-[var(--c-primary)] text-white' : 'bg-white border border-rule text-gray-600' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- My Rank Card --}}
@if($myRanking)
<div class="elite-card p-6 mb-8 border-l-4" style="border-color: var(--c-accent);">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <div class="elite-kicker text-[.6rem]">Posisi Kamu</div>
            <div class="flex items-baseline gap-3 mt-1">
                <span class="font-display text-5xl ink-primary">{{ $myRanking['rank'] }}</span>
                <span class="text-lg text-gray-500">dari {{ count($rankings) }} siswa</span>
            </div>
        </div>
        <div class="text-right">
            <div class="font-display text-4xl ink-accent">{{ (int)$myRanking['weighted_score'] }}</div>
            <div class="elite-kicker text-[.55rem]">Total Poin</div>
        </div>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 pt-4 border-t border-rule">
        <div class="text-center">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Akademik</div>
            <div class="font-mono font-bold text-lg ink-primary">{{ (int)$myRanking['academic_points'] }}</div>
        </div>
        <div class="text-center">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Absensi</div>
            <div class="font-mono font-bold text-lg ink-primary">{{ (int)$myRanking['attendance_points'] }}</div>
        </div>
        <div class="text-center">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Ekskul</div>
            <div class="font-mono font-bold text-lg ink-primary">{{ (int)$myRanking['extracurricular_points'] }}</div>
        </div>
        <div class="text-center">
            <div class="text-xs text-gray-500 uppercase tracking-wide">Disiplin</div>
            <div class="font-mono font-bold text-lg ink-primary">{{ (int)$myRanking['discipline_points'] }}</div>
        </div>
    </div>
</div>
@else
<div class="elite-card p-6 mb-8 text-center">
    <div class="font-serif text-lg text-gray-500 italic">Kamu belum masuk dalam peringkat {{ strtolower($periodLabel) }}.</div>
    <p class="text-xs text-gray-400 mt-1">Kumpulkan poin melalui prestasi akademik, kehadiran, ekstrakurikuler, dan disiplin.</p>
</div>
@endif

{{-- Podium Top 3 --}}
@if(count($top3) >= 3)
<div class="elite-card p-6 mb-8">
    <div class="text-center mb-4">
        <div class="ornament-center mb-1"></div>
        <div class="elite-h2 text-xl ink-primary">Top 3 {{ $periodLabel }}</div>
    </div>
    <div class="flex items-end justify-center gap-2 sm:gap-4" style="min-height:200px;">
        @php $ordered = [$top3[1] ?? null, $top3[0] ?? null, $top3[2] ?? null]; @endphp
        @foreach($ordered as $idx => $entry)
            @if(!$entry) @continue @endif
            @php $pos = $idx === 0 ? 2 : ($idx === 1 ? 1 : 3);
                $heights = [1 => '180px', 2 => '140px', 3 => '100px'];
                $gradients = [1 => 'linear-gradient(180deg, #FFD700, #B8860B)', 2 => 'linear-gradient(180deg, #C0C0C0, #808080)', 3 => 'linear-gradient(180deg, #CD7F32, #8B4513)'];
            @endphp
            <div style="display:flex;flex-direction:column;align-items:center;width:100px;">
                <div style="width:100%;height:{{ $heights[$pos] }};background:{{ $gradients[$pos] }};border-radius:4px 4px 0 0;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding-top:.75rem;">
                    <div class="font-display text-3xl font-extrabold" style="color:rgba(255,255,255,.85);text-shadow:0 1px 4px rgba(0,0,0,.2);">{{ $pos }}</div>
                    <div class="text-xs font-semibold mt-1 text-center" style="color:rgba(255,255,255,.9);">{{ \Illuminate\Support\Str::limit($entry['student_name'], 12) }}</div>
                    <div class="text-[.6rem] mt-.5" style="color:rgba(255,255,255,.6);">{{ (int)$entry['weighted_score'] }} poin</div>
                </div>
                <div style="font-size:.55rem;letter-spacing:.15em;text-transform:uppercase;margin-top:.35rem;font-weight:700;color:var(--c-accent);">{{ $pos === 1 ? 'JUARA 1' : ($pos === 2 ? 'JUARA 2' : 'JUARA 3') }}</div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Full Rankings --}}
@if(count($remaining) > 0)
<div class="elite-card overflow-hidden mb-5">
    <div class="px-5 py-3 border-b border-rule">
        <h3 class="elite-h3 text-lg ink-primary">Klasemen Lengkap</h3>
    </div>
    <div class="table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-center px-3 py-2 elite-kicker text-[.55rem]">#</th>
                    <th class="text-left px-3 py-2 elite-kicker text-[.55rem]">Nama</th>
                    <th class="text-right px-3 py-2 elite-kicker text-[.55rem]">Skor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($remaining as $entry)
                <tr class="border-t border-rule {{ $entry['student_id'] === $student->id ? 'bg-yellow-50' : '' }}">
                    <td class="px-3 py-2 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 text-xs font-display font-bold {{ $entry['rank'] <= 5 ? 'bg-[var(--c-accent)] text-white' : 'bg-gray-200 text-gray-600' }}">
                            {{ $entry['rank'] }}
                        </span>
                    </td>
                    <td class="px-3 py-2 font-serif font-semibold {{ $entry['student_id'] === $student->id ? 'ink-accent' : '' }}">
                        {{ $entry['student_name'] }}
                        @if($entry['student_id'] === $student->id) <span class="text-[.55rem] uppercase tracking-wide text-gray-400">(Kamu)</span> @endif
                    </td>
                    <td class="px-3 py-2 text-right font-mono font-bold">{{ (int)$entry['weighted_score'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="text-center mt-8 pt-6 border-t border-rule">
    <p class="font-script italic text-sm text-gray-500">"Keunggulan diraih melalui disiplin dan semangat belajar yang tak kenal lelah."</p>
</div>
@endsection
