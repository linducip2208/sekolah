@extends('super-admin.layout')
@section('title', 'Yayasan')
@section('content')

<div class="flex justify-between items-end mb-8">
    <div>
        <div class="elite-kicker mb-2">Fundationes</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Manajemen Yayasan</h1>
        <div class="elite-rule mb-3"></div>
        <p class="font-serif text-base text-gray-600">Yayasan / foundation dapat memiliki banyak sekolah ({{ $foundations->total() }} terdaftar).</p>
    </div>
    <a href="{{ route('super.foundations.create') }}" class="btn-elite-gold">+ Tambah Yayasan</a>
</div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 flex gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari yayasan…"
           class="flex-1 border-2 border-rule px-3 py-2 font-serif text-sm">
    <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Cari</button>
</form>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($foundations as $f)
        <div class="elite-card p-6">
            <div class="flex items-start justify-between mb-3">
                <h3 class="elite-h3 text-lg ink-primary">{{ $f->name }}</h3>
                @if($f->is_active)
                    <span class="text-xs font-semibold text-green-700">● Aktif</span>
                @else
                    <span class="text-xs font-semibold text-red-700">● Nonaktif</span>
                @endif
            </div>
            <div class="elite-kicker text-[.6rem] mb-3" style="color: var(--c-muted);">{{ $f->slug }}</div>
            <div class="font-serif text-sm text-gray-700 space-y-1">
                @if($f->npwp)<div><span class="text-gray-500">NPWP:</span> {{ $f->npwp }}</div>@endif
                @if($f->address)<div class="text-xs">{{ Str::limit($f->address, 100) }}</div>@endif
            </div>
            <div class="mt-4 pt-4 border-t border-rule flex justify-between text-xs">
                <span class="text-gray-500">{{ $f->admins_count }} admin</span>
                <span class="text-gray-500">{{ $f->created_at?->format('d M Y') }}</span>
            </div>
        </div>
    @empty
        <div class="md:col-span-2 lg:col-span-3 bg-white border border-rule p-10 text-center text-gray-500 font-serif italic">
            Belum ada yayasan terdaftar.
        </div>
    @endforelse
</div>

<div class="mt-5">{{ $foundations->links() }}</div>

@endsection
