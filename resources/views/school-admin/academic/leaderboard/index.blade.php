@extends('layouts.school-admin')
@section('title', 'Leaderboard Prestasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@push('head')
<style>
.podium { display: flex; align-items: flex-end; justify-content: center; gap: 1.5rem; min-height: 320px; }
.podium-item { display: flex; flex-direction: column; align-items: center; width: 180px; }
.podium-stand { width: 100%; border-radius: 4px 4px 0 0; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding-top: 1rem; transition: all .4s ease; }
.podium-1 .podium-stand { height: 220px; background: linear-gradient(180deg, #FFD700 0%, #B8860B 100%); }
.podium-2 .podium-stand { height: 180px; background: linear-gradient(180deg, #C0C0C0 0%, #808080 100%); }
.podium-3 .podium-stand { height: 140px; background: linear-gradient(180deg, #CD7F32 0%, #8B4513 100%); }
.podium-rank { font-family: 'Playfair Display', serif; font-size: 3rem; font-weight: 800; color: rgba(255,255,255,.85); text-shadow: 0 2px 8px rgba(0,0,0,.25); }
.podium-name { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; font-weight: 700; color: rgba(255,255,255,.95); text-align: center; margin-top: .25rem; }
.podium-score { font-size: .75rem; letter-spacing: .08em; color: rgba(255,255,255,.7); font-weight: 600; }
.podium-crown { font-size: 2.5rem; line-height: 1; margin-bottom: .25rem; }
</style>
@endpush

@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Honoris Discipulorum</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Leaderboard Prestasi</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Peringkat siswa berdasarkan bobot poin yang dikonfigurasi. Periode: <strong>{{ $periodLabel }}</strong>.</p>
</div>

{{-- Filters --}}
<div class="flex flex-wrap items-center gap-3 mb-6">
    <div class="flex bg-white border border-rule overflow-hidden">
        @foreach($periods as $key => $label)
        <a href="?period={{ $key }}@if($classSectionId)&class_section_id={{ $classSectionId }}@endif"
           class="px-4 py-2 text-xs font-semibold tracking-wide uppercase {{ $configType === $key ? 'bg-[var(--c-primary)] text-white' : 'text-gray-600 hover:bg-gray-50' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
    <form method="GET" class="flex items-center gap-2" x-data>
        <input type="hidden" name="period" value="{{ $configType }}">
        <select name="class_section_id" onchange="this.form.submit()" class="border-2 border-rule px-3 py-2 text-sm font-serif">
            <option value="">— Semua Kelas —</option>
            @foreach($classSections as $cs)
            <option value="{{ $cs->id }}" {{ (string)$classSectionId === (string)$cs->id ? 'selected' : '' }}>{{ $cs->name }}</option>
            @endforeach
        </select>
    </form>
    <span class="text-xs text-gray-500 italic font-serif">{{ count($rankings) }} siswa dalam peringkat</span>
</div>

{{-- Podium Top 3 --}}
@if(count($top3) >= 3)
<div class="elite-card p-8 mb-8">
    <div class="text-center mb-6">
        <div class="ornament-center mb-1"></div>
        <div class="elite-h2 text-2xl ink-primary">{{ $periodLabel }}</div>
    </div>
    <div class="podium">
        @php $ordered = [$top3[1] ?? null, $top3[0] ?? null, $top3[2] ?? null]; @endphp
        @foreach($ordered as $idx => $entry)
            @if(!$entry) @continue @endif
            @php $pos = $idx === 0 ? 2 : ($idx === 1 ? 1 : 3); @endphp
            <div class="podium-item podium-{{ $pos }}">
                @if($pos === 1)<div class="podium-crown">{{ __('leaderboard.crown') }}</div>@endif
                <div class="podium-stand">
                    <div class="podium-rank">{{ $entry['rank'] }}</div>
                    <div class="podium-name">{{ \Illuminate\Support\Str::limit($entry['student_name'], 20) }}</div>
                    <div class="podium-score">{{ (int)$entry['weighted_score'] }} poin</div>
                    <div style="font-size:.6rem;color:rgba(255,255,255,.55);margin-top:.15rem;">{{ $entry['class_section'] }}</div>
                </div>
                <div style="font-size:.7rem;letter-spacing:.1em;text-transform:uppercase;margin-top:.5rem;font-weight:600;color:var(--c-accent);">
                    {{ $pos === 1 ? 'JUARA 1' : ($pos === 2 ? 'JUARA 2' : 'JUARA 3') }}
                </div>
            </div>
        @endforeach
    </div>
</div>
@else
<div class="elite-card p-10 text-center mb-8">
    <div class="ornament-center mb-2"></div>
    <p class="font-serif text-lg text-gray-500 italic">Belum cukup data untuk menampilkan podium. Tambahkan poin ke siswa terlebih dahulu.</p>
</div>
@endif

{{-- Ranking Table --}}
@if(count($remaining) > 0)
<div class="elite-card overflow-hidden mb-8">
    <div class="px-5 py-4 border-b border-rule">
        <h3 class="elite-h3 text-lg ink-primary">Klasemen Lengkap</h3>
    </div>
    <div class="table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Peringkat</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Siswa</th>
                    <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kelas</th>
                    <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Akademik</th>
                    <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Absensi</th>
                    <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Ekskul</th>
                    <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Disiplin</th>
                    <th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Skor Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($remaining as $entry)
                <tr class="border-t border-rule {{ $entry['rank'] % 2 === 0 ? 'bg-gray-50/40' : '' }}">
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 font-display font-bold text-sm {{ $entry['rank'] <= 5 ? 'bg-[var(--c-accent)] text-white' : 'bg-gray-200 text-gray-600' }}">
                            {{ $entry['rank'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-serif font-semibold">{{ $entry['student_name'] }}</td>
                    <td class="px-4 py-3 text-xs">{{ $entry['class_section'] }}</td>
                    <td class="px-4 py-3 text-right font-mono text-xs">{{ (int)$entry['academic_points'] }}</td>
                    <td class="px-4 py-3 text-right font-mono text-xs">{{ (int)$entry['attendance_points'] }}</td>
                    <td class="px-4 py-3 text-right font-mono text-xs">{{ (int)$entry['extracurricular_points'] }}</td>
                    <td class="px-4 py-3 text-right font-mono text-xs">{{ (int)$entry['discipline_points'] }}</td>
                    <td class="px-4 py-3 text-right font-mono font-bold text-sm ink-accent">{{ (int)$entry['weighted_score'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Award/Deduct Points --}}
<div class="grid md:grid-cols-2 gap-6 mb-8">
    <div class="elite-card p-6">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Berikan Poin</h3>
        <form method="POST" action="{{ route('admin.leaderboard.award') }}">
            @csrf
            <div class="space-y-3">
                <select name="student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">— Pilih Siswa —</option>
                    @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }} ({{ $s->admission_no }})</option>@endforeach
                </select>
                <select name="point_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                    <option value="academic">Akademik</option>
                    <option value="attendance">Absensi</option>
                    <option value="extracurricular">Ekstrakurikuler</option>
                    <option value="discipline">Disiplin</option>
                    <option value="other">Lainnya</option>
                </select>
                <input type="number" name="points" required min="1" max="1000" placeholder="Jumlah Poin" class="w-full border-2 border-rule px-3 py-2 text-sm">
                <textarea name="reason" rows="2" required maxlength="500" placeholder="Alasan pemberian poin..." class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
                <button type="submit" class="btn-elite-gold w-full">Berikan Poin</button>
            </div>
        </form>
    </div>
    <div class="elite-card p-6">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Kurangi Poin</h3>
        <form method="POST" action="{{ route('admin.leaderboard.deduct') }}">
            @csrf
            <div class="space-y-3">
                <select name="student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">— Pilih Siswa —</option>
                    @foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }} ({{ $s->admission_no }})</option>@endforeach
                </select>
                <select name="point_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                    <option value="discipline">Disiplin</option>
                    <option value="academic">Akademik</option>
                    <option value="attendance">Absensi</option>
                    <option value="extracurricular">Ekstrakurikuler</option>
                    <option value="other">Lainnya</option>
                </select>
                <input type="number" name="points" required min="1" max="1000" placeholder="Jumlah Poin Dikurangi" class="w-full border-2 border-rule px-3 py-2 text-sm">
                <textarea name="reason" rows="2" required maxlength="500" placeholder="Alasan pengurangan poin..." class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
                <button type="submit" class="btn-elite w-full" style="background:var(--c-secondary);border-color:var(--c-secondary);">Kurangi Poin</button>
            </div>
        </form>
    </div>
</div>

{{-- Batch Award --}}
<div class="elite-card p-6 mb-8">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Poin Masal</h3>
    <form method="POST" action="{{ route('admin.leaderboard.award-batch') }}">
        @csrf
        <div class="space-y-3">
            <div class="max-h-48 overflow-y-auto border-2 border-rule p-3">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($students as $s)
                    <label class="flex items-center gap-2 text-xs cursor-pointer hover:bg-gray-50 px-2 py-1">
                        <input type="checkbox" name="student_ids[]" value="{{ $s->id }}" class="w-4 h-4"> {{ $s->user?->name }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <select name="point_type" required class="border-2 border-rule px-3 py-2 text-sm">
                    <option value="academic">Akademik</option>
                    <option value="attendance">Absensi</option>
                    <option value="extracurricular">Ekstrakurikuler</option>
                    <option value="discipline">Disiplin</option>
                    <option value="other">Lainnya</option>
                </select>
                <input type="number" name="points" required min="1" max="1000" placeholder="Jumlah Poin" class="border-2 border-rule px-3 py-2 text-sm">
                <button type="submit" class="btn-elite-gold">Berikan Poin Masal</button>
            </div>
            <textarea name="reason" rows="1" required maxlength="500" placeholder="Alasan pemberian poin masal..." class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        </div>
    </form>
</div>

{{-- Config Panel --}}
<div class="elite-card p-6 mb-8">
    <h3 class="elite-h3 text-lg ink-primary mb-4">Konfigurasi Bobot Leaderboard</h3>
    <form method="POST" action="{{ route('admin.leaderboard.config') }}">
        @csrf
        <input type="hidden" name="config_type" value="{{ $configType }}">
        <div class="grid md:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1">Akademik (%)</label>
                <input type="number" name="weight_academic" required min="0" max="100" value="{{ $config?->weight_academic ?? 30 }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1">Absensi (%)</label>
                <input type="number" name="weight_attendance" required min="0" max="100" value="{{ $config?->weight_attendance ?? 25 }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1">Ekskul (%)</label>
                <input type="number" name="weight_extracurricular" required min="0" max="100" value="{{ $config?->weight_extracurricular ?? 20 }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1">Disiplin (%)</label>
                <input type="number" name="weight_discipline" required min="0" max="100" value="{{ $config?->weight_discipline ?? 25 }}" class="w-full border-2 border-rule px-3 py-2 text-sm">
            </div>
        </div>
        <div class="flex items-center gap-3 mb-4">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ $config && $config->is_active ? 'checked' : 'checked' }} class="w-4 h-4"> Aktifkan leaderboard {{ strtolower($periodLabel) }}
            </label>
        </div>
        <button type="submit" class="btn-elite">Simpan Konfigurasi</button>
        <span class="text-xs text-gray-500 ml-3 font-serif italic">Total bobot harus 100%.</span>
    </form>
</div>

{{-- History & Sync --}}
<div class="flex justify-between items-center pt-4 border-t border-rule">
    <form method="POST" action="{{ route('admin.leaderboard.sync') }}" onsubmit="return confirm('Sinkronisasi poin dari data akademik? Ini akan membuat point records baru dari marks, attendance, dan discipline records.')">
        @csrf
        <button type="submit" class="btn-elite-ghost text-sm flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Sinkronisasi Poin dari Data Akademik
        </button>
    </form>
    <a href="{{ route('admin.leaderboard.history') }}" class="btn-elite-ghost text-sm">
        Riwayat Poin
    </a>
</div>
@endsection
