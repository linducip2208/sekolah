@extends('layouts.school-admin')
@section('title', 'Jurnal Umum')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.accounting.coa') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Bagan Akun</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Diarium</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Jurnal Umum</h1>
    <div class="elite-rule"></div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Buat Jurnal</summary>
    <form method="POST" action="{{ route('admin.accounting.journal.store') }}" class="px-5 py-5 border-t border-rule">@csrf
        <div class="grid md:grid-cols-3 gap-3 mb-4">
            <div><label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label><input type="date" name="entry_date" required value="{{ now()->toDateString() }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
            <div><label class="elite-kicker text-[.6rem] block mb-1">No. Referensi</label><input name="reference_no" maxlength="100" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
            <div><label class="elite-kicker text-[.6rem] block mb-1">Keterangan</label><input name="description" maxlength="500" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
        </div>

        <div class="elite-kicker text-[.6rem] mb-2">Baris Jurnal</div>
        <div id="lines-container" class="space-y-2">
            <div class="grid grid-cols-[1fr_auto_auto_auto] gap-2 jline">
                <select name="lines[0][chart_of_account_id]" required class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
                    <option value="">— akun —</option>
                    @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} · {{ $a->name }}</option>@endforeach
                </select>
                <input type="number" name="lines[0][debit]" min="0" step="0.01" placeholder="Debit" class="w-28 border-2 border-rule px-2 py-1.5 font-mono text-xs">
                <input type="number" name="lines[0][credit]" min="0" step="0.01" placeholder="Kredit" class="w-28 border-2 border-rule px-2 py-1.5 font-mono text-xs">
                <input name="lines[0][description]" placeholder="Ket" class="w-32 border-2 border-rule px-2 py-1.5 font-serif text-xs">
            </div>
        </div>
        <button type="button" onclick="addLine()" class="text-xs underline ink-secondary mt-2">+ Tambah baris</button>

        <div class="mt-5"><button class="btn-elite">Simpan Draft</button></div>
    </form>
</details>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Ref</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Keterangan</th>
            <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Debit</th>
            <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Kredit</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($entries as $e)
                @php $debit = $e->lines->sum('debit'); $credit = $e->lines->sum('credit'); @endphp
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs">{{ $e->entry_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $e->reference_no ?? '—' }}</td>
                    <td class="px-4 py-3 font-serif">{{ Str::limit($e->description ?? '', 60) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($debit/100, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($credit/100, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($e->status === 'posted')<span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-800">Posted</span>
                        @else<span class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-800">Draft</span>@endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.accounting.journal.show', $e) }}" class="text-xs underline ink-secondary">Detail</a>
                        @if($e->status === 'draft')
                            <form method="POST" action="{{ route('admin.accounting.journal.post', $e) }}" class="inline ml-2">@csrf<button class="text-xs text-green-700 hover:underline">Post</button></form>
                            <form method="POST" action="{{ route('admin.accounting.journal.destroy', $e) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada jurnal.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $entries->links() }}</div>

<script>
function addLine() {
    const container = document.getElementById('lines-container');
    const idx = container.querySelectorAll('.jline').length;
    const row = document.createElement('div');
    row.className = 'grid grid-cols-[1fr_auto_auto_auto] gap-2 jline';
    row.innerHTML = `<select name="lines[${idx}][chart_of_account_id]" required class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
        <option value="">— akun —</option>
        @foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->code }} · {{ $a->name }}</option>@endforeach
    </select>
    <input type="number" name="lines[${idx}][debit]" min="0" step="0.01" placeholder="Debit" class="w-28 border-2 border-rule px-2 py-1.5 font-mono text-xs">
    <input type="number" name="lines[${idx}][credit]" min="0" step="0.01" placeholder="Kredit" class="w-28 border-2 border-rule px-2 py-1.5 font-mono text-xs">
    <input name="lines[${idx}][description]" placeholder="Ket" class="w-32 border-2 border-rule px-2 py-1.5 font-serif text-xs">`;
    container.appendChild(row);
}
</script>

@endsection
