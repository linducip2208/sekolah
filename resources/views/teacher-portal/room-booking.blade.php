@extends('layouts.parent')
@section('title', 'Booking Ruangan')
@section('content')
<div class="max-w-7xl space-y-6" x-data="{
    showBookingModal: false,
    bookingForm: { bookable_room_id:'', title:'', purpose:'', date:'', start_time:'', end_time:'' },
    rooms: @json($rooms)
}">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Booking Ruangan</h2>
            <p class="text-sm text-gray-600">Pesan ruangan kelas, lab, atau aula untuk keperluan mengajar.</p>
        </div>
        <button @click="showBookingModal=true" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">
            + Booking Baru
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Calendar --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold mb-3">Kalender</h3>
            <div id="calendar"></div>
        </div>

        {{-- My Bookings --}}
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold mb-3">Booking Saya</h3>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($myBookings as $booking)
                <div class="border rounded-lg p-3 text-sm">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium">{{ $booking['room']['name'] ?? 'Ruangan' }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full
                            @if($booking['status'] === 'approved') bg-green-100 text-green-700
                            @elseif($booking['status'] === 'pending') bg-yellow-100 text-yellow-700
                            @elseif($booking['status'] === 'rejected') bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-500
                            @endif
                        ">
                            @if($booking['status'] === 'approved')Disetujui
                            @elseif($booking['status'] === 'pending')Pending
                            @elseif($booking['status'] === 'rejected')Ditolak
                            @elseDibatalkan
                            @endif
                        </span>
                    </div>
                    <p class="text-gray-700">{{ $booking['title'] }}</p>
                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-1">
                        <span>{{ \Carbon\Carbon::parse($booking['date'])->format('d M') }}</span>
                        <span>{{ $booking['start_time'] }}—{{ $booking['end_time'] }}</span>
                    </div>
                    @if(in_array($booking['status'], ['pending','approved']))
                    <div class="mt-2 pt-2 border-t">
                        <form method="POST" action="{{ route('teacher.room-booking.cancel', $booking['id']) }}" onsubmit="return confirm('Batalkan booking ini?')">
                            @csrf
                            <button class="text-xs text-red-600 hover:underline">Batalkan</button>
                        </form>
                    </div>
                    @endif
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-8">Belum ada booking.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Booking Modal --}}
    <div x-show="showBookingModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showBookingModal=false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md space-y-4">
            <h3 class="font-semibold text-lg">Booking Ruangan</h3>
            <form method="POST" action="{{ route('teacher.room-booking.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Ruangan</label>
                        <select name="bookable_room_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Pilih Ruangan</option>
                            @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }} ({{ $room->capacity }} org)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Judul / Keperluan</label>
                        <input type="text" name="title" required maxlength="200" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Praktikum Kimia, Rapat Wali Kelas, dll.">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tujuan / Deskripsi</label>
                        <textarea name="purpose" rows="2" maxlength="1000" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Detail keperluan..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tanggal</label>
                        <input type="date" name="date" required class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" required class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" required class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                </div>
                @if($errors->has('conflict'))
                <div class="mt-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">{{ $errors->first('conflict') }}</div>
                @endif
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="showBookingModal=false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">Ajukan Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '21:00:00',
        height: 500,
        locale: 'id',
        allDaySlot: false,
        events: function(info, successCallback, failureCallback) {
            fetch('{{ route('teacher.room-booking.calendar.feed') }}?start=' + info.startStr + '&end=' + info.endStr)
                .then(function(res) { return res.json(); })
                .then(function(data) { successCallback(data); })
                .catch(function(err) { failureCallback(err); });
        },
        dateClick: function(info) {
            document.querySelector('[name="date"]').value = info.dateStr;
            document.querySelector('[x-data]').__x.$data.showBookingModal = true;
        }
    });
    calendar.render();
});
</script>
@endpush
@endsection
