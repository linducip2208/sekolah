@extends('super-admin.layout')
@section('title', 'Pengguna')
@section('content')

<div class="mb-8">
    <div class="elite-kicker mb-2">Universalis Catalogus</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Pengguna Lintas Tenant</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-base text-gray-600">{{ $users->total() }} pengguna terdaftar di seluruh platform.</p>
</div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-5 gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email…"
           class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
    <select name="school_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua Sekolah —</option>
        @foreach($schools as $s)
            <option value="{{ $s->id }}" @selected(request('school_id') == $s->id)>{{ $s->name }}</option>
        @endforeach
    </select>
    <select name="role" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua Role —</option>
        @foreach($roles as $r)
            <option value="{{ $r->name }}" @selected(request('role') === $r->name)>{{ $r->name }}</option>
        @endforeach
    </select>
    <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">Status</option>
        <option value="active" @selected(request('status')==='active')>Aktif</option>
        <option value="inactive" @selected(request('status')==='inactive')>Nonaktif</option>
    </select>
    <button class="md:col-span-5 btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Email</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Sekolah</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Role</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-4 py-3 font-serif">{{ $u->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $u->email }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $u->school?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @foreach($u->roles as $r)
                            <span class="elite-kicker text-[.55rem] mr-1" style="color: var(--c-accent);">{{ $r->name }}</span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3">
                        @if($u->is_active)
                            <span class="text-xs font-semibold text-green-700">● Aktif</span>
                        @else
                            <span class="text-xs font-semibold text-red-700">● Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('super.users.toggle', $u) }}" class="inline">
                            @csrf
                            <button class="text-xs underline ink-secondary hover:ink-accent">
                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500 italic font-serif">Belum ada user yang cocok filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $users->links() }}</div>

@endsection
