@extends('layouts.school-admin')
@section('title', 'Kenaikan Kelas Massal')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Students</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Kenaikan Kelas Massal</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Promosikan semua siswa aktif dari satu kelas ke kelas lain.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ session('error') }}</div>@endif

<form method="POST" action="{{ route('admin.students.lifecycle.batch-promote') }}" class="mb-8 bg-white border border-rule p-6" onsubmit="return confirm('Promosikan semua siswa aktif dari kelas asal ke kelas tujuan?')">@csrf
    <h3 class="font-serif font-semibold text-sm mb-4 ink-primary">Form Kenaikan Kelas</h3>
    <div class="grid md:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-serif text-gray-500 mb-1">Kelas Asal</label>
            <select name="from_class_section_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— Pilih —</option>
                @foreach($classSections as $cs)<option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-serif text-gray-500 mb-1">Kelas Tujuan</label>
            <select name="to_class_section_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— Pilih —</option>
                @foreach($classSections as $cs)<option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-serif text-gray-500 mb-1">Tahun Ajaran</label>
            <input name="academic_year" required maxlength="20" placeholder="2026/2027" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
        <div>
            <label class="block text-xs font-serif text-gray-500 mb-1">Tanggal Efektif</label>
            <input name="effective_date" type="date" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
    </div>
    <div class="mt-4">
        <button class="btn-elite" style="padding:.6rem 2rem;font-size:.65rem;">Promosikan Sekarang</button>
    </div>
</form>

<h3 class="font-serif font-semibold text-sm mb-3 ink-primary">Riwayat Kenaikan Kelas</h3>
<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Siswa</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Dari</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Ke</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tahun</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($enrollments as $e)
            <tr class="border-t border-rule">
                <td class="px-4 py-3 text-xs font-semibold">{{ $e->student?->user?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs">{{ $e->fromClassSection?->classRoom?->name }} {{ $e->fromClassSection?->section?->name }}</td>
                <td class="px-4 py-3 text-xs">{{ $e->toClassSection?->classRoom?->name }} {{ $e->toClassSection?->section?->name }}</td>
                <td class="px-4 py-3 text-xs font-mono">{{ $e->academic_year }}</td>
                <td class="px-4 py-3">
                    @php $colors = ['enrolled' => 'bg-blue-100 text-blue-700', 'promoted' => 'bg-green-100 text-green-700', 'graduated' => 'bg-purple-100 text-purple-700', 'transferred' => 'bg-amber-100 text-amber-700', 'dropped' => 'bg-red-100 text-red-700']; @endphp
                    <span class="elite-kicker text-[.55rem] {{ $colors[$e->status] ?? '' }}">{{ ucfirst($e->status) }}</span>
                </td>
                <td class="px-4 py-3 text-xs">{{ $e->effective_date?->format('d M Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada riwayat kenaikan kelas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $enrollments->links() }}</div>
@endsection
