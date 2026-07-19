@extends('layouts.app')
@section('title', 'Aktivitas Saya')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-stone-800">Aktivitas Saya</h1>
        <p class="text-sm text-stone-500 mt-1">Riwayat aktivitas akademik dan non-akademik Anda</p>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        @php $types = ['attendance' => 'Absensi', 'mark' => 'Nilai', 'exam' => 'Ujian', 'assignment' => 'Tugas', 'achievement' => 'Prestasi', 'discipline' => 'Disiplin', 'payment' => 'Pembayaran', 'fee' => 'SPP', 'portfolio' => 'Portofolio', 'event' => 'Event', 'counseling' => 'Konseling']; @endphp
        <a href="{{ route('student.activity') }}" class="text-xs px-3 py-1.5 rounded-full {{ !request('activity_type') ? 'bg-indigo-600 text-white' : 'bg-stone-100 text-stone-600' }}">Semua</a>
        @foreach($types as $key => $label)
            <a href="?activity_type={{ $key }}" class="text-xs px-3 py-1.5 rounded-full {{ request('activity_type') === $key ? 'bg-indigo-600 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if($activities->isEmpty())
        <div class="bg-white border border-stone-200 rounded-xl p-12 text-center text-stone-500 italic">Belum ada aktivitas.</div>
    @else
    <div class="relative">
        <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-stone-200"></div>
        <div class="space-y-3">
            @foreach($activities as $activity)
            <div class="relative pl-12">
                <div class="absolute left-[14px] top-3 w-3 h-3 rounded-full border-2 border-white shadow" style="background-color: {{ $service->activityColor($activity->activity_type) }};"></div>
                <div class="bg-white border border-stone-200 rounded-xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span>{{ $service->activityIcon($activity->activity_type) }}</span>
                                <span class="font-semibold text-sm text-stone-800">{{ $activity->title }}</span>
                            </div>
                            @if($activity->description)
                                <p class="text-xs text-stone-500 mt-1">{{ $activity->description }}</p>
                            @endif
                        </div>
                        <span class="text-xs text-stone-400 whitespace-nowrap">{{ $service->relativeTime($activity->created_at) }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $activities->links() }}</div>
    </div>
    @endif
</div>
@endsection
