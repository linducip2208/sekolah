@extends('layouts.school-admin')
@section('title', 'Periode Akuntansi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Akuntansi</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Periode Akuntansi</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Periode tertutup menolak pembuatan dan posting jurnal baru (kode 423).</p>
</div>

<div class="elite-card p-5 mb-6">
    <form method="POST" action="{{ route('admin.accounting.periods.close') }}" class="flex flex-wrap items-end gap-3">
        @csrf
        <div>
            <label class="block text-xs font-semibold mb-1">Periode (YYYY-MM)</label>
            <input type="month" name="period" required class="border rounded px-3 py-2 text-sm" value="{{ now()->format('Y-m') }}">
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold mb-1">Catatan (opsional)</label>
            <input type="text" name="notes" maxlength="500" class="border rounded px-3 py-2 text-sm w-full" placeholder="Tutup buku bulan berjalan">
        </div>
        <button type="submit" class="btn-elite" onclick="return confirm('Tutup periode ini? Jurnal baru ke periode tersebut akan ditolak.')">Tutup Periode</button>
    </form>
    @error('period')<p class="text-red-600 text-xs mt-2">{{ $message }}</p>@enderror
</div>

@if($periods->isEmpty())
    <div class="elite-card p-8 text-center text-sm text-gray-500">Belum ada periode yang ditutup. Semua bulan terbuka.</div>
@else
<div class="table-scroll"><table class="w-full text-sm">
    <thead class="bg-[var(--c-primary)] text-white"><tr>
        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Periode</th>
        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Ditutup</th>
        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Dibuka kembali</th>
        <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Aksi</th>
    </tr></thead>
    <tbody>
        @foreach($periods as $p)
        <tr class="border-t border-rule">
            <td class="px-4 py-3 font-mono">{{ $p->period }}</td>
            <td class="px-4 py-3">
                @if($p->status === 'closed')<span class="text-red-700 font-semibold">Tertutup</span>@else<span class="text-green-700 font-semibold">Terbuka</span>@endif
            </td>
            <td class="px-4 py-3 text-xs text-gray-600">{{ $p->closed_at?->format('d M Y H:i') ?? '—' }}</td>
            <td class="px-4 py-3 text-xs text-gray-600">{{ $p->reopened_at?->format('d M Y H:i') ?? '—' }}</td>
            <td class="px-4 py-3 text-right">
                @if($p->status === 'closed')
                <form method="POST" action="{{ route('admin.accounting.periods.reopen', $p) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-elite-ghost text-xs" onclick="return confirm('Buka kembali periode {{ $p->period }}?')">Reopen</button>
                </form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table></div>
<div class="mt-4">{{ $periods->links() }}</div>
@endif

@endsection
