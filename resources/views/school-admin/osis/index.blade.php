@extends('layouts.school-admin')
@section('title', 'Pemilihan OSIS')
@section('sidebar')
    @include('school-admin.partials.sidebar')
@endsection

@push('head')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')
<div class="mb-7 flex justify-between items-start flex-wrap gap-3">
    <div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Pemilihan OSIS</h1>
        <div class="elite-rule"></div>
    </div>
    <a href="{{ route('admin.osis.candidates', $activeElection) }}" class="btn-elite">Kelola Kandidat</a>
</div>

{{-- Active election tracking --}}
@if($activeElection)
<div class="bg-white border-l-4 border-blue-600 p-5 mb-7">
    <div class="flex justify-between items-start flex-wrap gap-3">
        <div>
            <div class="elite-kicker text-[.6rem]">Pemilihan Aktif</div>
            <div class="font-display text-xl ink-primary mt-1">{{ $activeElection->title }}</div>
            <div class="text-xs text-gray-500 mt-1">
                Status: <span class="font-semibold">{{ $activeElection->status }}</span>
                @if($activeElection->voting_start)
                · Voting: {{ $activeElection->voting_start->format('d/m/Y H:i') }} — {{ $activeElection->voting_end?->format('d/m/Y H:i') ?? '—' }}
                @endif
            </div>
            <div class="text-xs text-gray-500 mt-0.5">
                Jabatan: {{ implode(', ', $activeElection->positions ?? []) }}
                · Maks suara/siswa: {{ $activeElection->max_votes_per_student }}
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.osis.results', $activeElection) }}" class="btn-elite-gold text-xs">Lihat Hasil Live</a>
            @if($activeElection->status === 'voting')
            <form method="POST" action="{{ route('admin.osis.finalize', $activeElection) }}">
                @csrf
                <button class="btn-elite text-xs" onclick="return confirm('Finalisasi hasil pemilihan?')">Finalisasi Hasil</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Create new election --}}
<div class="bg-white border border-rule p-7 mb-7" x-data="{ open: false }">
    <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
        <h3 class="elite-h3 text-lg ink-primary">Buat Pemilihan Baru</h3>
        <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </div>
    <form x-show="open" x-cloak method="POST" action="{{ route('admin.osis.store') }}" class="mt-4 space-y-4">
        @csrf
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="elite-kicker block mb-1">Judul Pemilihan</label>
                <input type="text" name="title" required class="w-full border border-rule p-2.5" placeholder="Pemilihan OSIS 2025/2026">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Tahun Ajaran</label>
                <select name="academic_year_id" class="w-full border border-rule p-2.5">
                    <option value="">— Pilih —</option>
                    @foreach(\App\Models\Academic\AcademicYear::orderByDesc('name')->get() as $ay)
                    <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="elite-kicker block mb-1">Nominasi Mulai</label>
                <input type="datetime-local" name="nomination_start" class="w-full border border-rule p-2.5">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Nominasi Selesai</label>
                <input type="datetime-local" name="nomination_end" class="w-full border border-rule p-2.5">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Voting Mulai</label>
                <input type="datetime-local" name="voting_start" class="w-full border border-rule p-2.5">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Voting Selesai</label>
                <input type="datetime-local" name="voting_end" class="w-full border border-rule p-2.5">
            </div>
        </div>
        <div>
            <label class="elite-kicker block mb-1">Jabatan (pisahkan dengan koma)</label>
            <input type="text" name="positions[]" required class="w-full border border-rule p-2.5" placeholder="Ketua OSIS, Wakil Ketua, Sekretaris">
        </div>
        <div>
            <label class="elite-kicker block mb-1">Maks Suara per Siswa</label>
            <input type="number" name="max_votes_per_student" value="1" min="1" class="w-full border border-rule p-2.5">
        </div>
        <button type="submit" class="btn-elite">Buat Pemilihan</button>
    </form>
</div>

{{-- Elections history --}}
<h3 class="elite-h3 text-lg ink-primary mb-4">Riwayat Pemilihan</h3>
<div class="table-scroll">
<table class="table-elite w-full">
    <thead>
        <tr>
            <th>Judul</th>
            <th>Status</th>
            <th>Tahun Ajaran</th>
            <th>Nominasi</th>
            <th>Voting</th>
            <th>Kandidat</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse($elections as $el)
        <tr>
            <td data-label="Judul" class="font-serif font-semibold">{{ $el->title }}</td>
            <td data-label="Status">
                @php
                    $statusColors = [
                        'setup' => 'bg-gray-100 text-gray-700',
                        'nomination' => 'bg-blue-100 text-blue-700',
                        'voting' => 'bg-yellow-100 text-yellow-700',
                        'completed' => 'bg-green-100 text-green-700',
                    ];
                @endphp
                <span class="text-xs px-2 py-0.5 {{ $statusColors[$el->status] ?? 'bg-gray-100' }}">
                    {{ $el->status === 'completed' ? 'Selesai' : ucfirst($el->status) }}
                </span>
            </td>
            <td data-label="Tahun Ajaran">{{ $el->academicYear?->name ?? '—' }}</td>
            <td data-label="Nominasi" class="text-xs">
                {{ $el->nomination_start?->format('d/m/Y') ?? '—' }} — {{ $el->nomination_end?->format('d/m/Y') ?? '—' }}
            </td>
            <td data-label="Voting" class="text-xs">
                {{ $el->voting_start?->format('d/m/Y') ?? '—' }} — {{ $el->voting_end?->format('d/m/Y') ?? '—' }}
            </td>
            <td data-label="Kandidat">{{ $el->candidates->count() }}</td>
            <td>
                <div class="flex gap-1 flex-wrap">
                    <a href="{{ route('admin.osis.candidates', $el) }}" class="text-xs text-blue-600 hover:underline">Kandidat</a>
                    <a href="{{ route('admin.osis.results', $el) }}" class="text-xs text-green-600 hover:underline">Hasil</a>
                    <form method="POST" action="{{ route('admin.osis.delete', $el) }}" class="inline" onsubmit="return confirm('Hapus pemilihan ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-600 hover:underline">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-gray-500 italic py-8">Belum ada pemilihan OSIS.</td></tr>
        @endforelse
    </tbody>
</table>
</div>
@endsection
