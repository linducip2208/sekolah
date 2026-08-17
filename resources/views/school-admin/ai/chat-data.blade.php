@extends('layouts.school-admin')
@section('title', 'Tanya Data Sekolah (AI)')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Oraculum</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Tanya Data Sekolah</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Tanyakan data sekolah Anda dalam bahasa sehari-hari, mis. "Berapa siswa per kelas?" atau "Berapa tunggakan SPP bulan ini?"</p>
</div>

@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<form method="POST" action="{{ route('admin.ai.chat-data.ask') }}" class="bg-white border border-rule p-4 mb-6 flex gap-2">
    @csrf
    <input name="question" required maxlength="500" value="{{ session('ai_chat_question') }}" placeholder="Tanyakan data sekolah..." class="flex-1 border-2 border-rule px-4 py-2.5 font-serif text-sm">
    <button class="btn-elite">Tanya</button>
</form>

@if($last)
    <div class="bg-white border border-rule p-5 mb-6">
        <div class="elite-kicker text-[.6rem] text-gray-500 mb-1">Pertanyaan</div>
        <div class="font-serif mb-3">{{ session('ai_chat_question') }}</div>

        <div class="elite-kicker text-[.6rem] text-gray-500 mb-1">
            Jawaban · {{ $last['metric_label'] }}
            @if($last['used_ai'])<span class="text-violet-700">(AI)</span>@else<span class="text-gray-400">(rule-based)</span>@endif
        </div>
        <div class="font-display text-lg ink-primary mb-4">{{ $last['answer'] }}</div>

        @if(!empty($last['rows']))
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white"><tr>
                    @foreach($last['columns'] as $c)<th class="text-left px-4 py-2 elite-kicker text-[.6rem]">{{ $c }}</th>@endforeach
                </tr></thead>
                <tbody>
                    @foreach(array_slice($last['rows'], 0, 15) as $row)
                    <tr class="border-t border-rule">
                        <td class="px-4 py-2 font-serif">{{ $row['label'] }}</td>
                        <td class="px-4 py-2 font-mono text-xs">{{ isset($row['currency']) && $row['currency'] ? 'Rp ' . number_format($row['value']/100, 0, ',', '.') : $row['value'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
@endif

<div class="bg-white border border-rule overflow-hidden">
    <div class="px-4 py-3 elite-kicker text-[.7rem] bg-gray-50 border-b border-rule">Riwayat</div>
    <table class="w-full text-sm">
        <tbody>
            @forelse($history as $h)
            <tr class="border-b border-rule">
                <td class="px-4 py-2 font-serif">{{ $h->question }}</td>
                <td class="px-4 py-2 text-xs text-gray-500">{{ $h->metric_key }}</td>
                <td class="px-4 py-2 text-xs text-gray-400">{{ $h->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td class="p-8 text-center text-gray-400 italic font-serif">Belum ada riwayat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
