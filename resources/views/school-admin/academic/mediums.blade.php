@extends('layouts.school-admin')
@section('title', 'Medium')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Lingua Instructionis</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Medium / Bahasa Pengantar</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Mis. Bahasa Indonesia, Inggris, Bilingual.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Medium</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.academic.mediums.store') }}" class="space-y-3">
                @csrf
                <input name="name" required maxlength="100" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Bahasa Indonesia">
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Nama</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mediums as $m)
                        <tr class="border-t border-rule">
                            <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $m->name }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.academic.mediums.destroy', $m) }}" class="inline" onsubmit="return confirm('Hapus medium ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="p-10 text-center text-gray-500 italic font-serif">Belum ada medium.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
