@extends('layouts.school-admin')
@section('title', 'Katalog Buku')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Bibliotheca</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Katalog Buku</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">{{ $books->total() }} judul buku.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.library.categories.index') }}" class="btn-elite-ghost">Kategori</a>
            <a href="{{ route('admin.library.issues.index') }}" class="btn-elite-ghost">Peminjaman</a>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Buku</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.library.books.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Judul</label>
                    <input name="title" required maxlength="255" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Penulis</label>
                    <input name="author" maxlength="255" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Kategori</label>
                    <select name="book_category_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih —</option>
                        @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                    @if($categories->isEmpty())
                        <p class="text-xs text-yellow-700 mt-1">Belum ada kategori. <a href="{{ route('admin.library.categories.index') }}" class="underline">Tambah dulu</a>.</p>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">ISBN</label>
                        <input name="isbn" maxlength="50" class="w-full border-2 border-rule px-2 py-2 font-mono text-xs">
                    </div>
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Tahun</label>
                        <input type="number" name="publish_year" min="1900" max="{{ date('Y')+1 }}" class="w-full border-2 border-rule px-2 py-2 font-mono text-xs">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Jumlah</label>
                        <input type="number" name="total_quantity" required min="1" max="9999" value="1" class="w-full border-2 border-rule px-2 py-2 font-mono text-xs">
                    </div>
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Rak</label>
                        <input name="rack_location" maxlength="50" placeholder="A-12" class="w-full border-2 border-rule px-2 py-2 font-mono text-xs">
                    </div>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Penerbit</label>
                    <input name="publisher" maxlength="255" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Tambah ke Katalog</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <form method="GET" class="bg-white border border-rule p-4 mb-4 grid grid-cols-3 gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul/penulis/ISBN" class="col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
            <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Cari</button>
        </form>

        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Judul</th>
                        <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Penulis</th>
                        <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kategori</th>
                        <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Tersedia</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $b)
                        <tr class="border-t border-rule hover:bg-gray-50">
                            <td class="px-3 py-3">
                                <div class="font-serif font-semibold">{{ $b->title }}</div>
                                <div class="text-xs text-gray-500">{{ $b->isbn ?? '—' }} {{ $b->publish_year ? '· '.$b->publish_year : '' }}</div>
                            </td>
                            <td class="px-3 py-3 text-xs">{{ $b->author ?? '—' }}</td>
                            <td class="px-3 py-3 text-xs">{{ $b->category?->name ?? '—' }}</td>
                            <td class="px-3 py-3 text-center font-mono">
                                <span class="{{ $b->available_quantity == 0 ? 'text-red-700' : 'ink-primary' }}">{{ $b->available_quantity }}/{{ $b->total_quantity }}</span>
                            </td>
                            <td class="px-3 py-3 text-right">
                                <form method="POST" action="{{ route('admin.library.books.destroy', $b) }}" class="inline" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada buku.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $books->links() }}</div>
    </div>
</div>

@endsection
