@extends('layouts.school-admin')
@section('title', 'Staff & Guru')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="flex justify-between items-end mb-7">
    <div>
        <div class="elite-kicker mb-2">Officiales</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Staff & Guru</h1>
        <div class="elite-rule"></div>
        <p class="font-serif text-sm text-gray-600 mt-3">{{ $staffs->total() }} staff terdaftar.</p>
    </div>
    <a href="{{ route('admin.staff.create') }}" class="btn-elite-gold">+ Tambah Staff</a>
</div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email / NIP"
           class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
    <select name="department" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua Departemen —</option>
        @foreach($departments as $d)<option value="{{ $d }}" @selected(request('department')===$d)>{{ $d }}</option>@endforeach
    </select>
    <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">NIP</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Departemen</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jabatan</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Role</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Gaji Pokok</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($staffs as $s)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs">{{ $s->employee_id ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="font-serif font-semibold ink-primary">{{ $s->user?->name }}</div>
                        <div class="text-xs text-gray-500">{{ $s->user?->email }}</div>
                    </td>
                    <td class="px-4 py-3">{{ $s->department ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $s->designation ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @foreach($s->user?->roles ?? [] as $r)
                            <span class="elite-kicker text-[.55rem]" style="color: var(--c-accent);">{{ $r->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $s->basic_salary ? 'Rp '.number_format($s->basic_salary/100, 0, ',', '.') : '—' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.staff.edit', $s) }}" class="text-xs underline ink-secondary hover:ink-accent">Edit</a>
                        <form method="POST" action="{{ route('admin.staff.destroy', $s) }}" class="inline ml-2"
                              onsubmit="return confirm('Nonaktifkan staff ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-700 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada staff.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $staffs->links() }}</div>

@endsection
