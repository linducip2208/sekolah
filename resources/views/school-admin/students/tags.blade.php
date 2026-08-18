@extends('layouts.school-admin')
@section('title', 'Tag Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Students</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Tag Siswa</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Kelola label untuk mengelompokkan siswa (mis. Prestasi, Beasiswa, Riskan).</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <form method="POST" action="{{ route('admin.students.lifecycle.store-tag') }}" class="space-y-3">@csrf
                <input name="name" required maxlength="100" placeholder="Nama tag (mis. Prestasi)" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                <input name="color" maxlength="20" placeholder="Warna (mis. #10b981)" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs">
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama Tag</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Warna</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jumlah Siswa</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tags as $tag)
                    <tr class="border-t border-rule">
                        <td class="px-4 py-3 font-serif font-semibold text-xs">{{ $tag->name }}</td>
                        <td class="px-4 py-3">
                            @if($tag->color)<span class="inline-block w-4 h-4 rounded" style="background:{{ $tag->color }}"></span>
                            @else<span class="text-gray-400 text-xs">—</span>@endif
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $tag->students_count }} siswa</td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('admin.students.lifecycle.destroy-tag', $tag) }}" class="inline" onsubmit="return confirm('Hapus tag?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada tag.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
