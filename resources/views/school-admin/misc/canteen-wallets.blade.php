@extends('layouts.school-admin')
@section('title', 'Dompet Kantin')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Marsupia Cibariae</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Dompet Cashless Kantin</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule"><summary class="px-5 py-4 cursor-pointer elite-kicker">+ Top-up Saldo Siswa</summary>
<form method="POST" action="{{ route('admin.misc.canteen.topup') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">@csrf
<input type="number" name="student_id" required placeholder="ID Siswa" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input type="number" step="1000" min="0" name="amount_rupiah" required placeholder="Jumlah top-up (Rp)" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<button class="btn-elite">Top-up</button>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">NIS</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Saldo</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Limit Harian</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
</tr></thead><tbody>
@forelse($wallets as $w)<tr class="border-t border-rule">
<td class="px-3 py-3 font-mono text-xs">{{ $w->admission_no }}</td>
<td class="px-3 py-3 font-serif">{{ $w->student_name }}</td>
<td class="px-3 py-3 text-right font-mono ink-primary">Rp {{ number_format($w->balance/100, 0, ',', '.') }}</td>
<td class="px-3 py-3 text-right font-mono text-xs">{{ $w->daily_limit ? 'Rp '.number_format($w->daily_limit/100, 0, ',', '.') : '∞' }}</td>
<td class="px-3 py-3"><span class="text-xs {{ $w->is_locked ? 'text-red-700' : 'text-green-700' }}">{{ $w->is_locked ? '🔒 Locked' : '✓ Aktif' }}</span></td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada dompet kantin. Top-up pertama akan auto-create.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $wallets->links() }}</div>
@endsection
