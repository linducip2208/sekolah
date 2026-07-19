@extends('layouts.school-admin')
@section('title', 'Dokumen Akreditasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Documenta Probatorum</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Kelola Dokumen Bukti</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Unggah dan kelola dokumen bukti fisik untuk setiap butir instrumen akreditasi.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Upload Dokumen</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.accreditation.documents.upload') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Instrumen</label>
                    <select name="accreditation_instrument_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">Pilih Instrumen...</option>
                        @foreach($allInstruments as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->number }} — {{ Str::limit($inst->description, 60) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">File (PDF/JPG/DOC, max 10MB)</label>
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="w-full border-2 border-rule px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="SK Tim Pengembang, Notulen Rapat..."></textarea>
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Upload</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="flex flex-wrap gap-2 mb-4 items-center">
            <form method="GET" class="flex flex-wrap gap-2 items-center">
                <select name="standard_id" class="border-2 border-rule px-2 py-1.5 text-xs font-serif" onchange="this.form.submit()">
                    <option value="">— Semua Standar —</option>
                    @foreach($standards as $std)
                        <option value="{{ $std->id }}" {{ $standardId == $std->id ? 'selected' : '' }}>Standar {{ $std->code }}: {{ $std->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="border-2 border-rule px-2 py-1.5 text-xs font-serif" onchange="this.form.submit()">
                    <option value="">— Semua Status —</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </form>
        </div>

        <div class="bg-white border border-rule overflow-hidden">
            <div class="px-5 py-3 border-b border-rule flex justify-between items-center">
                <div class="font-serif text-sm text-gray-600">{{ $documents->total() }} dokumen</div>
            </div>
            <div class="table-scroll">
                <table class="w-full text-sm">
                    <thead class="bg-[var(--c-primary)] text-white">
                        <tr>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Instrumen</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Standar</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">File</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Deskripsi</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Diunggah</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            <tr class="border-t border-rule">
                                <td class="px-4 py-3 font-mono text-xs">{{ $doc->instrument->number ?? '-' }}</td>
                                <td class="px-4 py-3 text-xs">{{ $doc->instrument->standard->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ Storage::disk('public')->url($doc->file_path) }}" target="_blank" class="text-[var(--c-accent)] hover:underline text-xs">
                                        📄 {{ Str::limit(basename($doc->file_path), 20) }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">{{ Str::limit($doc->description, 40) }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs {{ $doc->status === 'approved' ? 'text-green-700 font-semibold' : ($doc->status === 'rejected' ? 'text-red-700' : 'text-amber-600') }}">
                                        {{ $doc->status === 'approved' ? '✓ Disetujui' : ($doc->status === 'rejected' ? '✕ Ditolak' : '⏳ Pending') }}
                                    </span>
                                    @if($doc->reviewer_notes)
                                        <div class="text-[.55rem] text-gray-400 mt-0.5">{{ Str::limit($doc->reviewer_notes, 30) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ $doc->uploader?->name ?? '-' }}<br>
                                    <span class="text-[.6rem]">{{ $doc->created_at->translatedFormat('d M Y') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1">
                                        @if($doc->status === 'pending')
                                        <form method="POST" action="{{ route('admin.accreditation.documents.review', $doc) }}" class="inline-flex gap-1">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button class="text-[.6rem] text-green-700 hover:underline">Setujui</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.accreditation.documents.review', $doc) }}" class="inline-flex gap-1 items-center" onsubmit="return prompt('Alasan penolakan:')">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <input type="text" name="reviewer_notes" class="border border-rule px-1 py-0.5 text-[.6rem]" style="width:70px;" placeholder="Alasan...">
                                            <button class="text-[.6rem] text-red-700 hover:underline">Tolak</button>
                                        </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.accreditation.documents.destroy', $doc) }}" class="inline" onsubmit="return confirm('Hapus dokumen?')">
                                            @csrf @method('DELETE')
                                            <button class="text-[.6rem] text-gray-400 hover:underline">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada dokumen diunggah.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-rule">
                {{ $documents->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
