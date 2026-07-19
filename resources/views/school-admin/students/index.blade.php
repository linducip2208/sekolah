@extends('layouts.school-admin')
@section('title', 'Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="flex justify-between items-end mb-7">
    <div>
        <div class="elite-kicker mb-2">Discipuli</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Daftar Siswa</h1>
        <div class="elite-rule"></div>
        <p class="font-serif text-sm text-gray-600 mt-3">{{ $students->total() }} siswa terdaftar di sekolah Anda.</p>
    </div>
    <a href="{{ route('admin.students.create') }}" class="btn-elite-gold">+ Tambah Siswa</a>
</div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email / NIS"
           class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
    <select name="class_section_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua Rombel —</option>
        @foreach($classSections as $cs)
            <option value="{{ $cs->id }}" @selected(request('class_section_id') == $cs->id)>
                {{ $cs->classRoom?->name }} {{ $cs->section?->name }}
            </option>
        @endforeach
    </select>
    <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div x-data="{ checked: [], get count() { return this.checked.length } }">

{{-- Bulk action bar (sticky) --}}
<div x-show="count > 0" x-cloak class="bg-[var(--c-accent)] text-white px-4 py-3 mb-3 flex items-center justify-between sticky top-0 z-10">
    <span class="elite-kicker text-[.65rem]"><span x-text="count"></span> siswa terpilih</span>
    <form method="POST" action="{{ route('admin.bulk.students') }}" class="flex gap-2 items-center" onsubmit="return confirm('Eksekusi bulk action?')">
        @csrf
        <template x-for="id in checked" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
            <select name="action" required class="text-xs px-2 py-1 text-gray-800 border-0">
                <option value="">— pilih aksi —</option>
                <option value="activate">Aktifkan</option>
                <option value="deactivate">Nonaktifkan</option>
                <option value="send_whatsapp">Kirim WhatsApp</option>
                <option value="delete">Hapus</option>
            </select>
            <input type="text" name="whatsapp_message" placeholder="Pesan WhatsApp..." class="text-xs px-2 py-1 text-gray-800 border-0" style="display:none;" id="wa-msg-input">
            <button type="submit" class="text-xs bg-white text-gray-900 px-3 py-1 hover:bg-gray-100">Eksekusi</button>
        <button type="button" @click="checked = []; document.querySelectorAll('input.bulk-cb').forEach(c => c.checked=false)" class="text-xs underline">Batal</button>
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

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="px-3 py-3 w-8"><input type="checkbox" @change="checked = $event.target.checked ? Array.from(document.querySelectorAll('input.bulk-cb')).map(c => c.value) : []; document.querySelectorAll('input.bulk-cb').forEach(c => c.checked = $event.target.checked)"></th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">NIS</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Rombel</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Gender</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Wali</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $s)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-3 py-3"><input type="checkbox" class="bulk-cb" value="{{ $s->id }}" @change="$event.target.checked ? checked.push($event.target.value) : (checked = checked.filter(v => v !== $event.target.value))"></td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $s->admission_no ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="font-serif font-semibold ink-primary">{{ $s->user?->name }}</div>
                        <div class="text-xs text-gray-500">{{ $s->user?->email }}</div>
                    </td>
                    <td class="px-4 py-3">{{ $s->classSection?->classRoom?->name }} {{ $s->classSection?->section?->name }}</td>
                    <td class="px-4 py-3">
                        <span class="elite-kicker text-[.55rem]">{{ ucfirst($s->gender ?? '—') }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        @if($s->guardian_name)
                            <div>{{ $s->guardian_name }}</div>
                            <div class="text-gray-500">{{ $s->guardian_phone ?? '' }}</div>
                        @else —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.students.edit', $s) }}" class="text-xs underline ink-secondary hover:ink-accent">Edit</a>
                        <form method="POST" action="{{ route('admin.students.destroy', $s) }}" class="inline ml-2"
                              onsubmit="return confirm('Nonaktifkan siswa ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-700 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada siswa.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

</div>

<div class="mt-5">{{ $students->links() }}</div>

@endsection
