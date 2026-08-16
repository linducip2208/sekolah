@extends('layouts.school-admin')
@section('title', 'Analisis Butir Soal')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.exams.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Daftar Ujian</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Item Analysis</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Analisis Butir Soal: {{ $exam->title }}</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">{{ $analysis['total_students'] }} siswa mengerjakan · {{ $analysis['total_questions'] }} soal · kelompok daya beda 27% ({{ $analysis['group_size'] }} siswa)</p>
</div>

@if($analysis['total_students'] === 0)
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">
        Belum ada siswa yang mengumpulkan jawaban ujian ini. Analisis butir soal hanya bisa dihitung setelah ada hasil ujian.
    </div>
@else
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @php $s = $analysis['summary']; @endphp
        <div class="bg-white border border-rule p-4">
            <div class="elite-kicker text-[.55rem] text-gray-500">Tingkat Kesulitan</div>
            <div class="mt-2 text-sm space-y-1">
                <div class="flex justify-between"><span class="text-red-700">Sulit</span><span class="font-mono">{{ $s['hard'] }}</span></div>
                <div class="flex justify-between"><span class="text-amber-700">Sedang</span><span class="font-mono">{{ $s['medium'] }}</span></div>
                <div class="flex justify-between"><span class="text-green-700">Mudah</span><span class="font-mono">{{ $s['easy'] }}</span></div>
            </div>
        </div>
        <div class="bg-white border border-rule p-4">
            <div class="elite-kicker text-[.55rem] text-gray-500">Daya Beda Baik (≥0.30)</div>
            <div class="font-display text-3xl ink-primary mt-2">{{ $s['good_discrimination'] }}</div>
        </div>
        <div class="bg-white border border-rule p-4">
            <div class="elite-kicker text-[.55rem] text-gray-500">Butir Perlu Revisi (&lt;0.20)</div>
            <div class="font-display text-3xl {{ $s['needs_revision'] > 0 ? 'text-red-700' : 'text-green-700' }} mt-2">{{ $s['needs_revision'] }}</div>
        </div>
        <div class="bg-white border border-rule p-4">
            <div class="elite-kicker text-[.55rem] text-gray-500">Skor Rata-rata Ujian</div>
            <div class="font-display text-3xl ink-primary mt-2">{{ $analysis['total_students'] ? number_format($exam->results()->whereIn('status',['passed','failed'])->avg('obtained_marks'), 1) : '—' }}</div>
        </div>
    </div>

    <div class="bg-white border border-rule overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem] w-10">No</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Soal</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Jawab</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Tingkat (p)</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Interpretasi</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Daya Beda (D)</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Distraktor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysis['questions'] as $i => $q)
                    <tr class="border-t border-rule hover:bg-gray-50 align-top">
                        <td class="px-3 py-3 text-center font-mono text-xs text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-serif font-semibold leading-snug">{!! Str::limit(strip_tags($q['question']), 80) !!}</div>
                            <div class="elite-kicker text-[.55rem] text-gray-400 mt-1">Kunci: {{ $q['correct_answer'] }} · {{ $q['answered'] }} menjawab</div>
                        </td>
                        <td class="px-3 py-3 text-center font-mono text-xs">{{ $q['correct'] }} benar</td>
                        <td class="px-3 py-3 text-center font-mono text-xs">{{ $q['difficulty'] ?? '—' }}</td>
                        <td class="px-3 py-3 text-center">
                            <span class="text-xs px-2 py-0.5 rounded @if($q['difficulty_tone']==='success') bg-green-100 text-green-800 @elseif($q['difficulty_tone']==='warning') bg-amber-100 text-amber-800 @elseif($q['difficulty_tone']==='danger') bg-red-100 text-red-800 @else bg-gray-100 text-gray-500 @endif">
                                {{ $q['difficulty_label'] }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center font-mono text-xs">{{ $q['discrimination'] ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($q['type'] === 'essay')
                                <span class="text-xs text-gray-400 italic">Tidak dinilai otomatis</span>
                            @else
                                <div class="space-y-1">
                                    @foreach($q['distractors'] as $d)
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 font-mono text-xs {{ $d['is_correct'] ? 'text-green-700 font-bold' : 'text-gray-500' }}">{{ $d['answer'] }}{{ $d['is_correct'] ? ' ✓' : '' }}</span>
                                            <div class="flex-1 h-2 bg-gray-100 rounded overflow-hidden">
                                                <div class="h-full {{ $d['is_correct'] ? 'bg-green-500' : 'bg-gray-400' }}" style="width:{{ max($d['percentage'], 4) }}%"></div>
                                            </div>
                                            <span class="w-12 text-right font-mono text-xs text-gray-500">{{ $d['count'] }} ({{ $d['percentage'] }}%)</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@endsection
