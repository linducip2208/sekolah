@extends('layouts.school-admin')
@section('title', 'Variasi Soal AI')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<a href="{{ route('admin.qbank.items.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">&larr; Bank Soal</a>
<div class="mb-7"><div class="elite-kicker mb-2">Variatio Artificialis</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Variasi Soal AI</h1><div class="elite-rule"></div></div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-sm text-red-800">{{ $errors->first() }}</div>
@endif

<div class="grid lg:grid-cols-2 gap-6">
<div class="bg-white border border-rule p-6">
<h3 class="elite-kicker text-[.65rem] mb-3">Soal Asli (ID: {{ $item->id }})</h3>
<div class="bg-gray-50 p-4 rounded font-serif text-sm leading-relaxed mb-3">{!! $item->question_html !!}</div>
<div class="text-xs space-y-1 text-gray-600">
    <div><strong>Tipe:</strong> {{ $item->type }}</div>
    <div><strong>Kesulitan:</strong> {{ $item->difficulty }}</div>
    <div><strong>Level Kognitif:</strong> {{ strtoupper($item->cognitive_level ?? '—') }}</div>
    @if($item->options)
    <div class="mt-2"><strong>Pilihan:</strong>
        <ul class="list-disc ml-4 mt-1">
        @foreach($item->options as $o)
            <li class="{{ ($o['is_correct'] ?? false) ? 'text-green-700 font-semibold' : '' }}">{{ $o['text'] ?? $o }}</li>
        @endforeach
        </ul>
    </div>
    @endif
    <div><strong>Kunci:</strong> {{ is_array($item->answer_key) ? implode(', ', $item->answer_key) : $item->answer_key }}</div>
</div>

<div class="mt-6 border-t border-rule pt-4">
<h4 class="font-semibold text-sm mb-3">Generate Variasi</h4>
<form id="variationForm" class="space-y-3">
    @csrf
    <input type="hidden" name="question_id" value="{{ $item->id }}">
    <div>
        <label class="text-xs text-gray-500 mb-1 block">Jumlah Variasi</label>
        <input name="variation_count" type="number" min="1" max="10" value="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
    </div>
    <div>
        <label class="text-xs text-gray-500 mb-1 block">Model AI (otomatis jika kosong)</label>
        <select name="ai_model_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">-- Otomatis --</option>
            @foreach($aiModels as $m)
            <option value="{{ $m->id }}">{{ $m->name ?? $m->model_name }} ({{ $m->provider?->name }})</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Generate Variasi</button>
</form>
</div>
</div>

<div class="bg-white border border-rule p-6 overflow-auto max-h-[700px]" id="result-panel">
<h3 class="elite-kicker text-[.65rem] mb-3">Hasil Variasi</h3>
<div id="result-content" class="space-y-4 font-serif text-sm"></div>
<div id="result-empty" class="text-center text-gray-400 italic py-10 font-serif">Klik "Generate Variasi" untuk memulai...</div>
</div>
</div>

@push('scripts')
<script>
document.getElementById('variationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.textContent = 'Generating...';
    btn.disabled = true;
    document.getElementById('result-empty').style.display = 'none';

    try {
        const formData = new FormData(form);
        const data = {
            question_id: parseInt(formData.get('question_id')),
            variation_count: parseInt(formData.get('variation_count')),
            ai_model_id: formData.get('ai_model_id') || undefined,
        };

        const res = await fetch('{{ url("/ai/teacher-assistant/variasi") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        });
        const result = await res.json();
        const panel = document.getElementById('result-content');
        panel.innerHTML = '';

        if (result.success && result.parsed && result.parsed.variations) {
            result.parsed.variations.forEach((v, i) => {
                let html = '<div class="border border-rule p-4 rounded">';
                html += '<div class="font-bold text-xs text-gray-500 mb-2">Variasi ' + (i + 1) + ' / ' + result.parsed.variations.length + '</div>';
                html += '<div class="mb-2">' + v.question + '</div>';
                if (v.options) {
                    v.options.forEach(o => {
                        const cls = o.is_correct ? 'font-bold text-green-700' : '';
                        html += '<div class="text-xs ml-4 ' + cls + '">' + o.text + '</div>';
                    });
                }
                if (v.explanation) html += '<div class="text-xs text-gray-500 italic mt-2">Pembahasan: ' + v.explanation + '</div>';
                html += '</div>';
                panel.innerHTML += html;
            });

            let saveBtn = '<form method="POST" action="{{ url("/ai/teacher-assistant/variasi/save") }}" class="mt-4">';
            saveBtn += '<input type="hidden" name="question_id" value="{{ $item->id }}">';
            saveBtn += '<input type="hidden" name="variation_count" value="' + data.variation_count + '">';
            saveBtn += '<input type="hidden" name="ai_model_id" value="' + (data.ai_model_id || '') + '">';
            saveBtn += '@csrf <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;background:var(--c-secondary,#059669);">Simpan Semua Variasi ke Bank Soal</button></form>';
            panel.innerHTML += saveBtn;
        } else {
            panel.innerHTML = '<div class="text-red-600 text-sm">Gagal generate: ' + (result.error || 'Response tidak valid') + '</div>';
        }
    } catch (err) {
        document.getElementById('result-content').innerHTML = '<div class="text-red-600 text-sm">Error: ' + err.message + '</div>';
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
});
</script>
@endpush
@endsection
