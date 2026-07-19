@extends('layouts.school-admin')
@section('title', 'Maintenance Request')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Reparationes</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Laporan Kerusakan / Maintenance</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule"><summary class="px-5 py-4 cursor-pointer elite-kicker">+ Lapor Kerusakan</summary>
<form method="POST" action="{{ route('admin.misc.maintenance.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<select name="asset_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Aset (opsional) —</option>
@foreach($assets as $a)<option value="{{ $a->id }}">{{ $a->asset_code }} — {{ $a->name }}</option>@endforeach
</select>
<input name="location_text" maxlength="200" placeholder="Lokasi (jika bukan aset spesifik)" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<select name="priority" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="critical">Critical</option>
</select>
<textarea name="issue_description" rows="3" required placeholder="Deskripsi kerusakan" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<div class="md:col-span-2"><button class="btn-elite">Kirim Laporan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Aset/Lokasi</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Issue</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Priority</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th></th></tr></thead><tbody>
@forelse($requests as $r)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $r->created_at?->format('d M Y') }}</td>
<td class="px-3 py-3 text-xs">{{ $r->asset?->name ?? $r->location_text ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ Str::limit($r->issue_description, 60) }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $r->priority }}</span></td>
<td class="px-3 py-3"><span class="text-xs px-2 py-0.5 rounded {{ $r->status === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $r->status }}</span></td>
<td class="px-3 py-3 text-right">
@if($r->status !== 'resolved')
<details class="inline"><summary class="text-xs underline ink-secondary cursor-pointer">Selesaikan</summary>
<form method="POST" action="{{ route('admin.misc.maintenance.resolve', $r) }}" class="absolute right-4 mt-2 bg-white border border-rule p-3 shadow-lg z-10 space-y-2 w-72">
@csrf
<input name="resolution_note" required placeholder="Catatan resolusi" class="w-full border border-rule px-2 py-1 text-xs">
<input type="number" step="1000" min="0" name="cost_rupiah" placeholder="Biaya (Rp, opsional)" class="w-full border border-rule px-2 py-1 text-xs">
<button class="btn-elite w-full text-xs">Tandai Selesai</button>
</form></details>
@elseif($r->resolved_at)
<span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($r->resolved_at)->format('d M Y') }}</span>
@endif
</td></tr>
@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada laporan.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $requests->links() }}</div>
@endsection
