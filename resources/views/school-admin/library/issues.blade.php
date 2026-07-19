@extends('layouts.school-admin')
@section('title', 'Peminjaman Buku')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.library.books.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Katalog</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Loaning</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Peminjaman Buku</h1>
    <div class="elite-rule"></div>
</div>

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Pinjamkan Buku</summary>
    <form method="POST" action="{{ route('admin.library.issues.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">
        @csrf
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Buku</label>
            <select name="book_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— pilih buku —</option>
                @foreach($books as $b)
                    <option value="{{ $b->id }}">{{ $b->title }} (sisa: {{ $b->available_quantity }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Peminjam (user)</label>
            <select name="issued_to" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— pilih peminjam —</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Pinjam</label>
            <input type="date" name="issue_date" required value="{{ now()->toDateString() }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Jatuh Tempo</label>
            <input type="date" name="due_date" required value="{{ now()->addDays(14)->toDateString() }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
        <div class="md:col-span-2">
            <button class="btn-elite">Pinjamkan</button>
        </div>
    </form>
</details>

<form method="GET" class="mb-4 flex gap-2">
    <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua Status —</option>
        @foreach(['issued','returned','overdue','lost'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Buku</th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Peminjam</th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Pinjam</th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Jatuh Tempo</th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="px-3 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($issues as $iss)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-3 py-3 font-serif text-xs">{{ $iss->book?->title }}</td>
                    <td class="px-3 py-3 font-serif text-xs">{{ $iss->issuedTo?->name }}</td>
                    <td class="px-3 py-3 text-xs">{{ $iss->issue_date->format('d M Y') }}</td>
                    <td class="px-3 py-3 text-xs">{{ $iss->due_date->format('d M Y') }}</td>
                    <td class="px-3 py-3">
                        <span class="text-xs px-2 py-0.5 rounded
                            {{ $iss->status === 'issued' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $iss->status === 'returned' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $iss->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $iss->status === 'lost' ? 'bg-gray-200 text-gray-800' : '' }}">{{ $iss->status }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        @if($iss->status === 'issued' || $iss->status === 'overdue')
                            <details class="inline-block text-left">
                                <summary class="text-xs underline ink-secondary hover:ink-accent cursor-pointer">Kembalikan</summary>
                                <form method="POST" action="{{ route('admin.library.issues.return', $iss) }}" class="absolute right-4 mt-2 bg-white border border-rule p-3 shadow-lg z-10 space-y-2 w-64">
                                    @csrf
                                    <input type="date" name="return_date" required value="{{ now()->toDateString() }}" class="w-full border border-rule px-2 py-1 text-xs">
                                    <input type="number" step="100" min="0" name="fine_amount_rupiah" placeholder="Denda Rp (opsional)" class="w-full border border-rule px-2 py-1 text-xs font-mono">
                                    <button class="btn-elite w-full text-xs" style="padding:.4rem;">Konfirmasi</button>
                                </form>
                            </details>
                        @elseif($iss->return_date)
                            <span class="text-xs text-gray-500">{{ $iss->return_date->format('d M Y') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada peminjaman.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $issues->links() }}</div>

@endsection
