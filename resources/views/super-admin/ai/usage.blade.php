@extends('layouts.school-admin')

@section('title', 'Global AI Usage')

@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Super Admin</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Global AI Usage</h1>
    <div class="elite-rule"></div>
</div>

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-3 items-end text-sm">
    <div><label class="elite-kicker text-[.6rem] block mb-1">Dari</label>
    <input type="date" name="date_from" value="{{ $from }}" class="border-2 border-rule px-2 py-1.5"></div>
    <div><label class="elite-kicker text-[.6rem] block mb-1">Sampai</label>
    <input type="date" name="date_to" value="{{ $to }}" class="border-2 border-rule px-2 py-1.5"></div>
    <button class="btn-elite text-sm">Filter</button>
</form>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <div class="bg-white border border-rule p-3"><div class="elite-kicker text-[.55rem]">Total Calls</div><div class="text-2xl font-display ink-primary">{{ number_format($totals['calls']) }}</div></div>
    <div class="bg-white border border-rule p-3"><div class="elite-kicker text-[.55rem]">Total Input Tokens</div><div class="text-2xl font-display ink-primary">{{ number_format($totals['input_tokens']) }}</div></div>
    <div class="bg-white border border-rule p-3"><div class="elite-kicker text-[.55rem]">Total Output Tokens</div><div class="text-2xl font-display ink-primary">{{ number_format($totals['output_tokens']) }}</div></div>
    <div class="bg-white border border-rule p-3"><div class="elite-kicker text-[.55rem]">Total Estimated Cost</div><div class="text-2xl font-display ink-primary">{{ number_format($totals['total_cost'], 4) }}</div></div>
</div>

<div class="bg-white border border-rule">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-rule">
            <tr>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Sekolah</th>
                <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Calls</th>
                <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Input Tokens</th>
                <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Output Tokens</th>
                <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Cost</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bySchool as $row)
                <tr class="border-b border-rule">
                    <td class="px-3 py-2 font-serif">{{ $schools[$row->school_id]?->name ?? '#'.$row->school_id }}</td>
                    <td class="px-3 py-2 text-right">{{ number_format($row->calls) }}</td>
                    <td class="px-3 py-2 text-right">{{ number_format($row->input_tokens) }}</td>
                    <td class="px-3 py-2 text-right">{{ number_format($row->output_tokens) }}</td>
                    <td class="px-3 py-2 text-right font-semibold">{{ number_format($row->total_cost, 4) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500 font-serif">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
