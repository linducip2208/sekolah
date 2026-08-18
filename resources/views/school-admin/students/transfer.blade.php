@extends('layouts.school-admin')
@section('title', 'Pindah Sekolah')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Students</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Pindah Sekolah (Transfer)</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Proses siswa yang pindah ke sekolah lain.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<form method="POST" action="{{ route('admin.students.lifecycle.store-transfer') }}" class="mb-8 bg-white border border-rule p-6" onsubmit="return confirm('Proses transfer siswa ini?')">@csrf
    <h3 class="font-serif font-semibold text-sm mb-4 ink-primary">Form Pindah Sekolah</h3>
    <div class="grid md:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-serif text-gray-500 mb-1">Siswa</label>
            <select name="student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— Pilih siswa aktif —</option>
                @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->admission_no }} · {{ $s->user?->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-serif text-gray-500 mb-1">Sekolah Tujuan</label>
            <input name="to_school_name" required maxlength="200" placeholder="Nama sekolah tujuan" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
        <div>
            <label class="block text-xs font-serif text-gray-500 mb-1">Tanggal Pindah</label>
            <input name="transfer_date" type="date" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
        <div>
            <label class="block text-xs font-serif text-gray-500 mb-1">No. Dokumen (opsional)</label>
            <input name="document_no" maxlength="100" placeholder="Nomor surat pindah" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-serif text-gray-500 mb-1">Alasan</label>
            <textarea name="reason" rows="2" placeholder="Alasan pindah" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        </div>
    </div>
    <div class="mt-4">
        <button class="btn-elite" style="padding:.6rem 2rem;font-size:.65rem;">Proses Transfer</button>
    </div>
</form>

<h3 class="font-serif font-semibold text-sm mb-3 ink-primary">Riwayat Transfer</h3>
<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Siswa</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Asal Sekolah</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Ke Sekolah</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">No. Dokumen</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transfers as $t)
            <tr class="border-t border-rule">
                <td class="px-4 py-3 text-xs font-semibold">{{ $t->student?->user?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs">{{ $t->from_school_name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs font-semibold">{{ $t->to_school_name }}</td>
                <td class="px-4 py-3 text-xs font-mono">{{ $t->transfer_date?->format('d M Y') }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $t->document_no ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada riwayat transfer.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $transfers->links() }}</div>
@endsection
