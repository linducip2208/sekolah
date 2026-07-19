@extends('layouts.school-admin')
@section('title', 'Kategori Blog')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div x-data="categoryManager()">

    <div class="flex justify-between items-end mb-7">
        <div>
            <div class="elite-kicker mb-2">Categoria</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Kategori Blog</h1>
            <div class="elite-rule"></div>
        </div>
        <a href="{{ route('blog.index') }}" class="btn-elite-ghost" style="padding:.55rem 1rem;font-size:.65rem;">← Kembali ke Artikel</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-1">
            <div class="bg-white border border-rule p-5 sm:p-6">
                <h2 class="elite-h3 text-lg ink-primary mb-4" x-text="editingId ? 'Edit Kategori' : 'Tambah Kategori'"></h2>
                <form method="POST"
                      :action="editingId ? '{{ route('blog.categories.index') }}/' + editingId : '{{ route('blog.categories.store') }}'"
                      @submit="handleSubmit">
                    @csrf
                    <input type="hidden" name="_method" x-bind:value="editingId ? 'PUT' : 'POST'">

                    <div class="space-y-4">
                        <div>
                            <label class="block elite-kicker text-[.6rem] mb-1" style="color: var(--c-muted);">Nama Kategori</label>
                            <input type="text" name="name" x-model="formName" required
                                   class="w-full px-4 py-2.5 border border-rule font-serif text-base focus:outline-none"
                                   style="border-color: var(--c-rule);"
                                   placeholder="Contoh: Tips Mengajar">
                        </div>
                        <div>
                            <label class="block elite-kicker text-[.6rem] mb-1" style="color: var(--c-muted);">Slug</label>
                            <div class="flex gap-2">
                                <input type="text" name="slug" x-model="formSlug" required
                                       class="flex-1 px-4 py-2.5 border border-rule font-mono text-sm focus:outline-none"
                                       style="border-color: var(--c-rule);"
                                       placeholder="tips-mengajar">
                                <button type="button" @click="formSlug = slugify(formName)"
                                        class="btn-elite-ghost flex-shrink-0 text-xs" style="padding:.45rem .65rem;" title="Generate dari nama">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block elite-kicker text-[.6rem] mb-1" style="color: var(--c-muted);">Deskripsi (opsional)</label>
                            <textarea name="description" x-model="formDescription" rows="2"
                                      class="w-full px-4 py-2.5 border border-rule font-serif text-sm focus:outline-none"
                                      style="border-color: var(--c-rule);"
                                      placeholder="Deskripsi singkat kategori..."></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn-elite-gold flex-1" style="font-size:.65rem;padding:.55rem;" x-text="editingId ? 'Simpan' : 'Tambah'"></button>
                            <button type="button" @click="resetForm" x-show="editingId" class="btn-elite-ghost" style="font-size:.65rem;padding:.55rem;">Batal</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white border border-rule">
                <div class="table-scroll">
                    <table class="table-elite w-full">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Slug</th>
                                <th class="text-center">Artikel</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $cat)
                                <tr>
                                    <td>
                                        <div class="font-serif font-semibold ink-primary text-base">{{ $cat->name }}</div>
                                        @if($cat->description)
                                            <div class="text-xs text-gray-500 mt-0.5">{{ Str::limit($cat->description, 80) }}</div>
                                        @endif
                                    </td>
                                    <td class="font-mono text-sm text-gray-500">{{ $cat->slug }}</td>
                                    <td class="text-center">
                                        <span class="inline-flex items-center justify-center min-w-[2rem] h-6 px-2 text-xs font-semibold rounded-full"
                                              style="background: var(--c-primary); color: white;">{{ $cat->posts_count }}</span>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end items-center gap-2">
                                            <button type="button" @click="editCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->slug) }}', '{{ addslashes($cat->description ?? '') }}')"
                                                    class="text-xs underline ink-secondary hover:ink-accent">Edit</button>
                                            <form method="POST" action="{{ route('blog.categories.destroy', $cat) }}"
                                                  onsubmit="return confirm('Hapus kategori &quot;{{ addslashes($cat->name) }}&quot;? Artikel dalam kategori ini tidak akan terhapus, hanya menjadi tanpa kategori.')">
                                                @csrf @method('DELETE')
                                                <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-10 italic font-serif text-gray-500">
                                        Belum ada kategori. Tambahkan kategori pertama Anda di formulir sebelah kiri.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function categoryManager() {
    return {
        editingId: null,
        formName: '',
        formSlug: '',
        formDescription: '',

        editCategory(id, name, slug, desc) {
            this.editingId = id;
            this.formName = name;
            this.formSlug = slug;
            this.formDescription = desc || '';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        resetForm() {
            this.editingId = null;
            this.formName = '';
            this.formSlug = '';
            this.formDescription = '';
        },

        handleSubmit(e) {
            if (this.editingId) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);
                formData.append('_method', 'PUT');

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(r => r.json())
                .then(() => {
                    window.location.reload();
                })
                .catch(() => {
                    window.location.reload();
                });
            }
        },

        slugify(text) {
            return text.toString().toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s_]+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '')
                .substring(0, 80);
        }
    };
}
</script>

@endsection
