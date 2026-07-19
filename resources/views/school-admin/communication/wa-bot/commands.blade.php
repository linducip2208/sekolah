@extends('layouts.school-admin')
@section('title', 'WhatsApp Bot')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div x-data="waBotCommands()" class="max-w-6xl mx-auto">
<div class="mb-7 flex justify-between items-end">
    <div>
        <div class="elite-kicker mb-2">Komunikasi</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">WhatsApp Bot</h1>
        <div class="elite-rule"></div>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.wa-bot.conversations.index') }}" class="btn-elite-ghost text-xs">Riwayat Percakapan</a>
        <button @click="openModal()" class="btn-elite">+ Tambah Perintah</button>
    </div>
</div>

<div class="bg-white border border-rule overflow-hidden mb-6">
    <div class="p-5 border-b border-rule bg-stone-50">
        <p class="text-sm text-stone-600 font-serif">Bot akan otomatis membalas pesan WhatsApp yang masuk. Atur kata kunci dan respons di bawah ini. Nomor WhatsApp orang tua harus terdaftar di data siswa (guardian_phone / whatsapp_phone).</p>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kata Kunci</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tipe</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Fungsi / Respons</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($commands as $cmd)
            <tr class="border-t border-rule hover:bg-stone-50">
                <td class="px-4 py-3 font-mono font-semibold text-sm">{{ $cmd->command_keyword }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded {{ $cmd->response_type === 'text_function' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ $cmd->response_type === 'text_function' ? 'Fungsi' : 'Teks Statis' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs text-stone-600 max-w-xs truncate">
                    {{ $cmd->function_method ?? \Illuminate\Support\Str::limit($cmd->static_response, 60) }}
                </td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.wa-bot.commands.toggle', $cmd) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-semibold {{ $cmd->is_active ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ $cmd->is_active ? 'AKTIF' : 'NONAKTIF' }}
                        </button>
                    </form>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <button @click="editModal({{ $cmd->id }}, '{{ $cmd->command_keyword }}', '{{ $cmd->response_type }}', '{{ addslashes($cmd->static_response ?? '') }}', '{{ $cmd->function_method ?? '' }}', '{{ addslashes($cmd->description ?? '') }}')" class="text-xs underline ink-secondary hover:ink-accent">Edit</button>
                        <form method="POST" action="{{ route('admin.wa-bot.commands.destroy', $cmd) }}" class="inline" onsubmit="return confirm('Hapus perintah ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs underline text-red-500 hover:text-red-700">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada perintah bot. Tambahkan kata kunci pertama.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Test Send --}}
<div class="bg-white border border-rule p-5">
    <h2 class="font-serif font-semibold text-lg ink-primary mb-3">Uji Coba Bot</h2>
    <form method="POST" action="{{ route('admin.wa-bot.test') }}" class="grid md:grid-cols-3 gap-3">
        @csrf
        <input name="phone" required maxlength="30" placeholder="No HP (e.g. 62812...)" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <input name="message" required maxlength="500" placeholder="Pesan uji coba..." class="md:col-span-1 border-2 border-rule px-3 py-2 font-serif text-sm">
        <button class="btn-elite">Kirim Uji Coba</button>
    </form>
</div>

{{-- Add/Edit Modal --}}
<div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(11,29,58,.75);">
    <div @click.outside="modalOpen = false" class="bg-white w-full max-w-lg shadow-2xl border border-rule mx-4">
        <div class="flex items-center justify-between px-5 py-4 border-b border-rule">
            <h3 class="font-serif font-semibold text-lg ink-primary" x-text="editingId ? 'Edit Perintah' : 'Tambah Perintah'"></h3>
            <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
        </div>
        <form :action="editingId ? '{{ url('admin/wa-bot/commands') }}/' + editingId : '{{ route('admin.wa-bot.commands.store') }}'" method="POST" class="p-5 space-y-4">
            @csrf
            <template x-if="editingId">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Kata Kunci</label>
                <input name="command_keyword" x-model="form.command_keyword" required maxlength="100" placeholder="e.g. nilai, jadwal, spp, absen" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Tipe Respons</label>
                <select name="response_type" x-model="form.response_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                    <option value="static">Teks Statis</option>
                    <option value="text_function">Fungsi Otomatis</option>
                </select>
            </div>
            <div x-show="form.response_type === 'static'">
                <label class="block text-xs font-semibold text-stone-600 mb-1">Respons Teks</label>
                <textarea name="static_response" x-model="form.static_response" rows="3" maxlength="1000" placeholder="Teks balasan..." class="w-full border-2 border-rule px-3 py-2 text-sm font-serif"></textarea>
            </div>
            <div x-show="form.response_type === 'text_function'">
                <label class="block text-xs font-semibold text-stone-600 mb-1">Fungsi</label>
                <select name="function_method" x-model="form.function_method" class="w-full border-2 border-rule px-3 py-2 text-sm">
                    <option value="">-- Pilih Fungsi --</option>
                    <option value="getNilai">getNilai — Cek nilai siswa</option>
                    <option value="getJadwal">getJadwal — Jadwal hari ini</option>
                    <option value="getTagihan">getTagihan — Cek tagihan SPP</option>
                    <option value="getAbsensi">getAbsensi — Cek absensi</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Deskripsi (opsional)</label>
                <input name="description" x-model="form.description" maxlength="255" placeholder="Penjelasan singkat..." class="w-full border-2 border-rule px-3 py-2 text-sm">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="modalOpen = false" class="btn-elite-ghost text-xs">Batal</button>
                <button type="submit" class="btn-elite text-xs" x-text="editingId ? 'Simpan' : 'Tambah'"></button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
function waBotCommands() {
    return {
        modalOpen: false,
        editingId: null,
        form: {
            command_keyword: '',
            response_type: 'static',
            static_response: '',
            function_method: '',
            description: '',
        },
        openModal() {
            this.editingId = null;
            this.form = { command_keyword: '', response_type: 'static', static_response: '', function_method: '', description: '' };
            this.modalOpen = true;
        },
        editModal(id, keyword, type, staticResp, func, desc) {
            this.editingId = id;
            this.form.command_keyword = keyword;
            this.form.response_type = type;
            this.form.static_response = staticResp;
            this.form.function_method = func;
            this.form.description = desc;
            this.modalOpen = true;
        }
    };
}
</script>
@endpush
