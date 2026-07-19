@extends('layouts.school-admin')
@section('title', 'Deteksi Risiko Dropout')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Prædictio Periculi</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">AI Deteksi Risiko Dropout</h1>
    <div class="elite-rule"></div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    @php
        $summaryCards = [
            ['label' => 'Total Hari Ini', 'value' => $summary['total'], 'color' => '#64748B', 'icon' => '📊'],
            ['label' => 'Kritis', 'value' => $summary['critical'], 'color' => '#DC2626', 'icon' => '🔴'],
            ['label' => 'Tinggi', 'value' => $summary['high'], 'color' => '#EA580C', 'icon' => '🟠'],
            ['label' => 'Rendah & Aman', 'value' => $summary['medium'] + $summary['low'], 'color' => '#16A34A', 'icon' => '🟢'],
        ];
    @endphp
    @foreach($summaryCards as $card)
    <div class="bg-white border border-rule p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm">{{ $card['icon'] }}</span>
            <span class="elite-kicker text-[.55rem]" style="color:{{ $card['color'] }}">{{ $card['label'] }}</span>
        </div>
        <div class="font-display text-3xl font-bold" style="color:{{ $card['color'] }}">{{ $card['value'] }}</div>
    </div>
    @endforeach
</div>

@if($summary['lastRun'])
    <div class="text-[.6rem] text-gray-500 mb-4">Prediksi terakhir: {{ \Carbon\Carbon::parse($summary['lastRun'])->format('d M Y H:i') }}</div>
@endif

{{-- Actions --}}
<div class="flex flex-wrap gap-3 mb-5">
    <form method="POST" action="{{ route('admin.analytics.dropout-risk.predict') }}" class="inline">
        @csrf
        <input type="hidden" name="ai_provider_id" id="actionProviderId" value="">
        <input type="hidden" name="ai_model_id" id="actionModelId" value="">
        <button type="submit" class="btn-elite bg-red-800 border-red-800 text-white">
            🔮 Jalankan Prediksi Dropout
        </button>
    </form>
</div>

@if($providers->isNotEmpty())
<div class="bg-white border border-rule p-3 mb-5 flex flex-wrap items-center gap-3">
    <span class="elite-kicker text-[.6rem] text-gray-500">AI Model:</span>
    <select id="aiModelSelect" class="border-2 border-rule px-3 py-1.5 font-serif text-xs min-w-[200px]">
        <option value="">— Auto (default) —</option>
        @foreach($aiModels as $am)
            <option value="{{ $am->provider?->id }}|{{ $am->id }}">
                {{ $am->provider?->name }} / {{ $am->display_name ?? $am->model_name }}
            </option>
        @endforeach
    </select>
</div>
@else
    <div class="mb-5 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs">
        ⚠ Belum ada AI provider yang aktif. <a href="{{ route('admin.ai.providers.index') }}" class="underline font-semibold">Tambahkan provider AI</a> terlebih dahulu.
    </div>
@endif

{{-- Filter --}}
<div class="bg-white border border-rule p-3 mb-4 flex flex-wrap gap-2">
    @foreach(['' => 'Semua', 'critical' => '🔴 Kritis', 'high' => '🟠 Tinggi', 'medium' => '🟡 Sedang', 'low' => '🟢 Rendah'] as $val => $label)
        <a href="{{ route('admin.analytics.dropout-risk.index', ['risk_level' => $val]) }}"
           class="px-3 py-1.5 text-[.65rem] border {{ ($riskLevel === $val) || ($val === '' && !$riskLevel) ? 'bg-[var(--c-primary)] text-white border-[var(--c-primary)]' : 'border-rule hover:bg-gray-50' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Predictions Table --}}
