@extends('layouts.school-admin')
@section('title', 'Anggota Koperasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Koperasi</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Anggota Koperasi</h1>
    <div class="elite-rule"></div>
</div>

<button onclick="document.getElementById('addMemberForm').classList.toggle('hidden')" class="btn-elite-gold mb-4">+ Daftarkan Anggota</button>

<div id="addMemberForm" class="hidden elite-card p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Daftarkan Anggota Baru</h3>
    <form method="POST" action="{{ route('admin.cooperative.members.store') }}" class="grid md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tipe Anggota *</label>
            <select name="memberable_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="staff">Staff / Guru</option>
                <option value="student">Siswa</option>
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Nama *</label>
            <select name="memberable_id" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="">— Pilih —</option>
                <optgroup label="Staff/Guru">
                    @foreach($staff as $st)<option value="{{ $st->id }}">{{ $st->name }}</option>@endforeach
                </optgroup>
                <optgroup label="Siswa">
                    @foreach($students as $stu)<option value="{{ $stu->id }}">{{ $stu->user?->name }}</option>@endforeach
                </optgroup>
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tanggal Bergabung *</label>
            <input type="date" name="join_date" required value="{{ date('Y-m-d') }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-elite">Daftarkan</button>
        </div>
    </form>
</div>

<form method="GET" class="flex gap-3 mb-4 bg-white border border-rule p-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor anggota..." class="border-2 border-rule px-3 py-2 text-sm">
    <select name="status" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Status —</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Filter</button>
</form>

<div class="elite-card overflow-hidden">
    <div class="table-scroll">
    <table class="table-elite w-full text-sm">
        <thead>
            <tr>
                <th>No Anggota</th>
                <th>Tipe</th>
                <th>Nama</th>
                <th>Simpanan</th>
                <th>Pinjaman</th>
                <th>Status</th>
                <th>Tgl Gabung</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $member)
            <tr>
                <td class="font-mono text-[.65rem]">{{ $member->member_number }}</td>
                <td class="text-[.6rem] uppercase">{{ $member->memberable_type === 'App\\Models\\User' ? 'Staff' : 'Siswa' }}</td>
                <td class="font-serif font-semibold text-sm">{{ $member->memberable?->name ?? $member->memberable?->user?->name }}</td>
                <td class="font-mono text-xs">Rp {{ number_format($member->total_savings / 100, 0, ',', '.') }}</td>
                <td class="font-mono text-xs">Rp {{ number_format($member->total_loans / 100, 0, ',', '.') }}</td>
                <td><span class="text-[.6rem] uppercase px-2 py-0.5 rounded {{ $member->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $member->status }}</span></td>
                <td class="text-xs">{{ $member->join_date->format('d/m/Y') }}</td>
                <td class="text-right whitespace-nowrap">
                    <form method="POST" action="{{ route('admin.cooperative.members.update', $member) }}" class="inline" onsubmit="return confirm('Nonaktifkan/suspend anggota?')">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="inactive">
                        <button class="text-xs underline text-yellow-600 mr-2">Nonaktifkan</button>
                    </form>
                    <form method="POST" action="{{ route('admin.cooperative.members.delete', $member) }}" class="inline" onsubmit="return confirm('Hapus anggota?')">
                        @csrf @method('DELETE')
                        <button class="text-xs underline text-red-600">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="p-10 text-center text-gray-500 italic font-serif">Belum ada anggota koperasi.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-4">{{ $members->links() }}</div>
@endsection
