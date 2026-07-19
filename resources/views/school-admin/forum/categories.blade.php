@extends('layouts.school-admin')
@section('title', 'Kategori Forum')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="flex justify-between items-end mb-7">
    <div>
        <div class="elite-kicker mb-2">Categoriae</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Kategori Forum</h1>
        <div class="elite-rule"></div>
    </div>
</div>

{{-- Create Form --}}
<div class="bg-white border border-rule p-6 mb-6">
    <h3 class="elite-h3 text-base ink-primary mb-4">Buat Kategori Baru</h3>
    <form method="POST" action="{{ route('admin.forum.categories.store') }}">
        @csrf
        <div class="grid md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Nama</label>
                <input name="name" required maxlength="100" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Urutan</label>
                <input type="number" name="sort_order" min="0" value="0" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div class="flex items-end pb-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span class="text-sm">Aktif</span>
                </label>
            </div>
        </div>
        <div class="mb-4">
            <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
            <textarea name="description" rows="2" maxlength="500" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        </div>
        <button class="btn-elite text-xs">Tambah Kategori</button>
    </form>
</div>

{{-- Categories List --}}
<div class="bg-white border border-rule">
    <div class="table-elite overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-rule text-left">
                    <th class="py-3 px-4 w-10">#</th>
                    <th class="py-3 px-4">Nama</th>
                    <th class="py-3 px-4">Deskripsi</th>
                    <th class="py-3 px-4 text-center">Topik</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $c)
                    <tr class="border-b border-rule/40">
                        <td class="py-3 px-4 text-gray-400">{{ $c->sort_order }}</td>
                        <td class="py-3 px-4 font-semibold">{{ $c->name }}</td>
                        <td class="py-3 px-4 text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($c->description, 60) }}</td>
                        <td class="py-3 px-4 text-center">{{ $c->topics_count }}</td>
                        <td class="py-3 px-4 text-center">
                            @if($c->is_active)
                                <span class="text-xs text-green-700 font-semibold">Aktif</span>
                            @else
                                <span class="text-xs text-gray-400">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex gap-2 text-xs">
                                <button onclick="toggleEdit({{ $c->id }})" class="underline ink-secondary hover:ink-accent">Edit</button>
                                <form method="POST" action="{{ route('admin.forum.categories.destroy', $c) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-700 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr id="edit-row-{{ $c->id }}" style="display:none;" class="border-b border-rule/40 bg-gray-50">
                        <td colspan="6" class="py-4 px-4">
                            <form method="POST" action="{{ route('admin.forum.categories.update', $c) }}">
                                @csrf @method('PUT')
                                <div class="grid md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="elite-kicker text-[.55rem] block mb-1">Nama</label>
                                        <input name="name" required value="{{ $c->name }}" class="w-full border-2 border-rule px-3 py-1.5 font-serif text-sm">
                                    </div>
                                    <div>
                                        <label class="elite-kicker text-[.55rem] block mb-1">Urutan</label>
                                        <input type="number" name="sort_order" min="0" value="{{ $c->sort_order }}" class="w-full border-2 border-rule px-3 py-1.5 font-serif text-sm">
                                    </div>
                                    <div class="flex items-end gap-2 pb-1">
                                        <label class="flex items-center gap-1">
                                            <input type="checkbox" name="is_active" value="1" @checked($c->is_active)>
                                            <span class="text-xs">Aktif</span>
                                        </label>
                                        <button class="btn-elite text-xs px-3 py-1.5">Simpan</button>
                                        <button type="button" onclick="toggleEdit({{ $c->id }})" class="text-xs underline text-gray-500">Batal</button>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <input name="description" value="{{ $c->description }}" placeholder="Deskripsi" class="w-full border-2 border-rule px-3 py-1.5 font-serif text-sm">
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-gray-500 italic font-serif">Belum ada kategori forum.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
function toggleEdit(id) {
    const row = document.getElementById('edit-row-' + id);
    row.style.display = row.style.display === 'none' ? '' : 'none';
}
</script>
@endpush

@endsection
