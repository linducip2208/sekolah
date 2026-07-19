@extends('layouts.school-admin')
@section('title', 'Daftar Donatur')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.donations.campaigns.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Campaign</a>
<div class="mb-7"><div class="elite-kicker mb-2">Donatores</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Daftar Donatur</h1><div class="elite-rule"></div></div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Donatur</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Campaign</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Nominal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Receipt</th>
</tr></thead><tbody>
@forelse($donations as $d)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $d->created_at?->format('d M Y') }}</td>
<td class="px-3 py-3 font-serif">{{ $d->is_anonymous ? '— Anonim —' : $d->donor_name }}</td>
<td class="px-3 py-3 text-xs">{{ $d->campaign?->title }}</td>
<td class="px-3 py-3 text-right font-mono">{{ $d->show_amount || !$d->is_anonymous ? 'Rp '.number_format($d->amount/100, 0, ',', '.') : 'Rahasia' }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $d->status }}</span></td>
<td class="px-3 py-3 font-mono text-xs">{{ $d->receipt_no ?? '—' }}</td>
</tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada donasi.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $donations->links() }}</div>
@endsection
