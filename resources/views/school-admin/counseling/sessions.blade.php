@extends('layouts.school-admin')
@section('title', 'Sesi Konseling')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7 flex justify-between items-end"><div>
<div class="elite-kicker mb-2">Sessiones BK</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Sesi Konseling</h1>
<div class="elite-rule"></div></div>
<a href="{{ route('admin.counseling.bullying.index') }}" class="btn-elite-ghost">Laporan Bullying →</a></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Jadwalkan Sesi</summary>
<form method="POST" action="{{ route('admin.counseling.sessions.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-3 gap-3">@csrf
<div><label class="elite-kicker text-[.6rem] block mb-1">Siswa</label>
<select name="student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— pilih —</option>
@foreach($students as $s)<option value="{{ $s->id }}">{{ $s->user?->name }}</option>@endforeach
</select></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Konselor</label>
<select name="counselor_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— pilih —</option>
@foreach($counselors as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
</select></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Tipe</label>
<select name="type" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="academic">Akademik</option><option value="behavior">Perilaku</option>
<option value="mental_health">Mental</option><option value="career">Karir</option>
<option value="family">Keluarga</option><option value="social">Sosial</option>
</select></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Jadwal</label>
<input type="datetime-local" name="scheduled_at" required class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Durasi (menit)</label>
<input type="number" min="5" max="300" name="duration_minutes" value="60" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
<div class="md:col-span-3"><label class="elite-kicker text-[.6rem] block mb-1">Catatan (rahasia)</label>
<textarea name="notes" rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea></div>
<div class="md:col-span-3"><button class="btn-elite">Simpan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Jadwal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Siswa</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Konselor</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
</tr></thead><tbody>
@forelse($sessions as $sess)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs font-mono">{{ $sess->scheduled_at?->format('d M Y H:i') }}</td>
<td class="px-3 py-3 font-serif">{{ $sess->student?->user?->name }}</td>
<td class="px-3 py-3 text-gray-700">{{ $sess->counselor?->name }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $sess->type }}</span></td>
<td class="px-3 py-3"><span class="text-xs px-2 py-0.5 rounded {{ $sess->status==='completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $sess->status }}</span></td>
</tr>@empty<tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada sesi.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $sessions->links() }}</div>
@endsection
