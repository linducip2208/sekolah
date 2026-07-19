@extends('layouts.school-admin')
@section('title', 'Log Tamu')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Hospites</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Log Tamu / Visitor</h1><div class="elite-rule"></div></div>
<a href="{{ route('admin.visitor.blacklist.index') }}" class="btn-elite-ghost">Blacklist →</a></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Check-in Tamu</summary>
<form method="POST" action="{{ route('admin.visitor.logs.checkin') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">@csrf
<input name="visitor_name" required maxlength="200" placeholder="Nama tamu" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="id_number" maxlength="50" placeholder="No. KTP/ID" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input name="phone" maxlength="30" placeholder="No. HP" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input name="badge_no" maxlength="30" placeholder="Badge No" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input name="purpose" required maxlength="200" placeholder="Tujuan" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<div class="md:col-span-3"><button class="btn-elite">Check-in</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Check-in</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tamu</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tujuan</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Badge</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Check-out</th>
<th></th></tr></thead><tbody>
@forelse($logs as $l)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs font-mono">{{ $l->checked_in_at?->format('d M Y H:i') }}</td>
<td class="px-3 py-3"><div class="font-serif font-semibold">{{ $l->visitor_name }}</div><div class="text-xs text-gray-500">{{ $l->id_number ?? '' }} {{ $l->phone ? '· '.$l->phone : '' }}</div></td>
<td class="px-3 py-3 text-xs">{{ $l->purpose }}</td>
<td class="px-3 py-3 font-mono text-xs">{{ $l->badge_no ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ $l->checked_out_at?->format('d M Y H:i') ?? '—' }}</td>
<td class="px-3 py-3 text-right">@if(!$l->checked_out_at)<form method="POST" action="{{ route('admin.visitor.logs.checkout', $l) }}" class="inline">@csrf<button class="text-xs underline ink-secondary hover:ink-accent">Check-out</button></form>@endif</td>
</tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada tamu.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
