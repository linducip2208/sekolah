@extends('super-admin.layout')
@section('title', 'Audit Log')
@section('content')

<div class="mb-8">
    <div class="elite-kicker mb-2">Cronicon Actorum</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Catatan Aktivitas Sistem</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-base text-gray-600">Jejak audit semua perubahan model di seluruh platform — total {{ $logs->total() }} entri.</p>
</div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-3 gap-3">
    <select name="event" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua Event —</option>
        @foreach($events as $e)
            <option value="{{ $e }}" @selected(request('event')===$e)>{{ $e }}</option>
        @endforeach
    </select>
    <input type="text" name="log_name" value="{{ request('log_name') }}" placeholder="Log name"
           class="border-2 border-rule px-3 py-2 font-serif text-sm">
    <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Waktu</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Event</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Subject</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Pelaku</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-600 whitespace-nowrap">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded
                            {{ $log->event === 'created' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $log->event === 'updated' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $log->event === 'deleted' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ $log->event ?? '—' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs">
                        <div class="font-mono text-gray-700">{{ class_basename($log->subject_type ?? '') }}</div>
                        <div class="text-gray-400">#{{ $log->subject_id ?? '—' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if($log->causer)
                            <div class="font-serif text-sm">{{ $log->causer->name }}</div>
                            <div class="text-xs text-gray-400">{{ $log->causer->email }}</div>
                        @else
                            <span class="text-gray-400 italic">Sistem</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-serif text-sm text-gray-700">{{ $log->description }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500 italic font-serif">Belum ada audit log tercatat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $logs->links() }}</div>

@endsection
