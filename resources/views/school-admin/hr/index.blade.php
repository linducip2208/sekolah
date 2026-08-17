@extends('layouts.school-admin')
@section('title', 'HR / Human Capital')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Humanitas</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">HR / Human Capital</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Kontrak kerja, cuti, dan lembur karyawan.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Contracts --}}
    <div class="bg-white border border-rule overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Kontrak Kerja</div>
        <details class="border-b border-rule"><summary class="px-4 py-2 cursor-pointer text-xs underline ink-secondary">+ Tambah Kontrak</summary>
            <form method="POST" action="{{ route('admin.hr.contracts.store') }}" class="px-4 pb-3 grid gap-2">@csrf
                <select name="staff_id" required class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
                    <option value="">— pegawai —</option>
                    @foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->user?->name ?? $s->employee_id }}</option>@endforeach
                </select>
                <select name="type" class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
                    <option value="contract">Kontrak</option>
                    <option value="permanent">Tetap</option>
                    <option value="probation">Percobaan</option>
                </select>
                <input type="date" name="start_date" required class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
                <input type="date" name="end_date" class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
                <input type="number" name="salary_rupiah" required placeholder="Gaji (Rp)" class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
                <button class="btn-elite" style="padding:.3rem;font-size:.65rem;">Simpan</button>
            </form>
        </details>
        <div class="divide-y divide-rule max-h-72 overflow-y-auto">
            @forelse($contracts as $c)
            <div class="px-4 py-2 text-xs flex justify-between gap-2">
                <div><b>{{ $c->staff?->user?->name ?? '—' }}</b><div class="text-gray-500">{{ $c->type }} · {{ $c->start_date->format('d M Y') }}</div></div>
                <div class="text-right">
                    <span class="text-gray-500">{{ $c->status }}</span>
                    @if($c->status === 'active')<form method="POST" action="{{ route('admin.hr.contracts.terminate', $c) }}" class="inline">@csrf<button class="text-red-700 ml-1 underline">Akhiri</button></form>@endif
                </div>
            </div>
            @empty
            <div class="px-4 py-4 text-center text-gray-400 italic font-serif text-xs">Belum ada kontrak.</div>
            @endforelse
        </div>
    </div>

    {{-- Leave --}}
    <div class="bg-white border border-rule overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Cuti</div>
        <details class="border-b border-rule"><summary class="px-4 py-2 cursor-pointer text-xs underline ink-secondary">+ Ajukan Cuti</summary>
            <form method="POST" action="{{ route('admin.hr.leave.store') }}" class="px-4 pb-3 grid gap-2">@csrf
                <select name="staff_id" required class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
                    <option value="">— pegawai —</option>
                    @foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->user?->name ?? $s->employee_id }}</option>@endforeach
                </select>
                <select name="type" class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
                    <option value="annual">Tahunan</option>
                    <option value="sick">Sakit</option>
                    <option value="other">Lainnya</option>
                </select>
                <div class="flex gap-1"><input type="date" name="start_date" required class="border-2 border-rule px-2 py-1.5 font-mono text-xs flex-1"><input type="date" name="end_date" required class="border-2 border-rule px-2 py-1.5 font-mono text-xs flex-1"></div>
                <input type="number" name="days" min="1" required placeholder="Jumlah hari" class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
                <button class="btn-elite" style="padding:.3rem;font-size:.65rem;">Ajukan</button>
            </form>
        </details>
        <div class="divide-y divide-rule max-h-72 overflow-y-auto">
            @forelse($leaves as $l)
            <div class="px-4 py-2 text-xs flex justify-between gap-2">
                <div><b>{{ $l->staff?->user?->name ?? '—' }}</b><div class="text-gray-500">{{ $l->type }} · {{ $l->days }} hari · {{ $l->start_date->format('d M') }}</div></div>
                <div class="text-right">
                    @if($l->status === 'pending')
                    <form method="POST" action="{{ route('admin.hr.leave.decide', $l) }}" class="inline">@csrf<input type="hidden" name="action" value="approve"><button class="text-green-700 underline">Setujui</button></form>
                    <form method="POST" action="{{ route('admin.hr.leave.decide', $l) }}" class="inline">@csrf<input type="hidden" name="action" value="reject"><button class="text-red-700 ml-1 underline">Tolak</button></form>
                    @else
                    <span class="{{ $l->status === 'approved' ? 'text-green-700' : 'text-red-700' }}">{{ $l->status }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-4 py-4 text-center text-gray-400 italic font-serif text-xs">Belum ada cuti.</div>
            @endforelse
        </div>
    </div>

    {{-- Overtime --}}
    <div class="bg-white border border-rule overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Lembur</div>
        <details class="border-b border-rule"><summary class="px-4 py-2 cursor-pointer text-xs underline ink-secondary">+ Catat Lembur</summary>
            <form method="POST" action="{{ route('admin.hr.overtime.store') }}" class="px-4 pb-3 grid gap-2">@csrf
                <select name="staff_id" required class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
                    <option value="">— pegawai —</option>
                    @foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->user?->name ?? $s->employee_id }}</option>@endforeach
                </select>
                <input type="date" name="date" required value="{{ now()->toDateString() }}" class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
                <input type="number" step="0.5" name="hours" min="0.5" required placeholder="Jam lembur" class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
                <input type="number" name="rate_per_hour_rupiah" required placeholder="Tarif/jam (Rp)" class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
                <button class="btn-elite" style="padding:.3rem;font-size:.65rem;">Simpan</button>
            </form>
        </details>
        <div class="divide-y divide-rule max-h-72 overflow-y-auto">
            @forelse($overtimes as $o)
            <div class="px-4 py-2 text-xs flex justify-between gap-2">
                <div><b>{{ $o->staff?->user?->name ?? '—' }}</b><div class="text-gray-500">{{ $o->date->format('d M Y') }} · {{ $o->hours }} jam · Rp {{ number_format($o->amount/100,0,',','.') }}</div></div>
                <div class="text-right">
                    @if($o->status === 'pending')<form method="POST" action="{{ route('admin.hr.overtime.approve', $o) }}" class="inline">@csrf<button class="text-green-700 underline">Setujui</button></form>
                    @else<span class="text-green-700">{{ $o->status }}</span>@endif
                </div>
            </div>
            @empty
            <div class="px-4 py-4 text-center text-gray-400 italic font-serif text-xs">Belum ada lembur.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
