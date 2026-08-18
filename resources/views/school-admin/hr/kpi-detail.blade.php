@extends('layouts.school-admin')
@section('title', 'Detail KPI Appraisal')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Appraisal Detail</div>
            <h1 class="elite-h1 text-2xl ink-primary mb-2">{{ $appraisal->staff?->user?->name }} — {{ $appraisal->period }}</h1>
            <div class="elite-rule"></div>
        </div>
        <div class="flex gap-2">
            @if($appraisal->status === 'draft')
            <form method="POST" action="{{ route('admin.hr.kpi.submit', $appraisal) }}">
                @csrf
                <button class="btn-elite-gold" style="font-size:.65rem;">Submit</button>
            </form>
            @endif
            @if($appraisal->status === 'submitted')
            <form method="POST" action="{{ route('admin.hr.kpi.finalize', $appraisal) }}">
                @csrf
                <input type="hidden" name="reviewer_notes" value="">
                <button class="btn-elite" style="font-size:.65rem;" onclick="this.form.reviewer_notes.value=prompt('Catatan reviewer:')||''">Finalisasi</button>
            </form>
            @endif
            <a href="{{ route('admin.hr.kpi.index') }}" class="btn-elite-ghost">← Kembali</a>
        </div>
    </div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<div class="grid lg:grid-cols-3 gap-6 mb-6">
    <div class="bg-white border border-rule p-5 text-center">
        <div class="elite-kicker text-[.6rem] mb-1">Total Skor</div>
        <div class="text-4xl font-display ink-primary">{{ $appraisal->total_score ?? '—' }}</div>
    </div>
    <div class="bg-white border border-rule p-5 text-center">
        <div class="elite-kicker text-[.6rem] mb-1">Grade</div>
        <div class="text-4xl font-display ink-primary">{{ $appraisal->grade }}</div>
    </div>
    <div class="bg-white border border-rule p-5 text-center">
        <div class="elite-kicker text-[.6rem] mb-1">Status</div>
        <div class="text-lg font-serif mt-2">
            <span class="px-3 py-1 rounded text-sm
                {{ match($appraisal->status) { 'draft'=>'bg-gray-100 text-gray-600','submitted'=>'bg-yellow-100 text-yellow-700','finalized'=>'bg-green-100 text-green-700', default=>'bg-gray-100' }}">
                {{ ucfirst($appraisal->status) }}
            </span>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.hr.kpi.scores', $appraisal) }}">
    @csrf
    <div class="bg-white border border-rule overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Penilaian Kriteria</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kriteria</th>
                    <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Bobot</th>
                    <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Max</th>
                    <th class="text-center px-4 py-3 elite-kicker text-[.6rem] w-32">Skor</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Bukti / Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($criteria as $c)
                @php $existing = $existingScores->get($c->id); @endphp
                <tr class="border-t border-rule">
                    <td class="px-4 py-3 font-serif font-semibold text-xs">{{ $c->name }}</td>
                    <td class="px-4 py-3 text-center text-xs">{{ $c->weight }}</td>
                    <td class="px-4 py-3 text-center text-xs">{{ $c->max_score }}</td>
                    <td class="px-4 py-3 text-center">
                        <input type="number" name="scores[{{ $c->id }}][score]" value="{{ $existing?->score ?? '' }}" min="0" max="{{ $c->max_score }}"
                            class="w-20 border-2 border-rule px-2 py-1 font-mono text-sm text-center" {{ $appraisal->status === 'finalized' ? 'disabled' : '' }}>
                    </td>
                    <td class="px-4 py-3">
                        <input name="scores[{{ $c->id }}][evidence]" value="{{ $existing?->evidence }}" placeholder="Bukti / catatan"
                            class="w-full border border-rule px-2 py-1 text-xs" {{ $appraisal->status === 'finalized' ? 'disabled' : '' }}>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 bg-white border border-rule p-5">
        <label class="elite-kicker text-[.6rem] block mb-1">Catatan Reviewer</label>
        <textarea name="reviewer_notes" rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" {{ $appraisal->status === 'finalized' ? 'disabled' : '' }}>{{ $appraisal->reviewer_notes }}</textarea>
    </div>

    @if($appraisal->status !== 'finalized')
    <div class="mt-4">
        <button class="btn-elite" style="padding:.6rem 1.5rem;font-size:.65rem;">Simpan Penilaian</button>
    </div>
    @endif
</form>

@endsection
