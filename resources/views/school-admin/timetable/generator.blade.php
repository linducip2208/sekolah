@extends('layouts.school-admin')
@section('title', 'Generate Jadwal Otomatis')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

@php
    $steps = [
        1 => ['label' => 'Pilih Rombel',      'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        2 => ['label' => 'Konfigurasi',       'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
        3 => ['label' => 'Ketersediaan Guru', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        4 => ['label' => 'Alokasi Jam',       'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        5 => ['label' => 'Hasil Generate',     'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
    ];
    $currentStep = $step ?? 1;
@endphp

<div class="mb-8">
    <div class="elite-kicker mb-2">Generate Otomatis</div>
    <h1 class="elite-h1 text-4xl ink-primary mb-3">Jadwal Pelajaran</h1>
    <div class="elite-rule mb-4"></div>

    {{-- Step progress --}}
    <div class="flex items-center gap-2 flex-wrap" x-data="{}">
        @foreach($steps as $i => $s)
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-2 px-3 py-2 {{ $i === $currentStep ? 'bg-[var(--c-primary)] text-white' : ($i < $currentStep ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-stone-500') }}"
                     style="font-family:Inter,sans-serif; font-size:.68rem; letter-spacing:.1em; text-transform:uppercase; font-weight:600;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/>
                    </svg>
                    <span>{{ $i }}. {{ $s['label'] }}</span>
                </div>
                @if($i < 5)
                    <svg class="w-4 h-4 text-stone-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- STEP 1: Select Academic Year + Class Sections --}}
@if($currentStep === 1)
<div x-data="step1()">
    <form method="POST" action="{{ route('admin.timetable.generator.post-step') }}">
        @csrf
        <input type="hidden" name="step" value="1">

        <div class="elite-card p-6 mb-6">
            <h3 class="elite-h3 text-xl ink-primary mb-4">Tahun Ajaran</h3>
            <select name="academic_year_id" required
                    class="w-full max-w-md px-4 py-2.5 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                <option value="">-- Pilih Tahun Ajaran --</option>
                @foreach($academicYears ?? [] as $year)
                    <option value="{{ $year->id }}" {{ (($gen['academic_year_id'] ?? 0) == $year->id) ? 'selected' : '' }}>
                        {{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="elite-card p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="elite-h3 text-xl ink-primary">Pilih Rombongan Belajar</h3>
                <span class="elite-kicker" x-text="selectedCount() + ' dipilih'"></span>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach($classSections ?? [] as $cs)
                    <label class="flex items-start gap-3 p-3 border border-stone-200 hover:border-[var(--c-accent)] cursor-pointer transition"
                           :class="selected['{{ $cs->id }}'] ? 'border-[var(--c-primary)] bg-[var(--c-primary)]/5' : ''">
                        <input type="checkbox" name="class_section_ids[]" value="{{ $cs->id }}"
                               x-model="selected['{{ $cs->id }}']"
                               class="mt-0.5">
                        <div class="text-sm font-serif leading-tight">
                            <div class="font-semibold">{{ $cs->classRoom?->name ?? '' }} {{ $cs->section?->name ?? '' }}</div>
                            <div class="text-xs text-stone-500 mt-1">{{ $cs->subjects->count() }} Mapel</div>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex justify-between">
            <div></div>
            <button type="submit" class="btn-elite">
                Lanjut ke Konfigurasi
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </div>
    </form>
</div>

@endif

{{-- STEP 2: Configuration Parameters --}}
@if($currentStep === 2)
<div x-data="step2(@json($existingConfigs ?? []), @json($gen['configs'] ?? []))">
    <form method="POST" action="{{ route('admin.timetable.generator.post-step') }}">
        @csrf
        <input type="hidden" name="step" value="2">

        @foreach($classSections ?? [] as $cs)
        <div class="elite-card p-6 mb-6">
            <h3 class="elite-h3 text-xl ink-primary mb-4">{{ $cs->classRoom?->name ?? '' }} {{ $cs->section?->name ?? '' }}</h3>

            <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="elite-kicker block mb-1">Hari per Minggu</label>
                    <select name="config[{{ $cs->id }}][days_per_week]" :value="getConfig({{ $cs->id }}, 'days_per_week', 5)"
                            class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                        <option value="5">5 Hari (Senin-Jumat)</option>
                        <option value="6">6 Hari (Senin-Sabtu)</option>
                    </select>
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Periode per Hari</label>
                    <input type="number" name="config[{{ $cs->id }}][periods_per_day]" min="3" max="15"
                           :value="getConfig({{ $cs->id }}, 'periods_per_day', 8)"
                           class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]" required>
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Durasi (menit)</label>
                    <input type="number" name="config[{{ $cs->id }}][duration]" min="25" max="90"
                           :value="getConfig({{ $cs->id }}, 'duration', 45)"
                           class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]" required>
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Jam Mulai</label>
                    <input type="time" name="config[{{ $cs->id }}][start_time]"
                           :value="getConfig({{ $cs->id }}, 'start_time', '07:00')"
                           class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]" required>
                </div>
                <div>
                    <label class="elite-kicker block mb-1">Istirahat Setelah Periode</label>
                    <input type="text" name="config[{{ $cs->id }}][breaks]"
                           placeholder="Contoh: 3,6"
                           :value="getConfig({{ $cs->id }}, 'breaks', '')"
                           class="w-full px-3 py-2 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                </div>
            </div>
        </div>
        @endforeach

        <div class="flex justify-between">
            <a href="{{ route('admin.timetable.generator.wizard', ['step' => 1]) }}" class="btn-elite-ghost">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Kembali
            </a>
            <button type="submit" class="btn-elite">
                Lanjut ke Ketersediaan Guru
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </div>
    </form>
</div>
@endif

{{-- STEP 3: Teacher Availability --}}
@if($currentStep === 3)
<div x-data="step3()">
    <form method="POST" action="{{ route('admin.timetable.generator.post-step') }}">
        @csrf
        <input type="hidden" name="step" value="3">

        <div class="elite-card p-6 mb-6">
            <h3 class="elite-h3 text-xl ink-primary mb-4">Ketersediaan Guru per Hari</h3>
            <p class="font-serif text-sm text-stone-500 mb-4">Centang = tersedia. Kosongkan = tidak tersedia di hari tersebut.</p>

            <div class="overflow-x-auto">
                <table class="table-elite w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Guru</th>
                            @foreach($daysList ?? [] as $dCode => $dName)
                                <th class="text-center">{{ $dName }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers ?? [] as $teacher)
                        <tr>
                            <td class="font-serif font-semibold whitespace-nowrap">{{ $teacher->name }}</td>
                            @foreach($daysList ?? [] as $dCode => $dName)
                            <td class="text-center">
                                <input type="hidden" name="availability[{{ $teacher->id }}][{{ $dCode }}]" value="0">
                                <input type="checkbox"
                                       name="availability[{{ $teacher->id }}][{{ $dCode }}]"
                                       value="1"
                                       {{ ($availability[$teacher->id][$dCode] ?? true) ? 'checked' : '' }}
                                       class="w-4 h-4 accent-[var(--c-primary)]">
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex gap-2">
                <button type="button" @click="checkAll(true)" class="border border-stone-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:bg-stone-50 transition">Centang Semua</button>
                <button type="button" @click="checkAll(false)" class="border border-stone-300 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:bg-stone-50 transition">Hapus Semua</button>
            </div>
        </div>

        <div class="flex justify-between">
            <a href="{{ route('admin.timetable.generator.wizard', ['step' => 2]) }}" class="btn-elite-ghost">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Kembali
            </a>
            <button type="submit" class="btn-elite">
                Lanjut ke Alokasi Jam
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </div>
    </form>
</div>
@endif

{{-- STEP 4: Subject Hour Allocation --}}
@if($currentStep === 4)
<div x-data="step4()">
    <form method="POST" action="{{ route('admin.timetable.generator.post-step') }}">
        @csrf
        <input type="hidden" name="step" value="4">

        @php $prevCs = null; @endphp
        @foreach($subjectAllocations ?? [] as $key => $alloc)
            @if($alloc['class_section_id'] !== $prevCs)
                @if($prevCs !== null)
                    </tbody></table></div>
                @endif
                <div class="elite-card p-6 mb-6">
                    <h3 class="elite-h3 text-xl ink-primary mb-4">{{ $alloc['class_section'] }}</h3>
                    <div class="overflow-x-auto">
                    <table class="table-elite w-full">
                        <thead>
                            <tr>
                                <th class="text-left">Mata Pelajaran</th>
                                <th class="text-center">Guru</th>
                                <th class="text-center">Jam/Minggu</th>
                            </tr>
                        </thead>
                        <tbody>
                @php $prevCs = $alloc['class_section_id']; @endphp
            @endif
            <tr>
                <td class="font-serif font-semibold">{{ $alloc['subject_name'] }}</td>
                <td class="text-center text-sm">{{ $alloc['teacher_id'] ? \App\Models\User::find($alloc['teacher_id'])?->name ?? '-' : 'Belum diassign' }}</td>
                <td class="text-center">
                    <input type="number" name="hours[{{ $alloc['subject_id'] }}]"
                           value="{{ $alloc['hours'] }}" min="1" max="10"
                           class="w-20 text-center px-2 py-1 border border-stone-300 text-sm font-serif bg-white focus:outline-none focus:border-[var(--c-primary)]">
                </td>
            </tr>
        @endforeach
        @if($prevCs !== null)
                </tbody></table></div>
        @endif

        <div class="flex justify-between">
            <a href="{{ route('admin.timetable.generator.wizard', ['step' => 3]) }}" class="btn-elite-ghost">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Kembali
            </a>
            <button type="submit" class="btn-elite">
                Lanjut ke Generate
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </button>
        </div>
    </form>
</div>
@endif

{{-- STEP 5: Results Preview --}}
@if($currentStep === 5)
<div x-data="step5()">
    @if(!empty($warnings))
        <div class="elite-card p-6 mb-6 border-l-4" style="border-color: var(--c-warning, #eab308);">
            <h3 class="elite-h3 text-lg ink-primary mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" style="color: var(--c-warning, #eab308);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                Peringatan ({!! count($warnings) !!})
            </h3>
            <ul class="list-disc list-inside font-serif text-sm text-stone-700 space-y-1">
                @foreach($warnings as $w)
                    <li>{{ $w }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(empty($generatedSlots))
    <div class="elite-card p-10 text-center">
        <svg class="w-16 h-16 mx-auto mb-4" style="color: var(--c-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <h3 class="elite-h2 text-2xl ink-primary mb-2">Siap Generate Jadwal</h3>
        <p class="font-serif text-stone-500 mb-6">Klik tombol di bawah untuk memulai algoritma generate jadwal otomatis.</p>

        <form method="POST" action="{{ route('admin.timetable.generator.generate') }}" class="inline">
            @csrf
            <button type="submit" class="btn-elite text-base px-8 py-3">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Generate Jadwal
            </button>
        </form>
    </div>
    @else
    <div class="mb-6 flex items-center justify-between">
        <h3 class="elite-h2 text-2xl ink-primary">Hasil Generate Jadwal</h3>
        <a href="{{ route('admin.timetable.generator.wizard', ['step' => 1]) }}" class="btn-elite-ghost">Buat Ulang</a>
    </div>

    @foreach($generatedSlots as $csId => $daySlots)
        @php $cs = $classSections->firstWhere('id', $csId); @endphp
        <div class="elite-card p-6 mb-8" x-data="{ showCode: false }">
            <div class="flex items-center justify-between mb-4">
                <h4 class="elite-h3 text-xl ink-primary">{{ $cs->classRoom?->name ?? '' }} {{ $cs->section?->name ?? '' }}</h4>
                <button @click="showCode = !showCode" class="text-xs font-semibold uppercase tracking-wider px-3 py-1 border border-stone-300 hover:bg-stone-50 transition">
                    <span x-show="!showCode">Lihat Tabel</span>
                    <span x-show="showCode">Sembunyikan</span>
                </button>
            </div>

            <div x-show="showCode" class="overflow-x-auto">
                @php
                    $periods = [];
                    foreach ($daySlots as $slots) {
                        foreach ($slots as $slot) {
                            $periods[] = [$slot['start_time'], $slot['end_time']];
                        }
                    }
                    $allPeriods = collect($periods)->unique()->sort()->values();
                @endphp
                <table class="table-elite w-full text-xs">
                    <thead>
                        <tr>
                            <th class="text-left">Hari</th>
                            @foreach($allPeriods as $idx => $timeRange)
                                <th class="text-center">P{{ $idx + 1 }}<br>{{ $timeRange[0] }}-{{ $timeRange[1] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($daysList as $dCode => $dName)
                        <tr>
                            <td class="font-semibold whitespace-nowrap">{{ $dName }}</td>
                            @foreach($allPeriods as $pIdx => $timeRange)
                                @php
                                    $slot = collect($daySlots[$dCode] ?? [])->first(function($s) use ($timeRange) {
                                        return $s['start_time'] === $timeRange[0] && $s['end_time'] === $timeRange[1];
                                    });
                                    $colors = ['#e8f5e9', '#e3f2fd', '#fff3e0', '#fce4ec', '#f3e5f5', '#e0f7fa', '#f9fbe7', '#efebe9'];
                                    $bg = $slot ? $colors[($slot['subject_id'] ?? 0) % count($colors)] : 'transparent';
                                    $subjectName = $slot ? (\App\Models\Academic\Subject::find($slot['subject_id'])?->name ?? '?') : '';
                                    $teacherName = $slot ? (\App\Models\User::find($slot['teacher_id'])?->name ?? '?') : '';
                                @endphp
                                <td class="text-center p-2" style="background: {{ $bg }};">
                                    @if($slot)
                                        <div class="font-semibold">{{ $subjectName }}</div>
                                        <div class="text-[.6rem] text-stone-500 mt-0.5">{{ $teacherName }}</div>
                                    @else
                                        <span class="text-stone-300">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div class="flex justify-between mt-8">
        <a href="{{ route('admin.timetable.index') }}" class="btn-elite-ghost">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
            Kembali ke Jadwal
        </a>
        <a href="{{ route('admin.timetable.generator.wizard', ['step' => 1]) }}" class="btn-elite">
            Generate Ulang
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </a>
    </div>
    @endif
</div>
@endif

@push('scripts')
<script>
function step1() {
    return {
        selected: @json(collect($gen['class_section_ids'] ?? [])->mapWithKeys(fn($id) => [$id => true])->all()),
        selectedCount() {
            return Object.values(this.selected).filter(v => v).length;
        }
    };
}

function step2(existingConfigs, sessionConfigs) {
    return {
        existingConfigs: existingConfigs,
        sessionConfigs: sessionConfigs,
        getConfig(csId, key, defaultVal) {
            if (this.sessionConfigs[csId] && this.sessionConfigs[csId][key] !== undefined && this.sessionConfigs[csId][key] !== '') {
                return this.sessionConfigs[csId][key];
            }
            if (this.existingConfigs[csId] && this.existingConfigs[csId][key] !== undefined) {
                let val = this.existingConfigs[csId][key];
                if (key === 'break_after_periods' && Array.isArray(val)) return val.join(',');
                if (key === 'start_time' && val) return val.substring(0, 5);
                return val;
            }
            return defaultVal;
        }
    };
}

function step3() {
    return {
        checkAll(val) {
            document.querySelectorAll('input[type="checkbox"][name*="availability"]').forEach(cb => {
                cb.checked = val;
            });
        }
    };
}

function step4() {
    return {};
}

function step5() {
    return {};
}

@if($currentStep === 2)
document.addEventListener('DOMContentLoaded', () => {
    const data = @json(array_merge($existingConfigs ?? [], $gen['configs'] ?? []));
    document.querySelectorAll('select[name*="days_per_week"]').forEach(sel => {
        const match = sel.name.match(/config\[(\d+)\]\[days_per_week\]/);
        if (match) {
            const csId = match[1];
            const val = (data[csId]?.days_per_week) ?? 5;
            sel.value = val;
        }
    });
});
@endif
</script>
@endpush

@endsection
