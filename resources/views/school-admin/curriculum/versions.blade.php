@extends('layouts.school-admin')
@section('title', 'Versi Kurikulum')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Curriculum</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Versi Kurikulum</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Kelola versi/edisi kurikulum per framework.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if(session('error'))<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ session('error') }}</div>@endif

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <form method="POST" action="{{ route('admin.curriculum.versions.store') }}" class="space-y-3">@csrf
                <select name="curriculum_framework_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">— Pilih framework —</option>
                    @foreach($frameworks as $f)<option value="{{ $f->id }}">{{ $f->name }} ({{ $f->type }})</option>@endforeach
                </select>
                <input name="version_name" required maxlength="100" placeholder="Nama versi (mis. Edisi 2026)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <input name="academic_year" maxlength="20" placeholder="Tahun ajaran (mis. 2026/2027)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <input name="effective_date" type="date" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <textarea name="notes" rows="2" placeholder="Catatan (opsional)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Versi</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Framework</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tahun</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Efektif</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($versions as $v)
                    <tr class="border-t border-rule">
                        <td class="px-4 py-3 font-serif font-semibold">{{ $v->version_name }}</td>
                        <td class="px-4 py-3 text-xs">{{ $v->framework?->name }}</td>
                        <td class="px-4 py-3 text-xs">{{ $v->academic_year ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $v->effective_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="elite-kicker text-[.55rem] {{ $v->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $v->is_active ? '● Aktif' : 'Draft' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap text-xs">
                            @if(!$v->is_active)
                            <form method="POST" action="{{ route('admin.curriculum.versions.activate', $v) }}" class="inline">@csrf
                                <button class="text-green-700 hover:underline">Aktifkan</button>
                            </form>
                            @endif
                            <details class="inline-block ml-2"><summary class="underline cursor-pointer ink-secondary">Edit</summary>
                                <form method="POST" action="{{ route('admin.curriculum.versions.update', $v) }}" class="mt-2 grid gap-1">@csrf @method('PUT')
                                    <input name="version_name" value="{{ $v->version_name }}" required class="border-2 border-rule px-2 py-1 font-serif text-xs">
                                    <input name="academic_year" value="{{ $v->academic_year }}" class="border-2 border-rule px-2 py-1 font-serif text-xs">
                                    <input name="effective_date" type="date" value="{{ $v->effective_date?->format('Y-m-d') }}" class="border-2 border-rule px-2 py-1 font-serif text-xs">
                                    <textarea name="notes" rows="2" class="border-2 border-rule px-2 py-1 font-serif text-xs">{{ $v->notes }}</textarea>
                                    <button class="text-xs text-left ink-accent">Simpan</button>
                                </form>
                            </details>
                            <form method="POST" action="{{ route('admin.curriculum.versions.destroy', $v) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-700 hover:underline">Hapus</button></form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada versi kurikulum.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
