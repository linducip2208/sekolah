@extends('layouts.school-admin')
@section('title', 'Live Class')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Lectiones in Linea</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Sesi Live Class</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Jadwalkan Sesi</summary>
@if($errors->any())<div class="mx-5 my-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.live-class.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<div class="md:col-span-2"><input name="topic" required maxlength="255" placeholder="Topik sesi" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
<select name="class_section_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Rombel —</option>
@foreach($classSections as $cs)<option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>@endforeach
</select>
<select name="subject_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Mapel —</option>
@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
</select>
<select name="teacher_id" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— Guru —</option>
@foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
</select>
<input type="datetime-local" name="scheduled_start" required class="border-2 border-rule px-3 py-2 text-sm">
<input type="number" name="duration_minutes" min="15" max="480" required value="60" placeholder="Durasi (menit)" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<div class="md:col-span-2"><input type="url" name="join_url" required maxlength="500" placeholder="Join URL (Zoom/Meet/Jitsi)" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
<input name="meeting_id" maxlength="100" placeholder="Meeting ID" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input name="passcode" maxlength="50" placeholder="Passcode (opsional)" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<div class="md:col-span-2"><button class="btn-elite">Jadwalkan</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Topik</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Rombel</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Guru</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Mulai</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
<th></th></tr></thead><tbody>
@forelse($sessions as $sess)<tr class="border-t border-rule">
<td class="px-3 py-3 font-serif font-semibold">{{ $sess->topic }}</td>
<td class="px-3 py-3 text-xs">{{ $sess->classSection?->classRoom?->name }} {{ $sess->classSection?->section?->name }}</td>
<td class="px-3 py-3 text-xs">{{ $sess->teacher?->name }}</td>
<td class="px-3 py-3 text-xs font-mono">{{ $sess->scheduled_start?->format('d M Y H:i') }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $sess->status }}</span></td>
<td class="px-3 py-3 text-right">
<a href="{{ $sess->join_url }}" target="_blank" class="text-xs underline ink-secondary hover:ink-accent">Join</a>
<form method="POST" action="{{ route('admin.live-class.destroy', $sess) }}" class="inline ml-2" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
</td></tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada sesi.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $sessions->links() }}</div>
@endsection
