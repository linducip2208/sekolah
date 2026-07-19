@extends('layouts.school-admin')
@section('title', 'Respons Tracer Study')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Alumni</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Respons Tracer Study</h1>
    <div class="elite-rule"></div>
</div>

<div class="flex flex-wrap gap-3 mb-5">
    <form method="GET" class="flex gap-2 items-center">
        <select name="year" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— Tahun Lulus —</option>
            @foreach($years as $y)<option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
        </select>
        <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— Status —</option>
            @foreach($statuses as $k => $v)<option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>@endforeach
        </select>
        <button class="btn-elite text-xs">Filter</button>
    </form>
    <a href="{{ route('admin.tracer.export-csv') }}" class="btn-elite-ghost text-xs">↓ Ekspor CSV</a>
</div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Alumni</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Thn Lulus</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Perusahaan/Kampus</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Jabatan</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Relevan</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
</tr></thead><tbody>
@forelse($responses as $r)<tr class="border-t border-rule">
<td class="px-3 py-3"><div class="font-serif font-semibold">{{ $r->alumniProfile?->user?->name }}</div></td>
<td class="px-3 py-3 font-mono text-xs">{{ $r->graduation_year }}</td>
<td class="px-3 py-3 text-xs">{{ $statuses[$r->status] ?? $r->status }}</td>
<td class="px-3 py-3 text-xs">{{ $r->company_name ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ $r->position ?? '—' }}</td>
<td class="px-3 py-3 text-center text-xs">
    @if($r->is_relevant === true)<span class="text-green-700">✓</span>
    @elseif($r->is_relevant === false)<span class="text-red-700">✗</span>
    @else<span class="text-gray-400">—</span>@endif
</td>
<td class="px-3 py-3 text-xs">{{ $r->submitted_at?->format('d M Y') }}</td>
</tr>@empty<tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada respons tracer.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $responses->links() }}</div>
<div class="mt-4"><a href="{{ route('admin.tracer.dashboard') }}" class="btn-elite-ghost text-xs">← Dashboard</a></div>
@endsection
