@extends('layouts.school-admin')
@section('title', 'Konferensi Orang Tua-Guru')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="flex justify-between items-end mb-7">
    <div>
        <div class="elite-kicker mb-2">Conferentia</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Konferensi Orang Tua-Guru</h1>
        <div class="elite-rule"></div>
    </div>
    <a href="{{ route('admin.conferences.create') }}" class="btn-elite-gold">+ Sesi Baru</a>
</div>

<div class="space-y-3">
    @forelse($sessions as $s)
        <div class="bg-white border border-rule p-5">
            <div class="flex justify-between items-start gap-4">
                <div class="flex-1">
                    <div class="flex items-baseline gap-3 mb-1">
                        <h3 class="elite-h3 text-base ink-primary">{{ $s->title }}</h3>
                        @if($s->is_published)
                            <span class="text-xs text-green-700 font-semibold">● Published</span>
                        @else
                            <span class="text-xs text-gray-500">Draft</span>
                        @endif
                        <span class="elite-kicker text-[.55rem]">{{ $s->conference_type === 'individual' ? 'Individual' : 'Grup' }}</span>
                    </div>
                    <p class="font-serif text-sm text-gray-700 mb-1">{{ \Illuminate\Support\Str::limit($s->description, 150) }}</p>
                    <div class="text-xs text-gray-500 space-x-3">
                        <span>📅 {{ \Carbon\Carbon::parse($s->date)->translatedFormat('l, d M Y') }}</span>
                        <span>🕐 {{ $s->start_time }} - {{ $s->end_time }}</span>
                        <span>📍 {{ $s->location === 'online' ? 'Online' : 'Fisik' }}</span>
                        <span>📋 {{ $s->confirmed_count }} / {{ $s->max_bookings ?: '∞' }} booking</span>
                    </div>
                </div>
                <div class="flex flex-col gap-2 items-end flex-shrink-0">
                    <a href="{{ route('admin.conferences.bookings', $s) }}" class="text-xs underline ink-secondary hover:ink-accent">Lihat Booking</a>
                    <a href="{{ route('admin.conferences.edit', $s) }}" class="text-xs underline ink-secondary hover:ink-accent">Edit</a>
                    <a href="{{ route('admin.conferences.attendance-print', $s) }}" class="text-xs underline text-green-700 hover:text-green-900" target="_blank">Cetak Absensi</a>
                    <form method="POST" action="{{ route('admin.conferences.destroy', $s) }}" onsubmit="return confirm('Hapus sesi ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-700 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">
            Belum ada sesi konferensi.
        </div>
    @endforelse
</div>

<div class="mt-5">{{ $sessions->links() }}</div>

@endsection
