@extends('layouts.school-admin')
@section('title', 'Vaksinasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.clinic.visits.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kunjungan UKS</a>

<div class="mb-7"><div class="elite-kicker mb-2">Vaccinationes</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Catatan Vaksinasi</h1>
<div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Catat Vaksinasi</summary>
<form method="POST" action="{{ route('admin.clinic.vaccinations.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">@csrf
<div><label class="elite-kicker text-[.6rem] block mb-1">Siswa</label>
<select name="student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— pilih —</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
</select></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Vaksin</label>
<input name="vaccine_name" required maxlength="200" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label>
<input type="date" name="vaccinated_at" required class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Batch No</label>
<input name="batch_number" maxlength="50" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Pemberi</label>
<input name="administered_by" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Dosis Selanjutnya</label>
<input type="date" name="next_dose_due" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div class="md:col-span-3"><button class="btn-elite">Simpan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Vaksin</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Batch</th>
<th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Dosis Lanjut</th>
</tr></thead><tbody>
@forelse($vaccinations as $v)<tr class="border-t border-rule">
<td class="px-4 py-3 text-xs">{{ $v->vaccinated_at->format('d M Y') }}</td>
<td class="px-4 py-3 font-serif">{{ $v->student?->user?->name }}</td>
<td class="px-4 py-3 font-serif font-semibold">{{ $v->vaccine_name }}</td>
<td class="px-4 py-3 font-mono text-xs">{{ $v->batch_number ?? '—' }}</td>
<td class="px-4 py-3 text-xs">{{ $v->next_dose_due?->format('d M Y') ?? '—' }}</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada catatan vaksin.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $vaccinations->links() }}</div>
@endsection
