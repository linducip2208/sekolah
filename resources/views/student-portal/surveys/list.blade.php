@extends('layouts.parent')
@section('title', 'Survei Kepuasan')
@section('content')
@include('student-portal._nav')

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="elite-kicker mb-2">Vox Discipuli</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Survei Kepuasan</h1>
        <p class="text-sm text-gray-600 font-serif">Isi survei yang tersedia untuk memberikan masukan berharga bagi sekolah.</p>
        <div class="elite-rule"></div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm font-serif">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 text-sm font-serif">{{ session('error') }}</div>
    @endif

    @if($activeTemplates->isEmpty())
        <div class="elite-card p-10 text-center">
            <p class="text-gray-500 font-serif text-lg italic">Tidak ada survei yang sedang aktif saat ini.</p>
        </div>
    @else
        <div class="grid md:grid-cols-2 gap-6">
            @foreach($activeTemplates as $tpl)
                <div class="elite-card p-6">
                    <div class="flex items-start justify-between mb-3">
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $tpl->survey_type === 'guru' ? 'bg-blue-50 text-blue-800' : 'bg-green-50 text-green-800' }}">
                            {{ ['guru' => 'Evaluasi Guru', 'staff' => 'Staff', 'kepsek' => 'Kepsek', 'fasilitas' => 'Fasilitas'][$tpl->survey_type] ?? $tpl->survey_type }}
                        </span>
                        @if($tpl->end_date)
                            <span class="text-xs text-gray-500">s/d {{ $tpl->end_date->format('d M Y') }}</span>
                        @endif
                    </div>
                    <h3 class="font-serif font-semibold ink-primary text-lg mb-2">{{ $tpl->title }}</h3>
                    <p class="text-sm text-gray-600 font-serif mb-4">{{ $tpl->description }}</p>
                    <a href="{{ route('student.surveys.fill', $tpl) }}" class="btn-elite text-xs">Isi Survei →</a>
                </div>
            @endforeach
        </div>
    @endif
</div>

@stop
