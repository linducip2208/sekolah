@extends('layouts.school-admin')
@section('title', 'Persetujuan Booking Ruangan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Persetujuan Booking</h2>
            <p class="text-sm text-gray-600">Setujui atau tolak permintaan booking ruangan dari guru dan staff.</p>
        </div>
        <a href="{{ route('admin.facilities.rooms.calendar') }}" class="text-sm text-blue-600 hover:underline">Lihat Kalender</a>
    </div>

    <div class="space-y-3">
        @forelse($pending as $booking)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-start justify-between">
                <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold">{{ $booking['room']['name'] ?? 'Ruangan' }}</span>
                        <span class="text-xs px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full">Pending</span>
                    </div>
                    <div>
                        <span class="font-medium">{{ $booking['title'] }}</span>
                        @if(!empty($booking['purpose']))
                            <p class="text-sm text-gray-500">{{ $booking['purpose'] }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 text-sm text-gray-600">
                        <span>{{ \Carbon\Carbon::parse($booking['date'])->format('d M Y') }}</span>
                        <span>{{ $booking['start_time'] }} — {{ $booking['end_time'] }}</span>
                        <span>Oleh: {{ $booking['user']['name'] ?? '' }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-4">
                    <form method="POST" action="{{ route('admin.facilities.rooms.approve', $booking['id']) }}">
                        @csrf
                        <button class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('admin.facilities.rooms.reject', $booking['id']) }}" x-data="{ reason: '' }" @submit.prevent>
                        @csrf
                        <input type="hidden" name="reason" x-model="reason">
                        <button type="button" @click="reason=prompt('Alasan penolakan:') || 'Ditolak'; if(reason) $event.target.closest('form').submit()"
                            class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">Tolak</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-12 text-center text-gray-400">
            <p class="text-lg mb-2">✅ Tidak ada booking yang perlu disetujui</p>
            <p>Semua booking sudah diproses.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
