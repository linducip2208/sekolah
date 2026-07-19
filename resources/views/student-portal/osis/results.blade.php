@extends('layouts.parent')
@section('title', 'Hasil Pemilihan OSIS')
@section('content')
@include('student-portal._nav')

<div class="mb-7">
    <div class="elite-kicker mb-2">Partisipasi Sekolah</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Hasil Pemilihan OSIS</h1>
    <p class="font-serif text-lg ink-primary">{{ $election->title }}</p>
    <div class="elite-rule mt-2"></div>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-7">
    <div class="bg-white border-l-4 border-purple-600 p-4">
        <div class="elite-kicker text-[.6rem]">Total Pemilih</div>
        <div class="font-display text-2xl ink-primary mt-1">{{ $totalVoters }}</div>
    </div>
    <div class="bg-white border-l-4 border-green-600 p-4">
        <div class="elite-kicker text-[.6rem]">Status</div>
        <div class="font-display text-lg ink-primary mt-1">{{ $election->status === 'completed' ? 'Selesai' : 'Berlangsung' }}</div>
    </div>
    <div class="bg-white border-l-4 border-blue-600 p-4">
        <div class="elite-kicker text-[.6rem]">Status Anda</div>
        <div class="font-display text-lg {{ $hasVoted ? 'text-green-600' : 'text-gray-500' }} mt-1">
            {{ $hasVoted ? 'Sudah Memilih' : 'Belum Memilih' }}
        </div>
    </div>
</div>

@if(count($winners) > 0 && $election->status === 'completed')
<div class="bg-white border-l-4 border-yellow-500 p-7 mb-7" style="background:linear-gradient(135deg,#fff9e6,#fff);">
    <h3 class="elite-h3 text-lg ink-primary mb-4">🏆 Pemenang</h3>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($winners as $w)
        <div class="border border-rule p-5 text-center bg-white">
            <div class="crest-mark mx-auto mb-3 lg">
                <span class="font-display text-lg">{{ strtoupper(substr($w['candidate']->student?->user?->name ?? '?', 0, 1)) }}</span>
            </div>
            <div class="font-serif font-semibold ink-primary">{{ $w['candidate']->student?->user?->name }}</div>
            <div class="elite-kicker text-[.6rem] mt-1">{{ $w['position'] }}</div>
            <div class="font-display text-3xl ink-accent mt-2">{{ $w['vote_count'] }} <span class="text-sm text-gray-500">suara</span></div>
        </div>
        @endforeach
    </div>
</div>
@endif

<h3 class="elite-h3 text-lg ink-primary mb-4">Perolehan Suara</h3>
<div class="space-y-3">
    @foreach($election->candidates as $c)
    <div class="bg-white border border-rule p-4">
        <div class="flex justify-between items-center">
            <div>
                <span class="font-serif font-semibold">{{ $c->student?->user?->name }}</span>
                <span class="elite-kicker text-[.55rem] ml-2">{{ $c->position }}</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="h-4 bg-gray-200 min-w-[120px]">
                    @php
                    $maxVotes = $election->candidates->max('vote_count') ?: 1;
                    $barWidth = $maxVotes > 0 ? ($c->vote_count / $maxVotes * 100) : 0;
                    @endphp
                    <div class="h-4 bg-blue-600" style="width: {{ $barWidth }}%"></div>
                </div>
                <span class="font-mono font-semibold w-8 text-right">{{ $c->vote_count }}</span>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
