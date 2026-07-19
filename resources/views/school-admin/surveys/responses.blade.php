@extends('layouts.school-admin')
@section('title', 'Respons Survei — ' . $template->title)
@section('sidebar')@include('school-admin.partials.sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')

<div class="mb-7">
    <a href="{{ route('admin.surveys.templates.index') }}" class="text-xs ink-secondary hover:ink-accent">← Kembali ke Template</a>
    <div class="elite-kicker mb-2 mt-2">Responsa Collecta</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $template->title }}</h1>
    <p class="text-sm text-gray-600 font-serif mb-1">Total: {{ $responses->total() }} respons</p>
    <div class="elite-rule"></div>
</div>

<div class="elite-card overflow-hidden">
    @if($responses->count())
        @foreach($responses as $resp)
            <div x-data="{ open: false }" class="border-b border-rule last:border-b-0">
                <div class="flex items-center justify-between px-4 py-3 cursor-pointer hover:bg-gray-50" @click="open = !open">
                    <div>
                        <span class="font-sans font-semibold text-sm">
                            {{ $resp->respondent_type === 'student' ? '👨‍🎓' : '👨‍👩‍👧' }}
                            {{ ucfirst($resp->respondent_type) }} #{{ $resp->respondent_id }}
                        </span>
                        <span class="text-xs text-gray-500 ml-2">
                            Target: {{ $resp->target_type }}
                            @if($resp->target_id)
                                #{{ $resp->target_id }}
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-400">{{ $resp->submitted_at?->format('d M Y H:i') }}</span>
                        <form method="POST" action="{{ route('admin.surveys.responses.destroy', [$template, $resp]) }}" class="inline"
                              onsubmit="return confirm('Hapus respons ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-700 hover:underline">Hapus</button>
                        </form>
                        <svg class="w-4 h-4 text-gray-400 transform transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div x-show="open" x-cloak class="px-4 pb-4">
                    <div class="space-y-3">
                        @foreach($resp->answers as $ans)
                            <div class="p-3 bg-gray-50 rounded">
                                <p class="font-serif text-sm font-semibold ink-primary mb-1">{{ $ans->question?->question_text }}</p>
                                @if($ans->answer_rating)
                                    <div class="flex items-center gap-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="text-lg {{ $i <= $ans->answer_rating ? 'text-yellow-500' : 'text-gray-300' }}">{{ $i <= $ans->answer_rating ? '★' : '☆' }}</span>
                                        @endfor
                                        <span class="text-xs text-gray-500 ml-2">{{ $ans->answer_rating }}/5</span>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-700 font-serif italic">{{ $ans->answer_text ?: '-' }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

        <div class="px-4 py-3 border-t border-rule">
            {{ $responses->links() }}
        </div>
    @else
        <div class="p-10 text-center text-gray-500 italic font-serif">Belum ada respons.</div>
    @endif
</div>

@stop
