@extends('layouts.school-admin')
@section('title', 'Item Anggaran')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Linea Rationis</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Item Anggaran</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Rincian pos anggaran per kategori rekening.</p>
</div>

<div class="mb-4 flex flex-wrap gap-3 items-center">
    <a href="{{ route('admin.budget.items.index') }}" class="text-xs {{ !request()->academic_year_id && !request()->status && !request()->category_id ? 'text-[var(--c-accent)] font-bold' : 'text-gray-500' }}">Semua</a>
    @foreach($academicYears as $ay)
        <a href="?academic_year_id={{ $ay->id }}" class="text-xs {{ request()->academic_year_id == $ay->id ? 'text-[var(--c-accent)] font-bold' : 'text-gray-500' }}">{{ $ay->name }}</a>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Item</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.budget.items.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama Item</label>
                    <input name="name" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Gaji Guru Honor">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Kategori</label>
                    <select name="budget_category_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">Pilih...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->code }} — {{ $cat->name }} ({{ $cat->type === 'income' ? 'Pendapatan' : 'Belanja' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tahun Ajaran</label>
                    <select name="academic_year_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— Semua —</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Jumlah Rencana (Rp)</label>
                    <input type="number" step="1000" min="0" name="planned_amount_rp" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="5000000">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Status</label>
                    <select name="status" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="planned">Planned</option>
                        <option value="approved">Approved</option>
                        <option value="revised">Revised</option>
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <div class="px-5 py-3 border-b border-rule flex justify-between items-center">
                <div class="font-serif text-sm text-gray-600">{{ $items->total() }} item</div>
                <a href="{{ route('admin.budget.export', ['type' => 'items'] + request()->all()) }}" class="text-xs text-[var(--c-accent)] hover:underline">Export CSV</a>
            </div>
            <div class="table-scroll">
                <table class="w-full text-sm">
                    <thead class="bg-[var(--c-primary)] text-white">
                        <tr>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama Item</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kategori</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Rencana</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Realisasi</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">%</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr class="border-t border-rule">
                                <td class="px-4 py-3 font-serif font-semibold ink-primary">
                                    {{ $item->name }}
                                    <div class="text-xs text-gray-500 font-sans">{{ $item->academicYear?->name ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs">{{ $item->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $item->planned_amount_rupiah }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $item->actual_amount_rupiah }}</td>
                                <td class="px-4 py-3">
                                    <div class="w-16 bg-gray-200 h-1.5">
                                        <div class="h-1.5 {{ $item->progress_percent >= 100 ? 'bg-green-600' : 'bg-[var(--c-accent)]' }}" style="width:{{ $item->progress_percent }}%"></div>
                                    </div>
                                    <span class="text-[.6rem]">{{ $item->progress_percent }}%</span>
                                </td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('admin.budget.items.toggle', $item) }}" class="inline">
                                        @csrf
                                        <button class="text-xs {{ $item->status === 'approved' ? 'text-green-700' : ($item->status === 'revised' ? 'text-amber-600' : 'text-gray-500') }} hover:underline">
                                            {{ $item->status === 'planned' ? 'Planned' : ($item->status === 'approved' ? 'Approved' : 'Revised') }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex gap-2 justify-end">
                                        <button onclick="editItem({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->budget_category_id }}, {{ $item->academic_year_id ?? 'null' }}, {{ $item->planned_amount / 100 }}, '{{ $item->status }}', '{{ addslashes($item->description ?? '') }}')" class="text-xs text-[var(--c-accent)] hover:underline">Edit</button>
                                        <form method="POST" action="{{ route('admin.budget.items.destroy', $item) }}" class="inline" onsubmit="return confirm('Hapus item?')">
                                            @csrf @method('DELETE')
                                            <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada item anggaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-rule">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div x-data="{ open: false, id: null, name: '', catId: '', yearId: '', amt: 0, status: 'planned', desc: '' }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(11,29,58,.75);" @click.self="open=false">
    <div class="bg-white w-full max-w-md p-6 border border-rule shadow-2xl">
        <h3 class="elite-h3 text-base ink-primary mb-4">Edit Item Anggaran</h3>
        <form method="POST" :action="'/admin/budget/items/' + id" class="space-y-3">
            @csrf @method('PUT')
            <input name="name" x-model="name" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Nama Item">
            <select name="budget_category_id" x-model="catId" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">Pilih Kategori...</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->code }} — {{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="academic_year_id" x-model="yearId" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— Semua Tahun Ajaran —</option>
                @foreach($academicYears as $ay)
                    <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                @endforeach
            </select>
            <input type="number" step="1000" min="0" name="planned_amount_rp" x-model="amt" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="Jumlah Rencana">
            <select name="status" x-model="status" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="planned">Planned</option>
                <option value="approved">Approved</option>
                <option value="revised">Revised</option>
            </select>
            <textarea name="description" x-model="desc" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Deskripsi"></textarea>
            <div class="flex gap-2">
                <button class="btn-elite flex-1" style="padding:.6rem;font-size:.65rem;">Simpan</button>
                <button type="button" @click="open=false" class="btn-elite-ghost flex-1" style="padding:.6rem;font-size:.65rem;">Batal</button>
            </div>
        </form>
    </div>
</div>
<script>
function editItem(id, name, catId, yearId, amt, status, desc) {
    var d = document.querySelector('[x-data]').__x;
    d.$data.open = true; d.$data.id = id; d.$data.name = name;
    d.$data.catId = catId; d.$data.yearId = yearId || '';
    d.$data.amt = amt; d.$data.status = status; d.$data.desc = desc;
}
</script>

@endsection
