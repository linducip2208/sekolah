@extends('layouts.school-admin')
@section('title', 'Website — Testimoni')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="max-w-5xl space-y-6" x-data="{ showAdd: false, showEdit: false, editItem: null }">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Testimoni</h2>
            <p class="text-sm text-gray-600">Kelola testimoni dari alumni, orang tua, dan siswa untuk website sekolah.</p>
        </div>
        <button @click="showAdd=true" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Testimoni
        </button>
    </div>

    <div class="space-y-3">
        @forelse($items as $item)
        <div class="bg-white rounded-lg shadow p-4 flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center shrink-0 overflow-hidden">
                @if($item->photo_path)
                    <img src="{{ Storage::disk('public')->url($item->photo_path) }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-semibold">{{ $item->name }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                        @if($item->role === 'alumni') Alumni
                        @elseif($item->role === 'parent') Orang Tua
                        @else Siswa
                        @endif
                    </span>
                    @if($item->is_published)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Terbit</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Draft</span>
                    @endif
                </div>
                <div class="flex items-center gap-0.5 my-1">
                    @for($i=1; $i<=5; $i++)
                        <svg class="w-3.5 h-3.5 {{ $i<=$item->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    @endfor
                </div>
                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $item->testimonial_text }}</p>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <button @click="editItem = {{ $item->id }}; showEdit=true"
                    class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Edit</button>
                <form method="POST" action="{{ route('admin.branding.website.testimonials.destroy', $item) }}" onsubmit="return confirm('Hapus testimoni ini?')">
                    @csrf @method('DELETE')
                    <button class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100">Hapus</button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-12 text-center text-gray-400">
            <p class="text-lg mb-2">💬 Belum ada testimoni</p>
            <p>Klik "Tambah Testimoni" untuk menambahkan.</p>
        </div>
        @endforelse
    </div>

    {{-- Add Modal --}}
    <div x-show="showAdd" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showAdd=false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md space-y-4">
            <h3 class="font-semibold text-lg">Tambah Testimoni</h3>
            <form method="POST" action="{{ route('admin.branding.website.testimonials.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama</label>
                        <input type="text" name="name" required maxlength="100" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Peran</label>
                        <select name="role" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="alumni">Alumni</option>
                            <option value="parent">Orang Tua</option>
                            <option value="student">Siswa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Rating (1-5)</label>
                        <select name="rating" class="w-full border rounded-lg px-3 py-2 text-sm">
                            @for($i=5; $i>=1; $i--)
                            <option value="{{ $i }}">{{ $i }} Bintang</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Testimoni</label>
                        <textarea name="testimonial_text" required maxlength="1000" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Isi testimoni..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Foto (opsional)</label>
                        <input type="file" name="photo" accept="image/*" class="w-full text-sm">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_published" value="1" class="rounded">
                            <span class="text-sm">Publikasikan</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="showAdd=false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showEdit=false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md space-y-4">
            <h3 class="font-semibold text-lg">Edit Testimoni</h3>
            <form method="POST" :action="'/admin/website/testimonials/' + editItem + '/update'" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama</label>
                        <input type="text" name="name" required maxlength="100" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Peran</label>
                        <select name="role" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="alumni">Alumni</option>
                            <option value="parent">Orang Tua</option>
                            <option value="student">Siswa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Rating (1-5)</label>
                        <select name="rating" class="w-full border rounded-lg px-3 py-2 text-sm">
                            @for($i=5; $i>=1; $i--)
                            <option value="{{ $i }}">{{ $i }} Bintang</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Testimoni</label>
                        <textarea name="testimonial_text" required maxlength="1000" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Foto Baru (opsional)</label>
                        <input type="file" name="photo" accept="image/*" class="w-full text-sm">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_published" value="1" class="rounded">
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
