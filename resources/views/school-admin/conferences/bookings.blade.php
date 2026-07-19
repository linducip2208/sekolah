@extends('layouts.school-admin')
@section('title', 'Booking — ' . $session->title)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.conferences.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Perscriptio</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $session->title }}</h1>
    <div class="elite-rule mb-3"></div>
    <div class="text-sm text-gray-600 flex flex-wrap gap-x-4 gap-y-1">
        <span>📅 {{ \Carbon\Carbon::parse($session->date)->translatedFormat('l, d M Y') }}</span>
        <span>🕐 {{ $session->start_time }} - {{ $session->end_time }}</span>
        <span>📍 {{ $session->location === 'online' ? 'Online' : $session->location_detail ?: 'Fisik' }}</span>
        <span>📋 {{ $bookings->whereNotIn('status', ['cancelled'])->count() }} / {{ $session->max_bookings ?: '∞' }}</span>
    </div>
</div>

@php
    $slots = $session->timeSlots();
@endphp

<div class="space-y-5">
    @foreach($slots as $slot)
        @php
            $slotBookings = $bookings->where('booking_time', $slot['time']);
        @endphp
        <div class="bg-white border border-rule p-5">
            <div class="flex items-baseline gap-3 mb-3">
                <h3 class="elite-h3 text-lg ink-primary">🕐 {{ $slot['time'] }}</h3>
                <span class="text-xs text-gray-500">{{ $slotBookings->count() ?? 0 }} booking</span>
            </div>

            @if($slotBookings->isEmpty())
                <p class="text-sm text-gray-400 italic font-serif">Belum ada booking di slot ini.</p>
            @else
                <div class="table-elite overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-rule text-left">
                                <th class="py-2 px-3 font-semibold">NIS</th>
                                <th class="py-2 px-3 font-semibold">Siswa</th>
                                <th class="py-2 px-3 font-semibold">Orang Tua</th>
                                <th class="py-2 px-3 font-semibold">Status</th>
                                <th class="py-2 px-3 font-semibold">Catatan</th>
                                <th class="py-2 px-3 font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($slotBookings as $b)
                            <tr class="border-b border-rule/40">
                                <td class="py-2 px-3 font-mono text-xs">{{ $b->student->admission_no }}</td>
                                <td class="py-2 px-3">{{ $b->student->user->name }}</td>
                                <td class="py-2 px-3">{{ $b->parent->name }}</td>
                                <td class="py-2 px-3">
                                    @if($b->status === 'booked')
                                        <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5">Booked</span>
                                    @elseif($b->status === 'confirmed')
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-0.5">Confirmed</span>
                                    @elseif($b->status === 'cancelled')
                                        <span class="text-xs bg-red-100 text-red-800 px-2 py-0.5">Cancelled</span>
                                    @elseif($b->status === 'completed')
                                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5">Completed</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 max-w-[200px]">
                                    <div class="text-xs italic">{{ \Illuminate\Support\Str::limit($b->notes, 60) }}</div>
                                    @if($b->teacher_notes)
                                        <div class="text-xs text-gray-500 mt-1">📝 {{ \Illuminate\Support\Str::limit($b->teacher_notes, 60) }}</div>
                                    @endif
                                    <button type="button" onclick="showBookingNotes('{{ $b->id }}', '{{ addslashes($b->teacher_notes) }}')" class="text-xs underline text-gray-400 hover:text-gray-700 mt-1">Edit Catatan</button>
                                </td>
                                <td class="py-2 px-3">
                                    <div class="flex gap-1 text-xs">
                                        @if($b->status === 'booked')
                                            <form method="POST" action="{{ route('admin.conferences.bookings.confirm', $b) }}">
                                                @csrf
                                                <button class="text-green-700 hover:underline">Konfirmasi</button>
                                            </form>
                                        @endif
                                        @if(in_array($b->status, ['booked', 'confirmed']))
                                            <form method="POST" action="{{ route('admin.conferences.bookings.cancel', $b) }}" onsubmit="return confirm('Batalkan booking ini?')">
                                                @csrf
                                                <button class="text-red-700 hover:underline">Batal</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.conferences.bookings.complete', $b) }}">
                                                @csrf
                                                <button class="text-blue-700 hover:underline">Selesai</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endforeach
</div>

<!-- Notes Modal -->
<div id="notes-modal" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(11,29,58,.75);">
    <div class="bg-white max-w-lg w-full border border-rule p-7">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Catatan Guru</h3>
        <form id="notes-form" method="POST">
            @csrf
            <textarea name="teacher_notes" id="teacher-notes-input" rows="5" maxlength="5000"
                      class="w-full border-2 border-rule px-3 py-2 font-serif text-sm mb-4"></textarea>
            <div class="flex gap-3">
                <button class="btn-elite">Simpan</button>
                <button type="button" onclick="document.getElementById('notes-modal').style.display='none'" class="btn-elite-ghost">Tutup</button>
            </div>
        </form>
    </div>
</div>

<script>
function showBookingNotes(bookingId, currentNotes) {
    document.getElementById('notes-form').action = '/admin/conferences/bookings/' + bookingId + '/notes';
    document.getElementById('teacher-notes-input').value = currentNotes || '';
    document.getElementById('notes-modal').style.display = 'flex';
}
</script>

@endsection
