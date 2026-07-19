@extends('layouts.school-admin')
@section('title', 'Penilaian Kinerja Guru')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Pengajaran</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">PKG — Penilaian Kinerja Guru</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Berdasarkan Permendiknas No. 16/2007 — 14 Kompetensi Guru.</p>
</div>

<div class="flex flex-wrap gap-3 mb-5">
    <form method="GET" class="flex gap-2 items-center flex-wrap">
        <select name="teacher" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— Semua Guru —</option>
            @foreach($teachers as $t)<option value="{{ $t->user?->id }}" {{ request('teacher') == $t->user?->id ? 'selected' : '' }}>{{ $t->user?->name }}</option>@endforeach
        </select>
        <select name="type" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— Semua Tipe —</option>
            <option value="self" {{ request('type') == 'self' ? 'selected' : '' }}>Self Assessment</option>
            <option value="peer" {{ request('type') == 'peer' ? 'selected' : '' }}>Peer Review</option>
            <option value="supervisor" {{ request('type') == 'supervisor' ? 'selected' : '' }}>Kepala Sekolah</option>
        </select>
        <button class="btn-elite text-xs">Filter</button>
        <a href="{{ route('admin.pkg.index') }}" class="btn-elite-ghost text-xs">Reset</a>
    </form>
    <a href="{{ route('admin.pkg.create') }}" class="btn-elite text-xs">+ Buat PKG Baru</a>
</div>

@if($summaries->isNotEmpty())
<div class="grid md:grid-cols-3 gap-4 mb-6">
    @foreach($summaries->sortByDesc('avg_score') as $s)
    <div class="elite-card p-4">
        <div class="font-serif font-semibold text-sm ink-primary mb-1">{{ $s->teacher?->name }}</div>
        <div class="flex items-center gap-2">
            <span class="font-display text-2xl font-bold ink-accent">{{ number_format($s->avg_score, 1) }}</span>
            <span class="text-xs text-gray-500">dari {{ $s->total }} penilaian</span>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Guru</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Penilai</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Semester</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Skor</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th></th></tr></thead><tbody>
@forelse($assessments as $a)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif font-semibold">{{ $a->teacher?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $a->assessor?->name ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ match($a->type){'self'=>'Self','peer'=>'Peer','supervisor'=>'Kepsek',default:$a->type} }}</td>
<td class="px-3 py-3 font-mono text-xs">Smt {{ $a->semester }}</td>
<td class="px-3 py-3 text-xs">{{ $a->assessment_date?->format('d M Y') }}</td>
<td class="px-3 py-3 text-right font-mono font-bold">{{ $a->final_score ?? '—' }}</td>
<td class="px-3 py-3 text-center">
    @php
        $colors = ['draft'=>'#EAB308','submitted'=>'#2563EB','verified'=>'#16A34A'];
        $labels = ['draft'=>'Draft','submitted'=>'Terkirim','verified'=>'Terverifikasi'];
    @endphp
    <span class="text-xs px-2 py-0.5 rounded" style="background:{{ $colors[$a->status] }}22;color:{{ $colors[$a->status] }};font-weight:600">{{ $labels[$a->status] }}</span>
</td>
<td class="px-3 py-3 text-right space-x-1">
    <a href="{{ route('admin.pkg.detail', $a) }}" class="text-xs underline ink-secondary hover:ink-accent">Detail</a>
    @if($a->status !== 'verified')
    <form method="POST" action="{{ route('admin.pkg.verify', $a) }}" class="inline">@csrf<button class="text-xs text-green-700 underline">Verifikasi</button></form>
    @endif
    <a href="{{ route('admin.pkg.export-pdf', $a) }}" class="text-xs text-blue-700 underline">PDF</a>
    <form method="POST" action="{{ route('admin.pkg.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
</td>
</tr>@empty<tr><td colspan="8" class="p-10 text-center text-gray-500 italic font-serif">Belum ada penilaian PKG.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $assessments->links() }}</div>
@endsection
