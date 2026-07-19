@extends('layouts.school-admin')
@section('title', 'Penempatan BKK')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Bursa Kerja Khusus</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Penempatan Siswa</h1>
    <div class="elite-rule"></div>
</div>

<button onclick="document.getElementById('addPlacementForm').classList.toggle('hidden')" class="btn-elite-gold mb-4">+ Catat Penempatan</button>

<div id="addPlacementForm" class="hidden elite-card p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Catat Penempatan Baru</h3>
    <form method="POST" action="{{ route('admin.bkk.placements.store') }}" class="grid md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Siswa *</label>
            <select name="student_id" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="">— Pilih Siswa —</option>
                @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Mitra *</label>
            <select name="bkk_partner_id" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="">— Pilih Mitra —</option>
                @foreach($partners as $p)<option value="{{ $p->id }}">{{ $p->company_name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Posisi *</label>
            <input type="text" name="position" required class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Kontrak *</label>
            <select name="contract_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($contractTypes as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Penempatan *</label>
            <input type="date" name="placement_date" required class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Mulai Kerja</label>
            <input type="date" name="start_date" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Gaji (Rp)</label>
            <input type="number" name="salary" min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Status *</label>
            <select name="status" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($statuses as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Pembimbing</label>
            <input type="text" name="supervisor_name" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Telp Pembimbing</label>
            <input type="text" name="supervisor_phone" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-elite">Simpan</button>
        </div>
    </form>
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-4 bg-white border border-rule p-4">
    <select name="status" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Semua Status —</option>
        @foreach($statuses as $k => $l)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach
    </select>
    <select name="contract" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Semua Kontrak —</option>
        @foreach($contractTypes as $k => $l)<option value="{{ $k }}" {{ request('contract') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Filter</button>
    <a href="{{ route('admin.bkk.placements') }}" class="text-xs text-gray-500 hover:ink-accent self-center">Reset</a>
</form>

<div class="elite-card overflow-hidden">
    <div class="table-scroll">
    <table class="table-elite w-full text-sm">
        <thead>
            <tr>
                <th>Siswa</th>
                <th>Mitra</th>
                <th>Posisi</th>
                <th>Kontrak</th>
                <th>Gaji</th>
                <th>Pembimbing</th>
                <th>Status</th>
                <th>Tgl</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($placements as $placement)
            <tr>
                <td><span class="font-serif font-semibold text-sm">{{ $placement->student?->name }}</span></td>
                <td class="text-xs">{{ $placement->partner?->company_name }}</td>
                <td class="text-xs">{{ $placement->position }}</td>
                <td><span class="text-[.6rem] uppercase">{{ $contractTypes[$placement->contract_type] ?? $placement->contract_type }}</span></td>
                <td class="font-mono text-xs">Rp {{ number_format($placement->salary, 0, ',', '.') }}</td>
                <td class="text-xs">{{ $placement->supervisor_name ?? '—' }}</td>
                <td><span class="text-xs px-2 py-0.5 rounded {{ $placement->status === 'active' ? 'bg-green-100 text-green-800' : ($placement->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($placement->status) }}</span></td>
                <td class="text-xs">{{ $placement->placement_date->format('d/m/Y') }}</td>
                <td class="text-right whitespace-nowrap">
                    <button onclick="editPlacement({{ $placement->id }}, {{ $placement->student_id }}, {{ $placement->bkk_partner_id }}, '{{ e($placement->position) }}', '{{ $placement->contract_type }}', '{{ $placement->placement_date->format('Y-m-d') }}', '{{ $placement->start_date?->format('Y-m-d') }}', {{ $placement->salary }}, '{{ $placement->status }}', '{{ e($placement->supervisor_name) }}', '{{ e($placement->supervisor_phone) }}')" class="text-xs underline ink-secondary mr-2">Edit</button>
                    <form method="POST" action="{{ route('admin.bkk.placements.delete', $placement) }}" class="inline" onsubmit="return confirm('Hapus?')">
                        @csrf @method('DELETE')
                        <button class="text-xs underline text-red-600">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="p-10 text-center text-gray-500 italic font-serif">Belum ada data penempatan.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-4">{{ $placements->links() }}</div>

<div id="editPlacementForm" class="hidden elite-card p-6 mt-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Edit Penempatan</h3>
    <form id="editPlacementTag" method="POST" class="grid md:grid-cols-2 gap-4">
        @csrf @method('PUT')
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Siswa *</label>
            <select name="student_id" id="ep2_student_id" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Mitra *</label>
            <select name="bkk_partner_id" id="ep2_partner_id" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($partners as $p)<option value="{{ $p->id }}">{{ $p->company_name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Posisi *</label>
            <input type="text" name="position" id="ep2_position" required class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Kontrak *</label>
            <select name="contract_type" id="ep2_contract_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($contractTypes as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Penempatan *</label>
            <input type="date" name="placement_date" id="ep2_placement_date" required class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Mulai</label>
            <input type="date" name="start_date" id="ep2_start_date" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Gaji</label>
            <input type="number" name="salary" id="ep2_salary" min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Status *</label>
            <select name="status" id="ep2_status" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($statuses as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Pembimbing</label>
            <input type="text" name="supervisor_name" id="ep2_supervisor_name" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Telp Pembimbing</label>
            <input type="text" name="supervisor_phone" id="ep2_supervisor_phone" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-elite">Perbarui</button>
            <button type="button" onclick="document.getElementById('editPlacementForm').classList.add('hidden')" class="btn-elite-ghost ml-2">Batal</button>
        </div>
    </form>
</div>

<script>
function editPlacement(id, sid, pid, pos, ct, pd, sd, sal, st, sn, sp) {
    document.getElementById('addPlacementForm').classList.add('hidden');
    const f = document.getElementById('editPlacementForm'); f.classList.remove('hidden'); f.scrollIntoView({behavior:'smooth'});
    document.getElementById('editPlacementTag').action = '{{ route('admin.bkk.placements.update', ['placement' => '__ID__']) }}'.replace('__ID__', id);
    document.getElementById('ep2_student_id').value = sid;
    document.getElementById('ep2_partner_id').value = pid;
    document.getElementById('ep2_position').value = pos;
    document.getElementById('ep2_contract_type').value = ct;
    document.getElementById('ep2_placement_date').value = pd;
    document.getElementById('ep2_start_date').value = sd;
    document.getElementById('ep2_salary').value = sal;
    document.getElementById('ep2_status').value = st;
    document.getElementById('ep2_supervisor_name').value = sn;
    document.getElementById('ep2_supervisor_phone').value = sp;
}
</script>
@endsection
