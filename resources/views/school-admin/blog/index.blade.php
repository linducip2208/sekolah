@extends('layouts.school-admin')
@section('title', 'Blog')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="flex justify-between items-end mb-7">
    <div>
        <div class="elite-kicker mb-2">Schola Scripta</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Artikel Blog</h1>
        <div class="elite-rule"></div>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('blog.categories.index') }}" class="btn-elite-ghost" style="padding:.55rem 1rem;font-size:.65rem;">Kategori</a>
        <a href="{{ route('blog.create') }}" class="btn-elite-gold">+ Tulis Artikel</a>
    </div>
</div>

<div class="bg-white border border-rule p-4 sm:p-5 mb-6">
    <form method="GET" action="{{ route('blog.index') }}" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block elite-kicker text-[.55rem] mb-1" style="color: var(--c-muted);">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Judul atau konten..."
                   class="w-full px-3 py-2 border border-rule text-sm font-serif focus:outline-none"
                   style="border-color: var(--c-rule);">
        </div>
        <div>
            <label class="block elite-kicker text-[.55rem] mb-1" style="color: var(--c-muted);">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-rule text-sm font-serif focus:outline-none" style="border-color: var(--c-rule);">
                <option value="">Semua</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Dipublikasi</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.55rem] mb-1" style="color: var(--c-muted);">Kategori</label>
            <select name="category" class="w-full px-3 py-2 border border-rule text-sm font-serif focus:outline-none" style="border-color: var(--c-rule);">
                <option value="">Semua</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-elite" style="padding:.55rem 1rem;font-size:.65rem;">Filter</button>
            <a href="{{ route('blog.index') }}" class="btn-elite-ghost" style="padding:.55rem 1rem;font-size:.65rem;">Reset</a>
        </div>
    </form>
</div>

<div class="bg-white border border-rule">
    <div class="table-scroll">
        <table class="table-elite w-full">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th class="hidden md:table-cell">Kategori</th>
                    <th class="hidden sm:table-cell">Penulis</th>
                    <th class="hidden md:table-cell">Status</th>
                    <th class="hidden lg:table-cell">Tanggal</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>
                            <div>
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                   class="font-serif font-semibold ink-primary hover:ink-accent transition text-base block leading-tight">
                                    {{ $post->title }}
                                </a>
                                <span class="text-xs text-gray-400 font-mono">{{ $post->slug }}</span>
                            </div>
                        </td>
                        <td class="hidden md:table-cell">
                            <span class="elite-kicker text-[.55rem]">{{ $post->category?->name ?? '—' }}</span>
                        </td>
                        <td class="hidden sm:table-cell text-sm font-serif">{{ $post->author?->name ?? '—' }}</td>
                        <td class="hidden md:table-cell">
                            @if($post->is_published)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Publik
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs bg-gray-100 text-gray-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Draft
                                </span>
                            @endif
                        </td>
                        <td class="hidden lg:table-cell text-sm font-serif text-gray-500">
                            {{ $post->published_at?->format('d M Y') ?: '—' }}
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end items-center gap-2">
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                   class="text-xs text-gray-500 hover:ink-accent transition" title="Lihat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('blog.edit', $post) }}" class="text-xs underline ink-secondary hover:ink-accent">Edit</a>
                                <form method="POST" action="{{ route('blog.destroy', $post) }}"
                                      onsubmit="return confirm('Hapus artikel &quot;{{ addslashes($post->title) }}&quot;? Tindakan ini tidak dapat dibatalkan.')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 italic font-serif text-gray-500">
                            @if(request()->anyFilled(['search', 'status', 'category']))
                                Tidak ada artikel yang cocok dengan filter. <a href="{{ route('blog.index') }}" class="ink-accent hover:underline">Reset filter</a>.
                            @else
                                Belum ada artikel. <a href="{{ route('blog.create') }}" class="ink-accent hover:underline">Tulis artikel pertama →</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $posts->links() }}</div>

@endsection
