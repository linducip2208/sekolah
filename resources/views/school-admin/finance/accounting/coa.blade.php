@extends('layouts.school-admin')
@section('title', 'Bagan Akun')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Ratio Rationis</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Bagan Akun (COA)</h1>
    <div class="elite-rule"></div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif

<div class="flex gap-3 mb-4">
    <form method="POST" action="{{ route('admin.accounting.coa.seed') }}">@csrf
        <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Seed Akun Default</button>
    </form>
    <a href="{{ route('admin.accounting.journal.index') }}" class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Jurnal Umum</a>
    <a href="{{ route('admin.accounting.trial-balance') }}" class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Neraca Saldo</a>
</div>

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Akun</summary>
    <form method="POST" action="{{ route('admin.accounting.coa.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
        <input name="code" required maxlength="20" placeholder="Kode (mis. 4100)" class="border-2 border-rule px-3 py-2 font-mono text-sm">
        <input name="name" required maxlength="200" placeholder="Nama akun" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <select name="type" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="asset">Aset</option>
            <option value="liability">Kewajiban</option>
            <option value="equity">Ekuitas</option>
            <option value="revenue">Pendapatan</option>
            <option value="expense">Beban</option>
        </select>
        <select name="normal_balance" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="debit">Normal Debit</option>
            <option value="credit">Normal Kredit</option>
        </select>
        <div class="md:col-span-2"><button class="btn-elite">Simpan Akun</button></div>
    </form>
</details>

@foreach($types as $type)
    @php $rows = $accounts->get($type) ?? collect(); @endphp
    <div class="mb-6">
        <div class="elite-kicker text-[.7rem] mb-2 ink-secondary">{{ $typeLabels[$type] ?? $type }} ({{ $rows->count() }})</div>
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white"><tr>
                    <th class="text-left px-4 py-2 elite-kicker text-[.6rem]">Kode</th>
                    <th class="text-left px-4 py-2 elite-kicker text-[.6rem]">Nama</th>
                    <th class="text-center px-4 py-2 elite-kicker text-[.6rem]">Normal</th>
                    <th class="px-4 py-2"></th>
                </tr></thead>
                <tbody>
                    @forelse($rows as $a)
                    <tr class="border-t border-rule">
                        <td class="px-4 py-2 font-mono text-xs">{{ $a->code }}</td>
                        <td class="px-4 py-2 font-serif">{{ $a->name }}</td>
                        <td class="px-4 py-2 text-center text-xs">{{ $a->normal_balance }}</td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <details class="inline-block"><summary class="text-xs underline cursor-pointer ink-secondary">Edit</summary>
                            <form method="POST" action="{{ route('admin.accounting.coa.update', $a) }}" class="mt-2 grid gap-1">@csrf @method('PUT')
                                <input name="code" value="{{ $a->code }}" required class="border-2 border-rule px-2 py-1 font-mono text-xs">
                                <input name="name" value="{{ $a->name }}" required class="border-2 border-rule px-2 py-1 font-serif text-xs">
                                <select name="type" class="border-2 border-rule px-2 py-1 font-serif text-xs">
                                    @foreach($types as $t)<option value="{{ $t }}" @selected($a->type === $t)>{{ $typeLabels[$t] }}</option>@endforeach
                                </select>
                                <select name="normal_balance" class="border-2 border-rule px-2 py-1 font-serif text-xs">
                                    <option value="debit" @selected($a->normal_balance === 'debit')>Debit</option>
                                    <option value="credit" @selected($a->normal_balance === 'credit')>Kredit</option>
                                </select>
                                <button class="text-xs text-left ink-accent">Simpan</button>
                            </form></details>
                            <form method="POST" action="{{ route('admin.accounting.coa.destroy', $a) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')
                                <button class="text-xs text-red-700 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-6 text-center text-gray-400 italic font-serif">Tidak ada akun.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach

@endsection
