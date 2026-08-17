@extends('layouts.school-admin')
@section('title', 'Otomasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Automata</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Aturan Otomasi</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Jalankan aksi otomatis ketika terjadi pemicu (mis. SPP jatuh tempo → kirim notifikasi).</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Aturan</summary>
    <form method="POST" action="{{ route('admin.automation.rules.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
        <input name="name" required maxlength="200" placeholder="Nama aturan" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <select name="trigger_type" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="fee_due_soon">SPP Jatuh Tempo</option>
            <option value="fee_overdue">SPP Menunggak</option>
            <option value="student_absent_streak">Absen Beruntun</option>
            <option value="birthday">Ulang Tahun</option>
        </select>
        <select name="action_type" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="notify">Notifikasi In-App</option>
            <option value="email">Email</option>
        </select>
        <div class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" checked class="w-4 h-4"><span class="text-sm font-serif">Aktif</span></div>
        <input name="title" maxlength="200" placeholder="Judul notifikasi (mis. Tagihan SPP {student})" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
        <textarea name="body" rows="2" maxlength="1000" placeholder="Isi notifikasi. Gunakan {student}, {amount}, {due}, {streak}" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <div class="md:col-span-2"><button class="btn-elite">Simpan Aturan</button></div>
    </form>
</details>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Pemicu</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Aksi</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Pesan</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($rules as $r)
            <tr class="border-t border-rule">
                <td class="px-4 py-3 font-serif">{{ $r->name }}</td>
                <td class="px-4 py-3 text-xs">{{ $r->trigger_type }}</td>
                <td class="px-4 py-3 text-xs">{{ $r->action_type }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $r->action_config['title'] ?? '' }}</td>
                <td class="px-4 py-3 text-center">
                    @if($r->is_active)<span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-800">Aktif</span>
                    @else<span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">Nonaktif</span>@endif
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <form method="POST" action="{{ route('admin.automation.rules.toggle', $r) }}" class="inline">@csrf<button class="text-xs underline ink-secondary">{{ $r->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                    <form method="POST" action="{{ route('admin.automation.rules.destroy', $r) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada aturan otomasi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
