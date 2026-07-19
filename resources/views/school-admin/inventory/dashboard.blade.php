@extends('layouts.school-admin')
@section('title', 'Inventory')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">Inventory & Aset Sekolah</h2>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Total Aset</div><div class="text-3xl font-bold">{{ $totalAssets }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Sedang Dipinjam</div><div class="text-3xl font-bold text-orange-600">{{ $borrowed }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Maintenance</div><div class="text-3xl font-bold text-red-600">{{ $maintenance }}</div></div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-bold">Peminjaman Terbaru</div>
            <div class="divide-y max-h-96 overflow-y-auto">
                @forelse($recentLoans as $l)
                    <div class="p-3 text-sm">
                        <div class="flex justify-between">
                            <span>Asset #{{ $l->asset_id }}</span>
                            @php $c = match($l->status){'active'=>'green','overdue'=>'red','returned'=>'gray','pending'=>'yellow','lost'=>'red',default=>'gray'};@endphp
                            <span class="px-2 py-0.5 bg-{{ $c }}-100 text-{{ $c }}-800 rounded text-xs">{{ $l->status }}</span>
                        </div>
                        <div class="text-xs text-gray-500">User #{{ $l->borrower_id }} · Due {{ $l->due_at->format('d/m') }}</div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 text-sm">Belum ada peminjaman</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-bold">🔧 Maintenance Open</div>
            <div class="divide-y max-h-96 overflow-y-auto">
                @forelse($openMaintenance as $m)
                    <div class="p-3 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium">{{ \Illuminate\Support\Str::limit($m->issue_description, 50) }}</span>
                            <span class="px-2 py-0.5 bg-{{ $m->priority === 'critical' ? 'red' : ($m->priority === 'high' ? 'orange' : 'gray') }}-100 rounded text-xs">{{ $m->priority }}</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">{{ $m->status }} · {{ $m->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 text-sm">Tidak ada request</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
