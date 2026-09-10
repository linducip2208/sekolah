@extends('layouts.school-admin')
@section('title', 'Invoice SPP')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<x-ui.page-header title="Invoice / Tagihan SPP" subtitle="{{ $invoices->total() }} invoice tercatat.">
    @if(rescue(fn () => route('admin.finance.reports.outstanding'), null, false))
        <a href="{{ route('admin.finance.reports.outstanding') }}" class="btn btn-secondary btn-sm">Tunggakan</a>
    @endif
</x-ui.page-header>

{{-- Filter chips + saved views --}}
<x-ui.filter-bar :labels="['search' => 'Cari', 'status' => 'Status']"
                 :valueLabels="['status.unpaid' => 'Belum Bayar', 'status.partial' => 'Sebagian', 'status.paid' => 'Lunas', 'status.overdue' => 'Terlambat']" />

{{-- Generate invoice batch --}}
<details class="mb-5 card">
    <summary class="px-5 py-4 cursor-pointer text-sm font-semibold flex items-center gap-2 select-none">
        <svg class="w-4 h-4 text-[var(--color-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
        Generate Invoice Batch
    </summary>
    <form method="POST" action="{{ route('admin.fee.invoices.generate') }}" class="px-5 pb-5 pt-1 border-t border-[var(--color-border)] grid md:grid-cols-4 gap-3">
        @csrf
        <div>
            <label class="label" for="gi-structure">Struktur Biaya <span class="req">*</span></label>
            <select name="fee_structure_id" id="gi-structure" required class="select">
                <option value="">— pilih —</option>
                @foreach($structures as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} (Rp {{ number_format($s->amount/100, 0, ',', '.') }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="gi-section">Rombel</label>
            <select name="class_section_id" id="gi-section" class="select">
                <option value="">— Semua Siswa —</option>
                @foreach($classSections as $cs)
                    <option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="gi-period">Periode <span class="req">*</span></label>
            <input type="text" name="period" id="gi-period" required maxlength="20" placeholder="2026-08" class="input font-mono" value="{{ now()->format('Y-m') }}">
        </div>
        <div>
            <label class="label" for="gi-due">Jatuh Tempo <span class="req">*</span></label>
            <input type="date" name="due_date" id="gi-due" required class="input">
        </div>
        <div class="md:col-span-4">
            <button class="btn">Generate Invoice</button>
            <p class="form-hint mt-2">Invoice dibuat untuk seluruh siswa aktif pada rombel terpilih. Duplikasi periode otomatis dicegah.</p>
        </div>
    </form>
</details>

<div class="card overflow-hidden">
    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-4 py-3 border-b border-[var(--color-border)]">
        <form method="GET" class="flex flex-1 gap-2 min-w-0">
            @if(request()->filled('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
            <input type="search" name="search" value="{{ request('search') }}" placeholder="No invoice / nama siswa…" aria-label="Cari invoice"
                   class="input max-w-xs">
            <button class="btn btn-secondary btn-sm flex-shrink-0">Cari</button>
        </form>
        <div class="flex items-center gap-1.5 flex-wrap">
            <a href="{{ request()->url() }}" class="badge {{ !request('status') ? 'badge-primary' : '' }}">Semua</a>
            @foreach(['unpaid' => 'Belum Bayar', 'partial' => 'Sebagian', 'paid' => 'Lunas', 'overdue' => 'Terlambat'] as $st => $lbl)
                @php $qs = array_merge(request()->query(), ['status' => $st]); unset($qs['page']); @endphp
                <a href="{{ request()->url() . '?' . http_build_query($qs) }}"
                   class="badge {{ request('status') === $st ? 'badge-primary' : '' }}">{{ $lbl }}</a>
            @endforeach
        </div>
    </div>

    <div class="table-scroll">
        <table class="table-elite">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Siswa</th>
                    <th>Item</th>
                    <th>Periode</th>
                    <th>Jatuh Tempo</th>
                    <th class="!text-right">Tagihan</th>
                    <th class="!text-right">Dibayar</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td class="font-mono text-xs">{{ $inv->invoice_no }}</td>
                        <td class="font-semibold">{{ $inv->student?->user?->name ?? '—' }}</td>
                        <td class="text-[var(--color-text-secondary)]">{{ $inv->feeStructure?->name ?? '—' }}</td>
                        <td class="font-mono text-xs">{{ $inv->period }}</td>
                        <td class="text-[var(--color-text-secondary)]">{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
                        <td class="text-right tabular-nums">{{ number_format($inv->amount/100, 0, ',', '.') }}</td>
                        <td class="text-right tabular-nums" style="color: var(--color-success);">{{ number_format($inv->paid_amount/100, 0, ',', '.') }}</td>
                        <td><x-ui.status :status="$inv->status" /></td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.fee.invoices.show', $inv) }}" class="text-sm font-semibold text-[var(--color-primary)] hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">
                        @if(request()->filled('search') || request()->filled('status'))
                            <x-feedback.empty-state icon="search" title="Tidak ada invoice yang cocok"
                                description="Coba ubah kata kunci atau filter status." />
                        @else
                            <x-feedback.empty-state icon="money" title="Belum ada invoice"
                                description="Buat batch invoice pertama Anda dengan memilih struktur biaya dan rombel di panel atas." />
                        @endif
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($invoices->hasPages())
        <div class="px-4 py-3 border-t border-[var(--color-border)] flex items-center justify-between text-sm">
            <span class="text-[var(--color-text-muted)]">Halaman {{ $invoices->currentPage() }} dari {{ $invoices->lastPage() }}</span>
            {{ $invoices->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
