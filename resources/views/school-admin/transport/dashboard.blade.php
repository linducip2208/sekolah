@extends('layouts.school-admin')
@section('title', 'Transport & Gerbang')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">Transport & Gerbang</h2>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b">
            <h3 class="font-bold">🚌 Trip Aktif</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr><th class="px-4 py-2">Vehicle</th><th class="px-4 py-2">Route</th><th class="px-4 py-2">Direction</th><th class="px-4 py-2">Started</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($activeTrips as $trip)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $trip->vehicle?->registration_no ?? 'Vehicle #'.$trip->vehicle_id }}</td>
                        <td class="px-4 py-2">Route #{{ $trip->transport_route_id }}</td>
                        <td class="px-4 py-2">{{ $trip->direction === 'pickup' ? 'Antar Jemput' : 'Antar Pulang' }}</td>
                        <td class="px-4 py-2 text-xs">{{ $trip->started_at?->format('H:i') }}</td>
                        <td class="px-4 py-2 text-right"><a href="#" class="text-brand-primary text-xs">Lihat Map</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Tidak ada trip aktif</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b">
            <h3 class="font-bold">🚪 Tap Gerbang Hari Ini ({{ $gateEvents->count() }})</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr><th class="px-4 py-2">Waktu</th><th class="px-4 py-2">Siswa</th><th class="px-4 py-2">Arah</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($gateEvents as $e)
                    <tr>
                        <td class="px-4 py-2 font-mono text-xs">{{ $e->scanned_at->format('H:i:s') }}</td>
                        <td class="px-4 py-2">User #{{ $e->user_id }}</td>
                        <td class="px-4 py-2">
                            @if($e->direction === 'in')
                                <span class="text-green-600">→ Masuk</span>
                            @else
                                <span class="text-orange-600">← Keluar</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">Belum ada tap gerbang hari ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
