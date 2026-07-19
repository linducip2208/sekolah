@extends('super-admin.layout')
@section('title', 'Webhook Logs')
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Acta Webhook</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Payment Webhook Logs</h1><div class="elite-rule"></div></div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Source IP</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Signature</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Processing</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Provider</th>
</tr></thead><tbody>
@forelse($logs as $l)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs font-mono">{{ $l->created_at?->format('d M Y H:i:s') }}</td>
<td class="px-3 py-3 font-mono text-xs">{{ $l->source_ip }}</td>
<td class="px-3 py-3"><span class="text-xs px-2 py-0.5 rounded {{ $l->signature_status==='valid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $l->signature_status ?? '—' }}</span></td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $l->processing_status }}</span></td>
<td class="px-3 py-3 text-xs">#{{ $l->payment_provider_id }}</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada webhook tercatat.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
