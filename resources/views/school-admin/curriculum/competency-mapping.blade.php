@extends('layouts.school-admin')
@section('title', 'Mapping TP → CP')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Competentia</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Mapping Tujuan Pembelajaran → Capaian Pembelajaran</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Tautkan setiap TP ke CP yang bersangkutan per mata pelajaran.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ session('error') }}</div>@endif

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-2 items-center">
    <select name="subject_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua mapel —</option>
        @foreach($subjects as $s)<option value="{{ $s->id }}" @selected(request('subject_id') == $s->id)>{{ $s->name }}</option>@endforeach
    </select>
    <select name="class_room_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua kelas —</option>
        @foreach($classRooms as $c)<option value="{{ $c->id }}" @selected(request('class_room_id') == $c->id)>{{ $c->name }}</option>@endforeach
    </select>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- CP List --}}
    <div class="bg-white border border-rule overflow-hidden">
        <div class="px-4 py-3 bg-[var(--c-primary)] text-white elite-kicker text-[.6rem]">Capaian Pembelajaran (CP)</div>
        <div class="max-h-[600px] overflow-y-auto">
            @forelse($cpItems as $cp)
            <div class="px-4 py-3 border-b border-rule text-sm hover:bg-gray-50">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-mono text-xs ink-secondary">{{ $cp->code }}</span>
                        <span class="elite-kicker text-[.55rem] ml-1">{{ $cp->subject?->name }}</span>
                        <p class="font-serif text-xs mt-1">{{ Str::limit($cp->description, 120) }}</p>
                    </div>
                    <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ count($cp->mapping_rules ?? []) }} TP</span>
                </div>
                @if(!empty($cp->mapping_rules))
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach($cp->mapping_rules as $tpId)
                        @php $tp = \App\Models\Curriculum\CurriculumCompetency::find($tpId); @endphp
                        @if($tp)
                        <form method="POST" action="{{ route('admin.curriculum.mapping.destroy') }}" class="inline">@csrf
                            <input type="hidden" name="tp_id" value="{{ $tpId }}">
                            <input type="hidden" name="cp_id" value="{{ $cp->id }}">
                            <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 text-[10px] px-2 py-0.5 rounded">
                                {{ $tp->code }}
                                <button type="submit" class="text-red-400 hover:text-red-600">&times;</button>
                            </span>
                        </form>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="p-10 text-center text-gray-500 italic font-serif text-sm">Tidak ada CP ditemukan.</div>
            @endforelse
        </div>
    </div>

    {{-- TP List --}}
    <div class="bg-white border border-rule overflow-hidden">
        <div class="px-4 py-3 bg-[var(--c-primary)] text-white elite-kicker text-[.6rem]">Tujuan Pembelajaran (TP)</div>
        <div class="max-h-[600px] overflow-y-auto">
            @forelse($tpItems as $tp)
            <div class="px-4 py-3 border-b border-rule text-sm hover:bg-gray-50" x-data="{ showMap: false }">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-mono text-xs ink-secondary">{{ $tp->code }}</span>
                        <span class="elite-kicker text-[.55rem] ml-1">{{ $tp->subject?->name }}</span>
                        <p class="font-serif text-xs mt-1">{{ Str::limit($tp->description, 120) }}</p>
                    </div>
                    <button @click="showMap = !showMap" class="text-xs ink-accent whitespace-nowrap" x-text="showMap ? 'Tutup' : 'Map → CP'"></button>
                </div>
                @if(!empty($tp->mapping_rules))
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach($tp->mapping_rules as $cpId)
                        @php $cp = \App\Models\Curriculum\CurriculumCompetency::find($cpId); @endphp
                        @if($cp)
                        <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded">{{ $cp->code }}</span>
                        @endif
                    @endforeach
                </div>
                @endif
                <div x-show="showMap" x-cloak class="mt-2 bg-gray-50 p-2 rounded">
                    <form method="POST" action="{{ route('admin.curriculum.mapping.store') }}" class="flex gap-2 items-end">@csrf
                        <input type="hidden" name="tp_id" value="{{ $tp->id }}">
                        <select name="cp_id" required class="flex-1 border-2 border-rule px-2 py-1 font-serif text-xs">
                            <option value="">— Pilih CP tujuan —</option>
                            @foreach($cpItems as $cp)<option value="{{ $cp->id }}">{{ $cp->code }} · {{ Str::limit($cp->description, 50) }}</option>@endforeach
                        </select>
                        <button class="btn-elite text-[10px] py-1 px-3">Simpan</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-10 text-center text-gray-500 italic font-serif text-sm">Tidak ada TP ditemukan.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
