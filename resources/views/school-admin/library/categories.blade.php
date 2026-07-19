@extends('layouts.school-admin')
@section('title', 'Kategori Buku')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.library.books.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Katalog</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Categoriae Librorum</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Kategori Buku</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <form method="POST" action="{{ route('admin.library.categories.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama Kategori</label>
                    <input name="name" required maxlength="100" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Fiksi, Non-Fiksi, Pelajaran...">
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambah</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $c)
                        <tr class="border-t border-rule">
                            <td class="px-4 py-3 font-serif font-semibold">{{ $c->name }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.library.categories.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kategori.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
