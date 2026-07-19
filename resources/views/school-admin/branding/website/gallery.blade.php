@extends('layouts.school-admin')
@section('title', 'Website — Galeri')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="max-w-5xl space-y-6" x-data="{ showAdd: false, showEdit: false, editItem: null }">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Galeri</h2>
            <p class="text-sm text-gray-600">Kelola foto galeri untuk ditampilkan di website sekolah.</p>
        </div>
        <button @click="showAdd=true" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Foto
        </button>
    </div>

    <div class="grid grid-cols-4 gap-4">
        @forelse($items as $item)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">
                @if($item->file_path)
                    <img src="{{ Storage::disk('public')->url($item->file_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                @else
                    <span class="text-gray-400">No image</span>
                @endif
            </div>
            <div class="p-3 space-y-1">
                <p class="font-medium text-sm truncate">{{ $item->title ?? 'Tanpa Judul' }}</p>
                @if($item->caption)
                    <p class="text-xs text-gray-500 truncate">{{ $item->caption }}</p>
                @endif
                <div class="flex items-center justify-between pt-1">
                    <span class="text-xs px-1.5 py-0.5 rounded {{ $item->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $item->is_published ? 'Aktif' : 'Draft' }}
                    </span>
                    <div class="flex gap-1">
                        <button @click="editItem = {{ $item->id }}; showEdit=true"
                            class="text-xs text-blue-600 hover:underline">Edit</button>
                        <form method="POST" action="{{ route('admin.branding.website.gallery.destroy', $item) }}" onsubmit="return confirm('Hapus foto ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-4 bg-white rounded-lg shadow p-12 text-center text-gray-400">
            <p class="text-lg mb-2">🖼️ Belum ada galeri</p>
            <p>Klik "Tambah Foto" untuk menambahkan gambar ke galeri website.</p>
        </div>
        @endforelse
    </div>

    {{-- Add Modal --}}
    <div x-show="showAdd" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showAdd=false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md space-y-4">
            <h3 class="font-semibold text-lg">Tambah Foto</h3>
            <form method="POST" action="{{ route('admin.branding.website.gallery.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Judul</label>
                        <input type="text" name="title" maxlength="200" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Foto</label>
                        <input type="file" name="image" accept="image/*" required class="w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Caption</label>
                        <input type="text" name="caption" maxlength="300" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Deskripsi singkat">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_published" value="1" checked class="rounded">
                            <span class="text-sm">Publikasikan</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="showAdd=false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">Upload</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showEdit=false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md space-y-4">
            <h3 class="font-semibold text-lg">Edit Foto</h3>
            <form method="POST" :action="'/admin/website/gallery/' + editItem + '/update'">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Judul</label>
                        <input type="text" name="title" maxlength="200" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Caption</label>
                        <input type="text" name="caption" maxlength="300" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_published" value="1" checked class="rounded">
                            <span class="text-sm">Publikasikan</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="showEdit=false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
