@extends('layouts.school-admin')
@section('title', 'Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<x-ui.page-header title="Daftar Siswa" subtitle="{{ $students->total() }} siswa terdaftar di sekolah Anda.">
    @if(rescue(fn () => route('admin.import.index'), null, false))
        <a href="{{ route('admin.import.index') }}" class="btn btn-secondary btn-sm">Import</a>
    @endif
    <a href="{{ route('admin.students.create') }}" class="btn btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
        Tambah Siswa
    </a>
</x-ui.page-header>

{{-- Filter chips + saved views --}}
<x-ui.filter-bar :labels="['search' => 'Cari', 'class_section_id' => 'Rombel']"
                 :valueLabels="collect($classSections)->mapWithKeys(fn ($cs) => ['class_section_id.'.$cs->id => trim(($cs->classRoom?->name ?? '').' '.($cs->section?->name ?? ''))])->all()" />

<div x-data="{ checked: [], get count() { return this.checked.length } }">

{{-- Bulk action bar (sticky) --}}
<div x-show="count > 0" x-cloak class="card card-pad mb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky top-16 z-20 border-l-4" style="border-left-color: var(--color-accent);">
    <span class="text-sm font-semibold"><span x-text="count"></span> siswa terpilih</span>
    <form method="POST" action="{{ route('admin.bulk.students') }}" class="flex flex-wrap gap-2 items-center"
          data-confirm="Eksekusi bulk action pada siswa terpilih? Tindakan ini tidak bisa dibatalkan."
          data-confirm-title="Konfirmasi Bulk Action">
        @csrf
        <template x-for="id in checked" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
        <select name="action" required class="select max-w-44 text-sm" aria-label="Pilih aksi massal">
            <option value="">— pilih aksi —</option>
            <option value="activate">Aktifkan</option>
            <option value="deactivate">Nonaktifkan</option>
            <option value="send_whatsapp">Kirim WhatsApp</option>
            <option value="delete">Hapus</option>
        </select>
        <input type="text" name="whatsapp_message" placeholder="Pesan WhatsApp…" class="input max-w-56 text-sm" style="display:none;" id="wa-msg-input">
        <button type="submit" class="btn btn-sm">Eksekusi</button>
        <button type="button" @click="checked = []; document.querySelectorAll('input.bulk-cb').forEach(c => c.checked=false)" class="btn btn-ghost btn-sm">Batal</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const actionSelect = document.querySelector('select[name="action"]');
    const msgInput = document.getElementById('wa-msg-input');
    if (actionSelect && msgInput) {
        actionSelect.addEventListener('change', () => {
            msgInput.style.display = actionSelect.value === 'send_whatsapp' ? 'inline-block' : 'none';
            if (actionSelect.value === 'send_whatsapp') msgInput.required = true;
            else msgInput.required = false;
        });
    }
});
</script>

<div class="card overflow-hidden">
    {{-- Toolbar --}}
    <form method="GET" class="flex flex-col sm:flex-row gap-2 px-4 py-3 border-b border-[var(--color-border)]">
        @if(request()->filled('class_section_id'))<input type="hidden" name="class_section_id" value="{{ request('class_section_id') }}">@endif
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama / email / NIS…" aria-label="Cari siswa" class="input sm:max-w-xs">
        <select name="class_section_id" aria-label="Filter rombel" class="select sm:max-w-52">
            <option value="">— Semua Rombel —</option>
            @foreach($classSections as $cs)
                <option value="{{ $cs->id }}" @selected(request('class_section_id') == $cs->id)>
                    {{ $cs->classRoom?->name }} {{ $cs->section?->name }}
                </option>
            @endforeach
        </select>
        <button class="btn btn-secondary btn-sm flex-shrink-0">Terapkan</button>
    </form>

    <div class="table-scroll">
        <table class="table-elite">
            <thead>
                <tr>
                    <th class="w-10"><input type="checkbox" aria-label="Pilih semua siswa di halaman ini"
                        @change="checked = $event.target.checked ? Array.from(document.querySelectorAll('input.bulk-cb')).map(c => c.value) : []; document.querySelectorAll('input.bulk-cb').forEach(c => c.checked = $event.target.checked)"></th>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Rombel</th>
                    <th>Gender</th>
                    <th>Status</th>
                    <th>Wali</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $s)
                    <tr>
                        <td><input type="checkbox" class="bulk-cb" value="{{ $s->id }}" aria-label="Pilih {{ $s->user?->name }}"
                            @change="$event.target.checked ? checked.push($event.target.value) : (checked = checked.filter(v => v !== $event.target.value))"></td>
                        <td class="font-mono text-xs">{{ $s->admission_no ?? '—' }}</td>
                        <td>
                            <a href="{{ route('admin.students.show', $s) }}" class="font-semibold hover:underline" style="color: var(--color-primary);">{{ $s->user?->name }}</a>
                            <div class="text-xs text-[var(--color-text-muted)]">{{ $s->user?->email }}</div>
                        </td>
                        <td>{{ $s->classSection?->classRoom?->name }} {{ $s->classSection?->section?->name }}</td>
                        <td class="text-[var(--color-text-secondary)]">{{ match ($s->gender) { 'male' => 'Laki-laki', 'female' => 'Perempuan', default => '—' } }}</td>
                        <td><x-ui.status :status="$s->status ?? ($s->user?->is_active ? 'active' : 'inactive')" /></td>
                        <td>
                            @if($s->guardian_name)
                                <div>{{ $s->guardian_name }}</div>
                                <div class="text-xs text-[var(--color-text-muted)]">{{ $s->guardian_phone ?? '' }}</div>
                            @else —
                            @endif
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.students.show', $s) }}" class="text-sm font-semibold hover:underline" style="color: var(--color-primary);">Profil</a>
                            <a href="{{ route('admin.students.edit', $s) }}" class="text-sm font-medium ml-2 hover:underline" style="color: var(--color-text-secondary);">Edit</a>
                            <form method="POST" action="{{ route('admin.students.destroy', $s) }}" class="inline ml-2"
                                  data-confirm="Nonaktifkan {{ $s->user?->name }}? Data akademik dan keuangan tetap tersimpan sebagai riwayat."
                                  data-confirm-title="Konfirmasi Hapus Siswa" data-confirm-danger="true">
                                @csrf @method('DELETE')
                                <button class="text-sm font-medium hover:underline" style="color: var(--color-danger);">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">
                        @if(request()->filled('search') || request()->filled('class_section_id'))
                            <x-feedback.empty-state icon="search" title="Tidak ada siswa yang cocok" description="Coba ubah kata kunci atau filter rombel." />
                        @else
                            <x-feedback.empty-state icon="students" title="Belum ada siswa terdaftar"
                                description="Tambahkan siswa satu per satu atau import dari file Excel/CSV." />
                            <div class="flex justify-center gap-2 -mt-2 pb-6">
                                <a href="{{ route('admin.students.create') }}" class="btn btn-sm">Tambah Manual</a>
                                @if(rescue(fn () => route('admin.import.index'), null, false))
                                    <a href="{{ route('admin.import.index') }}" class="btn btn-secondary btn-sm">Import Excel</a>
                                @endif
                            </div>
                        @endif
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($students->hasPages())
        <div class="px-4 py-3 border-t border-[var(--color-border)] flex items-center justify-between text-sm flex-wrap gap-2">
            <span class="text-[var(--color-text-muted)]">Halaman {{ $students->currentPage() }} dari {{ $students->lastPage() }}</span>
            {{ $students->withQueryString()->links() }}
        </div>
    @endif
</div>

</div>

@endsection
