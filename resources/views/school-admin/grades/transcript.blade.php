@extends('layouts.school-admin')
@section('title', 'Transkrip Nilai')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Transcriptum</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Transkrip Nilai</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Rekap nilai lintas semester per siswa.</p>
</div>

<form method="GET" class="bg-white border border-rule p-4 mb-6 flex gap-2 items-center">
    <select name="student_id" required class="flex-1 border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— pilih siswa —</option>
        @foreach($students as $s)<option value="{{ $s->id }}" @selected(request('student_id') == $s->id)>{{ $s->admission_no }} · {{ $s->user?->name }}</option>@endforeach
    </select>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.7rem;">Lihat</button>
</form>

@if($selected)
    <div class="bg-white border border-rule p-6 mb-6 no-print">
        <div class="grid sm:grid-cols-3 gap-4">
            <div>
                <div class="elite-kicker text-[.55rem] text-gray-500">Nama</div>
                <div class="font-serif text-lg ink-primary">{{ $selected->user?->name }}</div>
                <div class="text-xs text-gray-500">{{ $selected->classSection?->classRoom?->name }} {{ $selected->classSection?->section?->name }}</div>
            </div>
            <div>
                <div class="elite-kicker text-[.55rem] text-gray-500">NIS</div>
                <div class="font-mono text-lg">{{ $selected->admission_no }}</div>
            </div>
            <div>
                <div class="elite-kicker text-[.55rem] text-gray-500">Rata-rata Kumulatif</div>
                <div class="font-display text-2xl ink-primary">{{ $cumulative['avg_pct'] }}% · GPA {{ $cumulative['gpa'] }}</div>
            </div>
        </div>
        <div class="mt-4 text-right"><button onclick="window.print()" class="btn-elite" style="padding:.4rem 1rem;font-size:.65rem;">Cetak</button></div>
    </div>

    <div class="bg-white border border-rule overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white"><tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Semester</th>
                <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Rata-rata</th>
                <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Grade</th>
                <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">GPA</th>
                <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Peringkat</th>
                <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
            </tr></thead>
            <tbody>
                @forelse($cards as $card)
                <tr class="border-t border-rule">
                    <td class="px-4 py-3 font-serif">{{ $card->semester?->name }}</td>
                    <td class="px-4 py-3 text-center font-mono text-xs">{{ $card->total_percentage }}%</td>
                    <td class="px-4 py-3 text-center font-display text-lg ink-primary">{{ $card->overall_grade ?? '—' }}</td>
                    <td class="px-4 py-3 text-center font-mono text-xs">{{ $card->gpa ?? '—' }}</td>
                    <td class="px-4 py-3 text-center font-mono text-xs">{{ $card->rank ?? '—' }}</td>
                    <td class="px-4 py-3 text-center"><span class="text-xs text-gray-500">{{ ucfirst($card->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada rapor untuk siswa ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@else
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Pilih siswa untuk melihat transkrip.</div>
@endif

@endsection
