@extends('layouts.school-admin')
@section('title', 'Kalender Booking Ruangan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<style>
    #calendar { max-width: 100%; }
    .fc-event { cursor: pointer; }
</style>
@endpush
@section('content')
<div class="max-w-7xl space-y-6" x-data="{
    showBookingModal: false,
    bookingForm: { bookable_room_id:'', title:'', purpose:'', date:'', start_time:'', end_time:'', is_recurring:false, recurring_pattern:'weekly', recurring_until:'' },
    rooms: @json($rooms)
}">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Kalender Booking Ruangan</h2>
            <p class="text-sm text-gray-600">Klik slot waktu kosong untuk membuat booking baru. Drag untuk melihat detail.</p>
        </div>
        <div class="flex items-center gap-3">
            <div>
                <select id="room-filter" class="border rounded-lg px-3 py-2 text-sm">
                    <option value="">Semua Ruangan</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            <button @click="showBookingModal=true" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">
                + Booking Baru
            </button>
            <a href="{{ route('admin.facilities.rooms.index') }}" class="text-sm text-gray-600 hover:underline">← Daftar Ruangan</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <div id="calendar"></div>
    </div>

    {{-- Booking Modal --}}
    <div x-show="showBookingModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showBookingModal=false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md space-y-4">
            <h3 class="font-semibold text-lg">Booking Ruangan</h3>
            <form method="POST" action="{{ route('admin.facilities.rooms.booking.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Ruangan</label>
                        <select name="bookable_room_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="">Pilih Ruangan</option>
                            @foreach($rooms as $room)
                            <option value="{{ $room->id }}" :selected="bookingForm.bookable_room_id==='{{ $room->id }}'">{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Judul Booking</label>
                        <input type="text" name="title" required maxlength="200" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Rapat Guru, Praktikum, dll.">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tujuan</label>
                        <textarea name="purpose" rows="2" maxlength="1000" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Deskripsi keperluan booking..."></textarea>
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
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_recurring" value="1" x-model="bookingForm.is_recurring" class="rounded">
                            <span class="text-sm">Booking Berulang</span>
                        </label>
                    </div>
                    <template x-if="bookingForm.is_recurring">
                        <div class="grid grid-cols-2 gap-3 pl-6 border-l-2 border-blue-200">
                            <div>
                                <label class="block text-sm font-medium mb-1">Pola</label>
                                <select name="recurring_pattern" class="w-full border rounded-lg px-3 py-2 text-sm">
                                    <option value="weekly">Mingguan</option>
                                    <option value="biweekly">2 Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Sampai Tanggal</label>
                                <input type="date" name="recurring_until" class="w-full border rounded-lg px-3 py-2 text-sm">
                            </div>
                        </div>
                    </template>
                </div>
                @if($errors->has('conflict'))
                <div class="mt-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">{{ $errors->first('conflict') }}</div>
                @endif
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="showBookingModal=false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        slotMinTime: '06:00:00',
        slotMaxTime: '21:00:00',
        height: 650,
        locale: 'id',
        allDaySlot: false,
        nowIndicator: true,
        events: function(info, successCallback, failureCallback) {
            var roomId = document.getElementById('room-filter').value;
            var url = '{{ route('admin.facilities.rooms.calendar.feed') }}?start=' + info.startStr + '&end=' + info.endStr;
            if (roomId) url += '&room_id=' + roomId;
            fetch(url)
                .then(function(res) { return res.json(); })
                .then(function(data) { successCallback(data); })
                .catch(function(err) { failureCallback(err); });
        },
        eventClick: function(info) {
            var props = info.event.extendedProps;
            alert('Ruangan: ' + props.room + '\nPemesan: ' + props.user + '\nStatus: ' + props.status + '\nTujuan: ' + (props.purpose || '-'));
        },
        dateClick: function(info) {
            document.querySelector('[name="date"]').value = info.dateStr;
            document.querySelector('[x-data]').__x.$data.showBookingModal = true;
        }
    });
    calendar.render();

    document.getElementById('room-filter').addEventListener('change', function() {
        calendar.refetchEvents();
    });
});
</script>
@endpush
@endsection
