@extends('layouts.parent')
@section('title', 'Ujian Online')
@section('content')
@include('student-portal._nav')

<div class="mb-7">
<div class="elite-kicker mb-2">CBT</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Ujian Online</h1>
<div class="elite-rule"></div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<div class="space-y-3">
@forelse($exams as $e)
    @php
        $r = $results->get($e->id);
        $started = $e->start_at && now()->isBefore($e->start_at);
        $ended = $e->end_at && now()->isAfter($e->end_at);
        $done = $r && in_array($r->status, ['passed','failed']);
    @endphp
    <div class="bg-white border border-rule p-5 flex items-center justify-between gap-4">
        <div>
            <div class="font-serif font-semibold text-lg ink-primary">{{ $e->title }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $e->subject?->name }} · {{ $e->total_marks }} poin · lulus {{ $e->pass_marks }}</div>
            <div class="text-xs text-gray-400 mt-1">
                @if($e->start_at)Mulai: {{ $e->start_at->format('d M Y H:i') }}@endif
                @if($e->end_at)· Selesai: {{ $e->end_at->format('d M Y H:i') }}@endif
                @if($e->duration_minutes)· Durasi: {{ $e->duration_minutes }} menit @endif
            </div>
        </div>
        <div class="text-right shrink-0">
            @if($done)
                <span class="text-xs px-2 py-1 rounded {{ $r->status === 'passed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $r->status === 'passed' ? 'Lulus' : 'Tidak Lulus' }} ({{ $r->obtained_marks }})</span>
                <a href="{{ route('student.exams.result', $e) }}" class="block mt-2 text-xs underline ink-secondary">Lihat Hasil</a>
            @elseif($started)
                <span class="text-xs text-gray-400">Belum dimulai</span>
            @elseif($ended && !$done)
                <span class="text-xs text-gray-400">Sudah berakhir</span>
            @else
                <a href="{{ route('student.exams.take', $e) }}" class="btn-elite" style="padding:.5rem 1.2rem;font-size:.7rem;">Kerjakan</a>
            @endif
        </div>
    </div>
@empty
    <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada ujian online.</div>
@endforelse
</div>
@endsection
