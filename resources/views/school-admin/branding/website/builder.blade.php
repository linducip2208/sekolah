@extends('layouts.school-admin')
@section('title', 'Builder: ' . $page->title)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
@endpush
<div class="max-w-6xl space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    editingSection: null,
    sectionForm: { section_type:'hero', title:'', subtitle:'', content:'', config:{} },
    availableTypes: @json($sectionTypes),
    selectedTypeLabel: 'Hero / Header',
}">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Page Builder: {{ $page->title }}</h2>
            <p class="text-sm text-gray-600">Status: <span class="font-semibold {{ $page->status === 'published' ? 'text-green-600' : 'text-yellow-600' }}">{{ $page->status === 'published' ? 'Terbit' : 'Draft' }}</span> &middot; <a href="{{ route('admin.branding.website.pages') }}" class="text-blue-600 hover:underline">Kembali ke daftar</a></p>
        </div>
        <button @click="showAddModal=true"
            class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Section
        </button>
    </div>

    <div class="grid grid-cols-3 gap-6">
        {{-- Section list --}}
        <div class="col-span-2 space-y-3" id="sections-list">
            @forelse($sections as $section)
            <div class="bg-white rounded-lg shadow p-4 section-card" data-id="{{ $section->id }}" data-order="{{ $section->sort_order }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="cursor-move handle text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $sectionTypes[$section->section_type]['icon'] ?? '📄' }}</span>
                                <span class="font-semibold">{{ $sectionTypes[$section->section_type]['label'] ?? $section->section_type }}</span>
                            </div>
                            @if($section->title)
                                <p class="text-sm text-gray-500 mt-0.5">{{ $section->title }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="editingSection={{ $section->id }}; sectionForm={section_type:'{{ $section->section_type }}',title:'{{ addslashes($section->title ?? '') }}',subtitle:'{{ addslashes($section->subtitle ?? '') }}',content:'',config:{}}; showEditModal=true"
                            class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200" title="Edit">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('admin.branding.website.section.destroy', $section) }}" onsubmit="return confirm('Hapus section ini?')">
                            @csrf @method('DELETE')
                            <button class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow p-12 text-center text-gray-400">
                <p class="text-lg mb-2">📄 Belum ada section</p>
                <p>Klik "Tambah Section" untuk mulai membangun halaman.</p>
            </div>
            @endforelse
        </div>

        {{-- Preview panel --}}
        <div class="space-y-3">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-sm mb-3">Preview</h3>
                <div class="bg-gray-100 rounded-lg p-3 text-center text-xs text-gray-400">
                    <p>Akan tampil di:</p>
                    <p class="font-mono mt-1 break-all">/s/{{ request()->user()->school->subdomain ?? 'sekolah' }}{{ $page->is_homepage ? '' : '/' . $page->slug }}</p>
                </div>
                @if($page->status === 'published')
                <div class="mt-3">
                    <a href="{{ url('/s/' . (request()->user()->school->subdomain ?? 'sekolah') . ($page->is_homepage ? '' : '/' . $page->slug)) }}"
                        target="_blank" class="block text-center btn-elite px-4 py-2 rounded-lg text-sm font-semibold">
                        Lihat Live
                    </a>
                </div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-sm mb-2">Section Types</h3>
                <div class="space-y-1">
                    @foreach($sectionTypes as $key => $type)
                    <div class="flex items-center gap-2 text-sm text-gray-600 py-1">
                        <span>{{ $type['icon'] }}</span>
                        <span>{{ $type['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Add Section Modal --}}
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showAddModal=false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-lg space-y-4">
            <h3 class="font-semibold text-lg">Tambah Section</h3>
            <form method="POST" action="{{ route('admin.branding.website.section.store', $page) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipe Section</label>
                        <select name="section_type" x-model="sectionForm.section_type" @change="selectedTypeLabel = availableTypes[sectionForm.section_type]?.label || sectionForm.section_type"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                            @foreach($sectionTypes as $key => $type)
                            <option value="{{ $key }}">{{ $type['icon'] }} {{ $type['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Judul (opsional)</label>
                        <input type="text" name="title" x-model="sectionForm.title" maxlength="200"
                            class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Judul section">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Subtitle (opsional)</label>
                        <input type="text" name="subtitle" x-model="sectionForm.subtitle" maxlength="300"
                            class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Deskripsi singkat">
                    </div>
                    <div x-show="sectionForm.section_type === 'custom_html'">
                        <label class="block text-sm font-medium mb-1">Konten HTML</label>
                        <textarea name="content" x-model="sectionForm.content" rows="6"
                            class="w-full border rounded-lg px-3 py-2 text-sm font-mono" placeholder="<div class='p-4'>Konten kustom Anda...</div>"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="showAddModal=false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">Tambah Section</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Section Modal --}}
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showEditModal=false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-lg space-y-4">
            <h3 class="font-semibold text-lg">Edit Section</h3>
            <form method="POST" :action="'/admin/website/section/' + editingSection + '/update'">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Judul</label>
                        <input type="text" name="title" x-model="sectionForm.title" maxlength="200"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Subtitle</label>
                        <input type="text" name="subtitle" x-model="sectionForm.subtitle" maxlength="300"
                            class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Konten (HTML / teks panjang)</label>
                        <textarea name="content" x-model="sectionForm.content" rows="6"
                            class="w-full border rounded-lg px-3 py-2 text-sm font-mono" placeholder="Konten untuk section ini..."></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="showEditModal=false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">Simpan</button>
                </div>
            </form>
            <div class="border-t pt-4 mt-4">
                <p class="text-xs font-semibold text-gray-500 mb-2">Upload Gambar</p>
                <form method="POST" :action="'/admin/website/section/' + editingSection + '/upload-image'" enctype="multipart/form-data">
                    @csrf
                    <div class="flex items-center gap-2">
                        <input type="file" name="image" accept="image/*" class="text-sm" required>
                        <button type="submit" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('sections-list');
    if (el) {
        new Sortable(el, {
            handle: '.handle',
            animation: 200,
            onEnd: function () {
                var items = el.querySelectorAll('.section-card');
                var orders = [];
                items.forEach(function (item, index) {
                    orders.push({ id: item.dataset.id, sort_order: index + 1 });
                });
                fetch('{{ route('admin.branding.website.sections.reorder', $page) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ orders: orders }),
                });
            }
        });
    }
});
</script>
@endpush
@endsection
