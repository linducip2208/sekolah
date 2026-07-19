@extends('layouts.school-admin')
@section('title', 'Live Class Attendance')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><h1 class="elite-h1 text-3xl ink-primary mb-2">Kehadiran Live Class</h1><div class="elite-rule"></div></div>
<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Topik</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Join</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Leave</th>
<th class="text-right px-3 py-3 elite-kicker text-[.6rem]">Total Menit</th>
</tr></thead><tbody>
@forelse($attendances as $a)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ Str::limit($a->topic, 40) }}</td>
<td class="px-3 py-3 font-serif text-xs">{{ $a->student_name }}</td>
<td class="px-3 py-3 text-xs font-mono">{{ \Carbon\Carbon::parse($a->joined_at)->format('d M H:i') }}</td>
<td class="px-3 py-3 text-xs font-mono">{{ $a->left_at ? \Carbon\Carbon::parse($a->left_at)->format('H:i') : '—' }}</td>
<td class="px-3 py-3 text-right font-mono">{{ $a->total_minutes }}</td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada catatan kehadiran live class.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $attendances->links() }}</div>
@endsection
