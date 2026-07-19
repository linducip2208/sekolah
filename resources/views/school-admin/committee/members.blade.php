@extends('layouts.school-admin')
@section('title', 'Anggota Komite Sekolah')
@section('sidebar')
    @include('school-admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-7 flex justify-between items-start flex-wrap gap-3">
    <div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Anggota Komite Sekolah</h1>
        <div class="elite-rule"></div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.committee.meetings') }}" class="btn-elite-ghost">Rapat</a>
        <a href="{{ route('admin.committee.decisions') }}" class="btn-elite-ghost">Keputusan</a>
        <a href="{{ route('admin.committee.proposals') }}" class="btn-elite-ghost">Proposal</a>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-7">
    {{-- Add member --}}
    <div class="bg-white border border-rule p-7">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Tambah Anggota</h3>
        <form method="POST" action="{{ route('admin.committee.members.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="elite-kicker block mb-1">User (Orang Tua / Staff)</label>
                <select name="user_id" required class="w-full border border-rule p-2.5">
                    <option value="">— Pilih —</option>
                    <optgroup label="Orang Tua">
                        @foreach($parents as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (Orang Tua)</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Staff">
                        @foreach($staff as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} (Staff)</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Jabatan</label>
                <select name="role" required class="w-full border border-rule p-2.5">
                    <option value="ketua">Ketua</option>
                    <option value="wakil">Wakil Ketua</option>
                    <option value="sekretaris">Sekretaris</option>
                    <option value="bendahara">Bendahara</option>
                    <option value="anggota">Anggota</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="elite-kicker block mb-1">Periode Mulai</label>
                    <input type="date" name="period_start" required class="w-full border border-rule p-2.5">
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Periode Selesai</label>
                    <input type="date" name="period_end" required class="w-full border border-rule p-2.5">
                </div>
            </div>
            <button type="submit" class="btn-elite w-full">Simpan Anggota</button>
        </form>
    </div>

    {{-- Members list --}}
    <div>
        <h3 class="elite-h3 text-lg ink-primary mb-4">Daftar Anggota Aktif</h3>
        @forelse($members as $m)
        <div class="bg-white border border-rule p-5 mb-3 {{ $m->is_active ? '' : 'opacity-50' }}">
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-serif font-semibold ink-primary">{{ $m->user?->name }}</div>
                    <div class="elite-kicker text-[.6rem] mt-1">{{ ucfirst($m->role) }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        Periode: {{ \Carbon\Carbon::parse($m->period_start)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($m->period_end)->format('d/m/Y') }}
                    </div>
                </div>
                <div class="flex gap-1">
                    <form method="POST" action="{{ route('admin.committee.members.delete', $m) }}" class="inline" onsubmit="return confirm('Hapus anggota ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-600 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <p class="font-serif text-gray-500 italic">Belum ada anggota komite.</p>
        @endforelse
    </div>
</div>
@endsection
