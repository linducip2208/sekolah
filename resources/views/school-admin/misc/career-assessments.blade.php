@extends('layouts.school-admin')
@section('title', 'Career Assessment')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Career Assessments</h1><div class="elite-rule"></div></div>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe Test</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Hasil</th>
</tr></thead><tbody>
@forelse($assessments as $a)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ \Carbon\Carbon::parse($a->taken_at)->format('d M Y') }}</td>
<td class="px-3 py-3 font-serif">{{ $a->student_name }}</td>
<td class="px-3 py-3 text-xs">{{ $a->test_type }}</td>
<td class="px-3 py-3 text-xs">{{ Str::limit($a->result, 100) }}</td>
</tr>@empty<tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada assessment.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $assessments->links() }}</div>
@endsection
