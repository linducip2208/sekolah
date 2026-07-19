@extends('layouts.parent')
@section('title', 'Pemilihan OSIS')
@section('content')
@include('student-portal._nav')

<div class="mb-7">
    <div class="elite-kicker mb-2">Partisipasi Sekolah</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Pemilihan OSIS</h1>
    <div class="elite-rule"></div>
</div>

@if($activeElection)
<div class="bg-white border-l-4 border-blue-600 p-5 mb-7">
    <div class="elite-kicker text-[.6rem]">Pemilihan Sedang Berlangsung</div>
    <div class="font-display text-xl ink-primary mt-1">{{ $activeElection->title }}</div>
    <div class="text-xs text-gray-500 mt-1">
        Voting berakhir: {{ $activeElection->voting_end?->format('d M Y H:i') }}
    </div>
</div>

@if($hasVoted)
<div class="bg-green-50 border border-green-300 p-5 mb-7 text-center">
    <div class="font-display text-xl text-green-700 mb-1">✓ Suara Anda Sudah Tercatat</div>
    <p class="text-sm text-green-600">Terima kasih sudah berpartisipasi!</p>
    <a href="{{ route('student.osis.results', $activeElection->id) }}" class="inline-block mt-3 text-sm text-blue-600 hover:underline">Lihat Hasil →</a>
</div>
@else
<form method="POST" action="{{ route('student.osis.vote', $activeElection) }}">
    @csrf
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5 mb-7">
        @foreach($activeElection->candidates as $c)
        @if($c->status === 'approved')
        <div class="bg-white border border-rule p-5 hover:shadow-lg transition cursor-pointer candidate-card"
             onclick="this.querySelector('input').checked = !this.querySelector('input').checked; this.classList.toggle('ring-2'); this.classList.toggle('ring-black');">
            <input type="checkbox" name="candidate_ids[]" value="{{ $c->id }}" class="hidden">
            <div class="text-center mb-3">
                @if($c->photo_path)
                <img src="{{ asset('storage/' . $c->photo_path) }}" class="w-16 h-16 object-cover rounded-full mx-auto mb-2">
                @else
                <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center font-display text-2xl mx-auto mb-2">
                    {{ strtoupper(substr($c->student?->user?->name ?? '?', 0, 1)) }}
                </div>
                @endif
                <div class="font-serif font-semibold ink-primary">{{ $c->student?->user?->name }}</div>
                <div class="elite-kicker text-[.6rem]">{{ $c->position }}</div>
            </div>
            @if($c->vision)
            <div class="text-xs text-gray-600 mt-2">
                <span class="font-semibold">Visi:</span> {{ Str::limit($c->vision, 100) }}
            </div>
            @endif
            @if($c->mission)
            <div class="text-xs text-gray-600 mt-1">
                <span class="font-semibold">Misi:</span> {{ Str::limit($c->mission, 100) }}
            </div>
            @endif
        </div>
        @endif
        @endforeach
    </div>

    @if($activeElection->candidates->where('status', 'approved')->count() > 0)
    <div class="text-center">
        <button type="submit" class="btn-elite-gold text-lg px-12 py-3"
                onclick="return confirm('Yakin dengan pilihan Anda? Suara TIDAK bisa diubah.')">
            ✋ KIRIM SUARA SAYA
        </button>
    </div>
    @endif
</form>
@endif

@else
{{-- No active election — show history --}}
@if($recentElections->isNotEmpty())
<h3 class="elite-h3 text-lg ink-primary mb-4">Riwayat Pemilihan</h3>
@foreach($recentElections as $el)
<div class="bg-white border border-rule p-5 mb-3">
    <div class="flex justify-between items-center">
        <div>
            <div class="font-serif font-semibold ink-primary">{{ $el->title }}</div>
            <div class="text-xs text-gray-500 mt-1">Status: {{ $el->status === 'completed' ? 'Selesai' : 'Berlangsung' }}</div>
        </div>
        <a href="{{ route('student.osis.results', $el->id) }}" class="text-sm text-blue-600 hover:underline">Lihat Hasil →</a>
    </div>
</div>
@endforeach
@else
<div class="bg-white border border-rule p-12 text-center">
    <div class="text-6xl mb-4">🗳️</div>
    <p class="font-serif text-gray-500 italic">Belum ada pemilihan OSIS yang aktif.</p>
</div>
@endif
@endif
@endsection
