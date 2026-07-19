@extends('layouts.school-admin')
@section('title', 'Supplier')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <div class="elite-kicker mb-2">Keuangan SPP &mdash; Pengadaan</div>
        <h1 class="elite-h1 text-4xl ink-primary mb-1">Supplier</h1>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.procurement.index') }}" class="btn-elite-ghost text-xs">Pengadaan</a>
        <button onclick="document.getElementById('addSupplierForm').classList.toggle('hidden')" class="btn-elite text-xs">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Supplier
        </button>
    </div>
</div>

{{-- Add form --}}
<div id="addSupplierForm" class="elite-card p-6 mb-6 hidden">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Supplier Baru</h3>
    <form method="POST" action="{{ route('admin.procurement.suppliers.store') }}" class="space-y-4">
        @csrf
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="elite-kicker block mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Kontak Person</label>
                <input type="text" name="contact_person" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Kategori</label>
                <select name="category" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                    @foreach(['atk' => 'ATK', 'elektronik' => 'Elektronik', 'furniture' => 'Furniture', 'catering' => 'Catering', 'maintenance' => 'Maintenance', 'other' => 'Lainnya'] as $v => $l)
                        <option value="{{ $v }}">{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Telepon</label>
                <input type="text" name="phone" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Email</label>
                <input type="email" name="email" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 px-3 py-2">
                    <input type="checkbox" name="is_active" value="1" checked class="accent-[var(--c-primary)]">
                    <span class="text-sm font-serif">Aktif</span>
                </label>
            </div>
        </div>
        <div>
            <label class="elite-kicker block mb-1">Alamat</label>
            <textarea name="address" rows="2" class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]"></textarea>
        </div>
        <button type="submit" class="btn-elite">Simpan Supplier</button>
    </form>
</div>

{{-- Filter --}}
<form method="GET" class="flex flex-wrap items-center gap-3 mb-4">
    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama atau kontak..."
           class="px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
    <select name="category" onchange="this.form.submit()" class="px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
        <option value="">Semua Kategori</option>
        @foreach(['atk' => 'ATK', 'elektronik' => 'Elektronik', 'furniture' => 'Furniture', 'catering' => 'Catering', 'maintenance' => 'Maintenance', 'other' => 'Lainnya'] as $v => $l)
            <option value="{{ $v }}" {{ ($category ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
    </select>
</form>

{{-- Supplier list --}}
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($suppliers ?? [] as $s)
    <div class="elite-card p-4" x-data="{ editOpen: false }">
        <div class="flex items-start justify-between gap-3 mb-2">
            <h3 class="font-serif font-semibold text-base ink-primary">{{ $s->name }}</h3>
            <div class="flex gap-1">
                <span class="px-2 py-0.5 text-[.55rem] uppercase tracking-wider {{ $s->is_active ? 'bg-green-50 text-green-700' : 'bg-stone-100 text-stone-400' }}">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</span>
            </div>
        </div>
        <div class="text-xs text-stone-500 font-serif space-y-1 mb-3">
            @if($s->contact_person)<div>{{ $s->contact_person }}</div>@endif
            @if($s->phone)<div>{{ $s->phone }}</div>@endif
            @if($s->email)<div>{{ $s->email }}</div>@endif
            @if($s->address)<div class="text-stone-400">{{ $s->address }}</div>@endif
            <div><span class="px-2 py-0.5 text-[.55rem] uppercase bg-stone-100 text-stone-500">{{ $s->category }}</span></div>
        </div>
        <div class="flex gap-2 pt-3 border-t border-stone-100">
            <button @click="editOpen = !editOpen" class="text-xs border border-stone-300 px-2 py-1 hover:bg-stone-50 transition">Edit</button>
            <form method="POST" action="{{ route('admin.procurement.suppliers.delete', $s->id) }}" onsubmit="return confirm('Hapus supplier ini?')">
                @csrf @method('DELETE')
                <button class="text-xs border border-red-200 px-2 py-1 text-red-600 hover:bg-red-50 transition">Hapus</button>
            </form>
        </div>

        <div x-show="editOpen" x-cloak class="mt-4 pt-4 border-t border-stone-100">
            <form method="POST" action="{{ route('admin.procurement.suppliers.update', $s->id) }}" class="space-y-3">
                @csrf @method('PUT')
                <input type="text" name="name" value="{{ $s->name }}" required class="w-full px-3 py-2 border border-stone-300 text-xs font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                <input type="text" name="contact_person" value="{{ $s->contact_person }}" placeholder="Kontak Person" class="w-full px-3 py-2 border border-stone-300 text-xs font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                <div class="flex gap-2">
                    <input type="text" name="phone" value="{{ $s->phone }}" placeholder="Telepon" class="flex-1 px-3 py-2 border border-stone-300 text-xs font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                    <input type="email" name="email" value="{{ $s->email }}" placeholder="Email" class="flex-1 px-3 py-2 border border-stone-300 text-xs font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                </div>
                <select name="category" class="w-full px-3 py-2 border border-stone-300 text-xs font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                    @foreach(['atk' => 'ATK', 'elektronik' => 'Elektronik', 'furniture' => 'Furniture', 'catering' => 'Catering', 'maintenance' => 'Maintenance', 'other' => 'Lainnya'] as $v => $l)
                        <option value="{{ $v }}" {{ $s->category === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
                <textarea name="address" rows="2" class="w-full px-3 py-2 border border-stone-300 text-xs font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">{{ $s->address }}</textarea>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ $s->is_active ? 'checked' : '' }} class="accent-[var(--c-primary)]">
                    <span class="text-xs font-serif">Aktif</span>
                </label>
                <button type="submit" class="btn-elite text-xs w-full">Simpan</button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-10">
        <p class="font-serif text-stone-500">Belum ada supplier.</p>
    </div>
    @endforelse
</div>

{{ $suppliers->links() ?? '' }}

@endsection
