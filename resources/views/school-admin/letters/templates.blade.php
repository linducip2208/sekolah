@extends('layouts.school-admin')
@section('title', 'Template Surat')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="flex justify-between items-end mb-7">
    <div>
        <div class="elite-kicker mb-2">Epistulae</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Template Surat</h1>
        <div class="elite-rule"></div>
        <p class="font-serif text-sm text-gray-600 mt-3">Kelola template surat resmi sekolah.</p>
    </div>
    <button type="button" onclick="openTemplateModal()" class="btn-elite">+ Tambah Template</button>
</div>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kode</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kategori</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($templates as $tpl)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-serif font-semibold ink-primary">{{ $tpl->name }}</div>
                        <div class="text-xs text-gray-500">Variabel: {{ is_array($tpl->variables) ? count($tpl->variables) : 0 }} / {{ is_array($tpl->variables) ? implode(', ', array_keys($tpl->variables)) : '' }}</div>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $tpl->code }}</td>
                    <td class="px-4 py-3">
                        <span class="elite-kicker text-[.55rem]">{{ $tpl->category_label }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs {{ $tpl->is_active ? 'text-green-700' : 'text-red-600' }}">{{ $tpl->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <button onclick="editTemplate({{ $tpl->id }}, '{{ addslashes($tpl->name) }}', '{{ $tpl->code }}', '{{ $tpl->category }}', {{ $tpl->is_active ? 'true' : 'false' }})" class="text-xs underline ink-secondary hover:ink-accent">Edit</button>
                        <form method="POST" action="{{ route('admin.letters.templates.delete', $tpl) }}" class="inline ml-2" onsubmit="return confirm('Hapus template ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-700 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada template surat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $templates->links() }}</div>

{{-- Template Modal --}}
<div id="template-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center" onclick="if(event.target===this) closeTemplateModal()">
    <div class="bg-white max-w-2xl w-full mx-4 rounded-none border-2 border-[var(--c-primary)] shadow-2xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="bg-[var(--c-primary)] text-white px-6 py-4 flex justify-between items-center">
            <h2 class="font-display text-xl" id="template-modal-title">Tambah Template</h2>
            <button onclick="closeTemplateModal()" class="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.letters.templates.store') }}" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="tpl-form-method" value="POST">
            <input type="hidden" name="id" id="tpl-id">
            <div>
                <label class="block elite-kicker text-[.6rem] mb-1">Nama Template</label>
                <input type="text" name="name" id="tpl-name" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Contoh: Surat Keterangan Aktif">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block elite-kicker text-[.6rem] mb-1">Kode (prefix nomor surat)</label>
                    <input type="text" name="code" id="tpl-code" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm uppercase" placeholder="SK.AKTIF">
                </div>
                <div>
                    <label class="block elite-kicker text-[.6rem] mb-1">Kategori</label>
                    <select name="category" id="tpl-category" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="sk">Surat Keputusan (SK)</option>
                        <option value="surat-keterangan">Surat Keterangan</option>
                        <option value="surat-izin">Surat Izin</option>
                        <option value="surat-panggilan">Surat Panggilan</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block elite-kicker text-[.6rem] mb-1">Variabel (dipisah koma)</label>
                <input type="text" name="variables_raw" id="tpl-variables" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs" placeholder="nama, nis, kelas, alamat, tanggal">
                <p class="text-xs text-gray-400 mt-1">Variabel yang tersedia: {nama}, {nis}, {nip}, {kelas}, {alamat}, {nama_wali}, {gender}, {tempat_lahir}, {tanggal_lahir}, {jabatan}, {departemen}, {sekolah}, {tanggal}, {nomor_surat}, {perihal}</p>
            </div>
            <div>
                <label class="block elite-kicker text-[.6rem] mb-1">Konten Template (HTML)</label>
                <textarea name="content" id="tpl-content" required rows="8" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm font-mono" placeholder="&lt;p&gt;Yang bertanda tangan di bawah ini...&lt;/p&gt;"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="tpl-active" value="1" checked class="w-4 h-4">
                <label for="tpl-active" class="text-sm font-serif">Aktif</label>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeTemplateModal()" class="text-xs text-gray-500 underline">Batal</button>
                <button type="submit" class="btn-elite">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openTemplateModal() {
    document.getElementById('template-modal-title').textContent = 'Tambah Template';
    document.getElementById('tpl-form-method').value = 'POST';
    document.querySelector('#template-modal form').action = '{{ route("admin.letters.templates.store") }}';
    document.getElementById('tpl-id').value = '';
    document.getElementById('tpl-name').value = '';
    document.getElementById('tpl-code').value = '';
    document.getElementById('tpl-category').value = 'sk';
    document.getElementById('tpl-variables').value = '';
    document.getElementById('tpl-content').value = '';
    document.getElementById('tpl-active').checked = true;
    document.getElementById('template-modal').classList.remove('hidden');
    document.getElementById('template-modal').classList.add('flex');
}

function closeTemplateModal() {
    document.getElementById('template-modal').classList.add('hidden');
    document.getElementById('template-modal').classList.remove('flex');
}
</script>
@endpush
