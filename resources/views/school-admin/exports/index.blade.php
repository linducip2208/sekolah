@extends('layouts.school-admin')

@section('title', 'Export Data Sekolah')

@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Compliance</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Export Data Sekolah</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">
        Export semua data sekolah (CSV + JSON) sebagai ZIP. Berguna untuk backup, audit, atau portabilitas. File expire 7 hari setelah selesai.
    </p>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border-l-4 border-green-700 text-green-800 text-sm">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-4 p-3 bg-red-50 border-l-4 border-red-700 text-red-800 text-sm">{{ $errors->first() }}</div>@endif

<form method="POST" action="{{ route('admin.exports.store') }}" class="mb-6 bg-white border border-rule p-4" x-data="{ scope: 'all' }">
    @csrf
    <div class="mb-3">
        <label class="elite-kicker text-[.6rem] block mb-2">Scope Export</label>
        <div class="flex gap-4 flex-wrap text-sm font-serif">
            <label class="flex items-center gap-1"><input type="radio" name="scope" value="all" x-model="scope" checked> Semua tabel</label>
            <label class="flex items-center gap-1"><input type="radio" name="scope" value="academic" x-model="scope"> Akademik</label>
            <label class="flex items-center gap-1"><input type="radio" name="scope" value="finance" x-model="scope"> Keuangan</label>
            <label class="flex items-center gap-1"><input type="radio" name="scope" value="communication" x-model="scope"> Komunikasi</label>
            <label class="flex items-center gap-1"><input type="radio" name="scope" value="custom" x-model="scope"> Custom</label>
        </div>
    </div>
    <div x-show="scope === 'custom'" x-cloak class="mb-3">
        <label class="elite-kicker text-[.6rem] block mb-1">Nama tabel (satu per baris)</label>
        <textarea name="tables_raw" rows="4" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"
                  placeholder="students&#10;fee_invoices&#10;attendances"
                  @input="$root.querySelectorAll('input[name=\'tables[]\']').forEach(e=>e.remove()); $event.target.value.split(/\s+/).filter(Boolean).forEach(t => { const i=document.createElement('input'); i.type='hidden'; i.name='tables[]'; i.value=t; $root.appendChild(i); })"></textarea>
        <p class="text-[.65rem] text-gray-500 mt-1">Hanya tabel ber-kolom <code>school_id</code> yang akan diekspor.</p>
    </div>
    <button class="btn-elite">Buat Export Baru</button>
</form>

<div class="bg-white border border-rule">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-rule">
            <tr>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">ID</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Diminta</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">User</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Status</th>
                <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Rows</th>
                <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Size</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Expire</th>
                <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($exports as $e)
                <tr class="border-b border-rule">
                    <td class="px-3 py-2 font-mono">#{{ $e->id }}</td>
                    <td class="px-3 py-2">{{ $e->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="px-3 py-2">{{ $e->requester?->name }}</td>
                    <td class="px-3 py-2">
                        @php $cls = match($e->status){
                            'completed' => 'text-green-700',
                            'failed'    => 'text-red-700',
                            'processing'=> 'text-amber-700',
                            default     => 'text-gray-600'
                        }; @endphp
                        <span class="font-semibold {{ $cls }}">{{ strtoupper($e->status) }}</span>
                    </td>
                    <td class="px-3 py-2 text-right font-mono">{{ number_format($e->row_count ?? 0) }}</td>
                    <td class="px-3 py-2 text-right font-mono">{{ $e->file_size_bytes ? number_format($e->file_size_bytes / 1024 / 1024, 1) . ' MB' : '—' }}</td>
                    <td class="px-3 py-2 text-xs">{{ $e->expires_at?->diffForHumans() }}</td>
                    <td class="px-3 py-2 text-right">
                        @if($e->isReady())
                            <a href="{{ route('admin.exports.download', $e) }}" class="text-blue-700 underline text-xs">Download</a>
                        @endif
                        <form method="POST" action="{{ route('admin.exports.destroy', $e) }}" class="inline"
                              onsubmit="return confirm('Hapus export ini?')">@csrf @method('DELETE')
                            <button class="text-red-700 underline text-xs ml-2">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-3 py-6 text-center text-gray-500 font-serif">Belum ada export.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $exports->links() }}</div>
@endsection
