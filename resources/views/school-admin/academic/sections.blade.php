@extends('layouts.school-admin')
@section('title', 'Section')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Sectiones</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Section / Rombongan Belajar</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Mis. A, B, C atau IPA-1, IPS-2.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Section</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.academic.sections.store') }}" class="space-y-3">
                @csrf
                <input name="name" required maxlength="50" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="A">
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
                    @forelse($sections as $s)
                        <tr class="border-t border-rule">
                            <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $s->name }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.academic.sections.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus section ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="p-10 text-center text-gray-500 italic font-serif">Belum ada section.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
