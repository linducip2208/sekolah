@extends('layouts.school-admin')
@section('title', 'Event Sekolah')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Eventus Scholae</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Event Sekolah</h1><div class="elite-rule"></div></div>

<details class="mb-6 bg-white border border-rule">
<summary class="px-5 py-4 cursor-pointer elite-kicker">+ Buat Event</summary>
<form method="POST" action="{{ route('admin.events.store') }}" class="px-5 py-5 border-t border-rule grid md:grid-cols-2 gap-3">@csrf
<input name="title" required maxlength="255" placeholder="Judul" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="slug" required pattern="[a-z0-9\-]+" maxlength="200" placeholder="slug-url" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<select name="event_type" required class="border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="academic">Akademik</option><option value="cultural">Budaya</option>
<option value="sports">Olahraga</option><option value="fundraising">Fundraising</option>
<option value="reunion">Reuni</option><option value="workshop">Workshop</option>
<option value="seminar">Seminar</option><option value="competition">Lomba</option>
</select>
<input type="datetime-local" name="starts_at" required class="border-2 border-rule px-3 py-2 text-sm">
<input type="datetime-local" name="ends_at" required class="border-2 border-rule px-3 py-2 text-sm">
<input name="venue" maxlength="200" placeholder="Lokasi" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="city" maxlength="100" placeholder="Kota" class="border-2 border-rule px-3 py-2 font-serif text-sm">
<input type="number" min="1" name="capacity" placeholder="Kapasitas" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input type="number" step="1000" min="0" name="ticket_price_rupiah" placeholder="Harga tiket (Rp)" class="border-2 border-rule px-3 py-2 font-mono text-sm">
<input name="target_audience" maxlength="200" placeholder="Target audience" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
<textarea name="description" rows="3" required placeholder="Deskripsi" class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
<div class="md:col-span-2"><button class="btn-elite">Simpan Event</button></div>
</form></details>

<div class="bg-white border border-rule overflow-hidden"><table class="w-full text-sm">
<thead class="bg-[var(--c-primary)] text-white"><tr>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tanggal</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Event</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
<th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Lokasi</th>
<th class="text-center px-3 py-3 elite-kicker text-[.6rem]">RSVP</th>
<th></th></tr></thead><tbody>
@forelse($events as $e)<tr class="border-t border-rule">
<td class="px-3 py-3 text-xs">{{ $e->starts_at?->format('d M Y H:i') }}</td>
<td class="px-3 py-3 font-serif font-semibold">{{ $e->title }}</td>
<td class="px-3 py-3"><span class="elite-kicker text-[.55rem]">{{ $e->event_type }}</span></td>
<td class="px-3 py-3 text-xs">{{ $e->venue }} {{ $e->city ? '· '.$e->city : '' }}</td>
<td class="px-3 py-3 text-center font-mono">{{ $e->rsvps_count }}</td>
<td class="px-3 py-3 text-right"><form method="POST" action="{{ route('admin.events.destroy', $e) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form></td>
</tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada event.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-4">{{ $events->links() }}</div>
@endsection
