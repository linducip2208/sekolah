@extends('layouts.school-admin')
@section('title', 'Peminjaman Aset')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.inventory.assets.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Aset</a>
<div class="mb-7"><div class="elite-kicker mb-2">Mutua Bonorum</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Peminjaman Aset</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Pinjamkan Aset</summary>
<form method="POST" action="{{ route('admin.inventory.loans.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<select name="asset_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— aset tersedia —</option>
@foreach($assets as $a)<option value="{{ $a->id }}">{{ $a->asset_code }} — {{ $a->name }}</option>@endforeach
</select>
<select name="borrower_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— peminjam —</option>
@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
</select>
<input type="date" name="borrowed_at" required value="{{ now()->toDateString() }}" class="border-2 border-rule px-3 py-2 text-sm">
<input type="date" name="due_at" required value="{{ now()->addDays(7)->toDateString() }}" class="border-2 border-rule px-3 py-2 text-sm">
<textarea name="note" rows="2" placeholder="Catatan" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<div class="md:col-span-2"><button class="btn-elite">Pinjamkan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Aset</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Peminjam</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Pinjam</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Jatuh Tempo</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th></th></tr></thead><tbody>
@forelse($loans as $l)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $l->asset?->asset_code }} {{ $l->asset?->name }}</td>
<td class="px-3 py-3 font-serif">{{ $l->borrower?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $l->borrowed_at?->format('d M Y') }}</td>
<td class="px-3 py-3 text-xs">{{ $l->due_at?->format('d M Y') }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $l->status }}</span></td>
<td class="px-3 py-3 text-right">@if($l->status === 'borrowed')<form method="POST" action="{{ route('admin.inventory.loans.return', $l) }}" class="inline">@csrf<button class="text-xs underline ink-secondary hover:ink-accent">Kembalikan</button></form>@endif</td>
</tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada peminjaman.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $loans->links() }}</div>
@endsection
