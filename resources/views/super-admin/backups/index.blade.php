@extends('super-admin.layout')
@section('title', 'Backup & Restore')
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Conservatio & Reductio</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Backup & Restore Database</h1><div class="elite-rule"></div></div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 text-sm text-green-800 border-l-4 border-green-700">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-4 p-3 bg-red-50 text-sm text-red-800 border-l-4 border-red-700">{{ $errors->first() }}</div>@endif

<div class="grid lg:grid-cols-2 gap-4 mb-6">
<div class="bg-white border border-rule p-6">
<h3 class="elite-h3 text-base ink-primary mb-3">⬇️ Backup Sekarang</h3>
<p class="font-serif text-sm text-gray-600 mb-3">Generate snapshot SQL dump dari database saat ini.</p>
<form method="POST" action="{{ route('super.backups.trigger') }}">@csrf
<button class="btn-elite-gold">Trigger Backup</button>
</form></div>

<div class="bg-white border border-rule p-6">
<h3 class="elite-h3 text-base ink-primary mb-3">⬆️ Upload SQL untuk Restore</h3>
<p class="font-serif text-sm text-gray-600 mb-3">Upload file .sql, lalu klik "Restore" di tabel.</p>
<form method="POST" action="{{ route('super.backups.upload') }}" enctype="multipart/form-data" class="flex gap-2">@csrf
<input type="file" name="sql_file" required accept=".sql,.txt" class="flex-1 border-2 border-rule px-3 py-2 text-sm">
<button class="btn-elite">Upload</button>
</form></div>
</div>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">File</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Size</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Aksi</th>
</tr></thead><tbody>
@forelse($files as $f)<tr class="border-t border-rule hover:bg-gray-50">
<td class="px-4 py-3 font-mono text-xs">{{ $f['name'] }}</td>
<td class="px-4 py-3 text-right font-mono text-xs">{{ number_format($f['size']/1024, 1) }} KB</td>
<td class="px-4 py-3 text-xs">{{ \Carbon\Carbon::createFromTimestamp($f['mtime'])->format('d M Y H:i') }}</td>
<td class="px-4 py-3 text-right whitespace-nowrap">
<a href="{{ route('super.backups.download', $f['name']) }}" class="text-xs underline ink-secondary hover:ink-accent">⬇️ Download</a>
<details class="inline ml-2"><summary class="text-xs underline cursor-pointer text-yellow-700">↻ Restore</summary>
<form method="POST" action="{{ route('super.backups.restore', $f['name']) }}" class="absolute right-4 mt-2 bg-white border-2 border-yellow-700 p-3 shadow-lg z-10 w-72" onsubmit="return confirm('PERINGATAN: Ini akan MENIMPA data existing di DB! Yakin?')">
@csrf
<div class="text-xs text-red-700 mb-2 font-semibold">⚠ DESTRUCTIVE — data existing akan ditimpa</div>
<input name="confirm" required placeholder="Ketik RESTORE untuk konfirmasi" class="w-full border border-rule px-2 py-1 text-xs font-mono mb-2">
<button class="text-xs btn-elite w-full" style="padding:.4rem;">Eksekusi Restore</button>
</form></details>
<form method="POST" action="{{ route('super.backups.destroy', $f['name']) }}" class="inline ml-2" onsubmit="return confirm('Hapus backup ini?')">
@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button>
</form>
</td></tr>
@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada backup. Klik "Trigger Backup" di atas.</td></tr>@endforelse
</tbody></table></div>

<div class="mt-6 deco-frame"><div class="bg-white p-6">
<div class="elite-kicker mb-2" style="color:var(--c-accent);">Catatan Keamanan</div>
<ul class="font-serif text-sm text-gray-700 space-y-2 list-disc list-inside">
<li><strong>Backup file disimpan di server</strong> — di-folder <code class="font-mono text-xs bg-gray-100 px-1">storage/app/backups/</code>. Download untuk simpan di lokasi aman (S3/cloud).</li>
<li><strong>Restore = destructive</strong> — data DB existing akan di-overwrite. Buat backup terbaru dulu sebelum restore.</li>
<li><strong>Format file</strong> — hanya `.sql` dump dari mysqldump. File harus untuk database dengan schema kompatibel.</li>
<li><strong>Otomatisasi</strong> — untuk backup terjadwal, setup cron: <code class="font-mono text-xs bg-gray-100 px-1">0 2 * * * curl -X POST https://your-domain/super/backups</code></li>
</ul></div></div>
@endsection
