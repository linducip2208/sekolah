@extends('layouts.parent')
@section('title', 'Konferensi Orang Tua-Guru')
@section('content')

<a href="{{ route('portal.dashboard') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Beranda</a>

<div class="mb-8">
    <div class="elite-kicker mb-2">Conferentia</div>
    <h1 class="elite-h1 text-4xl ink-primary mb-2">Konferensi Orang Tua-Guru</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-lg" style="color: var(--c-muted);">Pilih sesi dan booking slot waktu untuk konsultasi dengan guru.</p>
</div>

@php
    $sessions = \App\Models\Communication\ConferenceSession::where('school_id', auth()->user()->school_id)
        ->where('is_published', true)
        ->where('date', '>=', now()->toDateString())
        ->orderBy('date')
        ->get();
@endphp

@if($sessions->isEmpty())
    <div class="bg-white border border-rule p-10 text-center">
        <p class="font-serif text-base text-gray-600 italic mb-2">Belum ada sesi konferensi yang tersedia.</p>
        <p class="font-serif text-sm text-gray-500">Silakan cek kembali nanti.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach($sessions as $s)
            @php
                $myBooking = \App\Models\Communication\ConferenceBooking::where('conference_session_id', $s->id)
                    ->where('parent_id', auth()->id())
                    ->first();
                $children = \App\Models\Academic\Student::whereHas('parents', fn($q) => $q->where('parent_id', auth()->id()))
                    ->with('user:id,name')->get();
            @endphp
            <div class="bg-white border border-rule p-6">
                <div class="flex justify-between items-start gap-4 flex-wrap">
                    <div class="flex-1">
                        <h3 class="elite-h3 text-lg ink-primary mb-1">{{ $s->title }}</h3>
                        <p class="font-serif text-sm text-gray-700 mb-2">{{ $s->description }}</p>
                        <div class="text-xs text-gray-500 space-x-4">
                            <span>📅 {{ \Carbon\Carbon::parse($s->date)->translatedFormat('l, d M Y') }}</span>
                            <span>🕐 {{ $s->start_time }} - {{ $s->end_time }}</span>
                            <span>⏱️ {{ $s->duration_minutes }} menit/slot</span>
                            <span>📍 {{ $s->location === 'online' ? 'Online' . ($s->meeting_link ? ' (link tersedia)' : '') : $s->location_detail ?: 'Fisik' }}</span>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        @if($myBooking && $myBooking->status !== 'cancelled')
                            <div class="text-right">
                                <div class="text-xs font-semibold text-green-700 mb-1">
                                    @if($myBooking->status === 'booked') ⌛ Menunggu Konfirmasi
                                    @elseif($myBooking->status === 'confirmed') ✅ Terkonfirmasi
                                    @elseif($myBooking->status === 'completed') ✔️ Selesai
                                    @endif
                                </div>
                                <div class="text-sm font-serif">{{ $myBooking->booking_time }}</div>
                                <div class="text-xs text-gray-500">{{ $myBooking->student->user->name }}</div>
                                <form method="POST" action="{{ route('portal.conferences.cancel', $myBooking) }}" onsubmit="return confirm('Batalkan booking?')">
                                    @csrf
                                    <button class="text-xs text-red-700 hover:underline mt-2">Batalkan</button>
                                </form>
                            </div>
                        @else
                            <button type="button" onclick="openBookingModal({{ $s->id }})" class="btn-elite-gold text-xs">Book Slot</button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Booking Modal per session --}}
            <div id="booking-modal-{{ $s->id }}" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(11,29,58,.75);">
                <div class="bg-white max-w-md w-full border border-rule p-7 max-h-[90vh] overflow-y-auto">
                    <h3 class="elite-h3 text-lg ink-primary mb-4">Book Slot — {{ $s->title }}</h3>
                    <form method="POST" action="{{ route('portal.conferences.book', $s) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="elite-kicker text-[.6rem] block mb-1">Pilih Anak</label>
                            @php $myChildren = \App\Models\Academic\Student::whereHas('parents', fn($q) => $q->where('parent_id', auth()->id()))->with('user:id,name')->get(); @endphp
                            <select name="student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                                @foreach($myChildren as $c)
                                    <option value="{{ $c->id }}">{{ $c->user->name }} ({{ $c->admission_no }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="elite-kicker text-[.6rem] block mb-1">Pilih Slot Waktu</label>
                            <select name="booking_time" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                                @foreach($s->timeSlots() as $slot)
                                    @if($slot['available'])
                                        <option value="{{ $slot['time'] }}">{{ $slot['time'] }} (tersedia)</option>
                                    @else
                                        <option value="{{ $slot['time'] }}" disabled>{{ $slot['time'] }} (penuh)</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="elite-kicker text-[.6rem] block mb-1">Catatan / Agenda (opsional)</label>
                            <textarea name="notes" rows="3" maxlength="2000"
                                      class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Topik yang ingin dibahas..."></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button class="btn-elite">Book</button>
                            <button type="button" onclick="document.getElementById('booking-modal-{{ $s->id }}').style.display='none'" class="btn-elite-ghost">Tutup</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif

@push('scripts')
<script>
function openBookingModal(sessionId) {
    document.getElementById('booking-modal-' + sessionId).style.display = 'flex';
}
</script>
@endpush

@endsection
