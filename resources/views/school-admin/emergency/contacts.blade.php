@extends('layouts.school-admin')
@section('title', 'Kontak Darurat')
@section('sidebar')
    @include('school-admin.partials.sidebar')
@endsection

@section('content')
<div class="mb-7 flex justify-between items-start flex-wrap gap-3">
    <div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Kontak Darurat</h1>
        <div class="elite-rule"></div>
    </div>
    <a href="{{ route('admin.emergency.index') }}" class="btn-elite-ghost">← Kembali</a>
</div>

<div class="grid lg:grid-cols-2 gap-7">
    {{-- Add contact form --}}
    <div class="bg-white border border-rule p-7">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Tambah Kontak</h3>
        <form method="POST" action="{{ route('admin.emergency.contacts.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="elite-kicker block mb-1">Nama</label>
                <input type="text" name="name" required class="w-full border border-rule p-2.5" placeholder="Kapolsek / Rumah Sakit / Satpam...">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Telepon</label>
                <input type="text" name="phone" class="w-full border border-rule p-2.5" placeholder="08xxxxxxxxxx">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Email</label>
                <input type="email" name="email" class="w-full border border-rule p-2.5" placeholder="email@contoh.com">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Tipe Kontak</label>
                <select name="contact_type" required class="w-full border border-rule p-2.5">
                    <option value="police">Polisi</option>
                    <option value="fire">Pemadam Kebakaran</option>
                    <option value="hospital">Rumah Sakit</option>
                    <option value="security">Keamanan / Satpam</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Prioritas (0 = tertinggi)</label>
                <input type="number" name="priority_order" value="0" class="w-full border border-rule p-2.5">
            </div>
            <button type="submit" class="btn-elite w-full">Simpan Kontak</button>
        </form>
    </div>

    {{-- Contact list --}}
    <div>
        <h3 class="elite-h3 text-lg ink-primary mb-4">Daftar Kontak</h3>
        @forelse($contacts as $c)
        <div class="bg-white border border-rule p-5 mb-3 {{ $c->is_active ? '' : 'opacity-50' }}">
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-serif font-semibold ink-primary">{{ $c->name }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        @php
                            $typeLabels = [
                                'police' => 'Polisi', 'fire' => 'Pemadam', 'hospital' => 'Rumah Sakit',
                                'security' => 'Keamanan', 'other' => 'Lainnya',
                            ];
                        @endphp
                        {{ $typeLabels[$c->contact_type] ?? $c->contact_type }} · Prioritas {{ $c->priority_order }}
                    </div>
                    @if($c->phone)
                    <div class="font-mono text-sm mt-1">📞 {{ $c->phone }}</div>
                    @endif
                    @if($c->email)
                    <div class="font-mono text-xs text-gray-500">✉️ {{ $c->email }}</div>
                    @endif
                </div>
                <div class="flex gap-1">
                    <button onclick="editContact({{ $c->id }}, '{{ $c->name }}', '{{ $c->phone }}', '{{ $c->email }}', '{{ $c->contact_type }}', {{ $c->priority_order }}, {{ $c->is_active ? 1 : 0 }})"
                            class="text-xs text-blue-600 hover:underline">Edit</button>
                    <form method="POST" action="{{ route('admin.emergency.contacts.delete', $c) }}" class="inline" onsubmit="return confirm('Hapus kontak ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-600 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <p class="font-serif text-gray-500 italic">Belum ada kontak darurat.</p>
        @endforelse
    </div>
</div>

{{-- Edit Modal --}}
<div id="editContactModal" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center" style="background:rgba(0,0,0,.5);">
    <div class="bg-white p-7 max-w-md w-full mx-4 border border-rule">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Edit Kontak</h3>
        <form id="editContactForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" name="name" id="edit_name">
            <div>
                <label class="elite-kicker block mb-1">Nama</label>
                <input type="text" name="name" id="edit_name" required class="w-full border border-rule p-2.5">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Telepon</label>
                <input type="text" name="phone" id="edit_phone" class="w-full border border-rule p-2.5">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Email</label>
                <input type="email" name="email" id="edit_email" class="w-full border border-rule p-2.5">
            </div>
            <div>
                <label class="elite-kicker block mb-1">Tipe</label>
                <select name="contact_type" id="edit_type" required class="w-full border border-rule p-2.5">
                    <option value="police">Polisi</option>
                    <option value="fire">Pemadam Kebakaran</option>
                    <option value="hospital">Rumah Sakit</option>
                    <option value="security">Keamanan / Satpam</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="elite-kicker block mb-1">Prioritas</label>
                <input type="number" name="priority_order" id="edit_priority" value="0" class="w-full border border-rule p-2.5">
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" id="edit_active">
                <span class="text-sm">Aktif</span>
            </label>
            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('editContactModal').style.display='none'" class="btn-elite-ghost flex-1">Batal</button>
                <button type="submit" class="btn-elite flex-1">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function editContact(id, name, phone, email, type, priority, active) {
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_priority').value = priority;
    document.getElementById('edit_active').checked = active == 1;
    document.getElementById('editContactForm').action = `/admin/emergency/contacts/${id}/update`;
    document.getElementById('editContactModal').style.display = 'flex';
}
</script>
@endsection
