@extends('layouts.school-admin')
@section('title', 'Live Class')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">Live Class Sessions</h2>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">Sesi Mendatang ({{ $upcoming->count() }})</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr><th class="px-4 py-2">Jadwal</th><th class="px-4 py-2">Topik</th><th class="px-4 py-2">Durasi</th><th class="px-4 py-2">Provider</th><th class="px-4 py-2">Status</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($upcoming as $s)
                    <tr>
                        <td class="px-4 py-2 text-xs">{{ $s->scheduled_start->format('d/m H:i') }}</td>
                        <td class="px-4 py-2 font-medium">{{ $s->topic }}</td>
                        <td class="px-4 py-2">{{ $s->duration_minutes }} menit</td>
                        <td class="px-4 py-2 text-xs">Provider #{{ $s->video_provider_id }}</td>
                        <td class="px-4 py-2">
                            @if($s->status === 'live')<span class="px-2 py-0.5 bg-red-100 text-red-800 rounded text-xs animate-pulse">🔴 LIVE</span>
                            @else<span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs">Scheduled</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Tidak ada sesi mendatang</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
