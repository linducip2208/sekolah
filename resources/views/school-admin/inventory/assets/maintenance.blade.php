@extends('layouts.school-admin')
@section('title', 'Maintenance Aset')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Operasional</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Jadwal Maintenance</h1>
    <div class="elite-rule"></div>
</div>

<button onclick="document.getElementById('addMaintForm').classList.toggle('hidden')" class="btn-elite-gold mb-4">+ Tambah Jadwal</button>

<div id="addMaintForm" class="hidden elite-card p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Jadwalkan Maintenance</h3>
    <form method="POST" action="{{ route('admin.inventory.maintenance.store') }}" class="grid md:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Aset *</label>
            <select name="asset_id" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="">— Pilih Aset —</option>
                @foreach($assets as $a)<option value="{{ $a->id }}">{{ $a->name }} ({{ $a->asset_code }})</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Jenis *</label>
            <select name="maintenance_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                @foreach($types as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Dijadwalkan *</label>
            <input type="date" name="scheduled_date" required class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Dikerjakan Oleh</label>
            <input type="text" name="performed_by" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Estimasi Biaya (Rp)</label>
            <input type="number" name="cost" min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block elite-kicker text-[.6rem] mb-1">Catatan</label>
            <textarea name="notes" rows="2" class="w-full border-2 border-rule px-3 py-2 text-sm"></textarea>
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-elite">Simpan Jadwal</button>
        </div>
    </form>
</div>

<form method="GET" class="flex gap-3 mb-4 bg-white border border-rule p-4">
    <select name="status" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Semua Status —</option>
        @foreach($statuses as $k => $l)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Filter</button>
</form>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="elite-card overflow-hidden">
            <div class="table-scroll">
            <table class="table-elite w-full text-sm">
                <thead>
                    <tr>
                        <th>Aset</th>
                        <th>Jenis</th>
                        <th>Tgl Jadwal</th>
                        <th>Status</th>
                        <th>Biaya</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $s)
                    <tr>
                        <td class="font-serif font-semibold text-sm">{{ $s->asset?->name }}</td>
                        <td class="text-xs">{{ $types[$s->maintenance_type] ?? $s->maintenance_type }}</td>
                        <td class="text-xs">{{ $s->scheduled_date->format('d/m/Y') }}</td>
                        <td><span class="text-[.6rem] uppercase px-2 py-0.5 rounded
                            {{ $s->status === 'completed' ? 'bg-green-100 text-green-800' : ($s->status === 'overdue' ? 'bg-red-100 text-red-800' : ($s->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')) }}">
                            {{ $statuses[$s->status] ?? $s->status }}</span></td>
                        <td class="font-mono text-xs">Rp {{ number_format($s->cost, 0, ',', '.') }}</td>
                        <td class="text-right whitespace-nowrap">
                            @if($s->status !== 'completed')
                            <button onclick="updateMaint({{ $s->id }})" class="text-xs underline ink-secondary mr-2">Update</button>
                            @endif
                            <form method="POST" action="{{ route('admin.inventory.maintenance.delete', $s) }}" class="inline" onsubmit="return confirm('Hapus?')">
                                @csrf @method('DELETE')
                                <button class="text-xs underline text-red-600">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada jadwal maintenance.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="mt-4">{{ $schedules->links() }}</div>
    </div>

    <div class="elite-card p-5">
        <h3 class="elite-h3 text-base ink-primary mb-4">Kalender Maintenance</h3>
        <div id="maint-calendar" class="text-xs"></div>
    </div>
</div>

<div id="updateMaintForm" class="hidden elite-card p-6 mt-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Update Status Maintenance</h3>
    <form id="updateMaintTag" method="POST" class="grid md:grid-cols-2 gap-4">
        @csrf @method('PUT')
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Status</label>
            <select name="status" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="scheduled">Terjadwal</option>
                <option value="in_progress">Dalam Pengerjaan</option>
                <option value="completed">Selesai</option>
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tgl Selesai</label>
            <input type="date" name="completed_date" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <label class="block elite-kicker text-[.6rem] mb-1">Biaya Aktual</label>
            <input type="number" name="cost" min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-elite">Simpan</button>
            <button type="button" onclick="document.getElementById('updateMaintForm').classList.add('hidden')" class="btn-elite-ghost ml-2">Batal</button>
        </div>
    </form>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6/index.global.min.js"></script>
<script>
function updateMaint(id) {
    const f = document.getElementById('updateMaintForm'); f.classList.remove('hidden'); f.scrollIntoView({behavior:'smooth'});
    document.getElementById('updateMaintTag').action = '{{ route('admin.inventory.maintenance.update', ['schedule' => '__ID__']) }}'.replace('__ID__', id);
}

document.addEventListener('DOMContentLoaded', function() {
    const events = {!! json_encode($events) !!};
    const cal = new FullCalendar.Calendar(document.getElementById('maint-calendar'), {
        initialView: 'dayGridMonth', height: 'auto', locale: 'id',
        events: events,
        eventClick: function(info) { if (info.event.url) window.location = info.event.url; }
    });
    cal.render();
});
</script>
@endpush

@endsection
