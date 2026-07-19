@extends('layouts.school-admin')
@section('title', 'Instrumen Akreditasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Instrumentum Accreditandi</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Instrumen & Penilaian Mandiri</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Nilai mandiri setiap butir instrumen sesuai standar BAN-S/M IASP 2020.</p>
</div>

<div class="mb-4 flex flex-wrap gap-2 items-center">
    <a href="{{ route('admin.accreditation.instruments') }}" class="text-xs {{ !$standardId ? 'text-[var(--c-accent)] font-bold' : 'text-gray-500' }} px-2 py-1 border border-rule">Semua</a>
    @foreach($standards as $std)
        <a href="?standard_id={{ $std->id }}" class="text-xs {{ $standardId == $std->id ? 'text-[var(--c-accent)] font-bold' : 'text-gray-500' }} px-2 py-1 border border-rule">Standar {{ $std->code }}</a>
    @endforeach
</div>

<div class="bg-white border border-rule overflow-hidden">
    <div class="px-5 py-3 border-b border-rule flex justify-between items-center">
        <div class="font-serif text-sm text-gray-600">{{ $instruments->count() }} instrumen</div>
    </div>
    <div class="table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]" style="width:80px;">No</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Deskripsi</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]" style="width:80px;">Nilai</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Bukti</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]" style="width:140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($instruments as $inst)
                @php
                    $score = $scores[$inst->id] ?? null;
                    $docs  = $documents[$inst->id] ?? collect();
                    $approvedDocs = $docs->where('status', 'approved')->count();
                    $totalDocs    = $docs->count();
                @endphp
                <tr class="border-t border-rule">
                    <td class="px-4 py-3 font-mono text-xs font-semibold">{{ $inst->number }}</td>
                    <td class="px-4 py-3">
                        <div class="font-serif text-sm ink-primary font-semibold">{{ Str::limit($inst->description, 120) }}</div>
                        @if($inst->evidence_hint)
                            <div class="text-[.6rem] text-gray-400 mt-1 font-mono">Bukti: {{ $inst->evidence_hint }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($score && $score->self_score !== null)
                            <span class="font-display text-lg font-bold ink-primary">{{ $score->self_score }}</span>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($totalDocs > 0)
                            <span class="text-xs {{ $approvedDocs === $totalDocs ? 'text-green-700' : 'text-amber-600' }}">
                                {{ $approvedDocs }}/{{ $totalDocs }} ✓
                            </span>
                        @else
                            <span class="text-xs text-gray-400">0</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            {{-- Quick score buttons --}}
                            <form method="POST" action="{{ route('admin.accreditation.scores.save') }}" class="flex gap-1 items-center">
                                @csrf
                                <input type="hidden" name="accreditation_instrument_id" value="{{ $inst->id }}">
                                <select name="self_score" class="border border-rule px-1 py-1 text-xs font-mono" onchange="this.form.submit()">
                                    <option value="">Nilai</option>
                                    @for($v = 0; $v <= 4; $v++)
                                        <option value="{{ $v }}" {{ ($score && $score->self_score === $v) ? 'selected' : '' }}>{{ $v }}</option>
                                    @endfor
                                </select>
                                <input type="text" name="notes" class="border border-rule px-1 py-1 text-xs" style="width:60px;" placeholder="Catatan" value="{{ $score?->notes ?? '' }}">
                            </form>
                            <a href="{{ route('admin.accreditation.documents', ['standard_id' => $inst->accreditation_standard_id]) }}" class="text-xs text-[var(--c-accent)] hover:underline whitespace-nowrap">Upload</a>
                        </div>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada instrumen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
