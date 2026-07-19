@extends('layouts.school-admin')
@section('title', 'Observasi — ' . $lessonStudy->title)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Phase: Do</div>
            <h1 class="elite-h1 text-2xl ink-primary mb-2">Observasi: {{ $lessonStudy->title }}</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-2">Guru Model: <strong>{{ $lessonStudy->leadTeacher->name ?? '—' }}</strong> · Silakan amati dan catat.</p>
        </div>
        <a href="{{ route('admin.lesson-study.index') }}" class="btn-elite-ghost">← Kembali</a>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    @foreach($observationTypes as $type => $label)
        @php $existing = $existingObservations[$type] ?? null; @endphp
        <div class="bg-white border border-rule p-5">
            <h3 class="elite-h3 text-base ink-primary mb-3">{{ $label }}</h3>
            <form method="POST" action="{{ route('admin.lesson-study.store-observation', $lessonStudy) }}">
                @csrf
                <input type="hidden" name="observation_type" value="{{ $type }}">
                <div class="space-y-3">
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Rating (1-5)</label>
                        <div class="flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer">
                                    <input type="radio" name="rating" value="{{ $i }}" {{ ($existing->rating ?? '') == $i ? 'checked' : '' }} class="hidden peer">
                                    <span class="block w-10 h-10 flex items-center justify-center border-2 border-rule peer-checked:border-[var(--c-accent)] peer-checked:bg-[var(--c-accent)] peer-checked:text-white font-mono text-sm transition">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                    </div>
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Catatan Observasi</label>
                        <textarea name="notes" required rows="4" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Apa yang Anda amati? Fokus pada fakta, bukan interpretasi.">{{ $existing->notes ?? '' }}</textarea>
                    </div>
                    <button class="btn-elite w-full text-xs" style="padding:.6rem;">
                        {{ $existing ? 'Perbarui Observasi' : 'Simpan Observasi' }}
                    </button>
                </div>
            </form>
            @if($existing)
                <div class="mt-2 text-xs text-gray-500">Terakhir diobservasi: {{ $existing->updated_at->diffForHumans() }}</div>
            @endif
        </div>
    @endforeach
</div>

<div class="mt-6 flex justify-between">
    <span class="text-sm text-gray-500 font-serif">Observasi untuk fase <strong>Do</strong> — amati secara objektif tanpa intervensi.</span>
    <form method="POST" action="{{ route('admin.lesson-study.advance-phase', $lessonStudy) }}">
        @csrf
        <button class="btn-elite">Selesai Observasi → Fase See</button>
    </form>
</div>

@endsection
