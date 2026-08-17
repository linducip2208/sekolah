@extends('layouts.school-admin')
@section('title', 'Audit Internal')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Auditus</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Audit Internal</h1>
    <div class="elite-rule"></div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<div class="grid sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-rule p-4"><div class="elite-kicker text-[.55rem] text-gray-500">Temuan Terbuka</div><div class="font-display text-2xl text-amber-700 mt-1">{{ $summary['open'] }}</div></div>
    <div class="bg-white border border-rule p-4"><div class="elite-kicker text-[.55rem] text-gray-500">Sedang Ditangani</div><div class="font-display text-2xl ink-primary mt-1">{{ $summary['progress'] }}</div></div>
    <div class="bg-white border border-rule p-4"><div class="elite-kicker text-[.55rem] text-gray-500">Selesai</div><div class="font-display text-2xl text-green-700 mt-1">{{ $summary['resolved'] }}</div></div>
    <div class="bg-white border border-rule p-4"><div class="elite-kicker text-[.55rem] text-gray-500">Kritis (High)</div><div class="font-display text-2xl text-red-700 mt-1">{{ $summary['high'] }}</div></div>
</div>

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Buat Audit</summary>
    <form method="POST" action="{{ route('admin.internal-audit.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-2 gap-2">@csrf
        <input name="title" required maxlength="200" placeholder="Judul audit" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <input name="period" maxlength="100" placeholder="Periode (mis. Semester 1 2026)" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <input name="auditor" maxlength="200" placeholder="Auditor" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <textarea name="notes" rows="2" placeholder="Catatan" class="border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <div class="md:col-span-2"><button class="btn-elite">Buat Audit</button></div>
    </form>
</details>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Audit</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Periode</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Auditor</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Temuan</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($audits as $a)
            <tr class="border-t border-rule hover:bg-gray-50">
                <td class="px-4 py-3 font-serif"><a href="{{ route('admin.internal-audit.show', $a) }}" class="ink-secondary hover:underline">{{ $a->title }}</a></td>
                <td class="px-4 py-3 text-xs">{{ $a->period ?? '—' }}</td>
                <td class="px-4 py-3 text-xs">{{ $a->auditor ?? '—' }}</td>
                <td class="px-4 py-3 text-center font-mono text-xs">{{ $a->findings_count }}</td>
                <td class="px-4 py-3 text-center">
                    @php $tone = ['planned'=>'gray','in_progress'=>'amber','completed'=>'green'][$a->status]; @endphp
                    <span class="text-xs px-2 py-0.5 rounded bg-{{ $tone }}-100 text-{{ $tone }}-800">{{ ucfirst(str_replace('_',' ',$a->status)) }}</span>
                </td>
                <td class="px-4 py-3 text-right">
                    @if($a->status === 'planned')
                    <form method="POST" action="{{ route('admin.internal-audit.start', $a) }}" class="inline">@csrf<button class="text-xs underline ink-secondary">Mulai</button></form>
                    @elseif($a->status === 'in_progress')
                    <form method="POST" action="{{ route('admin.internal-audit.complete', $a) }}" class="inline">@csrf<button class="text-xs underline text-green-700">Selesaikan</button></form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada audit internal.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
