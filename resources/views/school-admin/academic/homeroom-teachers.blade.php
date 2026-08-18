@extends('layouts.school-admin')
@section('title', 'Wali Kelas')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Academic</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Wali Kelas</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Penugasan wali kelas per tahun ajaran.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ session('error') }}</div>@endif

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Penugasan</summary>
    <form method="POST" action="{{ route('admin.academic.homeroom-teachers.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <select name="staff_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— Pilih guru/staff —</option>
            @foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->user?->name }} ({{ $s->employee_id }})</option>@endforeach
        </select>
        <select name="class_room_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— Pilih kelas —</option>
            @foreach($classRooms as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
        </select>
        <input name="academic_year" value="{{ $currentYear }}" required maxlength="20" placeholder="Tahun ajaran" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <input name="start_date" type="date" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <div class="md:col-span-3"><button class="btn-elite">Simpan</button></div>
    </form>
</details>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Guru / Staff</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kelas</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tahun Ajaran</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Mulai</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Selesai</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $a)
            <tr class="border-t border-rule">
                <td class="px-4 py-3 font-serif font-semibold text-xs">{{ $a->staff?->user?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs">{{ $a->classRoom?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs font-mono">{{ $a->academic_year }}</td>
                <td class="px-4 py-3 text-xs">{{ $a->start_date?->format('d M Y') }}</td>
                <td class="px-4 py-3 text-xs">{{ $a->end_date?->format('d M Y') ?? '—' }}</td>
                <td class="px-4 py-3">
                    <span class="elite-kicker text-[.55rem] {{ $a->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $a->is_active ? '● Aktif' : 'Selesai' }}</span>
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap text-xs">
                    @if($a->is_active)
                    <form method="POST" action="{{ route('admin.academic.homeroom-teachers.deactivate', $a) }}" class="inline">@csrf
                        <button class="text-amber-700 hover:underline">Akhiri</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.academic.homeroom-teachers.destroy', $a) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-700 hover:underline">Hapus</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada penugasan wali kelas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
