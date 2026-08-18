@extends('layouts.school-admin')
@section('title', 'Surat Keluar')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Kantor</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Surat Keluar</h1>
<div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Catat Surat Keluar</h3>
            <form method="POST" action="{{ route('admin.office.outgoing.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div><label class="elite-kicker text-[.6rem] block mb-1">No. Surat</label><input name="mail_no" required maxlength="50" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Penerima</label><input name="recipient_name" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Alamat Penerima</label><input name="recipient_address" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Perihal</label><input name="subject" required maxlength="300" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Tanggal Kirim</label><input type="date" name="sent_date" required value="{{ now()->format('Y-m-d') }}" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Dokumen (PDF/Gambar)</label><input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="w-full border-2 border-rule px-3 py-2 text-xs"></div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-3 items-end flex-wrap">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Status</label>
                <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">— Semua —</option>
                    @foreach(['draft','sent','archived'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach
                </select>
            </div>
            <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
        </form>

        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white"><tr>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">No. Surat</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Penerima</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Perihal</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tgl</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($mails as $m)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-3 py-3 font-mono text-xs">{{ $m->mail_no }}</td>
                    <td class="px-3 py-3 font-serif text-sm">{{ $m->recipient_name }}</td>
                    <td class="px-3 py-3 text-xs">{{ $m->subject }}</td>
                    <td class="px-3 py-3 text-xs">{{ $m->sent_date->format('d/m/Y') }}</td>
                    <td class="px-3 py-3">
                        @if($m->status==='draft')<span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-semibold">Draft</span>
                        @elseif($m->status==='sent')<span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-semibold">Terkirim</span>
                        @else<span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded font-semibold">Diarsipkan</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-right whitespace-nowrap">
                        @if($m->status==='draft')
                        <form method="POST" action="{{ route('admin.office.outgoing.mark-sent', $m) }}" class="inline">@csrf<button class="text-xs text-green-700 hover:underline">Tandai Terkirim</button></form>
                        @endif
                        @if($m->status!=='archived')
                        <form method="POST" action="{{ route('admin.office.outgoing.archive', $m) }}" class="inline ml-1">@csrf<button class="text-xs text-gray-500 hover:underline">Arsip</button></form>
                        @endif
                        <form method="POST" action="{{ route('admin.office.outgoing.destroy', $m) }}" class="inline ml-1" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada surat keluar.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $mails->links() }}</div>
    </div>
</div>
@endsection
