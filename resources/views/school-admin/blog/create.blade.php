@extends('layouts.school-admin')
@section('title', isset($post) ? 'Edit Artikel' : 'Tulis Artikel Baru')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div x-data="blogEditor({
    title: '{{ isset($post) ? addslashes($post->title) : '' }}',
    initialSlug: '{{ isset($post) ? addslashes($post->slug) : '' }}',
    isEdit: {{ isset($post) ? 'true' : 'false' }},
    initialPublished: {{ isset($post) && $post->is_published ? 'true' : 'false' }},
})">

    <div class="flex justify-between items-end mb-7">
        <div>
            <div class="elite-kicker mb-2">Schola Scripta</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ isset($post) ? 'Edit Artikel' : 'Tulis Artikel Baru' }}</h1>
            <div class="elite-rule"></div>
        </div>
        <a href="{{ route('blog.index') }}" class="btn-elite-ghost" style="padding:.55rem 1rem;font-size:.65rem;">← Kembali</a>
    </div>

    <form method="POST" action="{{ isset($post) ? route('blog.update', $post) : route('blog.store') }}">
        @csrf
        @if(isset($post)) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border border-rule p-5 sm:p-6">
                    <label class="block elite-kicker mb-2" style="color: var(--c-muted);">Judul Artikel</label>
                    <input type="text" name="title" x-model="title"
                           value="{{ old('title', isset($post) ? $post->title : '') }}"
                           class="w-full px-4 py-3 border border-rule font-serif text-lg focus:outline-none"
                           style="border-color: var(--c-rule);" required>
                    @error('title') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-white border border-rule p-5 sm:p-6">
                    <label class="block elite-kicker mb-2" style="color: var(--c-muted);">Slug URL</label>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 font-mono flex-shrink-0">{{ url('/blog/') }}/</span>
                        <input type="text" name="slug" x-model="slug"
                               value="{{ old('slug', isset($post) ? $post->slug : '') }}"
                               class="flex-1 px-4 py-3 border border-rule font-mono text-sm focus:outline-none"
                               style="border-color: var(--c-rule);" required>
                        <button type="button" @click="slug = slugify(title)"
                                class="btn-elite-ghost flex-shrink-0 text-xs" style="padding:.45rem .65rem;" title="Generate dari judul">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
                    @error('slug') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-white border border-rule p-5 sm:p-6">
                    <label class="block elite-kicker mb-2" style="color: var(--c-muted);">Konten Artikel</label>
                    <textarea name="content" rows="20"
                              class="w-full px-4 py-3 border border-rule font-serif text-base leading-relaxed focus:outline-none"
                              style="border-color: var(--c-rule); min-height: 400px;"
                              placeholder="Tulis konten artikel di sini. Gunakan format HTML (teks tebal, daftar, subjudul, dsb)."
                              required>{{ old('content', isset($post) ? $post->content : '') }}</textarea>
                    @error('content') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-white border border-rule p-5 sm:p-6">
                    <label class="block elite-kicker mb-2" style="color: var(--c-muted);">Ringkasan / Kutipan</label>
                    <textarea name="excerpt" rows="3"
                              class="w-full px-4 py-3 border border-rule font-serif text-base focus:outline-none"
                              style="border-color: var(--c-rule);"
                              placeholder="Ringkasan singkat artikel (maks 500 karakter). Ditampilkan di halaman daftar dan meta description.">{{ old('excerpt', isset($post) ? $post->excerpt : '') }}</textarea>
                    @error('excerpt') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white border border-rule p-5 sm:p-6">
                    <label class="block elite-kicker mb-2" style="color: var(--c-muted);">Status Publikasi</label>
                    <div class="flex items-center gap-3 mb-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_published" value="1"
                                   x-model="isPublished"
                                   {{ old('is_published', isset($post) && $post->is_published ? true : false) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-10 h-5 bg-gray-300 rounded-full peer peer-checked:bg-green-600 peer-focus:ring-2 peer-focus:ring-green-300 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                        </label>
                        <span class="font-serif text-base ink-primary" x-text="isPublished ? 'Dipublikasi' : 'Draft'"></span>
                    </div>
                    <div x-show="isPublished" class="mb-0">
                        <label class="block elite-kicker text-[.55rem] mb-1" style="color: var(--c-muted);">Tanggal Publikasi</label>
                        <input type="datetime-local" name="published_at"
                               value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                               class="w-full px-3 py-2 border border-rule text-sm font-serif focus:outline-none"
                               style="border-color: var(--c-rule);">
                    </div>
                    @error('is_published') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-white border border-rule p-5 sm:p-6">
                    <label class="block elite-kicker mb-2" style="color: var(--c-muted);">Kategori</label>
                    <select name="category_id" class="w-full px-4 py-3 border border-rule font-serif text-base focus:outline-none" style="border-color: var(--c-rule);">
                        <option value="">— Tanpa Kategori —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', isset($post) ? $post->category_id : '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-white border border-rule p-5 sm:p-6">
                    <label class="block elite-kicker mb-2" style="color: var(--c-muted);">Gambar Unggulan (URL)</label>
                    <input type="text" name="featured_image"
                           value="{{ old('featured_image', isset($post) ? $post->featured_image : '') }}"
                           placeholder="Contoh: /storage/blog/artikel.jpg atau https://..."
                           class="w-full px-4 py-3 border border-rule text-sm font-mono focus:outline-none"
                           style="border-color: var(--c-rule);">
                    @error('featured_image') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-white border border-rule p-5 sm:p-6">
                    <label class="block elite-kicker mb-2" style="color: var(--c-muted);">SEO Meta Title</label>
                    <input type="text" name="meta_title"
                           value="{{ old('meta_title', isset($post) ? $post->meta_title : '') }}"
                           placeholder="Judul SEO (maks 60 karakter)"
                           maxlength="255"
                           class="w-full px-4 py-3 border border-rule text-sm font-serif focus:outline-none"
                           style="border-color: var(--c-rule);">
                    <p class="text-xs text-gray-400 mt-1">Kosongkan untuk menggunakan judul artikel.</p>
                    @error('meta_title') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="bg-white border border-rule p-5 sm:p-6">
                    <label class="block elite-kicker mb-2" style="color: var(--c-muted);">SEO Meta Description</label>
                    <textarea name="meta_description" rows="3" maxlength="300"
                              placeholder="Deskripsi SEO (maks 160 karakter, direkomendasikan)"
                              class="w-full px-4 py-3 border border-rule text-sm font-serif focus:outline-none"
                              style="border-color: var(--c-rule);">{{ old('meta_description', isset($post) ? $post->meta_description : '') }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">Kosongkan untuk menggunakan ringkasan artikel.</p>
                    @error('meta_description') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="deco-frame p-4" style="background: linear-gradient(135deg, var(--c-primary), rgba(11,29,58,.95)); color: white;">
                    <h3 class="font-display text-lg mb-2">Pratinjau SEO</h3>
                    <div class="text-xs mb-1" style="color: var(--c-accent); font-family:'Inter',sans-serif; letter-spacing:.1em;">GOOGLE SEARCH PREVIEW</div>
                    <div class="text-blue-400 text-sm mb-1" style="font-family:'Inter',sans-serif;" x-text="'{{ url('/blog/') }}/' + (slug || '...')"></div>
                    <div class="font-serif font-semibold text-base text-white mb-1"
                         x-text="(meta_title || title || 'Judul artikel') + ' — Blog eSchool' | truncate(60)"></div>
                    <div class="font-serif text-xs text-white/75 leading-relaxed"
                         x-text="(meta_description || excerpt || 'Deskripsi artikel...') | truncate(160)"></div>
                </div>

                <button type="submit" class="btn-elite-gold w-full">
                    {{ isset($post) ? 'Simpan Perubahan' : 'Terbitkan Artikel' }}
                </button>
            </div>

        </div>
    </form>
</div>

<script>
function blogEditor({ title, initialSlug, isEdit, initialPublished }) {
    return {
        title: title,
        slug: initialSlug,
        meta_title: '{{ old('meta_title', isset($post) ? addslashes($post->meta_title ?? '') : '') }}',
        meta_description: '{{ old('meta_description', isset($post) ? addslashes($post->meta_description ?? '') : '') }}',
        excerpt: '{{ old('excerpt', isset($post) ? addslashes($post->excerpt ?? '') : '') }}',
        isPublished: initialPublished,
        init() {
            if (!this.slug && this.title) {
                this.slug = this.slugify(this.title);
            }
            this.$watch('title', val => {
                if (!isEdit && val && !this.slug) {
                    this.slug = this.slugify(val);
                }
            });
        },
        slugify(text) {
            return text.toString().toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s_]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '')
                .substring(0, 100);
        }
    };
}
</script>

@endsection
