@extends('layouts.school-admin')
@section('title', 'Kategori Dokumen')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="flex flex-wrap items-center justify-between gap-4 mb-8">
    <div>
        <div class="elite-kicker mb-2">Administrasi</div>
        <h1 class="elite-h1 text-4xl ink-primary mb-1">Kategori Dokumen</h1>
    </div>
    <a href="{{ route('admin.documents.index') }}" class="btn-elite-ghost text-xs">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
        Kembali ke Dokumen
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="space-y-3">
            @forelse($categories ?? [] as $cat)
            <div class="elite-card p-5" x-data="{ editOpen: false }">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-serif font-semibold text-base ink-primary">{{ $cat->name }}</h3>
                        <div class="flex gap-3 mt-1 text-xs">
                            <span class="text-stone-500 font-serif">{{ $cat->documents_count }} dokumen</span>
                            <span class="text-[.6rem] uppercase tracking-wider px-2 py-0.5
                                {{ $cat->access_level === 'confidential' ? 'bg-red-50 text-red-700' : '' }}
                                {{ $cat->access_level === 'admin' ? 'bg-purple-50 text-purple-700' : '' }}
                                {{ $cat->access_level === 'staff' ? 'bg-blue-50 text-blue-700' : '' }}
                                {{ $cat->access_level === 'public' ? 'bg-green-50 text-green-700' : '' }}">
                                {{ $cat->access_level }}
                            </span>
                            @if($cat->parent)
                                <span class="text-stone-400">Sub dari: {{ $cat->parent->name }}</span>
                            @endif
                        </div>
                        @if($cat->description)
                            <p class="font-serif text-xs text-stone-500 mt-2">{{ $cat->description }}</p>
                        @endif
                    </div>
                    <div class="flex gap-1 flex-shrink-0">
                        <button @click="editOpen = !editOpen" class="text-xs border border-stone-300 px-2 py-1 hover:bg-stone-50 transition">Edit</button>
                        <form method="POST" action="{{ route('admin.documents.categories.delete', $cat->id) }}" onsubmit="return confirm('Hapus kategori ini? Dokumen akan menjadi tanpa kategori.');">
                            @csrf @method('DELETE')
                            <button class="text-xs border border-red-200 px-2 py-1 text-red-600 hover:bg-red-50 transition">Hapus</button>
                        </form>
                    </div>
                </div>

                <div x-show="editOpen" x-cloak class="mt-4 pt-4 border-t border-stone-100">
                    <form method="POST" action="{{ route('admin.documents.categories.update', $cat->id) }}" class="space-y-3">
                        @csrf @method('PUT')
                        <input type="text" name="name" value="{{ $cat->name }}" required class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                        <textarea name="description" rows="2" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">{{ $cat->description }}</textarea>
                        <div class="flex gap-4">
                            <select name="parent_id" class="px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                                <option value="">Induk (none)</option>
                                @foreach($categories->whereNull('parent_id')->where('id', '!=', $cat->id) as $p)
                                    <option value="{{ $p->id }}" {{ $cat->parent_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                            <select name="access_level" class="px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                                @foreach(['public' => 'Publik', 'staff' => 'Staff', 'admin' => 'Admin', 'confidential' => 'Konfidensial'] as $v => $l)
                                    <option value="{{ $v }}" {{ $cat->access_level === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn-elite text-xs">Simpan</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="elite-card p-10 text-center">
                <p class="font-serif text-stone-500">Belum ada kategori. Tambahkan dari form di samping.</p>
            </div>
            @endforelse
        </div>
    </div>

    <div>
        <div class="elite-card p-6">
            <h3 class="elite-h3 text-lg ink-primary mb-4">Kategori Baru</h3>
            <form method="POST" action="{{ route('admin.documents.categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="elite-kicker block mb-1">Nama</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]"></textarea>
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Induk</label>
                    <select name="parent_id" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                        <option value="">Tanpa Induk</option>
                        @foreach($categories->whereNull('parent_id') as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Level Akses</label>
                    <select name="access_level" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                        <option value="public">Publik</option>
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                        <option value="confidential">Konfidensial</option>
                    </select>
                </div>
                <button type="submit" class="btn-elite w-full">Tambah Kategori</button>
            </form>
        </div>
    </div>
</div>

@endsection
