@extends('layouts.school-admin')
@section('title', 'Daftar Surat')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="flex justify-between items-end mb-7">
    <div>
        <div class="elite-kicker mb-2">Epistulae</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Manajemen Surat</h1>
        <div class="elite-rule"></div>
        <p class="font-serif text-sm text-gray-600 mt-3">{{ $letters->total() }} surat tercatat.</p>
    </div>
    <a href="{{ route('admin.letters.create') }}" class="btn-elite-gold">+ Buat Surat</a>
</div>

<div x-data="{ checked: [], count: 0 }">
    <div class="bg-white border border-rule overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nomor Surat</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Perihal</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Penerima</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Template</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($letters as $l)
                    <tr class="border-t border-rule hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $l->letter_number }}</td>
                        <td class="px-4 py-3">
                            <div class="font-serif font-semibold ink-primary">{{ $l->subject }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <div>{{ $l->recipient_name }}</div>
                            <div class="text-gray-500">{{ ucfirst($l->recipient_type) }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $l->template?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="elite-kicker text-[.55rem] px-2 py-1 border {{ match($l->status) { 'sent' => 'text-green-700 border-green-300 bg-green-50', 'archived' => 'text-gray-500 border-gray-300 bg-gray-50', default => 'text-amber-700 border-amber-300 bg-amber-50' } }}">{{ match($l->status) { 'sent' => 'Terkirim', 'archived' => 'Arsip', default => 'Draft' } }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $l->issued_at?->format('d/m/Y') ?? $l->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.letters.edit', $l) }}" class="text-xs underline ink-secondary hover:ink-accent">Edit</a>
                            <a href="{{ route('admin.letters.print', $l) }}" class="text-xs underline ink-secondary hover:ink-accent ml-2" target="_blank">PDF</a>
                            <form method="POST" action="{{ route('admin.letters.destroy', $l) }}" class="inline ml-2" onsubmit="return confirm('Hapus surat ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-700 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada surat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $letters->links() }}</div>

@endsection
