@extends('layouts.school-admin')
@section('title', 'Detail Audit')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.internal-audit.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Audit Internal</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Auditus</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">{{ $audit->title }}</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">{{ $audit->period ?? '' }} {{ $audit->auditor ? '· ' . $audit->auditor : '' }}</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Temuan</summary>
    <form method="POST" action="{{ route('admin.internal-audit.findings.store', $audit) }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-2 gap-2">@csrf
        <input name="area" required maxlength="200" placeholder="Area (mis. Keuangan)" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <select name="severity" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="medium">Sedang</option>
            <option value="low">Rendah</option>
            <option value="high">Tinggi</option>
        </select>
        <textarea name="description" rows="2" required placeholder="Deskripsi temuan" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <textarea name="action" rows="2" placeholder="Tindakan perbaikan" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <input type="date" name="due_date" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <div><button class="btn-elite">Simpan Temuan</button></div>
    </form>
</details>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Area</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Temuan & Tindakan</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Severity</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Batas</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($audit->findings as $f)
            <tr class="border-t border-rule">
                <td class="px-4 py-3 font-serif text-xs">{{ $f->area }}</td>
                <td class="px-4 py-3">
                    <div class="text-sm">{{ $f->description }}</div>
                    @if($f->action)<div class="text-xs text-gray-500 mt-1">Tindakan: {{ $f->action }}</div>@endif
                </td>
                <td class="px-4 py-3 text-center">
                    @if($f->severity === 'high')<span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-800">Tinggi</span>
                    @elseif($f->severity === 'medium')<span class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-800">Sedang</span>
                    @else<span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600">Rendah</span>@endif
                </td>
                <td class="px-4 py-3 font-mono text-xs">{{ $f->due_date?->format('d M Y') ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                    @if($f->status === 'resolved')<span class="text-xs text-green-700">✓ Selesai</span>
                    @elseif($f->status === 'in_progress')<span class="text-xs text-amber-700">Ditangani</span>
                    @else<span class="text-xs text-gray-500">Terbuka</span>@endif
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    @if($f->status === 'open')
                    <form method="POST" action="{{ route('admin.internal-audit.findings.status', $f) }}" class="inline">@csrf<input type="hidden" name="status" value="in_progress"><button class="text-xs underline ink-secondary">Tangani</button></form>
                    @elseif($f->status === 'in_progress')
                    <form method="POST" action="{{ route('admin.internal-audit.findings.status', $f) }}" class="inline">@csrf<input type="hidden" name="status" value="resolved"><button class="text-xs underline text-green-700">Selesai</button></form>
                    @endif
                    <form method="POST" action="{{ route('admin.internal-audit.findings.destroy', $f) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada temuan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
