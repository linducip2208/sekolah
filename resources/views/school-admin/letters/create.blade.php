@extends('layouts.school-admin')
@section('title', isset($letter) ? 'Edit Surat' : 'Buat Surat')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    $isEdit = isset($letter);
    $letterService = app(\App\Services\LetterService::class);
@endphp

<div class="flex justify-between items-end mb-7">
    <div>
        <div class="elite-kicker mb-2">Epistulae</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $isEdit ? 'Edit Surat' : 'Buat Surat Baru' }}</h1>
        <div class="elite-rule"></div>
        @if($isEdit)
            <p class="font-serif text-sm text-gray-600 mt-3">Nomor: <span class="font-mono">{{ $letter->letter_number }}</span></p>
        @endif
    </div>
    <a href="{{ route('admin.letters.index') }}" class="text-xs underline ink-secondary hover:ink-accent">&larr; Kembali ke daftar</a>
</div>

<form method="POST" action="{{ $isEdit ? route('admin.letters.update', $letter) : route('admin.letters.store') }}" class="space-y-6" x-data="letterForm()">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white border border-rule p-6 space-y-5">
        <h3 class="font-display text-lg ink-primary border-b border-rule pb-2">Informasi Surat</h3>

        @if(!$isEdit)
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Template (opsional)</label>
            <select name="letter_template_id" id="template-select" x-model="templateId" @change="onTemplateChange()" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="">— Tanpa Template (Manual) —</option>
                @foreach($templates as $tpl)
                    <option value="{{ $tpl->id }}" data-content="{{ base64_encode($tpl->content) }}" data-vars="{{ base64_encode(json_encode($tpl->variables ?? [])) }}">
                        {{ $tpl->name }} ({{ $tpl->code }})
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block elite-kicker text-[.6rem] mb-1">Tipe Penerima</label>
                <select name="recipient_type" id="recipient-type" x-model="recipientType" @change="onRecipientChange()" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="student" @selected(($letter->recipient_type ?? 'student') === 'student')>Siswa</option>
                    <option value="staff" @selected(($letter->recipient_type ?? '') === 'staff')>Staff / Guru</option>
                    <option value="other" @selected(($letter->recipient_type ?? '') === 'other')>Lainnya</option>
                </select>
            </div>
            <div x-show="recipientType !== 'other'">
                <label class="block elite-kicker text-[.6rem] mb-1">Pilih {{ $letter->recipient_type ?? 'Penerima' }}</label>
                <select name="recipient_id" id="recipient-id" x-model="recipientId" @change="onRecipientChange()" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">— Pilih —</option>
                    <template x-if="recipientType === 'student'">
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" data-name="{{ $s->user->name }}" data-address="{{ $s->address }}" data-nis="{{ $s->admission_no }}" data-kelas="{{ $s->classSection?->classRoom?->name }} {{ $s->classSection?->section?->name }}" @selected(($letter->recipient_id ?? '') == $s->id)>
                                {{ $s->admission_no }} — {{ $s->user->name }}
                            </option>
                        @endforeach
                    </template>
                    <template x-if="recipientType === 'staff'">
                        @foreach($staffs as $st)
                            <option value="{{ $st->id }}" data-name="{{ $st->user->name }}" data-nip="{{ $st->employee_id }}" data-jabatan="{{ $st->designation }}" @selected(($letter->recipient_id ?? '') == $st->id)>
                                {{ $st->employee_id }} — {{ $st->user->name }}
                            </option>
                        @endforeach
                    </template>
                </select>
            </div>
        </div>

        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Nama Penerima</label>
            <input type="text" name="recipient_name" id="recipient-name" required value="{{ old('recipient_name', $letter->recipient_name ?? '') }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Alamat (opsional)</label>
            <textarea name="recipient_address" id="recipient-address" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">{{ old('recipient_address', $letter->recipient_address ?? '') }}</textarea>
        </div>
    </div>

    <div class="bg-white border border-rule p-6 space-y-5">
        <h3 class="font-display text-lg ink-primary border-b border-rule pb-2">Isi Surat</h3>

        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Perihal / Subject</label>
            <input type="text" name="subject" required value="{{ old('subject', $letter->subject ?? '') }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm font-semibold">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Konten Surat (HTML)</label>
            <textarea name="content" id="content-editor" required rows="14" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm font-mono">{{ old('content', $letter->content ?? '') }}</textarea>
            <p class="text-xs text-gray-400 mt-1">Gunakan variabel seperti {nama}, {nis}, {kelas}, {tanggal} — akan otomatis diganti saat render.</p>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Catatan Internal</label>
            <textarea name="notes" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">{{ old('notes', $letter->notes ?? '') }}</textarea>
        </div>
    </div>

    <div class="bg-white border border-rule p-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <label class="block elite-kicker text-[.6rem]">Status</label>
            <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                <option value="draft" @selected(($letter->status ?? 'draft') === 'draft')>Draft</option>
                <option value="sent" @selected(($letter->status ?? '') === 'sent')>Kirim</option>
                <option value="archived" @selected(($letter->status ?? '') === 'archived')>Arsip</option>
            </select>
        </div>
        <button type="submit" class="btn-elite">{{ $isEdit ? 'Perbarui Surat' : 'Simpan Surat' }}</button>
    </div>
</form>

@endsection

@push('scripts')
<script>
function letterForm() {
    return {
        templateId: '{{ old('letter_template_id', $letter->letter_template_id ?? '') }}',
        recipientType: '{{ old('recipient_type', $letter->recipient_type ?? 'student') }}',
        recipientId: '{{ old('recipient_id', $letter->recipient_id ?? '') }}',

        onRecipientChange() {
            const typeSelect = document.getElementById('recipient-type');
            const idSelect = document.getElementById('recipient-id');

            if (this.recipientType === 'other' || !idSelect || !idSelect.selectedOptions.length) return;

            const opt = idSelect.selectedOptions[0];

            if (this.recipientType === 'student') {
                document.getElementById('recipient-name').value = opt.dataset.name || '';
                document.getElementById('recipient-address').value = opt.dataset.address || '';
            } else if (this.recipientType === 'staff') {
                document.getElementById('recipient-name').value = opt.dataset.name || '';
            }
        },

        onTemplateChange() {
            const tplSelect = document.getElementById('template-select');
            if (!tplSelect || !tplSelect.selectedOptions.length) return;

            const opt = tplSelect.selectedOptions[0];
            const content = opt.dataset.content;
            if (content) {
                document.getElementById('content-editor').value = atob(content);
            }
        },
    };
}
</script>
@endpush
