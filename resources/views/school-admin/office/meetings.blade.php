@extends('layouts.school-admin')
@section('title', 'Agenda Rapat')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Kantor</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Agenda Rapat</h1>
<div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Jadwalkan Rapat</h3>
            <form method="POST" action="{{ route('admin.office.meetings.store') }}" class="space-y-3">
                @csrf
                <div><label class="elite-kicker text-[.6rem] block mb-1">Judul</label><input name="title" required maxlength="300" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Rapat Komite Kurikulum"></div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label><textarea name="description" rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea></div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label><input type="date" name="meeting_date" required class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="elite-kicker text-[.6rem] block mb-1">Jam Mulai</label><input type="time" name="start_time" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
                    <div><label class="elite-kicker text-[.6rem] block mb-1">Jam Selesai</label><input type="time" name="end_time" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
                </div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Lokasi</label><input name="location" maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Ruang Rapat Utama"></div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Jadwalkan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-3 items-end flex-wrap">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Status</label>
                <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">— Semua —</option>
                    @foreach(['planned','in_progress','completed','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
                </select>
            </div>
            <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
        </form>

        <div class="space-y-4">
        @forelse($agendas as $agenda)
        <div class="bg-white border border-rule p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <h3 class="font-serif font-semibold ink-primary text-base">{{ $agenda->title }}</h3>
                    <div class="text-xs text-gray-500 mt-1">
                        📅 {{ $agenda->meeting_date->format('d M Y') }}
                        @if($agenda->start_time) · {{ $agenda->start_time->format('H:i') }}@if($agenda->end_time) – {{ $agenda->end_time->format('H:i') }}@endif @endif
                        @if($agenda->location) · 📍 {{ $agenda->location }}@endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($agenda->status==='planned')<span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold">Direncanakan</span>
                    @elseif($agenda->status==='in_progress')<span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded font-semibold">Berlangsung</span>
                    @elseif($agenda->status==='completed')<span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-semibold">Selesai</span>
                    @else<span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded font-semibold">Dibatalkan</span>
                    @endif
                </div>
            </div>
            @if($agenda->description)<p class="text-sm text-gray-600 mb-2">{{ $agenda->description }}</p>@endif
            <div class="text-xs text-gray-400">Oleh: {{ $agenda->organizer?->name }}</div>

            <div class="mt-3 flex gap-2 flex-wrap">
                @if($agenda->status==='planned')
                <form method="POST" action="{{ route('admin.office.meetings.update-status', $agenda) }}" class="inline">@csrf
                    <input type="hidden" name="status" value="in_progress"><button class="text-xs bg-amber-50 text-amber-700 px-2 py-1 border border-amber-200 rounded hover:bg-amber-100">Mulai</button>
                </form>
                @elseif($agenda->status==='in_progress')
                <form method="POST" action="{{ route('admin.office.meetings.update-status', $agenda) }}" class="inline">@csrf
                    <input type="hidden" name="status" value="completed"><button class="text-xs bg-green-50 text-green-700 px-2 py-1 border border-green-200 rounded hover:bg-green-100">Selesai</button>
                </form>
                @endif

                <details class="inline-block text-left"><summary class="text-xs underline ink-secondary cursor-pointer">Notulensi</summary>
                    <form method="POST" action="{{ route('admin.office.meetings.store-minutes', $agenda) }}" class="mt-2 space-y-2 bg-gray-50 p-3 rounded">@csrf
                        <textarea name="content" required rows="4" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Isi notulensi rapat..."></textarea>
                        <div><label class="elite-kicker text-[.6rem] block mb-1">Keputusan (JSON array)</label><input name="decisions" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs" placeholder='["Keputusan 1","Keputusan 2"]'></div>
                        <button class="text-xs text-left ink-accent font-semibold">Simpan Notulensi</button>
                    </form>
                </details>

                <form method="POST" action="{{ route('admin.office.meetings.destroy', $agenda) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
            </div>

            @if($agenda->minutes->count())
            <div class="mt-3 border-t border-rule pt-3">
                <div class="text-xs font-semibold text-gray-700 mb-1">Notulensi:</div>
                @foreach($agenda->minutes as $min)
                <div class="bg-gray-50 rounded p-3 mb-2 text-xs text-gray-600">{{ $min->content }}</div>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada agenda rapat.</div>
        @endforelse
        </div>
        <div class="mt-4">{{ $agendas->links() }}</div>
    </div>
</div>
@endsection
