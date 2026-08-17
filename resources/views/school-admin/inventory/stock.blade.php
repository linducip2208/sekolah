@extends('layouts.school-admin')
@section('title', 'Stok Inventori')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Inventarium</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Stok Inventori</h1>
    <div class="elite-rule"></div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Item</summary>
    <form method="POST" action="{{ route('admin.inventory.stock.store') }}" class="px-5 py-4 border-t border-rule grid md:grid-cols-3 gap-2">@csrf
        <input name="name" required maxlength="200" placeholder="Nama item" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <input name="sku" maxlength="50" placeholder="SKU / kode" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <input name="unit" maxlength="20" placeholder="Satuan (pcs/box/dll)" value="pcs" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <input type="number" name="quantity" min="0" placeholder="Stok awal" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <input type="number" name="min_quantity" min="0" placeholder="Stok minimum" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <input name="location" maxlength="100" placeholder="Lokasi" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <div class="md:col-span-3"><button class="btn-elite">Simpan</button></div>
    </form>
</details>

<div class="bg-white border border-rule overflow-hidden mb-6">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Item</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Stok</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Min</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Lokasi</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($items as $item)
            <tr class="border-t border-rule {{ $item->isLowStock() ? 'bg-red-50' : '' }}">
                <td class="px-4 py-3">
                    <div class="font-serif">{{ $item->name }}</div>
                    <div class="text-xs text-gray-400 font-mono">{{ $item->sku }}</div>
                </td>
                <td class="px-4 py-3 text-center font-mono">{{ $item->quantity }} {{ $item->unit }}</td>
                <td class="px-4 py-3 text-center font-mono text-xs text-gray-500">{{ $item->min_quantity }}</td>
                <td class="px-4 py-3 text-xs">{{ $item->location ?? '—' }}</td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <a href="{{ route('admin.inventory.stock.index', ['item_id' => $item->id]) }}" class="text-xs underline ink-secondary">Mutasi</a>
                    <details class="inline-block ml-2 text-left"><summary class="text-xs underline cursor-pointer ink-secondary">Aksi</summary>
                        <div class="mt-2 grid gap-2">
                            <form method="POST" action="{{ route('admin.inventory.stock.in', $item) }}" class="flex gap-1">@csrf
                                <input type="number" name="quantity" min="1" required placeholder="Masuk" class="w-20 border-2 border-rule px-2 py-1 font-mono text-xs">
                                <button class="text-xs text-green-700">Masuk</button>
                            </form>
                            <form method="POST" action="{{ route('admin.inventory.stock.out', $item) }}" class="flex gap-1">@csrf
                                <input type="number" name="quantity" min="1" required placeholder="Keluar" class="w-20 border-2 border-rule px-2 py-1 font-mono text-xs">
                                <button class="text-xs text-red-700">Keluar</button>
                            </form>
                            <form method="POST" action="{{ route('admin.inventory.stock.opname', $item) }}" class="flex gap-1">@csrf
                                <input type="number" name="actual_qty" min="0" required placeholder="Opname" class="w-20 border-2 border-rule px-2 py-1 font-mono text-xs">
                                <button class="text-xs ink-secondary">Opname</button>
                            </form>
                            <form method="POST" action="{{ route('admin.inventory.stock.destroy', $item) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                        </div>
                    </details>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada item stok.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($itemId)
<div class="bg-white border border-rule overflow-hidden">
    <div class="px-4 py-3 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Riwayat Mutasi</div>
    <table class="w-full text-sm">
        <tbody>
            @forelse($movements as $m)
            <tr class="border-b border-rule">
                <td class="px-4 py-2 text-xs">{{ $m->created_at->format('d M Y H:i') }}</td>
                <td class="px-4 py-2 text-xs">{{ $m->type }}</td>
                <td class="px-4 py-2 text-right font-mono text-xs {{ $m->quantity >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ $m->quantity >= 0 ? '+' : '' }}{{ $m->quantity }}</td>
                <td class="px-4 py-2 text-xs text-gray-500">{{ $m->note }}</td>
                <td class="px-4 py-2 text-xs text-gray-400">{{ $m->creator?->name }}</td>
            </tr>
            @empty
            <tr><td class="p-6 text-center text-gray-400 italic font-serif text-xs">Belum ada mutasi.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-2">{{ $movements->links() }}</div>
</div>
@endif

@endsection
