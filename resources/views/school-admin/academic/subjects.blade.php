@extends('layouts.school-admin')
@section('title', 'Mata Pelajaran')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Disciplinae</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Mata Pelajaran</h1>
    <div class="elite-rule"></div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Mapel</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.academic.subjects.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama</label>
                    <input name="name" required maxlength="100" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Matematika">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Kode</label>
                    <input name="code" maxlength="20" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="MTK-01">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Tipe</label>
                    <select name="type" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="theory">Teori</option>
                        <option value="practical">Praktikum</option>
                        <option value="both">Kombinasi</option>
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Medium</label>
                    <select name="medium_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— optional —</option>
                        @foreach($mediums as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Jam Kredit</label>
                    <input type="number" name="credit_hours" min="0" max="20" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
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
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Kode</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tipe</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">SKS</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $s)
                        <tr class="border-t border-rule">
                            <td class="px-4 py-3 font-serif font-semibold ink-primary">{{ $s->name }}</td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $s->code ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs"><span class="elite-kicker text-[.55rem]">{{ ucfirst($s->type ?? 'theory') }}</span></td>
                            <td class="px-4 py-3">{{ $s->credit_hours ?? '—' }}</td>
                            <td class="px-4 py-3"><span class="text-xs {{ $s->is_active ? 'text-green-700' : 'text-gray-500' }}">{{ $s->is_active ? '● Aktif' : 'Nonaktif' }}</span></td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.academic.subjects.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus mapel ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada mata pelajaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