<div class="bg-white border border-rule overflow-hidden mb-6">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem] w-8">
                    <input type="checkbox" id="selectAll" title="Pilih semua">
                </th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Kelas</th>
                <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Skor Risiko</th>
                <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Level</th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Faktor</th>
                <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Analisis AI</th>
                <th class="px-3 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($predictions as $p)
                @php
                    $factors = $p->contributing_factors ?? [];
                    $notified = $p->notified_parents || $p->notified_teacher;
                @endphp
                <tr class="border-t border-rule hover:bg-gray-50 {{ $p->risk_level === 'critical' ? 'bg-red-50/30' : ($p->risk_level === 'high' ? 'bg-orange-50/30' : '') }}">
                    <td class="px-3 py-3">
                        @if(in_array($p->risk_level, ['critical', 'high']))
                            <input type="checkbox" name="prediction_ids[]" value="{{ $p->id }}" class="prediction-checkbox">
                        @endif
                    </td>
                    <td class="px-3 py-3 font-serif font-semibold text-xs">
                        {{ $p->student?->user?->name ?? "Siswa #{$p->student_id}" }}
                        <div class="text-[.6rem] text-gray-500">{{ $p->student?->admission_no }}</div>
                    </td>
                    <td class="px-3 py-3 text-xs">{{ $p->student?->classSection?->classRoom?->name }} {{ $p->student?->classSection?->section?->name }}</td>
                    <td class="px-3 py-3 text-center">
                        <span class="font-mono font-bold text-lg" style="color:{{ $p->riskLevelColor() }}">
                            {{ $p->risk_score }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="px-2 py-0.5 text-[.6rem] font-medium {{ $p->riskLevelBadgeClass() }}">
                            {{ strtoupper($p->risk_level) }}
                        </span>
                        @if($notified)
                            <div class="text-[.55rem] text-green-700 mt-0.5">✓ Notified</div>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-xs">
                        @if($factors)
                            <div class="flex flex-wrap gap-1">
                                @if(isset($factors['attendance_pct']))
                                    <span class="px-1.5 py-0.5 bg-gray-100 text-[.6rem] {{ $factors['attendance_pct'] < 75 ? 'text-red-700' : '' }}">
                                        Absensi: {{ $factors['attendance_pct'] }}%
                                    </span>
                                @endif
                                @if(isset($factors['avg_mark_pct']))
                                    <span class="px-1.5 py-0.5 bg-gray-100 text-[.6rem] {{ $factors['avg_mark_pct'] < 60 ? 'text-red-700' : '' }}">
                                        Nilai: {{ $factors['avg_mark_pct'] }}%
                                    </span>
                                @endif
                                @if(isset($factors['discipline_count']) && $factors['discipline_count'] > 0)
                                    <span class="px-1.5 py-0.5 bg-gray-100 text-[.6rem] text-orange-700">
                                        Disiplin: {{ $factors['discipline_count'] }}
                                    </span>
                                @endif
                                @if(isset($factors['unpaid_invoices']) && $factors['unpaid_invoices'] > 0)
                                    <span class="px-1.5 py-0.5 bg-gray-100 text-[.6rem] text-red-700">
                                        SPP: {{ $factors['unpaid_invoices'] }} bln
                                    </span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-xs max-w-xs">
                        @if($p->ai_analysis)
                            <details>
                                <summary class="cursor-pointer text-blue-700 hover:underline text-[.65rem]">Lihat analisis</summary>
                                <div class="mt-2 p-3 bg-gray-50 border border-rule text-xs leading-relaxed whitespace-pre-wrap">
                                    {{ $p->ai_analysis }}
                                    @if($p->recommended_actions)
                                        <div class="mt-2 pt-2 border-t border-rule">
                                            <div class="font-semibold text-[.6rem] mb-1">Rekomendasi:</div>
                                            <div class="text-[.65rem]">{{ $p->recommended_actions }}</div>
                                        </div>
                                    @endif
                                    <div class="mt-2 text-[.55rem] text-gray-400">
                                        {{ $p->aiModel?->display_name ?? $p->aiModel?->model_name ?? 'AI' }}
                                        &middot; {{ $p->tokens_used }} token &middot; {{ $p->prediction_date->format('d M Y') }}
                                    </div>
                                </div>
                            </details>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-right whitespace-nowrap">
                        <form method="POST" action="{{ route('admin.analytics.dropout-risk.predict-one') }}" class="inline">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $p->student_id }}">
                            <input type="hidden" name="ai_provider_id" value="">
                            <input type="hidden" name="ai_model_id" value="">
                            <button class="text-xs underline ink-secondary hover:ink-accent">Prediksi Ulang</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="p-10 text-center text-gray-500 italic font-serif">
                        @if($summary['total'] === 0)
                            <div class="space-y-3">
                                <div class="font-display text-4xl opacity-30">📉</div>
                                <p>Belum ada data prediksi dropout.</p>
                                <p class="text-xs text-gray-400">Klik "Jalankan Prediksi Dropout" untuk memulai analisis AI.</p>
                            </div>
                        @else
                            Tidak ada data untuk filter yang dipilih.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mb-4">{{ $predictions->links() }}</div>

{{-- Notify Parents Button --}}
@if($predictions->whereIn('risk_level', ['critical', 'high'])->count() > 0)
<div class="bg-white border border-rule p-5">
    <div class="flex items-center justify-between">
        <div>
            <div class="font-serif font-bold text-lg mb-1">Notifikasi Orang Tua/Wali</div>
            <p class="text-xs text-gray-500">Kirim notifikasi WhatsApp ke orang tua siswa dengan risiko tinggi/kritis.</p>
        </div>
        <form id="notifyForm" method="POST" action="{{ route('admin.analytics.dropout-risk.notify') }}">
            @csrf
            <div id="notifyInputs"></div>
            <button type="submit" class="btn-elite bg-orange-700 border-orange-700 text-white"
                    onclick="return confirm('Kirim notifikasi WhatsApp ke orang tua siswa terpilih?')">
                📱 Notifikasi Orang Tua
            </button>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.prediction-checkbox');
    const notifyInputs = document.getElementById('notifyInputs');

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateNotifyInputs();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateNotifyInputs));

    function updateNotifyInputs() {
        if (!notifyInputs) return;
        notifyInputs.innerHTML = '';
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'prediction_ids[]';
                input.value = cb.value;
                notifyInputs.appendChild(input);
            }
        });
    }

    const aiSelect = document.getElementById('aiModelSelect');
    aiSelect?.addEventListener('change', function() {
        const val = this.value;
        const [providerId, modelId] = val.split('|');
        document.getElementById('actionProviderId').value = providerId || '';
        document.getElementById('actionModelId').value = modelId || '';
        document.querySelectorAll('form.inline input[name="ai_provider_id"]').forEach(el => el.value = providerId || '');
        document.querySelectorAll('form.inline input[name="ai_model_id"]').forEach(el => el.value = modelId || '');
    });
});
</script>
@endpush
@endsection
