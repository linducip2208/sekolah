@extends('layouts.school-admin')
@section('title', 'Lesson Study')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Studium Collaborativum</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Lesson Study</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">Pengembangan pengajaran kolaboratif — Plan, Do, See.</p>
        </div>
        <a href="{{ route('admin.lesson-study.create') }}" class="btn-elite">+ Studi Baru</a>
    </div>
</div>

<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="elite-card p-4 text-center">
        <div class="font-display text-3xl ink-accent">{{ $stats['totalStudies'] }}</div>
        <div class="elite-kicker text-[.6rem] mt-1">Total Studi</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="font-display text-3xl ink-accent">{{ $stats['totalObservations'] }}</div>
        <div class="elite-kicker text-[.6rem] mt-1">Observasi</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="font-display text-3xl ink-accent">{{ $stats['totalReflections'] }}</div>
        <div class="elite-kicker text-[.6rem] mt-1">Refleksi</div>
    </div>
</div>

@if(isset($showDetail) && isset($detailStudy))
<div class="bg-white border border-rule p-6 mb-6">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h2 class="elite-h2 text-2xl ink-primary">{{ $detailStudy->title }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                Fase: <strong>{{ $detailStudy->phase }}</strong> · Status: <strong>{{ $detailStudy->status }}</strong>
                · Lead: {{ $detailStudy->leadTeacher->name ?? '—' }}
            </p>
        </div>
        <div class="flex gap-2">
            @if($detailStudy->phase !== 'see')
            <form method="POST" action="{{ route('admin.lesson-study.advance-phase', $detailStudy) }}">
                @csrf
                <button class="btn-elite text-xs">Lanjut Fase →</button>
            </form>
            @endif
            <a href="{{ route('admin.lesson-study.report-pdf', $detailStudy) }}" class="btn-elite-ghost text-xs">PDF</a>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-4">
        <div>
            <h3 class="elite-h3 text-sm ink-primary mb-2">Anggota</h3>
            <ul class="space-y-1">
                @foreach($detailStudy->members as $m)
                    <li class="text-sm font-serif">{{ $m->staff->name ?? '—' }} <span class="text-xs text-gray-400">({{ $m->role }})</span></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h3 class="elite-h3 text-sm ink-primary mb-2">Observasi</h3>
            @foreach($observationSummary as $type => $data)
                <div class="mb-2 p-2 border border-rule text-xs">
                    <div class="font-semibold">{{ $data['label'] }}: {{ $data['avg_rating'] ?? '—' }}/5 ({{ $data['count'] }} obs)</div>
                </div>
            @endforeach
        </div>
        <div>
            <h3 class="elite-h3 text-sm ink-primary mb-2">Refleksi ({{ $reflectionSummary['count'] }})</h3>
            @foreach($reflectionSummary['strengths'] as $s)
                <div class="text-xs text-green-700 mb-1">✓ {{ Str::limit($s, 100) }}</div>
            @endforeach
            @foreach($recommendations as $r)
                <div class="text-xs text-blue-700 mb-1">→ {{ $r }}</div>
            @endforeach
        </div>
    </div>
    <a href="{{ route('admin.lesson-study.index') }}" class="text-xs ink-accent hover:underline mt-3 inline-block">← Tutup detail</a>
</div>
@endif

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($studies as $study)
        @php
            $phaseColors = ['plan'=>'border-blue-400','do'=>'border-green-400','see'=>'border-amber-400'];
            $phaseLabels = ['plan'=>'Plan','do'=>'Do','see'=>'See'];
            $statusLabels = ['draft'=>'Draft','planned'=>'Terencana','observed'=>'Diobservasi','reflected'=>'Direfleksikan','completed'=>'Selesai'];
            $borderColor = $phaseColors[$study->phase] ?? 'border-gray-300';
        @endphp
        <a href="{{ route('admin.lesson-study.show', $study) }}" class="elite-card p-5 block group border-l-4 {{ $borderColor }}">
            <div class="flex justify-between items-start mb-2">
                <h3 class="elite-h3 text-base ink-primary leading-tight group-hover:ink-accent transition">{{ $study->title }}</h3>
            </div>
            <div class="flex flex-wrap gap-1 mb-3">
                <span class="text-[.55rem] px-1.5 py-0.5 bg-gray-100 text-gray-700 rounded">{{ $phaseLabels[$study->phase] ?? $study->phase }}</span>
                <span class="text-[.55rem] px-1.5 py-0.5 bg-gray-100 text-gray-700 rounded">{{ $statusLabels[$study->status] ?? $study->status }}</span>
            </div>
            <p class="font-serif text-xs text-gray-600 mb-3">{{ $study->subject->name ?? 'Tanpa mapel' }} · {{ optional($study->classSection)->classRoom->name ?? '' }} {{ optional($study->classSection)->section->name ?? '' }}</p>
            <div class="flex justify-between text-[.6rem] text-gray-400">
                <span>Lead: {{ $study->leadTeacher->name ?? '—' }}</span>
                <span>{{ $study->members_count }} anggota · {{ $study->observations_count }} obs · {{ $study->reflections_count }} ref</span>
            </div>
            <div class="w-full bg-gray-200 h-1.5 rounded mt-3">
                @php
                    $widths = ['draft'=>'10%','planned'=>'30%','observed'=>'55%','reflected'=>'80%','completed'=>'100%'];
                @endphp
                <div class="h-1.5 rounded transition-all" style="width:{{ $widths[$study->status] ?? '10%' }};background:var(--c-accent);"></div>
            </div>
        </a>
    @empty
        <div class="col-span-full p-10 text-center text-gray-500 italic font-serif">Belum ada Lesson Study. Buat studi kolaboratif pertama.</div>
    @endforelse
</div>
<div class="mt-6">{{ $studies->links() }}</div>

@endsection
