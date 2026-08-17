@extends('layouts.school-admin')
@section('title', 'Live Tracking Bus')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">GPS Vehiculum</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Live Tracking Bus</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Posisi kendaraan real-time · {{ $locations->count() }} kendaraan aktif · {{ $staleCount }} sinyal terlambat (&gt;15 mnt)</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div id="map" class="bg-white border border-rule" style="height: 480px;"></div>
    </div>
    <div class="bg-white border border-rule overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-rule elite-kicker text-[.7rem]">Kendaraan</div>
        <div id="vehicle-list" class="divide-y divide-rule max-h-[480px] overflow-y-auto">
            @forelse($locations as $loc)
            <div class="px-4 py-3" data-vehicle>
                <div class="font-serif font-semibold text-sm">{{ $loc->vehicle?->registration_no ?? '—' }}</div>
                <div class="text-xs text-gray-500">{{ $loc->vehicle?->driver_name ?? 'Tanpa sopir' }}</div>
                <div class="flex gap-3 mt-1 text-xs font-mono">
                    <span>{{ $loc->speed_kmh }} km/j</span>
                    <span class="text-gray-400">{{ $loc->recorded_at?->diffForHumans() }}</span>
                </div>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-gray-400 italic font-serif text-xs">Belum ada data GPS. Kirim lokasi dari perangkat kendaraan.</div>
            @endforelse
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    const initial = @json($locations->map(fn($loc) => [
        'id' => $loc->vehicle_id,
        'registration_no' => $loc->vehicle?->registration_no ?? '—',
        'driver_name' => $loc->vehicle?->driver_name,
        'lat' => (float) $loc->lat,
        'lng' => (float) $loc->lng,
        'speed_kmh' => (float) $loc->speed_kmh,
        'recorded_human' => $loc->recorded_at?->diffForHumans(),
    ])->values());

    const map = L.map('map').setView([-6.2, 106.816], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    const markers = new Map();

    function render(vehicles) {
        const bounds = [];
        for (const v of vehicles) {
            if (!v.lat || !v.lng) continue;
            const pos = [v.lat, v.lng];
            bounds.push(pos);

            const popup = `<b>${v.registration_no}</b><br>${v.driver_name || ''}<br>${v.speed_kmh} km/j · ${v.recorded_human || ''}`;

            if (markers.has(v.id)) {
                const m = markers.get(v.id);
                m.setLatLng(pos);
                m.setPopupContent(popup);
            } else {
                const m = L.marker(pos).addTo(map).bindPopup(popup);
                markers.set(v.id, m);
            }
        }
        if (bounds.length) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
        }
    }

    render(initial);

    async function poll() {
        try {
            const res = await fetch('{{ route('admin.transport.tracking.latest') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const data = await res.json();
            render(data.vehicles || []);

            // refresh list
            const list = document.getElementById('vehicle-list');
            list.innerHTML = (data.vehicles || []).map(v =>
                `<div class="px-4 py-3"><div class="font-serif font-semibold text-sm">${v.registration_no}</div>
                 <div class="text-xs text-gray-500">${v.driver_name || 'Tanpa sopir'}</div>
                 <div class="flex gap-3 mt-1 text-xs font-mono"><span>${v.speed_kmh} km/j</span><span class="text-gray-400">${v.recorded_human || ''}</span></div></div>`
            ).join('') || '<div class="px-4 py-8 text-center text-gray-400 italic font-serif text-xs">Belum ada data GPS.</div>';
        } catch (e) {}
    }

    setInterval(poll, 30000);
})();
</script>

@endsection
