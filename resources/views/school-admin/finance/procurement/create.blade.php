@extends('layouts.school-admin')
@section('title', isset($request) ? 'Edit Pengadaan' : 'Buat Pengadaan Baru')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    $isEdit = isset($request);
    $formAction = $isEdit
        ? route('admin.procurement.update', $request->id)
        : route('admin.procurement.store');
    $method = $isEdit ? 'PUT' : 'POST';

    $urgencyOptions = ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Mendesak'];
@endphp

<div class="max-w-4xl mx-auto" x-data="procurementForm(@json($isEdit ? $request->items->toArray() : []))">
    <div class="mb-6">
        <a href="{{ route('admin.procurement.index') }}" class="elite-kicker hover:underline">
            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
            Kembali ke daftar
        </a>
    </div>

    <h1 class="elite-h1 text-4xl ink-primary mb-6">{{ $isEdit ? 'Edit Pengadaan' : 'Buat Pengadaan Baru' }}</h1>

    <form method="POST" action="{{ $formAction }}" class="space-y-6">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="elite-card p-6 space-y-5">
            <h3 class="elite-h3 text-xl ink-primary mb-2">Informasi Permintaan</h3>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="elite-kicker block mb-1">Judul <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $isEdit ? $request->title : '') }}" required
                           class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Departemen / Unit</label>
                    <input type="text" name="department" value="{{ old('department', $isEdit ? $request->department : '') }}"
                           placeholder="Contoh: Tata Usaha, Kesiswaan"
                           class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                </div>
            </div>

            <div>
                <label class="elite-kicker block mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">{{ old('description', $isEdit ? $request->description : '') }}</textarea>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="elite-kicker block mb-1">Estimasi Budget (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="estimated_budget" value="{{ old('estimated_budget', $isEdit ? $request->estimated_budget / 100 : 0) }}" min="0" step="1" required
                           class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Kategori Anggaran</label>
                    <select name="budget_category_id"
                            class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                        <option value="">— Tidak Ditentukan —</option>
                        @foreach($budgetCategories ?? [] as $cat)
                            <option value="{{ $cat->id }}" {{ old('budget_category_id', $isEdit ? $request->budget_category_id : '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Urgensi <span class="text-red-500">*</span></label>
                    <select name="urgency" required
                            class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                        @foreach($urgencyOptions as $v => $l)
                            <option value="{{ $v }}" {{ old('urgency', $isEdit ? $request->urgency : 'medium') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Catatan</label>
                    <input type="text" name="notes" value="{{ old('notes', $isEdit ? $request->notes : '') }}"
                           class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="elite-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="elite-h3 text-xl ink-primary">Item Barang / Jasa</h3>
                <button type="button" @click="addItem()" class="btn-elite-ghost text-xs flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Item
                </button>
            </div>

            <template x-if="items.length === 0">
                <p class="font-serif text-sm text-stone-500 text-center py-6">Belum ada item. Klik "Tambah Item".</p>
            </template>

            <div class="space-y-3" x-show="items.length > 0">
                <template x-for="(item, index) in items" :key="index">
                    <div class="bg-stone-50 p-4 border border-stone-200 relative">
                        <div class="grid sm:grid-cols-12 gap-3 items-end">
                            <div class="sm:col-span-4">
                                <label class="elite-kicker block mb-1">Nama Item <span class="text-red-500">*</span></label>
                                <input type="text" :name="'items['+index+'][item_name]'" x-model="item.item_name" required
                                       class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="elite-kicker block mb-1">Qty <span class="text-red-500">*</span></label>
                                <input type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" min="0.01" step="0.01" required
                                       class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="elite-kicker block mb-1">Unit</label>
                                <input type="text" :name="'items['+index+'][unit]'" x-model="item.unit" placeholder="pcs"
                                       class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="elite-kicker block mb-1">Harga Satuan (Rp)<span class="text-red-500">*</span></label>
                                <input type="number" :name="'items['+index+'][estimated_unit_price]'" x-model="item.estimated_unit_price" min="0" step="1" required
                                       class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="elite-kicker block mb-1">Supplier (opsional)</label>
                                <select :name="'items['+index+'][supplier_id]'" x-model="item.supplier_id"
                                        class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                                    <option value="">Pilih Supplier</option>
                                    @foreach($suppliers ?? [] as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-1 text-right">
                                <button type="button" @click="removeItem(index)" class="text-xs text-red-500 hover:text-red-700 font-semibold">Hapus</button>
                            </div>
                        </div>
                        <div class="text-right mt-2" x-show="item.quantity > 0 && item.estimated_unit_price > 0">
                            <span class="text-[.6rem] uppercase tracking-wider text-stone-400">Subtotal:</span>
                            <span class="font-mono text-sm font-semibold ink-primary" x-text="formatRupiah(item.quantity * item.estimated_unit_price)"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-4 pt-4 border-t border-stone-200 text-right" x-show="items.length > 0">
                <span class="elite-kicker mr-2">Total:</span>
                <span class="font-mono text-lg font-bold text-[var(--c-primary)]" x-text="formatRupiah(totalAmount())"></span>
            </div>
        </div>

        <div class="flex justify-between pt-4">
            <a href="{{ route('admin.procurement.index') }}" class="btn-elite-ghost">Batal</a>
            <button type="submit" class="btn-elite text-base px-8">
                {{ $isEdit ? 'Simpan Perubahan' : 'Buat Permintaan' }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function procurementForm(existingItems) {
    const mapped = existingItems.map(i => ({
        item_name: i.item_name || '',
        quantity: parseFloat(i.quantity) || 1,
        unit: i.unit || '',
        estimated_unit_price: parseInt(i.estimated_unit_price) || 0,
        supplier_id: i.supplier_id || '',
        supplier_name: i.supplier_name || '',
    }));

    return {
        items: mapped.length > 0 ? mapped : [
            { item_name: '', quantity: 1, unit: '', estimated_unit_price: 0, supplier_id: '', supplier_name: '' }
        ],
        addItem() {
            this.items.push({ item_name: '', quantity: 1, unit: '', estimated_unit_price: 0, supplier_id: '', supplier_name: '' });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        totalAmount() {
            let total = 0;
            this.items.forEach(i => {
                total += (parseFloat(i.quantity) || 0) * (parseInt(i.estimated_unit_price) || 0);
            });
            return total;
        },
        formatRupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(amount));
        }
    };
}
</script>
@endpush

@endsection
