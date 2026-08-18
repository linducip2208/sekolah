@extends('super-admin.layout')

@section('title', 'Kupon')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Kupon Diskon</h2>
            <p class="text-sm text-gray-500 mt-0.5">Kelola kupon untuk langganan sekolah</p>
        </div>
        <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="btn-elite">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Kupon
        </button>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode/deskripsi..."
               class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-64">
        <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
        </select>
        <button type="submit" class="btn-elite bg-gray-600 hover:bg-gray-700">Filter</button>
    </form>

    {{-- Validate Coupon --}}
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100" x-data="{ show: false }">
        <button @click="show = !show" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Validasi Kupon
        </button>
        <div x-show="show" x-transition class="mt-3">
            <form action="{{ route('super.coupons.validate') }}" method="POST" class="flex flex-wrap gap-2 items-end">
                @csrf
                <div>
                    <label class="text-xs font-medium text-gray-500">Kode Kupon</label>
                    <input type="text" name="code" required placeholder="DISKON20"
                           class="mt-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 uppercase">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Jumlah (cents)</label>
                    <input type="number" name="amount" required value="1000000" min="0"
                           class="mt-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="btn-elite">Cek</button>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Kode</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Deskripsi</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Tipe</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Nilai</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Penggunaan</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Berlaku</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Status</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($coupons as $coupon)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-3 font-mono font-bold text-gray-900">{{ $coupon->code }}</td>
                        <td class="px-5 py-3 text-gray-600 max-w-[200px] truncate">{{ $coupon->description ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $coupon->discount_type === 'percentage' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $coupon->discount_type === 'percentage' ? 'Persen' : 'Flat' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 font-semibold">{{ $coupon->discount_label }}</td>
                        <td class="px-5 py-3">
                            {{ $coupon->used_count }}{{ $coupon->max_uses ? " / {$coupon->max_uses}" : ' / ∞' }}
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs">
                            {{ $coupon->valid_from?->format('d M Y') ?? '-' }} — {{ $coupon->valid_until?->format('d M Y') ?? '∞' }}
                        </td>
                        <td class="px-5 py-3">
                            <form action="{{ route('super.coupons.toggle', $coupon) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold cursor-pointer {{ $coupon->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                    {{ $coupon->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-right space-x-1">
                            <form action="{{ route('super.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kupon ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-gray-400">Belum ada kupon.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $coupons->links() }}
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div id="createModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40" x-data="{ open: true }" @keydown.escape.window="document.getElementById('createModal').classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-6" @click.outside="document.getElementById('createModal').classList.add('hidden')">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Kupon Baru</h3>
        <form action="{{ route('super.coupons.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Kode Kupon *</label>
                    <input type="text" name="code" required maxlength="50" placeholder="DISKON20"
                           class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 uppercase">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Tipe Diskon *</label>
                    <select name="discount_type" class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="percentage">Persen (%)</option>
                        <option value="fixed">Flat (Rp)</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Deskripsi</label>
                <input type="text" name="description" maxlength="500" placeholder="Diskon awal tahun"
                       class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Nilai Diskon *</label>
                    <input type="number" name="discount_value" required min="0" value="10"
                           class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-0.5">Persen (0-100) atau cents untuk flat</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Maks. Penggunaan</label>
                    <input type="number" name="max_uses" min="1" placeholder="∞ (unlimited)"
                           class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Berlaku Dari</label>
                    <input type="date" name="valid_from"
                           class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Berlaku Sampai</label>
                    <input type="date" name="valid_until"
                           class="mt-1 w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label class="text-sm text-gray-700">Aktif</label>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</button>
                <button type="submit" class="btn-elite">Simpan Kupon</button>
            </div>
        </form>
    </div>
</div>
@endsection
