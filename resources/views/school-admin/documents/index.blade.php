@extends('layouts.school-admin')
@section('title', 'Dokumen')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div x-data="docManager()" class="space-y-6">

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <div class="elite-kicker mb-2">Administrasi</div>
        <h1 class="elite-h1 text-4xl ink-primary mb-1">Dokumen</h1>
        <p class="font-serif text-sm" style="color: var(--c-muted);">Manajemen dokumen sekolah — unggah, kategorikan, setujui, bagikan.</p>
    </div>
    <div class="flex gap-2 flex-wrap">
        @if(($pendingApprovals ?? 0) > 0)
            <a href="{{ route('admin.documents.approvals') }}" class="px-3 py-2 text-xs font-semibold uppercase tracking-wider flex items-center gap-2"
               style="background:#fef3c7; color:#92400e;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                {{ $pendingApprovals }} Pending
            </a>
        @endif
        <button @click="openUpload()" class="btn-elite text-xs">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Unggah Dokumen
        </button>
        <a href="{{ route('admin.documents.categories') }}" class="btn-elite-ghost text-xs">Kategori</a>
    </div>
</div>

{{-- Filter bar --}}
<div class="flex flex-wrap items-center gap-3 mb-6 p-3 bg-white border border-stone-200">
    <form method="GET" class="flex flex-wrap items-center gap-3 w-full" x-ref="filterForm">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari judul dokumen..."
                   class="w-full pl-10 pr-4 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
        </div>
        <select name="category_id" onchange="this.form.submit()"
                class="px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
            <option value="">Semua Kategori</option>
            @foreach($categories ?? [] as $cat)
                <option value="{{ $cat->id }}" {{ ($categoryId ?? 0) == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ $cat->documents_count }})</option>
            @endforeach
        </select>
        <select name="sort" onchange="this.form.submit()"
                class="px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
            <option value="newest" {{ ($sort ?? 'newest') === 'newest' ? 'selected' : '' }}>Terbaru</option>
            <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Terlama</option>
            <option value="name" {{ ($sort ?? '') === 'name' ? 'selected' : '' }}>Nama (A-Z)</option>
            <option value="downloads" {{ ($sort ?? '') === 'downloads' ? 'selected' : '' }}>Terbanyak Diunduh</option>
        </select>
    </form>
</div>

{{-- Grid view --}}
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4" x-show="view === 'grid'">
    @forelse($documents ?? [] as $doc)
    <div class="elite-card p-4 group" style="display:flex; flex-direction:column;">
        <div class="flex items-start gap-3 mb-3">
            <div class="p-2 flex-shrink-0" style="background: var(--c-paper);">
                <svg class="w-8 h-8" style="color: var(--c-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="min-w-0">
                <h3 class="font-serif font-semibold text-base ink-primary leading-tight truncate" title="{{ $doc->title }}">{{ $doc->title }}</h3>
                <div class="elite-kicker text-[.55rem] mt-1">{{ $doc->file_type ?? 'Unknown' }} &middot; {{ number_format($doc->file_size / 1024, 1) }} KB &middot; v{{ $doc->version }}</div>
            </div>
        </div>
        <div class="flex-1">
            <p class="font-serif text-xs text-stone-500 mb-3 line-clamp-2">{{ $doc->description ?: 'Tidak ada deskripsi.' }}</p>
            @if($doc->category)
                <span class="inline-block text-[.6rem] uppercase tracking-wider px-2 py-0.5 mb-2" style="background: var(--c-paper); color: var(--c-muted);">{{ $doc->category->name }}</span>
            @endif
        </div>
        <div class="flex items-center justify-between pt-3 border-t border-stone-100 mt-auto">
            <div class="text-[.6rem] text-stone-400 font-serif">
                {{ $doc->uploader?->name ?? 'System' }}<br>{{ $doc->created_at->translatedFormat('d M Y') }}
            </div>
            <div class="flex gap-1">
                <a href="{{ route('admin.documents.download', $doc->id) }}" title="Unduh" class="p-1.5 hover:bg-stone-100 transition">
                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </a>
                <button @click="openShare('{{ $doc->id }}', '{{ $doc->title }}')" title="Bagikan" class="p-1.5 hover:bg-stone-100 transition">
                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/></svg>
                </button>
            </div>
        </div>
        <div class="flex gap-1 mt-2">
            <span class="text-[.55rem] px-2 py-0.5 uppercase tracking-wider {{ $doc->is_published ? 'bg-green-50 text-green-700' : 'bg-stone-100 text-stone-500' }}">@if($doc->is_published) Terbit @else Draft @endif</span>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-10">
        <p class="font-serif text-stone-500">Belum ada dokumen. Klik "Unggah Dokumen" untuk menambahkan.</p>
    </div>
    @endforelse
