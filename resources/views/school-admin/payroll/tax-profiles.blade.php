@extends('layouts.school-admin')
@section('title', 'Profil Pajak Staff')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Profil Fiscalis</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Profil Pajak Staff</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">NPWP, status PTKP, dan pengaturan BPJS/PPh21 per staff.</p>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Staff</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">NPWP</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">PTKP</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggungan</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">BPJS</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">PPh21</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($staffs as $s)
                @php $p = $profiles->get($s->id); @endphp
                <tr class="border-t border-rule hover:bg-gray-50" x-data="{ edit: false }">
                    <td class="px-4 py-3 font-serif">{{ $s->user?->name ?? $s->employee_id }}</td>
                    <td class="px-4 py-3 font-mono text-xs" x-show="!edit">{{ $p?->npwp ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs" x-show="!edit">{{ $ptkpLabels[$p?->pTKP_status ?? 1] ?? 'TK/0' }}</td>
                    <td class="px-4 py-3 text-xs" x-show="!edit">{{ $p?->number_of_dependents ?? 0 }}</td>
                    <td class="px-4 py-3" x-show="!edit">
                        <span class="text-xs px-2 py-0.5 rounded {{ $p?->is_bpjs_active ?? true ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $p?->is_bpjs_active ?? true ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3" x-show="!edit">
                        <span class="text-xs px-2 py-0.5 rounded {{ $p?->is_pph21_active ?? true ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $p?->is_pph21_active ?? true ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right" x-show="!edit">
                        <button @click="edit = true" class="text-xs underline ink-secondary">Edit</button>
                    </td>

                    {{-- Edit Form --}}
                    <td colspan="7" x-show="edit" x-cloak>
                        <form method="POST" action="{{ route('admin.payroll.tax-profile.update', $s->id) }}" class="p-4 bg-gray-50 grid grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                            @csrf
                            <div>
                                <label class="elite-kicker text-[.55rem] block mb-1">NPWP</label>
                                <input name="npwp" value="{{ $p?->npwp }}" class="w-full border border-rule px-2 py-1 font-mono text-xs">
                            </div>
                            <div>
                                <label class="elite-kicker text-[.55rem] block mb-1">Status PTKP</label>
                                <select name="pTKP_status" class="w-full border border-rule px-2 py-1 text-xs">
                                    @foreach($ptkpLabels as $k => $v)
                                        <option value="{{ $k }}" {{ ($p?->pTKP_status ?? 1) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="elite-kicker text-[.55rem] block mb-1">Tanggungan</label>
                                <input type="number" name="number_of_dependents" value="{{ $p?->number_of_dependents ?? 0 }}" min="0" max="3" class="w-full border border-rule px-2 py-1 font-mono text-xs">
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_bpjs_active" value="1" {{ ($p?->is_bpjs_active ?? true) ? 'checked' : '' }} class="rounded">
                                <label class="text-xs">BPJS Aktif</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_pph21_active" value="1" {{ ($p?->is_pph21_active ?? true) ? 'checked' : '' }} class="rounded">
                                <label class="text-xs">PPh21 Aktif</label>
                            </div>
                            <div class="flex gap-2">
                                <button class="btn-elite" style="padding:.3rem .6rem;font-size:.6rem;">Simpan</button>
                                <button type="button" @click="edit = false" class="btn-elite-ghost" style="font-size:.6rem;">Batal</button>
                            </div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada data staff.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
