@extends('layouts.school-admin')
@section('title', 'Kelas Pengganti')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Academic</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Kelas Pengganti (Makeup Class)</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Jadwalkan kelas pengganti untuk jadwal yang terlewat.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Jadwalkan Kelas Pengganti</summary>
    <form method="POST" action="{{ route('admin.academic.makeup-classes.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <select name="subject_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— Mata pelajaran —</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
        <select name="teacher_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— Guru pengajar —</option>
            @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
        </select>
        <select name="class_room_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— Kelas —</option>
            @foreach($classRooms as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        <input name="new_date" type="date" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <input name="new_period_number" type="number" min="1" required placeholder="Jam ke-" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <input name="new_room" maxlength="100" placeholder="Ruang baru (opsional)" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <textarea name="reason" rows="2" placeholder="Alasan" class="md:col-span-3 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <div class="md:col-span-3"><button class="btn-elite">Simpan</button></div>
    </form>
</details>

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-2 items-center">
    <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua status —</option>
        @foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
    </select>
    <input name="date" type="date" value="{{ request('date') }}" class="border-2 border-rule px-3 py-2 font-serif text-sm">
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal Baru</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jam</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Mapel</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Guru</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kelas</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($makeups as $m)
            <tr class="border-t border-rule">
                <td class="px-4 py-3 text-xs font-mono">{{ $m->new_date?->format('d M Y') }}</td>
                <td class="px-4 py-3 text-xs">Jam {{ $m->new_period_number }}</td>
                <td class="px-4 py-3 text-xs font-semibold">{{ $m->subject?->name }}</td>
                <td class="px-4 py-3 text-xs">{{ $m->teacher?->name }}</td>
                <td class="px-4 py-3 text-xs">{{ $m->classRoom?->name }}</td>
                <td class="px-4 py-3">
                    @php $colors = ['scheduled' => 'bg-blue-100 text-blue-700', 'completed' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-gray-100 text-gray-500']; @endphp
                    <span class="elite-kicker text-[.55rem] {{ $colors[$m->status] ?? '' }}">{{ ucfirst($m->status) }}</span>
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap text-xs">
                    @if($m->status === 'scheduled')
                    <form method="POST" action="{{ route('admin.academic.makeup-classes.complete', $m) }}" class="inline">@csrf
                        <button class="text-green-700 hover:underline">Selesai</button>
                    </form>
                    <form method="POST" action="{{ route('admin.academic.makeup-classes.cancel', $m) }}" class="inline ml-2">@csrf
                        <button class="text-amber-700 hover:underline">Batal</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.academic.makeup-classes.destroy', $m) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-700 hover:underline">Hapus</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kelas pengganti.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $makeups->links() }}</div>
@endsection
