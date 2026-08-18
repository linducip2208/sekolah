@extends('layouts.school-admin')
@section('title', 'Tanda Tangan Digital')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="space-y-6">
    <div>
        <div class="text-sm text-[var(--color-text-muted)]">Digital Documents</div>
        <h1 class="page-title mt-1">Tanda Tangan Digital</h1>
        <p class="text-sm text-[var(--color-text-secondary)] mt-1">Kelola tanda tangan digital pribadi untuk menandatangani dokumen.</p>
    </div>

    {{-- Upload Form --}}
    <div class="card card-pad">
        <h2 class="section-title mb-4">Unggah Tanda Tangan Baru</h2>
        @if($signatures->where('user_id', auth()->id())->isEmpty())
        <form action="{{ route('admin.digital-signatures.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4" x-data="{ preview: null }">
            @csrf
            <div>
                <label class="elite-label">Gambar Tanda Tangan</label>
                <input type="file" name="signature_image" accept="image/*" required class="input-elite"
                       @change="if($event.target.files[0]) { const r=new FileReader(); r.onload=e=>preview=e.target.result; r.readAsDataURL($event.target.files[0]); }">
                <div x-show="preview" class="mt-2">
                    <img :src="preview" class="h-24 rounded-lg border border-[var(--color-border)] bg-white p-2">
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="elite-label">PIN Tanda Tangan</label>
                    <input type="password" name="pin_code" required minlength="4" class="input-elite" placeholder="Minimal 4 digit">
                </div>
                <div>
                    <label class="elite-label">Konfirmasi PIN</label>
                    <input type="password" name="pin_code_confirmation" required class="input-elite" placeholder="Ulangi PIN">
                </div>
            </div>
            <button type="submit" class="btn-elite">Simpan Tanda Tangan</button>
        </form>
        @else
            <p class="text-sm text-[var(--color-text-secondary)]">Anda sudah memiliki tanda tangan digital aktif.</p>
        @endif
    </div>

    {{-- Existing Signatures --}}
    <div class="card">
        <div class="px-5 py-4 border-b border-[var(--color-border)]">
            <h2 class="section-title">Tanda Tangan Aktif</h2>
        </div>
        @if($signatures->isEmpty())
            <div class="p-6"><x-feedback.empty-state icon="pen" title="Belum ada tanda tangan" description="Unggah tanda tangan digital di atas." /></div>
        @else
            <div class="table-scroll">
                <table class="table-elite">
                    <thead><tr><th>Pengguna</th><th>Preview</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @foreach($signatures as $sig)
                        <tr>
                            <td class="font-semibold">{{ $sig->user?->name }}</td>
                            <td><img src="{{ Storage::disk('public')->url($sig->signature_image_path) }}" class="h-10 bg-white rounded p-1 border border-[var(--color-border)]"></td>
                            <td>
                                @if($sig->is_active)
                                    <x-ui.badge variant="success">Aktif</x-ui.badge>
                                @else
                                    <x-ui.badge variant="danger">Nonaktif</x-ui.badge>
                                @endif
                            </td>
                            <td class="text-sm text-[var(--color-text-muted)]">{{ $sig->created_at->format('d M Y') }}</td>
                            <td>
                                <form action="{{ route('admin.digital-signatures.destroy', $sig) }}" method="POST" onsubmit="return confirm('Hapus tanda tangan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Sign Document --}}
    <div class="card card-pad">
        <h2 class="section-title mb-4">Tandatangani Dokumen</h2>
        <form action="{{ route('admin.digital-signatures.sign') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="elite-label">Tipe Dokumen</label>
                    <select name="document_type" class="input-elite" required>
                        <option value="">Pilih tipe...</option>
                        <option value="report_card">Rapor</option>
                        <option value="certificate">Sertifikat</option>
                        <option value="letter">Surat</option>
                    </select>
                </div>
                <div>
                    <label class="elite-label">ID Dokumen</label>
                    <input type="number" name="document_id" required class="input-elite" placeholder="ID dokumen">
                </div>
            </div>
            <div>
                <label class="elite-label">PIN Tanda Tangan</label>
                <input type="password" name="pin_code" required class="input-elite" placeholder="PIN tanda tangan Anda">
            </div>
            <button type="submit" class="btn-elite">Tandatangani</button>
        </form>
    </div>
</div>
@endsection
