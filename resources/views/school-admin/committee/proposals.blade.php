@extends('layouts.school-admin')
@section('title', 'Proposal Komite')
@section('sidebar')
    @include('school-admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-7 flex justify-between items-start flex-wrap gap-3">
    <div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Proposal Komite</h1>
        <div class="elite-rule"></div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.committee.members') }}" class="btn-elite-ghost">Anggota</a>
        <a href="{{ route('admin.committee.meetings') }}" class="btn-elite-ghost">Rapat</a>
        <a href="{{ route('admin.committee.decisions') }}" class="btn-elite-ghost">Keputusan</a>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-7">
    {{-- Submit proposal --}}
    <div class="bg-white border border-rule p-7">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Ajukan Proposal</h3>
        <form method="POST" action="{{ route('admin.committee.proposals.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="elite-kicker block mb-1">Judul Proposal</label>
                <input type="text" name="title" required class="w-full border border-rule p-2.5" placeholder="Judul proposal...">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full border border-rule p-2.5"></textarea>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Estimasi Anggaran (Rp)</label>
                <input type="number" name="estimated_budget" class="w-full border border-rule p-2.5" placeholder="0">
            </div>
            <button type="submit" class="btn-elite w-full">Ajukan Proposal</button>
        </form>
    </div>

    {{-- Proposals list --}}
    <div>
        <h3 class="elite-h3 text-lg ink-primary mb-4">Daftar Proposal</h3>
        @forelse($proposals as $p)
        <div class="bg-white border border-rule p-5 mb-3">
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-serif font-semibold ink-primary">{{ $p->title }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        Oleh: {{ $p->proposer?->name }} ·
                        {{ $p->created_at->format('d/m/Y') }}
                        @if($p->estimated_budget)
                        · Rp {{ number_format($p->estimated_budget, 0, ',', '.') }}
                        @endif
                    </div>
                    @if($p->description)
                    <p class="text-sm mt-2 text-gray-600">{{ Str::limit($p->description, 200) }}</p>
                    @endif
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span class="text-xs px-2 py-0.5
                        {{ $p->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $p->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $p->status === 'reviewed' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $p->status === 'submitted' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                        {{ ucfirst($p->status) }}
                    </span>
                    <form method="POST" action="{{ route('admin.committee.proposals.review', $p) }}" class="flex gap-1 items-center">
                        @csrf
                        <select name="status" class="text-xs border p-1" onchange="this.form.submit()">
                            <option value="">Review...</option>
                            <option value="reviewed">Review</option>
                            <option value="approved">Setujui</option>
                            <option value="rejected">Tolak</option>
                        </select>
                    </form>
                    <form method="POST" action="{{ route('admin.committee.proposals.delete', $p) }}" class="inline" onsubmit="return confirm('Hapus proposal ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-600 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <p class="font-serif text-gray-500 italic">Belum ada proposal.</p>
        @endforelse
    </div>
</div>
@endsection
