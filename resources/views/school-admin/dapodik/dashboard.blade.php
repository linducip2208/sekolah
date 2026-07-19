@extends('layouts.school-admin')
@section('title', 'Dapodik Sync')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">Dapodik Sync</h2>

    <div class="bg-white rounded-lg shadow p-5">
        <h3 class="font-bold mb-3">Konfigurasi</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-xs text-gray-500">NPSN</div>
                <div class="font-mono">{{ $config->npsn ?: 'Belum di-set' }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-500">Last Sync</div>
                <div>{{ $config->last_sync_at?->diffForHumans() ?? '—' }}</div>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <button class="btn-brand text-sm">Edit Config</button>
            <button class="btn-brand text-sm" style="background-color: #16A34A">Import Students CSV</button>
            <button class="btn-brand text-sm" style="background-color: #6366F1">Export Students</button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">Riwayat Sync (20 terakhir)</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr><th class="px-4 py-2">Tanggal</th><th class="px-4 py-2">Direction</th><th class="px-4 py-2">Entity</th><th class="px-4 py-2">Total</th><th class="px-4 py-2">Success</th><th class="px-4 py-2">Failed</th><th class="px-4 py-2">Status</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($recentSyncs as $s)
                    <tr>
                        <td class="px-4 py-2 text-xs">{{ $s->created_at->format('d/m H:i') }}</td>
                        <td class="px-4 py-2">{{ $s->direction }}</td>
                        <td class="px-4 py-2">{{ $s->entity }}</td>
                        <td class="px-4 py-2">{{ $s->records_total }}</td>
                        <td class="px-4 py-2 text-green-600">{{ $s->records_success }}</td>
                        <td class="px-4 py-2 text-red-600">{{ $s->records_failed }}</td>
                        <td class="px-4 py-2">
                            @php $col = match($s->status){'completed'=>'green','running'=>'blue','failed'=>'red',default=>'gray'};@endphp
                            <span class="px-2 py-0.5 bg-{{ $col }}-100 text-{{ $col }}-800 rounded text-xs">{{ $s->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada sync</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
