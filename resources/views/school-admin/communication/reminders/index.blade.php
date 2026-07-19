@extends('layouts.school-admin')
@section('title', 'Pengingat Otomatis')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div x-data="reminderManager()" class="max-w-6xl mx-auto">
<div class="mb-7 flex justify-between items-end">
    <div>
        <div class="elite-kicker mb-2">Komunikasi</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Pengingat Otomatis</h1>
        <div class="elite-rule"></div>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.reminders.logs.index') }}" class="btn-elite-ghost text-xs">Lihat Log</a>
        <button @click="openModal()" class="btn-elite">+ Tambah Jadwal</button>
    </div>
</div>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Penerima</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">H- Hari</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Channel</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $s)
            <tr class="border-t border-rule hover:bg-stone-50">
                <td class="px-4 py-3">
                    <div class="font-serif font-semibold">{{ $s->name }}</div>
                    <div class="text-xs text-stone-500 mt-0.5">{{ \Illuminate\Support\Str::limit($s->message_template, 60) }}</div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700">
                        {{ match($s->recipient_type) { 'parent' => 'Orang Tua', 'student' => 'Siswa', 'staff' => 'Staff', default => $s->recipient_type } }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs font-mono">
                    {{ implode(', ', $s->trigger_days_before ?? []) }} hari sebelum
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded {{ $s->reminder_type === 'wa' ? 'bg-emerald-100 text-emerald-700' : ($s->reminder_type === 'email' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ strtoupper($s->reminder_type) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.reminders.toggle', $s) }}" class="inline">
                        @csrf
                        <button class="text-xs font-semibold {{ $s->is_active ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $s->is_active ? 'AKTIF' : 'DIJEDA' }}
                        </button>
                    </form>
                    @if($s->last_triggered_at)
                        <div class="text-[.6rem] text-stone-400 mt-0.5">Terakhir: {{ $s->last_triggered_at->format('d M H:i') }}</div>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <button @click="testModal({{ $s->id }}, '{{ $s->name }}')" class="text-xs underline ink-accent">Test</button>
                        <button @click="editModal({{ $s->id }}, '{{ addslashes($s->name) }}', '{{ $s->recipient_type }}', {{ json_encode($s->trigger_days_before) }}, '{{ $s->reminder_type }}', '{{ addslashes($s->message_template) }}')" class="text-xs underline ink-secondary">Edit</button>
                        <form method="POST" action="{{ route('admin.reminders.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus jadwal ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs underline text-red-500 hover:text-red-700">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada jadwal pengingat. Tambahkan jadwal pertama.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Add/Edit Modal --}}
<div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(11,29,58,.75);">
    <div @click.outside="modalOpen = false" class="bg-white w-full max-w-xl shadow-2xl border border-rule mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-rule sticky top-0 bg-white">
            <h3 class="font-serif font-semibold text-lg ink-primary" x-text="editingId ? 'Edit Jadwal' : 'Tambah Jadwal'"></h3>
            <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
        </div>
        <form :action="editingId ? '{{ url('admin/reminders') }}/' + editingId : '{{ route('admin.reminders.store') }}'" method="POST" class="p-5 space-y-4">
            @csrf
            <template x-if="editingId">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Nama Jadwal</label>
                <input name="name" x-model="form.name" required maxlength="200" placeholder="e.g. Pengingat SPP, Pengingat Ujian" class="w-full border-2 border-rule px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Penerima</label>
                <select name="recipient_type" x-model="form.recipient_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                    <option value="parent">Orang Tua / Wali</option>
                    <option value="student">Siswa</option>
                    <option value="staff">Staff / Guru</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Hari Sebelum (pisahkan dengan koma)</label>
                <input name="trigger_days_before[]" x-model="form.trigger_days" required placeholder="e.g. 7,3,1" class="w-full border-2 border-rule px-3 py-2 text-sm font-mono">
                <p class="text-[.6rem] text-stone-400 mt-1">Jumlah hari sebelum jatuh tempo untuk mengirim pengingat.</p>
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Channel</label>
                <select name="reminder_type" x-model="form.reminder_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                    <option value="wa">WhatsApp</option>
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Template Pesan</label>
                <textarea name="message_template" x-model="form.message_template" rows="5" required maxlength="2000" class="w-full border-2 border-rule px-3 py-2 text-sm font-serif"></textarea>
                <p class="text-[.6rem] text-stone-400 mt-1">Variabel: {nama}, {jumlah}, {jatuh_tempo}, {link_bayar}, {sekolah}, {kelas}, {nis}, {tanggal}</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="modalOpen = false" class="btn-elite-ghost text-xs">Batal</button>
                <button type="submit" class="btn-elite text-xs" x-text="editingId ? 'Simpan' : 'Tambah'"></button>
            </div>
        </form>
    </div>
</div>

{{-- Test Send Modal --}}
<div x-show="testOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(11,29,58,.75);">
    <div @click.outside="testOpen = false" class="bg-white w-full max-w-md shadow-2xl border border-rule mx-4">
        <div class="flex items-center justify-between px-5 py-4 border-b border-rule">
            <h3 class="font-serif font-semibold text-lg ink-primary">Uji Kirim — <span x-text="testName"></span></h3>
            <button @click="testOpen = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
        </div>
        <form :action="'{{ url('admin/reminders') }}/' + testId + '/test'" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">No HP Tujuan</label>
                <input name="test_phone" required maxlength="30" placeholder="e.g. 6281234567890" class="w-full border-2 border-rule px-3 py-2 text-sm font-mono">
            </div>
            <p class="text-xs text-stone-500">Pesan akan dikirim sesuai template jadwal dengan data dummy.</p>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="testOpen = false" class="btn-elite-ghost text-xs">Batal</button>
                <button type="submit" class="btn-elite text-xs">Kirim Uji Coba</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
function reminderManager() {
    return {
        modalOpen: false, editingId: null,
        testOpen: false, testId: null, testName: '',
        form: { name: '', recipient_type: 'parent', trigger_days: '7,3,1', reminder_type: 'wa', message_template: '' },
        openModal() {
            this.editingId = null;
            this.form = { name: '', recipient_type: 'parent', trigger_days: '7,3,1', reminder_type: 'wa', message_template: 'Yth. {nama},\n\nIni adalah pengingat pembayaran {sekolah}.\nJumlah: {jumlah}\nJatuh tempo: {jatuh_tempo}\n\nSilakan lakukan pembayaran.\nTerima kasih.' };
            this.modalOpen = true;
        },
        editModal(id, name, recipient, triggerDays, type, template) {
            this.editingId = id;
            this.form.name = name;
            this.form.recipient_type = recipient;
            this.form.trigger_days = Array.isArray(triggerDays) ? triggerDays.join(',') : triggerDays;
            this.form.reminder_type = type;
            this.form.message_template = template;
            this.modalOpen = true;
        },
        testModal(id, name) {
            this.testId = id; this.testName = name; this.testOpen = true;
        }
    };
}
</script>
@endpush
