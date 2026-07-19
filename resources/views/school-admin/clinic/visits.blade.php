@extends('layouts.school-admin')
@section('title', 'Kunjungan UKS')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Visitationes Clinicae</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Kunjungan UKS / Klinik</h1>
<div class="elite-rule"></div></div>
<a href="{{ route('admin.clinic.vaccinations.index') }}" class="btn-elite-ghost">Vaksinasi →</a></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Catat Kunjungan Baru</summary>
@if($errors->any())<div class="mx-5 my-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.clinic.visits.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">@csrf
<div><label class="elite-kicker text-[.6rem] block mb-1">Siswa</label>
<select name="student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— pilih —</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }} ({{ $s->admission_no }})</option>@endforeach
</select></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Tanggal & Jam</label>
<input type="datetime-local" name="visit_at" required value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Suhu (°C)</label>
<input type="number" step="0.1" min="30" max="45" name="temperature_c" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
<div class="md:col-span-3"><label class="elite-kicker text-[.6rem] block mb-1">Gejala</label>
<textarea name="symptoms" rows="2" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea></div>
<div class="md:col-span-2"><label class="elite-kicker text-[.6rem] block mb-1">Diagnosis</label>
<input name="diagnosis" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Tekanan Darah</label>
<input name="blood_pressure" maxlength="10" placeholder="120/80" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
<div class="md:col-span-3"><label class="elite-kicker text-[.6rem] block mb-1">Treatment</label>
<textarea name="treatment" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea></div>
<div class="md:col-span-3 flex gap-4">
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="returned_to_class" value="1"> Kembali ke kelas</label>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="sent_home" value="1"> Dipulangkan</label>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="parent_notified" value="1"> Ortu dihubungi</label>
</div>
<div class="md:col-span-3"><button class="btn-elite">Simpan Kunjungan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Gejala</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Suhu</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tindak Lanjut</th>
</tr></thead><tbody>
@forelse($visits as $v)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs font-mono">{{ $v->visit_at->format('d M Y H:i') }}</td>
<td class="px-3 py-3 font-serif">{{ $v->student?->user?->name }}</td>
<td class="px-3 py-3 text-xs text-gray-700">{{ Str::limit($v->symptoms, 80) }}</td>
<td class="px-3 py-3 font-mono text-xs">{{ $v->temperature_c ? $v->temperature_c.'°C' : '—' }}</td>
<td class="px-3 py-3 text-xs">
@if($v->returned_to_class)<span class="text-green-700">↩ Kembali</span>@endif
@if($v->sent_home)<span class="text-yellow-700">🏠 Pulang</span>@endif
@if($v->referred_external)<span class="text-red-700">🏥 Rujuk</span>@endif
</td></tr>
@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kunjungan.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $visits->links() }}</div>
@endsection
