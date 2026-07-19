@extends('layouts.school-admin')
@section('title', 'Kelas')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Classes</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Kelas (Class Rooms)</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Mis. Kelas 10, Kelas 11, Kelas 12 (atau SD: Kelas 1-6).</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Kelas</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.academic.classes.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama Kelas</label>
                    <input name="name" required maxlength="100" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Kelas 10">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Medium</label>
                    <select name="medium_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih —</option>
                        @foreach($mediums as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                    </select>
                    @if($mediums->isEmpty())
                        <p class="text-xs text-yellow-700 mt-1">Belum ada medium. <a href="{{ route('admin.academic.mediums.index') }}" class="underline">Tambah dulu</a>.</p>
                    @endif
                </div>
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
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Medium</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $c)
                        <tr class="border-t border-rule">
                            <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $c->name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $c->medium?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.academic.classes.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus kelas ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kelas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
