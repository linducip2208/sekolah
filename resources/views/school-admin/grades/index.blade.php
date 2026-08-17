@extends('layouts.school-admin')
@section('title', 'Sistem Penilaian')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Gradus</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Sistem Penilaian (Grading Scale)</h1>
    <div class="elite-rule"></div>
    <p class="text-sm text-gray-600 mt-3">Konfigurasi rentang nilai (A–E) untuk seluruh penilaian sekolah.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Buat Sistem Penilaian</summary>
    <form method="POST" action="{{ route('admin.grades.store') }}" class="px-5 py-4 border-t border-rule flex gap-2">@csrf
        <input name="name" required maxlength="200" placeholder="Nama (mis. Kurikulum Merdeka)" class="flex-1 border-2 border-rule px-3 py-2 font-serif text-sm">
        <button class="btn-elite">Buat</button>
    </form>
</details>

@forelse($systems as $system)
<div class="bg-white border border-rule mb-5 overflow-hidden">
    <div class="px-5 py-4 bg-gray-50 border-b border-rule flex items-center justify-between">
        <div>
            <span class="font-serif font-semibold">{{ $system->name }}</span>
            @if($system->is_active)<span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-800 ml-2">Aktif</span>@endif
        </div>
        <div class="flex gap-3 text-xs">
            @if(!$system->is_active)
            <form method="POST" action="{{ route('admin.grades.activate', $system) }}" class="inline">@csrf<button class="underline ink-secondary">Aktifkan</button></form>
            @endif
            <form method="POST" action="{{ route('admin.grades.destroy', $system) }}" class="inline" onsubmit="return confirm('Hapus sistem penilaian?')">@csrf @method('DELETE')<button class="text-red-700 hover:underline">Hapus</button></form>
        </div>
    </div>
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500">
            <th class="px-5 py-2 elite-kicker text-[.6rem]">Grade</th>
            <th class="px-5 py-2 elite-kicker text-[.6rem]">Min %</th>
            <th class="px-5 py-2 elite-kicker text-[.6rem]">Max %</th>
            <th class="px-5 py-2 elite-kicker text-[.6rem]">GPA</th>
            <th class="px-5 py-2"></th>
        </tr></thead>
        <tbody>
            @forelse($system->rules as $rule)
            <tr class="border-t border-rule">
                <td class="px-5 py-2 font-display text-lg ink-primary">{{ $rule->grade }}</td>
                <td class="px-5 py-2 font-mono text-xs">{{ $rule->min_percent }}</td>
                <td class="px-5 py-2 font-mono text-xs">{{ $rule->max_percent }}</td>
                <td class="px-5 py-2 font-mono text-xs">{{ $rule->gpa_point }}</td>
                <td class="px-5 py-2 text-right">
                    <form method="POST" action="{{ route('admin.grades.rules.destroy', $rule) }}" onsubmit="return confirm('Hapus rentang ini?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-4 text-center text-gray-400 italic font-serif text-xs">Belum ada rentang nilai.</td></tr>
            @endforelse
        </tbody>
    </table>
    <form method="POST" action="{{ route('admin.grades.rules.store', $system) }}" class="px-5 py-4 border-t border-rule grid grid-cols-2 md:grid-cols-5 gap-2">@csrf
        <input name="grade" required maxlength="10" placeholder="Grade (A/B/C...)" class="border-2 border-rule px-2 py-1.5 font-serif text-xs">
        <input type="number" name="min_percent" step="0.01" min="0" max="100" required placeholder="Min %" class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
        <input type="number" name="max_percent" step="0.01" min="0" max="100" required placeholder="Max %" class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
        <input type="number" name="gpa_point" step="0.01" min="0" max="4" placeholder="GPA" class="border-2 border-rule px-2 py-1.5 font-mono text-xs">
        <button class="btn-elite" style="padding:.4rem;font-size:.65rem;">Tambah</button>
    </form>
</div>
@empty
<div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada sistem penilaian. Buat satu untuk mengganti rentang default (A=90+, dst).</div>
@endforelse

@endsection
