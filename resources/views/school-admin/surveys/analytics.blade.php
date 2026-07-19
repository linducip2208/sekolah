@extends('layouts.school-admin')
@section('title', 'Analitik Survei — ' . $template->title)
@section('sidebar')@include('school-admin.partials.sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush
@section('content')

<div class="mb-7">
    <a href="{{ route('admin.surveys.templates.index') }}" class="text-xs ink-secondary hover:ink-accent">← Kembali ke Template</a>
    <div class="elite-kicker mb-2 mt-2">Analysis Numerorum</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $template->title }}</h1>
    <p class="text-sm text-gray-600 font-serif mb-1">Total: {{ $totalResponses }} respons</p>
    <div class="elite-rule"></div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="elite-card p-4 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Total Respons</div>
        <div class="font-display text-3xl font-bold ink-primary">{{ $totalResponses }}</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Rata-Rata Rating</div>
        <div class="font-display text-3xl font-bold ink-accent">
            @php
                $allRatings = collect($questionAnalytics)->where('type', 'rating_1_5')->pluck('avg_rating')->filter();
                $overallAvg = $allRatings->count() > 0 ? round($allRatings->avg(), 2) : '-';
            @endphp
            {{ $overallAvg }}
        </div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Jumlah Pertanyaan</div>
        <div class="font-display text-3xl font-bold ink-primary">{{ count($questionAnalytics) }}</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="elite-kicker text-[.55rem] mb-1">Responden Unik</div>
        <div class="font-display text-3xl font-bold ink-accent">{{ $totalResponses }}</div>
    </div>
</div>

{{-- Per Question Analysis --}}
<div class="elite-card overflow-hidden mb-6">
    <div class="px-4 py-3 bg-gray-50 border-b border-rule">
        <h3 class="elite-h3 text-base ink-primary">Analisis per Pertanyaan</h3>
    </div>
    <div class="table-scroll">
        <table class="w-full text-sm table-elite">
            <thead>
                <tr>
                    <th class="text-left px-4 py-3">Pertanyaan</th>
                    <th class="text-center px-4 py-3">Tipe</th>
                    <th class="text-center px-4 py-3">Respons</th>
                    <th class="text-center px-4 py-3">Rata-Rata</th>
                    <th class="text-left px-4 py-3">Distribusi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($questionAnalytics as $qa)
                    <tr class="border-t border-rule">
                        <td class="px-4 py-3 font-serif text-sm">{{ $qa['question'] }}</td>
                        <td class="px-4 py-3 text-center text-xs">
                            @if($qa['type'] === 'rating_1_5')
                                <span class="px-2 py-1 rounded bg-yellow-50 text-yellow-800 font-semibold">Rating 1-5</span>
                            @elseif($qa['type'] === 'text')
                                <span class="px-2 py-1 rounded bg-gray-100 text-gray-800 font-semibold">Text</span>
                            @else
                                <span class="px-2 py-1 rounded bg-indigo-50 text-indigo-800 font-semibold">Pilihan Ganda</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-semibold">{{ $qa['response_count'] }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($qa['avg_rating'] !== null)
                                <span class="font-display font-bold text-lg ink-accent">{{ $qa['avg_rating'] }}</span>
                                <span class="text-xs text-gray-500">/5</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($qa['type'] === 'rating_1_5' && !empty($qa['distribution']))
                                <div class="flex items-center gap-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        @php $cnt = $qa['distribution'][$i] ?? 0; @endphp
                                        <div class="flex-1">
                                            <div class="text-center text-[10px] font-semibold text-gray-500 mb-1">{{ $cnt }}</div>
                                            <div class="bg-gray-200 rounded-full h-2">
                                                <div class="bg-yellow-500 rounded-full h-2" style="width: {{ $qa['response_count'] > 0 ? ($cnt / $qa['response_count'] * 100) : 0 }}%"></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            @elseif($qa['type'] === 'text' && !empty($qa['text_answers']))
                                <div x-data="{ open: false }" class="text-xs">
                                    <button @click="open = !open" class="underline ink-secondary hover:ink-accent">Lihat {{ count($qa['text_answers']) }} jawaban</button>
                                    <div x-show="open" x-cloak class="mt-2 space-y-1 max-h-40 overflow-y-auto">
                                        @foreach($qa['text_answers'] as $text)
                                            <p class="text-gray-600 italic border-l-2 border-gray-200 pl-2 text-[11px]">"{{ $text }}"</p>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Per Target Analysis (for teacher/staff surveys) --}}
@if(!empty($targetAnalytics))
<div class="elite-card overflow-hidden mb-6">
    <div class="px-4 py-3 bg-gray-50 border-b border-rule">
        <h3 class="elite-h3 text-base ink-primary">Peringkat Evaluasi</h3>
    </div>
    <div class="table-scroll">
        <table class="w-full text-sm table-elite">
            <thead>
                <tr>
                    <th class="text-center px-4 py-3">Peringkat</th>
                    <th class="text-left px-4 py-3">Nama</th>
                    <th class="text-center px-4 py-3">Jumlah Respons</th>
                    <th class="text-center px-4 py-3">Rata-Rata</th>
                </tr>
            </thead>
            <tbody>
                @foreach($targetAnalytics as $idx => $ta)
                    <tr class="border-t border-rule">
                        <td class="px-4 py-3 text-center">
                            @if($idx === 0) <span class="text-lg">🥇</span>
                            @elseif($idx === 1) <span class="text-lg">🥈</span>
                            @elseif($idx === 2) <span class="text-lg">🥉</span>
                            @else <span class="text-xs text-gray-500">{{ $idx + 1 }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $ta['name'] }}</td>
                        <td class="px-4 py-3 text-center">{{ $ta['count'] }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($ta['avg'] !== null)
                                <span class="font-display font-bold text-lg ink-accent">{{ $ta['avg'] }}</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Chart: Average rating per question --}}
@php
    $chartLabels = collect($questionAnalytics)->where('avg_rating', '!=', null)->pluck('question')->map(fn($t) => \Illuminate\Support\Str::limit($t, 40))->toArray();
    $chartValues = collect($questionAnalytics)->where('avg_rating', '!=', null)->pluck('avg_rating')->toArray();
@endphp
@if(count($chartLabels) > 0)
<div class="elite-card p-6">
    <h3 class="elite-h3 text-base ink-primary mb-4">Grafik Rata-Rata per Pertanyaan</h3>
    <canvas id="perQuestionChart" height="80"></canvas>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(count($chartLabels) > 0)
    new Chart(document.getElementById('perQuestionChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Rata-Rata Rating',
                data: {!! json_encode($chartValues) !!},
                backgroundColor: 'rgba(184, 134, 11, 0.7)',
                borderColor: '#b8860b',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    ticks: { stepSize: 1 },
                }
            },
            plugins: {
                legend: { display: false },
            }
        }
    });
    @endif
});
</script>
@endpush

@stop
