@extends('layouts.school-admin')
@section('title', 'Gate Pass Asrama')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Asrama</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Gate Pass</h1><div class="elite-rule"></div></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<form method="POST" action="{{ route('admin.hostel.gate-passes.store') }}" class="space-y-3">@csrf
<select name="student_id" required class="w-full border-2 border-rule px-3 py-2 text-sm"><option value="">— Siswa —</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user->name }}</option>@endforeach</select>
<select name="pass_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
<option value="out">Keluar</option><option value="in">Masuk</option></select>
<input name="purpose" required maxlength="500" placeholder="Keperluan" class="w-full border-2 border-rule px-3 py-2 text-sm">
<input name="visitor_name" maxlength="200" placeholder="Nama Pengantar (opsional)" class="w-full border-2 border-rule px-3 py-2 text-sm">
<input name="visitor_phone" maxlength="30" placeholder="Telepon Pengantar (opsional)" class="w-full border-2 border-rule px-3 py-2 text-sm">
<input type="datetime-local" name="expected_return" class="w-full border-2 border-rule px-3 py-2 text-sm">
<textarea name="note" maxlength="1000" rows="2" placeholder="Catatan (opsional)" class="w-full border-2 border-rule px-3 py-2 text-sm"></textarea>
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Buat Gate Pass</button>
</form></div></div>

<div class="lg:col-span-2"><div class="bg-white border border-rule overflow-hidden">
<table class="w-full text-sm"><thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-center px-4 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Keperluan</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
<th class="text-right px-4 py-3 elite-kicker text-[.6rem]">Aksi</th>
</tr></thead><tbody>
@forelse($passes as $p)<tr class="border-t border-rule">
<td class="px-4 py-3 text-sm font-medium">{{ $p->student->user->name }}</td>
<td class="px-4 py-3 text-center"><span class="text-xs px-2 py-0.5 rounded {{ $p->pass_type==='out' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">{{ $p->pass_type==='out' ? 'Keluar' : 'Masuk' }}</span></td>
<td class="px-4 py-3 text-sm max-w-[200px] truncate">{{ $p->purpose }}</td>
<td class="px-4 py-3">
    @if($p->status==='pending')<span class="text-xs px-2 py-0.5 rounded bg-yellow-100 text-yellow-700">Menunggu</span>
    @elseif($p->status==='approved')<span class="text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">Disetujui</span>
    @elseif($p->status==='rejected')<span class="text-xs px-2 py-0.5 rounded bg-red-100 text-red-700">Ditolak</span>
    @else<span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700">Selesai</span>@endif
</td>
<td class="px-4 py-3 text-right">
    @if($p->status==='pending')
    <form method="POST" action="{{ route('admin.hostel.gate-passes.approve', $p) }}" class="inline">@csrf
        <button class="text-xs text-green-700 hover:underline">Setuju</button></form>
    <form method="POST" action="{{ route('admin.hostel.gate-passes.reject', $p) }}" class="inline">@csrf
        <button class="text-xs text-red-700 hover:underline">Tolak</button></form>
    @elseif($p->status==='approved')
    <form method="POST" action="{{ route('admin.hostel.gate-passes.complete', $p) }}" class="inline">@csrf
        <button class="text-xs text-blue-700 hover:underline">Selesai</button></form>
    @endif
</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada gate pass.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
