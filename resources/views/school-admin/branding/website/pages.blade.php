@extends('layouts.school-admin')
@section('title', 'Website Builder — Halaman')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="max-w-5xl space-y-6" x-data="{
    showModal: false,
    editMode: false,
    editingId: null,
    form: { title:'', slug:'', meta_description:'', status:'draft', is_homepage:false }
}">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Website Builder — Halaman</h2>
            <p class="text-sm text-gray-600">Kelola halaman website publik sekolah Anda. Gunakan drag-drop builder untuk mengatur konten.</p>
        </div>
        <button @click="showModal=true; editMode=false; form={title:'',slug:'',meta_description:'',status:'draft',is_homepage:false}"
            class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Halaman
        </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                <tr>
                    <th class="text-left px-4 py-3">Halaman</th>
                    <th class="text-left px-4 py-3">Slug</th>
                    <th class="text-center px-4 py-3">Status</th>
                    <th class="text-center px-4 py-3">Homepage</th>
                    <th class="text-right px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $p)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $p->title }}</td>
                    <td class="px-4 py-3 text-gray-500">/s/{{ request()->user()->school->subdomain ?? 'sekolah' }}/{{ $p->slug }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="badge text-xs px-2 py-0.5 rounded-full {{ $p->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $p->status === 'published' ? 'Terbit' : 'Draft' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($p->is_homepage) <span class="text-blue-600 font-semibold">Ya</span>
                        @else <span class="text-gray-400">—</span> @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.branding.website.builder', $p) }}" class="px-2 py-1 text-xs bg-blue-50 text-blue-600 rounded hover:bg-blue-100" title="Builder">Builder</a>
                            <button @click="editMode=true; editingId='{{ $p->id }}'; form={title:'{{ addslashes($p->title) }}',slug:'{{ $p->slug }}',meta_description:'{{ addslashes($p->meta_description ?? '') }}',status:'{{ $p->status }}',is_homepage:{{ $p->is_homepage ? 'true' : 'false' }}}; showModal=true"
                                class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Edit</button>
                            <form method="POST" action="{{ route('admin.branding.website.page.update', $p) }}" style="display:none" id="editForm-{{ $p->id }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="title" :value="form.title">
                                <input type="hidden" name="slug" :value="form.slug">
                                <input type="hidden" name="meta_description" :value="form.meta_description">
                                <input type="hidden" name="status" :value="form.status">
                                <input type="hidden" name="is_homepage" :value="form.is_homepage ? '1' : '0'">
                            </form>
                            <form method="POST" action="{{ route('admin.branding.website.page.destroy', $p) }}" onsubmit="return confirm('Hapus halaman ini?')">
                                @csrf @method('DELETE')
                                <button class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-400">Belum ada halaman. Klik "Tambah Halaman" untuk memulai.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showModal=false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-lg space-y-4">
            <h3 class="font-semibold text-lg" x-text="editMode ? 'Edit Halaman' : 'Tambah Halaman'"></h3>
            <form method="POST" :action="editMode ? '{{ route('admin.branding.website.page.update', '__ID__') }}'.replace('__ID__', editingId) : '{{ route('admin.branding.website.page.store') }}'">
                @csrf
                <template x-if="editMode">@method('PUT')</template>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Judul Halaman</label>
                        <input type="text" name="title" x-model="form.title" required maxlength="200"
                            class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Tentang Kami">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Slug (URL)</label>
                        <input type="text" name="slug" x-model="form.slug" maxlength="200"
                            class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="tentang-kami">
                        <p class="text-xs text-gray-400 mt-0.5">Kosongkan untuk generate otomatis dari judul.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Meta Description</label>
                        <textarea name="meta_description" x-model="form.meta_description" maxlength="300" rows="2"
                            class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Deskripsi singkat halaman (SEO)"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select name="status" x-model="form.status" class="w-full border rounded-lg px-3 py-2 text-sm">
                                <option value="draft">Draft</option>
                                <option value="published">Terbit</option>
                            </select>
                        </div>
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_homepage" value="1" x-model="form.is_homepage" class="rounded">
                                <span class="text-sm">Jadikan Homepage</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="showModal=false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold" x-text="editMode ? 'Simpan' : 'Tambah'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
