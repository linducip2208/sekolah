@extends('layouts.school-admin')
@section('title', $request->request_number . ' — Detail Pengadaan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    $statusColors = [
        'draft' => 'bg-stone-100 text-stone-600', 'submitted' => 'bg-blue-100 text-blue-700',
        'approved' => 'bg-green-100 text-green-700', 'rejected' => 'bg-red-100 text-red-700',
        'ordered' => 'bg-purple-100 text-purple-700', 'received' => 'bg-emerald-100 text-emerald-700',
    ];
    $urgencyColors = [
        'low' => 'bg-stone-100 text-stone-500', 'medium' => 'bg-sky-100 text-sky-700',
        'high' => 'bg-orange-100 text-orange-700', 'urgent' => 'bg-red-100 text-red-700',
    ];
    $statusLabels = [
        'draft' => 'Draft', 'submitted' => 'Diajukan', 'approved' => 'Disetujui',
        'rejected' => 'Ditolak', 'ordered' => 'Dipesan', 'received' => 'Diterima',
    ];
    $totalEst = $request->totalEstimated();
@endphp

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.procurement.index') }}" class="elite-kicker hover:underline">
            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
            Kembali ke daftar
        </a>
    </div>

    {{-- Header --}}
    <div class="elite-card p-6 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="font-mono text-lg font-semibold text-[var(--c-primary)]">{{ $request->request_number }}</span>
                    <span class="px-2 py-0.5 text-[.6rem] font-semibold uppercase tracking-wider {{ $statusColors[$request->status] ?? '' }}">{{ $statusLabels[$request->status] ?? $request->status }}</span>
                    <span class="px-2 py-0.5 text-[.6rem] font-semibold uppercase tracking-wider {{ $urgencyColors[$request->urgency] ?? '' }}">{{ $request->urgency }}</span>
                </div>
                <h1 class="elite-h1 text-3xl ink-primary">{{ $request->title }}</h1>
                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm text-stone-500 font-serif">
                    <span>Pemohon: {{ $request->requester?->name ?? '-' }}</span>
                    @if($request->department)
                        <span>Departemen: {{ $request->department }}</span>
                    @endif
                    <span>{{ $request->created_at->translatedFormat('d M Y H:i') }}</span>
                </div>
            </div>

            <div class="flex gap-2 flex-wrap">
                @if($request->status === 'draft')
                    <form method="POST" action="{{ route('admin.procurement.submit', $request->id) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-elite text-xs">Submit Persetujuan</button>
                    </form>
                    <a href="{{ route('admin.procurement.edit', $request->id) }}" class="btn-elite-ghost text-xs">Edit</a>
                @endif
                @if($request->status === 'approved')
                    <form method="POST" action="{{ route('admin.procurement.mark-ordered', $request->id) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-elite text-xs">Tandai Dipesan</button>
                    </form>
                @endif
                @if($request->status === 'ordered')
                    <button onclick="document.getElementById('receiveForm').classList.toggle('hidden')" class="btn-elite text-xs">Terima Barang</button>
                @endif
            </div>
        </div>

        @if($request->description)
            <div class="mt-4 pt-4 border-t border-stone-100">
                <p class="font-serif text-sm text-stone-600">{{ $request->description }}</p>
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4 pt-4 border-t border-stone-100 text-center">
            <div>
                <div class="elite-kicker text-[.55rem]">Budget</div>
                <div class="font-mono font-bold text-lg text-[var(--c-primary)]">Rp {{ number_format($request->estimated_budget / 100, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="elite-kicker text-[.55rem]">Total Item</div>
                <div class="font-mono font-bold text-lg">{{ $request->items->count() }}</div>
            </div>
            <div>
                <div class="elite-kicker text-[.55rem]">Total Estimasi</div>
                <div class="font-mono font-bold text-lg">Rp {{ number_format($totalEst / 100, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="elite-kicker text-[.55rem]">Total Aktual</div>
                @php $totalAct = $request->totalActual(); @endphp
                <div class="font-mono font-bold text-lg">{{ $totalAct !== null ? 'Rp ' . number_format($totalAct / 100, 0, ',', '.') : '-' }}</div>
            </div>
        </div>
    </div>

    {{-- Items table --}}
    <div class="elite-card p-6 mb-6">
        <h3 class="elite-h3 text-xl ink-primary mb-4">Item Barang / Jasa</h3>
        <div class="overflow-x-auto">
            <table class="table-elite w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="text-left">Nama Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Unit</th>
                        <th class="text-right">Harga Estimasi</th>
                        <th class="text-right">Harga Aktual</th>
                        <th class="text-right">Subtotal Est</th>
                        <th class="text-center">Supplier</th>
                        <th class="text-center">Qty Diterima</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($request->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="font-serif font-semibold">{{ $item->item_name }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                        <td class="text-center">{{ $item->unit ?: '-' }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($item->estimated_unit_price / 100, 0, ',', '.') }}</td>
                        <td class="text-right font-mono">{{ $item->actual_unit_price !== null ? 'Rp ' . number_format($item->actual_unit_price / 100, 0, ',', '.') : '-' }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($item->subtotalEstimated() / 100, 0, ',', '.') }}</td>
                        <td class="text-center text-sm">{{ $item->supplier?->name ?? $item->supplier_name ?? '-' }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($item->received_qty, 2), '0'), '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Receive form --}}
    @if($request->status === 'ordered')
    <div id="receiveForm" class="elite-card p-6 mb-6 hidden">
        <h3 class="elite-h3 text-xl ink-primary mb-4">Terima Barang</h3>
        <form method="POST" action="{{ route('admin.procurement.receive-items', $request->id) }}">
            @csrf
            <div class="space-y-3">
                @foreach($request->items as $item)
                <div class="flex items-center gap-4">
                    <span class="font-serif text-sm flex-1">{{ $item->item_name }} (pesan: {{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }})</span>
                    <input type="number" name="received_qty[{{ $item->id }}]" value="{{ $item->quantity }}" min="0" step="0.01"
                           class="w-32 px-3 py-2 border border-stone-300 text-sm font-mono bg-white focus:outline-none focus:border-[var(--c-primary)]">
                </div>
                @endforeach
            </div>
            <div class="mt-4 text-right">
                <button type="submit" class="btn-elite">Simpan Penerimaan</button>
            </div>
        </form>
    </div>
    @endif

    {{-- Approval chain --}}
    @if($request->approvals->count() > 0 || ($userApproval ?? null))
    <div class="elite-card p-6 mb-6">
        <h3 class="elite-h3 text-xl ink-primary mb-4">Alur Persetujuan</h3>
        <div class="space-y-2">
            @foreach($request->approvals as $step)
            @php
                $approved = $step->status === 'approved';
                $rejected = $step->status === 'rejected';
                $pending = $step->status === 'pending';
            @endphp
            <div class="flex items-center gap-3 p-3 {{ $approved ? 'bg-green-50' : ($rejected ? 'bg-red-50' : 'bg-stone-50') }}">
                <span class="font-mono text-sm w-12 text-center">{{ $step->step_order }}.</span>
                <span class="font-serif text-sm flex-1">{{ $step->approver?->name ?? 'User #'.$step->approver_id }}</span>
                <span class="px-2 py-0.5 text-[.6rem] font-semibold uppercase tracking-wider {{ $approved ? 'bg-green-100 text-green-700' : ($rejected ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $step->status }}</span>
                @if($step->decided_at)
                    <span class="text-[.6rem] text-stone-400">{{ $step->decided_at->translatedFormat('d M H:i') }}</span>
                @endif
            </div>
            @if($step->notes)
                <div class="ml-16 text-xs text-stone-500 font-serif mb-2">{{ $step->notes }}</div>
            @endif

            @if($pending && $step->approver_id === auth()->id())
                <div class="ml-16 mt-2 mb-3 p-3 bg-amber-50 border border-amber-200">
                    <form method="POST" action="{{ route('admin.procurement.decide-approval', $step->id) }}" class="flex items-end gap-3">
                        @csrf
                        <div class="flex-1">
                            <textarea name="notes" rows="2" placeholder="Catatan..." class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)] text-xs"></textarea>
                        </div>
                        <button name="decision" value="rejected" class="px-3 py-2 border border-red-300 text-xs font-semibold uppercase tracking-wider text-red-600 hover:bg-red-50 transition">Tolak</button>
                        <button name="decision" value="approved" class="btn-elite text-xs">Setujui</button>
                    </form>
                </div>
            @endif
            @endforeach
        </div>
    </div>
    @endif

    @if($request->rejected_reason)
    <div class="elite-card p-6 mb-6 border-l-4 border-red-500">
        <h4 class="elite-h3 text-lg text-red-800 mb-2">Ditolak</h4>
        <p class="font-serif text-sm text-red-700">{{ $request->rejected_reason }}</p>
    </div>
    @endif

    @if($request->notes && $request->status !== 'rejected')
    <div class="elite-card p-6 mb-6">
        <h4 class="elite-kicker mb-2">Catatan</h4>
        <p class="font-serif text-sm text-stone-600">{{ $request->notes }}</p>
    </div>
    @endif
</div>
@endsection
