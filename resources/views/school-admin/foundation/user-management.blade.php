@extends('layouts.school-admin')
@section('title', 'User Management Yayasan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Yayasan</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">User Management</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Kelola user yang memiliki akses lintas sekolah cabang.</p></div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah / Edit User</h3>
            <form method="POST" action="{{ route('admin.foundation.user-management.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">User</label>
                    <select name="user_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— Pilih User —</option>
                        @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Role</label>
                    <input name="role" required maxlength="50" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="e.g. foundation_admin">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Sekolah yang Diakses (ID, koma pisah)</label>
                    <input name="assigned_schools" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs" placeholder="1,2,3">
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white"><tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">User</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Role</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Akses Sekolah</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($assignments as $a)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-serif font-semibold">{{ $a->user?->name }}</div>
                        <div class="text-xs text-gray-500">{{ $a->user?->email }}</div>
                    </td>
                    <td class="px-4 py-3"><span class="elite-kicker text-[.55rem]">{{ $a->role }}</span></td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $a->assigned_schools ? implode(', ', $a->assigned_schools) : 'Semua' }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.foundation.user-management.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada user yayasan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
