@extends('layouts.school-admin')
@section('title', 'Kantin')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">Kantin Cashless</h2>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Order Hari Ini</div><div class="text-3xl font-bold">{{ $todayOrders->count() }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Revenue Hari Ini</div><div class="text-3xl font-bold text-green-600">Rp {{ number_format($todayRevenue / 100, 0, ',', '.') }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Menu Aktif</div><div class="text-3xl font-bold">{{ $menu->where('is_available', true)->count() }}</div></div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b flex justify-between"><h3 class="font-bold">Menu</h3><button class="btn-brand text-xs">+ Tambah Menu</button></div>
            <div class="divide-y max-h-96 overflow-y-auto">
                @forelse($menu as $m)
                    <div class="p-3 flex justify-between items-center">
                        <div>
                            <div class="font-medium">{{ $m->name }}</div>
                            <div class="text-xs text-gray-500">Stok: {{ $m->stock_today ?? '∞' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold">Rp {{ number_format($m->price / 100, 0, ',', '.') }}</div>
                            @if(!$m->is_available)<span class="text-xs text-red-600">Habis</span>@endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">Belum ada menu</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-bold">Pesanan Hari Ini</div>
            <div class="divide-y max-h-96 overflow-y-auto">
                @forelse($todayOrders as $o)
                    <div class="p-3">
                        <div class="flex justify-between">
                            <span class="font-mono text-xs">{{ $o->order_no }}</span>
                            @php $col = match($o->status){'ready'=>'green','preparing'=>'blue','picked_up'=>'gray','cancelled'=>'red',default=>'yellow'};@endphp
                            <span class="px-2 py-0.5 bg-{{ $col }}-100 text-{{ $col }}-800 rounded text-xs">{{ $o->status }}</span>
                        </div>
                        <div class="text-sm mt-1">Student #{{ $o->student_id }} · Rp {{ number_format($o->total / 100, 0, ',', '.') }}</div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">Belum ada pesanan</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
