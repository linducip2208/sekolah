@extends('layouts.school-admin')
@section('title', 'Mitra BKK')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Bursa Kerja Khusus</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Mitra Industri</h1>
    <div class="elite-rule"></div>
</div>

<button onclick="document.getElementById('addPartnerForm').classList.toggle('hidden')" class="btn-elite-gold mb-4">+ Tambah Mitra</button>

<div id="addPartnerForm" class="hidden elite-card p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Tambah Mitra Baru</h3>
    <form method="POST" action="{{ route('admin.bkk.partners.store') }}" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Nama Perusahaan *</label>
            <input type="text" name="company_name" required class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Industri</label>
            <input type="text" name="industry_type" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Kontak Person</label>
            <input type="text" name="contact_person" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Telepon</label>
            <input type="text" name="phone" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Email</label>
            <input type="email" name="email" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Level Kemitraan *</label>
            <select name="partnership_level" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($partnershipLevels as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Status MoU *</label>
            <select name="mou_status" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($mouStatuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Mulai MoU</label>
            <input type="date" name="mou_start_date" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Akhir MoU</label>
            <input type="date" name="mou_end_date" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block elite-kicker text-[.6rem] mb-1">Alamat</label>
            <textarea name="address" rows="2" class="w-full border-2 border-rule px-3 py-2 text-sm"></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block elite-kicker text-[.6rem] mb-1">File MoU</label>
            <input type="file" name="mou_file" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-elite">Simpan Mitra</button>
        </div>
    </form>
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-4 bg-white border border-rule p-4">
    <select name="status" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Semua Status MoU —</option>
        @foreach($mouStatuses as $key => $label)
        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <select name="level" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Semua Level —</option>
        @foreach($partnershipLevels as $key => $label)
        <option value="{{ $key }}" {{ request('level') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Filter</button>
    <a href="{{ route('admin.bkk.partners') }}" class="text-xs text-gray-500 hover:ink-accent self-center">Reset</a>
</form>

<div class="elite-card overflow-hidden">
    <div class="table-scroll">
    <table class="table-elite w-full text-sm">
        <thead>
            <tr>
                <th>Perusahaan</th>
                <th>Industri</th>
                <th>Kontak</th>
                <th>Level</th>
                <th>MoU</th>
                <th>Periode</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($partners as $partner)
            <tr>
                <td>
                    <div class="font-serif font-semibold">{{ $partner->company_name }}</div>
                    <div class="text-xs text-gray-500">{{ $partner->contact_person ?? '—' }}</div>
                </td>
                <td class="text-xs">{{ $partner->industry_type ?? '—' }}</td>
                <td class="text-xs">{{ $partner->phone ?? '—' }}<br>{{ $partner->email ?? '—' }}</td>
                <td>
                    <span class="inline-block px-2 py-0.5 text-[.6rem] uppercase font-semibold rounded
                        {{ $partner->partnership_level === 'gold' ? 'bg-yellow-100 text-yellow-800' : ($partner->partnership_level === 'silver' ? 'bg-gray-100 text-gray-700' : 'bg-orange-100 text-orange-700') }}">
                        {{ $partnershipLevels[$partner->partnership_level] ?? $partner->partnership_level }}
                    </span>
                </td>
                <td>
                    <span class="inline-block px-2 py-0.5 text-[.6rem] uppercase font-semibold rounded
                        {{ $partner->mou_status === 'active' ? 'bg-green-100 text-green-800' : ($partner->mou_status === 'expired' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600') }}">
                        {{ $mouStatuses[$partner->mou_status] ?? $partner->mou_status }}
                    </span>
                </td>
                <td class="text-xs">{{ $partner->mou_start_date?->format('d/m/Y') ?? '—' }} — {{ $partner->mou_end_date?->format('d/m/Y') ?? '—' }}</td>
                <td class="text-right whitespace-nowrap">
                    <button onclick="editPartner({{ $partner->id }}, '{{ e($partner->company_name) }}', '{{ e($partner->industry_type) }}', '{{ e($partner->contact_person) }}', '{{ e($partner->phone) }}', '{{ e($partner->email) }}', '{{ e($partner->address) }}', '{{ $partner->mou_status }}', '{{ $partner->partnership_level }}', '{{ $partner->mou_start_date?->format('Y-m-d') }}', '{{ $partner->mou_end_date?->format('Y-m-d') }}')" class="text-xs underline ink-secondary hover:ink-accent mr-2">Edit</button>
                    <form method="POST" action="{{ route('admin.bkk.partners.delete', $partner) }}" class="inline" onsubmit="return confirm('Hapus mitra ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs underline text-red-600">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada mitra BKK.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-4">{{ $partners->links() }}</div>

<div id="editPartnerForm" class="hidden elite-card p-6 mt-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Edit Mitra</h3>
    <form id="editPartnerFormTag" method="POST" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-4">
        @csrf @method('PUT')
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Nama Perusahaan *</label>
            <input type="text" name="company_name" id="ep_company_name" required class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Industri</label>
            <input type="text" name="industry_type" id="ep_industry_type" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Kontak Person</label>
            <input type="text" name="contact_person" id="ep_contact_person" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Telepon</label>
            <input type="text" name="phone" id="ep_phone" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Email</label>
            <input type="email" name="email" id="ep_email" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Level Kemitraan *</label>
            <select name="partnership_level" id="ep_partnership_level" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($partnershipLevels as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Status MoU *</label>
            <select name="mou_status" id="ep_mou_status" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($mouStatuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Mulai MoU</label>
            <input type="date" name="mou_start_date" id="ep_mou_start_date" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Akhir MoU</label>
            <input type="date" name="mou_end_date" id="ep_mou_end_date" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block elite-kicker text-[.6rem] mb-1">Alamat</label>
            <textarea name="address" id="ep_address" rows="2" class="w-full border-2 border-rule px-3 py-2 text-sm"></textarea>
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-elite">Perbarui Mitra</button>
            <button type="button" onclick="document.getElementById('editPartnerForm').classList.add('hidden')" class="btn-elite-ghost ml-2">Batal</button>
        </div>
    </form>
</div>

<script>
function editPartner(id, name, industry, contact, phone, email, address, mouStatus, level, start, end) {
    document.getElementById('addPartnerForm').classList.add('hidden');
    const form = document.getElementById('editPartnerForm');
    form.classList.remove('hidden');
    form.scrollIntoView({behavior: 'smooth'});
    document.getElementById('editPartnerFormTag').action = '{{ route('admin.bkk.partners.update', ['partner' => '__ID__']) }}'.replace('__ID__', id);
    document.getElementById('ep_company_name').value = name;
    document.getElementById('ep_industry_type').value = industry;
    document.getElementById('ep_contact_person').value = contact;
    document.getElementById('ep_phone').value = phone;
    document.getElementById('ep_email').value = email;
    document.getElementById('ep_address').value = address;
    document.getElementById('ep_mou_status').value = mouStatus;
    document.getElementById('ep_partnership_level').value = level;
    document.getElementById('ep_mou_start_date').value = start;
    document.getElementById('ep_mou_end_date').value = end;
}
</script>
@endsection