</div>

{{ $documents->links() ?? '' }}

{{-- Upload Modal --}}
<div x-show="uploadOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(11,29,58,.75);">
    <div @click.outside="uploadOpen = false" class="bg-white w-full max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl border border-stone-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-stone-200">
            <h3 class="elite-h3 text-lg ink-primary">Unggah Dokumen</h3>
            <button @click="uploadOpen = false" class="text-stone-400 hover:text-stone-700 text-xl">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="elite-kicker block mb-1">Judul Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="title" required class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]"></textarea>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Kategori</label>
                <select name="document_category_id" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                    <option value="">Tanpa Kategori</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker block mb-1">File <span class="text-red-500">*</span> (max 50 MB)</label>
                <input type="file" name="file" required class="w-full text-sm font-serif">
            </div>
            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_published" value="1" id="publishDoc" class="accent-[var(--c-primary)]">
                <label for="publishDoc" class="text-sm font-serif">Terbitkan langsung</label>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-stone-100">
                <button type="button" @click="uploadOpen = false" class="btn-elite-ghost">Batal</button>
                <button type="submit" class="btn-elite">Unggah</button>
            </div>
        </form>
    </div>
</div>

{{-- Share Modal --}}
<div x-show="shareOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(11,29,58,.75);">
    <div @click.outside="shareOpen = false" class="bg-white w-full max-w-md shadow-2xl border border-stone-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-stone-200">
            <h3 class="elite-h3 text-lg ink-primary">Bagikan Dokumen</h3>
            <button @click="shareOpen = false" class="text-stone-400 hover:text-stone-700 text-xl">&times;</button>
        </div>
        <form method="POST" :action="'/admin/documents/' + shareDocId + '/share'" class="p-6 space-y-4">
            @csrf
            <p class="font-serif text-sm" x-text="shareDocTitle"></p>
            <div>
                <label class="elite-kicker block mb-1">Tipe Penerima</label>
                <select name="shared_with_type" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                    <option value="user">User</option>
                    <option value="role">Role</option>
                    <option value="school">Sekolah</option>
                </select>
            </div>
            <div>
                <label class="elite-kicker block mb-1">ID Penerima</label>
                <input type="number" name="shared_with_id" required class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Kadaluarsa (hari)</label>
                <input type="number" name="expires_days" min="1" max="365" placeholder="Kosongkan untuk tanpa batas"
                       class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-stone-100">
                <button type="button" @click="shareOpen = false" class="btn-elite-ghost">Batal</button>
                <button type="submit" class="btn-elite">Buat Link</button>
            </div>
        </form>
    </div>
</div>

</div>

@push('scripts')
<script>
function docManager() {
    return {
        view: 'grid',
        uploadOpen: false,
        shareOpen: false,
        shareDocId: null,
        shareDocTitle: '',
        openUpload() { this.uploadOpen = true; },
        openShare(id, title) {
            this.shareDocId = id;
            this.shareDocTitle = title;
            this.shareOpen = true;
        }
    };
}
</script>
@endpush

@endsection
