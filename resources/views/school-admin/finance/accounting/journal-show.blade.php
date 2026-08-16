@extends('layouts.school-admin')
@section('title', 'Detail Jurnal')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.accounting.journal.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Jurnal Umum</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Diarium</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Jurnal #{{ $entry->id }}</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">
        {{ $entry->entry_date->format('d M Y') }} · Ref: {{ $entry->reference_no ?? '—' }} ·
        @if($entry->status === 'posted')<span class="text-green-700">Posted</span>@else<span class="text-amber-700">Draft</span>@endif
    </p>
    <p class="text-sm text-gray-600">{{ $entry->description }}</p>
</div>

<div class="bg-white border border-rule overflow-hidden mb-6">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Akun</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Keterangan</th>
            <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Debit</th>
            <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Kredit</th>
        </tr></thead>
        <tbody>
            @foreach($entry->lines as $line)
            <tr class="border-t border-rule">
                <td class="px-4 py-3 font-serif">{{ $line->account?->code }} · {{ $line->account?->name }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $line->description }}</td>
                <td class="px-4 py-3 text-right font-mono text-xs">{{ $line->debit ? number_format($line->debit/100, 0, ',', '.') : '' }}</td>
                <td class="px-4 py-3 text-right font-mono text-xs">{{ $line->credit ? number_format($line->credit/100, 0, ',', '.') : '' }}</td>
            </tr>
            @endforeach
            <tr class="border-t-2 border-rule bg-gray-50 font-semibold">
                <td class="px-4 py-3" colspan="2">Total</td>
                <td class="px-4 py-3 text-right font-mono">{{ number_format($entry->lines->sum('debit')/100, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-right font-mono">{{ number_format($entry->lines->sum('credit')/100, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</div>

@endsection
