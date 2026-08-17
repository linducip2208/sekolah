@extends('layouts.school-admin')
@section('title', 'Rencana Perbaikan Akreditasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.accreditation.dashboard') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Dashboard Akreditasi</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Consilium Emendandi</div>
    <h1 class="elite-h1 text-2xl ink-primary mb-2">Rencana Perbaikan Akreditasi</h1>
    <div class="elite-rule"></div>
</div>

@if(session('success'))<div class="mb-3 px-3 py-2 bg-green-50 text-xs text-green-800">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif

<details class="mb-6 bg-white border border-rule">
    <summary class="px-5 py-4 cursor-pointer elite-kicker">+ Tambah Rencana Perbaikan</summary>
    <form method="POST" action="{{ route('admin.accreditation.action-plans.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
        <input name="title" required maxlength="200" placeholder="Judul rencana" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <select name="accreditation_standard_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— standar (opsional) —</option>
            @foreach($standards as $s)<option value="{{ $s->id }}">{{ $s->code }} · {{ $s->name }}</option>@endforeach
        </select>
        <select name="accreditation_instrument_id" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— instrumen (opsional) —</option>
            @foreach($instruments as $i)<option value="{{ $i->id }}">{{ $i->standard?->code }} - {{ $i->number }} {{ $i->title }}</option>@endforeach
        </select>
        <textarea name="action" rows="3" required placeholder="Rencana tindakan..." class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <select name="responsible_id" class="border-2 border-rule px-3 py-2 font-serif text-sm">
            <option value="">— penanggung jawab —</option>
            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
        </select>
        <input type="date" name="due_date" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <textarea name="notes" rows="2" placeholder="Catatan (opsional)" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
        <div class="md:col-span-2"><button class="btn-elite">Simpan</button></div>
    </form>
</details>

<form method="GET" class="bg-white border border-rule p-4 mb-4 flex gap-2 items-center">
    <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua status —</option>
        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
        <option value="in_progress" @selected(request('status') === 'in_progress')>Berjalan</option>
        <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
    </select>
    <button class="btn-elite" style="padding:.5rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white"><tr>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Rencana</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Standar/Instrumen</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">PJ</th>
            <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Batas</th>
            <th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Status</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody>
            @forelse($plans as $p)
            <tr class="border-t border-rule hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="font-serif font-semibold">{{ $p->title }}</div>
                    <div class="text-xs text-gray-500">{{ Str::limit($p->action, 80) }}</div>
                </td>
                <td class="px-4 py-3 text-xs">{{ $p->standard?->code ?? '—' }} {{ $p->instrument ? '- ' . $p->instrument->number : '' }}</td>
                <td class="px-4 py-3 text-xs">{{ $p->responsible?->name ?? '—' }}</td>
                <td class="px-4 py-3 font-mono text-xs {{ $p->due_date && $p->due_date->isPast() && $p->status !== 'completed' ? 'text-red-700 font-bold' : '' }}">{{ $p->due_date?->format('d M Y') ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                    @if($p->status === 'completed')<span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-800">Selesai</span>
                    @elseif($p->status === 'in_progress')<span class="text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-800">Berjalan</span>
                    @else<span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700">Pending</span>@endif
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    @if($p->status !== 'completed')
                    <form method="POST" action="{{ route('admin.accreditation.action-plans.status', $p) }}" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="{{ $p->status === 'pending' ? 'in_progress' : 'completed' }}">
                        <button class="text-xs underline ink-secondary hover:ink-accent">{{ $p->status === 'pending' ? 'Mulai' : 'Selesai' }}</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.accreditation.action-plans.destroy', $p) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada rencana perbaikan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $plans->links() }}</div>

@endsection
