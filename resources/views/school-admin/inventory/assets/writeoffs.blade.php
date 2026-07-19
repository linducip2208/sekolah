@extends('layouts.school-admin')
@section('title', 'Penghapusan Aset')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Operasional</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Penghapusan Aset</h1>
    <div class="elite-rule"></div>
</div>

<button onclick="document.getElementById('addWriteOffForm').classList.toggle('hidden')" class="btn-elite-gold mb-4">+ Ajukan Penghapusan</button>

<div id="addWriteOffForm" class="hidden elite-card p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Ajukan Penghapusan Aset</h3>
    <form method="POST" action="{{ route('admin.inventory.writeoffs.store') }}" class="grid md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Aset *</label>
            <select name="asset_id" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="">— Pilih Aset —</option>
                @foreach($assets as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Nilai Taksiran</label>
            <input type="number" name="estimated_value" min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block elite-kicker text-[.6rem] mb-1">Alasan Penghapusan *</label>
            <textarea name="reason" required rows="3" class="w-full border-2 border-rule px-3 py-2 text-sm"></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block elite-kicker text-[.6rem] mb-1">Kondisi Saat Dihapus</label>
            <input type="text" name="condition_at_writeoff" class="w-full border-2 border-rule px-3 py-2 text-sm" placeholder="Mis: sudah rusak total / hilang / dijual">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-elite">Ajukan</button>
        </div>
    </form>
</div>

<form method="GET" class="flex gap-3 mb-4 bg-white border border-rule p-4">
    <select name="status" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Semua Status —</option>
        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf</option>
        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Diajukan</option>
        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Filter</button>
</form>

<div class="elite-card overflow-hidden">
    <div class="table-scroll">
    <table class="table-elite w-full text-sm">
        <thead>
            <tr>
                <th>Aset</th>
                <th>Alasan</th>
                <th>Nilai Taksiran</th>
                <th>Kondisi</th>
                <th>Status</th>
                <th>Tgl Pengajuan</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($writeOffs as $wo)
            <tr>
                <td class="font-serif font-semibold text-sm">{{ $wo->asset?->name }}</td>
                <td class="text-xs max-w-xs truncate">{{ $wo->reason }}</td>
                <td class="font-mono text-xs">Rp {{ number_format($wo->estimated_value, 0, ',', '.') }}</td>
                <td class="text-xs">{{ $wo->condition_at_writeoff ?? '—' }}</td>
                <td>
                    <span class="text-[.6rem] uppercase px-2 py-0.5 rounded
                        {{ $wo->status === 'approved' ? 'bg-green-100 text-green-800' : ($wo->status === 'rejected' ? 'bg-red-100 text-red-800' : ($wo->status === 'submitted' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600')) }}">
                        {{ $wo->status === 'draft' ? 'Draf' : ($wo->status === 'submitted' ? 'Diajukan' : ($wo->status === 'approved' ? 'Disetujui' : 'Ditolak')) }}
                    </span>
                </td>
                <td class="text-xs">{{ $wo->request_date->format('d/m/Y') }}</td>
                <td class="text-right whitespace-nowrap">
                    @if($wo->status === 'draft')
                    <form method="POST" action="{{ route('admin.inventory.writeoffs.submit', $wo) }}" class="inline mr-1">
                        @csrf <button class="text-xs underline ink-accent">Ajukan</button>
                    </form>
                    @endif
                    @if($wo->status === 'submitted')
                    <form method="POST" action="{{ route('admin.inventory.writeoffs.approve', $wo) }}" class="inline mr-1">
                        @csrf <button class="text-xs underline text-green-600" onclick="return confirm('Setujui penghapusan?')">Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('admin.inventory.writeoffs.reject', $wo) }}" class="inline mr-1">
                        @csrf <button class="text-xs underline text-red-600" onclick="return confirm('Tolak penghapusan?')">Tolak</button>
                    </form>
                    @endif
                    @if($wo->approver)
                    <span class="text-[.55rem] text-gray-500">oleh {{ $wo->approver->name }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada pengajuan penghapusan.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-4">{{ $writeOffs->links() }}</div>
@endsection
