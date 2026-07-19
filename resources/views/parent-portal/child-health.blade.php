@extends('layouts.parent')
@section('title', 'UKS - '.$student->user?->name)
@section('content')
<a href="{{ route('portal.child', $student) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← {{ $student->user?->name }}</a>
@include('parent-portal._child_tabs')

<h2 class="elite-h2 text-2xl ink-primary mb-4">Riwayat UKS / Klinik</h2>
<div class="bg-white border border-rule overflow-hidden mb-7"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Gejala</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Diagnosis</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Suhu</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tindak Lanjut</th>
</tr></thead><tbody>
@forelse($visits as $v)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs font-mono">{{ $v->visit_at->format('d M Y') }}</td>
<td class="px-3 py-3 text-xs">{{ Str::limit($v->symptoms, 60) }}</td>
<td class="px-3 py-3 text-xs">{{ $v->diagnosis ?? '—' }}</td>
<td class="px-3 py-3 font-mono text-xs">{{ $v->temperature_c ? $v->temperature_c.'°C' : '—' }}</td>
<td class="px-3 py-3 text-xs">
@if($v->returned_to_class)<span class="text-green-700">↩ Kembali kelas</span>@endif
@if($v->sent_home)<span class="text-yellow-700">🏠 Pulang</span>@endif
@if($v->referred_external)<span class="text-red-700">🏥 Rujuk RS</span>@endif
</td></tr>
@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada kunjungan UKS.</td></tr>@endforelse
</tbody></table></div>
<div class="mb-7">{{ $visits->links() }}</div>

<h2 class="elite-h2 text-2xl ink-primary mb-4">Vaksinasi</h2>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Vaksin</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Batch</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Dosis Lanjut</th>
</tr></thead><tbody>
@forelse($vaccinations as $v)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $v->vaccinated_at->format('d M Y') }}</td>
<td class="px-3 py-3 font-serif font-semibold">{{ $v->vaccine_name }}</td>
<td class="px-3 py-3 font-mono text-xs">{{ $v->batch_number ?? '—' }}</td>
<td class="px-3 py-3 text-xs">{{ $v->next_dose_due?->format('d M Y') ?? '—' }}</td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada vaksinasi tercatat.</td></tr>@endforelse
</tbody></table></div>
@endsection
