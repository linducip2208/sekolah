@extends('layouts.parent')
@section('title', 'Komite Sekolah')
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Partisipasi Orang Tua</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Komite Sekolah</h1>
    <div class="elite-rule"></div>
</div>

{{-- Upcoming Meetings --}}
<h3 class="elite-h3 text-lg ink-primary mb-4">📅 Rapat Mendatang</h3>
@forelse($upcomingMeetings as $m)
<div class="bg-white border border-rule p-5 mb-3">
    <div class="font-serif font-semibold ink-primary">{{ $m->title }}</div>
    <div class="text-sm text-gray-500 mt-1">
        📅 {{ $m->meeting_date->format('d M Y H:i') }} · 📍 {{ $m->location ?? '—' }}
    </div>
    @if($m->agenda)
    <div class="mt-2 text-sm text-gray-600">{{ Str::limit($m->agenda, 200) }}</div>
    @endif
</div>
@empty
<p class="font-serif text-gray-500 italic mb-7">Tidak ada rapat mendatang.</p>
@endforelse

{{-- Recent Meetings with Minutes --}}
<h3 class="elite-h3 text-lg ink-primary mb-4">📋 Rapat Terkini & Notulen</h3>
@forelse($recentMeetings as $m)
<div class="bg-white border border-rule p-5 mb-4">
    <div class="font-serif font-semibold ink-primary">{{ $m->title }}</div>
    <div class="text-xs text-gray-500 mt-1">{{ $m->meeting_date->format('d M Y H:i') }}</div>

    @if($m->minutes)
    <div class="mt-3 p-3 bg-gray-50 border border-rule text-sm">
        <div class="elite-kicker text-[.6rem] mb-1">Notulen</div>
        <div class="whitespace-pre-wrap">{{ $m->minutes }}</div>
    </div>
    @endif

    @if($m->decisions->isNotEmpty())
    <div class="mt-3">
        <div class="elite-kicker text-[.6rem] mb-2">Keputusan Rapat</div>
        @foreach($m->decisions as $d)
        <div class="flex items-center gap-2 p-2 border-b border-rule last:border-0">
            <span class="text-xs px-2 py-0.5 {{ $d->voting_result === 'approved' ? 'bg-green-100 text-green-700' : 'bg-gray-100' }}">
                {{ $d->voting_result === 'approved' ? '✓' : ($d->voting_result === 'rejected' ? '✗' : '—') }}
            </span>
            <div>
                <span class="text-sm font-semibold">{{ $d->title }}</span>
                <span class="text-xs text-gray-500 ml-2">{{ ucfirst($d->decision_type) }}</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($m->attendances->isNotEmpty())
    <div class="mt-3">
        <div class="elite-kicker text-[.6rem] mb-1">Kehadiran</div>
        <div class="flex flex-wrap gap-1">
            @foreach($m->attendances as $att)
            <span class="text-xs px-2 py-0.5 border {{ $att->status === 'hadir' ? 'bg-green-50 border-green-300' : ($att->status === 'izin' ? 'bg-yellow-50 border-yellow-300' : 'bg-red-50 border-red-300') }}">
                {{ $att->member?->user?->name }} ({{ ucfirst($att->status) }})
            </span>
            @endforeach
        </div>
    </div>
    @endif
</div>
@empty
<p class="font-serif text-gray-500 italic mb-7">Belum ada rapat selesai.</p>
@endforelse

{{-- Proposals --}}
<h3 class="elite-h3 text-lg ink-primary mb-4">📝 Proposal Komite</h3>
@forelse($proposals as $p)
<div class="bg-white border border-rule p-5 mb-3">
    <div class="flex justify-between items-start">
        <div>
            <div class="font-serif font-semibold ink-primary">{{ $p->title }}</div>
            <div class="text-xs text-gray-500 mt-1">
                Oleh: {{ $p->proposer?->name }} · {{ $p->created_at->format('d/m/Y') }}
            </div>
        </div>
        <span class="text-xs px-2 py-0.5
            {{ $p->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
            {{ $p->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
            {{ $p->status === 'reviewed' ? 'bg-blue-100 text-blue-800' : '' }}
            {{ $p->status === 'submitted' ? 'bg-yellow-100 text-yellow-800' : '' }}">
            {{ ucfirst($p->status) }}
        </span>
    </div>
    @if($p->description)
    <p class="text-sm mt-2 text-gray-600">{{ Str::limit($p->description, 200) }}</p>
    @endif
    @if($p->estimated_budget)
    <div class="text-sm mt-1">Anggaran: <span class="font-mono">Rp {{ number_format($p->estimated_budget, 0, ',', '.') }}</span></div>
    @endif
</div>
@empty
<p class="font-serif text-gray-500 italic">Belum ada proposal komite.</p>
@endforelse
@endsection
