@extends('layouts.school-admin')
@section('title', 'Rapat Komite')
@section('sidebar')
    @include('school-admin.partials.sidebar')
@endsection

@push('head')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="mb-7 flex justify-between items-start flex-wrap gap-3">
    <div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Rapat Komite</h1>
        <div class="elite-rule"></div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.committee.members') }}" class="btn-elite-ghost">Anggota</a>
        <a href="{{ route('admin.committee.decisions') }}" class="btn-elite-ghost">Keputusan</a>
        <a href="{{ route('admin.committee.proposals') }}" class="btn-elite-ghost">Proposal</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-7">
    {{-- Create meeting --}}
    <div class="bg-white border border-rule p-7">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Jadwalkan Rapat</h3>
        <form method="POST" action="{{ route('admin.committee.meetings.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="elite-kicker block mb-1">Judul Rapat</label>
                <input type="text" name="title" required class="w-full border border-rule p-2.5" placeholder="Rapat Komite...">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Tanggal & Waktu</label>
                <input type="datetime-local" name="meeting_date" required class="w-full border border-rule p-2.5">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Lokasi</label>
                <input type="text" name="location" class="w-full border border-rule p-2.5" placeholder="Ruang rapat / Aula / Online...">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Agenda</label>
                <textarea name="agenda" rows="3" class="w-full border border-rule p-2.5"></textarea>
            </div>
            <button type="submit" class="btn-elite w-full">Jadwalkan Rapat</button>
        </form>
    </div>

    {{-- Meetings list --}}
    <div class="lg:col-span-2">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Daftar Rapat</h3>
        @forelse($meetings as $m)
        <div class="bg-white border border-rule p-5 mb-4" x-data="{ open: false }">
            <div class="flex justify-between items-start cursor-pointer" @click="open = !open">
                <div>
                    <div class="font-serif font-semibold ink-primary">{{ $m->title }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        📅 {{ $m->meeting_date->format('d M Y H:i') }} ·
                        📍 {{ $m->location ?? '—' }} ·
                        <span class="{{ $m->status === 'completed' ? 'ink-accent' : ($m->status === 'cancelled' ? 'text-red-500' : 'text-blue-600') }}">
                            {{ ucfirst($m->status) }}
                        </span>
                    </div>
                </div>
                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
            <div x-show="open" x-cloak class="mt-4 pt-4 border-t border-rule">
                @if($m->agenda)
                <div class="mb-3">
                    <div class="elite-kicker text-[.6rem] mb-1">Agenda</div>
                    <p class="text-sm whitespace-pre-wrap">{{ $m->agenda }}</p>
                </div>
                @endif
                @if($m->minutes)
                <div class="mb-3">
                    <div class="elite-kicker text-[.6rem] mb-1">Notulen</div>
                    <p class="text-sm whitespace-pre-wrap">{{ $m->minutes }}</p>
                </div>
                @endif
                <div class="flex gap-2 mt-3 flex-wrap">
                    <form method="POST" action="{{ route('admin.committee.meetings.update', $m) }}" class="inline-flex gap-2 flex-wrap">
                        @csrf @method('PUT')
                        <input type="hidden" name="title" value="{{ $m->title }}">
                        <input type="hidden" name="meeting_date" value="{{ $m->meeting_date }}">
                        <select name="status" class="border border-rule p-1.5 text-xs" onchange="this.form.submit()">
                            <option value="scheduled" {{ $m->status==='scheduled'?'selected':'' }}>Terjadwal</option>
                            <option value="ongoing" {{ $m->status==='ongoing'?'selected':'' }}>Sedang Berlangsung</option>
                            <option value="completed" {{ $m->status==='completed'?'selected':'' }}>Selesai</option>
                            <option value="cancelled" {{ $m->status==='cancelled'?'selected':'' }}>Dibatalkan</option>
                        </select>
                    </form>
                    <form method="POST" action="{{ route('admin.committee.meetings.delete', $m) }}" class="inline" onsubmit="return confirm('Hapus rapat ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-600">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <p class="font-serif text-gray-500 italic">Belum ada rapat komite.</p>
        @endforelse
    </div>
</div>
@endsection
