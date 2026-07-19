@extends('layouts.school-admin')

@section('title', 'Audit Log')

@section('content')
<div class="mb-7">
    <div class="elite-kicker mb-2">Keamanan</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Audit Log</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Riwayat perubahan data per-field. Lacak siapa mengubah apa dan kapan.</p>
</div>

<form method="GET" class="bg-white border border-rule p-4 mb-4 grid md:grid-cols-5 gap-3 text-sm">
    <select name="event" class="border-2 border-rule px-2 py-1.5">
        <option value="">— event —</option>
        @foreach($events as $e)<option value="{{ $e }}" @selected(request('event')===$e)>{{ $e }}</option>@endforeach
    </select>
    <select name="log_name" class="border-2 border-rule px-2 py-1.5">
        <option value="">— model —</option>
        @foreach($logNames as $ln)<option value="{{ $ln }}" @selected(request('log_name')===$ln)>{{ $ln }}</option>@endforeach
    </select>
    <select name="causer_id" class="border-2 border-rule px-2 py-1.5">
        <option value="">— user —</option>
        @foreach($users as $u)<option value="{{ $u->id }}" @selected(request('causer_id')==$u->id)>{{ $u->name }}</option>@endforeach
    </select>
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border-2 border-rule px-2 py-1.5">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border-2 border-rule px-2 py-1.5">
    <div class="md:col-span-5 flex gap-2">
        <button class="btn-elite text-sm">Filter</button>
        <a href="{{ route('admin.audit.index') }}" class="px-3 py-1.5 bg-gray-200 text-sm rounded">Reset</a>
    </div>
</form>

<div class="bg-white border border-rule">
    <table class="w-full text-xs">
        <thead class="bg-gray-50 border-b border-rule">
            <tr>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Waktu</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">User</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Event</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Model</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Subject ID</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Perubahan</th>
                <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Detail</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                @php
                    $attrs = data_get($log->properties, 'attributes', []);
                    $old   = data_get($log->properties, 'old', []);
                    $diffKeys = is_array($attrs) ? array_keys($attrs) : [];
                @endphp
                <tr class="border-b border-rule align-top">
                    <td class="px-3 py-2 font-mono text-[.7rem]">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                    <td class="px-3 py-2 font-serif">{{ $log->causer?->name ?? 'system' }}</td>
                    <td class="px-3 py-2"><span class="elite-kicker text-[.55rem]">{{ $log->event }}</span></td>
                    <td class="px-3 py-2 font-mono text-[.7rem]">{{ $log->log_name }}</td>
                    <td class="px-3 py-2 font-mono text-[.7rem]">{{ $log->subject_id }}</td>
                    <td class="px-3 py-2">
                        @if(count($diffKeys))
                            @foreach(array_slice($diffKeys, 0, 3) as $k)
                                <div><span class="font-mono text-[.65rem] text-gray-500">{{ $k }}:</span>
                                    <span class="line-through text-red-700">{{ \Illuminate\Support\Str::limit((string) data_get($old, $k), 30) }}</span>
                                    →
                                    <span class="text-green-700">{{ \Illuminate\Support\Str::limit((string) data_get($attrs, $k), 30) }}</span>
                                </div>
                            @endforeach
                            @if(count($diffKeys) > 3)<div class="text-gray-500">+{{ count($diffKeys)-3 }} field lain</div>@endif
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right">
                        <a href="{{ route('admin.audit.show', $log) }}" class="text-blue-700 underline text-xs">Lihat</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500 font-serif">Belum ada audit log.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $logs->links() }}</div>
@endsection
