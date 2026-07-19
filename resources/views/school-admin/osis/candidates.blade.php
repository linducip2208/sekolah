@extends('layouts.school-admin')
@section('title', 'Kandidat OSIS — ' . $election->title)
@section('sidebar')
    @include('school-admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-7 flex justify-between items-start flex-wrap gap-3">
    <div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Kandidat {{ $election->title }}</h1>
        <div class="elite-rule"></div>
        <div class="text-xs text-gray-500 mt-1">
            Status: {{ $election->status }} · Jabatan: {{ implode(', ', $election->positions ?? []) }}
        </div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.osis.index') }}" class="btn-elite-ghost">← Pemilihan</a>
        <a href="{{ route('admin.osis.results', $election) }}" class="btn-elite-gold text-xs">Hasil</a>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-7">
    {{-- Add candidate --}}
    <div class="bg-white border border-rule p-7">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Daftarkan Kandidat</h3>
        <form method="POST" action="{{ route('admin.osis.candidates.store', $election) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="elite-kicker block mb-1">Siswa</label>
                <select name="student_id" required class="w-full border border-rule p-2.5">
                    <option value="">— Pilih Siswa —</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}">{{ $s->user?->name }} ({{ $s->admission_no }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Jabatan</label>
                <input type="text" name="position" required class="w-full border border-rule p-2.5" placeholder="Ketua OSIS / Wakil / Sekretaris...">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Visi</label>
                <textarea name="vision" rows="3" class="w-full border border-rule p-2.5 font-serif"></textarea>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Misi</label>
                <textarea name="mission" rows="3" class="w-full border border-rule p-2.5 font-serif"></textarea>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Foto Kandidat</label>
                <input type="file" name="photo" accept="image/*" class="w-full border border-rule p-2.5">
            </div>
            <button type="submit" class="btn-elite w-full">Daftarkan Kandidat</button>
        </form>
    </div>

    {{-- Candidates list --}}
    <div>
        <h3 class="elite-h3 text-lg ink-primary mb-4">Daftar Kandidat</h3>
        @forelse($election->candidates as $c)
        <div class="bg-white border border-rule p-5 mb-3 {{ $c->status === 'disqualified' ? 'opacity-50' : '' }}">
            <div class="flex justify-between items-start gap-3">
                <div class="flex gap-3">
                    @if($c->photo_path)
                    <img src="{{ asset('storage/' . $c->photo_path) }}" class="w-12 h-12 object-cover rounded-full">
                    @else
                    <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center font-display text-lg">{{ strtoupper(substr($c->student?->user?->name ?? '?', 0, 1)) }}</div>
                    @endif
                    <div>
                        <div class="font-serif font-semibold ink-primary">{{ $c->student?->user?->name }}</div>
                        <div class="elite-kicker text-[.6rem]">{{ $c->position }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            Status:
                            <span class="{{ $c->status === 'approved' ? 'ink-accent' : ($c->status === 'disqualified' ? 'text-red-600' : 'text-gray-500') }}">
                                {{ $c->status === 'approved' ? 'Disetujui' : ($c->status === 'disqualified' ? 'Didiskualifikasi' : 'Terdaftar') }}
                            </span>
                            · Suara: <span class="font-mono">{{ $c->vote_count }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    @if($c->status === 'registered')
                    <form method="POST" action="{{ route('admin.osis.candidates.approve', $c) }}">
                        @csrf
                        <button class="text-xs text-green-600 hover:underline">Setujui</button>
                    </form>
                    @endif
                    @if($c->status !== 'disqualified')
                    <form method="POST" action="{{ route('admin.osis.candidates.disqualify', $c) }}">
                        @csrf
                        <button class="text-xs text-yellow-600 hover:underline">Diskualifikasi</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.osis.candidates.delete', $c) }}" onsubmit="return confirm('Hapus kandidat?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-600 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
            @if($c->vision)
            <div class="mt-2 pt-2 border-t border-rule">
                <div class="elite-kicker text-[.6rem] mb-1">Visi</div>
                <p class="text-sm">{{ $c->vision }}</p>
            </div>
            @endif
            @if($c->mission)
            <div class="mt-2">
                <div class="elite-kicker text-[.6rem] mb-1">Misi</div>
                <p class="text-sm">{{ $c->mission }}</p>
            </div>
            @endif
        </div>
        @empty
        <p class="font-serif text-gray-500 italic">Belum ada kandidat terdaftar.</p>
        @endforelse
    </div>
</div>
@endsection
