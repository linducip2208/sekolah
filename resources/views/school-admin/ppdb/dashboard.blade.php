@extends('layouts.school-admin')
@section('title', 'PPDB Dashboard')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-bold">PPDB — Penerimaan Peserta Didik Baru</h2>
        <p class="text-sm text-gray-600">Kelola periode pendaftaran, review applications, jalankan seleksi.</p>
    </div>

    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white rounded-lg p-4 shadow"><div class="text-xs text-gray-500">Total</div><div class="text-2xl font-bold">{{ $stats['total'] }}</div></div>
        <div class="bg-white rounded-lg p-4 shadow"><div class="text-xs text-gray-500">Submitted</div><div class="text-2xl font-bold text-blue-600">{{ $stats['submitted'] }}</div></div>
        <div class="bg-white rounded-lg p-4 shadow"><div class="text-xs text-gray-500">Diterima</div><div class="text-2xl font-bold text-green-600">{{ $stats['accepted'] }}</div></div>
        <div class="bg-white rounded-lg p-4 shadow"><div class="text-xs text-gray-500">Ditolak</div><div class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</div></div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b flex justify-between">
            <h3 class="font-bold">Periode PPDB Aktif</h3>
            <button class="btn-brand text-xs">+ Tambah Periode</button>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Buka</th>
                    <th class="px-4 py-2">Tutup</th>
                    <th class="px-4 py-2">Jalur</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($periods as $p)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $p->name }}</td>
                        <td class="px-4 py-2">{{ $p->open_date->format('d M Y') }}</td>
                        <td class="px-4 py-2">{{ $p->close_date->format('d M Y') }}</td>
                        <td class="px-4 py-2 text-xs">
                            @foreach((array)$p->jalur_config as $j => $q)
                                <span class="inline-block px-2 py-0.5 bg-gray-100 rounded mr-1">{{ $j }}: {{ $q }}</span>
                            @endforeach
                        </td>
                        <td class="px-4 py-2">
                            @if($p->is_published)
                                <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs">Published</span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-800 rounded text-xs">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right">
                            <button class="text-brand-primary text-xs">Run Selection</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500">Belum ada periode PPDB</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b">
            <h3 class="font-bold">Pendaftaran Terbaru (50)</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-2">No. Reg</th>
                    <th class="px-4 py-2">Nama Siswa</th>
                    <th class="px-4 py-2">Jalur</th>
                    <th class="px-4 py-2">Skor</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Tanggal</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($applications as $a)
                    <tr>
                        <td class="px-4 py-2 font-mono text-xs">{{ $a->registration_no }}</td>
                        <td class="px-4 py-2 font-medium">{{ $a->student_name }}</td>
                        <td class="px-4 py-2"><span class="text-xs px-2 py-0.5 bg-blue-50 text-blue-800 rounded">{{ $a->jalur }}</span></td>
                        <td class="px-4 py-2">{{ $a->ranking_score ?? '—' }}</td>
                        <td class="px-4 py-2">
                            @php $statusColor = match($a->status){'accepted'=>'green','rejected'=>'red','submitted'=>'blue','verified'=>'purple',default=>'gray'};@endphp
                            <span class="px-2 py-0.5 bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 rounded text-xs">{{ $a->status }}</span>
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $a->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500">Belum ada pendaftaran</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
