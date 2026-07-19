@extends('layouts.school-admin')
@section('title', 'Keputusan Rapat Komite')
@section('sidebar')
    @include('school-admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-7 flex justify-between items-start flex-wrap gap-3">
    <div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Keputusan Rapat</h1>
        <div class="elite-rule"></div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.committee.members') }}" class="btn-elite-ghost">Anggota</a>
        <a href="{{ route('admin.committee.meetings') }}" class="btn-elite-ghost">Rapat</a>
        <a href="{{ route('admin.committee.proposals') }}" class="btn-elite-ghost">Proposal</a>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-7">
    {{-- Create decision --}}
    <div class="bg-white border border-rule p-7">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Catat Keputusan</h3>
        <form method="POST" action="{{ route('admin.committee.decisions.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="elite-kicker block mb-1">Rapat</label>
                <select name="committee_meeting_id" required class="w-full border border-rule p-2.5">
                    <option value="">— Pilih Rapat —</option>
                    @foreach($meetings as $mtg)
                    <option value="{{ $mtg->id }}">{{ $mtg->title }} ({{ $mtg->meeting_date->format('d/m/Y') }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Judul Keputusan</label>
                <input type="text" name="title" required class="w-full border border-rule p-2.5">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full border border-rule p-2.5"></textarea>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Tipe Keputusan</label>
                <select name="decision_type" required class="w-full border border-rule p-2.5">
                    <option value="kebijakan">Kebijakan</option>
                    <option value="anggaran">Anggaran</option>
                    <option value="program">Program</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Hasil Voting</label>
                <select name="voting_result" class="w-full border border-rule p-2.5">
                    <option value="">— Belum voting —</option>
                    <option value="approved">Disetujui</option>
                    <option value="rejected">Ditolak</option>
                    <option value="deferred">Ditunda</option>
                </select>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Status</label>
                <select name="status" required class="w-full border border-rule p-2.5">
                    <option value="draft">Draft</option>
                    <option value="finalized">Finalized</option>
                </select>
            </div>
            <button type="submit" class="btn-elite w-full">Simpan Keputusan</button>
        </form>
    </div>

    {{-- Decisions list --}}
    <div>
        <h3 class="elite-h3 text-lg ink-primary mb-4">Daftar Keputusan</h3>
        @forelse($decisions as $d)
        <div class="bg-white border border-rule p-5 mb-3">
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-serif font-semibold ink-primary">{{ $d->title }}</div>
                    <div class="elite-kicker text-[.6rem] mt-1">Rapat: {{ $d->meeting?->title }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        Tipe: {{ ucfirst($d->decision_type) }}
                        @if($d->voting_result)
                        · Voting: <span class="{{ $d->voting_result === 'approved' ? 'ink-accent' : ($d->voting_result === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}">
                            {{ $d->voting_result === 'approved' ? 'Disetujui' : ($d->voting_result === 'rejected' ? 'Ditolak' : 'Ditunda') }}
                        </span>
                        @endif
                    </div>
                    @if($d->description)
                    <p class="text-sm mt-2 text-gray-600">{{ Str::limit($d->description, 150) }}</p>
                    @endif
                </div>
                <div class="flex gap-1 flex-col items-end">
                    <span class="text-xs px-2 py-0.5 {{ $d->status === 'finalized' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $d->status === 'finalized' ? 'Final' : 'Draft' }}
                    </span>
                    <div class="flex gap-1 mt-1">
                        <form method="POST" action="{{ route('admin.committee.decisions.delete', $d) }}" class="inline" onsubmit="return confirm('Hapus keputusan ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <p class="font-serif text-gray-500 italic">Belum ada keputusan rapat.</p>
        @endforelse
    </div>
</div>
@endsection
