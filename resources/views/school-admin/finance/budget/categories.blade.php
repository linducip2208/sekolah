@extends('layouts.school-admin')
@section('title', 'Kategori Anggaran')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Categoriae Pecuniae</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Kategori Anggaran</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Struktur kode rekening RKAS: Pendapatan & Belanja.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Kategori</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.budget.categories.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Kode</label>
                    <input name="code" required maxlength="20" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="1.1">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama</label>
                    <input name="name" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Belanja Pegawai">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tipe</label>
                    <select name="type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="income">Pendapatan</option>
                        <option value="expense">Belanja</option>
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Induk (Opsional)</label>
                    <select name="parent_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— Tanpa Induk —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->code }} — {{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Keterangan</label>
                    <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        @foreach($categories as $cat)
        <div class="mb-6">
            <div class="bg-white border border-rule overflow-hidden">
                <div class="px-5 py-3 flex justify-between items-center" style="background:var(--c-primary)">
                    <div>
                        <span class="font-mono text-xs text-[var(--c-accent)]">{{ $cat->code }}</span>
                        <span class="elite-h3 text-base text-white ml-2">{{ $cat->name }}</span>
                        <span class="elite-kicker text-[.55rem] ml-2" style="{{ $cat->type === 'income' ? 'color:#86efac' : 'color:#fca5a5' }}">{{ $cat->type === 'income' ? 'PENDAPATAN' : 'BELANJA' }}</span>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="editCat({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ $cat->code }}', '{{ $cat->type }}', '{{ $cat->parent_id }}', '{{ addslashes($cat->description ?? '') }}')" class="text-xs text-[var(--c-accent)] hover:underline">Edit</button>
                        <form method="POST" action="{{ route('admin.budget.categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
                @if($cat->children->isNotEmpty())
                <table class="w-full text-sm">
                    <thead><tr class="bg-gray-50">
                        <th class="text-left px-4 py-2 elite-kicker text-[.55rem]">Kode</th>
                        <th class="text-left px-4 py-2 elite-kicker text-[.55rem]">Sub-Kategori</th>
                        <th class="text-left px-4 py-2 elite-kicker text-[.55rem]">Tipe</th>
                        <th class="px-4 py-2"></th>
                    </tr></thead>
                    <tbody>
                        @foreach($cat->children as $child)
                        <tr class="border-t border-rule">
                            <td class="px-4 py-2 font-mono text-xs">{{ $child->code }}</td>
                            <td class="px-4 py-2 font-serif text-sm">{{ $child->name }}</td>
                            <td class="px-4 py-2"><span class="text-xs {{ $child->type === 'income' ? 'text-green-700' : 'text-red-700' }}">{{ $child->type === 'income' ? 'Pendapatan' : 'Belanja' }}</span></td>
                            <td class="px-4 py-2 text-right">
                                <button onclick="editCat({{ $child->id }}, '{{ addslashes($child->name) }}', '{{ $child->code }}', '{{ $child->type }}', '{{ $child->parent_id }}', '{{ addslashes($child->description ?? '') }}')" class="text-xs text-[var(--c-accent)] hover:underline mr-2">Edit</button>
                                <form method="POST" action="{{ route('admin.budget.categories.destroy', $child) }}" class="inline" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="px-4 py-3 text-xs text-gray-500 italic font-serif">Belum ada sub-kategori.</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Edit Modal --}}
<div x-data="{ open: false, id: null, name: '', code: '', type: 'expense', parentId: '', desc: '' }" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(11,29,58,.75);" @click.self="open=false">
    <div class="bg-white w-full max-w-md p-6 border border-rule shadow-2xl">
        <h3 class="elite-h3 text-base ink-primary mb-4">Edit Kategori</h3>
        <form method="POST" :action="'/admin/budget/categories/' + id" class="space-y-3">
            @csrf @method('PUT')
            <input type="hidden" name="parent_id" x-model="parentId">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Kode</label>
                <input name="code" x-model="code" required maxlength="20" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Nama</label>
                <input name="name" x-model="name" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Tipe</label>
                <select name="type" x-model="type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="income">Pendapatan</option>
                    <option value="expense">Belanja</option>
                </select>
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Keterangan</label>
                <textarea name="description" x-model="desc" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
            </div>
            <div class="flex gap-2">
                <button class="btn-elite flex-1" style="padding:.6rem;font-size:.65rem;">Simpan</button>
                <button type="button" @click="open=false" class="btn-elite-ghost flex-1" style="padding:.6rem;font-size:.65rem;">Batal</button>
            </div>
        </form>
    </div>
</div>
<script>
function editCat(id, name, code, type, parentId, desc) {
    var d = document.querySelector('[x-data]').__x;
    d.$data.open = true;
    d.$data.id = id;
    d.$data.name = name;
    d.$data.code = code;
    d.$data.type = type;
    d.$data.parentId = parentId || '';
    d.$data.desc = desc;
}
</script>

@endsection
