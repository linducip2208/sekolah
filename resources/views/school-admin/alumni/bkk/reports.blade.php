@extends('layouts.school-admin')
@section('title', 'Laporan BKK — Disnaker')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-6">
    <div class="elite-kicker mb-2">Bursa Kerja Khusus</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Laporan Penelusuran Tamatan</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Laporan resmi untuk Disnaker — Form Penelusuran Tamatan SMK</p>
</div>

<div class="elite-card p-6 mb-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Generate Laporan Baru</h3>
    <form method="POST" action="{{ route('admin.bkk.reports.generate') }}" class="flex flex-wrap gap-4 items-end">
        @csrf
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Tahun Ajaran *</label>
            <select name="academic_year_id" required class="border-2 border-rule px-3 py-2 text-sm">
                <option value="">— Pilih —</option>
                @foreach($academicYears as $ay)
                <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Semester *</label>
            <select name="semester" required class="border-2 border-rule px-3 py-2 text-sm">
                <option value="1">Semester 1</option>
                <option value="2">Semester 2</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn-elite-gold">Generate Otomatis</button>
        </div>
    </form>
</div>

<div class="elite-card overflow-hidden">
    <div class="table-scroll">
    <table class="table-elite w-full text-sm">
        <thead>
            <tr>
                <th>Tahun Ajaran</th>
                <th>Semester</th>
                <th>Lulusan</th>
                <th>Ditempatkan</th>
                <th>Wirausaha</th>
                <th>Kuliah</th>
                <th>Belum</th>
                <th>Status</th>
                <th>Tgl Laporan</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr>
                <td class="text-xs">{{ $report->academicYear?->name }}</td>
                <td>{{ $report->semester }}</td>
                <td class="font-mono text-xs">{{ $report->total_graduates }}</td>
                <td class="font-mono text-xs">{{ $report->total_placed }}</td>
                <td class="font-mono text-xs">{{ $report->total_entrepreneur }}</td>
                <td class="font-mono text-xs">{{ $report->total_university }}</td>
                <td class="font-mono text-xs">{{ $report->total_unemployed }}</td>
                <td>
                    <span class="text-[.6rem] uppercase px-2 py-0.5 rounded
                        {{ $report->status === 'verified' ? 'bg-green-100 text-green-800' : ($report->status === 'submitted' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600') }}">
                        {{ ucfirst($report->status) }}
                    </span>
                </td>
                <td class="text-xs">{{ $report->report_date->format('d/m/Y') }}</td>
                <td class="text-right whitespace-nowrap">
                    <button onclick="editReport({{ $report->id }}, {{ $report->total_graduates }}, {{ $report->total_placed }}, {{ $report->total_entrepreneur }}, {{ $report->total_university }}, {{ $report->total_unemployed }}, '{{ $report->status }}')" class="text-xs underline ink-secondary mr-2">Edit</button>
                    <form method="POST" action="{{ route('admin.bkk.reports.delete', $report) }}" class="inline" onsubmit="return confirm('Hapus laporan?')">
                        @csrf @method('DELETE')
                        <button class="text-xs underline text-red-600">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="p-10 text-center text-gray-500 italic font-serif">Belum ada laporan. Klik "Generate Otomatis" di atas.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-4">{{ $reports->links() }}</div>

<div id="editReportForm" class="hidden elite-card p-6 mt-6">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Edit Laporan</h3>
    <form id="editReportTag" method="POST" class="grid md:grid-cols-3 gap-4">
        @csrf @method('PUT')
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Total Lulusan</label>
            <input type="number" name="total_graduates" id="er_total_graduates" required min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Ditempatkan</label>
            <input type="number" name="total_placed" id="er_total_placed" required min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Wirausaha</label>
            <input type="number" name="total_entrepreneur" id="er_total_entrepreneur" required min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Kuliah</label>
            <input type="number" name="total_university" id="er_total_university" required min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Belum Bekerja</label>
            <input type="number" name="total_unemployed" id="er_total_unemployed" required min="0" class="w-full border-2 border-rule px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block elite-kicker text-[.6rem] mb-1">Status</label>
            <select name="status" id="er_status" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                <option value="draft">Draf</option>
                <option value="submitted">Diajukan</option>
                <option value="verified">Terverifikasi</option>
            </select>
        </div>
        <div class="md:col-span-3">
            <button type="submit" class="btn-elite">Perbarui</button>
            <button type="button" onclick="document.getElementById('editReportForm').classList.add('hidden')" class="btn-elite-ghost ml-2">Batal</button>
        </div>
    </form>
</div>

<script>
function editReport(id, graduates, placed, entrepreneur, university, unemployed, status) {
    const f = document.getElementById('editReportForm'); f.classList.remove('hidden'); f.scrollIntoView({behavior:'smooth'});
    document.getElementById('editReportTag').action = '{{ route('admin.bkk.reports.update', ['report' => '__ID__']) }}'.replace('__ID__', id);
    document.getElementById('er_total_graduates').value = graduates;
    document.getElementById('er_total_placed').value = placed;
    document.getElementById('er_total_entrepreneur').value = entrepreneur;
    document.getElementById('er_total_university').value = university;
    document.getElementById('er_total_unemployed').value = unemployed;
    document.getElementById('er_status').value = status;
}
</script>
@endsection
