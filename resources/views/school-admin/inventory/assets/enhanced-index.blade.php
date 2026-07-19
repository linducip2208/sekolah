@extends('layouts.school-admin')
@section('title', 'Manajemen Aset Lanjutan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Operasional</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Manajemen Aset</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="elite-card p-4 text-center">
        <div class="font-display text-2xl ink-primary">{{ $assets->total() }}</div>
        <div class="elite-kicker text-[.55rem]">Total Aset</div>
    </div>
    @foreach(['excellent' => 'Sangat Baik', 'good' => 'Baik', 'damaged' => 'Rusak'] as $ck => $cl)
    <div class="elite-card p-4 text-center">
        <div class="font-display text-2xl ink-primary">{{ \App\Models\Inventory\Asset::where('school_id', auth()->user()->school_id)->where('condition', $ck)->count() }}</div>
        <div class="elite-kicker text-[.55rem]">Kondisi {{ $cl }}</div>
    </div>
    @endforeach
</div>

<button onclick="document.getElementById('addAssetForm').classList.toggle('hidden')" class="btn-elite-gold mb-4">+ Tambah Aset</button>

<div id="addAssetForm" class="hidden elite-card p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Tambah Aset Baru</h3>
    <form method="POST" action="{{ route('admin.inventory.enhanced.store') }}" class="grid md:grid-cols-3 gap-4">
        @csrf
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Kategori *</label>
            <select name="asset_category_id" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Nama Aset *</label>
            <input type="text" name="name" required class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Kode Aset</label>
            <input type="text" name="asset_code" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Beli</label>
            <input type="date" name="purchase_date" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Harga Beli (Rp)</label>
            <input type="number" name="purchase_price" min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Umur Ekonomis (thn)</label>
            <input type="number" name="useful_life_years" min="1" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Nilai Sisa</label>
            <input type="number" name="salvage_value" min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Metode Penyusutan</label>
            <select name="depreciation_method" class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="straight_line">Garis Lurus</option>
                <option value="double_declining">Saldo Menurun Ganda</option>
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Kondisi</label>
            <select name="condition" class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($conditions as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Supplier</label>
            <input type="text" name="supplier_name" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Garansi Sampai</label>
            <input type="date" name="warranty_expiry_date" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Lokasi</label>
            <input type="text" name="location" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Maintenance Berikutnya</label>
            <input type="date" name="next_maintenance_date" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Deskripsi</label>
            <input type="text" name="description" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-3">
            <button type="submit" class="btn-elite">Simpan Aset</button>
        </div>
    </form>
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-4 bg-white border border-rule p-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aset..." class="border-2 border-rule px-3 py-2 text-sm flex-1 min-w-[150px]">
    <select name="condition" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Kondisi —</option>
        @foreach($conditions as $k => $l)<option value="{{ $k }}" {{ request('condition') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach
    </select>
    <select name="category" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Kategori —</option>
        @foreach($categories as $c)<option value="{{ $c->id }}" {{ request('category') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Filter</button>
</form>

<div class="elite-card overflow-hidden">
    <div class="table-scroll">
    <table class="table-elite w-full text-sm">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Penyusutan/Bln</th>
                <th>Nilai Buku</th>
                <th>Kondisi</th>
                <th>QR</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
            <tr>
                <td class="font-mono text-[.65rem]">{{ $asset->asset_code ?? '—' }}</td>
                <td>
                    <span class="font-serif font-semibold text-sm">{{ $asset->name }}</span>
                </td>
                <td class="text-xs">{{ $asset->category?->name }}</td>
                <td class="font-mono text-xs">Rp {{ number_format($asset->purchase_price, 0, ',', '.') }}</td>
                <td class="font-mono text-xs">Rp {{ number_format($asset->monthly_depreciation, 0, ',', '.') }}</td>
                <td class="font-mono text-xs">Rp {{ number_format($depService->currentBookValue($asset), 0, ',', '.') }}</td>
                <td>
                    <span class="text-[.6rem] uppercase px-2 py-0.5 rounded
                        {{ $asset->condition === 'excellent' ? 'bg-green-100 text-green-800' : ($asset->condition === 'good' ? 'bg-blue-100 text-blue-800' : ($asset->condition === 'damaged' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                        {{ $conditions[$asset->condition] ?? $asset->condition }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.inventory.qr-print', $asset) }}" target="_blank" class="text-xs underline ink-accent">Cetak QR</a>
                </td>
                <td class="text-right whitespace-nowrap">
                    <button onclick="editAsset({{ $asset->id }}, {{ $asset->asset_category_id }}, '{{ e($asset->name) }}', '{{ e($asset->asset_code) }}', '{{ e($asset->description) }}', '{{ $asset->purchase_date?->format('Y-m-d') }}', {{ $asset->purchase_price }}, {{ $asset->useful_life_years }}, {{ $asset->salvage_value }}, '{{ $asset->depreciation_method }}', '{{ $asset->condition }}', '{{ e($asset->supplier_name) }}', '{{ $asset->warranty_expiry_date?->format('Y-m-d') }}', '{{ e($asset->location) }}', '{{ $asset->next_maintenance_date?->format('Y-m-d') }}')" class="text-xs underline ink-secondary mr-2">Edit</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="p-10 text-center text-gray-500 italic font-serif">Belum ada aset.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-4">{{ $assets->links() }}</div>

<div id="editAssetForm" class="hidden elite-card p-6 mt-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Edit Aset</h3>
    <form id="editAssetTag" method="POST" class="grid md:grid-cols-3 gap-4">
        @csrf @method('PUT')
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Kategori *</label>
            <select name="asset_category_id" id="ea_category" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div><label class="block elite-kicker text-[.6rem] mb-1">Nama *</label><input type="text" name="name" id="ea_name" required class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
        <div><label class="block elite-kicker text-[.6rem] mb-1">Kode</label><input type="text" name="asset_code" id="ea_code" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
        <div><label class="block elite-kicker text-[.6rem] mb-1">Deskripsi</label><input type="text" name="description" id="ea_desc" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
        <div><label class="block elite-kicker text-[.6rem] mb-1">Tgl Beli</label><input type="date" name="purchase_date" id="ea_pdate" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
        <div><label class="block elite-kicker text-[.6rem] mb-1">Harga</label><input type="number" name="purchase_price" id="ea_price" min="0" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
        <div><label class="block elite-kicker text-[.6rem] mb-1">Umur (thn)</label><input type="number" name="useful_life_years" id="ea_life" min="1" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
        <div><label class="block elite-kicker text-[.6rem] mb-1">Nilai Sisa</label><input type="number" name="salvage_value" id="ea_salvage" min="0" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Metode</label>
            <select name="depreciation_method" id="ea_method" class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="straight_line">Garis Lurus</option>
                <option value="double_declining">Saldo Menurun</option>
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Kondisi</label>
            <select name="condition" id="ea_condition" class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($conditions as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
            </select>
        </div>
        <div><label class="block elite-kicker text-[.6rem] mb-1">Supplier</label><input type="text" name="supplier_name" id="ea_supplier" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
        <div><label class="block elite-kicker text-[.6rem] mb-1">Garansi</label><input type="date" name="warranty_expiry_date" id="ea_warranty" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
        <div><label class="block elite-kicker text-[.6rem] mb-1">Lokasi</label><input type="text" name="location" id="ea_location" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
        <div><label class="block elite-kicker text-[.6rem] mb-1">Maintenance</label><input type="date" name="next_maintenance_date" id="ea_maint" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
        <div class="md:col-span-3">
            <button type="submit" class="btn-elite">Perbarui</button>
            <button type="button" onclick="document.getElementById('editAssetForm').classList.add('hidden')" class="btn-elite-ghost ml-2">Batal</button>
        </div>
    </form>
</div>

<script>
function editAsset(id, cat, name, code, desc, pdate, price, life, salvage, method, cond, supplier, warranty, loc, maint) {
    document.getElementById('addAssetForm').classList.add('hidden');
    const f = document.getElementById('editAssetForm'); f.classList.remove('hidden'); f.scrollIntoView({behavior:'smooth'});
    document.getElementById('editAssetTag').action = '{{ route('admin.inventory.enhanced.update', ['asset' => '__ID__']) }}'.replace('__ID__', id);
    document.getElementById('ea_category').value = cat;
    document.getElementById('ea_name').value = name;
    document.getElementById('ea_code').value = code;
    document.getElementById('ea_desc').value = desc;
    document.getElementById('ea_pdate').value = pdate;
    document.getElementById('ea_price').value = price;
    document.getElementById('ea_life').value = life;
    document.getElementById('ea_salvage').value = salvage;
    document.getElementById('ea_method').value = method;
    document.getElementById('ea_condition').value = cond;
    document.getElementById('ea_supplier').value = supplier;
    document.getElementById('ea_warranty').value = warranty;
    document.getElementById('ea_location').value = loc;
    document.getElementById('ea_maint').value = maint;
}
</script>
@endsection
