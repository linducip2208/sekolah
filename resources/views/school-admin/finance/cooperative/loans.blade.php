@extends('layouts.school-admin')
@section('title', 'Pinjaman Koperasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Koperasi</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Pinjaman Anggota</h1>
    <div class="elite-rule"></div>
</div>

<button onclick="document.getElementById('addLoanForm').classList.toggle('hidden')" class="btn-elite-gold mb-4">+ Buat Pinjaman</button>

<div id="addLoanForm" class="hidden elite-card p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Buat Pinjaman Baru</h3>
    <form method="POST" action="{{ route('admin.cooperative.loans.store') }}" class="grid md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Anggota *</label>
            <select name="cooperative_member_id" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="">— Pilih —</option>
                @foreach($members as $m)<option value="{{ $m->id }}">{{ $m->member_number }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Jumlah Pinjaman (Rp) *</label>
            <input type="number" name="loan_amount" required min="1000" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Bunga (%) / Tahun</label>
            <input type="number" name="interest_rate" step="0.01" value="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Jangka Waktu (bulan) *</label>
            <input type="number" name="term_months" required min="1" max="60" value="12" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Mulai *</label>
            <input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block elite-kicker text-[.6rem] mb-1">Tujuan</label>
            <textarea name="purpose" rows="2" class="w-full border-2 border-rule px-3 py-2 text-sm"></textarea>
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-elite">Buat Pinjaman</button>
        </div>
    </form>
</div>

<form method="GET" class="flex gap-3 mb-4 bg-white border border-rule p-4">
    <select name="status" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Status —</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
        <option value="paid_off" {{ request('status') === 'paid_off' ? 'selected' : '' }}>Lunas</option>
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Filter</button>
</form>

<div class="space-y-4">
    @forelse($loans as $loan)
    <div class="elite-card p-5">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-3">
            <div>
                <div class="font-mono text-[.65rem]">{{ $loan->member?->member_number }}</div>
                <div class="font-serif font-semibold ink-primary">Rp {{ number_format($loan->loan_amount / 100, 0, ',', '.') }}</div>
            </div>
            <div class="text-right">
                <span class="text-[.6rem] uppercase px-2 py-0.5 rounded
                    {{ $loan->status === 'active' ? 'bg-green-100 text-green-800' : ($loan->status === 'paid_off' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                    {{ $loan->status === 'pending' ? 'Pending' : ($loan->status === 'active' ? 'Aktif' : 'Lunas') }}
                </span>
                <div class="text-[.55rem] text-gray-500 mt-1">Bunga {{ $loan->interest_rate }}% · {{ $loan->term_months }} bln</div>
            </div>
        </div>

        @if($loan->status === 'pending')
        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.cooperative.loans.approve', $loan) }}" class="inline">
                @csrf <button class="btn-elite-gold text-xs">Setujui</button>
            </form>
            <form method="POST" action="{{ route('admin.cooperative.loans.reject', $loan) }}" class="inline">
                @csrf <button class="btn-elite-ghost text-xs">Tolak</button>
            </form>
        </div>
        @endif

        @if($loan->status === 'active')
        <div class="text-xs text-gray-600 mb-2">Angsuran: Rp {{ number_format($loan->monthly_installment / 100, 0, ',', '.') }}/bulan · {{ $loan->start_date?->format('d/m/Y') }} — {{ $loan->end_date?->format('d/m/Y') }}</div>
        <div class="space-y-1">
            @foreach($loan->installments()->orderBy('installment_number')->get() as $inst)
            <div class="flex items-center justify-between text-xs p-2 border border-rule 
                {{ $inst->status === 'paid' ? 'bg-green-50' : ($inst->status === 'overdue' ? 'bg-red-50' : '') }}">
                <span class="font-mono">#{{ $inst->installment_number }} · {{ $inst->due_date->format('d/m/Y') }}</span>
                <div class="flex items-center gap-2">
                    <span class="font-mono">Rp {{ number_format($inst->amount / 100, 0, ',', '.') }}</span>
                    @if($inst->status === 'paid')
                    <span class="text-green-700">Lunas {{ $inst->paid_date?->format('d/m/Y') }}</span>
                    @elseif($inst->status === 'overdue')
                    <span class="text-red-700">Overdue</span>
                    @else
                    <form method="POST" action="{{ route('admin.cooperative.installments.pay', $inst) }}" class="inline-flex gap-1">
                        @csrf
                        <input type="number" name="paid_amount" value="{{ $inst->amount }}" min="1" class="w-24 text-xs border px-1 py-0.5">
                        <button class="text-xs underline ink-accent">Bayar</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($loan->approvedBy)
        <div class="text-[.55rem] text-gray-400 mt-2">Disetujui oleh {{ $loan->approvedBy->name }} · {{ $loan->approved_at?->format('d/m/Y H:i') }}</div>
        @endif
    </div>
    @empty
    <div class="elite-card p-10 text-center text-gray-500 italic font-serif">Belum ada pinjaman.</div>
    @endforelse
</div>
<div class="mt-4">{{ $loans->links() }}</div>
@endsection
