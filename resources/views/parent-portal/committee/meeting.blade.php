@extends('layouts.parent')
@section('title', 'Detail Rapat Komite')
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Komite Sekolah</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $meeting->title }}</h1>
    <div class="elite-rule"></div>
    <div class="text-sm text-gray-500 mt-2">
        {{ $meeting->meeting_date?->format('d M Y H:i') }} · {{ $meeting->location ?? '—' }} · Status: {{ ucfirst($meeting->status) }}
    </div>
</div>

@if($meeting->agenda)
<div class="bg-white border border-rule p-5 mb-4">
    <div class="elite-kicker text-[.6rem] mb-1">Agenda</div>
    <div class="text-sm whitespace-pre-wrap">{{ $meeting->agenda }}</div>
</div>
@endif

@if($meeting->minutes)
<div class="bg-white border border-rule p-5 mb-4">
    <div class="elite-kicker text-[.6rem] mb-1">Notulen</div>
    <div class="text-sm whitespace-pre-wrap">{{ $meeting->minutes }}</div>
</div>
@endif

{{-- Attendance --}}
<h3 class="elite-h3 text-lg ink-primary mb-4">Kehadiran</h3>
<div class="bg-white border border-rule overflow-x-auto mb-7">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left border-b-2 border-rule">
                <th class="px-3 py-2">Nama</th>
                <th class="px-3 py-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($meeting->attendances as $a)
            <tr class="border-b border-rule">
                <td class="px-3 py-2">{{ $a->member?->user?->name ?? '—' }}</td>
                <td class="px-3 py-2 text-xs">{{ ucfirst($a->status ?? '—') }}</td>
            </tr>
            @empty
            <tr><td colspan="2" class="px-3 py-6 text-center text-gray-500 italic">Belum ada data kehadiran.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Decisions --}}
<h3 class="elite-h3 text-lg ink-primary mb-4">Keputusan</h3>
@forelse($meeting->decisions as $d)
<div class="bg-white border border-rule p-5 mb-3">
    <div class="font-serif font-semibold ink-primary">{{ $d->title ?? 'Keputusan' }}</div>
    @if($d->description)<div class="mt-1 text-sm text-gray-600">{{ $d->description }}</div>@endif
</div>
@empty
<p class="font-serif text-gray-500 italic mb-7">Belum ada keputusan tercatat.</p>
@endforelse

<div class="mt-4">
    <a href="{{ route('portal.committee') }}" class="text-sm underline">← Kembali ke Komite</a>
</div>
@endsection
