@extends('layouts.school-admin')
@section('title', 'Timeline Aktivitas Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div x-data="studentTimeline()" class="max-w-5xl mx-auto">
<div class="mb-7">
    <div class="elite-kicker mb-2">Akademik</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Timeline Aktivitas Siswa</h1>
    <div class="elite-rule"></div>
</div>

<div class="bg-white border border-rule p-5 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-stone-600 mb-1">Siswa</label>
            <select name="student_id" x-ref="studentSelect" class="border-2 border-rule px-3 py-2 text-sm min-w-[250px]" x-on:change="$refs.form.submit()">
                <option value="">-- Pilih Siswa --</option>
                @foreach(\App\Models\Academic\Student::where('school_id', auth()->user()->school_id)->with('user:id,name')->orderBy('id')->get() as $s)
                    <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>{{ $s->user?->name ?? 'Siswa #'.$s->id }} ({{ $s->admission_no ?? '-' }})</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

@if(request('student_id'))
    @php
        $student = \App\Models\Academic\Student::with('user:id,name', 'classSection.classRoom', 'classSection.section')->find(request('student_id'));
        $timeline = app(\App\Services\ActivityTimelineService::class);
        $activities = $timeline->getTimeline(request('student_id'), request()->all());
    @endphp

    @if($student)
    <div class="bg-white border border-rule p-5 mb-6">
        <h2 class="font-serif font-semibold text-lg ink-primary">{{ $student->user?->name ?? 'Siswa' }}</h2>
        <div class="text-sm text-stone-500">{{ $student->classSection?->display_name ?? '—' }} · {{ $student->admission_no ?? '—' }}</div>
    </div>
    @endif

    @if($activities->isEmpty())
        <div class="bg-white border border-rule p-12 text-center text-gray-500 italic font-serif">Belum ada aktivitas untuk siswa ini.</div>
    @else
    <div class="relative">
        {{-- Vertical line --}}
        <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-stone-200"></div>

        <div class="space-y-4">
            @foreach($activities as $activity)
            <div class="relative pl-12" x-data="{ revealed: false }" x-intersect="revealed = true" :class="revealed ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'" style="transition: all .5s ease;">
                {{-- Dot --}}
                <div class="absolute left-[14px] top-3 w-3 h-3 rounded-full border-2 border-white shadow" style="background-color: {{ $timeline->activityColor($activity->activity_type) }};"></div>

                <div class="bg-white border border-rule p-4 hover:shadow-sm transition">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-lg">{{ $timeline->activityIcon($activity->activity_type) }}</span>
                                <span class="font-serif font-semibold text-sm ink-primary">{{ $activity->title }}</span>
                            </div>
                            @if($activity->description)
                                <p class="text-xs text-stone-600 mt-1">{{ $activity->description }}</p>
                            @endif
                            @if($activity->metadata)
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($activity->metadata as $key => $val)
                                        @if(!is_array($val))
                                            <span class="text-[.6rem] bg-stone-100 text-stone-600 px-2 py-0.5 rounded font-mono">{{ $key }}: {{ $val }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-[.6rem] text-stone-400 whitespace-nowrap">{{ $timeline->relativeTime($activity->created_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $activities->links() }}</div>
    </div>
    @endif
@else
    <div class="bg-white border border-rule p-12 text-center text-gray-500 italic font-serif">
        Pilih siswa untuk melihat timeline aktivitas.
    </div>
@endif
</div>
@endsection
